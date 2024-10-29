import { useState } from 'react';
import RadioComponents from '../commons/RadioComponent';
import { useTextContextProvider } from '../../Context/Text';

const DescriptionInvoice = ({ setArubaData, arubaData, register }) => {

	const radio_property = "description_invoice";

	const textDataProvider = useTextContextProvider();

	const radioDataInit = [
		{
			value: 'product_name_fe',
			label: textDataProvider.aruba_fe_product_name_fe,
			checked: (arubaData.global_data[radio_property] == 'product_name_fe') ? true : false,
		},
		{
			value: 'descriction_product_name_fe',
			label: textDataProvider.aruba_fe_descriction_product_name_fe,
			checked: (arubaData.global_data[radio_property] == 'descriction_product_name_fe') ? true : false,
		},
		{
			value: 'short_descriction_product_name_fe',
			label: textDataProvider.aruba_fe_short_descriction_product_name_fe,
			checked: (arubaData.global_data[radio_property] == 'short_descriction_product_name_fe') ? true : false,
		}
	];
	const [radioData, setRadioData] = useState(radioDataInit);



	return (

		<>

			<div className="block_create_invoice">
				<div className="block_create_invoice_title">
					<h3>{textDataProvider.aruba_fe_block_desc_title}</h3>
				</div>

			</div>
			<div className="block_create_invoice_content">
				<div>
					<p>{textDataProvider.aruba_fe_text_desc}</p>
				</div>
			</div>
			<RadioComponents radio_property={radio_property} register={register} radioData={radioData} setRadioData={setRadioData} setArubaData={setArubaData} arubaData={arubaData} />

		</>
	)
}

export default DescriptionInvoice;
