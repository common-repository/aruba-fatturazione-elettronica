import { useTextContextProvider } from '../../Context/Text';
import SelectPaymentsComponent from './SelectPaymentsComponent';
import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';
import HelpOutlineIcon from '@mui/icons-material/HelpOutline';
const Payments = ({ setArubaData, arubaData, register }) => {

	const textDataProvider = useTextContextProvider();

	return (
		<>
			<div className='border_bottom'>

				<div className="block_create_invoice ">
					<div className="block_create_invoice_title">
						<h3>{textDataProvider.aruba_fe_payments}</h3>
					</div>
					<div className="block_create_invoice_content">
						<div>
							{textDataProvider.aruba_fe_payments_tt}
						</div>
					</div>
				</div>
				<div className="block_create_invoice_content">
					<div>
						<p><a href={textDataProvider.aruba_fe_payments_text_link} target="_blank">{textDataProvider.aruba_fe_payments_text}</a></p>
					</div>
				</div>
				<SelectPaymentsComponent register={register} setArubaData={setArubaData} arubaData={arubaData} />
			</div>
		</>
	)
}

export default Payments;
