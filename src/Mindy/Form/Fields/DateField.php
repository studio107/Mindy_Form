<?php

namespace Mindy\Form\Fields;

use Mindy\Helper\JavaScript;
use Mindy\Helper\JavaScriptExpression;

/**
 * Class DateField
 * @package Mindy\Form
 */
class DateField extends CharField
{
    public $options = [
        'showTime' => false,
        'showSeconds' => false,
        'use24hour' => true,
        'incrementHourBy' => 1,
        'incrementMinuteBy' => 1,
        'incrementSecondBy' => 1
    ];

    public function render()
    {
        $out = parent::render();
        $jsOptions = JavaScript::encode(array_merge($this->options, [
            'field' => new JavaScriptExpression('document.getElementById("' . $this->getHtmlId() . '")')
        ]));
        $js = "<script type='text/javascript'>new Pikaday($jsOptions);</script>";
        return $out . $js;
    }
}
