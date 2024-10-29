import React, { useState, useEffect } from 'react';
import DoubleButtons from './DoubleButtons';
import DeleteTaxRateClass from "./deleteTaxClass/DeleteTaxRateClass";

import classNames from "classnames";

import { useTextContextProvider } from '../../../Context/Text';

const RateComponent = ({ rate_type, register, arubaData, saveButton, onSubmit, handleSubmit, setTaxClasses, taxRateApi, taxClasses }) => {

	// global data

	const className = classNames({
		'border_top': (rate_type != "") ? true : false,
		'mt-10': (rate_type != "") ? true : false,
	});

	const classNameHeader = classNames({
		'header_tax_tate_container': true,
		'header_tax_rate': (rate_type != "") ? true : false,
	});

	const textDataProvider = useTextContextProvider();

	return (
		<>
			<div className={className}>

				<div className={classNameHeader}>

					<div className="block_create_invoice_title">
						<h3>{textDataProvider.aruba_fe_aliquota} <b>{(rate_type == "") ? "Standard" : rate_type}</b></h3>
					</div>

					<div className='delete_tax_rate_wrapper'>
						<DeleteTaxRateClass setTaxClasses={setTaxClasses} rate_type={rate_type} />
					</div>

				</div>

				<div className="block_create_invoice_content">
					<div>
						<p>{textDataProvider.aruba_fe_rate_config_desc}</p>
						<a target='_blank' href={textDataProvider.aruba_fe_set_invoice_text_4_link}>{textDataProvider.aruba_fe_rate_config_desc_2}</a>
					</div>
				</div>

			</div>

			<DoubleButtons taxClasses={taxClasses} taxRateApi={taxRateApi} handleSubmit={handleSubmit} onSubmit={onSubmit} saveButton={saveButton} arubaData={arubaData} rate_type={rate_type} register={register} />



		</>
	)
}

export default RateComponent;
