<?php declare(strict_types=1);
/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\Marketing\Model\Config\Source\Survey;

use TNW\Marketing\Model\Config\Source\Survey\Options\Base;

/**
 * Options
 */
class ExtendedOptions extends Base
{

    /** @var array */
    public $optionsDetails = [
        [
            'label' => 'I like everything this solution offers',
            'timemodifier' => '+6 month',
        ],
        [
            'label' => 'Not bad, but it missed a few features',
            'timemodifier' => '+3 month',
        ],
        [
            'label' => 'Not bad, but I could use some help',
            'timemodifier' => '+2 week',
        ],
        [
            'label' => 'I just cannot get it to work right',
            'timemodifier' => '+2 week',
        ]
    ];
}
