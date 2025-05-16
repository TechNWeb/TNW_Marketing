<?php declare(strict_types=1);
/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\Marketing\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteRepository;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Store\Model\ScopeInterface;
/**
 * Class Survey
 * @package TNW\Marketing\Model\Config
 */
class Survey
{
    /**
     *  config path template
     */
    const START_DATE = '%s/survey/start_date';

    /**
     * template path
     */
    const SURVEY_EMAIL_TEMPLATE = '%s_survey_email_template';

    /**
     * dummy value to avoid issues
     */
    const DEFAULT_MODULE = 'tnw_marketing';

    /**
     * How far should we postpone survey when user just close popup
     */
    const SNOOZE_TIME_MODIFIER = '+3 day';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ConfigInterface
     */
    protected $configResource;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var WebsiteRepository
     */
    protected $websiteRepository;

    /**
     * @var array
     */
    protected $optionsObjects;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    protected $authSession;

    /**
     * Acl builder
     *
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    protected $_aclRetriever;

    /**
     * CanViewNotification constructor.
     *
     * Survey constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $configResource
     * @param TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param TimezoneInterface $timezone
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepository $websiteRepository
     * @param $optionsObjects array
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $configResource,
        TypeListInterface $cacheTypeList,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        TimezoneInterface $timezone,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        WebsiteRepository $websiteRepository,
        $optionsObjects,
        Session $authSession,
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->configResource = $configResource;
        $this->cacheTypeList = $cacheTypeList;
        $this->_eventManager = $eventManager;
        $this->timezone = $timezone;

        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;

        $this->optionsObjects = $optionsObjects;

        $this->authSession = $authSession;
        $this->_aclRetriever = $aclRetriever;
    }

    /**
     * @return string
     */
    public function startDate($module = null)
    {
        if (!$module) {
            $module = self::DEFAULT_MODULE;
        }

        $configPath = sprintf(self::START_DATE, $module);

        return $this->scopeConfig->getValue($configPath);
    }

    public function isAdmin()
    {
        $user = $this->authSession->getUser();
        $role = $user->getRole();
        $resources = $this->_aclRetriever->getAllowedResourcesByRole($role->getId());

        return in_array("Magento_Backend::all", $resources);
    }
    /**
     * @param null $module
     * @return bool
     */
    public function shallShow($module = null)
    {

        // show survey dialog for admin with full permissions only
        if (!$this->isAdmin()) {
            return false;
        }

        $startDate = $this->startDate($module);
        if (empty($startDate)) {
            return false;
        }

        $startTime = $this->timezone->date($startDate);
        if ($this->timezone->date()->diff($startTime)->format('%r%a') <= 0) {
            return true;
        }

        return false;
    }

    /**
     * @param null $timestamp
     * @param null $module
     * @return $this
     */
    public function setStartDate($module = null, $timestamp = null)
    {
        if (!$module) {
            $module = self::DEFAULT_MODULE;
        }

        $configPath = sprintf(self::START_DATE, $module);

        $this->configResource
            ->saveConfig($configPath, $timestamp, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

        return $this;
    }

    /**
     * @param $type string
     * @return \TNW\Marketing\Model\Config\Source\Survey\Options\Base
     */
    public function getSurveyOptionsByType($type)
    {
        $optionsObject = isset($this->optionsObjects[$type]) ?
            $this->optionsObjects[$type] :
            $this->optionsObjects[self::DEFAULT_MODULE];

        return $optionsObject;
    }

    /**
     * @param $type null|string
     * @return string
     */
    public function getEmailTemplate($type = null)
    {
        if (!$type) {
            $type = self::DEFAULT_MODULE;
        }

        $template = sprintf(self::SURVEY_EMAIL_TEMPLATE, $type);

        return $template;
    }

    /**
     * @param $params
     * @return bool
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmail($params)
    {
        if (!empty($params['snooze_survey'])) {
            return false;
        }

        $userEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
        $userName = $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);

        $surveyResult = $params['survey_result'];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->getEmailTemplate($params['type']))
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_ADMINHTML,
                    'store' => $this->storeManager->getStore()->getId()
                ]
            )
            ->setTemplateVars([
                'name' => $userName,
                'email' => $userEmail,
                'surveyResult' => $this->getSurveyOptionsByType($params['type'])->getOptionText($surveyResult),
                'websites' => $this->websiteRepository->getList(),
                'rating' => __('%1 star(s)', $params['rating']),
                'comments' => $params['comments'],
                'module' => $params['module'],
                'moduleName' => $params['moduleName'],
            ])
            ->setFrom([
                'name' => $userName,
                'email' => $userEmail,
            ])
            ->addTo('marketing@powersync.biz', 'Marketing PowerSync')
            ->setReplyTo($userEmail, $userName)
            ->getTransport();

        $transport->sendMessage();

        return true;
    }

    /**
     * @param $params
     * @return int|null
     */
    public function getTimestampByRequest($params)
    {
        $timestamp = null;

        if (!empty($params['snooze_survey'])) {
            $timeModifier = self::SNOOZE_TIME_MODIFIER;
        } else {
            $timeModifier = $this->getSurveyOptionsByType($params['type'])->getOptionTimeModifier($params['survey_result']);
        }

        if (!is_null($timeModifier)) {
            $timestamp = $this->timezone->date()->modify($timeModifier)->getTimestamp();
        } else {
            $timestamp = null;
        }

        return $timestamp;
    }

    /**
     * @param $params
     * @return $this
     * @throws LocalizedException
     */
    public function processAnswer($params)
    {
        $timestamp = $this->getTimestampByRequest($params);

        $this->sendEmail($params);

        $this->setStartDate($params['module'], $timestamp);

        // clear the block html cache
        $this->cacheTypeList->cleanType('config');
        $this->_eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => 'config']);

        return $this;
    }
}
