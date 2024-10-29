import {useState} from 'react';
import RadioComponents from '../commons/RadioComponent';
import {useTextContextProvider} from '../../Context/Text';

export default function ShowFullPrice({ setArubaData, arubaData, register }) {

    const radio_property = "show_full_price";

    const textDataProvider = useTextContextProvider();

    const radioDataInit = [
        {
            value: '0',
            label: textDataProvider.aruba_fe_view_totals_opt_1,
            checked: (arubaData.global_data[radio_property] == 0) ? true : false,
        },
        {
            value: '1',
            label: textDataProvider.aruba_fe_view_totals_opt_2,
            checked: (arubaData.global_data[radio_property] == 1) ? true : false,
        }
    ];

    const [radioData, setRadioData] = useState(radioDataInit);

    return (
        <>
            <div className="block_create_invoice">
                <div className="block_create_invoice_title">
                    <h3>{textDataProvider.aruba_fe_view_totals_title}</h3>
                </div>
            </div>

            <RadioComponents
                radio_property={radio_property}
                register={register}
                radioData={radioData}
                setRadioData={setRadioData}
                setArubaData={setArubaData}
                arubaData={arubaData} />

        </>
    );

}