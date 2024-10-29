<?php

namespace ArubaFe\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}


class CustomOptions {
	public function __construct(){}

	/**
	 * Serializes in get function
	 *
	 * @param [type] $data
	 * @return void
	 */
	public static function maybe_serialize( $data ) {
		if ( is_array( $data ) || is_object( $data ) ) {
			return serialize( $data );
		}
		return $data;
	}

	/**
	 * Unserialize in get function
	 *
	 * @param [type] $data
	 * @return void
	 */
	public static function maybe_unserialize( $data ) {
		if ( is_serialized( $data ) ) { // Don't attempt to unserialize data that wasn't serialized going in.
			return @unserialize( trim( $data ) );
		}

		return $data;
	}

	/**
	 * Add custom option aruba fe
	 *
	 * @param [type] $option
	 * @param string $value
	 * @param string $deprecated
	 * @param string $autoload
	 * @return void
	 */
	public static function add_option( $option, $value = '', $deprecated = '', $autoload = 'no' ) {
		global $wpdb;

		if ( self::get_option( $option ) ) {
			return false;
		}

		if ( ! empty( $deprecated ) ) {
			_deprecated_argument( __FUNCTION__, '2.3.0' );
		}

		if ( is_scalar( $option ) ) {
			$option = trim( $option );
		}

		if ( empty( $option ) ) {
			return false;
		}

		if ( is_object( $value ) ) {
			$value = clone $value;
		}

		$value            = sanitize_option( $option, $value );
		$serialized_value = self::maybe_serialize( $value );
        // @codingStandardsIgnoreStart
		$result = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}aruba_fe (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) 
                                        ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
                $option,
                $serialized_value,
                $autoload
            )
        );
        // @codingStandardsIgnoreEnd

        if ( ! $result ) {
			return false;
		}
		return true;
	}


	/**
	 * @param $option
	 * @param $default_value
	 *
	 * @return false|mixed|null
	 */
	public static function get_option( $option, $default_value = false ) {
		global $wpdb;

		if ( is_scalar( $option ) ) {
			$option = trim( $option );
		}

		if ( empty( $option ) ) {
			return false;
		}

		if ( defined( 'WP_SETUP_CONFIG' ) ) {
			return false;
		}

		$table_name = $wpdb->prefix . 'aruba_fe';

        // @codingStandardsIgnoreStart
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM {$wpdb->prefix}aruba_fe WHERE option_name = %s LIMIT 1", $option ) );
        // @codingStandardsIgnoreEnd

        if ( $row == null || ! $row->option_value ) {  // nul not exist
			return false;
		}

		return self::maybe_unserialize( $row->option_value );
	}


	/**
	 * Delete custom option
	 *
	 * @param [type] $option
	 * @return void
	 */
	public static function delete_option( $option ) {
		global $wpdb;

		if ( is_scalar( $option ) ) {
			$option = trim( $option );
		}

		if ( empty( $option ) ) {
			return false;
		}

		wp_protect_special_option( $option );

		$table_name = $wpdb->prefix . 'aruba_fe';

        // @codingStandardsIgnoreStart

        $result     = $wpdb->delete( $table_name, array( 'option_name' => $option ) );

        // @codingStandardsIgnoreEnd

        if ( $result ) {
			return true;
		}

		return false;
	}

	/**
	 * Update custom option aruba fe
	 *
	 * @param [type] $option
	 * @param [type] $value
	 * @param [type] $autoload
	 * @return void
	 */
	public static function update_option( $option, $value, $autoload = null ) {
		global $wpdb;

		if ( is_scalar( $option ) ) {
			$option = trim( $option );
		}

		if ( empty( $option ) ) {
			return false;
		}

		if ( is_object( $value ) ) {
			$value = clone $value;
		}

		$value     = sanitize_option( $option, $value );
		$old_value = self::get_option( $option );

		if ( $value === $old_value || maybe_serialize( $value ) === maybe_serialize( $old_value ) ) {
			return false;
		}

		$serialized_value = maybe_serialize( $value );

		$update_args = array(
			'option_value' => $serialized_value,
		);
		$table_name  = $wpdb->prefix . 'aruba_fe';

        // @codingStandardsIgnoreStart

        $result      = $wpdb->update( $table_name, $update_args, array( 'option_name' => $option ) );

        // @codingStandardsIgnoreEnd

        if ( ! $result ) {
			return false;
		}
		return true;
	}
}
