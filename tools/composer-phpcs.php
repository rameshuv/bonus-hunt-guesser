<?php
/**
 * Composer helper for running PHP_CodeSniffer.
 *
 * @package BonusHuntGuesser
 */

$root_dir = dirname( __DIR__ );
$binary   = $root_dir . '/vendor/bin/phpcs';

if ( '\\' === DIRECTORY_SEPARATOR ) {
	$windows_candidate = $binary . '.bat';

	if ( file_exists( $windows_candidate ) ) {
		$binary = $windows_candidate;
	}
}

if ( ! file_exists( $binary ) ) {
	fwrite(
		STDOUT,
		"PHP_CodeSniffer is not installed for this project.\n" .
		"Run 'composer install' to download development dependencies before executing this command.\n"
	);
	exit( 0 );
}

$command = escapeshellarg( $binary ) . ' --standard=phpcs.xml';

$process = proc_open( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_open
	$command,
	array(
		0 => STDIN,
		1 => STDOUT,
		2 => STDERR,
	),
	$pipes,
	$root_dir
);

if ( ! is_resource( $process ) ) {
	fwrite( STDERR, "Unable to launch PHP_CodeSniffer.\n" );
	exit( 1 );
}

$status = proc_close( $process ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_close

exit( (int) $status );
