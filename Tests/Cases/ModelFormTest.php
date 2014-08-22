<?php

namespace Mindy\Form\Tests;

use Mindy\Form\BaseForm;
use Mindy\Form\ModelForm;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Model;
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

class Example extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className()
            ]
        ];
    }
}

class ExampleForm extends ModelForm
{
    public function getModel()
    {
        return Example::className();
    }
}

class ModelFormTest extends \Tests\DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        BaseForm::$ids = [];
        $this->initModels([new Example]);
    }

    public function testForm()
    {
        // TODO
        $this->assertEquals(1, 1);
    }
}