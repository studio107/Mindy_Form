<?php

namespace Mindy\Form\Fields;

/**
 * Class DateTimeField
 * @package Mindy\Form
 */
class DateTimeField extends DateField
{
    public $options = [
        'showTime' => true,
        'showSeconds' => false,
        'use24hour' => true,
        'incrementHourBy' => 1,
        'incrementMinuteBy' => 1,
        'incrementSecondBy' => 1
    ];

    public function render()
    {
        return parent::render();
    }
}
