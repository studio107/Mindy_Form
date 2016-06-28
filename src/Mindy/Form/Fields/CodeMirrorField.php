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
    public $defaultOptions = [
        'lineNumbers' => true,
        'mode' => ['name' => "jinja2", 'htmlMode' => true],
        'styleActiveLine' => true,
        'matchBrackets' => true,
        'theme' => 'material'
    ];

    public $options = [];

    public function render()
    {
        $out = parent::render();
        $jsOptions = JavaScript::encode(array_merge($this->defaultOptions, $this->options));
        $js = '<script type="text/javascript">
            var editor = CodeMirror.fromTextArea(document.getElementById("' . $this->getHtmlId() . '"), ' . $jsOptions . ');
        </script>';
        return $out . $js;
    }
}