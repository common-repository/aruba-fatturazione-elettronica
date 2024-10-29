import React, { useState, useEffect } from 'react';
import classNames from "classnames";
import TableTaxChooseComponent from './tableTaxChooseComponent/TableTaxChooseComponent';
import TableTaxSimpleComponent from './tableTaxSimpleComponent/TableTaxSimpleComponent';
import { useTextContextProvider } from '../../../Context/Text';
import { useStateContextProvider } from '../../../Context/State';


const DoubleButtons = ({ rate_type, register, arubaData, saveButton, onSubmit, handleSubmit, taxRateApi, taxClasses }) => {



	let taxChoses = 'simple';

	if (arubaData.tax_config[`tax_method_${rate_type}`] && arubaData.tax_config[`tax_method_${rate_type}`] == 'complex') {

		taxChoses = 'complex';

	}

	const [taxView, setTaxView] = useState(taxChoses);

	const textDataProvider = useTextContextProvider();

	const { formSetValue } = useStateContextProvider();

	const className = classNames({
		button: true,
		'button-large': true
	});





	const handlerOnClick = (e, value) => {
		e.stopPropagation();
		setTaxView(value);
	};


	useEffect(() => {
		formSetValue(`tax_method_${rate_type}`, taxView);
	}, [taxView]);



	return (
		<>
			<div className='wrapper_tax_button mb-10 mt-10'>
				<a onClick={(e) => { handlerOnClick(e, 'simple') }} className={className + (taxView == "simple" ? ' button-selected' : '')}>{textDataProvider.aruba_fe_simple_version}</a>
				<a onClick={(e) => { handlerOnClick(e, 'complex') }} className={className + (taxView == "complex" ? ' button-selected' : '')}>{textDataProvider.aruba_fe_complex_version}</a>
				<input type='hidden' {...register(`tax_method_${rate_type}`, { required: true, value: taxView })} />
			</div>

			{(taxView == "simple") && <TableTaxSimpleComponent taxClasses={taxClasses} tax_rate_api={taxRateApi} arubaData={arubaData} rate_type={rate_type} register={register} />}

			{(taxView == "complex") && <TableTaxChooseComponent taxClasses={taxClasses} tax_rate_api={taxRateApi} handleSubmit={handleSubmit} onSubmit={onSubmit} saveButton={saveButton} arubaData={arubaData} rate_type={rate_type} register={register} />}
		</>
	)
}

export default DoubleButtons;
