<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SortTest extends TestCase
{
    public function tearDown(): void
    {
        File::delete(config('changelogger.directory') . '/CHANGELOG.md');
    }

    public function testFileExistsCheck() : void
    {
        $this->artisan('sort',
            [])
            ->assertExitCode(-1);
    }

    public function testSorting() : void
    {
        $this->createBrokenOrder();
        $this->artisan('sort',
            [])
            ->assertExitCode(0);

        self::assertFileExists(config('changelogger.directory') . '/CHANGELOG.md');

        $today = Carbon::now()->format('Y-m-d');
        $changelog = <<<CHANGE
<!-- CHANGELOGGER -->

## [v2.0.0] - {$today}

### New feature (1 change)

- Feature 2 added


## [v1.0.1] - {$today}

### Hotfix (1 change)

- Hotfix 1


## [v1.0.0] - {$today}

### New feature (1 change)

- Feature 1 added

CHANGE;

        self::assertEquals(
            $changelog,
            File::get(config('changelogger.directory') . '/CHANGELOG.md')
        );
    }

    public function createBrokenOrder() : void
    {
        $this->artisan('new',
            ['--type' => 'added', '--message' => 'Feature 1 added', '--file' => 'file1'])
            ->assertExitCode(0);
        $this->artisan('release', ['tag' => 'v1.0.0'])
            ->expectsOutput('Changelog for v1.0.0 created')
            ->assertExitCode(0);
        $this->artisan('new',
            ['--type' => 'added', '--message' => 'Feature 2 added', '--file' => 'file2'])
            ->assertExitCode(0);

        $this->artisan('release', ['tag' => 'v2.0.0'])
            ->expectsOutput('Changelog for v2.0.0 created')
            ->assertExitCode(0);
        $this->artisan('new',
            ['--type' => 'hotfix', '--message' => 'Hotfix 1', '--file' => 'file3'])
            ->assertExitCode(0);

        $this->artisan('release', ['tag' => 'v1.0.1'])
            ->expectsOutput('Changelog for v1.0.1 created')
            ->assertExitCode(0);

    }
}
