window.addEventListener('DOMContentLoaded',()=>{

    const billing_customer_type_aruba_fe = document.querySelector('[name="billing_customer_type_aruba_fe"]');
    if(!billing_customer_type_aruba_fe)
        return;

    const checkFunction = (billing_customer_type_aruba_fe)=>{

        billing_customer_type_aruba_fe.addEventListener('change',()=>{
            jQuery( document.body ).trigger( 'update_checkout' );
        })

    }

    checkFunction(billing_customer_type_aruba_fe);

});