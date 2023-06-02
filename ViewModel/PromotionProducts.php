<?php

namespace TNW\Marketing\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

class PromotionProducts implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array|int[]|string[]
     */
    private $moduleListArray;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ModuleList $moduleList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ModuleList $moduleList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->moduleListArray = $moduleList->getNames();
    }

    /**
     * Get marketing not installed text
     *
     * @param $name string
     * @return string
     */
    public function getNotInstalledText($name)
    {
        return !empty($this->getProduct($name)['not_installed_text'])
            ? $this->getProduct($name)['not_installed_text']
            : '';
    }

    /**
     * Get marketing product button text
     *
     * @param $name string
     * @return string
     */
    public function getButtonText($name)
    {
        return !empty($this->getProduct($name)['button_text']) ? $this->getProduct($name)['button_text'] : '';
    }

    /**
     * Should we render marketing item for current layout handle?
     *
     * @param $name string
     * @param $handles array
     * @return string
     */
    public function shouldRender($name, $handles)
    {
        $applicableRoutes = !empty($this->getProduct($name)['applicable_routes'])
            ? explode(',', $this->getProduct($name)['applicable_routes'])
            : [];
        foreach ($applicableRoutes as $applicableRoute) {
            if (in_array($applicableRoute, $handles)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get marketing product title
     *
     * @param $name string
     * @return string
     */
    public function getProductTitle($name)
    {
        return !empty($this->getProduct($name)['title']) ? $this->getProduct($name)['title'] : '';
    }

    /**
     * Get marketing product url
     *
     * @param $name string
     * @return string
     */
    public function getProductUrl($name)
    {
        return !empty($this->getProduct($name)['url']) ? $this->getProduct($name)['url'] : '';
    }

    /**
     * Get marketing product full description
     *
     * @param $name
     * @return string
     */
    public function getProductFullDescription($name)
    {
        return !empty($this->getProduct($name)['full_description']) ? $this->getProduct($name)['full_description'] : '';
    }

    /**
     * Get marketing product
     *
     * @param $name
     * @return array
     */
    public function getProduct($name)
    {
        return $this->scopeConfig->getValue('marketing/promotions/' . $name);
    }

    /**
     * Get module status by name
     *
     * @param $name
     * @return bool
     */
    public function isModuleInstalled($name)
    {
        return in_array($name, $this->moduleListArray);
    }
}
