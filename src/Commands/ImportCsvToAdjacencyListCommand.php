<?php

namespace Kgalanos\ImportCsvCommandLaravel\Commands;

use Illuminate\Console\Command;

class ImportCsvToAdjacencyListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:csvList {--truncated} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import CSV Adjacency-List data to a model';

    protected $key = 'import-csv-list-command';
    public function handle():int
    {
        echo "import list";
        return 0;
    }
}
