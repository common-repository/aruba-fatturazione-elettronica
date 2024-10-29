import { useState } from 'react';
import RadioComponents from '../commons/RadioComponent';
import { useTextContextProvider } from '../../Context/Text';

const UpdateDataCustomer = ({ setArubaData, arubaData, register }) => {

	const radio_property = "update_data_customer";

	const textDataProvider = useTextContextProvider();

	const radioDataInit = [
		{
			value: 'automatically_update_data_customer',
			label: textDataProvider.aruba_fe_automatically_update_data_customer,

			checked: (arubaData.global_data[radio_property] == 'automatically_update_data_customer') ? true : false,
		},
		{
			value: 'not_automatically_update_data_customer',
			label: textDataProvider.aruba_fe_not_automatically_update_data_customer,
			checked: (arubaData.global_data[radio_property] == 'not_automatically_update_data_customer') ? true : false,
		}
	];

	const [radioData, setRadioData] = useState(radioDataInit);

	return (

		<>

			<div className="block_create_invoice">
				<div className="block_create_invoice_title">
					<h3>{textDataProvider.aruba_fe_update_data_customer}</h3>
				</div>
				<div className="block_update_data_customer_content">
					<p>{textDataProvider.aruba_fe_update_data_customer_desc}</p>
				</div>
			</div>

			<RadioComponents radio_property={radio_property} register={register} radioData={radioData} setRadioData={setRadioData} setArubaData={setArubaData} arubaData={arubaData} />

		</>
	)
}

export default UpdateDataCustomer;
