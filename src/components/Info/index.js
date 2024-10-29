export default function Message({text}) {
    return (
        <div className={`aruba_fe_notice notice info`}>
            <p>{text.replace(/&quot;/g, '"')}</p>
        </div>
    )
}