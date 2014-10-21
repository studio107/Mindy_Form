<?php

namespace Mindy\Form\Tests;

use Mindy\Form\BaseForm;
use Mindy\Form\InlineModelForm;
use Mindy\Form\TestInlineModelForm;
use Mindy\Form\TestManagedForm;
use Mindy\Form\ModelForm;
use Mindy\Form\Fields\CharField as FormCharField;
use Mindy\Form\TestModelForm;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Model;

class User extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className()
            ],
        ];
    }
}

class Customer extends Model
{
    public static function getFields()
    {
        return [
            'user' => [
                'class' => ForeignField::className(),
                'modelClass' => User::className(),
                'relatedName' => 'customer'
            ],
            'address' => [
                'class' => CharField::className()
            ]
        ];
    }
}

class UserForm extends TestModelForm
{
    public function init()
    {
        parent::init();
        $this->templates = [
            'block' => __DIR__ . '/../Templates/block.php'
        ];
    }

    public function getFields()
    {
        return [
            'name' => ['class' => FormCharField::className()]
        ];
    }

    public function getModel()
    {
        return new User;
    }
}

class CustomerInlineForm extends TestInlineModelForm
{
    public $extra = 1;

    public $max = 1;

    public function init()
    {
        parent::init();
        $this->templates = [
            'inline' => __DIR__ . '/../Templates/inline.php'
        ];
    }

    public function getFields()
    {
        return [
            'address' => ['class' => FormCharField::className()]
        ];
    }

    public function getModel()
    {
        return new Customer;
    }
}

class CustomerExtraInlineForm extends CustomerInlineForm
{
    public $max = 5;
    public $extra = 3;
}

class AdminForm extends TestManagedForm
{
    public function init()
    {
        parent::init();
        $this->templates = [
            'ul' => __DIR__ . '/../Templates/managed.php',
        ];
    }

    /**
     * @return string form class
     */
    public function getFormClass()
    {
        return UserForm::className();
    }

    public function getInlines()
    {
        return [
            'user' => CustomerInlineForm::className()
        ];
    }
}

class AdminExtraForm extends AdminForm
{
    public function getInlines()
    {
        return [
            'user' => CustomerExtraInlineForm::className()
        ];
    }
}

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 07/05/14.05.2014 15:44
 */
class ManagedTest extends \Tests\DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->settings = require __DIR__ . '/../config_local.php';
        $this->initModels([new User, new Customer]);
        BaseForm::$ids = [];
    }

    public function tearDown()
    {
        $this->dropModels([new User, new Customer]);
    }

    public function testManagedFormInit()
    {
        $managed = new AdminForm();
        $existInlines = $managed->getInlinesInit();

        $this->assertInstanceOf(UserForm::className(), $managed->getForm());
        $this->assertEquals(0, count($managed->getInlinesExist(false)));
        $this->assertEquals(1, count($existInlines));

        foreach ($managed->getInlinesExist() as $name => $inlines) {
            foreach ($inlines as $inline) {
                $this->assertEquals(1, $inline->extra);
            }
        }

        $mainForm = "<label for='UserForm_0_name'>Name</label><input type='text' value='' id='UserForm_0_name' name='[UserForm_0][name]'/><input type='hidden' value='' name='[UserForm_0][customer]' /><label for='UserForm_0_customer'>Customer</label><select id='UserForm_0_customer' name='[UserForm_0][customer][]'  multiple='multiple'></select>";
        $inlineForms = "<h1>CustomerInlineForm</h1><label for='CustomerInlineForm_0_address'>Address</label><input type='text' value='' id='CustomerInlineForm_0_address' name='CustomerInlineForm[CustomerInlineForm_0][address]'/><input type='hidden' value='' name='CustomerInlineForm[CustomerInlineForm_0][to_be_deleted]' /><input type='checkbox' id='CustomerInlineForm_0_to_be_deleted' name='CustomerInlineForm[CustomerInlineForm_0][to_be_deleted]' disabled='disabled'/><label for='CustomerInlineForm_0_to_be_deleted'>Delete</label>";
        $this->assertEquals($mainForm . $inlineForms, $managed->asUl());
    }

    public function testManagedFormInstanceInit()
    {
        $user = User::objects()->getOrCreate(['name' => 'example']);
        $managed = new AdminForm([
            'instance' => $user
        ]);

        $mainForm = "<label for='UserForm_0_name'>Name</label><input type='text' value='example' id='UserForm_0_name' name='[UserForm_0][name]'/><input type='hidden' value='' name='[UserForm_0][customer]' /><label for='UserForm_0_customer'>Customer</label><select id='UserForm_0_customer' name='[UserForm_0][customer][]'  multiple='multiple'></select>";
        $inlineForms = "<h1>CustomerInlineForm</h1><label for='CustomerInlineForm_0_address'>Address</label><input type='text' value='' id='CustomerInlineForm_0_address' name='CustomerInlineForm[CustomerInlineForm_0][address]'/><input type='hidden' value='' name='CustomerInlineForm[CustomerInlineForm_0][to_be_deleted]' /><input type='checkbox' id='CustomerInlineForm_0_to_be_deleted' name='CustomerInlineForm[CustomerInlineForm_0][to_be_deleted]' disabled='disabled'/><label for='CustomerInlineForm_0_to_be_deleted'>Delete</label>";
        $this->assertEquals($mainForm . $inlineForms, $managed->asUl());
    }

    public function testManagedFormInstanceSave()
    {
        $this->assertEquals(0, Customer::objects()->count());
        $user = User::objects()->getOrCreate(['name' => 'example']);
        $this->assertEquals('example', $user->name);
        $this->assertFalse($user->getIsNewRecord());

        $m = new AdminForm(['instance' => $user]);
        list($save, $delete) = $m->setAttributes(['name' => 'oleg']);
        $this->assertTrue(empty($save));
        $this->assertTrue(empty($delete));

        $this->assertTrue($m->isValid());
        $this->assertTrue($m->save());

        $this->assertEquals('oleg', $m->getInstance()->name);
        $this->assertEquals('oleg', User::objects()->filter(['pk' => 1])->get()->name);

        $this->assertEquals(0, count($m->getInlinesExist(false)));
        $this->assertEquals(1, count($m->getInlinesInit()));

        list($save, $delete) = $m->setAttributes([
            'name' => 'oleg',

            'CustomerInlineForm' => [
                ['address' => "test1"],
                ['address' => 'test2'],
                ['address' => 'test3'],
            ]
        ]);
        $this->assertTrue(empty($delete));
        $this->assertFalse(empty($save));
        $this->assertEquals(1, count($save));
        $this->assertEquals(1, count($m->inlinesData));
        $this->assertTrue($m->isValid());
        $this->assertTrue($m->save());
        $this->assertEquals(1, Customer::objects()->count());
        $this->assertEquals('test1', Customer::objects()->filter(['pk' => 1])->get()->address);

        // Update inline
        list($save, $delete) = $m->setAttributes([
            'CustomerInlineForm' => [
                ['address' => "123123"],
            ]
        ]);
        $this->assertTrue(empty($delete));
        $this->assertFalse(empty($save));
        $this->assertEquals(1, count($save));
        $this->assertEquals(0, count($delete));
        $this->assertEquals(1, count($m->inlinesData));
        $this->assertTrue($m->isValid());
        $this->assertTrue($m->save());
        $this->assertEquals(1, Customer::objects()->count());
        $this->assertEquals("123123", $m->getInstance()->customer->filter(['pk' => 1])->get()->address);
        $this->assertEquals("123123", Customer::objects()->filter(['pk' => 1])->get()->address);

        // Delete inline
        list($save, $delete) = $m->setAttributes([
            'name' => 'oleg',
            'CustomerInlineForm' => [
                ['address' => "test1", InlineModelForm::DELETE_KEY => "on"],
            ]
        ]);
        $this->assertFalse(empty($delete));
        $this->assertTrue(empty($save));
        $this->assertEquals(0, count($save));
        $this->assertEquals(1, count($delete));
        $this->assertEquals(1, count($m->inlinesDelete));
        $this->assertTrue($m->isValid());
        $this->assertTrue($m->save());

        $this->assertEquals(0, Customer::objects()->count());
    }

    public function testManagedIncorrectData()
    {
        $user = User::objects()->getOrCreate(['name' => 'example']);
        $m = new AdminExtraForm(['instance' => $user]);
        list($save, $delete) = $m->setAttributes(['name' => 'oleg']);
        $this->assertTrue(empty($save));
        $this->assertTrue(empty($delete));

        $this->assertTrue($m->isValid());
        $this->assertTrue($m->save());

        // Delete inline
        list($save, $delete) = $m->setAttributes([
            'name' => 'oleg',
            'CustomerExtraInlineForm' => [
                ['address' => "test1", InlineModelForm::DELETE_KEY => ""],
                ['address' => "test2", InlineModelForm::DELETE_KEY => ""],
                ['address' => "test3", InlineModelForm::DELETE_KEY => ""],
            ]
        ]);
        $this->assertTrue(empty($delete));
        $this->assertFalse(empty($save));
        $this->assertEquals(3, count($save));
        $this->assertEquals(0, count($delete));
        $this->assertEquals(0, count($m->inlinesDelete));
        $this->assertTrue($m->isValid());
        $this->assertTrue($m->save());

        $this->assertEquals(3, Customer::objects()->count());
    }
}
