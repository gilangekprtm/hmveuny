<?php

namespace App\Console\Commands;

use App\Support\JsonStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportDatabaseToJson extends Command
{
    protected $signature = 'data:import-database';

    protected $description = 'Copy existing HMVE database records into the JSON data files';

    public function handle(JsonStore $store): int
    {
        $tables = [
            'users',
            'pembinas',
            'pimpinans',
            'units',
            'anggotas',
            'site_contents',
            'publications',
        ];

        foreach ($tables as $table) {
            if ($store->all($table)->isNotEmpty()) {
                $this->components->error("JSON collection [{$table}] is not empty. Import stopped to avoid overwriting data.");

                return self::FAILURE;
            }
        }

        $imported = [];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $imported[$table] = DB::table($table)->orderBy('id')->get()
                ->map(fn (object $record) => (array) $record)
                ->all();
        }

        foreach ($imported as $table => $records) {
            $store->replace($table, $records);
            $this->components->info('Imported '.count($records)." records into [{$table}.json].");
        }

        $this->components->info('Database records were copied. The original database was not modified.');

        return self::SUCCESS;
    }
}
