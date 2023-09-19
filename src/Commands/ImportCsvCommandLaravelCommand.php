<?php

namespace Kgalanos\ImportCsvCommandLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use kgalanos\conversion\File\ToCodepage as ConvertFileClass;
use Kgalanos\ImportCsvCommandLaravel\ImportCsvCommandLaravelInterface;
use League\Csv\Reader;

class ImportCsvCommandLaravelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:csv {--truncated} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import CSV data to a model';

    protected $key = 'import-csv-command';

    public function handle(): int
    {
        //Read option $model from command line
        $model = $this->option('model');

        try {
            $csv = $this->checkModel($model);
        } catch (\Exception $exception) {
            exit($exception->getMessage());
        }
        /*
         *
         */
        try {
            $check_is_utf8 = new ConvertFileClass($csv);
        } catch (\Exception $exception) {
            exit($exception->getMessage());
        }
        if (! $check_is_utf8->check()) {
            echo "Convert to UTF-8 $csv\n";
            $check_is_utf8->convert();
        }

        /*
         *
         */
        $CsvReader = Reader::createFromPath($csv);
        echo "Start import $csv\n";
        echo str_repeat('=', strlen("Start impost $csv"));
        echo "\n";

        $headers = config($this->key)[$model]['headers'];
        $records = $CsvReader->getRecords();
        $bar = $this->output->createProgressBar();
        $bar->start($CsvReader->count());

        $modelEloquent = config($this->key)[$model]['model'];
        if (array_key_exists('foreigns', config($this->key)[$model])) {
            $foreignsModels = config($this->key)[$model]['foreigns'];
        } else {
            $foreignsModels = [];
        }
        if ($this->option('truncated')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $modelEloquent::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        /*
         *
         */
        $problem_rec = 0;
        $folderToSave = 'data'.DIRECTORY_SEPARATOR.'csv'.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR;
        $stream_errors = fopen($folderToSave.$model.'.err.txt', 'w');

        foreach ($records as $record) {
            $record = array_combine($headers, $record);
            //            dd($record);
            $record['phone'] = null;
            //                        dd($record);

            /** @var ImportCsvCommandLaravelInterface|Model $foreignModel */
            foreach ($foreignsModels as $foreignModel) {
                /** @var array $foreignData */
                $foreignData = [];
                foreach ($foreignModel::getCsvHeaders() as $foreignElement) {
                    //                    dd($record[$foreignElement]);
                    $foreignData[$foreignElement] = $record[$foreignElement];
                }
                $foreignModel::updateOrCreate($foreignData);
            }
            //            dd($record);
            try {
                $data_rec = $modelEloquent::create($record);
                //                $user = User::findOrFail($record['KODPRA'],'username')->get()->first();
                //                $data_rec->user()->associate($user);
            } catch (QueryException $queryException) {
                $problem_rec++;
                fwrite($stream_errors, $queryException->getMessage());
                fwrite($stream_errors, "$problem_rec -- ".print_r($record, true));
            }

            $bar->advance();
        }
        fclose($stream_errors);
        $bar->finish();
        if ($problem_rec > 0) {
            echo "\n";
            $this->line("There is found <fg=red;bg=yellow>$problem_rec</> records with problem and not update details in <fg=red;bg=yellow>$model.err.txt</>");
        }

        return self::SUCCESS;
    }

    private function checkModel(?string $model): string
    {
        if (is_null($model)) {
            throw new \Exception('no model define');
        }
        if (is_null(config($this->key)[$model])) {
            throw new \Exception("the $model is not defined");
        }
        if (! in_array(ImportCsvCommandLaravelInterface::class, class_implements(config($this->key)[$model]['model']))) {
            throw new \Exception('no interface '.ImportCsvCommandLaravelInterface::class.' implements');
        }
        //        dd(config($this->key)[$model]);
        $csv = config($this->key)[$model]['csv'];
        if (! file_exists($csv)) {
            throw new \Exception("the $csv file does not exist");
        }

        return $csv;
    }
}
