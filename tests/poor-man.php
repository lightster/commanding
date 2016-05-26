<?php

use Lstr\Commanding\ProcessBuilder;

$process_builder = new ProcessBuilder(
    [
        'host'        => 'You logged into %(hostname)',
        'date'        => "echo %(date)",
        'day_of_year' => "echo %(day_of_year)",
        'exit'        => "if [ %(exit_code) != '1' ] ; then echo a ; fi",
        'inspiration' => "echo %(inspiration)",
    ]
);

$hosts = [
    '127.0.0.1',
    'localhost',
];

$params_template = [
    'host'        => '',
    'date'        => 'Today is ' . date('F j, Y'),
    'day_of_year' => 'Today is day ' . date('z') . ' of they year',
    'exit_code'   => 0,
    'inspiration' => 'You are awesome to me!',
];

$param_builder = function ($exit_code) use ($params_template) {
    return function ($host) use ($params_template, $exit_code) {
        $params = $params_template;

        $params['host'] = $host;
        $params['exit_code'] = $exit_code;

        return $params;
    };
}

$failed_hosts = [];
foreach ($hosts as $index => $host) {
    $params = $params_template;

    $process = $process_builder->buildSshProcess($host, $param_builder($index));

    $retry_from = $process->run();
    if ($retry_from) {
        $failed_hosts[$host] = $retry_from;
    }
}

foreach ($failed_hosts as $host => $retry_from) {
    $process = $process_builder->buildSshProcess($host, $param_builder($index), $retry_from);
    $process->run();
}
