import React, { useState, useEffect, useContext } from 'react';
import { useForm } from 'react-hook-form';
import apiFetch from '@wordpress/api-fetch';
import { useTextContextProvider } from '../Context/Text';
import { useStateContextProvider } from '../Context/State';
import DisconnectAlert from './Alert/Disconnect';
import { confirmAlert } from 'react-confirm-alert'; // Import
import 'react-confirm-alert/src/react-confirm-alert.css'; // Import css
import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';
import HelpOutlineIcon from '@mui/icons-material/HelpOutline';

const ApiConnection = ({ setArubaData, arubaData, taxEnabled, incompatible_plugins, setVatCode }) => {


	// form
	const textDataProvider = useTextContextProvider();
	const stateDataProvider = useStateContextProvider();
	const { register, control, handleSubmit, formState: { errors } } = useForm();

	const [connectLabel, setConnectLabel] = useState(textDataProvider.aruba_fe_connect);
	const [disconnectLabel, setdisconnectLabel] = useState('Disconnect');

	const [connected, setConnected] = useState(false);

	const [warning, setWarning] = useState();

	//const [taxEnabledLabel, setTaxEnabledLabel] = useState(textDataProvider.aruba_fe_checkTaxes);

	const nonce = aruba_fe_data.nonce;

	const onSubmit = (data) => {

		setConnectLabel(textDataProvider.aruba_fe_conneting);

		const check_connection = async () => {
			stateDataProvider.setLoading(true);

			apiFetch({ path: '/aruba_fe/v1/check_connection', method: 'POST', data: { api_data: data, nonce: nonce }, })
				.then((data) => {

					if (data.success) {

						//setConnectLabel(data.button)
						setConnected(true);
						setArubaData(data.data);
						setVatCode(data.vatCode);
						setdisconnectLabel(textDataProvider.aruba_fe_disconnect);
						setWarning(null)

						stateDataProvider.setHasMessage({
							text: textDataProvider.aruba_fe_connction_success,
							className: 'notice-success'
						});

						stateDataProvider.setPreventUnload(true);

					} else {

						setWarning(data.message)
						setConnectLabel(data.button)

					}

					stateDataProvider.setLoading(false);

				});
		}
		check_connection();
	}

	const handlerDisconnect = async (e) => {

		e.preventDefault();

		const disconnect = async () => {
			stateDataProvider.setLoading(true);

			apiFetch({ path: '/aruba_fe/v1/drop_connection', method: 'POST', data: { nonce: nonce } }).then((data) => {

				if (data.success) {

					setConnected(false);

					setArubaData(data.data)

					stateDataProvider.setHasMessage({
						text: textDataProvider.aruba_fe_disconnect_success,
						className: 'notice-success'
					});

				} else {

					setWarning(data.message)

				}
				stateDataProvider.setPreventUnload(false);
				stateDataProvider.setLoading(false);
			});
		}

		confirmAlert({

			title: textDataProvider.aruba_fe_disconnect_title,

			overlayClassName: 'aruba-fe-overlay',

			customUI: (props) => {
				return (
					<DisconnectAlert {...props} confirm={textDataProvider.aruba_fe_disconnect_title} message={textDataProvider.aruba_fe_disconnect_message}>
						<button className='fe-btn fe-btn-empty' onClick={(e) => props.onClose()}>
							{textDataProvider.aruba_fe_abort}
						</button>
						<button className='fe-btn fe-btn-primary' onClick={(e) => { props.onClose(); disconnect() }}>
							{textDataProvider.aruba_fe_disconnect_btn}
						</button>
					</DisconnectAlert>
				);
			}
		});


	}

	const getConnectLabel = () => {

		if ((arubaData && arubaData.api_connection.connected) || connected) {

			return textDataProvider.aruba_fe_connected;

		}

		return textDataProvider.aruba_fe_connect;
	}

	const disabledInput = (placeholder = '********') => {

		if ((arubaData && arubaData.api_connection.connected) || connected) {
			return {
				disabled: "disabled",
				placeholder: placeholder,
				value: ""
			}
		}

		return "";
	}

	useEffect(() => stateDataProvider.setLoading(false), [])

	const handleChange = (e) => {
		const value = e.target.value;
		if (/^[a-zA-Z0-9]*$/.test(value)) {
			setTaxClass(value);
		}
	}

	return (
		<>
			{(warning) && <div className="notice notice-warning aruba_fe_notice">
				<p>{/*textDataProvider.aruba_fe_error_on_connection*/}{warning}</p>
			</div>}
			{(!taxEnabled) && <div className="notice notice-error aruba_fe_notice">
				<p>{textDataProvider.aruba_fe_taxes_disabled} <a href={textDataProvider._wc_settings_link}>{textDataProvider.aruba_fe_config_action}</a></p>
			</div>}
			{(taxEnabled) && <form onSubmit={handleSubmit(onSubmit)}>
				<div className="block_api_connection">
					<div className="block_api_connection_single_item">
						<div className="block_api_connection_single_item_label">
							<label>{textDataProvider.aruba_fe_shop_name}</label>
							<Tooltip title={textDataProvider.aruba_fe_shop_name_tt} placement="right">
								<IconButton>
									<HelpOutlineIcon />
								</IconButton>
							</Tooltip>
						</div>
						<div className="block_api_connection_single_item_input">
							<input {...disabledInput(arubaData.api_connection?.shopName)} className={errors.shopName ? 'invalid' : ''} type="text"  {...register('shopName', { required: true })}></input>
						</div>
					</div>
					<div className="block_api_connection_single_item">
						<div className="block_api_connection_single_item_label">
							<label>{textDataProvider.aruba_fe_customer_code}</label>
						</div>
						<div className="block_api_connection_single_item_input">
							<input autoComplete='off' {...disabledInput()} className={errors.customerCode ? 'invalid' : ''} type="text" {...register('customerCode', { required: true })}></input>
						</div>
					</div>
					<div className="block_api_connection_single_item">
						<div className="block_api_connection_single_item_label">
							<label>{textDataProvider.aruba_fe_secret_code}</label>
						</div>
						<div className="block_api_connection_single_item_input">
							<input autoComplete='off' {...disabledInput()} className={errors.secretCode ? 'invalid' : ''} type="password" {...register('secretCode', { required: true })}></input>
						</div>
					</div>

					{!(arubaData.api_connection.connected) && !incompatible_plugins &&
						<div className="block_api_connection_single_item ">
							<div className="block_api_connection_single_item_input ">
								<button {...disabledInput()} type="submit" className="fe-btn">{getConnectLabel()}</button>
							</div>
						</div>
					}

					{(arubaData.api_connection.connected) && <div key={connected} className="block_api_connection_single_item ">
						<div className="block_api_connection_single_item_input ">
							<button onClick={(e) => handlerDisconnect(e)} className="fe-btn">{textDataProvider.aruba_fe_disconnect_btn}</button>
						</div>
					</div>}
				</div>
			</form>}

		</>
	)
}

export default ApiConnection;
