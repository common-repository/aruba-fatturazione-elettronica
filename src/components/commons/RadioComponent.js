//import React, { useState, useEffect } from 'react';
import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';
import HelpOutlineIcon from '@mui/icons-material/HelpOutline';
import { useStateContextProvider } from '../../Context/State';
const RadioComponents = ({ radioData, setRadioData, setArubaData, arubaData, register, radio_property, tooltip, tooltipsMEssage }) => {

	const StateContextProvider = useStateContextProvider();

	const handleRadioButtonClick = (e) => {
		var newArubaData = {
			...arubaData,
			global_data: {
				...arubaData.global_data,
				[radio_property]: e.target.value
			}

		};
		setArubaData(newArubaData);
		StateContextProvider.setPreventUnload(true);
	};


	return (
		<>

			<div className="block_create_invoice_radio" >
				<div className="border_bottom" >
					<ul>
						{radioData.map((single, i) => {
							const { className } = single;
							return <li className={className} key={i}>
								<input id={single.value} onClick={(e) => { handleRadioButtonClick(e) }} defaultChecked={single.checked} type="radio"  {...register(radio_property, { required: true })} value={single.value}></input>
								<label htmlFor={single.value}>{single.label}</label>
								{tooltip &&
									<Tooltip title={tooltipsMEssage[i]} placement="right">
										<IconButton>
											<HelpOutlineIcon />
										</IconButton>
									</Tooltip>
								}
							</li>;
						})}

					</ul>
				</div>

			</div>

		</>
	)
}

export default RadioComponents;
