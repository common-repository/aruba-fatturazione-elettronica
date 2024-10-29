import React, { useEffect } from "react";
import classNames from "classnames";
import apiFetch from '@wordpress/api-fetch';
import { useTextContextProvider } from "../../../../Context/Text";
import { useStateContextProvider } from "../../../../Context/State";
import SimpleAlert from "../../../Alert/SimpleAlert";
import { confirmAlert } from 'react-confirm-alert'; //
const DeleteTaxRateClass = ({ rate_type, setTaxClasses }) => {

  const className = classNames({
    button: true,
    'button-large': true,
    'delete_tax_rate_button': true,
  });

  const textDataProvider = useTextContextProvider();
  const { setLoading, setFeAlert } = useStateContextProvider();

  const handlerDeleteTaxRate = async () => {

    const deleteTax = async () => {

      setLoading(true);

      try {

        const data = await apiFetch({ path: '/aruba_fe/v1/delete_tax_rate', method: 'POST', data: { rate_type: rate_type, nonce: aruba_fe_data.nonce } });

        if (data.success) {

          setTaxClasses(data.data_taxes.tax_classes)

        }

        setLoading(false)

      } catch (error) {

        setFeAlert(error.message);
        setLoading(false)

      }
    }

    confirmAlert({
      title: textDataProvider.aruba_fe_delete_tax_title,
      overlayClassName: 'aruba-fe-overlay',
      customUI: (props) => {
        return (
          <SimpleAlert {...props} message={textDataProvider.aruba_fe_delete_tax_message}>
            <button className='fe-btn fe-btn-empty' onClick={(e) => props.onClose()}>
              {textDataProvider.aruba_fe_abort}
            </button>
            <button className='fe-btn' onClick={(e) => { props.onClose(); deleteTax() }}>
              {textDataProvider.aruba_fe_confirm_tax_delete}
            </button>
          </SimpleAlert>
        );
      }
    });

  }

  return (
    <>

      {(rate_type != "") &&

        <a onClick={(e) => { handlerDeleteTaxRate() }} className={className + ""}>{textDataProvider.aruba_fe_delete_tax_rate}</a>

      }

    </ >

  );
};

export default DeleteTaxRateClass;
