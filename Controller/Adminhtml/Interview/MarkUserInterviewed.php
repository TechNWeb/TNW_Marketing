<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Marketing\Controller\Adminhtml\Interview;

use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\TypeListInterface;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

use Psr\Log\LoggerInterface;
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
     * @var Survey
     */
    private $configSurvey;

    /**
     * MarkUserNotified constructor.
     *
     * @param Action\Context $context
     * @param TimezoneInterface $timezone
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepository $websiteRepository
     * @param TypeListInterface $cacheTypeList
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,



        Survey $configSurvey,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;

        $this->configSurvey = $configSurvey;
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
            $params = $this->_request->getParams();
            $this->configSurvey->processAnswer($params, $user);

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
        $this->_eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => 'config']);

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)
            ->setData($responseContent);
    }
}
