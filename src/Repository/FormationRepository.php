<?php

namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Formation>
 *
 * @method Formation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Formation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Formation[]    findAll()
 * @method Formation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }
    public function findFormationsNonInscritesPourEmploye($employe)
    {
        $qb = $this->createQueryBuilder('f');

        // Sous-requête pour récupérer les IDs des formations auxquelles l'employé est déjà inscrit
        $subQuery = $this->_em->createQueryBuilder()
            ->select('IDENTITY(i.formation)')
            ->from('App\Entity\Inscription', 'i')
            ->where('i.employe = :employe');

        // Utilisation de NOT IN pour exclure les formations auxquelles l'employé est déjà inscrit
        $qb->andWhere($qb->expr()->notIn('f.id', $subQuery->getDQL()))
            ->setParameter('employe', $employe);

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Formation[] Returns an array of Formation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Formation
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
