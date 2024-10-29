window.addEventListener('DOMContentLoaded', () => {

    const wc_order_action = document.querySelector('select[name="wc_order_action"]');
    const wc_order_doaction = document.querySelectorAll('button.wc-reload');

    if (!wc_order_action || !wc_order_doaction.length)
        return;

    if (!aruba_fe_links)
        return;

    const openInNewTab = (url) => {

        const options = 'noopener,noreferrer';
        window.open(url, '_blank', options);

    }


    const {
        aruba_fe_order_link,
        aruba_fe_invoice_link,
        aruba_fe_invoice_pdf_link,
        aruba_fe_invoice_ndc_link
    } = aruba_fe_links;

    const actions = {
        aruba_fe_view_order :  aruba_fe_order_link,
        aruba_fe_view_invoice : aruba_fe_invoice_link ,
        aruba_fe_download_invoice : aruba_fe_invoice_pdf_link,
        aruba_fe_view_invoice_credit_note : aruba_fe_invoice_ndc_link,
    };


    [...wc_order_doaction].map(button => {

        button.addEventListener('click',e => {

            const selectedValue = wc_order_action.value;

            if(actions[selectedValue]) {
                e.preventDefault();
                openInNewTab(actions[selectedValue]);
            }
        })

    });

    // [...wc_order_action].map(select => {
    //
    //     select.addEventListener('change', e => {
    //
    //         switch (e.target.value) {
    //
    //             case 'aruba_fe_view_order':
    //                 openInNewTab(aruba_fe_order_link);
    //                 break;
    //
    //             case 'aruba_fe_view_invoice':
    //                 openInNewTab(aruba_fe_invoice_link);
    //                 break;
    //
    //             case 'aruba_fe_download_invoice':
    //                 openInNewTab(aruba_fe_invoice_pdf_link);
    //                 break;
    //
    //             case 'aruba_fe_view_invoice_credit_note':
    //
    //                 openInNewTab(aruba_fe_invoice_ndc_link);
    //                 break;
    //
    //         }
    //
    //     });
    //
    // })

})