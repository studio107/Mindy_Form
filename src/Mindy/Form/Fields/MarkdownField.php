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
 * @date 02/05/14.05.2014 16:00
 */

namespace Mindy\Form\Fields;


class MarkdownField extends TextAreaField
{
    public function getValue()
    {
        // use github
        $parser = new \cebe\markdown\GithubMarkdown();
        return $parser->parse($this->value);
    }
}
