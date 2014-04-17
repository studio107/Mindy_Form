<?php
use Mindy\Form;
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
        $template = <<<TPL
<section class="form-row">
    <p><label for="id_{name}">{label}</label></p>
    <p>{field}</p>
    <p>{help_text}</p>
    <p>{error}</p>
</section>
TPL;

        $form->addTemplate('block', $template);
        $this->assertEquals('123', $form->asBlock());
    }

//    public function testRender()
//    {
//        $form = new SimpleForm();
//        $out = <<<OUT
//<input type="text" name="name" id="id_name" />
//OUT;
//
//        $this->assertEquals($out, $form->render());
//    }
}
