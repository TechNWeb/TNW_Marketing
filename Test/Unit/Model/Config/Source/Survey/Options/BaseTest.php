<?php
/**
 * Created by PhpStorm.
 * User: eermolaev
 * Date: 14.09.18
 * Time: 19:20
 */

namespace TNW\Marketing\Test\Unit\Model\Config\Source\Survey\Options;

use TNW\Marketing\Model\Config\Source\Survey\Options\Base;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class BaseTest
 * @package TNW\Marketing\Test\Unit\Model\Config\Source\Survey\Options
 */
class BaseTest extends TestCase
{

    /**
     * @var Base|MockObject
     */
    protected $optionObject;

    protected function setUp()
    {
        $this->optionObject = new Base();

        /** @var array */
        $optionsDetails = [
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

        $this->optionObject->setOptionsDetails($optionsDetails);

    }

    /**
     * @dataProvider dataProviderGetOptionTimeModifier
     *
     * @param array $optionsDetails
     * @param array $expectation
     */
    public function testGetOptionTimeModifier(array $optionsDetails, array $expectations)
    {
        $this->optionObject->setOptionsDetails($optionsDetails);

        foreach ($expectations as $index => $expectation) {
            $this->assertEquals($expectation, $this->optionObject->getOptionTimeModifier($index));
        }
    }

    /**
     * @return array
     */
    public function dataProviderGetOptionTimeModifier()
    {
        return [
            [ # case #1 : $optionsDetails + $expectations
                [[
                    'label' => 'I like everything this solution offers',
                    'timemodifier' => '+6 month',
                ],
                [
                    'label' => 'Not bad, but it missed a few features',
                ]],
                ['+6 month', null]
            ]

        ];
    }
}
