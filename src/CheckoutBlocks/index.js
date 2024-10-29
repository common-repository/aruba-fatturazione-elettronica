import metadata from './billing/block.json';
import metadataShipping from './shipping/block.json';

import {registerBlockType,getBlockType} from '@wordpress/blocks';

import {Edit} from './billing/edit';
import {EditShipping} from "./shipping/edit";

import {SVG} from '@wordpress/components';

import arubaFatturazioneElettronica from '../assets/images/login-fatturazione-elettronica.svg';

if(!getBlockType('aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks')) {
    registerBlockType(metadata, {
        icon: {
            src: (
                <SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                    {arubaFatturazioneElettronica}
                </SVG>
            ),
            foreground: '#FFFFFF',
        },
        edit: Edit
    });

    registerBlockType(metadataShipping, {
        icon: {
            src: (
                <SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                    {arubaFatturazioneElettronica}
                </SVG>
            ),
            foreground: '#FFFFFF',
        },
        edit: EditShipping
    });

}