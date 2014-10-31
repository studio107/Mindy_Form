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
 * @date 08/05/14.05.2014 17:13
 */

namespace Mindy\Form\Fields;


use Mindy\Utils\RenderTrait;

class MarkdownField extends TextAreaField
{
    use RenderTrait;

    public $html = [
        'rows' => 10
    ];

    public function render()
    {
        return self::renderInternal(__DIR__ . '/markdown_template.php', [
            'this' => $this,
        ]);
    }
}
