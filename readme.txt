=== CartRules One Product Type in Cart for WooCommerce ===
Contributors: businessbloomer
Tags: woocommerce, cart, product type, restrict cart, checkout
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin ensures customers can only buy one product type at a time.

== Description ==

This plugin stops customers from mixing different product types, for example simple and variable products, in the same order. If a product of a different type is already in the cart, adding one of a different type will either be blocked, or the cart will be emptied first, depending on the option you choose.

This is useful for stores that need to keep certain product types separate at checkout, for example because grouped or external/affiliate products are fulfilled differently from simple or variable ones.

Once activated, go to WooCommerce > Settings > CartRules > One Product Type in Cart to turn the restriction on and choose what should happen.

Works with both the classic, shortcode-based cart and checkout, and the newer WooCommerce Cart and Checkout blocks.

== Frequently Asked Questions ==

= Which "product type" does this plugin use? =

WooCommerce's own built-in product type: simple, variable, grouped, or external/affiliate, plus any custom type registered by another plugin (for example a subscription type).

= Does this work with variable products? =

Yes. A variation is always treated as the type of its parent product (variable), whichever variation is actually added to the cart.

= Does this work with the WooCommerce Cart and Checkout blocks, or only the classic shortcode-based cart? =

Both. The restriction is applied when a product is added to the cart, so it works the same way whether your store uses the classic cart/checkout pages or the block-based versions.

= Does this affect orders created or edited from wp-admin? =

No, the restriction only applies to the storefront cart. Orders added or changed from wp-admin are not affected.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install it through the Plugins menu in WordPress directly.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to WooCommerce > Settings > CartRules > One Product Type in Cart to turn the restriction on and choose what should happen.

== Changelog ==

= 1.0.0 =
* Initial release
