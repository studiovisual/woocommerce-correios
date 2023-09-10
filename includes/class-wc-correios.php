<?php
/**
 * Correios
 *
 * @package WooCommerce_Correios/Classes
 * @since   3.6.0
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugins main class.
 */
class WC_Correios {

	/**
	 * Initialize the plugin public actions.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ), -1 );

		// Checks with WooCommerce is installed.
		if ( class_exists( 'WC_Integration' ) ) {
			self::includes();

			if ( is_admin() ) {
				self::admin_includes();
			}

			add_filter( 'woocommerce_integrations', array( __CLASS__, 'include_integrations' ) );
			add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'include_methods' ) );
			add_filter( 'woocommerce_email_classes', array( __CLASS__, 'include_emails' ) );
		} else {
			add_action( 'admin_notices', array( __CLASS__, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-correios', false, dirname( plugin_basename( WC_CORREIOS_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private static function includes() {
		include_once dirname( __FILE__ ) . '/wc-correios-functions.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-install.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-package.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-webservice.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-webservice-international.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-cws-connect.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-cws-calculate.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-autofill-addresses.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-tracking-history.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-rest-api.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-orders.php';
		include_once dirname( __FILE__ ) . '/class-wc-correios-cart.php';

		// Integration.
		include_once dirname( __FILE__ ) . '/integrations/class-wc-correios-integration.php';

		// Shipping methods.
		include_once dirname( __FILE__ ) . '/abstracts/class-wc-correios-shipping.php';
		include_once dirname( __FILE__ ) . '/abstracts/class-wc-correios-shipping-carta.php';
		include_once dirname( __FILE__ ) . '/abstracts/class-wc-correios-shipping-impresso.php';
		include_once dirname( __FILE__ ) . '/abstracts/class-wc-correios-shipping-international.php';
		foreach ( glob( plugin_dir_path( __FILE__ ) . '/shipping/*.php' ) as $filename ) {
			include_once $filename;
		}
	}

	/**
	 * Admin includes.
	 */
	private static function admin_includes() {
		include_once dirname( __FILE__ ) . '/admin/class-wc-correios-admin-orders.php';
	}

	/**
	 * Include Correios integration to WooCommerce.
	 *
	 * @param  array $integrations Default integrations.
	 *
	 * @return array
	 */
	public static function include_integrations( $integrations ) {
		$integrations[] = 'WC_Correios_Integration';

		return $integrations;
	}

	/**
	 * Include Correios shipping methods to WooCommerce.
	 *
	 * @param  array $methods Default shipping methods.
	 *
	 * @return array
	 */
	public static function include_methods( $methods ) {
		$methods['correios-cws']                               = 'WC_Correios_Shipping_Cws';
		$methods['correios-carta-registrada']                  = 'WC_Correios_Shipping_Carta_Registrada';
		$methods['correios-impresso-normal']                   = 'WC_Correios_Shipping_Impresso_Normal';
		$methods['correios-impresso-urgente']                  = 'WC_Correios_Shipping_Impresso_Urgente';
		$methods['correios-pac']                               = 'WC_Correios_Shipping_PAC';
		$methods['correios-sedex']                             = 'WC_Correios_Shipping_SEDEX';
		$methods['correios-sedex10-envelope']                  = 'WC_Correios_Shipping_SEDEX_10_Envelope';
		$methods['correios-sedex10-pacote']                    = 'WC_Correios_Shipping_SEDEX_10_Pacote';
		$methods['correios-sedex12']                           = 'WC_Correios_Shipping_SEDEX_12';
		$methods['correios-sedex-hoje']                        = 'WC_Correios_Shipping_SEDEX_Hoje';
		$methods['correios-esedex']                            = 'WC_Correios_Shipping_ESEDEX';
		$methods['correios-exporta-facil-economico']           = 'WC_Correios_Shipping_Exporta_Facil_Economico';
		$methods['correios-exporta-facil-expresso']            = 'WC_Correios_Shipping_Exporta_Facil_Expresso';
		$methods['correios-exporta-facil-premium']             = 'WC_Correios_Shipping_Exporta_Facil_Premium';
		$methods['correios-exporta-facil-standard']            = 'WC_Correios_Shipping_Exporta_Facil_Standard';
		$methods['correios-documento-economico']               = 'WC_Correios_Shipping_Documento_Economico';
		$methods['correios-documento-internacional-expresso']  = 'WC_Correios_Shipping_Documento_Internacional_Expresso';
		$methods['correios-documento-internacional-premium']   = 'WC_Correios_Shipping_Documento_Internacional_Premium';
		$methods['correios-documento-internacional-standard']  = 'WC_Correios_Shipping_Documento_Internacional_Standard';

		return $methods;
	}

	/**
	 * Include emails.
	 *
	 * @param  array $emails Default emails.
	 *
	 * @return array
	 */
	public static function include_emails( $emails ) {
		if ( ! isset( $emails['WC_Correios_Tracking_Email'] ) ) {
			$emails['WC_Correios_Tracking_Email'] = include dirname( __FILE__ ) . '/emails/class-wc-correios-tracking-email.php';
		}

		return $emails;
	}

	/**
	 * WooCommerce fallback notice.
	 */
	public static function woocommerce_missing_notice() {
		include_once dirname( __FILE__ ) . '/admin/views/html-admin-missing-dependencies.php';
	}

	/**
	 * Get main file.
	 *
	 * @return string
	 */
	public static function get_main_file() {
		return WC_CORREIOS_PLUGIN_FILE;
	}

	/**
	 * Get plugin path.
	 *
	 * @return string
	 */
	public static function get_plugin_path() {
		return plugin_dir_path( WC_CORREIOS_PLUGIN_FILE );
	}

	/**
	 * Get templates path.
	 *
	 * @return string
	 */
	public static function get_templates_path() {
		return self::get_plugin_path() . 'templates/';
	}
}
