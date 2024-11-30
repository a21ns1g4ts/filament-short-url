<?php

namespace A21ns1g4ts\FilamentShortUrl\Commands;

use Illuminate\Console\Command;

class FilamentShortUrlCommand extends Command
{
    public $signature = 'filament-short-url';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
