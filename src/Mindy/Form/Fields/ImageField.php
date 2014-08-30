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
 * @date 15/07/14.07.2014 12:52
 */

namespace Mindy\Form\Fields;


class ImageField extends FileField
{
    public $currentTemplate = '<p class="current-file-container">{label}:<br/><a class="current-file" href="{current}" target="_blank"><img src="{current}" alt="{current}" /></a></p>';
}
