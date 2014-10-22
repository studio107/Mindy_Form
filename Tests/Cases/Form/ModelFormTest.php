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
 * @date 22/10/14.10.2014 16:34
 */

namespace Mindy\Form\Tests;

use Exception;
use Mindy\Form\ModelForm;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Model;
use Mindy\Orm\Sync;
use Mindy\Query\ConnectionManager;

class Game extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
                'required' => true
            ],
            'patches' => [
                'class' => HasManyField::className(),
                'modelClass' => Patch::className(),
                'editable' => false
            ],
        ];
    }
}

class Patch extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
                'required' => true
            ],
            'game' => [
                'class' => ForeignField::className(),
                'modelClass' => Game::className(),
                'relatedName' => 'patches'
            ],
            'file' => [
                'class' => FileField::className(),
            ]
        ];
    }
}

abstract class TestModelForm extends ModelForm
{
    public $templates = [
        'block' => '../../templates/block.php',
        'table' => '../../templates/table.php',
        'ul' => '../../templates/ul.php',
    ];

    public function getTemplateFromType($type)
    {
        if (array_key_exists($type, $this->templates)) {
            $template = $this->templates[$type];
        } else {
            throw new Exception("Template type {$type} not found");
        }
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim($template, DIRECTORY_SEPARATOR));
    }

    public function renderInternal($view, array $data = [])
    {
        ob_start();
        extract($data);
        include($view);
        return ob_get_clean();
    }
}

class GameForm extends TestModelForm
{
    public function getModel()
    {
        return new Game;
    }

    public function getInlines()
    {
        return [
            ['game' => PatchForm::className()],
        ];
    }
}

class PatchForm extends TestModelForm
{
    public function getModel()
    {
        return new Patch;
    }
}

class ModelFormTest extends TestCase
{
    public function setUp()
    {
        $db = new ConnectionManager([
            'databases' => [
                'default' => [
                    'class' => '\Mindy\Query\Connection',
                    'dsn' => 'mysql:host=localhost;dbname=tmp',
                    'username' => 'root',
                    'password' => '123456',
                    'charset' => 'utf8',
                ],
                'sqlite' => [
                    'class' => '\Mindy\Query\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ]
        ]);

        $sync = new Sync([new Game, new Patch]);
        $sync->create();
    }

    public function renew()
    {
        $this->tearDown();
        $this->setUp();
    }

    public function tearDown()
    {
        $sync = new Sync([new Game, new Patch]);
        $sync->delete();
    }

    public function testInit()
    {
        $this->assertEquals(0, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());
    }

    public function testModelForm()
    {
        $form = new GameForm();
        $this->assertEquals(1, count($form->getFieldsInit()));
        $this->assertEquals(implode("\n", [
            "<label for='GameForm_name'>Name</label>",
            "<input type='text' value='' id='GameForm_name' name='GameForm[name]'/>",
            "",
            "<ul class='error' id='GameForm_name_errors' style='display:none;'></ul>",
            "<h2>PatchForm</h2>",
            "<label for='GameForm_PatchForm_0_name'>Name</label>",
            "<input type='text' value='' id='GameForm_PatchForm_0_name' name='GameForm[PatchForm][0][name]'/>",
            "",
            "<ul class='error' id='GameForm_PatchForm_0_name_errors' style='display:none;'></ul>",
            "<input type='hidden' value='' name='GameForm[PatchForm][0][game]' />",
            "<label for='GameForm_PatchForm_0_game'>Game</label>",
            "<span class='select-holder'><select id='GameForm_PatchForm_0_game' name='GameForm[PatchForm][0][game]' ></select></span>",
            "",
            "<ul class='error' id='GameForm_PatchForm_0_game_errors' style='display:none;'></ul>",
            "<label for='GameForm_PatchForm_0_file'>File</label>",
            "<input type='file' id='GameForm_PatchForm_0_file' name='GameForm[PatchForm][0][file]'/>",
            "",
            "<ul class='error' id='GameForm_PatchForm_0_file_errors' style='display:none;'></ul>",
            "",
        ]), $form->asBlock());

        $form->setAttributes([
            'name' => 'test'
        ]);
        $this->assertEquals(['name' => 'test'], $form->getAttributes());
        $this->assertEquals(0, count($form->getInlinesCreate()));
        $this->assertEquals(0, count($form->getInlinesDelete()));
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->save());

        $instance = $form->getInstance();
        $this->assertEquals(1, $instance->pk);

        $this->renew();

        $form = new GameForm();
        $form->setAttributes([
            'name' => 'test',
            'PatchForm' => [
                ['name' => 'Summer update']
            ]
        ]);
        $this->assertEquals(['name' => 'test'], $form->getAttributes());
        $this->assertEquals(1, count($form->getInlinesCreate()));
        $this->assertEquals(0, count($form->getInlinesDelete()));
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->save());

        $instance = $form->getInstance();
        $this->assertEquals(1, $instance->pk);
    }

    public function testUpdateInline()
    {
        $model = Game::objects()->getOrCreate(['name' => 'foo']);
        $form = new GameForm(['instance' => $model]);
        $form->setAttributes([
            'name' => 'test',
            'PatchForm' => [
                ['name' => 'Winter update', '_pk' => 1]
            ]
        ]);
        $this->assertEquals(['name' => 'test'], $form->getAttributes());
        $this->assertEquals(1, count($form->getInlinesCreate()));
        $this->assertEquals(0, count($form->getInlinesDelete()));
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->save());

        $patch = Patch::objects()->get();
        $this->assertEquals('Winter update', $patch->name);

        $instance = $form->getInstance();
        $this->assertEquals(1, $instance->pk);

        $this->assertEquals(1, Game::objects()->count());
        $this->assertEquals(1, Patch::objects()->count());
        $this->assertEquals(1, $instance->patches->count());
    }

    public function testDeleteInline()
    {
        $model = Game::objects()->getOrCreate(['name' => 'foo']);
        $form = new GameForm(['instance' => $model]);
        $form->setAttributes([
            'name' => 'test',
            'PatchForm' => [
                ['name' => 'Winter update', '_delete' => 1]
            ]
        ]);
        $this->assertEquals(['name' => 'test'], $form->getAttributes());
        $this->assertEquals(0, count($form->getInlinesCreate()));
        $this->assertEquals(1, count($form->getInlinesDelete()));
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->save());

        $instance = $form->getInstance();
        $this->assertEquals(1, $instance->pk);

        $this->assertEquals(1, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());
        $this->assertEquals(0, $instance->patches->count());
    }
}
