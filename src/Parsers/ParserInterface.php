<?php
namespace Almhdy\RenderEase\Parsers;

interface ParserInterface
{
    public function parse(string $content): string;
}
