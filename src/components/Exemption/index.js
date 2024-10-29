import { useStateContextProvider } from "../../Context/State";
import { useTextContextProvider } from "../../Context/Text";
import { useState, useEffect } from 'react';
import { parseFeSting } from "../../TextHelper/TextParser";
export default function Exemption({ register, arubaData }) {

    const textDataProvider = useTextContextProvider();
    const { taxRatesProvider } = useStateContextProvider();
    const [filtred, setFiltred] = useState([]);

    useEffect(() => {

        setFiltred(taxRatesProvider.filter((array) => array.value.startsWith('0::')));

    }, [taxRatesProvider])

    return (
        <div className="mt-10">

            <h3>{textDataProvider.aruba_fe_extra_title}</h3>

            <table className='table table-bordered'>
                <thead className={'tableRowHeader'}>
                    <tr>
                        <th className={'tableHeader'}>
                            {textDataProvider.aruba_fe_customer_type}
                        </th>
                        <th className={'tableHeader'}>
                            {textDataProvider.aruba_fe_tax_rate}
                        </th>
                    </tr>
                </thead>
                {filtred.length ?
                    <tbody>
                        <tr className="tableRowItems">
                            <td className="tableCell">{textDataProvider.aruba_fe_tipo_cliente_1}</td>
                            <td className="tableCell"><SelectTaxRate register={register} tax_rates={filtred} name='aruba_fe_exemption_for_pf_eu' arubaData={arubaData} /></td>
                        </tr>
                        <tr className="tableRowItems">
                            <td className="tableCell">{textDataProvider.aruba_fe_tipo_cliente_2}</td>
                            <td className="tableCell"><SelectTaxRate register={register} tax_rates={filtred} name='aruba_fe_exemption_for_co_eu' arubaData={arubaData} /></td>
                        </tr>

                        <tr className="tableRowItems">
                            <td className="tableCell">{textDataProvider.aruba_fe_tipo_cliente_3}</td>
                            <td className="tableCell"><SelectTaxRate register={register} tax_rates={filtred} name='aruba_fe_exemption_for_pf_exeu' arubaData={arubaData} /></td>
                        </tr>
                        <tr className="tableRowItems">
                            <td className="tableCell">{textDataProvider.aruba_fe_tipo_cliente_4}</td>
                            <td className="tableCell"><SelectTaxRate register={register} tax_rates={filtred} name='aruba_fe_exemption_for_co_exeu' arubaData={arubaData} /></td>
                        </tr>

                    </tbody>
                    : null}
            </table>

        </div>
    )

}


const SelectTaxRate = ({ tax_rates, name, arubaData, register }) => {
    const textDataProvider = useTextContextProvider();

    const { exemptions } = arubaData;

    return (<select  {...register(name)}
    >
        <option value="">{textDataProvider.aruba_fe_noapply}</option>

        {tax_rates.map((select) => {
            return (
                <option selected={exemptions[name] == select.value} value={select.value}>{parseFeSting(select.label)}</option>
            )
        })}

    </select>
    );
}