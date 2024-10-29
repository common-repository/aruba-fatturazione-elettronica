
document.addEventListener("DOMContentLoaded", function() {

    document.querySelector('#billing_customer_type_aruba_fe_field').parentNode.insertBefore(
        document.querySelector('#billing_customer_type_aruba_fe_field'),
        document.querySelector('#billing_first_name_field')
    );

    document.querySelector('#billing_send_choice_invoice_aruba_fe_field').parentNode.insertBefore(
        document.querySelector('#billing_send_choice_invoice_aruba_fe_field'),
        document.querySelector('#billing_first_name_field')
    );

    document.querySelector('#billing_codice_fiscale_aruba_fe_field').parentNode.insertBefore(
        document.querySelector('#billing_codice_fiscale_aruba_fe_field'),
        document.querySelector('#billing_first_name_field')
    );

    document.querySelector('#billing_need_invoice_aruba_fe_field').parentNode.insertBefore(
        document.querySelector('#billing_need_invoice_aruba_fe_field'),
        document.querySelector('#billing_codice_fiscale_aruba_fe_field')
    );

    document.querySelector('#billing_partita_iva_aruba_fe_field').parentNode.insertBefore(
        document.querySelector('#billing_partita_iva_aruba_fe_field'),
        document.querySelector('#billing_first_name_field')
    );

    document.querySelector('#billing_sdi_aruba_fe_field').parentNode.insertBefore(
        document.querySelector('#billing_sdi_aruba_fe_field'),
        document.querySelector('#billing_first_name_field')
    );

    document.querySelector('#billing_pec_aruba_fe_field').parentNode.insertBefore(
        document.querySelector('#billing_pec_aruba_fe_field'),
        document.querySelector('#billing_first_name_field')
    );

    document.querySelector('#billing_company_field').parentNode.insertBefore(
        document.querySelector('#billing_company_field'),
        document.querySelector('#billing_codice_fiscale_aruba_fe_field')
    );

    document.querySelector('#billing_company_field').parentNode.insertBefore(
        document.querySelector('p#billing_sdi_aruba_fe_field'),
        document.querySelector('#billing_company_field')
    );

    document.querySelector('#billing_company_field').parentNode.insertBefore(
        document.querySelector('p#billing_pec_aruba_fe_field'),
        document.querySelector('#billing_company_field')
    );



    document.querySelector('p#billing_pec_aruba_fe_field').style.display = 'none';
    document.querySelector('p#billing_sdi_aruba_fe_field').style.display = 'none';
    document.querySelector('#billing_need_invoice_aruba_fe_field').style.display = 'none';
    document.querySelector('#billing_send_choice_invoice_aruba_fe_field').style.display = 'none';
    document.querySelector('#billing_company_field').style.display = 'none';
    document.querySelector('#billing_codice_fiscale_aruba_fe_field').style.display = 'none';
    document.querySelector('#billing_partita_iva_aruba_fe_field').style.display = 'none';
    document.querySelector('#billing_sdi_aruba_fe_field').style.display = 'none';
    document.querySelector('#billing_pec_aruba_fe_field').style.display = 'none';

    jQuery('select#billing_customer_type_aruba_fe').change(function() {

        if (this.value == '*') {

            let $ids_h = [
                'billing_partita_iva_aruba_fe_field',
                'billing_company_field',
                'billing_pec_aruba_fe_field',
                'billing_sdi_aruba_fe_field',
                'billing_codice_fiscale_aruba_fe_field',
                'billing_need_invoice_aruba_fe_field',
                'billing_send_choice_invoice_aruba_fe_field'
            ];

            $ids_h.map(function(val,i){
                jQuery('#'+val).hide();
                jQuery('#'+val).removeClass('validate-required').removeClass('woocommerce-validated').removeClass('woocomerce-invalid').removeClass('woocommerce-invalid-required-field');
            });

        } else if (this.value == 'company') {

            let billingCodiceFiscaleField = document.querySelector('#billing_codice_fiscale_aruba_fe_field');
            billingCodiceFiscaleField.classList.remove('form-row-wide');
            billingCodiceFiscaleField.classList.add('form-row-first');

            let $ids_s = [
                'billing_partita_iva_aruba_fe_field',
                'billing_company_field',
                'billing_send_choice_invoice_aruba_fe_field',
                'billing_codice_fiscale_aruba_fe_field'
            ];

            $ids_s.map(function(val,i){
                jQuery('#'+val).show();
            });


            let $ids_h = [
                'billing_sdi_aruba_fe_field',
                'billing_pec_aruba_fe_field',
                'billing_need_invoice_aruba_fe_field'
            ];

            $ids_h.map(function(val,i){
                jQuery('#'+val).hide();
                jQuery('#'+val).removeClass('validate-required').removeClass('woocommerce-validated').removeClass('woocomerce-invalid').removeClass('woocommerce-invalid-required-field');
            });

        } else if (this.value == 'person') {


            jQuery('#billing_send_choice_invoice_aruba_fe_field').hide();
            jQuery('#billing_need_invoice_aruba_fe_field').show();
            jQuery('#billing_customer_type_aruba_fe_field').removeClass('woocommerce-validated');
            jQuery('#billing_customer_type_aruba_fe_field').removeClass('woocommerce-invalid');
            jQuery('#billing_customer_type_aruba_fe_field').removeClass('woocommerce-invalid-required-field');

            jQuery('#billing_codice_fiscale_aruba_fe_field').show();
            jQuery('#billing_codice_fiscale_aruba_fe_field').addClass('form-row-wide');
            jQuery('#billing_codice_fiscale_aruba_fe_field').removeClass('form-row-first');
            let $ids = [
                'billing_partita_iva_aruba_fe_field',
                'billing_company_field',
                'billing_pec_aruba_fe_field',
                'billing_sdi_aruba_fe_field'
            ];

            $ids.map(function(val,i){
                jQuery('#'+val).hide();
                jQuery('#'+val).removeClass('validate-required').removeClass('woocommerce-validated');
            });

        }

	});

    setTimeout(function(){
        var element = document.querySelector('select#billing_customer_type_aruba_fe');
        var event = new Event("change");
        element.dispatchEvent(event);
    },1000)

    document.querySelector('select#billing_send_choice_invoice_aruba_fe').addEventListener('change', function() {
        if (this.value == '*') {
            document.querySelector('p#billing_pec_aruba_fe_field').style.display = 'none';
            document.querySelector('p#billing_sdi_aruba_fe_field').style.display = 'none';
        } else if (this.value == 'sdi') {
            document.querySelector('p#billing_pec_aruba_fe_field').style.display = 'none';
            document.querySelector('p#billing_sdi_aruba_fe_field').style.display = 'block';
            document.querySelector('p#billing_sdi_aruba_fe_field').classList.add('form-row-wide');
            document.querySelector('p#billing_sdi_aruba_fe_field').classList.remove('form-row-first');
        } else if (this.value == 'pec') {
            document.querySelector('p#billing_pec_aruba_fe_field').style.display = 'block';
            document.querySelector('p#billing_sdi_aruba_fe_field').style.display = 'none';
            document.querySelector('p#billing_pec_aruba_fe_field').classList.add('form-row-wide');
            document.querySelector('p#billing_pec_aruba_fe_field').classList.remove('form-row-last');
        }
    });

    document.querySelector('select#billing_send_choice_invoice_aruba_fe').addEventListener('change', function() {
        if (this.value == '*') {
            document.querySelector('#billing_codice_fiscale_aruba_fe_field').style.display = 'block';
            document.querySelector('#billing_partita_iva_aruba_fe_field').style.display = 'block';

            document.querySelector('#billing_pec_aruba_fe_field').style.display = 'none';
            document.querySelector('#billing_sdi_aruba_fe_field').style.display = 'none';
        }

        if (this.value == 'sdi') {
            document.querySelector('#billing_codice_fiscale_aruba_fe_field').style.display = 'block';
            document.querySelector('#billing_partita_iva_aruba_fe_field').style.display = 'block';

            document.querySelector('#billing_pec_aruba_fe_field').style.display = 'none';
            document.querySelector('#billing_sdi_aruba_fe_field').style.display = 'block';
            document.querySelector('#billing_sdi_aruba_fe_field').insertAfter(document.querySelector('#billing_company_field'));
            document.querySelector('#billing_sdi_aruba_fe_field').classList.add('form-row-wide');
            document.querySelector('#billing_sdi_aruba_fe_field').classList.remove('form-row-first');
        }

        if (this.value == 'pec') {
            document.querySelector('#billing_codice_fiscale_aruba_fe_field').style.display = 'block';
            document.querySelector('#billing_partita_iva_aruba_fe_field').style.display = 'block';

            document.querySelector('#billing_pec_aruba_fe_field').style.display = 'block';
            document.querySelector('#billing_sdi_aruba_fe_field').style.display = 'none';
            document.querySelector('#billing_pec_aruba_fe_field').insertAfter(document.querySelector('#billing_company_field'));
            document.querySelector('#billing_pec_aruba_fe_field').classList.add('form-row-wide');
            document.querySelector('#billing_pec_aruba_fe_field').classList.remove('form-row-last');

        }

        if (this.value == 'cfe') {
            document.querySelector('#billing_pec_aruba_fe_field').style.display = 'none';
            document.querySelector('#billing_sdi_aruba_fe_field').style.display = 'none';
            document.querySelector('#billing_codice_fiscale_aruba_fe_field').style.display = 'none';
            document.querySelector('#billing_partita_iva_aruba_fe_field').style.display = 'none';
        }

    });


    setTimeout(()=>{
        const $choice_invoice = document.querySelector('select#billing_send_choice_invoice_aruba_fe');

        if ($choice_invoice.value == 'cfe') {
            document.querySelector('#billing_pec_aruba_fe_field').style.display = 'none';
            document.querySelector('#billing_sdi_aruba_fe_field').style.display = 'none';
            document.querySelector('#billing_codice_fiscale_aruba_fe_field').style.display = 'none';
            document.querySelector('#billing_partita_iva_aruba_fe_field').style.display = 'none';
        }
    },1000);


});


    jQuery( function($){
        $('#billing_need_invoice_aruba_fe,#billing_customer_type_aruba_fe').change(function (){
            checkCF();
        });

        function checkCF(){
            let need_invoice_fe = $('#billing_need_invoice_aruba_fe').val();
            let customer_type_fe = $('#billing_customer_type_aruba_fe').val();

            if( customer_type_fe == 'person' && need_invoice_fe != 1 ){
                $('#billing_codice_fiscale_aruba_fe_field').hide()
            } else if ( customer_type_fe != '*' ) {
                $('#billing_codice_fiscale_aruba_fe_field').show()
            }
        }
    });