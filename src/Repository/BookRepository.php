<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Cette méthode retourne $limit livres à partir de la page $page. 
     *
     * @param integer $page
     * @param integer $limit
     * @return mixed
     */
    public function findAllWithPagination(int $page, int $limit) {
        $qb = $this->createQueryBuilder('b')
            ->setFirstResult(($page -1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
