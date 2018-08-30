<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Marketing\Controller\Adminhtml\Interview;

use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteRepository;
use Psr\Log\LoggerInterface;
use TNW\Marketing\Model\Config\Source\Survey\Options;
use TNW\Marketing\Model\Config\Survey;

/**
 * Class MarkUserNotified
 */
class MarkUserInterviewed extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigInterface
     */
    private $configResource;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

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
     * @var Options
     */
    private $surveyOptions;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * MarkUserNotified constructor.
     *
     * @param Action\Context $context
     * @param ConfigInterface $configResource
     * @param TimezoneInterface $timezone
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepository $websiteRepository
     * @param Options $surveyOptions
     * @param TypeListInterface $cacheTypeList
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        ConfigInterface $configResource,
        TimezoneInterface $timezone,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        WebsiteRepository $websiteRepository,
        Options $surveyOptions,
        TypeListInterface $cacheTypeList,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->configResource = $configResource;
        $this->timezone = $timezone;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->surveyOptions = $surveyOptions;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Log information about the last shown advertisement
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\User\Model\User $user */
        $user = $this->_auth->getUser();

        try {
            switch (true) {
                case $this->_request->getParam('snooze_survey', false):
                    $timestamp = $this->timezone->date()->modify('+3 day')->getTimestamp();
                    break;

                case (int)$this->_request->getParam('survey_result') === Options::STILL_EVALUATING:
                    $timestamp = $this->timezone->date()->modify('+7 day')->getTimestamp();
                    break;

                case $this->_request->getParam('survey_result') > 0:
                    $timestamp = null;
                    $surveyResult = (int)$this->_request->getParam('survey_result');

                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier('tnw__marketing_survey_email_template')
                        ->setTemplateOptions(
                            [
                                'area' => Area::AREA_ADMINHTML,
                                'store' => $this->storeManager->getStore()->getId()
                            ]
                        )
                        ->setTemplateVars([
                            'name' => $user->getName(),
                            'email' => $user->getEmail(),
                            'surveyResult' => $this->surveyResultText($surveyResult),
                            'websites' => $this->websiteRepository->getList(),
                            'rating' => __('%1 star(s)', $this->_request->getParam('rating')),
                            'comments' => $this->_request->getParam('comments'),
                        ])
                        ->setFrom([
                            'name' => $user->getName(),
                            'email' => $user->getEmail(),
                        ])
                        ->addTo('sales@powersync.biz', 'Sales PowerSync')
                        ->setReplyTo($user->getEmail(), $user->getName())
                        ->getTransport();

                    $transport->sendMessage();
                    break;

                default:
                    throw new LocalizedException(__('Fill out the form before sending'));
                    break;
            }

            $this->configResource
                ->saveConfig(Survey::START_DATE, $timestamp, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

            // clear the block html cache
            $this->cacheTypeList->cleanType('config');
            $this->_eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => 'config']);

            $responseContent = [
                'success' => true,
                'error_message' => ''
            ];
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $responseContent = [
                'success' => false,
                'error_message' => __('It is impossible to log user action')
            ];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)
            ->setData($responseContent);
    }

    /**
     * @param int $surveyResult
     * @return string
     */
    private function surveyResultText($surveyResult)
    {
        foreach ($this->surveyOptions->toOptionArray() as $option) {
            if ($option['value'] != $surveyResult) {
                continue;
            }

            return $option['label'];
        }

        return '';
    }
}
