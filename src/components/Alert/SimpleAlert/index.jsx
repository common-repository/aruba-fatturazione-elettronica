export default function SimpleAlert({ title, message, children }) {

    return (<div>
        <h1>{title}</h1>
        <div className="modal-message">
            {message}
        </div>
        <div className="modal-actions">
            {children}
        </div>
    </div>
    );
}