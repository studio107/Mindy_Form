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
 * @date 23/04/14.04.2014 18:52
 */

namespace Mindy\Form\Renderer;


use Mindy\Utils\RenderTrait;
use Yii;

class MindyRenderer implements IFormRenderer
{
    use RenderTrait;

    public function render($view, array $data = [])
    {
        return $this->renderTemplate($view, $data);
    }
}
