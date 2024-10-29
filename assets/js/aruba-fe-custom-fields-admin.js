class ArubaFeCustomFieldsAdmin {

    aruba_fe_fields = [
        '_billing_codice_fiscale_aruba_fe',
        '_billing_partita_iva_aruba_fe',
        '_billing_company',
        '_billing_sdi_aruba_fe',
        '_billing_pec_aruba_fe',
        '_billing_send_choice_invoice_aruba_fe',
        '_billing_need_invoice_aruba_fe',
        '_billing_customer_type_aruba_fe',
    ];


    constructor() {

        this.aruba_fe_root_node = document.querySelector('div.edit_address');
        this.toggle_address     = document.querySelector('a.edit_address');
        if(!this.aruba_fe_root_node)
            return;

        if(this.aruba_fe_root_node.dataset.script_loaded){
            return;
        }

        this.aruba_fe_root_node.dataset.script_loaded = true;

        this.aruba_fe_elements = {};

        this.aruba_fe_fields.forEach(elId => {
            this.aruba_fe_elements[elId] = {
                input: document.querySelector(`#${elId}`),
                inputWrapper: document.querySelector(`#${elId}`).parentNode
            };
        });

        this.invoiceAlwaysRequired = window.aruba_fe_settings.invoice_always_required || 0;

        this.checkButton = document.querySelector('#aruba-fe-check-order');

        this.addEvents();

    }

    addEvents() {

        const {
            _billing_customer_type_aruba_fe,
            _billing_need_invoice_aruba_fe,
            _billing_send_choice_invoice_aruba_fe
        } = this.aruba_fe_elements;

        jQuery(_billing_send_choice_invoice_aruba_fe.input).on('change', (e) => {

            this.onBilling_send_choice_invoice_aruba_fe(e.target.value);

        });

        jQuery(_billing_need_invoice_aruba_fe.input).on('change', (e) => {

            this.onBilling_need_invoice_aruba_fe(e.target.value);

        });


        jQuery(_billing_customer_type_aruba_fe.input).on('change', (e) => {

            this.onBilling_customer_type_aruba_feChange(e.target.value);

        });

        this.onBilling_customer_type_aruba_feChange(_billing_customer_type_aruba_fe.input.value)


        this.aruba_fe_root_node.parentNode.querySelector('a.edit_address').addEventListener('click',e => {

            let currentSib = this.aruba_fe_root_node.nextElementSibling;

            while(currentSib && currentSib.tagName == 'P'){
                currentSib.style.display = 'none';
                currentSib = currentSib.nextElementSibling;
            }

        })


        if(this.checkButton){

            this.checkButton.addEventListener('click', e => {

                e.preventDefault();

                if(this.toggle_address.style.display == 'none'){
                    return;
                }

                this.toggle_address.dispatchEvent((new Event('click')));

            })

        }


    }

    onBilling_customer_type_aruba_feChange(value) {

        switch (value) {
            case 'person':
                this.hideAndShowFields([
                    '_billing_send_choice_invoice_aruba_fe',
                    '_billing_partita_iva_aruba_fe',
                    '_billing_company',
                    '_billing_pec_aruba_fe',
                    '_billing_sdi_aruba_fe'
                ], [
                    '_billing_need_invoice_aruba_fe',
                    '_billing_codice_fiscale_aruba_fe',
                ]);


                this.onBilling_need_invoice_aruba_fe(this.aruba_fe_elements._billing_need_invoice_aruba_fe.input.value);

                break;

            case 'company':

                this.hideAndShowFields(
                    ['_billing_need_invoice_aruba_fe'],
                    ['_billing_send_choice_invoice_aruba_fe',
                        '_billing_codice_fiscale_aruba_fe',
                        '_billing_partita_iva_aruba_fe',
                        '_billing_company',
                        '_billing_pec_aruba_fe',
                        '_billing_sdi_aruba_fe']);


                this.onBilling_send_choice_invoice_aruba_fe(this.aruba_fe_elements._billing_send_choice_invoice_aruba_fe.input.value);

                break;

            default:
                this.hideAndShowFields(
                    ['_billing_send_choice_invoice_aruba_fe',
                        '_billing_partita_iva_aruba_fe',
                        '_billing_company',
                        '_billing_pec_aruba_fe',
                        '_billing_sdi_aruba_fe',
                        '_billing_need_invoice_aruba_fe',
                        '_billing_codice_fiscale_aruba_fe'],
                    [])

                break;
        }
    }


    onBilling_need_invoice_aruba_fe(value) {


        if (this.invoiceAlwaysRequired)
            value = 1;



        switch (+value) {

            case 0:
                this.hideAndShowFields(['_billing_codice_fiscale_aruba_fe'], [])
                break;

            case 1:

                this.hideAndShowFields([], ['_billing_codice_fiscale_aruba_fe']);

                break;
        }
    }

    onBilling_send_choice_invoice_aruba_fe(value) {

        switch (value) {
            case 'sdi':
                this.hideAndShowFields(['_billing_pec_aruba_fe'], ['_billing_sdi_aruba_fe']);

                break;
            case 'pec':
                this.hideAndShowFields(['_billing_sdi_aruba_fe'], ['_billing_pec_aruba_fe']);
                break;
            case 'cfe':
                this.hideAndShowFields(['_billing_sdi_aruba_fe', '_billing_pec_aruba_fe'], []);
                break;
            case '*':
                this.hideAndShowFields(['_billing_sdi_aruba_fe', '_billing_pec_aruba_fe'], []);
                break;
        }

    }

    hideAndShowFields(fieldsToHide, fieldsToShow) {

        fieldsToHide.map(field => {
                if (this.aruba_fe_elements[field].inputWrapper) {
                    this.aruba_fe_elements[field].inputWrapper.style.display = 'none';
                }
                if (this.aruba_fe_elements[field].input) {
                    this.aruba_fe_elements[field].input.disabled = true;
                }
            }
        );

        fieldsToShow.map(field => {
                if (this.aruba_fe_elements[field].inputWrapper) {
                    this.aruba_fe_elements[field].inputWrapper.style.display = '';
                }
                if (this.aruba_fe_elements[field].input) {
                    this.aruba_fe_elements[field].input.disabled = false;
                }
            }
        )

    }

}

jQuery(function($){

   new ArubaFeCustomFieldsAdmin();

})

