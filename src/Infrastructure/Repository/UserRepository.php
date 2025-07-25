<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use DateInterval;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @extends AbstractRepository<User>
 */
class UserRepository extends AbstractRepository
{
    public function create(User $user): int
    {
        return $this->store($user);
    }

    public function subscribeUser(User $author, User $follower): void
    {
        $author->addFollower($follower);
        $follower->addAuthor($author);
        $this->flush();
    }

    /**
     * @return User[]
     */
    public function findUsersByLogin(string $name): array
    {
        return $this->entityManager->getRepository(User::class)->findBy(['login' => $name]);
    }


    /**
     * @return User[]
     */
    public function findUsersByLoginWithCriteria(string $login): array
    {
        $criteria = Criteria::create();
        $criteria->andWhere(Criteria::expr()?->eq('login', $login));
        $repository = $this->entityManager->getRepository(User::class);

        return $repository->matching($criteria)->toArray();
    }

    public function find(int $userId): ?User
    {
        $repository = $this->entityManager->getRepository(User::class);
        /** @var User|null $user */
        $user = $repository->find($userId);

        return $user;
    }

    public function updateLogin(User $user, string $login): void
    {
        $user->setLogin($login);
        $this->flush();
    }

    public function updateAvatarLink(User $user, string $avatarLink): void
    {
        $user->setAvatarLink($avatarLink);
        $this->flush();
    }

    public function findUsersByLoginWithQueryBuilder(string $login): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from(User::class, 'u')
            ->andWhere($queryBuilder->expr()->like('u.login',':userLogin'))
            ->setParameter('userLogin', "%$login%");

        return $queryBuilder->getQuery()->getResult();
    }

    public function updateUserLoginWithQueryBuilder(int $userId, string $login): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->update(User::class,'u')
            ->set('u.login', ':userLogin')
            ->where($queryBuilder->expr()->eq('u.id', ':userId'))
            ->setParameter('userId', $userId)
            ->setParameter('userLogin', $login);

        $queryBuilder->getQuery()->execute();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateUserLoginWithDBALQueryBuilder(int $userId, string $login): void
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder->update('"user"')
            ->set('login', ':userLogin')
            ->where($queryBuilder->expr()->eq('id', ':userId'))
            ->setParameter('userId', $userId)
            ->setParameter('userLogin', $login);

        $queryBuilder->executeStatement();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findUserWithTweetsWithQueryBuilder(int $userId): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('u', 't')
            ->from(User::class, 'u')
            ->leftJoin('u.tweets', 't')
            ->where($queryBuilder->expr()->eq('u.id', ':userId'))
            ->setParameter('userId', $userId);

        return $queryBuilder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findUserWithTweetsWithDBALQueryBuilder(int $userId): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder->select('u', 't')
            ->from('"user"', 'u')
            ->leftJoin('u', 'tweet', 't', 'u.id = t.author_id')
            ->where($queryBuilder->expr()->eq('u.id', ':userId'))
            ->setParameter('userId', $userId);

        return $queryBuilder->executeQuery()->fetchAllNumeric();
    }

    public function remove(User $user): void
    {
        $user->setDeletedAt();
        $this->flush();
    }

    public function removeInFuture(User $user, DateInterval $dateInterval): void
    {
        $user->setDeletedAtInFuture($dateInterval);
        $this->flush();
    }

    /**
     * @return User[]
     */
    public function findUsersByLoginWithDeleted(string $name): array
    {
        $filters = $this->entityManager->getFilters();
        if ($filters->isEnabled('soft_delete_filter')) {
            $filters->disable('soft_delete_filter');
        }
        return $this->entityManager->getRepository(User::class)->findBy(['login' => $name]);
    }

    /**
     * @return User[]
     */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(User::class)->findAll();
    }

    public function updateUserToken(User $user): string
    {
        $token = base64_encode(random_bytes(20));
        $user->setToken($token);
        $this->flush();

        return $token;
    }

    public function findUserByToken(string $token): ?User
    {
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['token' => $token]);

        return $user;
    }

    public function clearUserToken(User $user): void
    {
        $user->setToken(null);
        $this->flush();
    }
}
