<?php
use Mindy\Form;
use Mindy\Form\BaseForm;
use Mindy\Form\Fields\CharField;
use Mindy\Form\ModelForm;
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
 * @date 17/04/14.04.2014 18:15
 */

class FormTest extends TestCase
{
    public function setUp()
    {
        BaseForm::$ids = [];
    }

    public function testInit()
    {
        $form = new ModelForm;
        $this->assertInstanceOf('Mindy\Form\ModelForm', $form);
        $this->assertEquals([], $form->getFields());
    }

    public function testTemplates()
    {
        $form = new TemplateForm();
        $this->assertEquals('block.twig', $form->asBlock());
        $this->assertEquals('table.twig', $form->asTable());
        $this->assertEquals('custom.twig', $form->asCustom());
    }

    public function testRenderTemplates()
    {
        $form = new RenderTemplateForm();
        $form->addTemplate('block', 'block');
        $this->assertEquals('block', $form->asBlock());
    }

    /**
     * @expectedException Exception
     */
    public function testRenderUnknown()
    {
        $form = new RenderTemplateForm();
        $this->assertEquals('foo', $form->asFoo());
    }

    public function testCountFields()
    {
        $form = new SimpleForm();
        $this->assertEquals(2, count($form));
    }

    public function testIterateFields()
    {
        $form = new SimpleForm();
        foreach($form as $field) {
            $this->assertInstanceOf('\Mindy\Form\Fields\Field', $field);
        }
    }

    public function testArrayAccessFields()
    {
        $form = new SimpleForm();
        $this->assertInstanceOf('\Mindy\Form\Fields\Field', $form["name"]);
        $this->assertInstanceOf('\Mindy\Form\Fields\Field', $form["email"]);
    }

    public function testGetId()
    {
        $form = new SimpleForm();
        $this->assertEquals('SimpleForm_0', $form->getId());
    }

    public function testFieldInit()
    {
        $form = new SimpleForm();
        $field = new CharField([
            'form' => $form,
            'hint' => 'hint',
            'label' => 'foo',
            'value' => '123'
        ]);

        $this->assertEquals('hint', $field->hint);
        $this->assertEquals('foo', $field->label);

        $this->assertEquals('123', $field->value);
        $this->assertEquals('123', $field->getValue());
    }

    public function testFieldRender()
    {
        $form = new SimpleForm();
        $field = new CharField([
            'form' => $form,
            'name' => 'bar',
            'hint' => 'hint',
            'label' => 'foo',
            'value' => '123'
        ]);

        $this->assertEquals('<p class="form-hint-text">hint</p>', $field->renderHint());
        $this->assertEquals('<label for="SimpleForm_0_bar">foo</label>', $field->renderLabel());
    }

    public function testValidation()
    {
        $form = new ValidationForm();
        $this->assertFalse($form->isValid());
        $form->setData([
            'name' => '123',
            'email' => '123@123.com'
        ]);
        $this->assertTrue($form->isValid());
    }
}
