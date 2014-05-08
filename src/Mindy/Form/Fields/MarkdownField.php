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


class MarkdownField extends TextAreaField
{
    public function render()
    {
        $id = $this->getId();
        return <<<JS
<div id="$id"></div>
<script type="text/javascript">
var editor = new Pen(document.getElementById('$id'));
</script>
JS;
    }
}
