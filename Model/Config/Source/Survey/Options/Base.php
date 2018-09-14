<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Marketing\Model\Config\Source\Survey\Options;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options
 */
abstract class Base implements OptionSourceInterface
{
    /** @var array */
    public $optionsDetails = [];

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {

        $options = [];
        foreach ($this->optionsDetails as $optionDetails) {
            $options[] = $optionDetails['label'];
        }
        return $options;
    }
    /**
     * Retrieve all options
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Retrieve option text
     *
     * @param int $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * @param $optionId
     * @return null
     */
    public function getOptionTimeModifier($optionId)
    {
        $timeModifier = isset($this->optionsDetails[$optionId]['timemodifier'])? $this->optionsDetails[$optionId]['timemodifier']: null;

        return $timeModifier;
    }
}
