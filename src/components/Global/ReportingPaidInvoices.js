import { useState } from 'react';
import RadioComponents from '../commons/RadioComponent';
import { useTextContextProvider } from '../../Context/Text';

const ReportingPaidInvoices = ({ setArubaData, arubaData, register }) => {

	const radio_property = "reporting_receipts_paid_invoices";
	const textDataProvider = useTextContextProvider();
	const radioDataInit = [
		{
			value: 'automatic_receipts_fe',
			label: textDataProvider.aruba_fe_automatic_receipts_fe,
			checked: (arubaData.global_data[radio_property] == 'automatic_receipts_fe') ? true : false,
		},
		{
			value: 'manual_receipts_fe',
			label: textDataProvider.aruba_fe_manual_receipts_fe,
			checked: (arubaData.global_data[radio_property] == 'manual_receipts_fe') ? true : false,
		}
	];

	const [radioData, setRadioData] = useState(radioDataInit);



	return (
		<>

			<div className="block_create_invoice">
				<div className="block_create_invoice_title">
					<h3>{textDataProvider.aruba_fe_block_paind_invoice_title}</h3>
				</div>
			</div>

			<div className="block_create_invoice_content">
				<div>
					<p>{textDataProvider.aruba_fe_block_paind_invoice_text}</p>
				</div>
			</div>

			<RadioComponents radio_property={radio_property} register={register} radioData={radioData} setRadioData={setRadioData} setArubaData={setArubaData} arubaData={arubaData} />

		</>
	)
}

export default ReportingPaidInvoices;
