<?php

namespace ArubaFe\Initialization;

use ArubaFe\Admin\Constants\ArubaFeOptionsList;
use ArubaFe\Admin\Tax\TaxBackup;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

class Uninstall {

	public static function startUninstall() {
		self::deleteColumsFromWooTaxRates();
		self::cleanData();
		self::cleanOptions();
	}

	private static function deleteColumsFromWooTaxRates() {
		global $wpdb;
		$column_name = 'id_aruba';
		// Check if columns exists
        // @codingStandardsIgnoreStart

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$wpdb->prefix}woocommerce_tax_rates LIKE %s", $column_name ) ) == $column_name ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_tax_rates DROP COLUMN id_aruba" );
		}

        // @codingStandardsIgnoreEnd

    }

	private static function cleanData() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        // @codingStandardsIgnoreStart

        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}aruba_fe"  );

        // @codingStandardsIgnoreEnd

    }

	private static function cleanOptions() {
		global $wpdb;

		$list = ArubaFeOptionsList::getAlloptions();
		/*remove all sensitive data*/

		foreach ( $list['_postmeta'] as $meta ) {

			delete_post_meta_by_key( $meta );

		}

		foreach ( $list['_woocommerce_order_itemmeta'] as $meta ) {

            // @codingStandardsIgnoreStart

			$order_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT order_item_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = %s", sanitize_text_field( $meta ) ) );

            // @codingStandardsIgnoreEnd

            foreach ( $order_ids as $order_id ) {
				wc_delete_order_item_meta( $order_id, $meta );
			}
		}
	}
}
