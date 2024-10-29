import { useStateContextProvider } from "../../Context/State"

export default function Error({ textData }) {

    const { feErrors } = useStateContextProvider();

    return (

        <div className='aruba_fe_notice notice notice-error notice-important'>
            <p>{textData.aruba_fe_has_some_errors}</p>
        </div>

    )
}