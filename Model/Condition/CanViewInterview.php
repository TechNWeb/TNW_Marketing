<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\Marketing\Model\Condition;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use TNW\Marketing\Model\Config\Survey;

/**
 * Class CanViewInterview
 *
 * Dynamic validator for UI release notification, manage UI component visibility.
 * Return true if the logged in user has not seen the notification.
 */
class CanViewInterview implements VisibilityConditionInterface
{
    /**
     * Unique condition name.
     *
     * @var string
     */
    private static $conditionName = 'can_view_interview';

    /**
     * @var Survey
     */
    private $configSurvey;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * CanViewInterview constructor.
     *
     * @param Survey $configSurvey
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Survey $configSurvey,
        TimezoneInterface $timezone
    ) {
        $this->configSurvey = $configSurvey;
        $this->timezone = $timezone;
    }

    /**
     * Validate if survey popup can be shown and set the survey flag
     *
     * @inheritdoc
     */
    public function isVisible(array $arguments)
    {
        $module = $arguments['module'] ?: '';

        $startDate = $this->configSurvey->startDate($module);
        if (empty($startDate)) {
            return false;
        }

        $startTime = $this->timezone->date($startDate);
        return $this->timezone->date()->diff($startTime)->format('%r%a') <= 0;
    }

    /**
     * Get condition name
     *
     * @return string
     */
    public function getName()
    {
        return self::$conditionName;
    }
}
