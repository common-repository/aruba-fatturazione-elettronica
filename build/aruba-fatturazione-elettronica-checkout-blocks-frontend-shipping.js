(()=>{"use strict";const e=window.React,a=window.wc.blocksCheckout,t=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks-shipping","version":"1.0.0","title":"Aruba Elettronic Invoicing custom checkout blocks","category":"woocommerce","parent":["woocommerce/checkout-shipping-address-block"],"attributes":{"lock":{"type":"object","default":{"remove":true,"move":true}}},"textdomain":"aruba-fatturazione-elettronica","editorScript":"file:./build/index.js"}'),r=window.wp.element,i=window.wc.wcSettings,n=window.wp.data,o=window.wp.components,l=window.wp.i18n,c=/^([0-9]{11}|[A-Z]{6}[0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z])$/i,u=/^[A-Z0-9]{11,16}$/i,_=/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,b=/^[A-Z0-9]{6,7}$/i,s={height:"auto",padding:"1.5em .5em 1.5em",fontSize:"1em",borderRadius:"4px"},f=(0,i.getSetting)("aruba-fatturazione-elettronica-checkout-blocks_data",{billing_option_aruba_fe_need_invoice:"",billing_codice_fiscale_aruba_fe:!1,billing_customer_type_aruba_fe:"",billing_partita_iva_aruba_fe:"",billing_send_choice_invoice_aruba_fe:"",billing_sdi_aruba_fe:"",billing_pec_aruba_fe:"",billing_need_invoice_aruba_fe:!1});function m({checkoutExtensionData:t,cart:i,context:m}){const{billingAddress:{country:p}}=i,[g,d]=(0,r.useState)(null),[h,z]=(0,r.useState)(f.billing_need_invoice_aruba_fe),[k,v]=(0,r.useState)(f.billing_codice_fiscale_aruba_fe),[E,w]=(0,r.useState)(f.billing_customer_type_aruba_fe),[S,y]=(0,r.useState)(f.billing_partita_iva_aruba_fe),[C,I]=(0,r.useState)(f.billing_send_choice_invoice_aruba_fe),[T,A]=(0,r.useState)(f.billing_sdi_aruba_fe),[V,x]=(0,r.useState)(f.billing_pec_aruba_fe),{setExtensionData:N,extensionData:D}=t,{VALIDATION_STORE_KEY:R,CHECKOUT_STORE_KEY:F}=window.wc.wcBlocksData,{setValidationErrors:M,clearValidationError:Z}=(0,n.useDispatch)(R),[P,B]=(0,r.useState)(!1),[q,O]=(0,r.useState)(!1),[U,$]=(0,r.useState)(!1),[H,L]=(0,r.useState)(!1),[j,K]=(0,r.useState)(!1),[Q,Y]=(0,r.useState)(!1),[J,G]=(0,r.useState)(!1),[W,X]=(0,r.useState)(!1),[ee,ae]=(0,r.useState)(!1),[te,re]=(0,r.useState)(!1);return(0,n.useSelect)((e=>{const a=e(F);d(a.getUseShippingAsBilling())}),[]),(0,r.useEffect)((()=>{}),[g]),(0,r.useEffect)((()=>{N("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks","billing_codice_fiscale_aruba_fe",k),N("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks","billing_customer_type_aruba_fe",E),N("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks","billing_partita_iva_aruba_fe",S),N("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks","billing_send_choice_invoice_aruba_fe",C),N("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks","billing_sdi_aruba_fe",T),N("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks","billing_pec_aruba_fe",V),N("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks","billing_need_invoice_aruba_fe",h?"1":"0");const e="IT"===p?c:u;k.length>0&&!q&&!e.test(k)?(B(!0),M({"aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_codice_fiscale_aruba_fe":{message:(0,l.__)("Fiscal Code is not filled in correctly","aruba-fatturazione-elettronica")}})):(B(!1),Z("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_codice_fiscale_aruba_fe")),S.length>0&&!H&&(S.length<11||S.length>16)?($(!0),M({"aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_partita_iva_aruba_fe":{message:wp.i18n.__("VAT number not filled in correctly","aruba-fatturazione-elettronica")}})):($(!1),Z("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_partita_iva_aruba_fe")),T.length>0&&!J&&!b.test(T)?(K(!0),M({"aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_sdi_aruba_fe":{message:(0,l.__)("Company Destination Reference is not in the correct format","aruba-fatturazione-elettronica")}})):(K(!1),Z("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_sdi_aruba_fe")),V.length>0&&!W&&!_.test(V)?(Y(!0),M({"aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_pec_aruba_fe":{message:(0,l.__)("Company PEC is not in the correct format","aruba-fatturazione-elettronica")}})):(Y(!1),Z("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_pec_aruba_fe")),E?Z("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_customer_type_aruba_fe"):M({"aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_customer_type_aruba_fe":{message:(0,l.__)("Select customer type is a required field","aruba-fatturazione-elettronica")}}),"company"!==E||C?Z("aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_send_choice_invoice_aruba_fe"):M({"aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks/billing_send_choice_invoice_aruba_fe":{message:(0,l.__)("How would you like to receive an invoice is a required field","aruba-fatturazione-elettronica")}})}),[h,E,k,S,C,T,V,C,g,q,H,p,W,J]),g||"Shipping"!==m?(0,e.createElement)(e.Fragment,null,(0,e.createElement)(o.SelectControl,{style:{...s},value:E,options:[{label:(0,l.__)("Customer type","aruba-fatturazione-elettronica"),value:""},{label:(0,l.__)("Physical person","aruba-fatturazione-elettronica"),value:"person"},{label:(0,l.__)("Company","aruba-fatturazione-elettronica"),value:"company"}],className:E?"aruba-fe-select":"has-error aruba-fe-error aruba-fe-select",onChange:e=>{w(e),ae(!0)}}),!E&&ee&&(0,e.createElement)(a.ValidationInputError,{errorMessage:(0,l.__)("Select customer type is a required field","aruba-fatturazione-elettronica")}),"person"!==E||f.billing_option_aruba_fe_need_invoice?(0,e.createElement)(a.ValidatedTextInput,{type:"hidden",value:"1"}):(0,e.createElement)(a.CheckboxControl,{id:"billing_need_invoice_aruba_fe",checked:h?1:0,onChange:()=>{z(!h)}},(0,l.__)("Do you want an invoice?","aruba-fatturazione-elettronica")),"company"===E&&(0,e.createElement)("h2",{className:"wc-block-components-title wc-block-components-checkout-step__title"},(0,l.__)("Fiscal data","aruba-fatturazione-elettronica")," ",(0,l.__)("(One of the two fields required)","aruba-fatturazione-elettronica")),("company"===E||"person"===E&&(f.billing_option_aruba_fe_need_invoice||h))&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)(a.TextInput,{onBlur:()=>O(!1),onInput:()=>O(!0),className:P?"has-error aruba-fe-error":"",value:k,onChange:v,label:(0,l.__)("Tax code","aruba-fatturazione-elettronica")}),P&&(0,e.createElement)(a.ValidationInputError,{errorMessage:(0,l.__)("Tax code is not in the correct format","aruba-fatturazione-elettronica")})),"company"===E&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)(a.TextInput,{className:U?"has-error aruba-fe-error":"",onBlur:()=>L(!1),onInput:()=>L(!0),value:S,onChange:y,label:(0,l.__)("VAT number","aruba-fatturazione-elettronica")}),U&&(0,e.createElement)(a.ValidationInputError,{errorMessage:(0,l.__)("VAT number is not in the correct format","aruba-fatturazione-elettronica")}),(0,e.createElement)(o.SelectControl,{required:!0,style:{...s},value:C,label:(0,l.__)("How would you like to receive an invoice?","aruba-fatturazione-elettronica"),options:[{label:(0,l.__)("Select","aruba-fatturazione-elettronica"),value:""},{label:(0,l.__)("SDI (Recipient Code)","aruba-fatturazione-elettronica"),value:"sdi"},{label:(0,l.__)("PEC","aruba-fatturazione-elettronica"),value:"pec"},{label:(0,l.__)("No identifier","aruba-fatturazione-elettronica"),value:"*"},{label:(0,l.__)("Foreign invoice number","aruba-fatturazione-elettronica"),value:"cfe"}],className:C?"aruba-fe-select":"has-error aruba-fe-error aruba-fe-select",onChange:e=>{I(e),re(!0)}}),!C&&te&&(0,e.createElement)(a.ValidationInputError,{errorMessage:(0,l.__)("How would you like to receive an invoice is a required field","aruba-fatturazione-elettronica")}),"sdi"===C&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)(a.TextInput,{onBlur:()=>G(!1),onInput:()=>G(!0),className:j?"has-error aruba-fe-error":"",value:T,onChange:A,label:(0,l.__)("SDI (Recipient Code)","aruba-fatturazione-elettronica")}),j&&(0,e.createElement)(a.ValidationInputError,{errorMessage:(0,l.__)("Company Destination Reference is not in the correct format","aruba-fatturazione-elettronica")})),"pec"===C&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)(a.TextInput,{onBlur:()=>X(!1),onInput:()=>X(!0),className:Q?"has-error aruba-fe-error":"",value:V,onChange:x,label:(0,l.__)("PEC","aruba-fatturazione-elettronica")}),Q&&(0,e.createElement)(a.ValidationInputError,{errorMessage:(0,l.__)("Company PEC is not in the correct format","aruba-fatturazione-elettronica")})))):(0,e.createElement)(e.Fragment,null)}const p={metadata:t,component:a=>(0,e.createElement)(m,{...a,context:"Shipping"})};(0,a.registerCheckoutBlock)(p)})();