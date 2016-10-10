<?php

namespace Mindy\Form\Fields;

/**
 * Class ShortUrlField
 * @package Mindy\Form
 */
class SlugField extends TextField
{
    public function renderValue() : string
    {
        $slugs = explode('/', parent::getValue());
        return end($slugs);
    }
}
