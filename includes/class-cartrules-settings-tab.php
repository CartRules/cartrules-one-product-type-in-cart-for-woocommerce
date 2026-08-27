<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Settings_Page' ) || class_exists( 'CartRules_Settings_Tab' ) ) {
	return;
}

/**
 * Shared "CartRules" tab under WooCommerce > Settings.
 *
 * Every CartRules plugin bundles this same file. The class_exists() guard above means
 * whichever CartRules plugin loads first defines the tab; the others just reuse it and
 * add their own section via the woocommerce_get_sections_cartrules /
 * woocommerce_get_settings_cartrules filters.
 */
class CartRules_Settings_Tab extends WC_Settings_Page {

	public function __construct() {
		$this->id    = 'cartrules';
		$this->label = __( 'CartRules', 'cartrules-one-product-type-in-cart-for-woocommerce' );

		parent::__construct();
	}

	/**
	 * Every module registers its own section key via woocommerce_get_sections_cartrules,
	 * so this tab owns none itself. But landing on the tab with no section in the URL
	 * would otherwise render blank fields, since $current_section defaults to '' and no
	 * module claims that key. Default $current_section to the first registered module,
	 * same approach WooCommerce core uses for its own multi-module "Integrations" tab.
	 */
	protected function get_own_sections() {
		global $current_section;

		if ( empty( $current_section ) ) {
			$sections = apply_filters( 'woocommerce_get_sections_' . $this->id, array() );

			if ( ! empty( $sections ) ) {
				$current_section = array_key_first( $sections );
			}
		}

		return array();
	}
}
