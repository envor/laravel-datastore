<?php

namespace Envor\Datastore\Commands;

use Illuminate\Console\Command;

class DatastoreCommand extends Command
{
    public $signature = 'laravel-datastore';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
