<?php

namespace ArubaFe\Admin\Tax;
if (!defined('ABSPATH')) die('No direct access allowed');


class TaxBackup
{
    private $all_tax_rates = [];
    private $wc_tax_rates_backup = [];
    private $all_tax_classes = [];

    public function __construct()
    {
    }


    public function fire()
    {
        // prelevare le tax class
        $tax_classes = \WC_Tax::get_tax_classes(); // Retrieve all tax classes.

        $this->all_tax_classes = \WC_Tax::get_tax_rate_classes();

        if (!in_array('', $tax_classes)) { // Make sure "Standard rate" (empty class name) is present.
            array_unshift($tax_classes, '');
        }


        foreach ($tax_classes as $tax_class) { // For each tax class, get all rates.
            $taxes = \WC_Tax::get_rates_for_tax_class($tax_class);
            $this->all_tax_rates[$tax_class] = $taxes;
        }

        return $this;
    }


    public function getSavedTaxRata()
    {
        return unserialize(get_option('wc_tax_rates_backup'));
    }

    /**
     * Salviamo i dati di backup
     *
     * @return $this
     */
    public function saveTaxRateDataBackup()
    {

        update_option('aruba_fe_wc_tax_rates_classes_backup', serialize($this->all_tax_classes));

        update_option('aruba_fe_wc_tax_rates_backup', serialize($this->all_tax_rates));

        return $this;
    }

    public function restoreSavedTaxRate()
    {
        global $wpdb;

        $cache_key = 'tax-rate-classes';
        wp_cache_delete($cache_key);

        $taxClasses = get_option('aruba_fe_wc_tax_rates_classes_backup');
        /**
        * check for retro compatibility
         */
        if(!$taxClasses){
            $taxClasses = get_option('wc_tax_rates_classes_backup');
        }

        $this->wc_tax_classes = $taxClasses ? unserialize($taxClasses) : [];

        $taxRates = get_option('aruba_fe_wc_tax_rates_backup');
        /**
         * check for retro compatibility
         */
        if(!$taxRates){
            $taxRates = get_option('wc_tax_rates_backup');
        }

        $this->wc_tax_rates_backup = $taxRates ? unserialize($taxRates) : [];

        $reformattedTaxes = [];

        if (!empty($this->wc_tax_classes)) {

            foreach ($this->wc_tax_classes as $class) {
                $reformattedTaxes[$class->slug] = $class->name;
            }
        }

        $taxRatesId = [];

        $alreadyDone = [];

        $taxRatesClassesInitial = (array)\WC_Tax::get_tax_classes();

        foreach ($this->wc_tax_rates_backup as $key => $tax_class_array) {

            foreach ($tax_class_array as $tax_rate_id => $tax) {

                if (!(empty($tax->tax_rate_class) || in_array($tax->tax_rate_class, $taxRatesClassesInitial))) {

                    $name = isset($reformattedTaxes[$tax->tax_rate_class])
                        && !empty($reformattedTaxes[$tax->tax_rate_class]) ? $reformattedTaxes[$tax->tax_rate_class] : $tax->tax_rate_class;
                    \WC_Tax::create_tax_class($name, $tax->tax_rate_class);

                    $taxRatesClasses[] = $tax->tax_rate_class;
                } else {
                    $taxRatesClasses[] = $tax->tax_rate_class;
                }

                $args = [
                    // "tax_rate_id" => ($tax->tax_rate_id) ? $tax->tax_rate_id : "",
                    "tax_rate_country" => ($tax->tax_rate_country) ? $tax->tax_rate_country : "",
                    "tax_rate_state" => ($tax->tax_rate_state) ? $tax->tax_rate_state : "",
                    "tax_rate" => ($tax->tax_rate) ? $tax->tax_rate : "",
                    "tax_rate_name" => ($tax->tax_rate_country) ? $tax->tax_rate_name : "",
                    "tax_rate_priority" => ($tax->tax_rate_priority) ? $tax->tax_rate_priority : "",
                    "tax_rate_compound" => ($tax->tax_rate_compound) ? $tax->tax_rate_compound : "",
                    "tax_rate_shipping" => ($tax->tax_rate_shipping) ? $tax->tax_rate_shipping : "",
                    "tax_rate_order" => ($tax->tax_rate_order) ? $tax->tax_rate_order : "",
                    "tax_rate_class" => ($tax->tax_rate_class) ? $tax->tax_rate_class : "",
                ];

                if (!in_array("{$tax->tax_rate_country}::{$tax->tax_rate_class}", $alreadyDone)) {

                    $taxRatesId[] = $this->insertTaxRate($args);

                    $alreadyDone[] = "{$tax->tax_rate_country}::{$tax->tax_rate_class}";

                }

            }
        };


        wp_cache_delete($cache_key);

        $tax_classes = \WC_Tax::get_tax_classes(); // Retrieve all tax classes.


        foreach ($tax_classes as $tax_class) { // For each tax class, get all rates.

            $taxes = \WC_Tax::get_rates_for_tax_class($tax_class);

            foreach ($taxes as $tax) {

                if (!in_array($tax->tax_rate_id, $taxRatesId)) {
                    \WC_Tax::_delete_tax_rate($tax->tax_rate_id);
                }

            }

        }

        // @codingStandardsIgnoreStart

        $rates = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates`");

        // @codingStandardsIgnoreEnd

        foreach ($rates as $rate) {

            if (!in_array($rate->tax_rate_id, $taxRatesId)) {
                \WC_Tax::_delete_tax_rate($rate->tax_rate_id);
            }

        }

        foreach ((array)\WC_Tax::get_tax_classes() as $tax_class) {
            if (!in_array($tax_class, $taxRatesClasses)) {
                \WC_Tax::delete_tax_class_by('slug', $tax_class);

            }
        }

        return $this;
    }


    /**
     * Insert TAXT rate
     *
     * @param [type] $country_code
     * @param [type] $iva
     * https://woocommerce.com/document/setting-up-taxes-in-woocommerce/
     * @return int
     */
    public function insertTaxRate($args)
    {
        return \WC_TAX::_insert_tax_rate($args);
    }


    /**
     * Insert TAXT rate
     * DA TESTARE
     *
     * @param [type] $tax_rate_id
     * @param [type] $tax_rate_value
     * @return void
     */
    public function updateTaxRate($tax_rate_id, $tax_rate_value)
    {
        \WC_TAX::_update_tax_rate($tax_rate_id, ['tax_rate' => $tax_rate_value]);
    }


    /**
     * DELETE ALL TAX RATES
     *
     * @param [type] $tax_rate_id
     * @return void
     */
    public function deleteTaxRate()
    {

        foreach ($this->all_tax_rates as $key => $tax_class_array) {

            foreach ($tax_class_array as $tax_rate_id => $tax) {
                \WC_TAX::_delete_tax_rate($tax_rate_id);
            }
        };


    }

}
