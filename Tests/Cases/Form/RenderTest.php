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
 * @date 21/04/14.04.2014 19:39
 */

use Mindy\Form\BaseForm;
use Mindy\Form\Renderer\PhpRenderer;
use Tests\TestCase;

class RenderTest extends TestCase
{
    public function setUp()
    {
        BaseForm::setRenderer(new PhpRenderer());
        BaseForm::$ids = [];
    }

    public function testRender()
    {
        $form = new RenderForm();
        $this->assertEquals(2, count($form));
        $this->assertEquals('123', $form->asBlock());
    }
}
