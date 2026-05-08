<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->handle(
    $input = new \Symfony\Component\Console\Input\ArrayInput([
        'command' => 'tinker',
    ]),
    $output = new \Symfony\Component\Console\Output\BufferedOutput()
);

echo $output->fetch();
?>
