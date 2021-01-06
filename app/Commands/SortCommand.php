<?php
declare(strict_types=1);

namespace App\Commands;

use App\Changelog;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class SortCommand extends Command
{
    /** @inheritdoc */
    protected $signature = 'sort';

    /** @inheritdoc */
    protected $description = 'Short releases inside CHANGELOG.md by version';


    public function handle() : ?int
    {
        $dir = config('changelogger.directory');
        $changelogFile = $dir .'/CHANGELOG.md';

        if (! File::exists($changelogFile)) {
            $this->error(sprintf('There is no CHANGELOG.md file inside %s', $dir));
            return -1;
        }

        $changelog = Changelog::parse(File::get($changelogFile));

        $changelog->sort();

        File::replace($changelogFile, $changelog->write());

        return 0;
    }

}
