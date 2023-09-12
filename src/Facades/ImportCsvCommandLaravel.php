<?php

namespace Kgalanos\ImportCsvCommandLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kgalanos\ImportCsvCommandLaravel\ImportCsvCommandLaravel
 */
class ImportCsvCommandLaravel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Kgalanos\ImportCsvCommandLaravel\ImportCsvCommandLaravel::class;
    }
}
