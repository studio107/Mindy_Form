<?php
use Mindy\Form\Form;

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
 * @date 17/04/14.04.2014 18:30
 */

class TemplateForm extends Form
{
    public $templates = [
        'block' => 'block.twig',
        'table' => 'table.twig',
        'custom' => 'custom.twig'
    ];

    public function render($template)
    {
        return $template;
    }
}
