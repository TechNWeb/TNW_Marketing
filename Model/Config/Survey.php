<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\Marketing\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteRepository;

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
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

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
        ModuleListInterface $moduleList,
        $optionsObjects
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
        $this->moduleList = $moduleList;
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

    /**
     * @param $module
     * @return bool|null
     */
    private function resolver($module): bool
    {
        $isModuleInstall = null;
        switch ($module):
            case 'tnw_module-authorizenetcim':
                $isModuleInstall = $this->moduleList->getOne('TNW_Subscriptions');
                break;
            case 'tnw_module-stripe':
                $isModuleInstall = $this->moduleList->getOne('TNW_Subscriptions');
                break;
        endswitch;
        return (bool)$isModuleInstall;
    }

    /**
     * @param null $module
     * @return bool
     */
    public function shallShow($module = null)
    {
        $startDate = $this->startDate($module);
        if (empty($startDate)) {
            return false;
        }
        $isInstalled = $this->resolver($module);
        if ($isInstalled) {
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
     * @param $user
     * @return bool
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmail($params, $user)
    {
        if (!empty($params['snooze_survey'])) {
            return false;
        }

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
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'surveyResult' => $this->getSurveyOptionsByType($params['type'])->getOptionText($surveyResult),
                'websites' => $this->websiteRepository->getList(),
                'rating' => __('%1 star(s)', $params['rating']),
                'comments' => $params['comments'],
                'module' => $params['module'],
                'moduleName' => $params['moduleName'],
            ])
            ->setFrom([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ])
            ->addTo('marketing@powersync.biz', 'Marketing PowerSync')
            ->setReplyTo($user->getEmail(), $user->getName())
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
     * @param $user \Magento\User\Model\User
     * @return $this
     * @throws LocalizedException
     */
    public function processAnswer($params, $user)
    {
        $timestamp = $this->getTimestampByRequest($params);

        $this->sendEmail($params, $user);

        $this->setStartDate($params['module'], $timestamp);

        // clear the block html cache
        $this->cacheTypeList->cleanType('config');
        $this->_eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => 'config']);

        return $this;
    }
}
