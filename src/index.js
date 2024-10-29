
import { StrictMode } from 'react';
import App from './App';
// import { render } from '@wordpress/element';
import { createRoot } from 'react-dom';
import '../assets/admin/scss/aruba_fe.scss';

document.addEventListener('DOMContentLoaded', function () {

    var element = document.getElementById('aruba-fe-admin-app');

    if (typeof element !== 'undefined' && element !== null) {

        const { aruba_fe_labels, aruba_fe_wc_configs } = aruba_fe_data;
        const root = createRoot(element);
        root.render(<StrictMode><App configs={aruba_fe_wc_configs} arubafelabels={aruba_fe_labels} /></StrictMode>);
    }

})
