<?php

namespace OpenConext\Component\EngineBlockMetadata\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use OpenConext\Component\EngineBlockMetadata\Logo;
use OpenConext\Component\EngineBlockMetadata\Organization;
use OpenConext\Component\EngineBlockMetadata\ContactPerson;
use OpenConext\Component\EngineBlockMetadata\Service;
use OpenConext\Component\EngineBlockMetadata\X509\X509Certificate;
use SAML2_Const;

/**
 * Abstract base class for configuration entities.
 *
 * @package OpenConext\Component\EngineBlockMetadata\Entity
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @ORM\Entity
 * @ORM\Table(name="sso_provider_roles")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *  "sp"  = "OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider",
 *  "idp" = "OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider"
 * })
 */
abstract class AbstractRole
{
    const WORKFLOW_STATE_PROD = 'prodaccepted';
    const WORKFLOW_STATE_TEST = 'testaccepted';
    const WORKFLOW_STATE_DEFAULT = self::WORKFLOW_STATE_PROD;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_id", type="string")
     */
    public $entityId;

    /**
     * @var string
     * @ORM\Column(name="name_nl", type="string")
     */
    public $nameNl;

    /**
     * @var string
     *
     * @ORM\Column(name="name_en", type="string")
     */
    public $nameEn;

    /**
     * @var string
     *
     * @ORM\Column(name="description_nl", type="string")
     */
    public $descriptionNl;

    /**
     * @var string
     *
     * @ORM\Column(name="description_en", type="string")
     */
    public $descriptionEn;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name_nl", type="string")
     */
    public $displayNameNl;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name_en", type="string")
     */
    public $displayNameEn;

    /**
     * @var Logo
     *
     * @ORM\Column(name="logo", type="text")
     */
    public $logo;

    /**
     * @var string
     *
     * @ORM\Column(name="organization_nl_name",type="string")
     */
    public $organizationNl;

    /**
     * @var string
     *
     * @ORM\Column(name="organization_en_name",type="string")
     */
    public $organizationEn;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords_nl", type="string")
     */
    public $keywordsNl;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords_en", type="string")
     */
    public $keywordsEn;

    /**
     * @var bool
     *
     * @ORM\Column(name="publish_in_edugain", type="boolean")
     */
    public $publishInEdugain;

    /**
     * @var X509Certificate[]
     *
     * @ORM\Column(name="certificates", type="array")
     */
    public $certificates = array();

    /**
     * @var string
     *
     * @ORM\Column(name="workflow_state", type="string")
     */
    public $workflowState = self::WORKFLOW_STATE_DEFAULT;

    /**
     * @var ContactPerson[]
     *
     * @ORM\Column(name="contact_persons", type="array")
     */
    public $contactPersons;

    /**
     * @var string
     *
     * @ORM\Column(name="name_id_format", type="string")
     */
    public $nameIdFormat;

    /**
     * @var string[]
     *
     * @ORM\Column(name="name_id_formats", type="array")
     */
    public $nameIdFormats;

    /**
     * @var Service
     */
    public $singleLogoutService;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="publish_in_edu_gain_date", type="date")
     */
    public $publishInEduGainDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="disable_scoping", type="boolean")
     */
    public $disableScoping;

    /**
     * @var bool
     *
     * @ORM\Column(name="additional_logging", type="boolean")
     */
    public $additionalLogging = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="requests_must_be_signed", type="boolean")
     */
    public $requestsMustBeSigned = false;

    /**
     * @var string
     *
     * @ORM\Column(name="response_processing_service_binding", type="string")
     */
    public $responseProcessingService;

    public function __construct(
        $entityId,
        Organization $organizationEn = null,
        Organization $organizationNl = null,
        Service $singleLogoutService = null,
        $additionalLogging = false,
        array $certificates = array(),
        array $contactPersons = array(),
        $descriptionEn = '',
        $descriptionNl = '',
        $disableScoping = false,
        $displayNameEn = '',
        $displayNameNl = '',
        $keywordsEn = '',
        $keywordsNl = '',
        Logo $logo = null,
        $nameEn = '',
        $nameNl = '',
        $nameIdFormat = null,
        $nameIdFormats = array(
            SAML2_Const::NAMEID_TRANSIENT,
            SAML2_Const::NAMEID_PERSISTENT,
        ),
        $publishInEduGainDate = null,
        $publishInEdugain = false,
        $requestsMustBeSigned = false,
        Service $responseProcessingService = null,
        $workflowState = self::WORKFLOW_STATE_DEFAULT
    ) {
        $this->additionalLogging = $additionalLogging;
        $this->certificates = $certificates;
        $this->contactPersons = $contactPersons;
        $this->descriptionEn = $descriptionEn;
        $this->descriptionNl = $descriptionNl;
        $this->disableScoping = $disableScoping;
        $this->displayNameEn = $displayNameEn;
        $this->displayNameNl = $displayNameNl;
        $this->entityId = $entityId;
        $this->keywordsEn = $keywordsEn;
        $this->keywordsNl = $keywordsNl;
        $this->logo = $logo;
        $this->nameEn = $nameEn;
        $this->nameIdFormat = $nameIdFormat;
        $this->nameIdFormats = $nameIdFormats;
        $this->nameNl = $nameNl;
        $this->organizationEn = $organizationEn;
        $this->organizationNl = $organizationNl;
        $this->publishInEduGainDate = $publishInEduGainDate;
        $this->publishInEdugain = $publishInEdugain;
        $this->requestsMustBeSigned = $requestsMustBeSigned;
        $this->responseProcessingService = $responseProcessingService;
        $this->singleLogoutService = $singleLogoutService;
        $this->workflowState = $workflowState;
    }
}