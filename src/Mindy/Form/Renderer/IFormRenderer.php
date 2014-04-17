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
 * @date 17/04/14.04.2014 18:43
 */

namespace Mindy\Form\Renderer;


interface IFormRenderer
{
    public function renderField($template, array $data = []);

    public function renderContainer($template, array $data = []);
}
