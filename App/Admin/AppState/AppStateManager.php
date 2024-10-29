<?php

namespace ArubaFe\Admin\AppState;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\Notice\ArubaFeNotices;
use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;
use ArubaFe\Admin\Tax\Countries;

class AppStateManager {

	protected $notice;

	public function __construct() {
		$this->aruba_fe_url = admin_url( 'admin.php?page=aruba-fatturazione-elettronica' );
		$this->notice       = ArubaFeNotices::getInstance();
		$this->fe_options   = new ArubaFeOptionParser();
		$this->checkAppState();
	}

	protected function checkAppState() {
		global $current_screen;

		/**
		 * non eseguo i controlli nella pagina dei settings
		 */

		$current_warning = CustomOptions::get_option( ArubaFeConstants::ARUBA_FE_IN_ERROR, null );

		if ( $current_warning ) {

			$link = 'https://guide.hosting.aruba.it/hosting/hosting-woocommerce-gestito/plugin-fatturazione-elettronica.aspx#aggiornare';

			$string = wp_kses( '<p>' . __( 'An updated version of the Aruba Electronic Invoicing plugin is now available that brings improvements over the current version.', 'aruba-fatturazione-elettronica' ) . '</p>' , array( 'p' => array() ) );



            $string .= wp_kses(

            sprintf(
            // translators: %s: plugin download link.
                __( '<p><a href=\'%s\' target=\'_blank\'>Download it now from this page</a>, install it on WooCommerce and confirm the substitution.</p>', 'aruba-fatturazione-elettronica' ), $link ),
				array(
					'p' => array(),
					'a' => array(
						'href'   => array(),
						'target' => array(),
					),
				)
			);

			$this->notice->addNoticeHtml( $string, 'error', false );

		}

		if ( is_object( $current_screen ) && $current_screen->base != ArubaFeConstants::ARUBA_FE_CONFIG_BASE ) {

			$this->checkUrl();

			// controllare lo stato di connessione
			if ( $this->checkConnectionState() ) {
				return;
			}

			if ( $this->firstConfigNotDone() ) {
				return;
			}

			$this->registerIncompatiblePlugins();

			// Controllare la valuta
			$this->checkCurrency();
			// Controllare metodi di pagamento
			$this->checkPaymentMethods();
			// Controllare le aliquote
			$this->checkCalcTax();

			$this->checkTaxConfigs();
		}
	}

	protected function checkUrl() {

		if ( $this->fe_options->getConnectionState() ) {

			$configUrl = $this->fe_options->getConfigOption( 'shopUrl', 'api_connection' );

			if ( $configUrl != esc_url( get_bloginfo( 'url' ) ) ) {

				$text = esc_html__( 'The web address of your online shop has changed. Go to the Electronic Billing Panel Settings and generate a new customer code and secret code for WooCommerce within the E-commerce section. Then replace them in the configuration of the Aruba Electronic Invoicing plugin.', 'aruba-fatturazione-elettronica' );

				$this->notice->addNoticeHtml( '<p>' . $text . '</p>', 'error', true, ArubaFeConstants::ARUBA_FE_NOTICES['domain_config'] );

			}
		}
	}

	protected function checkPluginStatus() {

		$text = esc_html__( 'The WooCommerce module has expired. No new orders will be imported on Aruba\'s Electronic Invoicing service.', 'aruba-fatturazione-elettronica' );
	}

	protected function checkCalcTax() {

		$prices_include_tax = get_option( 'woocommerce_prices_include_tax' );

		if ( $prices_include_tax === 'yes' ) {

			$this->notice->addNoticeHtml(
				sprintf(
					esc_html__(
						'In the WooCommerce settings you have selected the indication of prices including VAT. Change this setting to allow the correct functioning of the Aruba Electronic Invoicing plugin.',
						'aruba-fatturazione-elettronica'
					)
				),
				'warning'
			);
		}
	}

	protected function firstConfigNotDone() {

		if ( ! CustomOptions::get_option( '_aruba_fe_first_config_done' ) ) {
			$this->notice->addNoticeHtml(
				'
				 <p>' . esc_html__( 'Complete the configuration of the Aruba Electronic Invoicing plugin to immediately start importing orders into the invoicing service and automatically generate electronic invoices.', 'aruba-fatturazione-elettronica' ) . '</p>
				 <p><a href="' . esc_url( $this->aruba_fe_url ) . '" class="fe-btn"> ' . esc_html__( 'Configure now', 'aruba-fatturazione-elettronica' ) . '</a></p>
			 ',
				'warning',
				true,
				ArubaFeConstants::ARUBA_FE_NOTICES['missing_config_done']
			);
		}

		return false;
	}

	protected function checkConnectionState() {

		if ( ! $this->fe_options->getConnectionState() ) {

			$this->notice->addNoticeHtml(
				'
				 <p>' . esc_html__( 'Complete the configuration of the Aruba Electronic Invoicing plugin to immediately start importing orders into the invoicing service and automatically generate electronic invoices.', 'aruba-fatturazione-elettronica' ) . '</p>
				 <p><a href="' . esc_url( $this->aruba_fe_url ) . '" class="fe-btn"> ' . esc_html__( 'Activate now', 'aruba-fatturazione-elettronica' ) . '</a></p>
			 ',
				'warning',
				true,
				ArubaFeConstants::ARUBA_FE_NOTICES['missing_config']
			);

			return true;
		}

		return false;
	}

	protected function checkTaxConfigs() {

		global $wpdb;
        // @codingStandardsIgnoreStart
		$rates = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates`" );
        // @codingStandardsIgnoreEnd
		$hasError = false;

		foreach ( $rates as $rate ) {

			if ( empty( $rate->id_aruba ) ) {
				$hasError = true;
			}
			if ( $hasError ) {
				break;
			}
		}

		if ( ! $hasError ) {

			$countries = new Countries();

			$all          = array_keys( $countries->get_all_countries() );
			$taxClasses   = \WC_Tax::get_tax_classes();
			$taxClasses[] = '';

			foreach ( $all as $country ) {

				foreach ( $taxClasses as $taxClass ) {

					$hasRate = \WC_Tax::find_rates(
						array(
							'country'   => $country,
							'tax_class' => $taxClass,
						)
					);

					if ( ! $hasRate ) {
						$hasError = "[$country] - [$taxClass]";
					}

					if ( $hasError ) {
						break;
					}
				}

				if ( $hasError ) {
					break;
				}
			}
		}

		if ( $hasError ) {

			$html[] = '<p>' . esc_html__( 'Some rates are not configured correctly with the Aruba Electronic Invoicing plugin', 'aruba-fatturazione-elettronica' ) . '</p>';
			$html[] = '<p><a href="' . esc_url( $this->aruba_fe_url . '#taxes' ) . '" class="fe-btn"> ' . esc_html__( 'Configure now', 'aruba-fatturazione-elettronica' ) . '</a></p>';
			$this->notice->addNoticeHtml( implode( "\n", $html ), 'warning',true, ArubaFeConstants::ARUBA_FE_NOTICES['hide_tax_error']);

		}
	}

	protected function checkCurrency() {

		$current_currency = get_woocommerce_currency();

		if ( $current_currency !== ArubaFeConstants::ARUBA_FE_ALLOWED_CURRENCY ) {
			$this->notice->addNotice(
				esc_html__( 'You have set a currency other than €, the Aruba Electronic Invoicing plugin will create invoices in € without currency conversion', 'aruba-fatturazione-elettronica' ),
				'error',
				true,
				ArubaFeConstants::ARUBA_FE_NOTICES['hide_euro_error']
			);
		}
	}

	protected function checkPaymentMethods() {
		$wc_gateways = new \WC_Payment_Gateways();

		$payment_gateways = $wc_gateways->get_available_payment_gateways();

		$notices = array();

		foreach ( $payment_gateways as $gateway_id => $gateway ) {

			$code = $this->fe_options->getPaymentMethodCode( $gateway_id );

			if ( ! $code || $code == '*' ) {
                // translators: %s: string payment method.
                $notices [] = sprintf( esc_html__( 'You have added %s to your payment methods. Associate it with one of the payment methods provided by the e-Invoicing service to avoid invoicing errors.', 'aruba-fatturazione-elettronica' ), $gateway->get_title() );
			}
		}

		if ( $notices ) {
			$html[] = '<p>' . implode( '<br>', $notices ) . '</p>';
			$html[] = '<p><a href="' . esc_url($this->aruba_fe_url) . '" class="fe-btn"> ' . esc_html__( 'Configure now', 'aruba-fatturazione-elettronica' ) . '</a></p>';
			$this->notice->addNoticeHtml( implode( "\n", $html ), 'warning', true, ArubaFeConstants::ARUBA_FE_NOTICES['missing_payments_config'] );
		}
	}

	static $listOfIncomplatiblePlugins = array(
		'woo-piva-codice-fiscale-e-fattura-pdf-per-italia/dot4all-wc-cf-piva.php',
		'freeinvoice-api/freeinvoice-api.php',
		'easy-fattura-elettronica-free/easy-fattura-elettronica.php',
		'woo-fattureincloud/woo-fattureincloud.php',
		'woopop-electronic-invoice-free/index.php',
		'fattura24/fattura24.php',
		'partita-iva-per-fattura-elettronica/bootstrap.php',
		'winddoc/winddoc.php',
		'woocommerce-pdf-invoices-italian-add-on/woocommerce-pdf-italian-add-on.php',
		'wc-exporter-for-danea/wc-exporter-for-danea.php',
		'fatture-help-wc/fatture-help-wc.php',
		'fatture.help/fatture.help.php',
		'fatture-help-wc/fatture-help-wc.php',
		'woocommerce-services/woocommerce-services.php',
	);

	protected function registerIncompatiblePlugins() {

		self::$listOfIncomplatiblePlugins = apply_filters( 'aruba_fe_incompatible_plugins', self::$listOfIncomplatiblePlugins );

		$text = array();

		foreach ( self::$listOfIncomplatiblePlugins as $plugin ) {

			if ( is_plugin_active( $plugin ) ) {

				$t = explode( '/', $plugin );

				$text[] = ucfirst( str_replace( '-', ' ', array_shift( $t ) ) );

			}
		}

		if ( $text ) {

			$text = implode( ', ', $text );
            // translators: %s: plugin name.
            $this->notice->addNoticeHtml( sprintf( __( "Some of the plugins installed are not compatible with Aruba Electronic Invoicing. Uninstall %s plugins to correctly import orders and issue electronic invoices with Aruba's service", 'aruba-fatturazione-elettronica' ), $text ), 'error', true, ArubaFeConstants::ARUBA_FE_NOTICES['hide_incompatible'] );

		}
	}

	public static function hasIncompatiblePlugins() {

		self::$listOfIncomplatiblePlugins = apply_filters( 'aruba_fe_incompatible_plugins', self::$listOfIncomplatiblePlugins );

		foreach ( self::$listOfIncomplatiblePlugins as $plugin ) {

			if ( is_plugin_active( $plugin ) ) {

				$t = explode( '/', $plugin );

				$text = ucfirst( str_replace( '-', ' ', array_shift( $t ) ) );
                // translators: %s: plugin name.
				return wp_strip_all_tags( sprintf( __( "Some of the plugins installed are not compatible with Aruba Electronic Invoicing. Uninstall %s plugins to correctly import orders and issue electronic invoices with Aruba's service", 'aruba-fatturazione-elettronica' ), $text ) );

			}
		}

		return false;
	}
}
