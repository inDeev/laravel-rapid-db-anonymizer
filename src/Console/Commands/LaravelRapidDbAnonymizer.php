<?php

namespace Indeev\LaravelRapidDbAnonymizer\Console\Commands;

use Faker\Factory;
use Faker\Generator;
use Carbon\CarbonInterval;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class LaravelRapidDbAnonymizer extends Command
{
    protected $signature = 'db:anonymize
                            {--M|model=allRelated : Model to anonymize}
                            {--C|columns=allRelated : Specified columns to anonymize (separated by comma)}';

    protected $description = 'Rapidly anonymize huge amount of data';

    protected Generator $faker;

    protected int $chunkSize;

    protected ?array $columns;

    public function __construct()
    {
        parent::__construct();
        CarbonInterval::setLocale('en');
        $this->faker = Factory::create(config('laravel-rapid-db-anonymizer.faker.locale'));
        $this->chunkSize = config('laravel-rapid-db-anonymizer.anonymizer.chunk_size');
        $this->columns = [];
    }

    public function handle()
    {
        $anonymizationStart = microtime(true);

        $model = $this->option('model');
        $columns = $this->option('columns');
        if ($columns) $this->columns = explode(',', $columns);

        if ($model === 'allRelated') {
            if (in_array(config('app.env'), config('laravel-rapid-db-anonymizer.anonymizer.forbidden_environments'))) {
                $this->error('It is forbidden to run this command on ' . config('app.env') . ' environment');
                return 0;
            }

            $modelFiles = array_diff(scandir(base_path(config('laravel-rapid-db-anonymizer.anonymizer.model_dir'))), ['..', '.']);
            $classes = array_map(fn($className) => config('laravel-rapid-db-anonymizer.anonymizer.model_namespace') . pathinfo($className, PATHINFO_FILENAME), $modelFiles);
            $anonymizableClasses = array_filter($classes, fn($class) => in_array(\Indeev\LaravelRapidDbAnonymizer\Anonymizable::class, class_uses($class), true));
        } else {
            if (in_array(\Indeev\LaravelRapidDbAnonymizer\Anonymizable::class, class_uses($model))) {
                $anonymizableClasses = [$model];
            } else {
                $this->error('Selected model [' . $model . '] doesn\'t have the Anonymizable trait defined.');
                return 0;
            }
        }

        $this->warn('Anonymizing database...');

        foreach ($anonymizableClasses as $anonymizableClass) {
            $this->anonymizeTable($anonymizableClass);
        }
        $this->warn('Anonymization done in ' . CarbonInterval::seconds(microtime(true) - $anonymizationStart)->cascade()->forHumans(['parts' => 3, 'short' => true]));
    }

    private function anonymizeTable(string $modelClass): void
    {
        $start = microtime(true);
        $model = new $modelClass();
        $tableName = $model->getTable();
        $primaryKey = $model->getKeyName();
        $anonymizable = $model::getAnonymizable();
        if ($anonymizable === []) {
            $this->error('ANONYMIZABLE constant is empty or not defined in model ' . $modelClass);
            return;
        }

        if ($anonymizable === ['truncate']) {
            $this->info("Truncating table {$tableName}");
            DB::table($tableName)->truncate();
        } else {
            $this->info("Anonymizing {$tableName} table");
            $progressBar = $this->output->createProgressBar($model->all()->count());
            $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%% | Remaining: %remaining:6s%');
            $model->chunk($this->chunkSize, function (Collection $chunkItems) use (&$progressBar, $tableName, $primaryKey, $anonymizable) {
                $chunkItemsIds = $chunkItems->pluck($primaryKey, $primaryKey)->toArray();
                try {
                    $casesString = $this->prepareSqlCasesString($primaryKey, $anonymizable, $chunkItemsIds);
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                    return;
                }
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

    /**
     * @throws \Exception
     */
    private function prepareSqlCasesString(string $primaryKey, array $anonymizable, array $chunkItemsIds): string
    {
        $casesArray = [];
        foreach ($anonymizable as $columnName => $config) {
            if ($this->columns !== ['allRelated'] && !in_array($columnName, $this->columns)) continue;
            $anonymizeNull = $config['anonymizeNull'] ?? false;
            if (array_key_exists('faker', $config)) {
                $provider = $config['faker']['provider'] ?? null;
                if (!$provider) throw new \Exception("Faker's provider is not defined for {$columnName} column");
                $params = $config['faker']['params'] ?? null;
                $updateArray = $params && count($params) > 0
                    ? array_map(fn() => call_user_func_array([$this->faker, $provider], $params), $chunkItemsIds)
                    : array_map(fn() => call_user_func([$this->faker, $provider]), $chunkItemsIds);
                $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $updateArray, $anonymizeNull);
            } else if (array_key_exists('setTo', $config)) {
                $setTo = $config['setTo'];
                if ($setTo === null) {
                    $casesArray[] = "`{$columnName}` = NULL";
                } else {
                    $updateArray = array_map(fn() => $setTo, $chunkItemsIds);
                    $casesArray[] = $this->generateCaseString($primaryKey, $columnName, $updateArray, $anonymizeNull);
                }
            }
        }
        return implode(',', $casesArray);
    }

    private function generateCaseString(string $primaryKey, string $columnName, array $updateArray, bool $anonymizeNull): string
    {
        $caseString =  "`{$columnName}` = CASE ";
        $whensArray = array_map(function($id) use ($primaryKey, $columnName, $updateArray, $anonymizeNull) {
             $whenString = "WHEN `{$primaryKey}` = {$id}";
             $whenString .= ($anonymizeNull ? " " : " AND `{$columnName}` IS NOT NULL ");
             $whenString .= "THEN '";
             $whenString .= is_array($updateArray[$id]) ? json_encode($updateArray[$id]) : $updateArray[$id];
             $whenString .= "'";
             return $whenString;
        }, array_keys($updateArray));
        $caseString .= implode(' ', $whensArray);
        $caseString .= ' END';
        return $caseString;
    }
}
