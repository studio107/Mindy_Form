<?php
use Mindy\Form\BaseForm;
use Mindy\Form\TestForm;

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
 * @date 17/04/14.04.2014 18:49
 */

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
