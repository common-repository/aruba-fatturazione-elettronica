import { useStateContextProvider } from "../../Context/State"

export default function Message() {

    const { hasMessage, setHasMessage } = useStateContextProvider();

    return (

        <div className={`aruba_fe_notice notice ${hasMessage.className}`}>
            <p>{hasMessage.text}</p>
            <button type="button" onClick={e => setHasMessage(null)} className="notice-dismiss"><span className="screen-reader-text">X</span></button>
        </div>

    )
}