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

$failed_hosts = [];
foreach ($hosts as $index => $host) {
    $params = $params_template;
    $params['host'] = $host;
    $params['exit_code'] = $index;

    $process = $process_builder->buildSshProcess($host, $params);

    $retry_from = $process->run();
    if ($retry_from) {
        $failed_hosts[$host] = $retry_from;
    }
}

foreach ($failed_hosts as $host => $retry_from) {
    $params = $params_template;
    $params['host'] = $host;

    $process = $process_builder->buildSshProcess($host, $params, $retry_from);
    $process->run();
}
