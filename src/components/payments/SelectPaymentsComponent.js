import { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { useStateContextProvider } from '../../Context/State';
import { useTextContextProvider } from '../../Context/Text';
import { parseFeSting } from '../../TextHelper/TextParser';



const SelectComponent = ({ setArubaData, arubaData, register }) => {

	const [payments, setPayments] = useState([]);

	const [wcPayments, setWcPayments] = useState([]);

	const [defaultPaymentMethods, setDefaultPaymentMethods] = useState([]);

	const { feReducer } = useStateContextProvider();

	const textDataProvider = useTextContextProvider();

	const { feErrors: errors, setPreventUnload } = useStateContextProvider();

	useEffect(() => {

		const get_payments = async () => {

			apiFetch({ path: '/aruba_fe/v1/get_payments', method: 'GET' }).then((data) => {

				setDefaultPaymentMethods(data.default_payments);
				setPayments(data.avaiable_payments)
				setWcPayments(data.wc_avaiable_payments);
				feReducer({ type: 'remove', payload: true, func: 'payment_loaded' });

			});
		}

		get_payments();

	}, [])

	const checkSelectedPayment = (payment_slug, method_code) => {

		if (defaultPaymentMethods[payment_slug] && !arubaData.payments[`paymentsMethods_${payment_slug}`]) {
			return defaultPaymentMethods[payment_slug] == method_code;
		}

		return arubaData.payments[`paymentsMethods_${payment_slug}`] == method_code;

	}

	const handleChange = (e) => {

		setPreventUnload(true);

	}

	return (
		<>

			{(wcPayments.length == 0) && (
				<div className="custom_notice">
					<h3>{textDataProvider.aruba_fe_payments_loading}</h3>
				</div>
			)}

			{(wcPayments.length > 0) && (

				<table className="block_payments mb-10 table table-bordered">
					<thead>
						<tr className="block_payments_header header_payments header">

							<th className='wc-method'>{textDataProvider.aruba_fe_wc_payments}</th>
							<th className='fe-method'>{textDataProvider.aruba_fe_payments_fe}</th>

						</tr>
					</thead>

					<tbody className="block_payments_select_blocks">

						{wcPayments.map((wc_payment, index) => {

							return (

								<tr key={`tr_${index}`} className="block_payments_select cf-field cf-select payments">

									<td className="cf-field__head">
										<label>{wc_payment.name}</label>
									</td>

									<td className="cf-field__body">
										<select className={errors[`paymentsMethods_${wc_payment.slug}`] ? 'invalid' : ''} {...register(`paymentsMethods_${wc_payment.slug}`, {
											required: true,
											onChange: (e) => handleChange(e)
										})}>
											<option key={wc_payment.name} value="">{textDataProvider.aruba_fe_select}</option>
											{payments.map((method) => {
												return (
													<option key={method.code} selected={checkSelectedPayment(wc_payment.slug, method.code)} value={method.code}>{parseFeSting(method.name)}</option>
												)
											})}

										</select>
									</td>
								</tr>
							)


						})}
					</tbody>
				</table>)


			}

		</>
	)
}

export default SelectComponent;
