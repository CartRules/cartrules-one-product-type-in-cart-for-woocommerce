<?php
/**
 * Plugin Name:          CartRules One Product Type in Cart for WooCommerce
 * Description:          Restrict the WooCommerce cart to products of a single product type at a time.
 * Version:              1.0.0
 * Requires at least:    6.5
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * WC requires at least: 7.1
 * WC tested up to:      11.0.1
 * Author:               Rodolfo Melogli
 * Author URI:           https://businessbloomer.com/
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          cartrules-one-product-type-in-cart-for-woocommerce
 *
 * @package CartRules_OPTIC
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

define( 'CARTRULES_OPTIC_VERSION', '1.0.0' );
define( 'CARTRULES_OPTIC_FILE', __FILE__ );
define( 'CARTRULES_OPTIC_DIR', plugin_dir_path( __FILE__ ) );
define( 'CARTRULES_OPTIC_URL', plugin_dir_url( __FILE__ ) );

$cartrules_optic_autoload = CARTRULES_OPTIC_DIR . 'vendor/autoload.php';

if ( file_exists( $cartrules_optic_autoload ) ) {
	require_once $cartrules_optic_autoload;
}

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);

add_action( 'plugins_loaded', 'cartrules_optic_init' );

/**
 * Bail if WooCommerce isn't active; WordPress's own dependency check (see the "Requires Plugins" header above)
 * already prevents activation and auto-deactivates this plugin if WooCommerce is removed, so this is just
 * a cheap fallback against fatal errors in edge cases the core check doesn't cover.
 */
function cartrules_optic_init(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	require_once CARTRULES_OPTIC_DIR . 'includes/class-cartrules-optic-cart-restriction.php';
	new CartRules_OPTIC_Cart_Restriction();

	require_once CARTRULES_OPTIC_DIR . 'includes/cartrules-optic-settings.php';

	add_filter( 'woocommerce_get_settings_pages', 'cartrules_optic_register_settings_tab' );
}

/**
 * Registers the shared "CartRules" settings tab if no other CartRules plugin has
 * already added it; this module's own fields attach to it as a section (see
 * includes/cartrules-optic-settings.php).
 */
function cartrules_optic_register_settings_tab( array $settings ): array {
	require_once CARTRULES_OPTIC_DIR . 'includes/class-cartrules-settings-tab.php';

	foreach ( $settings as $page ) {
		if ( $page instanceof CartRules_Settings_Tab ) {
			return $settings;
		}
	}

	$settings[] = new CartRules_Settings_Tab();

	return $settings;
}
