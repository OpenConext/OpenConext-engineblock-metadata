<?php

namespace OpenConext\Component\EngineBlockMetadata\MetadataRepository\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveDisallowedIdentityProvidersFilter
 *
 * @package OpenConext\Component\EngineBlockMetadata\MetadataRepository\Filter
 */
class RemoveDisallowedIdentityProvidersFilter extends AbstractFilter
{
    /**
     * @var string
     */
    private $serviceProviderEntityId;

    /**
     * @var string[]
     */
    protected $allowedIdentityProviderEntityIds;

    /**
     * @param string $serviceProviderEntityId
     * @param array $allowedIdentityProviderEntityIds
     */
    public function __construct($serviceProviderEntityId, array $allowedIdentityProviderEntityIds)
    {
        $this->serviceProviderEntityId          = $serviceProviderEntityId;
        $this->allowedIdentityProviderEntityIds = $allowedIdentityProviderEntityIds;
    }

    /**
     * {@inheritdoc}
     */
    public function filterRole(AbstractRole $role, LoggerInterface $logger = null)
    {
        if (!$role instanceof IdentityProvider) {
            return $role;
        }

        if (in_array($role->entityId, $this->allowedIdentityProviderEntityIds)) {
            return $role;
        }

        if (!is_null($logger)) {
            $logger->debug(sprintf('Identity Provider is not allowed (%s)', $this->__toString()));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function toQueryBuilder(QueryBuilder $queryBuilder, $repositoryClassName)
    {
        if ($repositoryClassName !== 'OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider') {
            return null;
        }

        return $queryBuilder
            ->andWhere("role.entityId IN(:allowedEntityIds)")
            ->setParameter('allowedEntityIds', $this->allowedIdentityProviderEntityIds);
    }

    /**
     * {@inheritdoc}
     */
    public function toExpression($repositoryClassName)
    {
        if ($repositoryClassName !== 'OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider') {
            return null;
        }

        return Criteria::expr()->in('entityId', $this->allowedIdentityProviderEntityIds);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return parent::__toString() . ' -> ' . $this->serviceProviderEntityId;
    }
}
