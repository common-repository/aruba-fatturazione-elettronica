
import React, { useState, useEffect, useMemo } from 'react';

import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';
import HelpOutlineIcon from '@mui/icons-material/HelpOutline';

import { useStateContextProvider } from '../../../../Context/State';
import { useTextContextProvider } from '../../../../Context/Text';

import { parseFeSting } from '../../../../TextHelper/TextParser';

const TableTaxChooseComponent = ({ setArubaData, arubaData, register, rate_type, saveButton, onSubmit, handleSubmit, tax_rate_api, taxClasses }) => {

	const rowsPerPage = 10;

	const rate_type_selected = (rate_type == "") ? "standard" : rate_type;

	const [countries, setCountries] = useState([]);

	const checkSelectedTaxeRate = (tax_register_name, select_value) => {
		return arubaData.tax_complex_data[tax_register_name] == select_value;
	}

	const { allowedCountriesGlobal } = useStateContextProvider();

	const textDataProvider = useTextContextProvider();
	const stateDataProvider = useStateContextProvider();
	const [page, setPage] = useState(0);

	const [currentSlice, setCurrentSlice] = useState({
		min: 0,
		max: rowsPerPage
	})

	const { feErrors: errors, formSetValue } = useStateContextProvider();

	const numberOfItems = Object.entries(allowedCountriesGlobal).length
	const nOfPages = Math.ceil(numberOfItems / rowsPerPage);

	useEffect(() => {

		setCurrentSlice((prev) => {

			return { min: (page * rowsPerPage), max: (page * rowsPerPage) + rowsPerPage }

		})

	}, [page])

	useMemo(() => {

		Object.entries(arubaData.tax_complex_data).map(([key, value]) => formSetValue(key, value));

	}, [arubaData.tax_complex_data, taxClasses])

	const move = (pos) => {

		if (pos === 'next') {
			setPage(page + 1);
		} else if (pos === 'prev') {
			setPage(page - 1);
		} else if (pos === 'begin') {
			setPage(0)
		} else if (pos === 'end') {
			setPage(nOfPages - 1)
		}

	}

	const handleChange = (e) => {

		stateDataProvider.setPreventUnload(true);

	}

	let cicle = -1;

	return (
		<table className='table'>
			<thead className='tableRowHeader'>
				<tr>
					<th style={{ width: '50%' }} className='tableHeader'>{textDataProvider.aruba_fe_country_code}
						<Tooltip title={textDataProvider.aruba_fe_country_code} placement="right">
							<IconButton>
								<HelpOutlineIcon />
							</IconButton>
						</Tooltip>
					</th>
					<th style={{ width: '50%' }} className='tableHeader'>{textDataProvider.aruba_fe_country_vat_code}
						<Tooltip title={textDataProvider.aruba_fe_country_vat_code} placement="right">
							<IconButton>
								<HelpOutlineIcon />
							</IconButton>
						</Tooltip>
					</th>
				</tr>
			</thead>

			<tbody>
				{

					Object.entries(allowedCountriesGlobal).map(([key, country]) => {

						cicle++;

						return (<tr style={(cicle >= currentSlice.min && cicle < currentSlice.max) ? {} : { display: 'none' }} className={'tableRowItems'} >
							<td className='tableCell'>{country}</td>
							<td className='tableCell'>
								<select onChange={(e) => console.log('ciao')}
									className={errors[`taxComplexClass_${rate_type_selected}_${key}`] ? 'invalid' : ''}
									{
									...register(`taxComplexClass_${rate_type_selected}_${key}`,
										{
											required: true,
											onChange: (e) => handleChange(e)
										})
									}
								>
									<option value="">Seleziona</option>
									{tax_rate_api.map((select) => {
										return (
											<option selected={
												checkSelectedTaxeRate(`taxComplexClass_${rate_type_selected}_${key}`, select.value)}
												value={select.value}>{parseFeSting(select.label)}
											</option>
										)
									})}

								</select>

							</td>

						</tr>)



					})}

			</tbody>
			{nOfPages > 1 && <TablePagination nOfPages={nOfPages} numberOfItems={numberOfItems} currentSlice={page} rowsPerPage={rowsPerPage} moveFunction={move} />}
		</table >
	)


};

export default TableTaxChooseComponent;


const TablePagination = ({ numberOfItems, currentSlice, moveFunction, nOfPages }) => {


	const textDataProvider = useTextContextProvider();

	return (
		<tfoot className='table-pagination'>
			<tr>
				<td>
					{numberOfItems} {textDataProvider.aruba_fe_total}
				</td>
				<td className='text-right' colSpan={2}>
					<div className='pagination'>
						<button disabled={currentSlice === 0} type='button' className='fe-btn fe-btn-pagination' onClick={e => moveFunction('begin')}>«
						</button>
						<button disabled={currentSlice === 0} type='button' className='fe-btn fe-btn-pagination' onClick={(e) => moveFunction('prev')}>&larr;
						</button>
						<button disabled={true} type='button' className='fe-btn fe-btn-pagination'>{currentSlice + 1}</button> {textDataProvider.aruba_fe_of} {nOfPages}
						<button disabled={currentSlice === nOfPages - 1} type='button' className='fe-btn fe-btn-pagination' onClick={(e) => moveFunction('next')}>&rarr;
						</button>
						<button disabled={currentSlice === nOfPages - 1} type='button' className='fe-btn fe-btn-pagination' onClick={(e) => moveFunction('end')}>»
						</button>
					</div>
				</td>
			</tr>
		</tfoot>
	)

}