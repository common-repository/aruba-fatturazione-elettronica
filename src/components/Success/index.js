export default function Success({ textData, setSaved }) {

    return (

        <div className='aruba_fe_notice notice notice-success'>
            <p>{textData.aruba_fe_config_saved}</p>
            <button type="button" onClick={e => setSaved(false)} className="notice-dismiss"><span className="screen-reader-text">X</span></button>
        </div>

    )
}