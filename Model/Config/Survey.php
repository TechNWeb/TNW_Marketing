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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteRepository;

class Survey
{
    const START_DATE = '%s/survey/start_date';
    const SURVEY_EMAIL_TEMPLATE = '%s_survey_email_template';
    const DEFAULT_MODULE = 'tnw_marketing';
    const SNOOZE_TIME_MODIFIER = '+3 day';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigInterface
     */
    private $configResource;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WebsiteRepository
     */
    private $websiteRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

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
        $optionsObjects
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configResource = $configResource;
        $this->cacheTypeList = $cacheTypeList;
        $this->_eventManager = $eventManager;
        $this->timezone = $timezone;

        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;

        $this->optionsObjects = $optionsObjects;
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
     * @param null $module
     * @return bool
     */
    public function shallShow($module = null)
    {
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
    public function setStartDate($timestamp = null, $module = null)
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
     * @return $this
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmail($params, $user)
    {
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
            ->addTo('sales@powersync.biz', 'Sales PowerSync')
            ->setReplyTo($user->getEmail(), $user->getName())
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }

    /**
     * @param $params
     * @param $user \Magento\User\Model\User
     * @return $this
     * @throws LocalizedException
     */
    public function processAnswer($params, $user)
    {
        if (!empty($params['snooze_survey'])) {
            $timeModifier = self::SNOOZE_TIME_MODIFIER;
        } else {
            $timeModifier = $this->getSurveyOptionsByType($params['type'])->getOptionTimeModifier($params['survey_result']);
            $this->sendEmail($params, $user);
        }

        if (!is_null($timeModifier)) {
            $timestamp = $this->timezone->date()->modify($timeModifier)->getTimestamp();
        } else {
            $timestamp = null;
        }

        $this->setStartDate($timestamp, $params['module']);

        // clear the block html cache
        $this->cacheTypeList->cleanType('config');
        $this->_eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => 'config']);

        return $this;
    }
}
