<?php

namespace Nox\Framework\Support;

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class Composer
{
    protected ?BufferedOutput $output = null;

    public function __construct()
    {
        putenv('COMPOSER_HOME=' . __DIR__ . '/vendor/bin/composer');
    }

    public function require(string $package): int
    {
        return $this->run('require', [
            'packages' => [$package],
        ]);
    }

    public function run(string $command, array $extraParameters = []): int
    {
        $input = new ArrayInput([
            'command' => $command,
            '-d' => base_path(),
            ...$extraParameters,
        ]);

        $input->setInteractive(false);

        $this->output = new BufferedOutput();

        return (new Application())->doRun($input, $this->output);
    }

    public function update(string $package): int
    {
        return $this->run('update', [
            'packages' => [$package],
            '-W' => true,
            '-o' => true,
            '--no-scripts' => true,
            '--no-dev' => true
        ]);
    }

    public function remove(string $package): int
    {
        return $this->run('remove', [
            'packages' => [$package]
        ]);
    }

    public function getOutput(): ?BufferedOutput
    {
        return $this->output;
    }
}
