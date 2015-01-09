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
 * @date 28/04/14.04.2014 11:59
 */

namespace Mindy\Form;

use Mindy\Utils\RenderTrait;

class Form extends BaseForm
{
    use RenderTrait;

    public function renderInternal($template, array $params)
    {
        return self::renderTemplate($template, $params);
    }
}
