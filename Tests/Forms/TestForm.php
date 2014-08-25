<?php
/**
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 22/08/14.08.2014 18:27
 */

namespace Mindy\Form;

class TestForm extends BaseForm
{
    public function renderTemplate($view, array $data = [])
    {
        $data = array_merge($data, ['form' => $this]);
        ob_start();
        extract($data);
        include($view);
        return ob_get_clean();
    }
}
