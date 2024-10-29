import { useState, useEffect } from 'react';
import RadioComponents from '../commons/RadioComponent';
import { useTextContextProvider } from '../../Context/Text';

const StateOrderInvoice = (props) => {

	// global data
	const { setArubaData, arubaData, register } = props;

	const [keyAuto, setKeyAuto] = useState();

	useEffect(() => {

		if (arubaData.global_data.create_invoice == 'manual_create_fe') {

			setKeyAuto('_manual');

		} else if (arubaData.global_data.send_invoice == 'automatic_send_fe') {

			setKeyAuto('_auto');

		} else {

			setKeyAuto('');

		}

	}, [arubaData.global_data.send_invoice, arubaData.global_data.create_invoice]);

	const textDataProvider = useTextContextProvider();

	const radio_property = "order_state";

	const tooltipsMEssage = [

		textDataProvider.aruba_fe_order_state_tt_1,
		textDataProvider.aruba_fe_order_state_tt_2,
		textDataProvider.aruba_fe_order_state_tt_3,

	];

	const radioDataInit = [
		{
			value: 'state_order_complete',
			label: textDataProvider.aruba_fe_state_order_complete,
			checked: (arubaData.global_data[radio_property] == 'state_order_complete') ? true : false,
			className: 'status-completed',
		},
		{
			value: 'state_order_processing',
			label: textDataProvider.aruba_fe_state_order_processing,
			checked: (arubaData.global_data[radio_property] == 'state_order_processing' || arubaData.global_data[radio_property] == 'state_order_pending') ? true : false,
			className: 'status-processing',
		},
		/*{
			value: 'state_order_pending',
			label: textDataProvider.aruba_fe_state_order_pending,
			checked: (arubaData.global_data[radio_property] == 'state_order_pending') ? true : false,
			className: 'status-on-hold',
		},*/
	];

	const [radioData, setRadioData] = useState(radioDataInit);

	return (
		<>

			<div className="block_create_invoice">
				<div className="block_create_invoice_title">
					<h3>{textDataProvider[`aruba_fe_block_state_create_invoice_title${keyAuto}`]}</h3>
				</div>
			</div>
			<div className="block_create_invoice_content">
				<div>
					<p>{textDataProvider[`aruba_fe_block_state_create_invoice_content${keyAuto}`]}</p>
				</div>
			</div>
			<div className='woocommerce-states'>
				<RadioComponents tooltipsMEssage={tooltipsMEssage} tooltip={true} radio_property={radio_property} register={register} radioData={radioData} setRadioData={setRadioData} setArubaData={setArubaData} arubaData={arubaData} />
			</div>
		</>
	)
}

export default StateOrderInvoice;
