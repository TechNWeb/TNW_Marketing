<?php
/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\Marketing\Block\Adminhtml\Grid;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class PromoButton implements ButtonProviderInterface
{

    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        return [
            'class' => 'icon-pagebuilder-widgets',
            'sort_order' => 20,
            'label' => '',
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'index = tnw_promo_automation_modal',
                                'actionName' => 'openModal'
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
