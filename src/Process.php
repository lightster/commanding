<?php

namespace Lstr\Commanding;

use Symfony\Component\Process\Process as SymfonyProcess;

class Process
{
    /**
     * @var string
     */
    private $command_string;

    /**
     * @param string $command_string
     */
    public function __construct($command_string)
    {
        $this->command_string = $command_string;
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

        $last_command_ran = json_decode($non_blank_line, true);

        return $last_command_ran;
    }

    /**
     * @return SymfonyProcess
     */
    public function getSymfonyProcess()
    {
        if ($this->process) {
            return $this->process;
        }

        $this->process = new SymfonyProcess($this->command_string);

        return $this->process;
    }
}
