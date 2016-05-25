<?php

namespace Lstr\Commanding;

use Lstr\Sprintf\Middleware\Cli\Bundle;
use Lstr\Sprintf\Sprintf;

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
    public function __construct(array $commands)
    {
        $this->commands = $commands;
    }

    /**
     * @param string $starting_command
     * @return Process
     */
    public function buildShellProcess(array $params, $starting_command = null)
    {
        $script_string = $this->buildScriptString($starting_command);

        $shell_command = "exec bash -s << 'BASH'\n"
            . $script_string
            . "\nexit"
            . "\nBASH";

        return new Process($shell_command);
    }

    /**
     * @param string $ssh_host
     * @param string $starting_command
     * @return Process
     */
    public function buildSshProcess($ssh_host, array $params, $starting_command = null)
    {
        $ssh_command = 'exec ssh -q -t -t '
            . escapeshellarg($ssh_host)
            . " 'bash -s' << 'BASH'\n"
            . $script_string
            . "\nexit"
            . "\nBASH";

        return new Process($ssh_command);
    }
}
