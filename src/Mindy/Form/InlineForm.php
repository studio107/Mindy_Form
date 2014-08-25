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
 * @date 06/05/14.05.2014 20:05
 */

namespace Mindy\Form;


abstract class InlineForm extends Form
{
    public $templates = [
        'inline' => 'core/form/inline.html',
    ];

    public $defaultTemplateType = 'inline';
}
