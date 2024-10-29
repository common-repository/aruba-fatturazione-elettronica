import React, { useState, useEffect, useReducer, useMemo } from 'react';

const _set = require('lodash.set');


import TextContextProvider from './Context/Text';
import ApiConnection from './components/ApiConnection';
import MessageHeaderBlock from './components/MessageHeaderBlock';
import Create_invoice from './components/Automatic_invoice/Create_invoice';
import SendInvoice from './components/Automatic_invoice/SendInvoice';
import StateOrderInvoice from './components/Automatic_invoice/StateOrderInvoice';
import ReportingPaidInvoices from './components/Global/ReportingPaidInvoices';
import DescriptionInvoice from './components/Global/DescriptionInvoice';
import IndividualCreateInvoce from './components/Global/IndividualCreateInvoce';
import UpdateDataCustomer from './components/Global/UpdateDataCustomer';
import SendCourtesyCopy from './components/Global/SendCourtesyCopy';
import ExemptionForForeign from './components/Global/ExemptionForForeign';
//import globalDataObject from './DataObject/DataObject.js';
import { useForm } from 'react-hook-form';
import Payments from './components/payments/Payments';
import TaxConfiguration from './components/tax/TaxConfiguration';
import GlobalServices from './components/globalServices/index';
import Loading from "./components/Loading";
import ArubaLogo from './assets/images/aruba.it.svg';
import StateContextProvider from './Context/State';
import { FeReducer, initialState } from './Reducer/FeReducer';
import Error from './components/Error';
import GenericError from './components/Alert/GenericError';
import TabFe from './components/TabFe';
import { parseAllLabels, useTextFormatter } from './TextHelper/TextParser';
import Success from './components/Success';
import Message from './components/Message';
import ShowFullPrice from "./components/Global/ShowFullPrice";
import Selectbank from './components/payments/Selectbank';
function App({ arubafelabels, configs }) {


    /**
     * SETUP DATA
     */
    const [hash, setHash] = useState('');

    const { taxEnabled: taxActive } = configs;

    useEffect(() => {



    }, [])



    const textData = arubafelabels;

    const { updateData, getData } = GlobalServices;

    const { register, control, handleSubmit, formState, formState: { errors }, setValue } = useForm();

    /**
     * SETUP REDUCER
     */
    const [feReady, dispatch] = useReducer(FeReducer, initialState);

    const { ready } = feReady;

    /**
         * SETUP STATES
         */

    const [loading, setLoading] = useState(true);

    const [arubaData, setArubaData] = useState(null);

    const checkApiConnected = () => {
        return (arubaData) ? arubaData.api_connection.connected : false;
    }

    const [arubaInitialData, setArubaInitialData] = useState(false);

    const [init, setInit] = useState(false);

    const [preventUnload, setPreventUnload] = useState(false);

    const [saveButton, setSaveButton] = useState(textData.aruba_fe_save);

    const [hasErrors, setHasErrors] = useState(false);

    const [hasMessage, setHasMessage] = useState();

    const [saved, setSaved] = useState(false);

    const [feAlert, setFeAlert] = useState();

    const [configDone, setConfigDone] = useState(false);

    const [currentTab, setCurrentTab] = useState(0);

    const [incompatible_plugins, setIncompatible_plugins] = useState(null);

    const [vatCode, setVatCode] = useState('');

    /**
        * SETUP EFFECTS
    */

    useEffect(() => {

        setHasErrors(Object.keys(errors).length !== 0);

    }, [formState]);


    useEffect(() => {


        parseAllLabels(arubafelabels);

        if (window.location.hash) {

            switch (window.location.hash) {

                case '#configs':
                    setCurrentTab(1)
                    break;

                case '#payments':
                    setCurrentTab(2)
                    break;

                case '#taxes':
                    setCurrentTab(3)
                    break;

            }

        }

        /**
         * Load config data
         */

        const loadData = async () => {

            const dataAruba = await getData();
            const { incompatible_plugins, vatCode, aruba_global_data } = dataAruba.data;

            setIncompatible_plugins(incompatible_plugins);
            setVatCode(vatCode);
            setArubaData(dataAruba.data.aruba_global_data)
            setArubaInitialData(aruba_global_data);

        };

        loadData();
        /**
         * END
         */


    }, []);


    useEffect(() => {

        try {

            window.aruba_fe_preventUnload = preventUnload;

        } catch (error) {

            console.error(error);

        }

    }, [preventUnload]);

    useEffect(() => {

        if (arubaData) {

            setInit(true);

            const { configDone: done } = arubaData.api_connection;

            if (done) {
                setConfigDone(true);
            }

        }



    }, [arubaData])

    const onSubmit = async (data) => {

        setLoading(true);

        const dataAsArray = Object.entries(data);

        const exemptions = dataAsArray.filter(([k, value]) => {
            return k.startsWith('aruba_fe_exemption_for_')
        });

        const filteredTax = dataAsArray.filter(([k, value]) => {
            return k.startsWith('taxClass_')
        });

        const filteredComplexTax_toAdd = dataAsArray.filter(([k, value]) => {
            return k.startsWith('taxComplexClass_')
        });

        const filteredPayments = dataAsArray.filter(([k, value]) => {
            return k.startsWith('paymentsMethods_')
        });

        const filterGlobal = dataAsArray.filter(([k, value]) => {

            return !k.startsWith('paymentsMethods_') &&
                !k.startsWith('taxClass_') &&
                !k.startsWith('taxComplexClass_') &&
                !k.startsWith('tax_method_') &&
                !k.startsWith('aruba_fe_exemption_for_')

        });


        const taxConfigs = dataAsArray.filter(([k, value]) => {
            return k.startsWith('tax_method_');
        });

        const tax_data = Object.fromEntries(filteredTax);

        const methods = Object.fromEntries(taxConfigs);

        const exemptionsOb = Object.fromEntries(exemptions);

        const tax_complex_data_added = Object.fromEntries(filteredComplexTax_toAdd);

        const newDataComplexTax = { ...tax_complex_data_added }

        const payments_data = Object.fromEntries(filteredPayments);

        const global_data = Object.fromEntries(filterGlobal);

        // build new object
        var newArubaData = {};
        _set(newArubaData, 'global_data', global_data);
        _set(newArubaData, 'api_connection', arubaData.api_connection);
        _set(newArubaData, 'tax_simple_data', tax_data);
        _set(newArubaData, 'tax_complex_data', newDataComplexTax);
        _set(newArubaData, 'payments', payments_data);
        _set(newArubaData, 'tax_config', methods);
        _set(newArubaData, 'exemptions', exemptionsOb);

        try {

            const dataUpdate = await updateData(newArubaData);

            if (dataUpdate.data.success) {

                const dataGet = await getData();

                if (dataGet.data.success) {

                    let extract_aruba_data = dataGet.data.aruba_global_data;

                    setArubaData(extract_aruba_data);
                    setSaved(true);
                    setPreventUnload(false);
                }

            }

            setLoading(false);

        } catch (error) {

            setLoading(false);

            setFeAlert(error.message);

        }

    }


    return (
        <>
            <TextContextProvider textData={textData}>
                <StateContextProvider setPreventUnload={setPreventUnload} hasMessage={hasMessage} setHasMessage={setHasMessage} arubaInitialData={arubaInitialData} setArubaInitialData={setArubaInitialData} setFeAlert={setFeAlert} formSetValue={setValue} feErrors={errors} feReducer={dispatch} loading={loading} setLoading={setLoading}>
                    {loading && <Loading />}
                    <div className="aruba_fe_container">
                        <div id="poststuff">
                            <>
                                <div className='main_header'>
                                    <div>
                                        <h1 className="title">{textData.aruba_fe_title}</h1>
                                    </div>
                                    <div>
                                        <a target="_blank" href='https://www.aruba.it/'>
                                            <img
                                                src={ArubaLogo} alt="Aruba.it"></img>
                                        </a>
                                    </div>
                                </div>
                                {/**&& !checkApiConnected() */}
                                {init && (
                                    <div className="aruba_fe_intro">

                                        <p dangerouslySetInnerHTML={{ __html: useTextFormatter(textData.aruba_fe_intro) }} />
                                        <p>{textData.aruba_fe_more_info} <a href={textData.aruba_fe_online_guide_link} target='_blank'>{textData.aruba_fe_online_guide}</a></p>
                                    </div>
                                )}
                                <div className="columns-2_ columns">
                                    <div id="post-body-content">
                                        <div className="postbox">


                                            {init &&
                                                (

                                                    <fieldset className="inside theme-options-container_off">

                                                        {checkApiConnected() && configDone ? <TabFe setCurrentTab={setCurrentTab} currentTab={currentTab} /> : null}
                                                        {
                                                            <>
                                                                {hasMessage && <Message />}
                                                                {saved && <Success setSaved={setSaved} textData={textData} />}
                                                                {hasErrors && <Error textData={textData} />}
                                                            </>
                                                        }
                                                        <div style={(configDone && currentTab != 0) ? { 'display': 'none' } : null}>

                                                            <ApiConnection incompatible_plugins={incompatible_plugins} setLoading={setLoading} taxEnabled={taxActive}
                                                                setArubaData={setArubaData} arubaData={arubaData} setVatCode={setVatCode} />

                                                            {incompatible_plugins ? <div className='notice notice-error aruba_fe_notice'><p>{incompatible_plugins}</p></div> : null}
                                                        </div>

                                                        {(taxActive) &&

                                                            <form onError={(e) => alert('erro')} onSubmit={handleSubmit(onSubmit)}>

                                                                {checkApiConnected() ?
                                                                    (
                                                                        <>

                                                                            <div style={(configDone && currentTab != 1) ? { 'display': 'none' } : null}>

                                                                                <MessageHeaderBlock vatCode={vatCode} arubaData={arubaData} />

                                                                                <Create_invoice register={register} setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />

                                                                                {arubaData.global_data.create_invoice == 'automatic_create_fe' &&
                                                                                    <SendInvoice register={register} setArubaData={setArubaData}
                                                                                        arubaData={arubaData} />}

                                                                                <StateOrderInvoice register={register}
                                                                                    setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />

                                                                                <ReportingPaidInvoices register={register}
                                                                                    setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />

                                                                                <DescriptionInvoice register={register}
                                                                                    setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />

                                                                                <ShowFullPrice register={register} setArubaData={setArubaData} arubaData={arubaData} />

                                                                                <IndividualCreateInvoce register={register}
                                                                                    setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />

                                                                                <UpdateDataCustomer register={register}
                                                                                    setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />

                                                                                <SendCourtesyCopy register={register}
                                                                                    setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />
                                                                            </div>

                                                                            <div style={(configDone && currentTab != 2) ? { 'display': 'none' } : null}>

                                                                                <Payments register={register} setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />

                                                                                <Selectbank register={register} setArubaData={setArubaData} arubaData={arubaData} />
                                                                            </div>

                                                                            <div style={(configDone && currentTab != 3) ? { 'display': 'none' } : null}>
                                                                                <TaxConfiguration
                                                                                    handleSubmit={handleSubmit}
                                                                                    onSubmit={onSubmit} saveButton={saveButton}
                                                                                    register={register}
                                                                                    setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />

                                                                                <ExemptionForForeign register={register}
                                                                                    setArubaData={setArubaData}
                                                                                    arubaData={arubaData} />
                                                                            </div>
                                                                            <hr />
                                                                            {ready && !incompatible_plugins && <button type="submit" className="fe-btn fe-btn-primary">{saveButton}</button>}



                                                                        </>
                                                                    ) : null}
                                                            </form>}

                                                    </fieldset>
                                                )
                                            }
                                        </div>
                                    </div>
                                </div>
                            </>
                        </div>
                    </div>
                    {feAlert ? <GenericError setFeAlert={setFeAlert} message={feAlert} /> : null}
                </StateContextProvider>
            </TextContextProvider >

        </>
    )
}

export default App;
