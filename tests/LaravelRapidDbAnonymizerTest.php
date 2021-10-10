<?php

namespace Indeev\LaravelRapidDbAnonymizer\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Indeev\LaravelRapidDbAnonymizer\Tests\Support\TestSupport;
use Indeev\LaravelRapidDbAnonymizer\LaravelRapidDbAnonymizerServiceProvider;

class LaravelRapidDbAnonymizerTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            LaravelRapidDbAnonymizerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/Support/database.sqlite',
            'prefix'   => '',
        ]);

        include_once __DIR__ . '/../database/migrations/2021_10_09_091225_create_test_support_table.php';
    }

    protected function setUp(): void
    {
        parent::setUp();
        (new \CreateTestSupportTable)->down();
        (new \CreateTestSupportTable)->up();
    }

    /** Deal of these tests is not to test whole faker library, but to check a few representative faker's providers and special cases of Anonymizable */

    /** @test */
    public function it_can_anonymize_providers()
    {
        $testSupport = TestSupport::create();
        $this->artisan('db:anonymize', ['model' => TestSupport::class]);
        $testSupport->refresh();

        $modelTestingArray = ['a', 'b', 1, 2];

        // randomDigit
        $this->assertNotNull($testSupport->randomDigit);
        $this->assertIsNumeric($testSupport->randomDigit);
        $this->assertTrue($testSupport->randomDigit >= 0 && $testSupport->randomDigit < 10);
        // randomNumber
        $this->assertNotNull($testSupport->randomNumber);
        $this->assertIsNumeric($testSupport->randomNumber);
        $this->assertTrue($testSupport->randomNumber >= 10_000 && $testSupport->randomNumber < 1_000_000);
        // randomFloat
        $this->assertNotNull($testSupport->randomFloat);
        $this->assertIsFloat($testSupport->randomFloat);
        $this->assertTrue($testSupport->randomFloat >= 3 && $testSupport->randomFloat < 4);
        $stringValue = (string)($testSupport->randomFloat * 100_000);
        $floatValue = (float)(substr($stringValue, 0, 1) . '.' . substr($stringValue, 1));
        $this->assertEqualsWithDelta($floatValue, $testSupport->randomFloat, 0.00001);
        // numberBetween
        $this->assertNotNull($testSupport->numberBetween);
        $this->assertIsNumeric($testSupport->numberBetween);
        $this->assertTrue($testSupport->numberBetween >= 1_000 && $testSupport->numberBetween <= 2_000);
        // randomElements
        $this->assertNotNull($testSupport->randomElements);
        $this->assertTrue(is_array($testSupport->randomElements));
        $this->assertTrue(count($testSupport->randomElements) === 2);
        $this->assertTrue(!array_diff($testSupport->randomElements, $modelTestingArray));
        // randomElement
        $this->assertNotNull($testSupport->randomElement);
        $this->assertTrue(in_array($testSupport->randomElement, $modelTestingArray));
        // shuffle
        $this->assertNotNull($testSupport->shuffle);
        $this->assertTrue(is_array($testSupport->shuffle));
        $this->assertEqualsCanonicalizing($testSupport->shuffle, $modelTestingArray);
    }

    /** @test */
    public function it_can_ignore_null_values()
    {
        $testSupport = TestSupport::create();
        $this->artisan('db:anonymize', ['model' => TestSupport::class, 'columns' => 'ignoreNull']);
        $testSupport->refresh();
        // ignoreNull
        $this->assertNull($testSupport->ignoreNull);
    }

    /** @test */
    public function it_can_anonymize_using_set_to_value()
    {
        $testSupport = TestSupport::create();
        $this->artisan('db:anonymize', ['model' => TestSupport::class, 'columns' => 'exactValue']);
        $testSupport->refresh();
        // exactValue
        $this->assertSame($testSupport->exactValue, 'CONFIDENTIAL');
    }
}
