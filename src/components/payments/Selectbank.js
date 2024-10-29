import {useTextContextProvider} from "../../Context/Text";
import {parseFeSting} from "../../TextHelper/TextParser";
import {useStateContextProvider} from "../../Context/State";
import {useEffect, useState} from "react";
import apiFetch from "@wordpress/api-fetch";
import Info from "../Info";

export default function Selectbank({setArubaData, arubaData, register}) {

    const textDataProvider = useTextContextProvider();

    const [banks, setBanks] = useState([]);

    const handleChange = (e) => {
        setPreventUnload(true);
    }

    const {feErrors: errors, setPreventUnload, feReducer} = useStateContextProvider();

    useEffect(() => {

        const getBanksData = async () => {

            try {

                const {banks, success} = await apiFetch({
                    path: '/aruba_fe/v1/get_banks',
                    method: 'POST',
                    data: {nonce: aruba_fe_data.nonce}
                });

                if (!success) {

                    throw new Error('Errore nel recupero delle banche');

                }

                setBanks(banks);

            } catch (error) {

                console.error(error)

            }

            feReducer({type: 'remove', payload: true, func: 'banks_loaded'});
        }

        getBanksData();


    }, [])

    return (
        <>
            {banks.length > 0 && (
                <div className='border_bottom'>
                    <div className="block_create_invoice ">
                        <div className="block_create_invoice_title">
                            <h4>{textDataProvider.aruba_fe_banks_title}</h4>
                        </div>
                    </div>
                    <div className={'aruba_fe_container mb-10'}>
                        <select defaultValue={arubaData.global_data.default_bank} style={{minWidth: '300px'}} {...register(`default_bank`, {
                            onChange: (e) => handleChange(e)
                        })}>
                            <option value="" key={'nobk'}>{textDataProvider.aruba_fe_select}</option>
                            {banks.map(bank => <option key={bank.id}
                                                        value={bank.id}>{bank.description}</option>)}
                        </select>
                    </div>

                    <Info text={textDataProvider.aruba_fe_banks_info} />

                </div>)}
        </>
    )

}