<?php

namespace OpenConext\Component\EngineBlockMetadata\JanusRestV1;

/**
 * Interface RestClientInterface
 * @package OpenConext\Component\EngineBlockMetadata\JanusRestV1
 */
interface RestClientInterface
{
    /**
     * Retrieve the allowed IDPs for an SP. The SP is only
     * allowed to make connections to the retrieved IDP's.
     *
     * @param string $spEntityId the URN of the SP entity.
     * @return array containing the URN's of the IDP's that this SP is allowed to make a connection to.
     */
    public function getAllowedIdps($spEntityId);

    /**
     * Get full information for a given entity.
     *
     * @param $entityId
     * @return array
     */
    public function getEntity($entityId);

    /**
     * Retrieve a list of metadata values of all available
     * IDP entities.
     * @param array $keys An array of keys to retrieve. Retrieves
     *                    all available keys if omited or empty
     * @param String $forSpEntityId An optional identifier of an SP
     *               If present, idplist will return a list of only the
     *               idps that this sp is allowed to authenticate against.
     * @return array An associative array of values, indexed by IDP
     *               identifier. Each value is another associative
     *               array with key/value pairs containing the metadata.
     */
    public function getIdpList($keys = array(), $forSpEntityId = null);

    /**
     * Find entities based on metadata.
     *
     * Finds the identifiers (URNS) of all SPs/IDPs that match a certain
     * metadata value. The rest webservice that's behind this call supports
     * regular expressions in the metadata values in its database. So you
     * can pass "www.google.com" as value to this function and match
     * entities that have '.*\.google\.com in their url:en metadata field.
     *
     * @param string $key The key you want to match against
     * @param string $value The value you want to match against
     * @return array An array of URNS of entities that match the request.
     */
    public function findIdentifiersByMetadata($key, $value);

    /**
     * Retrieve a list of metadata values of all available
     * SP entities.
     * @param array $keys An array of keys to retrieve. Retrieves
     *                    all available keys if omited or empty
     * @return array An associative array of values, indexed by SP
     *               identifier. Each value is another associative
     *               array with key/value pairs containing the metadata.
     */
    public function getSpList($keys = array());
}
