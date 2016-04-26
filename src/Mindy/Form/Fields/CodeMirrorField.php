<?php
/**
 * User: max
 * Date: 04/04/16
 * Time: 20:57
 */

namespace Mindy\Form\Fields;

use Mindy\Helper\JavaScript;

class CodeMirrorField extends Field
{
    /**
     * @var array
     */
    public $options = [
        'lineNumbers' => true,
        'mode' => ['name' => "jinja2", 'htmlMode' => true],
        'styleActiveLine' => true,
        'matchBrackets' => true,
        'theme' => 'material'
    ];

    public function render()
    {
        $out = parent::render();
        $jsOptions = JavaScript::encode($this->options);
        $js = '<script type="text/javascript">
            var editor = CodeMirror.fromTextArea(document.getElementById("' . $this->getHtmlId() . '"), ' . $jsOptions . ');
        </script>';
        return $out . $js;
    }
}