import React, { useState } from 'react';

const StateContext = React.createContext();


export default function StateContextProvider({ children, unregister, loading, setLoading, feReducer, feErrors, formSetValue, setFeAlert, setHasMessage, hasMessage, arubaInitialData, setArubaInitialData, setPreventUnload }) {

    const [allowedCountries, setAllowedCountries] = useState([]);
    const [allowedCountriesGlobal, setAllowedCountriesGlobal] = useState([]);
    const [taxRatesProvider, setTaxRatesProvider] = useState([]);

    const state = {
        loading,
        setLoading,
        feReducer,
        feErrors,
        allowedCountries,
        allowedCountriesGlobal,
        setAllowedCountries,
        setAllowedCountriesGlobal,
        formSetValue,
        taxRatesProvider,
        setTaxRatesProvider,
        setFeAlert,
        setArubaInitialData,
        setHasMessage,
        hasMessage,
        setPreventUnload,
        unregister
    }

    return (
        <StateContext.Provider value={state}>{children}</StateContext.Provider>
    );

}

export function useStateContextProvider() {
    return React.useContext(StateContext);
}