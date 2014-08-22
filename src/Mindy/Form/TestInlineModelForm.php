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
 * @date 22/08/14.08.2014 19:54
 */

namespace Mindy\Form;


class TestInlineModelForm extends InlineModelForm
{
    public function renderTemplate($template, $data = [])
    {
        $data = array_merge($data, ['form' => $this]);
        ob_start();
        extract($data);
        include($template);
        return ob_get_clean();
    }
}
