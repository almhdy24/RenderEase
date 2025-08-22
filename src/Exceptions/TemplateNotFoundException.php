<?php
namespace Almhdy\RenderEase\Exceptions;

use Exception;

class TemplateNotFoundException extends Exception
{
    public function __construct(string $template, string $path)
    {
        parent::__construct("Template not found: '{$template}' at path: '{$path}'");
    }
}
