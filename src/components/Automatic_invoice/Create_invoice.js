import { useState } from 'react';
import { useTextContextProvider } from '../../Context/Text';
import RadioComponents from '../commons/RadioComponent';

const Create_invoice = (props) => {

	const textDataProvider = useTextContextProvider();

	const { setArubaData, arubaData, register } = props;

	const radio_property = "create_invoice";

	const tooltipsMEssage = [
		textDataProvider.aruba_fe_automatic_create_fe_tt,
		textDataProvider.aruba_fe_manual_create_fe_tt,
	]

	const radioDataInit = [
		{
			value: 'automatic_create_fe',
			label: textDataProvider.aruba_fe_automatic_create_fe_lb,
			checked: (arubaData.global_data[radio_property] == 'automatic_create_fe') ? true : false,
		},
		{
			value: 'manual_create_fe',
			label: textDataProvider.aruba_fe_manual_create_fe_lb,
			checked: (arubaData.global_data[radio_property] == 'manual_create_fe') ? true : false,
		},
	];

	const [radioData, setRadioData] = useState(radioDataInit);



	return (
		<>

			<div className="block_create_invoice">
				<div className="block_create_invoice_title">
					<h3>{textDataProvider.aruba_fe_block_create_invoice_title}</h3>
				</div>
			</div>

			<div className="block_create_invoice_content">
				<div>
					<p>{textDataProvider.aruba_fe_create_invoice_content}</p>
					<p>{textDataProvider.aruba_fe_create_invoice_content_1}</p>
				</div>
			</div>

			<RadioComponents tooltipsMEssage={tooltipsMEssage} tooltip={false} radio_property={radio_property} register={register} radioData={radioData} setRadioData={setRadioData} setArubaData={setArubaData} arubaData={arubaData} />

		</>
	)
}

export default Create_invoice;
