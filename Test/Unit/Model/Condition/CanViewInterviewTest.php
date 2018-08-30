<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Marketing\Test\Unit\Model\Condition;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use TNW\Marketing\Model\Condition\CanViewInterview;
use TNW\Marketing\Model\Config\Survey;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CanViewInterviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Survey|MockObject
     */
    private $configSurvey;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $timezone;

    /**
     * @var CanViewInterview
     */
    private $canViewInterview;

    protected function setUp()
    {
        $this->configSurvey = $this->getMockBuilder(Survey::class)
            ->disableOriginalConstructor()
            ->setMethods(['startDate'])
            ->getMock();

        $this->timezone = $this->createMock(TimezoneInterface::class);

        $this->canViewInterview = new CanViewInterview(
            $this->configSurvey,
            $this->timezone
        );
    }

    /**
     * @param string $currentDate
     * @param string $startDate
     * @param bool $expected
     *
     * @dataProvider dataProviderBuild
     */
    public function testIsVisible($currentDate, $startDate, $expected)
    {
        $this->configSurvey->method('startDate')
            ->willReturn('1532044800');

        $this->timezone->expects(static::at(0))
            ->method('date')
            ->with('1532044800')
            ->willReturn(date_create($startDate));

        $this->timezone->expects(static::at(1))
            ->method('date')
            ->willReturn(date_create($currentDate));

        self::assertEquals($expected, $this->canViewInterview->isVisible([]));
    }

    /**
     *
     */
    public function testIsVisibleDateNull()
    {
        $this->configSurvey->method('startDate')
            ->willReturn(null);

        self::assertFalse($this->canViewInterview->isVisible([]));
    }

    /**
     * @return array
     */
    public function dataProviderBuild()
    {
        return [
            ['2018-07-20', '2018-07-19', true],
            ['2018-07-20', '2018-07-20', true],
            ['2018-07-20', '2018-07-21', false],
        ];
    }
}
