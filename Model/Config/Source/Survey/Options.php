<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\Marketing\Model\Config\Source\Survey;

use TNW\Marketing\Model\Config\Source\Survey\Options\Base;

/**
 * Options
 */
class Options extends Base
{
    /** @var array */
    public $optionsDetails = [
        ['label' => 'Request an Upgrade'],
        [
            'label' => 'I am still evaluating',
            'timemodifier' => '+7 day',
        ],
        ['label' => 'I am happy with the FREE version'],
        ['label' => 'I am a system integrator and just testing this out']
    ];

}
