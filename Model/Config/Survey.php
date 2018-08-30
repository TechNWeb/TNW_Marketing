<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Marketing\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Survey
{
    const START_DATE = 'tnw_marketing/survey/start_date';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CanViewNotification constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function startDate()
    {
        return $this->scopeConfig->getValue(self::START_DATE);
    }
}
