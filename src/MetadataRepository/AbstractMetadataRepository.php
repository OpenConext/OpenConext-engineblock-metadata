<?php

namespace OpenConext\Component\EngineBlockMetadata\MetadataRepository;

use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;
use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\Filter\FilterInterface;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\Helper\FilterCollection;

/**
 * Class AbstractMetadataRepository
 * @package OpenConext\Component\EngineBlockMetadata\MetadataRepository
 * @SuppressWarnings(PMD.TooManyMethods)
 */
abstract class AbstractMetadataRepository implements MetadataRepositoryInterface
{
    /**
     * @var FilterCollection
     */
    protected $filterCollection;

    /**
     * Create a new Metadata Repository
     */
    protected function __construct()
    {
        $this->filterCollection = new Helper\FilterCollection();
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function filter(FilterInterface $filter)
    {
        $this->filterCollection->add($filter);
        return $this;
    }

    /**
     * @return string[]
     */
    public function findAllIdentityProviderEntityIds()
    {
        $identityProviders = $this->findIdentityProviders();

        $entityIds = array();
        foreach ($identityProviders as $identityProvider) {
            $entityIds[] = $identityProvider->entityId;
        }
        return $entityIds;
    }

    /**
     * @return string[]
     */
    public function findReservedSchacHomeOrganizations()
    {
        return array_unique(
            array_map(
                function (IdentityProvider $entity) {
                    return $entity->schacHomeOrganization;
                },
                $this->findIdentityProviders()
            ),
            SORT_STRING
        );
    }

    /**
     * @param array $identityProviderEntityIds
     * @return array|IdentityProvider[]
     * @throws EntityNotFoundException
     */
    public function fetchIdentityProvidersByEntityId(array $identityProviderEntityIds)
    {
        $identityProviders = $this->findIdentityProviders();

        $filteredIdentityProviders = array();
        foreach ($identityProviderEntityIds as $identityProviderEntityId) {
            if (!isset($identityProviders[$identityProviderEntityId])) {
                // @todo warn
                continue;
            }

            $filteredIdentityProviders[$identityProviderEntityId] = $identityProviders[$identityProviderEntityId];
        }
        return $filteredIdentityProviders;
    }

    /**
     * @param string $entityId
     * @return ServiceProvider
     * @throws EntityNotFoundException
     */
    public function fetchServiceProviderByEntityId($entityId)
    {
        $serviceProvider = $this->findServiceProviderByEntityId($entityId);

        if (!$serviceProvider) {
            throw new EntityNotFoundException("Service Provider '$entityId' not found in InMemoryMetadataRepository");
        }

        return $serviceProvider;
    }

    /**
     * @param $entityId
     * @return null|IdentityProvider|ServiceProvider
     * @throws EntityNotFoundException
     */
    public function fetchIdentityProviderByEntityId($entityId)
    {
        $identityProvider = $this->findIdentityProviderByEntityId($entityId);

        if (!$identityProvider) {
            throw new EntityNotFoundException("Identity Provider '$entityId' not found in InMemoryMetadataRepository");
        }

        return $identityProvider;
    }

    /**
     *
     * @param string $entityId
     * @return AbstractRole
     * @throws EntityNotFoundException
     */
    public function fetchEntityByEntityId($entityId)
    {
        $entity = $this->findEntityByEntityId($entityId);

        if (!$entity) {
            throw new EntityNotFoundException("Entity '$entityId' not found in InMemoryMetadataRepository");
        }

        return $entity;
    }

    /**
     * @param string $entityId
     * @return AbstractRole|null
     */
    public function findEntityByEntityId($entityId)
    {
        $serviceProvider = $this->findServiceProviderByEntityId($entityId);
        if ($serviceProvider) {
            return $serviceProvider;
        }

        $identityProvider = $this->findIdentityProviderByEntityId($entityId);
        if ($identityProvider) {
            return $identityProvider;
        }

        return null;
    }

    /**
     * @param AbstractRole $entity
     * @return string
     */
    public function fetchEntityManipulation(AbstractRole $entity)
    {
        return '';
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @return AttributeReleasePolicy
     */
    public function fetchServiceProviderArp(ServiceProvider $serviceProvider)
    {
        return null;
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @return \string[]
     */
    public function findAllowedIdpEntityIdsForSp(ServiceProvider $serviceProvider)
    {
        return $this->findAllIdentityProviderEntityIds();
    }
}
