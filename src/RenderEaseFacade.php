<?php
namespace Almhdy\RenderEase;

class RenderEaseFacade
{
    private static $instance;

    public static function getInstance(): RenderEase
    {
        if (self::$instance === null) {
            self::$instance = new RenderEase();
        }
        return self::$instance;
    }

    public static function __callStatic($method, $args)
    {
        $instance = self::getInstance();
        return $instance->$method(...$args);
    }
}
