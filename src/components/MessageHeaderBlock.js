import { useTextContextProvider } from '../Context/Text';

const MessageHeaderBlock = ({ arubaData, vatCode }) => {

	const textDataProvider = useTextContextProvider();



	return (
		<>
			<div className="block_config_invoice">
				<div className="block_config_invoice_title">
					<h3>{textDataProvider.aruba_fe_section_1}</h3>
				</div>
				<div className="block_config_invoice_message">

					<div className="custom_notice custom_notice-info">
						<p dangerouslySetInnerHTML={{ __html: textDataProvider.aruba_fe_automatic_create_fe.replace('[PIVA]', `<b>${vatCode}</b>`) }} />
					</div>

				</div>
			</div>
		</>
	)
}

export default MessageHeaderBlock;
