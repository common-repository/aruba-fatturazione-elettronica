import {
    ValidatedTextInput,
    TextInput,
    CheckboxControl,
    ValidationInputError
} from '@woocommerce/blocks-checkout';
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';

import {useEffect, useState, useMemo} from '@wordpress/element';
import {getSetting} from '@woocommerce/settings';
import {useSelect, useDispatch} from '@wordpress/data';
import {SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';



const selectStyle = {height: 'auto', padding: '1.5em .5em 1.5em', fontSize: '1em', borderRadius: '4px'};

// import * as wcc from '@woocommerce';
const defaultData = getSetting('aruba-fatturazione-elettronica-checkout-blocks_data',
    {
        billing_option_aruba_fe_need_invoice: '',
        billing_codice_fiscale_aruba_fe: false,
        billing_customer_type_aruba_fe: '',
        billing_partita_iva_aruba_fe: '',
        billing_send_choice_invoice_aruba_fe: '',
        billing_sdi_aruba_fe: '',
        billing_pec_aruba_fe: '',
        billing_need_invoice_aruba_fe: false,
    });

export default function ArubaFatturazioneElettronicaAddressFieldsEdit({context}) {


    const [useShippingAsBilling, setUseShippingAsBilling] = useState(null);
    const [billing_need_invoice_aruba_fe, setBilling_need_invoice_aruba_fe] = useState(defaultData.billing_need_invoice_aruba_fe);
    const [billing_codice_fiscale_aruba_fe, setBilling_codice_fiscale_aruba_fe] = useState(defaultData.billing_codice_fiscale_aruba_fe);
    const [billing_customer_type_aruba_fe, setBilling_customer_type_aruba_fe] = useState(defaultData.billing_customer_type_aruba_fe);
    const [billing_partita_iva_aruba_fe, setBilling_partita_iva_aruba_fe] = useState(defaultData.billing_partita_iva_aruba_fe);
    const [billing_send_choice_invoice_aruba_fe, setBilling_send_choice_invoice_aruba_fe] = useState(defaultData.billing_send_choice_invoice_aruba_fe);
    const [billing_sdi_aruba_fe, setBilling_sdi_aruba_fe] = useState(defaultData.billing_sdi_aruba_fe);
    const [billing_pec_aruba_fe, setBilling_pec_aruba_fe] = useState(defaultData.billing_pec_aruba_fe);

    const {VALIDATION_STORE_KEY, CHECKOUT_STORE_KEY} = window.wc.wcBlocksData;

    const {setValidationErrors, clearValidationError} = useDispatch(
        VALIDATION_STORE_KEY
    );

    const [cfError, setCfError] = useState(false);
    const [cfT, setcfT] = useState(false);
    const [pivaError, setPivaError] = useState(false);
    const [pivaT, setPivaT] = useState(false);
    const [pecError, setPecError] = useState(false);
    const [pecT, setPecT] = useState(false);

    const [s1, setS1] = useState(false);
    const [s2, setS2] = useState(false);


    useSelect((select) => {

        const store = select(CHECKOUT_STORE_KEY);
        setUseShippingAsBilling(store.getUseShippingAsBilling());

    }, []);

    useEffect(() => {

    }, [useShippingAsBilling])

    if (!useShippingAsBilling && context === 'Shipping') {
        return <></>
    }

    return (
        <>

            <SelectControl style={{...selectStyle}} value={billing_customer_type_aruba_fe}
                           options={[
                               {label: __('Customer type', 'aruba-fatturazione-elettronica'), value: ''},
                               {label: __('Physical person', 'aruba-fatturazione-elettronica'), value: 'person'},
                               {label: __('Company', 'aruba-fatturazione-elettronica'), value: 'company'},
                           ]}
                           className={!billing_customer_type_aruba_fe ? 'has-error aruba-fe-error aruba-fe-select' : 'aruba-fe-select'}
                           onChange={(value) => {
                               setBilling_customer_type_aruba_fe(value);
                               setS1(true)
                           }}
            />

            {!billing_customer_type_aruba_fe && s1 && (<ValidationInputError
                errorMessage={__('Select customer type is a required field', 'aruba-fatturazione-elettronica')}/>)}

            {billing_customer_type_aruba_fe === 'person' && !defaultData.billing_option_aruba_fe_need_invoice ? (
                <CheckboxControl
                    id="billing_need_invoice_aruba_fe"
                    checked={billing_need_invoice_aruba_fe ? 1 : 0}
                    onChange={() => {setBilling_need_invoice_aruba_fe(!billing_need_invoice_aruba_fe)}}
                >
                    {__('Do you want an invoice?', 'aruba-fatturazione-elettronica')}
                </CheckboxControl>
            ) : <ValidatedTextInput type="hidden" value="1"/>}


            {billing_customer_type_aruba_fe === 'company' && (
                <h2 className="wc-block-components-title wc-block-components-checkout-step__title">{__('Fiscal data','aruba-fatturazione-elettronica')} {__('(One of the two fields required)','aruba-fatturazione-elettronica')}</h2>
            )}

            {(billing_customer_type_aruba_fe === 'company' ||
                (billing_customer_type_aruba_fe === 'person' &&
                    (defaultData.billing_option_aruba_fe_need_invoice || billing_need_invoice_aruba_fe)
                )
            ) && (
                <>
                    <TextInput onBlur={() => setcfT(false)} onInput={() => setcfT(true)}
                               className={cfError ? 'has-error aruba-fe-error' : ''}
                               value={billing_codice_fiscale_aruba_fe}
                               onChange={setBilling_codice_fiscale_aruba_fe}
                               label={__('Tax code', 'aruba-fatturazione-elettronica')}/>
                    {cfError && (
                        <ValidationInputError
                            errorMessage={__('Tax code is not in the correct format', 'aruba-fatturazione-elettronica')}/>)}
                </>
            )
            }

            {billing_customer_type_aruba_fe === 'company' && (
                <>
                    <TextInput
                        className={pivaError ? 'has-error aruba-fe-error' : ''}
                        onBlur={() => setPivaT(false)} onInput={() => setPivaT(true)}
                        value={billing_partita_iva_aruba_fe}
                        onChange={setBilling_partita_iva_aruba_fe}
                        label={__('VAT number', 'aruba-fatturazione-elettronica')}/>
                    {pivaError && (
                        <ValidationInputError
                            errorMessage={__('VAT number is not in the correct format', 'aruba-fatturazione-elettronica')}/>)}

                    <SelectControl required={true} style={{...selectStyle}} value={billing_send_choice_invoice_aruba_fe}
                                   label={__('How would you like to receive an invoice?', 'aruba-fatturazione-elettronica')}
                                   options={[
                                       {label: __('Select', 'aruba-fatturazione-elettronica'), value: ''},
                                       {
                                           label: __('SDI (Recipient Code)', 'aruba-fatturazione-elettronica'),
                                           value: 'sdi'
                                       },
                                       {label: __('PEC', 'aruba-fatturazione-elettronica'), value: 'pec'},
                                       {label: __('No identifier', 'aruba-fatturazione-elettronica'), value: '*'},
                                       {
                                           label: __('Foreign invoice number', 'aruba-fatturazione-elettronica'),
                                           value: 'cfe'
                                       },
                                   ]}
                                   className={!billing_send_choice_invoice_aruba_fe ? 'has-error aruba-fe-error aruba-fe-select' : 'aruba-fe-select'}
                                   onChange={(value) => {
                                       setBilling_send_choice_invoice_aruba_fe(value);
                                       setS2(true);
                                   }}
                    />

                    {!billing_send_choice_invoice_aruba_fe && s2 && (<ValidationInputError
                        errorMessage={__('How would you like to receive an invoice is a required field', 'aruba-fatturazione-elettronica')}/>)}

                    {billing_send_choice_invoice_aruba_fe === 'sdi' && (
                        <ValidatedTextInput required={true} value={billing_sdi_aruba_fe}
                                            onChange={setBilling_sdi_aruba_fe}
                                            label={__('SDI (Recipient Code)', 'aruba-fatturazione-elettronica')}/>
                    )}

                    {billing_send_choice_invoice_aruba_fe === 'pec' && (
                        <>
                            <TextInput onBlur={() => setPecT(false)} onInput={() => setPecT(true)}
                                       className={pecError ? 'has-error aruba-fe-error' : ''}
                                       value={billing_pec_aruba_fe}
                                       onChange={setBilling_pec_aruba_fe}
                                       label={__('PEC', 'aruba-fatturazione-elettronica')}/>
                            {pecError && (
                                <ValidationInputError
                                    errorMessage={__('Company PEC is not in the correct format', 'aruba-fatturazione-elettronica')}/>)}
                        </>)}
                </>
            )}

        </>
    );
}
