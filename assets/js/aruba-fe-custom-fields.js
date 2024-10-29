class ArubaFeCustomFields {

    aruba_fe_fields = [
        'billing_codice_fiscale_aruba_fe',
        'billing_partita_iva_aruba_fe',
        'billing_company',
        'billing_sdi_aruba_fe',
        'billing_pec_aruba_fe',
        'billing_send_choice_invoice_aruba_fe',
        'billing_need_invoice_aruba_fe',
        'billing_customer_type_aruba_fe',
    ];


    constructor() {


        this.labels = {...aruba_fe_labels_fe};

        this.aruba_fe_root_node = document.querySelector('.woocommerce-billing-fields');

        if (!this.aruba_fe_root_node)
            this.aruba_fe_root_node = document.querySelector('.woocommerce-address-fields');

        if (!this.aruba_fe_root_node)
            return;

        if (this.aruba_fe_root_node.dataset.script_loaded) {
            return;
        }

        this.aruba_fe_root_node.dataset.script_loaded = true;

        this.aruba_fe_elements = {};

        this.aruba_fe_fields.forEach(elId => {
            this.aruba_fe_elements[elId] = {
                input: document.querySelector(`#${elId}`),
                inputWrapper: document.querySelector(`#${elId}_field`)
            };
        });

        this.alterForm();

        this.addEvents();

        if(aruba_fe_labels_fe.isCheckout) {

            try {

                jQuery('#billing_country').on('change', () => {
                    setTimeout(() => this.checkFormFields(),1);
                });

            } catch (e) {
                console.log('country doesnt exists')
            }

        }
    }

    createSeparator(title, hidden, legend = '') {

        const p = document.createElement('p');

        if (hidden)
            p.style.display = 'none';

        if (legend)
            p.innerHTML = `<h3>${title} <small>${legend}</small></h3>`;
        else
            p.innerHTML = `<h3>${title}</h3>`;

        p.classList.add('form-row-wide');

        return p;


    }

    alterForm() {

        try {

            this.mainheading = this.aruba_fe_root_node?.querySelector('h3');

            this.invoiceAlwaysRequired = this.aruba_fe_elements['billing_need_invoice_aruba_fe'].input.type === 'checkbox' ? 0 : 1;

            this.address_data = this.createSeparator(this.labels.address, false);
            this.f_data = this.createSeparator(this.labels.dati_fiscali, true, this.labels.dati_fiscali_desc);

            this.aruba_fe_root_node.insertBefore(this.aruba_fe_elements['billing_customer_type_aruba_fe'].inputWrapper, this.aruba_fe_root_node.firstChild);

            if (this.aruba_fe_elements['billing_need_invoice_aruba_fe']) {

                this.insertAfter(this.aruba_fe_elements['billing_customer_type_aruba_fe'].inputWrapper, this.aruba_fe_elements['billing_need_invoice_aruba_fe'].inputWrapper)

            }

            this.insertAfter(document.querySelector('#billing_country_field'), this.aruba_fe_elements['billing_codice_fiscale_aruba_fe'].inputWrapper);
            this.insertAfter(document.querySelector('#billing_country_field'), this.aruba_fe_elements['billing_partita_iva_aruba_fe'].inputWrapper);
            this.insertAfter(document.querySelector('#billing_email_field'), this.aruba_fe_elements['billing_send_choice_invoice_aruba_fe'].inputWrapper)
            this.insertAfter(this.aruba_fe_elements['billing_send_choice_invoice_aruba_fe'].inputWrapper, this.aruba_fe_elements['billing_pec_aruba_fe'].inputWrapper);
            this.insertAfter(this.aruba_fe_elements['billing_send_choice_invoice_aruba_fe'].inputWrapper, this.aruba_fe_elements['billing_sdi_aruba_fe'].inputWrapper);


            this.insertAfter(document.querySelector('#billing_country_field'), this.f_data)
            this.insertBefore(document.querySelector('#billing_address_1_field'), this.address_data);

        } catch (error) {
            console.log('Cant find some nodes');
        }


    }

    moveCfField(position) {

        if (position === 'afterCountry') {

            this.insertAfter(document.querySelector('#billing_country_field'), this.aruba_fe_elements['billing_codice_fiscale_aruba_fe'].inputWrapper);
            this.aruba_fe_elements['billing_codice_fiscale_aruba_fe'].inputWrapper.classList.add('form-row-last');
            this.aruba_fe_elements['billing_codice_fiscale_aruba_fe'].inputWrapper.classList.remove('form-row-wide');

        } else if (position === 'beforeVat') {

            this.insertBefore(this.aruba_fe_elements['billing_partita_iva_aruba_fe'].inputWrapper, this.aruba_fe_elements['billing_codice_fiscale_aruba_fe'].inputWrapper);
            this.aruba_fe_elements['billing_codice_fiscale_aruba_fe'].inputWrapper.classList.remove('form-row-last');
            this.aruba_fe_elements['billing_codice_fiscale_aruba_fe'].inputWrapper.classList.add('form-row-wide');

        }

    }

    insertAfter(target, element) {

        try {
            target.insertAdjacentElement('afterend', element)
        } catch (e) {
            console.error(e);
        }

    }

    insertBefore(target, element) {

        try {
            target.parentNode.insertBefore(element, target);
        } catch (e) {
            console.error(e);
        }
    }


    addEvents() {

        const {
            billing_customer_type_aruba_fe,
            billing_need_invoice_aruba_fe,
            billing_send_choice_invoice_aruba_fe
        } = this.aruba_fe_elements;

        billing_send_choice_invoice_aruba_fe.input.addEventListener('change', (e) => {

            this.onBilling_send_choice_invoice_aruba_fe(e.target.value);

        });

        billing_need_invoice_aruba_fe.input.addEventListener('click', (e) => {

            this.onBilling_need_invoice_aruba_fe(e.target.checked);

        });


        billing_customer_type_aruba_fe.input.addEventListener('change', (e) => {

            this.onBilling_customer_type_aruba_feChange(e.target.value);

        });

        this.onBilling_customer_type_aruba_feChange(billing_customer_type_aruba_fe.input.value)

    }

    onBilling_customer_type_aruba_feChange(value) {

        switch (value) {
            case 'person':
                this.hideAndShowFields([
                    'billing_send_choice_invoice_aruba_fe',
                    'billing_partita_iva_aruba_fe',
                    'billing_company',
                    'billing_pec_aruba_fe',
                    'billing_sdi_aruba_fe'
                ], [
                    'billing_need_invoice_aruba_fe',
                    'billing_codice_fiscale_aruba_fe',
                ]);
                this.f_data.style.display = 'none';
                if (this.mainheading) {

                    if (this.invoiceAlwaysRequired || this.aruba_fe_elements.billing_need_invoice_aruba_fe.input.checked)
                        this.mainheading.innerHTML = `${this.labels.main_heading_2}`;
                    else
                        this.mainheading.innerHTML = `${this.labels.main_heading_3}`;
                }
                this.moveCfField('afterCountry');
                this.onBilling_need_invoice_aruba_fe(this.aruba_fe_elements.billing_need_invoice_aruba_fe.input.checked);
                break;

            case 'company':

                this.hideAndShowFields(
                    ['billing_need_invoice_aruba_fe'],
                    ['billing_send_choice_invoice_aruba_fe',
                        'billing_codice_fiscale_aruba_fe',
                        'billing_partita_iva_aruba_fe',
                        'billing_company',
                        'billing_pec_aruba_fe',
                        'billing_sdi_aruba_fe']);
                this.f_data.style.display = '';
                this.moveCfField('beforeVat');

                if (this.mainheading) {

                    this.mainheading.innerHTML = `${this.labels.main_heading_1} <small>${this.labels.main_heading_1_desc}</small>`;

                }

                this.onBilling_send_choice_invoice_aruba_fe(this.aruba_fe_elements.billing_send_choice_invoice_aruba_fe.input.value);

                break;

            default:
                this.hideAndShowFields(
                    ['billing_send_choice_invoice_aruba_fe',
                        'billing_partita_iva_aruba_fe',
                        'billing_company',
                        'billing_pec_aruba_fe',
                        'billing_sdi_aruba_fe', 'billing_need_invoice_aruba_fe',
                        'billing_codice_fiscale_aruba_fe'],
                    [])
                this.f_data.style.display = 'none';
                break;
        }
    }


    onBilling_need_invoice_aruba_fe(value) {

        if (this.invoiceAlwaysRequired)
            value = 1;


        if (this.mainheading) {

            if (value)
                this.mainheading.innerHTML = `${this.labels.main_heading_2}`;
            else
                this.mainheading.innerHTML = `${this.labels.main_heading_3}`;
        }

        switch (+value) {

            case 0:
                this.hideAndShowFields(['billing_codice_fiscale_aruba_fe'], [])
                break;

            case 1:

                this.hideAndShowFields([], ['billing_codice_fiscale_aruba_fe']);

                break;
        }
    }

    onBilling_send_choice_invoice_aruba_fe(value) {

        switch (value) {
            case 'sdi':
                this.hideAndShowFields(['billing_pec_aruba_fe'], ['billing_sdi_aruba_fe']);
                this.aruba_fe_elements.billing_sdi_aruba_fe.inputWrapper.classList.add('form-row-wide');
                this.aruba_fe_elements.billing_sdi_aruba_fe.inputWrapper.classList.remove('form-row-first', 'form-row-last');
                break;
            case 'pec':
                this.hideAndShowFields(['billing_sdi_aruba_fe'], ['billing_pec_aruba_fe']);
                this.aruba_fe_elements.billing_pec_aruba_fe.inputWrapper.classList.add('form-row-wide');
                this.aruba_fe_elements.billing_pec_aruba_fe.inputWrapper.classList.remove('form-row-first', 'form-row-last');
                break;
            case 'cfe':
                this.hideAndShowFields(['billing_sdi_aruba_fe', 'billing_pec_aruba_fe'], []);
                break;
            case '*':
                this.hideAndShowFields(['billing_sdi_aruba_fe', 'billing_pec_aruba_fe'], []);
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

    checkFormFields() {

        try {

            this.aruba_fe_root_node.insertBefore(this.aruba_fe_elements['billing_customer_type_aruba_fe'].inputWrapper, this.aruba_fe_root_node.firstChild);

            if (this.aruba_fe_elements['billing_need_invoice_aruba_fe']) {

                this.insertAfter(this.aruba_fe_elements['billing_customer_type_aruba_fe'].inputWrapper, this.aruba_fe_elements['billing_need_invoice_aruba_fe'].inputWrapper)

            }

            this.insertAfter(document.querySelector('#billing_country_field'), this.aruba_fe_elements['billing_codice_fiscale_aruba_fe'].inputWrapper);
            this.insertAfter(document.querySelector('#billing_country_field'), this.aruba_fe_elements['billing_partita_iva_aruba_fe'].inputWrapper);
            this.insertAfter(document.querySelector('#billing_email_field'), this.aruba_fe_elements['billing_send_choice_invoice_aruba_fe'].inputWrapper)
            this.insertAfter(this.aruba_fe_elements['billing_send_choice_invoice_aruba_fe'].inputWrapper, this.aruba_fe_elements['billing_pec_aruba_fe'].inputWrapper);
            this.insertAfter(this.aruba_fe_elements['billing_send_choice_invoice_aruba_fe'].inputWrapper, this.aruba_fe_elements['billing_sdi_aruba_fe'].inputWrapper);
            this.insertAfter(document.querySelector('#billing_country_field'), this.f_data)
            this.insertBefore(document.querySelector('#billing_address_1_field'), this.address_data);

        }catch (e){
            console.error(e);
        }
    }
}

if (aruba_fe_labels_fe.isCheckout) {
    jQuery(document.body).on('init_checkout', () => {

        new ArubaFeCustomFields();
    })
} else {
    jQuery(document.body).on('wc_address_i18n_ready', () => {

        new ArubaFeCustomFields();
    })
}

