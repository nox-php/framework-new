<?php

namespace Nox\Framework\Support;

use Composer\Console\Application;
use Composer\InstalledVersions;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class Composer
{
    protected ?BufferedOutput $output = null;

    public function __construct()
    {
        putenv('COMPOSER_HOME='.__DIR__.'/vendor/bin/composer');
    }

    public function require(string|array $packages): int
    {
        $packages = is_array($packages) ? $packages : [$packages];

        return $this->run('require', [
            'packages' => $packages,
        ]);
    }

    public function run(string $command, array $extraParameters = []): int
    {
        $input = new ArrayInput([
            'command' => $command,
            '-d' => base_path(),
            '-o' => true,
            '--no-scripts' => true,
            ...$extraParameters,
        ]);

        $input->setInteractive(false);

        $this->output = new BufferedOutput();

        return (new Application())->doRun($input, $this->output);
    }

    public function update(string|array $packages): int
    {
        $packages = is_array($packages) ? $packages : [$packages];

        return $this->run('update', [
            'packages' => $packages,
            '-W' => true,
            '--no-dev' => true,
        ]);
    }

    public function remove(string|array $packages): int
    {
        $packages = is_array($packages) ? $packages : [$packages];

        return $this->run('remove', [
            'packages' => $packages,
        ]);
    }

    public function manifest(string $package): ?array
    {
        $path = InstalledVersions::getInstallPath($package).'/composer.json';

        if (! File::exists($path)) {
            return null;
        }

        return rescue(
            static fn () => json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR),
            null,
            false
        );
    }

    public function getOutput(): ?BufferedOutput
    {
        return $this->output;
    }
}
