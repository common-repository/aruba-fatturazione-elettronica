<?php
namespace ArubaFe\Admin\WcBackend;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\WcBackend\BuildComplexTaxRate\BuildComplexTaxRate;
use ArubaFe\Admin\WcBackend\BuildSimpleTaxRate\BuildSimpleTaxRate;
use PHPStan\Type\VoidType;

class InitWcBackend
{
	protected $aruba_global_data = []; // global data after updated
	protected $old_aruba_global_data = []; // old global data for compare and fire or not updates tax rates
	protected $tax_simple_data = []; // simple tax rates array
	protected $tax_complex_data = []; // complex tax rates array


	/**
	 * Initializes the object with the given old global Aruba data and updates it if there are changes in the new data.
	 *
	 * @param datatype $old_aruba_global_data The old global Aruba data.
	 */
	protected function init($old_aruba_global_data)
	{
		// get old global aruba data and update only is change found in aruba_global_data
		$this->old_aruba_global_data = $old_aruba_global_data;

		$this->getRequiredData();
	}

	/**
	 * Retrieves and compares new Aruba data with old data after an update.
	 *
	 * @throws Some_Exception_Class description of exception
	 */
	private function getRequiredData()
	{
		$this->aruba_global_data = CustomOptions::get_option('aruba_global_data');
		$this->compare_aruba_tax_data();
	}

	/**
	 * Compares the old and new Aruba tax data and builds the simple and complex tax rates if there is a difference.
	 *
	 * @param array $old_aruba_global_data The old Aruba global tax data.
	 */
	private function compare_aruba_tax_data()
	{

        $taxMethods = $this->aruba_global_data['tax_config'];

        $ativeTaxClasses = \WC_Tax::get_tax_classes();
        array_unshift($ativeTaxClasses, '');
        $simpleManage = [];
        $complexManage = [];

        foreach ($taxMethods as $key => $method){

            $class = str_replace('tax_method_','',$key);

            if(!in_array($class,$ativeTaxClasses))
                continue;

            if($method === 'simple')
                $simpleManage[] = $class;
            elseif($method === 'complex')
                $complexManage[] = $class;
        }


		$new_tax_simple_data = $this->aruba_global_data["tax_simple_data"];

        if ($new_tax_simple_data && $simpleManage) {

			new BuildSimpleTaxRate($new_tax_simple_data,$simpleManage);
		}

		$new_tax_complex_data = $this->aruba_global_data["tax_complex_data"];

        if ( $new_tax_complex_data && $complexManage) {
			new BuildComplexTaxRate($new_tax_complex_data, $complexManage);
		}
	}
}
