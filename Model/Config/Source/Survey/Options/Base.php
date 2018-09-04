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
    public static $optionsDetails = [];

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
    public static function getOptionArray()
    {

        $options = [];
        foreach (self::$optionsDetails as $optionDetails) {
            $options[] = $optionDetails['label'];
        }
        return $options;
    }
    /**
     * Retrieve all options
     *
     * @return array
     */
    public static function getAllOption()
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
    public static function getAllOptions()
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
    public static function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * @param $optionId
     * @return null
     */
    public function getOptionTimeModifier($optionId)
    {
        $timeModifier = isset($optionsDetails[$optionId]['timemodifier'])? self::$optionsDetails[$optionId]['timemodifier']: null;

        return $timeModifier;
    }
}
