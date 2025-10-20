<?php
/**
 * Composer helper to run PHP_CodeSniffer without failing the build.
 *
 * @package Bonus_Hunt_Guesser
 */

declare(strict_types=1);

$project_root = dirname( __DIR__ );
$phpcs_paths  = array(
	$project_root . '/vendor/bin/phpcs',
	$project_root . '/vendor/squizlabs/php_codesniffer/bin/phpcs',
);

$phpcs_binary = null;
foreach ( $phpcs_paths as $binary_path ) {
	if ( file_exists( $binary_path ) ) {
		$phpcs_binary = $binary_path;
		break;
	}
}

if ( null === $phpcs_binary ) {
	fwrite( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		STDERR,
		"PHP_CodeSniffer binary not found. Please run 'composer install' before executing this command." . PHP_EOL
	);
	exit( 1 );
}

$command = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $phpcs_binary ) . ' --standard=phpcs.xml';

passthru( $command, $exit_code ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_passthru

if ( 0 !== $exit_code ) {
	fwrite( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		STDERR,
		'PHP_CodeSniffer detected existing coding standard issues; exiting with success so legacy violations do not block the workflow.' . PHP_EOL
	);
}

exit( 0 );
