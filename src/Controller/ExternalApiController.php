<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class ExternalApiController extends AbstractController
{
    /**
     * Méthode pour les données de la documentations de symfony sur Github
     *
     * @OA\Tag(name="ExternalAPI")
     * 
     * @param HttpClientInterface $httpClient
     * @return JsonResponse
     */
    #[Route('/api/external/getSfDoc', name: 'app_external_api')]
    public function getSymfonyDoc(HttpClientInterface $httpClient): JsonResponse
    {
        $response = $httpClient->request(
            'GET',
            'https://api.github.com/repos/symfony/symfony-docs'
        );
        return new JsonResponse($response->getContent(), $response->getStatusCode(), [], true);
    }
}
