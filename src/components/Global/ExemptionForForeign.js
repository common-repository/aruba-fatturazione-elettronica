import React, { useState, useEffect } from 'react';
import RadioComponents from '../commons/RadioComponent';
import { useTextContextProvider } from '../../Context/Text';
import Exemption from '../Exemption';

const ExemptionForForeign = ({ setArubaData, arubaData, register }) => {


	const radio_property = "exemption_for_foreign";

	const textDataProvider = useTextContextProvider();

	const radioDataInit = [
		{
			value: 'apply_exemption_for_foreign',
			label: textDataProvider.aruba_fe_apply,
			checked: (arubaData.global_data[radio_property] == 'apply_exemption_for_foreign') ? true : false,
		},
		{
			value: 'not_apply_exemption_for_foreign',
			label: textDataProvider.aruba_fe_noapply,
			checked: (arubaData.global_data[radio_property] == 'not_apply_exemption_for_foreign') ? true : false,
		}
	];
	const [radioData, setRadioData] = useState(radioDataInit);



	return (
		<>

			<div className="block_create_invoice">
				<div className="block_create_invoice_title">
					<h3>{textDataProvider.aruba_fe_exemption}</h3>
				</div>
				<div className="block_update_data_customer_content">
					<p>{textDataProvider.aruba_fe_exemption_text}</p>
				</div>
			</div>

			<RadioComponents radio_property={radio_property} register={register} radioData={radioData} setRadioData={setRadioData} setArubaData={setArubaData} arubaData={arubaData} />

			{arubaData.global_data[radio_property] === 'apply_exemption_for_foreign' && <Exemption arubaData={arubaData} register={register} />}
		</>
	)
}

export default ExemptionForForeign;
