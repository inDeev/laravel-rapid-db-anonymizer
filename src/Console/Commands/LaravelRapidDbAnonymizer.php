<?php

namespace Indeev\LaravelRapidDbAnonymizer\Console\Commands;

use Faker\Factory;
use Carbon\Carbon;
use Faker\Generator;
use Carbon\CarbonInterval;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class LaravelRapidDbAnonymizer extends Command
{
    protected $signature = 'db:anonymize';

    protected $description = 'Rapidly anonymize huge amount of data';

    protected Generator $faker;

    protected int $chunkSize;

    public function __construct()
    {
        parent::__construct();
        CarbonInterval::setLocale('en');
        $this->faker = Factory::create(config('laravel-rapid-db-anonymizer.faker.locale'));
        $this->chunkSize = config('laravel-rapid-db-anonymizer.anonymizer.chunk_size');
    }

    public function handle()
    {
        $anonymizationStart = microtime(true);

        if (in_array(config('app.env'), config('laravel-rapid-db-anonymizer.anonymizer.forbidden_environments'))) {
            $this->error('It is forbidden to run this command on ' . config('app.env') . ' environment');
            return 0;
        }

        $modelFiles = array_diff(scandir(base_path(config('laravel-rapid-db-anonymizer.anonymizer.model_dir'))), array('..', '.'));
        $classes = array_map(fn($className) => config('laravel-rapid-db-anonymizer.anonymizer.model_namespace') . pathinfo($className, PATHINFO_FILENAME), $modelFiles);
        $anonymizableClasses = array_filter($classes, fn($class) => in_array(\Indeev\LaravelRapidDbAnonymizer\Anonymizable::class, class_uses($class), true));

        $this->warn('Anonymizing database...');

        foreach ($anonymizableClasses as $anonymizableClass) {
            $this->anonymizeTable($anonymizableClass);
        }
        $this->warn('Anonymization done in ' . CarbonInterval::seconds(microtime(true) - $anonymizationStart)->cascade()->forHumans(['parts' => 3, 'short' => true]));
    }

    // ANONYMIZE

    private function anonymizeTable(string $modelClass): void
    {
        $start = microtime(true);
        $model = new $modelClass();
        $tableName = $model->getTable();
        $primaryKey = $model->getPrimaryKeyName();
        $anonymizable = $model::getAnonymizable();

        if ($anonymizable === ['truncate']) {
            $this->info("Truncating table {$tableName}");
            DB::table($tableName)->truncate();
        } else {
            $this->info("Anonymizing {$tableName} table");
            $progressBar = $this->output->createProgressBar($model->all()->count());
            $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%% | Remaining: %remaining:6s%');
            $model->chunk($this->chunkSize, function (Collection $chunkItems) use (&$progressBar, $tableName, $primaryKey, $anonymizable) {
                $chunkItemsIds = $chunkItems->pluck($primaryKey, $primaryKey)->toArray();
                $casesString = $this->prepareSqlCasesString($primaryKey, $anonymizable, $chunkItemsIds);
                $chunkItemsIdsString = implode(',', $chunkItemsIds);
                try {
                    DB::unprepared("UPDATE `{$tableName}` SET {$casesString} WHERE `{$primaryKey}` IN ({$chunkItemsIdsString})");
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
                $progressBar->advance($chunkItems->count());
            });
            $progressBar->finish();
        }
        $this->info(' - Done in ' . CarbonInterval::seconds(microtime(true) - $start)->cascade()->forHumans(['parts' => 3, 'short' => true]));
    }

    // INTERNAL

    private function prepareSqlCasesString(string $primaryKey, array $columnAndFaker, array $chunkItemsIds): string
    {
        $casesArray = [];
        foreach ($columnAndFaker as $columnName => $faker) {
            if ($faker === 'null') {
                $casesArray[] = "`{$columnName}` = NULL";
            } else {
                $pieces = explode('|', $faker);
                if (count($pieces) > 1) {
                    $faker = array_shift($pieces);
                    $arguments = implode(', ', $pieces);
                    $fakedArray = array_map(fn() => $this->faker->{$faker}($arguments), $chunkItemsIds);
                } else {
                    $fakedArray = array_map(fn() => $this->faker->{$faker}, $chunkItemsIds);
                }
                $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
            }
//            if (str_starts_with($faker, 'number:')) {
//                $extra = strlen($faker) > strlen('number') ? str_replace('number:', '', $faker) : '####';
//                $faker = 'number';
//            } else if (str_starts_with($faker, 'text:')) {
//                $extra = strlen($faker) > strlen('text') ? str_replace('text:', '', $faker) : 'cenzurovano';
//                $faker = 'text';
//            }
//            switch ($faker) {
//                case 'fullName':
//                    $fakedArray = array_map(fn() => $this->faker->name, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'firstName':
//                    $fakedArray = array_map(fn() => $this->faker->firstName, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'lastName':
//                    $fakedArray = array_map(fn() => $this->faker->lastName, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'company':
//                    $fakedArray = array_map(fn() => $this->faker->company, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'date':
//                    $fakedArray = array_map(fn() => $this->faker->date, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'number':
//                    $fakedArray = array_map(fn() => $this->faker->numerify($extra), $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'email':
//                    $fakedArray = array_map(fn() => $this->faker->email, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'companyEmail':
//                    $fakedArray = array_map(fn() => $this->faker->companyEmail, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'address':
//                    $fakedArray = array_map(fn() => $this->faker->streetAddress . ' ' . $this->faker->numerify('### ##') . ' ' . $this->faker->city, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'streetAddress':
//                    $fakedArray = array_map(fn() => $this->faker->streetAddress, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'city':
//                    $fakedArray = array_map(fn() => $this->faker->city, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'word':
//                    $fakedArray = array_map(fn() => $this->faker->word, $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'words':
//                    $fakedArray = array_map(fn() => $this->faker->words(random_int(1, 5), true), $chunkItemsIds);
//                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $fakedArray);
//                    break;
//                case 'text':
//                    $casesArray[] = "`{$columnName}` = '{$extra}'";
//                    break;
//                case 'null':
//                    $casesArray[] = "`{$columnName}` = NULL";
//            }
        }
        return implode(',', $casesArray);
    }

    private function generateCaseString(string $primaryKey, string $columnName, array $fakedArray): string
    {
        return "`{$columnName}` = CASE " .
            implode(' ',
                array_map(fn($id) => "WHEN `{$primaryKey}` = {$id}  AND `{$columnName}` IS NOT NULL THEN \"{$fakedArray[$id]}\"", array_keys($fakedArray))
            ) . ' END';
    }
}
