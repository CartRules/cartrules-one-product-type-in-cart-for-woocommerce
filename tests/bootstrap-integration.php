<?php
/**
 * Bootstrap for integration tests.
 *
 * Boots real WordPress from the core test library, then loads the code under
 * test before WP finishes initializing. How that happens differs by kind, and
 * setup.sh splices in the right one: a plugin is required by its entry point,
 * a theme is switched to. Both have to run before WordPress reads the active
 * plugin and theme options, which is why they hook muplugins_loaded.
 *
 * WP_TESTS_DIR is set automatically inside wp-env, so these run there rather
 * than on the host — the test library and the database both live in the
 * container. On the host, point WP_TESTS_DIR at a separate WordPress develop
 * checkout: export WP_TESTS_DIR=/path/to/wordpress-develop/tests/phpunit
 *
 * The --env-cwd path below uses the project's directory name, which wp-env
 * mounts as-is; it is not necessarily the slug.
 *
 * @package CartRulesOPTIC
 */

declare( strict_types=1 );

$cartrules_optic_autoload = __DIR__ . '/../vendor/autoload.php';

if ( file_exists( $cartrules_optic_autoload ) ) {
	require_once $cartrules_optic_autoload;
}

$cartrules_optic_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $cartrules_optic_tests_dir ) {
	$cartrules_optic_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

$cartrules_optic_functions = $cartrules_optic_tests_dir . '/includes/functions.php';

if ( ! file_exists( $cartrules_optic_functions ) ) {
	fwrite(
		STDERR,
		"Could not find the WordPress test library at {$cartrules_optic_tests_dir}.\n" .
		"Integration tests run inside wp-env, not on the host:\n" .
		"  npx wp-env run tests-cli \\\n" .
		"    --env-cwd=wp-content/{plugins|themes}/<project-folder> \\\n" .
		"    composer test:integration\n" .
		"Or set WP_TESTS_DIR to a wordpress-develop/tests/phpunit checkout.\n"
	);
	exit( 1 );
}

require_once $cartrules_optic_functions;

/**
 * Load this plugin before WordPress finishes booting.
 *
 * Plugins under test are not in the active-plugins option, so nothing else
 * loads them. muplugins_loaded fires before WordPress reads that option.
 *
 * @return void
 */
function cartrules_optic_manually_load_plugin() {
	require dirname( __DIR__ ) . '/cartrules-one-product-type-in-cart-for-woocommerce.php';
}
tests_add_filter( 'muplugins_loaded', 'cartrules_optic_manually_load_plugin' );

require $cartrules_optic_tests_dir . '/includes/bootstrap.php';
