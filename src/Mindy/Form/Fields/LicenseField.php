<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 01/09/14.09.2014 12:38
 */

namespace Mindy\Form\Fields;


class LicenseField extends CheckboxField
{
    public $htmlLabel = [];
    public $errorMessage = 'You must agree terms';

    public function init()
    {
        $this->validators[] = function ($value) {
            if (!$value) {
                return $this->errorMessage;
            }
            return true;
        };
        parent::init();
    }

    public function renderLabel()
    {
        if($this->label === false) {
            return '';
        }
        $label = $this->label ? $this->label : ucfirst($this->name);
        return strtr("<label for='{for}'{html}>{label}</label>", [
            '{for}' => $this->id,
            '{label}' => $label,
            '{html}' => $this->getHtmlLabelAttributes()
        ]);
    }

    public function getHtmlLabelAttributes()
    {
        if (is_array($this->htmlLabel)) {
            $html = '';
            foreach ($this->htmlLabel as $name => $value) {
                $html .= " $name='$value'";
            }
            return $html;
        } else {
            return $this->htmlLabel;
        }
    }
}
