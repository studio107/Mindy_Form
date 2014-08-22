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
 * @date 22/08/14.08.2014 18:15
 */

namespace Mindy\Form\Tests;

use Mindy\Form\Fields\CharField;
use Mindy\Form\TestForm;
use PHPUnit_Framework_TestCase;


class EmptyForm extends TestForm
{
}

class FirstForm extends TestForm
{
    public function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
                'required' => true
            ]
        ];
    }

    public function cleanName($value)
    {
        if(mb_strlen($value, 'utf-8') < 3) {
            $this->addError('name', '<3');
        } else {
            return '1' . $value;
        }
    }
}

class ExcludeForm extends TestForm
{
    public $exclude = ['first'];

    public function getFields()
    {
        return [
            'first' => [
                'class' => CharField::className(),
                'required' => true
            ],
            'last' => [
                'class' => CharField::className(),
                'required' => true
            ],
        ];
    }
}

class BaseFormTest extends PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $form = new EmptyForm();
        $this->assertEquals([], $form->getFields());
        $this->assertEquals('0', $form->render(__DIR__ . '/../templates/empty.php'));

        $this->assertTrue($form->isValid());
        $form->setAttributes([
            'foo' => 'bar'
        ]);
        $this->assertEquals([], $form->cleanedData);
    }

    public function testFirst()
    {
        $form = new FirstForm();
        $this->assertEquals(1, count($form->getFields()));
        $this->assertEquals(
            "<label for='FirstForm_0_name'>Name</label><input type='text' value='' id='FirstForm_0_name' name='name'/>",
            $form->render(__DIR__ . '/../templates/block.php'));
        $this->assertFalse($form->isValid());
        $this->assertEquals(
            "<label for='FirstForm_0_name'>Name</label><input type='text' value='' id='FirstForm_0_name' name='name'/><ul class='error'><li>\"name\" cannot be empty</li></ul>",
            $form->render(__DIR__ . '/../templates/block.php'));
        $form->setAttributes([
            'name' => 'name'
        ]);
        $this->assertTrue($form->isValid());
        $this->assertEquals([
            'name' => '1name'
        ], $form->cleanedData);
        $this->assertEquals(
            "<label for='FirstForm_0_name'>Name</label><input type='text' value='1name' id='FirstForm_0_name' name='name'/>",
            $form->render(__DIR__ . '/../templates/block.php'));

        $form->setAttributes([
            'name' => 'na'
        ]);
        $this->assertFalse($form->isValid());
        $this->assertEquals([
            'name' => 'na'
        ], $form->cleanedData);
        $this->assertEquals(
            "<label for='FirstForm_0_name'>Name</label><input type='text' value='na' id='FirstForm_0_name' name='name'/>",
            $form->render(__DIR__ . '/../templates/block.php'));

        $this->assertEquals('1', $form->render(__DIR__ . '/../templates/empty.php'));
        $form->setAttributes([]);

        $this->assertFalse($form->isValid());
        $this->assertEquals(
            "<label for='FirstForm_0_name'>Name</label><input type='text' value='' id='FirstForm_0_name' name='name'/>\"name\" cannot be empty",
            $form->render(__DIR__ . '/../templates/block_separate.php'));
    }

    public function testExclude()
    {
        $form = new ExcludeForm;
        $this->assertEquals(2, count($form->getFields()));
        $this->assertEquals(1, count($form->getRenderFields()));
    }
}
