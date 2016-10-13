<?php

namespace Mindy\Form\Fields;

use Mindy\Form\Widget\FileWidget;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FileField
 * @package Mindy\Form
 */
class FileField extends Field
{
    /**
     * @var string
     */
    public $template = "<input type='file' id='{id}' name='{name}'{html}/>";
    /**
     * List of allowed file types
     * @var array|null
     */
    public $mimeTypes = [];
    /**
     * @var null|int maximum file size or null for unlimited. Default value 2 mb.
     */
    public $maxSize = '5M';
    /**
     * @var array
     */
    public $widget = [
        'class' => FileWidget::class
    ];

    /**
     * FileField constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->html['accept'] = implode('|', $this->mimeTypes);
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        if ($value === '__clean') {
            // Clean file field hack
            $value = null;
        }
        return parent::setValue($value);
    }

    /**
     * @return string
     */
    public function renderValue() : string
    {
        if (is_object($this->value)) {
            if ($this->value instanceof UploadedFile) {
                return $this->value->getClientFilename();
            }
            
            return '';
        }
        return parent::renderValue();
    }
}
