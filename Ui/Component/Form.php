<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Marketing\Ui\Component;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use TNW\Marketing\Model\Config\Survey;

class Form extends \Magento\Ui\Component\Form
{
    /**
     * @var Survey
     */
    private $configSurvey;

    /**
     * Module name where UI component is located
     *
     * @var string
     */
    private $module;

    public function __construct(
        ContextInterface $context,
        FilterBuilder $filterBuilder,
        Survey $configSurvey,
        $components = [],
        array $data = [],
        $module = null
    ) {
        if (!$module) {
            $module = Survey::DEFAULT_MODULE;
        }

        parent::__construct($context, $filterBuilder, $components, $data);
        $this->configSurvey = $configSurvey;
        $this->module = $module;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (!$this->configSurvey->shallShow($this->module)) {
            return false;
        }

        return parent::render();
    }
}