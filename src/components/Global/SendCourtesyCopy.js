import { useState } from 'react';

import RadioComponents from '../commons/RadioComponent';
import { useTextContextProvider } from '../../Context/Text';

const SendCourtesyCopy = ({ setArubaData, arubaData, register }) => {

	const radio_property = "send_coutesy_copy";

	const textDataProvider = useTextContextProvider();

	const radioDataInit = [
		{
			value: 'automatic_send_coutesy_copy',
			label: textDataProvider.aruba_fe_automatic_send_coutesy_copy,
			checked: (arubaData.global_data[radio_property] == 'automatic_send_coutesy_copy') ? true : false,
		},
		{
			value: 'not_send_coutesy_copy',
			label: textDataProvider.aruba_fe_not_send_coutesy_copy,
			checked: (arubaData.global_data[radio_property] == 'not_send_coutesy_copy') ? true : false,
		}
	];
	const [radioData, setRadioData] = useState(radioDataInit);



	return (

		<>

			<div className="block_create_invoice">
				<div className="block_create_invoice_title">
					<h3>{textDataProvider.aruba_fe_send_coutesy_copy}</h3>
				</div>
			</div>

			<RadioComponents radio_property={radio_property} register={register} radioData={radioData} setRadioData={setRadioData} setArubaData={setArubaData} arubaData={arubaData} />

		</>
	)
}

export default SendCourtesyCopy;
