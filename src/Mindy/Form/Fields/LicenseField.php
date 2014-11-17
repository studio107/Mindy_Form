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

use Mindy\Utils\RenderTrait;

class LicenseField extends CheckboxField
{
    use RenderTrait;

    /**
     * @var array
     */
    public $htmlLabel = [];
    /**
     * @var string
     */
    public $errorMessage = 'You must agree terms';

    public $licenseTemplate = '';

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

    public function render()
    {
        if(!empty($this->licenseTemplate)) {
            $tpl = self::renderTemplate($this->licenseTemplate);
            return $tpl . parent::render();
        } else {
            return parent::render();
        }
    }
}
