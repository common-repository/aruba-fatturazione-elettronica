<?php


namespace ArubaFe\Admin\WcBackend\BuildComplexTaxRate;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\Tax\Countries;
use ArubaFe\Admin\Tax\TaxUtils;
use ArubaFe\Admin\WcBackend\BuildSimpleTaxRate\BuildSimpleTaxRate;


class BuildComplexTaxRate
{
    private $tax_complex_data_to_update = [];
    private $decompose_array = [];
    private $taxUtilsClass;
    private $countriesClass;
    private $all_countries;
    private $aruba_tax_simple_data;

    private $complexManage;
    //private $eu_countries;
    //private $extra_eu_countries;
    public function __construct($new_tax_complex_data, $complexManage = [])
    {

        $this->complexManage = $complexManage;
        $this->taxUtilsClass = new TaxUtils(); // for CRUD
        $this->countriesClass = new Countries(); // for get code country
      //  $this->aruba_tax_simple_data = [];//$new_tax_simple_data;
        $this->all_countries = $this->countriesClass->get_all_countries();
        $this->tax_complex_data_to_update = $new_tax_complex_data;

        if ($this->decompose_array()) {
            $this->register_complex_tax_rate();
        }
    }


    private function register_complex_tax_rate()
    {

        $activeNations = [];

        foreach ($this->decompose_array as $keys => $tax_to_update) {

            $class = ($tax_to_update['class'] == 'standard') ? "" : $tax_to_update['class'];

            if(!in_array($class,$this->complexManage))
                continue;


            $country = $tax_to_update['country'];
            $value = $tax_to_update['value'];
            $code = $tax_to_update['code'];
            $to_delete = $tax_to_update['to_delete'];
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
                    if ($to_delete) {
                        // rebuild simple tax
                        //echo "<pre>";var_dump('delete' . $args['tax_rate_country']);echo "</pre>";
                        // inside updaterubaIdInWoocommerceTaxRates
                        $this->taxUtilsClass->restore_default_tax_rate_by_term($args, $tax_rate_id, $id_aruba);
                    } else {
                        //echo "<pre>";var_dump('update' . $args['tax_rate_country']);echo "</pre>";
                        $this->taxUtilsClass->updateTaxRate($tax_rate_id, $args['tax_rate']);
                        $this->taxUtilsClass->updaterubaIdInWoocommerceTaxRates($tax_rate_id, $id_aruba);
                    }
                } else {

                    if (!$to_delete) {
                        //echo "<pre>";var_dump('insert ' . $args['tax_rate_country']);echo "</pre>";
                        $tax_rate_id = $this->taxUtilsClass->insertTaxRate($args);
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

    private function decompose_array()
    {

        if (!empty($this->tax_complex_data_to_update)) {
            foreach ($this->tax_complex_data_to_update as $key => $value) {

                $parts = explode('_', $key); // [1] = rate class ; [2] = country

                $extract_value = "";

                $id_aruba = "";

                if ($value == '') {
                    $extract_value = '';

                } else {
                    $extract_value = explode("::", $value)[0];
                    $id_aruba = explode("::", $value)[1];
                }

                $this->decompose_array[] = [
                    'to_delete' => "" == $value,
                    'class' => $parts[1],
                    'country' => $parts[2],
                    'value' => $extract_value,
                    'id_aruba' => $id_aruba,
                    'code' => [$parts[2] => $parts[2]]//$this->get_code_country($parts[2]),
                ];
            };

            return true;
        } else {
            return false;
        }
    }

}
