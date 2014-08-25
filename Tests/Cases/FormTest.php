<?php

namespace Mindy\Form\Tests;

use Mindy\Form;
use Mindy\Form\BaseForm;
use Mindy\Form\Fields\CharField;
use Mindy\Form\Fields\EmailField;
use Mindy\Form\TestForm;
use Mindy\Form\Validator\EmailValidator;
use Mindy\Form\Validator\RequiredValidator;
use Tests\TestCase;

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 17/04/14.04.2014 18:15
 */

class TemplateForm extends TestForm
{
    public $templates = [
        'block' => 'block.twig',
        'table' => 'table.twig',
        'custom' => 'custom.twig'
    ];

    public function render($template, array $fields = [])
    {
        return $template;
    }
}

class SimpleForm extends TestForm
{
    public function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className()
            ],
            'email' => [
                'class' => EmailField::className()
            ],
        ];
    }
}

class ValidationForm extends BaseForm
{
    public function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
                'validators' => [
                    new RequiredValidator()
                ]
            ],
            'email' => [
                'class' => EmailField::className(),
                'validators' => [
                    new RequiredValidator(),
                    new EmailValidator()
                ]
            ],
        ];
    }
}

class RenderTemplateForm extends TestForm
{
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
        return $this;
    }

    public function addTemplate($name, $template)
    {
        $this->templates[$name] = $template;
        return $this;
    }
}

class FormTest extends TestCase
{
    public function setUp()
    {
        BaseForm::$ids = [];
    }

    public function testInit()
    {
        $form = new EmptyForm;
        $this->assertInstanceOf('\Mindy\Form\Tests\EmptyForm', $form);
        $this->assertEquals([], $form->getFields());
    }

    public function testTemplates()
    {
        $form = new TemplateForm;
        $this->assertEquals('block.twig', $form->asBlock());
        $this->assertEquals('table.twig', $form->asTable());
        $this->assertEquals('custom.twig', $form->asCustom());
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
        foreach ($form as $field) {
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
        $this->assertEquals("<label for='SimpleForm_0_bar'>foo</label>", $field->renderLabel());
    }

    public function testValidation()
    {
        $form = new ValidationForm();
        $this->assertFalse($form->isValid());
        $form->setAttributes([
            'name' => '123',
            'email' => '123@123.com'
        ]);
        $this->assertTrue($form->isValid());
    }

    public function testRenderSelectedFields()
    {
        $form = new SimpleForm();
        $form->setRenderFields(["name"]);
        $this->assertEquals(1, count($form->getRenderFields()));

        $form->setRenderFields([]);
        $this->assertEquals(2, count($form->getRenderFields()));
    }

    public function testValidationCustom()
    {
        $form = new ValidationForm();
        $this->assertFalse($form->isValid());

        $this->assertTrue($form["name"]->hasErrors());
        $this->assertEquals(1, count($form->getErrors("name")));

        $this->assertEquals(1, count($form->getField("name")->getErrors()));

        $this->assertFalse($form->getField("name")->isValid());
        $this->assertEquals(1, count($form->getField("name")->getErrors()));

        $this->assertEquals(
            "<label for='ValidationForm_0_name'>Name</label><input type='text' value='' id='ValidationForm_0_name' name='[ValidationForm_0][name]'/><ul class='error'><li>\"name\" cannot be empty</li></ul>",
            $form->getField("name")->render()
        );
        $this->assertEquals(
            "<label for='ValidationForm_0_name'>Name</label><input type='text' value='' id='ValidationForm_0_name' name='[ValidationForm_0][name]'/><ul class='error'><li>\"name\" cannot be empty</li></ul>",
            $form["name"]->render()
        );
        $this->assertEquals(
            "<label for='ValidationForm_0_email'>Email</label><input type='text' id='ValidationForm_0_email' name='[ValidationForm_0][email]'/><ul class='error'><li>\"email\" cannot be empty</li><li>is not a valid email address</li></ul>",
            $form->getField("email")->render()
        );
    }
}
