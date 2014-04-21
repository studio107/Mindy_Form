<?php
use Mindy\Form\BaseForm;
use Mindy\Form\Renderer\DebugRenderer;
use Tests\TestCase;

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
 * @date 21/04/14.04.2014 18:45
 */

class ModelFormTest extends TestCase
{
    public function setUp()
    {
        BaseForm::setRenderer(new DebugRenderer());
        BaseForm::$ids = [];
    }

    public function testModelForm()
    {
        $form = new DummyForm();

        $this->assertEquals(1, DummyModel::$count);
        $this->assertInstanceOf('DummyModel', $form->getInstance());

        $this->assertEquals(1, DummyModel::$count);
        $form->setData(["name" => "1"]);

        $this->assertEquals(1, DummyModel::$count);
        $this->assertEquals(["name" => "1"], $form->getInstance()->getData());

        $this->assertTrue($form->isValid());
        $form->setData([]);
        $this->assertFalse($form->isValid());
    }
}

