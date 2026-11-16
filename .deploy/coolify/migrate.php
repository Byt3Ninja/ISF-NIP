<?php

/*
 * Runs `artisan migrate --force` with Laravel's native column changes switched on.
 *
 * Several older migrations change enum columns (for example
 * 2024_12_01_212635_add_group_to_questions_bank). Laravel can only do that
 * natively. Since doctrine/dbal was added to composer.json, a plain
 * `artisan migrate` routes every ->change() through DBAL instead, which stops
 * on an empty database with: Unknown column type "enum" requested.
 *
 * The switch is set here, for this run only, so no application code changes.
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Builder;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

define('LARAVEL_START', microtime(true));

$root = dirname(__DIR__, 2);

require $root . '/vendor/autoload.php';

$app = require $root . '/bootstrap/app.php';

Builder::useNativeSchemaOperationsIfPossible();

$kernel = $app->make(Kernel::class);

$status = $kernel->handle(
    $input = new ArgvInput(['artisan', 'migrate', '--force']),
    new ConsoleOutput
);

$kernel->terminate($input, $status);

exit($status);
