import {
    registerCheckoutBlock
} from '@woocommerce/blocks-checkout';
import metadata from './block.json';
import ArubaFatturazioneElettronicaAddressFields from "../components/ArubaFatturazioneElettronicaAddressFields";

const Block = (props) => {

    return <ArubaFatturazioneElettronicaAddressFields {...props} context={'Billing'}/>

}

const options = {
    metadata,
    component: Block
};


registerCheckoutBlock(options);