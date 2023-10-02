<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class AuthorController extends AbstractController
{
    /**
     * Méthode pour récupérer l'ensemble des auteurs
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des auteurs",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Author::class, groups={"getAuthors"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Authors")
     *
     * @param AuthorRepository $authorRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */ 
    #[Route('/api/authors', name: 'authors', methods: ['GET'])]
    public function getAllAuthors(AuthorRepository $authorRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        $idCache = "getAllAuthor-" . $page . "-" . $limit;

        $jsonAuthorList = $cache->get($idCache, function (ItemInterface $item) use ($authorRepository, $page, $limit, $serializer) {
            $item->tag("authorsCache");
            $authorList = $authorRepository->findAllWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(["getAuthors"]);
            return $serializer->serialize($authorList, 'json', $context);
        });
        
        return new JsonResponse($jsonAuthorList, Response::HTTP_OK, [], true);
    }


    /**
     * Méthode pour récupérer 1 seul auteur
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne les données d'un auteur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Author::class, groups={"getAuthors"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="L'id de l'auteur que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Tag(name="Authors")
     *
     * @param Author $author
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/authors/{id}', name: 'detailAuthor', methods: ['GET'])]
    public function getDetailAuthor(Author $author, SerializerInterface $serializer): JsonResponse {
        $context = SerializationContext::create()->setGroups(["getAuthors"]);
        $jsonAuthor = $serializer->serialize($author, 'json', $context);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
    }


    /**
     * Méthode pour supprimer un auteur
     * 
     * @OA\Response(
     *     response=204,
     *     description="Supprime les données d'un auteur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Author::class, groups={"getAuthors"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="L'id de l'auteur que l'on veut supprimer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Tag(name="Authors")
     *
     * @param Author $author
     * @param EntityManagerInterface $em
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[Route('/api/authors/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un auteur')]
    public function deleteAuthor(Author $author, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse {
        
        $em->remove($author);
        $em->flush();

        $cache->invalidateTags(["authorsCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    
    /**
     * Méthode pour ajouter un nouveau auteur
     *
     * @OA\Response(
     *     response=201,
     *     description="Ajouter les données d'un auteur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Author::class, groups={"getAuthors"}))
     *     )
     * )
     *
     * @OA\Tag(name="Authors")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[Route('/api/authors', name: 'createAuthor', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un auteur')]
    public function createAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse {
        $author = $serializer->deserialize($request->getContent(), Author::class, 'json');
        
        $errors = $validator->validate($author);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($author);
        $em->flush();
        
        $cache->invalidateTags(["authorsCache"]);

        $context = SerializationContext::create()->setGroups(["getAuthors"]);
        $jsonAuthor = $serializer->serialize($author, 'json', $context);
        $location = $urlGenerator->generate('detailAuthor', ['id' => $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, ["Location" => $location], true);	
    }


    #[Route('/api/authors/{id}', name:"updateAuthors", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer un auteur')]
    /**
     * Méthode pour modifier un auteur
     *
     * @OA\Response(
     *     response=204,
     *     description="Modifier les données d'un auteur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Author::class, groups={"getAuthors"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="L'id de l'auteur que l'on veut modifier",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Tag(name="Authors")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Author $currentAuthor
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    public function updateAuthor(Request $request, SerializerInterface $serializer, Author $currentAuthor, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse {

        $errors = $validator->validate($currentAuthor);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $newAuthor = $serializer->deserialize($request->getContent(), Author::class, 'json');

        $currentAuthor->setFirstName($newAuthor->getFirstName());
        $currentAuthor->setLastName($newAuthor->getLastName());

        $em->persist($currentAuthor);
        $em->flush();

        $cache->invalidateTags(["authorsCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
