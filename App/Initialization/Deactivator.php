<?php

/**
 * deactivation class
 **/

namespace ArubaFe\Initialization;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\RestApi\ArubaFeApiRestManager;
use ArubaFe\Admin\Tax\TaxBackup;
use WP_Error;

class Deactivator {


	public static function deactivator() {

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : false;

		$action = 'deactivate-plugin_' . ARUBA_FE_BN;

		if ( ! wp_verify_nonce( $nonce, $action ) ) {

			return false;

		}

		ArubaFeApiRestManager::getInstance()->disconnect();

		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_TOKEN, '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_TOKEN_EXPIRE, '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_TOKEN_LAST_REFRESH, '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_TOKEN_USERNAME, '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_TOKEN_TYPE, '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_DEFAULT_ZERO_RATE, '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_SENDFDATE, '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_FCDONE, '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_NOTICES['missing_config'], '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_NOTICES['missing_config_done'], '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_NOTICES['missing_payments_config'], '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_NOTICES['domain_config'], '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_NOTICES['hide_incompatible'], '' );
		CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_NOTICES['hide_euro_error'], '' );

		if ( wp_next_scheduled( ArubaFeConstants::ARUBA_FE_CRON_HOOK ) ) {

			$timestamp = wp_next_scheduled( ArubaFeConstants::ARUBA_FE_CRON_HOOK );
			wp_unschedule_event( $timestamp, ArubaFeConstants::ARUBA_FE_CRON_HOOK );

		}

		if ( wp_next_scheduled( ArubaFeConstants::ARUBA_FE_CRON_HOOK_ORDERS ) ) {

			$timestamp = wp_next_scheduled( ArubaFeConstants::ARUBA_FE_CRON_HOOK_ORDERS );
			wp_unschedule_event( $timestamp, ArubaFeConstants::ARUBA_FE_CRON_HOOK_ORDERS );

		}

		if ( isset( $_GET['mantain'] ) && $_GET['mantain'] == 1 ) {
			update_option( ArubaFeConstants::ARUBA_FE_KEEP_SETTINGS, 1 );
			return;
		}

		update_option( ArubaFeConstants::ARUBA_FE_KEEP_SETTINGS, 0 );

		CustomOptions::update_option( 'aruba_global_data', '' );

		$backupClass = new TaxBackup();
		$backupClass->fire()->restoreSavedTaxRate();
	}
}
