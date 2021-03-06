<?php

namespace OpenConext\Component\EngineBlockMetadata\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;
use OpenConext\Component\EngineBlockMetadata\Logo;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\Visitor\VisitorInterface;
use OpenConext\Component\EngineBlockMetadata\Organization;
use OpenConext\Component\EngineBlockMetadata\ContactPerson;
use OpenConext\Component\EngineBlockMetadata\Service;
use OpenConext\Component\EngineBlockMetadata\X509\X509Certificate;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;

/**
 * Abstract base class for configuration entities.
 *
 * @package OpenConext\Component\EngineBlockMetadata\Entity
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\Table(
 *      name="sso_provider_roles_eb5",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="idx_sso_provider_roles_entity_id_type",
 *              columns={"type", "entity_id"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(
 *              name="idx_sso_provider_roles_type",
 *              columns={"type"}
 *          ),
 *          @ORM\Index(
 *              name="idx_sso_provider_roles_entity_id",
 *              columns={"entity_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_sso_provider_roles_publish_in_edugain",
 *              columns={"publish_in_edugain"}
 *          ),
 *      }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *  "sp"  = "OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider",
 *  "idp" = "OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider"
 * })
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
    public $id;

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
     * @ORM\Column(name="logo", type="object")
     */
    public $logo;

    /**
     * @var Organization
     *
     * @ORM\Column(name="organization_nl_name",type="object", nullable=true)
     */
    public $organizationNl;

    /**
     * @var Organization
     *
     * @ORM\Column(name="organization_en_name",type="object", nullable=true)
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
     * @ORM\Column(name="name_id_format", type="string", nullable=true)
     */
    public $nameIdFormat;

    /**
     * @var string[]
     *
     * @ORM\Column(name="name_id_formats", type="array")
     */
    public $supportedNameIdFormats;

    /**
     * @var Service
     *
     * @ORM\Column(name="single_logout_service", type="object", nullable=true)
     */
    public $singleLogoutService;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="publish_in_edu_gain_date", type="date", nullable=true)
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
     * @ORM\Column(name="signature_method", type="string")
     */
    public $signatureMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="response_processing_service_binding", type="string", nullable=true)
     */
    public $responseProcessingService;

    /**
     * @var string
     *
     * @ORM\Column(name="manipulation", type="text")
     */
    protected $manipulation;

    /**
     * @param $entityId
     * @param Organization $organizationEn
     * @param Organization $organizationNl
     * @param Service $singleLogoutService
     * @param bool $additionalLogging
     * @param array $certificates
     * @param array $contactPersons
     * @param string $descriptionEn
     * @param string $descriptionNl
     * @param bool $disableScoping
     * @param string $displayNameEn
     * @param string $displayNameNl
     * @param string $keywordsEn
     * @param string $keywordsNl
     * @param Logo $logo
     * @param string $nameEn
     * @param string $nameNl
     * @param null $nameIdFormat
     * @param array $supportedNameIdFormats
     * @param null $publishInEduGainDate
     * @param bool $publishInEdugain
     * @param bool $requestsMustBeSigned
     * @param string $signatureMethod
     * @param Service $responseProcessingService
     * @param string $workflowState
     * @param string $manipulation
     */
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
        $supportedNameIdFormats = array(
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_PERSISTENT,
        ),
        $publishInEduGainDate = null,
        $publishInEdugain = false,
        $requestsMustBeSigned = false,
        $signatureMethod = XMLSecurityKey::RSA_SHA1,
        Service $responseProcessingService = null,
        $workflowState = self::WORKFLOW_STATE_DEFAULT,
        $manipulation = ''
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
        $this->supportedNameIdFormats = $supportedNameIdFormats;
        $this->nameNl = $nameNl;
        $this->organizationEn = $organizationEn;
        $this->organizationNl = $organizationNl;
        $this->publishInEduGainDate = $publishInEduGainDate;
        $this->publishInEdugain = $publishInEdugain;
        $this->requestsMustBeSigned = $requestsMustBeSigned;
        $this->signatureMethod = $signatureMethod;
        $this->responseProcessingService = $responseProcessingService;
        $this->singleLogoutService = $singleLogoutService;
        $this->workflowState = $workflowState;
        $this->manipulation = $manipulation;
    }

    /**
     * @param VisitorInterface $visitor
     * @return null|AbstractRole
     */
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitRole($this);
    }

    /**
     * @return string
     */
    public function getManipulation()
    {
        return $this->manipulation;
    }

    /**
     * @return $this
     */
    public function toggleWorkflowState()
    {
        if ($this->workflowState === static::WORKFLOW_STATE_PROD) {
            $this->workflowState = static::WORKFLOW_STATE_TEST;
            return $this;
        }

        if ($this->workflowState === static::WORKFLOW_STATE_TEST) {
            $this->workflowState = static::WORKFLOW_STATE_PROD;
            return $this;
        }

        throw new \RuntimeException('Unknown workflow state');
    }
}
