<?php

namespace ArubaFe\Admin\Tax;
if (!defined('ABSPATH')) die('No direct access allowed');



use WooCommerce;

class Countries
{

    protected $allowedCountries = [];

	/**
	 * Returns the European Union countries based on the value of $is_eu parameter.
	 *
	 * @param bool $is_eu If true, returns EU countries, else returns non-EU countries.
	 * @return array Returns an array of countries based on the value of $is_eu parameter.
	 */
	public function get_european_union_countries($is_eu = true)
	{

		$all = $this->get_all_countries(); // 248 ["AT"]=> "Austria"

		if ($is_eu) {

			return $this->get_ue_countries($all);

        } else {

			return $this->get_extra_ue_countries($all);
		}
	}


	/**
	 * Removes all European Union countries from an array of countries.
	 *
	 * @param array $all The array of countries to filter.
	 * @return array The filtered array of countries.
	 */
	public function get_extra_ue_countries($all)
	{
		$wc_Countries = new \WC_Countries(); //["AT"]=> "Austria"
		$eu_country = $wc_Countries->get_european_union_countries(); // [0] => "IT"
		foreach ($all as $key => $value) {
			if (in_array($key, $eu_country)) {
				unset($all[$key]);
			}
		};

		return $all;
	}

	/**
	 * Returns an array of European Union countries from the input array.
	 *
	 * @param array $all An array of countries to filter
	 * @return array The filtered array of EU countries
	 */
	public function get_ue_countries($all)
	{
		$wc_Countries = new \WC_Countries(); //["AT"]=> "Austria"
		$eu_country = $wc_Countries->get_european_union_countries(); // [0] => "IT"
		foreach ($all as $key => $value) {
			if (!in_array($key, $eu_country)) {
				unset($all[$key]);
			}
		};

		return $all;
	}


	/**
	 * Retrieves all available countries using the WooCommerce Countries class.
	 *
	 * @return array List of all available countries.
	 */
	public function get_all_countries()
	{
        return Wc()->countries->get_allowed_countries();
	}

    public function get_active_eu_countries(){

        $this->allowedCountries = WooCommerce::instance()->countries->get_allowed_countries();

        $eu = WC()->countries->get_european_union_countries();

        $euCountries = [];

        foreach ($eu as $euCounty){
            if($euCounty === 'IT')
                continue;
            if(array_key_exists($euCounty,$this->allowedCountries))
                $euCountries[$euCounty] = $this->allowedCountries[$euCounty];
        }

        return $euCountries;

    }

    public function get_active_extra_eu_countries(){

        $allowedCountries = WooCommerce::instance()->countries->get_allowed_countries();

        $eu = array_keys($this->get_active_eu_countries());

        $noEuCountries = [];

        foreach ($allowedCountries as $iso => $country){

            if(!in_array($iso,$eu) && $iso !== 'IT')
                $noEuCountries[$iso] = $country;

        }

        return $noEuCountries;

    }

}
