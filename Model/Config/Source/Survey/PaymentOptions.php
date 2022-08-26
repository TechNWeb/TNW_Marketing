<?php declare(strict_types=1);
/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\Marketing\Model\Config\Source\Survey;

use TNW\Marketing\Model\Config\Source\Survey\Options\Base;

/**
 * PaymentOptions
 */
class PaymentOptions extends Base
{
    /** @var array */
    public $optionsDetails = [
        ['label' => 'I want to mPower my business'],
        [
            'label' => 'Contact me later',
            'timemodifier' => '+1 month',
        ],
        ['label' => 'I am happy with the FREE version'],
        ['label' => 'I am a system integrator and just testing this out']
    ];

}
