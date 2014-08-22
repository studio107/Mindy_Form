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
        $existInlines = $managed->getExistInlines();

        $this->assertInstanceOf(UserForm::className(), $managed->getForm());
        $this->assertEquals(1, count($existInlines['CustomerInlineForm']));

        foreach ($managed->getExistInlines() as $name => $inlines) {
            foreach ($inlines as $inline) {
                $this->assertEquals(1, $inline->extra);
            }
        }

        $mainForm = "<label for='UserForm_0_name'>Name</label><input type='text' value='' id='UserForm_0_name' name='name'/><input type='hidden' value='' name='customer' /><label for='UserForm_0_customer'>Customer</label><select id='UserForm_0_customer' name='customer[]'  multiple='multiple'></select>";
        $inlineForms = "<h1>CustomerInlineForm</h1><label for='CustomerInlineForm_0_address'>Address</label><input type='text' value='' id='CustomerInlineForm_0_address' name='CustomerInlineForm[CustomerInlineForm_0][address]'/><input type='hidden' value='' name='to_be_deleted' /><input type='checkbox' id='CustomerInlineForm_0_to_be_deleted' name='CustomerInlineForm[CustomerInlineForm_0][to_be_deleted]' disabled='disabled'/><label for='CustomerInlineForm_0_to_be_deleted'>Delete</label>";
        $this->assertEquals($mainForm . $inlineForms, $managed->asUl());
    }

    public function testManagedFormInstanceInit()
    {
        $user = User::objects()->getOrCreate(['name' => 'example']);
        $managed = new AdminForm([
            'instance' => $user
        ]);

        $mainForm = "<label for='UserForm_0_name'>Name</label><input type='text' value='example' id='UserForm_0_name' name='name'/>";
        $inlineForms = "<h1>CustomerInlineForm</h1><label for='CustomerInlineForm_0_address'>Address</label><input type='text' value='' id='CustomerInlineForm_0_address' name='CustomerInlineForm[CustomerInlineForm_0][address]'/><input type='hidden' value='' name='to_be_deleted' /><input type='checkbox' id='CustomerInlineForm_0_to_be_deleted' name='CustomerInlineForm[CustomerInlineForm_0][to_be_deleted]'disabled/><label for='CustomerInlineForm_0_to_be_deleted'>Delete</label>";
        $this->assertEquals($mainForm . $inlineForms, $managed->asUl());
    }

    public function testManagedFormInstanceSave()
    {
        $user = User::objects()->getOrCreate(['name' => 'example']);
        $managed = new AdminForm([
            'instance' => $user
        ]);

        $mainForm = "<label for='UserForm_0_name'>Name</label><input type='text' value='example' id='UserForm_0_name' name='name'/>";
        $inlineForms = "<h1>CustomerInlineForm</h1><label for='CustomerInlineForm_0_address'>Address</label><input type='text' value='' id='CustomerInlineForm_0_address' name='CustomerInlineForm[CustomerInlineForm_0][address]'/><input type='hidden' value='' name='to_be_deleted' /><input type='checkbox' id='CustomerInlineForm_0_to_be_deleted' name='CustomerInlineForm[CustomerInlineForm_0][to_be_deleted]'disabled/><label for='CustomerInlineForm_0_to_be_deleted'>Delete</label>";
        $this->assertEquals($mainForm . $inlineForms, $managed->asUl());

        $managed->setData(['name' => 'oleg']);
        $this->assertTrue($managed->isValid());
        $managed->save();

        $user = User::objects()->filter(['pk' => 1])->get();
        $this->assertEquals('oleg', $user->name);

        $this->assertEquals(1, count($managed->getExistInlines()));
        list($save, $delete) = $managed->setData([
            'name' => 'oleg',

            'CustomerInlineForm' => [
                ['address' => "test1"],
                ['address' => 'test2'],
                ['address' => 'test3'],
            ]
        ]);
        $this->assertEquals(1, count($save));
        $this->assertEquals(1, count($managed->inlinesData));
        $this->assertTrue($managed->isValid());
        $managed->save();
        $this->assertEquals(1, Customer::objects()->count());

        $mainForm = "<label for='UserForm_0_name'>Name</label><input type='text' value='oleg' id='UserForm_0_name' name='name'/>";
        $inlineForms = "<h1>CustomerInlineForm</h1><label for='CustomerInlineForm_1_address'>Address</label><input type='text' value='test1' id='CustomerInlineForm_1_address' name='CustomerInlineForm[CustomerInlineForm_1][address]'/><input type='hidden' value='1' id='CustomerInlineForm_1_id' name='CustomerInlineForm[CustomerInlineForm_1][id]'/><input type='hidden' value='' name='to_be_deleted' /><input type='checkbox' id='CustomerInlineForm_1_to_be_deleted' name='CustomerInlineForm[CustomerInlineForm_1][to_be_deleted]'/><label for='CustomerInlineForm_1_to_be_deleted'>Delete</label>";
        $this->assertEquals($mainForm . $inlineForms, $managed->asUl());

        $customer = Customer::objects()->filter(['pk' => 1])->get();
        $this->assertEquals('test1', $customer->address);

        // Update inline
        list($save, $delete) = $managed->setData([
            'CustomerInlineForm' => [
                ['address' => "123123"],
            ]
        ]);
        $this->assertEquals(1, count($save));
        $this->assertEquals(0, count($delete));
        $this->assertEquals(1, count($managed->inlinesData));
        $this->assertTrue($managed->isValid());
        $managed->save();
        $this->assertEquals("123123", Customer::objects()->filter(['pk' => 1])->get()->address);

        // Delete inline
        list($save, $delete) = $managed->setData([
            'name' => 'oleg',

            'CustomerInlineForm' => [
                ['address' => "test1", InlineModelForm::DELETE_KEY => "on"],
            ]
        ]);
        $this->assertEquals(0, count($save));
        $this->assertEquals(1, count($delete));
        $this->assertEquals(1, count($managed->inlinesDelete));
        $this->assertTrue($managed->isValid());
        $managed->save();
        $this->assertEquals(0, Customer::objects()->count());
    }
}
