<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Usuario>
 */
class UsuarioRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Usuario) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return Usuario[] Returns an array of Usuario objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    /**
     * Busca usuarios cercanos dentro de 5km usando la fórmula de Haversine
     * @return Usuario[] Returns an array of Usuario objects nearby
     */
    public function buscarCercanos(float $latitud, float $longitud, float $distanciaKm = 5): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT u.*, 
            (
                6371 * acos(
                    cos(radians(:latitud)) * cos(radians(u.latitud)) *
                    cos(radians(u.longitud) - radians(:longitud)) +
                    sin(radians(:latitud)) * sin(radians(u.latitud))
                )
            ) as distancia
            FROM usuario u
            WHERE u.latitud IS NOT NULL 
            AND u.longitud IS NOT NULL
            HAVING distancia <= :distanciaKm
            ORDER BY distancia ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'latitud' => $latitud,
            'longitud' => $longitud,
            'distanciaKm' => $distanciaKm
        ]);
        
        $usuarios = [];
        foreach ($result->fetchAllAssociative() as $row) {
            $usuario = $this->find($row['id']);
            if ($usuario) {
                $usuarios[] = $usuario;
            }
        }
        
        return $usuarios;
    }

    //    public function findOneBySomeField($value): ?Usuario
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
