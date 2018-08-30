<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Marketing\Model\Config\Source\Survey;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options
 */
class Options implements OptionSourceInterface
{
    const REQUEST_UPGRADE = 1;
    const STILL_EVALUATING = 2;
    const HAPPY_FREE = 3;
    const SYSTEM_INTEGRATOR = 4;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Request an Upgrade'),
                'value' => self::REQUEST_UPGRADE,
            ],
            [
                'label' => __('I am still evaluating'),
                'value' => self::STILL_EVALUATING,
            ],
            [
                'label' => __('I am happy with the FREE version'),
                'value' => self::HAPPY_FREE,
            ],
            [
                'label' => __('I am a system integrator and just testing this out'),
                'value' => self::SYSTEM_INTEGRATOR,
            ],
        ];
    }
}
