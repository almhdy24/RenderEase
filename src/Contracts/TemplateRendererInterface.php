<?php
namespace Almhdy\RenderEase\Contracts;

interface TemplateRendererInterface
{
    public function set(string $key, mixed $value): void;
    public function setMultiple(array $vars): void;
    public function get(string $key): mixed;
    public function clear(): void;
    public function render(string $template): string;
    public function include(string $template, array $variables = []): string;
    public function setErrorTemplate(string $template): void;
    public function enableCaching(bool $enable = true): void;
    public function setCacheTime(int $seconds): void;
    public function clearCache(): void;
}