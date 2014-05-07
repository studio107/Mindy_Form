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
 * @date 17/04/14.04.2014 18:44
 */

namespace Mindy\Form\Renderer;

use Exception;

class PhpRenderer implements IFormRenderer
{
    public function render($template, array $data = [])
    {
        if(!is_file($template)) {
            throw new Exception("Template {$template} not found");
        }

        ob_start();
        extract($data);
        include($template);
        return ob_get_clean();
    }
}
