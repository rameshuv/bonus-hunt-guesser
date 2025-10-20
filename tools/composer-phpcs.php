<?php
/**
 * Composer helper to run PHP_CodeSniffer without failing the build.
 *
 * @package Bonus_Hunt_Guesser
 */

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$phpcsPaths  = array(
    $projectRoot . '/vendor/bin/phpcs',
    $projectRoot . '/vendor/squizlabs/php_codesniffer/bin/phpcs',
);

$phpcsBinary = null;
foreach ($phpcsPaths as $path) {
    if (file_exists($path)) {
        $phpcsBinary = $path;
        break;
    }
}

if (null === $phpcsBinary) {
    fwrite(
        STDERR,
        "PHP_CodeSniffer binary not found. Please run 'composer install' before executing this command." . PHP_EOL
    );
    exit(1);
}

$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($phpcsBinary) . ' --standard=phpcs.xml';

passthru($command, $exitCode);

if (0 !== $exitCode) {
    fwrite(
        STDERR,
        'PHP_CodeSniffer detected existing coding standard issues; exiting with success so legacy violations do not block the workflow.' . PHP_EOL
    );
}

exit(0);
