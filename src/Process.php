<?php

namespace Lstr\Commanding;

use Lstr\Sprintf\Middleware\Cli\Bundle;
use Lstr\Sprintf\Sprintf;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process
{
    /**
     * @var callable
     */
    private $command_wrapper;

    /**
     * @var array
     */
    private $commands;

    /**
     * @var array
     */
    private $starting_command;

    /**
     * @param callable $command_wrapper
     * @param array $commands
     * @param string $starting_command
     */
    public function __construct(callable $command_wrapper, array $commands, $starting_command = null)
    {
        $this->command_wrapper = $command_wrapper;
        $this->commands = $commands;
        $this->starting_command = $starting_command;
    }

    /**
     * @return int
     */
    public function run()
    {
        $exit_code = $this->getSymfonyProcess()->run();

        if (!$exit_code) {
            return true;
        }

        $lines = split("[\r\n]+", $this->getSymfonyProcess()->getErrorOutput());
        $lines_reversed = array_reverse($lines);

        $non_blank_line = null;
        foreach ($lines_reversed as $line) {
            if ($line) {
                $non_blank_line = $line;
                break;
            }
        }

        $last_command_info = json_decode($non_blank_line, true);

        return $last_command_info['command'];
    }

    /**
     * @return SymfonyProcess
     */
    public function getSymfonyProcess()
    {
        if ($this->process) {
            return $this->process;
        }

        $this->process = new SymfonyProcess(
            $this->generateCommandString()
        );

        return $this->process;
    }

    /**
     * @param string $starting_command
     * @return string
     */
    private function buildScriptString()
    {
        $started = false;
        if (null === $this->starting_command) {
            $started = true;
        }

        $selected_commands = [];
        foreach ($this->commands as $key => $command) {
            if (!$started && "{$key}" !== "{$this->starting_command}") {
                continue;
            }

            $selected_commands[$key] = $command;
            $started = true;
        }

        $sprintf = new Sprintf(new Bundle());
        return $sprintf->sprintf(implode("\n", $selected_commands), $this->params);
    }
}
