<?php

namespace Indeev\LaravelRapidDbAnonymizer\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Indeev\LaravelRapidDbAnonymizer\Anonymizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestSupport extends Model
{
    use HasFactory, Anonymizable;

    const ANONYMIZABLE = [
        'randomDigit' => [
            'faker' => ['provider' => 'randomDigit'],
            'anonymizeNull' => true,
        ],
        'randomNumber' => [
            'faker' => ['provider' => 'randomNumber', 'params' => [5, true]],
            'anonymizeNull' => true,
        ],
        'randomFloat' => [
            'faker' => ['provider' => 'randomFloat', 'params' => [5, 3, 4]],
            'anonymizeNull' => true,
        ],
        'numberBetween' => [
            'faker' => ['provider' => 'numberBetween', 'params' => [1000, 2000]],
            'anonymizeNull' => true,
        ],
        'randomElements' => [
            'faker' => ['provider' => 'randomElements', 'params' => [['a', 'b', 1, 2], 2]],
            'anonymizeNull' => true,
        ],
        'randomElement' => [
            'faker' => ['provider' => 'randomElement', 'params' => [['a', 'b', 1, 2]]],
            'anonymizeNull' => true,
        ],
        'shuffle' => [
            'faker' => ['provider' => 'shuffle', 'params' => [['a', 'b', 1, 2]]],
            'anonymizeNull' => true,
        ],
        'ignoreNull' => [
            'faker' => ['provider' => 'bothify', 'params' => ['Hello ##??']],
            'anonymizeNull' => false,
        ],
        'exactValue' => [
            'setTo' => 'CONFIDENTIAL',
            'anonymizeNull' => true,
        ],
    ];

    protected $table = 'test_support';
    protected $guarded = [];
    protected $casts = [
        'randomFloat' => 'float',
        'randomElements' => 'json',
        'shuffle' => 'json',
    ];

    protected static function newFactory()
    {
        return \Indeev\LaravelRapidDbAnonymizer\Factories\TestSupportFactory::new();
    }
}
