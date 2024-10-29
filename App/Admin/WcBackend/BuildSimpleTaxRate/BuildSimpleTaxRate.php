<?php

namespace ArubaFe\Admin\WcBackend\BuildSimpleTaxRate;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\Tax\Countries;
use ArubaFe\Admin\Tax\TaxUtils;
use ArubaFe\Admin\Traits\ArubaFeLogTrait;

class BuildSimpleTaxRate
{
    use ArubaFeLogTrait;

    /**
     * @var mixed 3 possibili valori it,ue,extra-ue
     */
    private $tax_simple_data_to_update = [];
    //private $old_tax_simple_data = [];
    private $decompose_array = [];
    private $taxUtilsClass;
    private $countriesClass;
    private $all_countries;
    private $eu_countries;
    private $extra_eu_countries;

    private $simpleManage;

    /**
     * Constructs a new object with the given tax simple data to update.
     *
     * @param mixed $new_tax_simple_data the tax simple data to update
     * @return None
     * @throws None
     */
    public function __construct($new_tax_simple_data, $simpleManage = [])
    {
        $this->simpleManage = $simpleManage;
        $this->taxUtilsClass = new TaxUtils(); // for CRUD
        $this->countriesClass = new Countries(); // for get code country

        /**
         * array associativo [iso] => paese
         */

        $this->eu_countries = $this->countriesClass->get_active_eu_countries();
        $this->extra_eu_countries = $this->countriesClass->get_active_extra_eu_countries();

        /**
         * array associativo [iso] => paese
         */

        $this->tax_simple_data_to_update = $new_tax_simple_data;

        if ($this->decompose_array()) {
            $this->process_simple_tax_rate();
        }
    }


    /**
     * Processes a simple tax rate for the given decomposed array.
     *
     * @throws Exception if there is an error processing the tax rate.
     */
    private function process_simple_tax_rate()
    {

        $activeNations = [];

        foreach ($this->decompose_array as $key => $tax_to_update) {

            $class = ($tax_to_update['class'] == 'standard') ? "" : $tax_to_update['class'];

            if (($class === "" && !in_array("", $this->simpleManage)) && !in_array($class, $this->simpleManage))
                continue;


            $value = $tax_to_update['value'];
            $code = $tax_to_update['code'];
            $tax_name = "Iva";
            $tax_priority = "1";
            $tax_compound = "";
            $tax_shipping = "1";
            $tax_order_rate = "";
            $tax_rate_state = "";
            $id_aruba = $tax_to_update['id_aruba'];


            $args = [];
            $tax_rates_bly_class = \WC_Tax::get_rates_for_tax_class($class);


            foreach ($code as $key => $tax) {
                $activeNations[$class][] = $key;
                // check if tax rate exists
                $check_tax_rate = array_filter($tax_rates_bly_class, function ($value) use ($key) {
                    return $value->tax_rate_country == $key;
                });

                $args = [
                    //"tax_rate_id"       => ( $tax ) ? $tax : "", //IT
                    "tax_rate_country" => ($key) ? $key : "", //IT
                    "tax_rate_state" => ($tax_rate_state) ? $tax_rate_state : "", // a tutti vuoto
                    "tax_rate" => ($value) ? $value : "", // 24
                    "tax_rate_name" => ($tax_name) ? $tax_name : "", // nome imposta
                    "tax_rate_priority" => ($tax_priority) ? $tax_priority : "",
                    "tax_rate_compound" => ($tax_compound) ? $tax_compound : "",
                    "tax_rate_shipping" => ($tax_shipping) ? $tax_shipping : "",
                    "tax_rate_order" => ($tax_order_rate) ? $tax_order_rate : "",
                    "tax_rate_class" => ($class) ? $class : "",
                ];


                if (!empty($check_tax_rate)) {
                    $tax_rate_id = reset($check_tax_rate)->tax_rate_id;
                    $this->taxUtilsClass->updateTaxRate($tax_rate_id, $args['tax_rate']);
                    $this->taxUtilsClass->updaterubaIdInWoocommerceTaxRates($tax_rate_id, $id_aruba);

                } else {

                    $tax_rate_id = $this->taxUtilsClass->insertTaxRate($args);

                    if ($tax_rate_id) {

                        $this->taxUtilsClass->updaterubaIdInWoocommerceTaxRates($tax_rate_id, $id_aruba);
                    }
                }
            }

        }

        ##RIMUOVERE QUELLE NON ATTIVE

        foreach ($activeNations as $class => $countries) {

            $tax_rates_bly_class = \WC_Tax::get_rates_for_tax_class($class);

            foreach ($tax_rates_bly_class as $tax_rate) {

                if (!in_array($tax_rate->tax_rate_country, $countries))
                    $this->taxUtilsClass->deleteTaxRate($tax_rate->tax_rate_id);

            }

        }


    }

    /**
     * Decomposes an array of tax values to update into individual parts for easier handling.
     *
     * @throws Some_Exception_Class if there is an error with the decomposition
     */
    private function decompose_array()
    {
        if (!empty($this->tax_simple_data_to_update)) {


            foreach ($this->tax_simple_data_to_update as $key => $value) {
                $parts = explode('_', $key); // [1] = rate class ; [2] = country

                $extract_value = "";

                $id_aruba = "";

                if ($value == '') {
                    $extract_value = '*';

                } else {
                    $extract_value = explode("::", $value)[0];
                    $id_aruba = explode("::", $value)[1];
                }

                $this->decompose_array[] = [

                    'class' => $parts[1],
                    'country' => $parts[2],
                    'value' => $extract_value,
                    'id_aruba' => $id_aruba,
                    'code' => $this->get_code_country($parts[2]),
                ];
            };

            return true;

        } else {

            return false;

        }
    }

    /**
     * Retrieves the country code for a given country name.
     *
     * @param string $value The name of the country to retrieve the code for.
     *
     * @return mixed The country code for the given country name, or an array of codes if $value is 'Italia'.
     */
    private function get_code_country($value)
    {

        if ($value == 'it') {
            return ["IT" => "IT"];
        }
        if ($value == 'extra-ue') {
            return $this->extra_eu_countries;
        }
        if ($value == 'ue') {
            return $this->eu_countries;
        }
    }


}
