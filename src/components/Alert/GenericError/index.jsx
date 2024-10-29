import { confirmAlert } from "react-confirm-alert";
import { useTextContextProvider } from "../../../Context/Text";

export default function GenericError({ titleMessage, message, setFeAlert }) {

    const textDataProvider = useTextContextProvider();

    const title = titleMessage || textDataProvider.aruba_fe_generic_error;

    return (
        confirmAlert({
            title: title,
            overlayClassName: 'aruba-fe-overlay',
            customUI: (props) => {
                return (
                    <div>
                        <h1>{title}</h1>
                        <div className="modal-message">
                            {message}
                        </div>
                        <div className="modal-actions">
                            <button className='fe-btn fe-btn-empty' onClick={(e) => { setFeAlert(null); props.onClose() }}>
                                {textDataProvider.aruba_fe_close}
                            </button>
                        </div>
                    </div>


                );
            }
        })

    );
}

