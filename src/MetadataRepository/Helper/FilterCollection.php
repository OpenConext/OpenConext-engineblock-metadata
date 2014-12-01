<?php

namespace OpenConext\Component\EngineBlockMetadata\MetadataRepository\Helper;

use Doctrine\ORM\QueryBuilder;
use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\Filter\FilterInterface;

/**
 * Class FilterCollection
 * @package OpenConext\Component\EngineBlockMetadata\MetadataRepository\Helper
 */
class FilterCollection
{
    /**
     * @var FilterInterface[]
     */
    private $filters = array();

    /**
     * @var string
     */
    private $disallowedByFilter;

    /**
     * @param FilterInterface $filter
     */
    public function add(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function toQueryBuilder(QueryBuilder $queryBuilder)
    {
        foreach ($this->filters as $filter) {
            $filter->toQueryBuilder($queryBuilder);
        }
    }

    /**
     * @param AbstractRole $entity
     * @return AbstractRole|null
     */
    public function filterEntity(AbstractRole $entity)
    {
        foreach ($this->filters as $filter) {
            $entity = $filter->filter($entity);

            if (!$entity) {
                $this->disallowedByFilter = $filter->__toString();
                return null;
            }
        }
        return $entity;
    }

    /**
     * @return string
     */
    public function getDisallowedByFilter()
    {
        return $this->disallowedByFilter;
    }
}
