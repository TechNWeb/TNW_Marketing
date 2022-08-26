<?php declare(strict_types=1);
/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Marketing\Block\System\Config\Form\Extensions;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleList;

class Products extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'system/config/form/extensions/products.phtml';

    /**
     * Scope Config Interface
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Installed module list
     *
     * @var array|string[]
     */
    private $moduleListArray;

    /**
     * Products constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param ModuleList $moduleList
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        ModuleList $moduleList,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->moduleListArray = $moduleList->getNames();
        parent::__construct($context, $data);
    }

    /**
     * Render fieldset html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_decorateRowHtml($element, $this->toHtml());
    }

    /**
     * Get marketing products
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->scopeConfig->getValue('marketing/products');
    }

    /**
     * Get module status by name
     *
     * @param $name
     * @return bool
     */
    public function moduleIsInstalled($name)
    {
        return in_array($name, $this->moduleListArray);
    }
}
