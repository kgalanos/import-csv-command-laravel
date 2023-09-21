<?php

namespace Kgalanos\ImportCsvCommandLaravel;

use Kgalanos\ImportCsvCommandLaravel\Commands\ImportCsvCommandLaravelCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ImportCsvCommandLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('import-csv-command-laravel')
            ->hasConfigFile('import-csv-command')
//            ->hasViews()
//            ->hasMigration('create_import-csv-command-laravel_table')
            ->hasCommands([
                ImportCsvCommandLaravelCommand::class,
                //                ImportCsvToAdjacencyListCommand::class,
            ]);
    }
}
