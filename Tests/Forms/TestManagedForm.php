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
 * @date 22/08/14.08.2014 19:45
 */

namespace Mindy\Form;


abstract class TestManagedForm extends ManagedForm
{
    public function renderTemplateInternal($view, array $data = [])
    {
        $data = array_merge($data, ['form' => $this->getForm()]);
        ob_start();
        extract($data);
        include($view);
        return ob_get_clean();
    }
}
