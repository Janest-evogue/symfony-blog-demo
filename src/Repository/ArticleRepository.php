<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @param array $filters
     * @return QueryBuilder
     */
    public function getSearchQb(array $filters = []): QueryBuilder
    {
        // constructeur de requête SQL
        // le 'a' est l'alias de l'entité Article
        $qb = $this->createQueryBuilder('a');

        // tri par date publication décroissante
        $qb->orderBy('a.publicationDate', 'DESC');

        if (!empty($filters['title'])) {
            $qb
                ->andWhere('a.title LIKE :title')
                ->setParameter('title', '%' . $filters['title'] . '%')
            ;
        }

        if (!empty($filters['category'])) {
            $qb
                ->andWhere('a.category = :category')
                ->setParameter('category', $filters['category'])
            ;
        }

        if (!empty($filters['start_date'])) {
            $qb
                ->andWhere('a.publicationDate >= :start_date')
                ->setParameter('start_date', $filters['start_date'])
            ;
        }

        if (!empty($filters['end_date'])) {
            $qb
                ->andWhere('a.publicationDate <= :end_date')
                ->setParameter('end_date', $filters['end_date'])
            ;
        }

        if (!empty($filters['with_comments'])) {
            $qb->andWhere('a.comments IS NOT EMPTY');
        }

        return $qb;
    }

    public function search(array $filters = [])
    {
        $qb = $this->getSearchQb($filters);

        // la requête générée
        $query = $qb->getQuery();

        // on retourne le résultat de la requête
        return $query->getResult();
    }
}
