<?php
/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\Marketing\Plugin;

use Magento\Framework\View\LayoutInterface;
use Magento\Integration\Block\Adminhtml\Integration;
use TNW\Marketing\Block\Adminhtml\Grid\PromoButton;

class AddPromoButton
{
    /**
     * @var PromoButton
     */
    private $promoButtonProvider;

    /**
     * @param PromoButton $promoButtonProvider
     */
    public function __construct(PromoButton $promoButtonProvider)
    {
        $this->promoButtonProvider = $promoButtonProvider;
    }

    /**
     * @param Integration $subject
     * @param LayoutInterface $layout
     * @return LayoutInterface[]
     */
    public function beforeSetLayout(Integration $subject, LayoutInterface $layout)
    {
        $subject->addButton('tnw_grid_promo_button', $this->promoButtonProvider->getButtonData());
        return [$layout];
    }
}
