<?php
use Mindy\Form\BaseForm;
use Mindy\Form\InlineModelForm;
use Mindy\Form\Renderer\PhpRenderer;

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
 * @date 07/05/14.05.2014 15:44
 */
class ManagedTest extends \Tests\DatabaseTestCase
{
    public function setUp()
    {
        $this->settings = require __DIR__ . '/../../config_local.php';
        $this->setConnection('mysql');

        $this->initModels([new User, new Customer]);

        BaseForm::setRenderer(new PhpRenderer());
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

        foreach($managed->getExistInlines() as $name => $inlines) {
            foreach($inlines as $inline) {
                $this->assertEquals(1, $inline->extra);
            }
        }

        $mainForm = "<label for='UserForm_0_name'>Name</label><input type='text' value='' id='UserForm_0_name' name='name'/>";
        $inlineForms = "<h1>CustomerInlineForm</h1><label for='CustomerInlineForm_0_address'>Address</label><input type='text' value='' id='CustomerInlineForm_0_address' name='CustomerInlineForm[CustomerInlineForm_0][address]'/>";
        $this->assertEquals($mainForm . $inlineForms, $managed->asUl());
    }

    public function testManagedFormInstanceInit()
    {
        $user = User::objects()->getOrCreate(['name' => 'example']);
        $managed = new AdminForm([
            'instance' => $user
        ]);

        $mainForm = "<label for='UserForm_0_name'>Name</label><input type='text' value='example' id='UserForm_0_name' name='name'/>";
        $inlineForms = "<h1>CustomerInlineForm</h1><label for='CustomerInlineForm_0_address'>Address</label><input type='text' value='' id='CustomerInlineForm_0_address' name='CustomerInlineForm[CustomerInlineForm_0][address]'/>";
        $this->assertEquals($mainForm . $inlineForms, $managed->asUl());
    }

    public function testManagedFormInstanceSave()
    {
        $user = User::objects()->getOrCreate(['name' => 'example']);
        $managed = new AdminForm([
            'instance' => $user
        ]);

        $mainForm = "<label for='UserForm_0_name'>Name</label><input type='text' value='example' id='UserForm_0_name' name='name'/>";
        $inlineForms = "<h1>CustomerInlineForm</h1><label for='CustomerInlineForm_0_address'>Address</label><input type='text' value='' id='CustomerInlineForm_0_address' name='CustomerInlineForm[CustomerInlineForm_0][address]'/>";
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

        $customer = Customer::objects()->filter(['pk' => 1])->get();
        $this->assertEquals('test1', $customer->address);

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
