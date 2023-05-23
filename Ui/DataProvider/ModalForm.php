<?php
/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\Marketing\Ui\DataProvider;

class ModalForm extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    public function getData()
    {
        return [];
    }

    public function getMeta()
    {
        $title = $this->request->getFullActionName() === 'adminhtml_integration_index'
            ? __('Additional Integrations')
            : __('Additional Automations');
        return [
            'tnw_promo_automation_modal' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'options' => [
                                'title' => $title
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
