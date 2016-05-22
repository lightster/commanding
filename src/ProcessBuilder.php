<?php

namespace Lstr\Commanding;

use Lstr\Sprintf\Middleware\Cli\Bundle;
use Lstr\Sprintf\Sprintf;
use Symfony\Component\Process\Process;

class ProcessBuilder
{
    /**
     * @var array
     */
    private $commands;

    /**
     * @var array
     */
    private $params;

    /**
     * @param array $commands
     * @param array $params
     */
    public function __construct(array $commands, array $params = [])
    {
        $this->commands = $commands;
        $this->params = $params;
    }

    /**
     * @param string $starting_command
     * @return Process
     */
    public function buildShellProcess($starting_command = null)
    {
        $script_string = $this->buildScriptString($starting_command);

        $ssh_command = "exec bash -s << 'BASH'\n"
            . $script_string
            . "\nexit"
            . "\nBASH";

        return new Process($ssh_command);
    }

    /**
     * @param string $ssh_host
     * @param string $starting_command
     * @return Process
     */
    public function buildSshProcess($ssh_host, $starting_command = null)
    {
        $script_string = $this->buildScriptString($starting_command);

        $ssh_command = 'exec ssh -q -t -t '
            . escapeshellarg($ssh_host)
            . " 'bash -s' << 'BASH'\n"
            . $script_string
            . "\nexit"
            . "\nBASH";

        return new Process($ssh_command);
    }

    /**
     * @param string $starting_command
     * @return string
     */
    private function buildScriptString($starting_command)
    {
        $started = false;
        if (!$starting_command) {
            $started = true;
        }

        $selected_commands = [];
        foreach ($this->commands as $key => $command) {
            if (!$started && "{$key}" !== "{$starting_command}") {
                continue;
            }

            $selected_commands[$key] = $command;
            $started = true;
        }

        $sprintf = new Sprintf(new Bundle());
        return $sprintf->sprintf(implode("\n", $selected_commands), $this->params);
    }
}
