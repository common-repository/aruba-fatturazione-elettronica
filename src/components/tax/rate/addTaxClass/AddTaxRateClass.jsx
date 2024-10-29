import { useState } from "react";
import classNames from "classnames";
import apiFetch from '@wordpress/api-fetch';
import { useTextContextProvider } from "../../../../Context/Text";
import SimpleAlert from "../../../Alert/SimpleAlert";
import { confirmAlert } from "react-confirm-alert";
import { useStateContextProvider } from "../../../../Context/State";

const AddTaxRateClass = ({ setTaxClasses }) => {


  const [open, setOpen] = useState(false)
  const [tax_class, setTaxClass] = useState("")
  const className = classNames({
    button: true,
    'button-large': true,
    'delete_tax_rate_button': true,
    'ml-10': true,
  });

  const textDataProvider = useTextContextProvider();
  const { setLoading, setFeAlert } = useStateContextProvider();

  const handleChange = (e) => {
    const value = e.target.value;
    if (/^[a-zA-Z0-9]*$/.test(value)) {
      setTaxClass(value);
    }
  }

  const handlerAddTaxRate = async () => {

    const addTaxRate = async () => {

      setLoading(true);

      try {
        const data = await apiFetch({ path: '/aruba_fe/v1/add_tax_rate', method: 'POST', data: { tax_class: tax_class, nonce: aruba_fe_data.nonce } });
        if (data.success) {
          setTaxClasses(data.data_taxes.tax_classes)
          setTaxClass("")
          setOpen(false);
        }
        setLoading(false)
      } catch (error) {
        setFeAlert(error.message);
        setLoading(false)
      }


    }

    confirmAlert({
      title: textDataProvider.aruba_fe_add_tax_title,
      overlayClassName: 'aruba-fe-overlay',
      customUI: (props) => {
        return (
          <SimpleAlert {...props} message={textDataProvider.aruba_fe_add_tax_message}>
            <button className='fe-btn fe-btn-empty' onClick={(e) => props.onClose()}>
              {textDataProvider.aruba_fe_abort}
            </button>
            <button className='fe-btn fe-btn-primary' onClick={(e) => { props.onClose(); addTaxRate() }}>
              {textDataProvider.aruba_fe_confirm_tax_add}
            </button>
          </SimpleAlert>
        );
      }
    });


  }

  return (
    <div className="add-new-rate">
      <a onClick={(e) => { setOpen(!open) }} className={className}>{open ? textDataProvider.aruba_fe_abort : textDataProvider.aruba_fe_add_tax_rate}</a>
      {open &&
        <div className="add_tax_class">
          <input className="input input-xlarge" placeholder={textDataProvider.aruba_fe_nex_tax_palceholder} onChange={handleChange} type="text" value={tax_class} />
          <a onClick={(e) => { handlerAddTaxRate() }} className={className}>{textDataProvider.aruba_fe_add_tax_rate_save}</a>
        </div>
      }
    </div>
  );
};

export default AddTaxRateClass;
