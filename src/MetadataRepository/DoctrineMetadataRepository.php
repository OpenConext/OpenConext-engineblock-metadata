<?php

namespace OpenConext\Component\EngineBlockMetadata\MetadataRepository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use OpenConext\Component\EngineBlockMetadata\Container\ContainerInterface;
use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class DoctrineMetadataRepository
 * @package OpenConext\Component\EngineBlockMetadata\MetadataRepository
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DoctrineMetadataRepository extends AbstractMetadataRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $spRepository;

    /**
     * @var EntityRepository
     */
    private $idpRepository;

    /**
     * @param array $repositoryConfig
     * @param ContainerInterface $container
     * @return self
     */
    public static function createFromConfig(array $repositoryConfig, ContainerInterface $container)
    {
        /** @var EntityManager $em */
        $em = $container->getEntityManager();
        $idpRepository = $em->getRepository('OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider');
        $spRepository  = $em->getRepository('OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider');

        return new self($em, $spRepository, $idpRepository);
    }

    /**
     * @param EntityRepository $spRepository
     * @param EntityRepository $idpRepository
     */
    public function __construct(
        EntityManager $entityManager,
        EntityRepository $spRepository,
        EntityRepository $idpRepository
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->spRepository  = $spRepository;
        $this->idpRepository = $idpRepository;
    }

    /**
     *
     * @return string[]
     */
    public function findAllIdentityProviderEntityIds()
    {
        $queryBuilder = $this->idpRepository
            ->createQueryBuilder('role')
            ->select('role.entityId');

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->idpRepository->getClassName());

        return array_map('current', $queryBuilder->getQuery()->execute(null, AbstractQuery::HYDRATE_ARRAY));
    }

    /**
     * Find all SchacHomeOrganizations that are reserved by Identity Providers.
     *
     * @return string[]
     */
    public function findReservedSchacHomeOrganizations()
    {
        $queryBuilder = $this->idpRepository
            ->createQueryBuilder('role')
            ->select('role.schacHomeOrganization')
            ->distinct()
            ->orderBy('role.schacHomeOrganization');

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->idpRepository->getClassName());

        return $queryBuilder
            ->getQuery()
            ->execute();
    }

    /**
     * @param array $identityProviderIds
     * @return array|IdentityProvider[]
     * @throws EntityNotFoundException
     */
    public function findIdentityProvidersByEntityId(array $identityProviderIds)
    {
        $identityProviders = $this->idpRepository->matching(
            $this->compositeFilter->toCriteria($this->idpRepository->getClassName())
                ->andWhere(Criteria::expr()->in('entityId', $identityProviderIds))
        )->toArray();

        foreach ($identityProviders as $identityProvider) {
            if (!$identityProvider instanceof IdentityProvider) {
                throw new RuntimeException('Non-IdentityProvider found');
            }

            $identityProvider->accept($this->compositeVisitor);
        }

        return $identityProviders;
    }

    /**
     * @param string $entityId
     * @return IdentityProvider|null
     */
    public function findIdentityProviderByEntityId($entityId)
    {
        $identityProviderCollection = $this->idpRepository->matching(
            $this->compositeFilter->toCriteria($this->idpRepository->getClassName())
                ->andWhere(Criteria::expr()->eq('entityId', $entityId))
        );

        if ((int) $identityProviderCollection->count() === 0) {
            return null;
        }

        if ((int) $identityProviderCollection->count() > 1) {
            throw new RuntimeException('Multiple Identity Providers found for entityId: ' . $entityId);
        }

        $identityProvider = $identityProviderCollection->first();
        if (!$identityProvider instanceof IdentityProvider) {
            throw new RuntimeException('Entity found for entityId: ' . $entityId . ' is not an Identity Provider!');
        }

        $identityProvider->accept($this->compositeVisitor);

        return $identityProvider;
    }

    /**
     * @param $entityId
     * @param LoggerInterface|null $logger
     * @return null|ServiceProvider
     */
    public function findServiceProviderByEntityId($entityId, LoggerInterface $logger = null)
    {
        $serviceProviderCollection = $this->spRepository->matching(
            $this->compositeFilter->toCriteria($this->spRepository->getClassName())
                ->andWhere(Criteria::expr()->eq('entityId', $entityId))
        );

        if ((int) $serviceProviderCollection->count() === 0) {
            return null;
        }

        if ((int) $serviceProviderCollection->count() > 1) {
            throw new RuntimeException('Multiple Service Providers found for entityId: ' . $entityId);
        }

        $serviceProvider = $serviceProviderCollection->first();
        if (!$serviceProvider instanceof ServiceProvider) {
            throw new RuntimeException('Entity found for entityId: ' . $entityId . ' is not an ServiceProvider!');
        }

        if (!$serviceProvider) {
            return null;
        }

        $serviceProvider->accept($this->compositeVisitor);

        return $serviceProvider;
    }

    /**
     * @return IdentityProvider[]
     */
    public function findIdentityProviders()
    {
        $identityProviders = $this->idpRepository->matching(
            $this->compositeFilter->toCriteria($this->idpRepository->getClassName())
        )->toArray();

        foreach ($identityProviders as $identityProvider) {
            if (!$identityProvider instanceof IdentityProvider) {
                throw new RuntimeException('Non-IdentityProvider found');
            }
            $identityProvider->accept($this->compositeVisitor);
        }

        return $identityProviders;
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @return \string[]
     */
    public function findAllowedIdpEntityIdsForSp(ServiceProvider $serviceProvider)
    {
        return $serviceProvider->allowedIdpEntityIds;
    }

    /**
     * @return AbstractRole[]
     */
    public function findEntitiesPublishableInEdugain(MetadataRepositoryInterface $repository = null)
    {
        $result = array();
        $result = array_merge($result, $this->idpRepository->findBy(array('publishInEdugain' => true)));
        $result = array_merge($result, $this->spRepository->findBy(array('publishInEdugain' => true)));
        return $result;
    }

    /**
     * Synchronize the database with the provided roles.
     *
     * Any roles (idp or sp) already existing the database are updated. New
     * roles are created. All identity- or service providers in the database
     * which are NOT in the provided roles are deleted at the end of the
     * synchronization process.
     *
     * @param AbstractRole[] $roles
     * @return SynchronizationResult
     */
    public function synchronize(array $roles)
    {
        $result = new SynchronizationResult();

        $repository = $this;
        $this->entityManager->transactional(function (EntityManager $em) use ($roles, $repository, $result) {
            $idpsToBeRemoved = $repository->findAllIdentityProviderEntityIds();
            $spsToBeRemoved = $repository->findAllServiceProviderEntityIds();

            foreach ($roles as $role) {
                if ($role instanceof IdentityProvider) {
                    // Does the IDP already exist in the database?
                    $index = array_search($role->entityId, $idpsToBeRemoved);

                    if ($index === false) {
                        // The IDP is new: create it.
                        $em->persist($role);
                        $result->createdIdentityProviders[] = $role->entityId;
                    } else {
                        // Remove from the list of entity ids so it won't get deleted later on.
                        unset($idpsToBeRemoved[$index]);

                        // The IDP already exists: update it.
                        $identityProvider = $repository->findIdentityProviderByEntityId($role->entityId);
                        $role->id = $identityProvider->id;
                        $em->persist($em->merge($role));
                        $result->updatedIdentityProviders[] = $role->entityId;
                    }
                    continue;
                }

                if ($role instanceof ServiceProvider) {
                    // Does the SP already exist in the database?
                    $index = array_search($role->entityId, $spsToBeRemoved);
                    if ($index === false) {
                        // The SP is new: create it.
                        $em->persist($role);
                        $result->createdServiceProviders[] = $role->entityId;
                    } else {
                        // Remove from the list of entity ids so it won't get deleted later on.
                        unset($spsToBeRemoved[$index]);

                        // The SP already exists: update it.
                        $serviceProvider = $repository->findServiceProviderByEntityId($role->entityId);
                        $role->id = $serviceProvider->id;
                        $em->persist($em->merge($role));
                        $result->updatedServiceProviders[] = $role->entityId;
                    }
                    continue;
                }

                throw new RuntimeException('Unsupported role provided to synchonization: ' . var_export($role, true));
            }

            if ($idpsToBeRemoved) {
                $this->deleteRolesByEntityIds($this->idpRepository, $idpsToBeRemoved);

                $result->removedIdentityProviders = $idpsToBeRemoved;
            }

            if ($spsToBeRemoved) {
                $this->deleteRolesByEntityIds($this->spRepository, $spsToBeRemoved);

                $result->removedServiceProviders = $spsToBeRemoved;
            }
        });

        return $result;
    }

    /**
     * @param EntityRepository $repository
     * @param array $entityIds
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    private function deleteRolesByEntityIds(EntityRepository $repository, array $entityIds)
    {
        $qb = $repository->createQueryBuilder('role')
            ->delete()
            ->where('role.entityId IN (:ids)')
            ->setParameter('ids', $entityIds);

        $qb->getQuery()->execute();
    }

    public function findAllServiceProviderEntityIds()
    {
        $queryBuilder = $this->spRepository
            ->createQueryBuilder('role')
            ->select('role.entityId');

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->spRepository->getClassName());

        return array_map('current', $queryBuilder->getQuery()->execute(null, AbstractQuery::HYDRATE_ARRAY));
    }
}
