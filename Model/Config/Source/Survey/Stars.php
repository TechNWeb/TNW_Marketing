<?php declare(strict_types=1);
/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\Marketing\Model\Config\Source\Survey;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options
 */
class Stars implements OptionSourceInterface
{
    const POOR = 1;
    const FAIR = 2;
    const GOOD = 3;
    const EXCELLENT = 4;
    const WOW = 5;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Poor'),
                'value' => self::POOR,
            ],
            [
                'label' => __('Fair'),
                'value' => self::FAIR,
            ],
            [
                'label' => __('Good'),
                'value' => self::GOOD,
            ],
            [
                'label' => __('Excellent'),
                'value' => self::EXCELLENT,
            ],
            [
                'label' => __('WOW!!!'),
                'value' => self::WOW,
            ],
        ];
    }
}
