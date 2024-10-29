import { useState } from 'react';
import RadioComponents from '../commons/RadioComponent';
import { useTextContextProvider } from '../../Context/Text';

const SendInvoice = ({ setArubaData, arubaData, register }) => {

	const textDataProvider = useTextContextProvider();

	const radio_property = "send_invoice";

	const radioDataInit = [
		{
			value: 'automatic_send_fe',
			label: textDataProvider.aruba_fe_automatic_send_fe,
			checked: (arubaData.global_data[radio_property] == 'automatic_send_fe') ? true : false,
		},
		{
			value: 'manual_send_fe',
			label: textDataProvider.aruba_fe_manual_send_fe,
			checked: (arubaData.global_data[radio_property] == 'manual_send_fe') ? true : false,
		},
	];

	const [radioData, setRadioData] = useState(radioDataInit);

	return (
		<>

			<div className="block_create_invoice">
				<div className="block_create_invoice_title">
					<h3>{textDataProvider.aruba_fe_block_send_invoice_title}</h3>
				</div>
			</div>
			<div className="block_create_invoice_content">
				<div>
					<p>{textDataProvider.aruba_fe_send_invoice_content}</p>
					<p>{textDataProvider.aruba_fe_send_invoice_content_1}</p>
				</div>
			</div>
			<RadioComponents radio_property={radio_property} register={register} radioData={radioData} setRadioData={setRadioData} setArubaData={setArubaData} arubaData={arubaData} />
		</>
	)
}

export default SendInvoice;
