export default function DisconnectAlert({ title, message, confirm, children }) {

    return (<div>
        <h1>{title}</h1>
        <div className="modal-message">
            {message}
            {confirm ? <p className="text-right">{confirm}</p> : null}
        </div>
        <div className="modal-actions">
            {children}
        </div>
    </div>
    );
}