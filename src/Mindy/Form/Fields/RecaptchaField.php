<?php

namespace Mindy\Form\Fields;

use Mindy\Validation\RecaptchaValidator;

/**
 * Class RecaptchaField
 * @package Mindy\Form
 */
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
