import React from 'react';

const TextContext = React.createContext();


export default function TextContextProvider({ children, textData }) {

    return (
        <TextContext.Provider value={textData}>{children}</TextContext.Provider>
    );

}

export function useTextContextProvider() {
    return React.useContext(TextContext);
}