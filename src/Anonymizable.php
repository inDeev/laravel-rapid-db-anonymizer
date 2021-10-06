<?php

namespace Indeev\LaravelRapidDbAnonymizer;

trait Anonymizable
{
    protected static function getAnonymizable(): array
    {
        return self::ANONYMIZABLE;
    }
}
