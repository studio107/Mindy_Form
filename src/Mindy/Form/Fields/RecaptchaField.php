<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 17/12/14 15:00
 */

namespace Mindy\Form\Fields;

use Mindy\Validation\RecaptchaValidator;

class RecaptchaField extends CharField
{
    /**
     * @var string
     */
    public $apiUrl = "<script src='https://www.google.com/recaptcha/api.js'></script>";
    /**
     * @var string
     */
    public $template = "<div class='g-recaptcha' data-sitekey='{publicKey}'></div>";
    /**
     * @var string
     */
    public $publicKey;
    /**
     * @var string
     */
    public $secretKey;

    public function init()
    {
        $this->validators[] = new RecaptchaValidator($this->publicKey, $this->secretKey);
    }

    public function render()
    {
        return implode("\n", [
            $this->apiUrl,
            strtr($this->template, ["{publicKey}" => $this->publicKey]),
            $this->renderErrors()
        ]);
    }
}
