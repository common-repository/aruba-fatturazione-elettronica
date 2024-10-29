import { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import RateComponent from './rate/RateComponent';
import { useStateContextProvider } from '../../Context/State';
import { useTextContextProvider } from '../../Context/Text';
import AddTaxRateClass from './rate/addTaxClass/AddTaxRateClass';

const TaxConfiguration = ({ setArubaData, arubaData, register, saveButton, onSubmit, handleSubmit }) => {

	const [taxClasses, setTaxClasses] = useState([])
	const [taxRateApi, setTaxRateApi] = useState([]);

	const { feReducer, setAllowedCountries, setAllowedCountriesGlobal, setTaxRatesProvider } = useStateContextProvider();

	const textDataProvider = useTextContextProvider();

	useEffect(() => {

		const getTaxesData = async () => {

			try {

				const { tax_rate_api, data_taxes: { tax_classes }, success, allowed_countries_by_zone, allowed_countries } = await apiFetch({ path: '/aruba_fe/v1/get_taxes_complete', method: 'POST', data: { nonce: aruba_fe_data.nonce } });

				setAllowedCountries(allowed_countries_by_zone);

				setAllowedCountriesGlobal(allowed_countries);

				setTaxClasses(tax_classes);

				setTaxRateApi(tax_rate_api);

				setTaxRatesProvider(tax_rate_api);

			} catch (error) {

				console.error(tax_rate_api)

			}

			feReducer({ type: 'remove', payload: true, func: 'tax_loaded' });
		}

		getTaxesData();


	}, [])



	return (
		<>
			<div className='border_bottom '>
				<div className="block_create_invoice">
					<div className="block_create_invoice_title">
						<h3>{textDataProvider.aruba_fe_set_invoice_title}</h3>
					</div>
				</div>
				<div className="block_create_invoice_content">
					<div>
						<p>{textDataProvider.aruba_fe_set_invoice_text_1}</p>
						<p>{textDataProvider.aruba_fe_set_invoice_text_2}</p>
						<p>{textDataProvider.aruba_fe_set_invoice_text_3}</p>
						<p><a target='_blank' href={textDataProvider.aruba_fe_set_invoice_text_4_link}>{textDataProvider.aruba_fe_set_invoice_text_4}</a></p>
					</div>
				</div>
			</div>

			{taxClasses.length > 0 && taxRateApi.length > 0 && (
				taxClasses.map((tax,index) => {
					return (
						<div key={`tax_row_${index}`} className='tax-rate-row'>
							<RateComponent
								taxRateApi={taxRateApi}
								setTaxClasses={setTaxClasses}
								taxClasses={taxClasses}
								handleSubmit={handleSubmit}
								onSubmit={onSubmit}
								saveButton={saveButton}
								arubaData={arubaData}
								rate_type={tax}
								register={register}
							/>
						</div>)
				}))
			}
			{taxClasses.length > 0 && taxRateApi.length > 0 && (<AddTaxRateClass setTaxClasses={setTaxClasses} />)}
		</>
	)
}

export default TaxConfiguration;
