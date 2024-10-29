
import React, { useState, useEffect } from 'react';
import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';
import HelpOutlineIcon from '@mui/icons-material/HelpOutline';
import { useStateContextProvider } from '../../../../Context/State';
import { useTextContextProvider } from '../../../../Context/Text';
import { parseFeSting } from '../../../../TextHelper/TextParser';
const TableTaxSimpleComponent = (props) => {

	const { register, rate_type, arubaData, tax_rate_api, taxClasses } = props;
	const rowsPerPage = 6;
	const rate_type_selected = (rate_type == "") ? "standard" : rate_type;
	const register_name = rate_type_selected
	const [countries, setCountries] = useState([]);

	const { allowedCountries } = useStateContextProvider();
	const [simple_tax_rows_country, setSimple_tax_rows_country] = useState([])

	const textDataProvider = useTextContextProvider();

	useEffect(() => {

		const list = [];

		if (allowedCountries['IT'].length) {
			list.push({ state: "it", register_name: "taxClass_" + register_name + "_" + 'it', label: textDataProvider.aruba_fe_italia })
		}

		if (Object.keys(allowedCountries['EU']).length > 0) {
			list.push({ state: "ue", register_name: "taxClass_" + register_name + "_" + 'ue', label: textDataProvider.aruba_fe_ue })
		}

		if (Object.keys(allowedCountries['EXTRA_EU']).length > 0) {
			list.push({ state: "extra-ue", register_name: "taxClass_" + register_name + "_" + 'extra-ue', label: textDataProvider.aruba_fe_extraue })
		}

		setSimple_tax_rows_country(list);



	}, [allowedCountries, taxClasses]);


	const checkSelectedTaxeRate = (tax_register_name, select_value) => {

		return arubaData.tax_simple_data[tax_register_name] == select_value ? 'selected' : '';

	}


	const { feErrors: errors, setPreventUnload } = useStateContextProvider();

	const handleChange = (e) => {

		setPreventUnload(true);

	}

	return (simple_tax_rows_country.length ? (
		<>
			<table className='table table-bordered'>
				<thead className={'tableRowHeader'}>
					<tr>
						<th className={'tableHeader'}>{textDataProvider.aruba_fe_country_code}
							<Tooltip title={textDataProvider.aruba_fe_country_code_tt} placement="right">
								<IconButton>
									<HelpOutlineIcon />
								</IconButton>
							</Tooltip>
						</th>
						<th className={'tableHeader'}>{textDataProvider.aruba_fe_country_vat_code}
							<Tooltip title={textDataProvider.aruba_fe_country_vat_code_tt} placement="right">
								<IconButton>
									<HelpOutlineIcon />
								</IconButton>
							</Tooltip>
						</th>
					</tr>
				</thead>

				<tbody>
					{simple_tax_rows_country.map((tax,index) => {

						return (
							<tr key={`tax_row_${index}`} className={'tableRowItems'} >
								<td className={'tableCell'}>{tax.label}</td>
								<td className={'tableCell'}>
									<select className={errors[tax.register_name] ? 'invalid' : ''} {...register(tax.register_name, {
										required: true,
										onChange: (e) => handleChange(e)
									})}
									>
										<option key={`${tax.register_name}_nv`} value="">Seleziona</option>
										{tax_rate_api.map((select) => {
											return (
												<option key={select.value} selected={checkSelectedTaxeRate(tax.register_name, select.value)} value={select.value}>{parseFeSting(select.label)}</option>
											)
										})}
									</select>

								</td>

							</tr>
						)
					})}
				</tbody>
			</table>

		</>
	) : null
	);
};

export default TableTaxSimpleComponent;
