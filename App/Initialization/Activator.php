<?php

/**
 * Activation class
 **/

namespace ArubaFe\Initialization;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}


use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\Migrations\ArubaFeMigrate;
use ArubaFe\Admin\Tax\TaxBackup;

class Activator {


	public static function activator() {

		/* salva dati all'attivazione */
		$backupClass = new TaxBackup();
		$bk          = $backupClass->fire()->saveTaxRateDataBackup();
		$keep        = get_option( ArubaFeConstants::ARUBA_FE_KEEP_SETTINGS, false );

		if ( $bk && ! $keep ) {
			$bk->deleteTaxRate();
		}

		self::create_custom_option_table();
		self::addColumsToWooTaxRates();

        $migrator = new ArubaFeMigrate();

        $migrator->checkVersions();

	}


	// /Applications/MAMP/htdocs/dynamic-content-node/wp-includes/option.php
	private static function create_custom_option_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}aruba_fe (
		option_id bigint(20) unsigned NOT NULL auto_increment,
		option_name varchar(191) NOT NULL default '',
		option_value longtext NOT NULL,
		autoload varchar(20) NOT NULL default 'no',
		PRIMARY KEY  (option_id),
		UNIQUE KEY option_name (option_name),
		KEY autoload (autoload)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	private static function addColumsToWooTaxRates() {
		global $wpdb;
		$table       = $wpdb->prefix . 'woocommerce_tax_rates';
		$column_name = 'id_aruba';

        // @codingStandardsIgnoreStart

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$wpdb->prefix}woocommerce_tax_rates LIKE %s", $column_name ) ) != $column_name ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$wpdb->query(
                "ALTER TABLE {$wpdb->prefix}woocommerce_tax_rates 
                       ADD COLUMN id_aruba VARCHAR(255) NULL DEFAULT NULL AFTER tax_rate_class"
            );
		}

        // @codingStandardsIgnoreEnd

    }
}
