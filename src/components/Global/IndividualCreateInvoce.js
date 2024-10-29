import { useState } from 'react';
import RadioComponents from '../commons/RadioComponent';
import { useTextContextProvider } from '../../Context/Text';

const IndividualCreateInvoce = ({ setArubaData, arubaData, register }) => {

	const radio_property = "individual_create_invoce";

	const textDataProvider = useTextContextProvider();

	const radioDataInit = [
		{
			value: 'create_always_fe',
			label: textDataProvider.aruba_fe_create_always_fe,
			checked: (arubaData.global_data[radio_property] == 'create_always_fe') ? true : false,
		},
		{
			value: 'allow_choose_create_fe',
			label: textDataProvider.aruba_fe_allow_choose_create_fe,
			checked: (arubaData.global_data[radio_property] == 'allow_choose_create_fe') ? true : false,
		}
	];

	const [radioData, setRadioData] = useState(radioDataInit);

	return (

		<>

			<div className="block_create_invoice">
				<div className="block_create_invoice_title">
					<h3>{textDataProvider.aruba_fe_individual_create_invoice}</h3>
				</div>
			</div>

			<div className="block_create_invoice_content">
				<div>
					<p>{textDataProvider.aruba_fe_text_create_always}</p>
				</div>
			</div>
			<RadioComponents radio_property={radio_property} register={register} radioData={radioData} setRadioData={setRadioData} setArubaData={setArubaData} arubaData={arubaData} />

		</>
	)
}

export default IndividualCreateInvoce;
