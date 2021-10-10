<?php

namespace Indeev\LaravelRapidDbAnonymizer;

trait Anonymizable
{
    protected static function getAnonymizable(): array
    {
        if (defined('static::ANONYMIZABLE')) {
            return self::ANONYMIZABLE;
        } else {
            return [];
        }
    }
}
