<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'serve:large')]
class ServeWithUploadLimits extends ServeCommand
{
    protected $name = 'serve:large';

    protected $description = 'Serve the application (same as serve) with 210M upload limits for large videos';

    /**
     * @return array<int, string>
     */
    protected function serverCommand(): array
    {
        $command = parent::serverCommand();

        array_splice($command, 1, 0, [
            '-d', 'post_max_size=210M',
            '-d', 'upload_max_filesize=210M',
            '-d', 'max_execution_time=600',
        ]);

        return $command;
    }
}
