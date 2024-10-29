<?php

namespace ArubaFe\Admin\Constants;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

/**
 *
 */
abstract class ArubaFeConstants {

	const ARUBA_FE_ORDER_ID               = '_aruba_fe_order_id';
	const ARUBA_FE_ORDER_ITEM_DISCOUNT    = '_aruba_fe_order_item_discount';
	const ARUBA_FE_ORDER_ITEM_TAX_RATE    = '_aruba_fe_order_item_tax_rate';
	const ARUBA_FE_ORDER_ITEM_TAX_RATE_ID = '_aruba_fe_order_item_tax_rate_id';
	const ARUBA_FE_ORDER_REGISTRED        = '_aruba_fe_order_registred';
	const ARUBA_FE_INVOICE_DATA           = '_aruba_fe_invoices';

	const ARUBA_FE_DRAFTS_DATA  = '_aruba_fe_drafts';
	const ARUBA_FE_ORDER_STATES = array(
		0 => 'Incompleto',
		1 => 'Completo',
		2 => 'Inviato',
		3 => 'Approvato',
		4 => 'Rifiutato',
	);

	const ARUBA_FE_DRAFT_STATES         = array(
		0 => 'Incompleto',
		1 => 'Completo',
	);
	const ARUBA_FE_ORDER_STATUS         = '_aruba_fe_order_status';
	const ARUBA_FE_INVOICE_STATES       = array(
		0  => 'Incompleto',
		1  => 'Presa in carico',
		2  => 'Errore elaborazione',
		3  => 'Inviata a SDI',
		4  => 'Scartata',
		5  => 'Non consegnata',
		6  => 'Recapito impossibile',
		7  => 'Consegnato',
		8  => 'Accettata',
		9  => 'Rifiutata',
		10 => 'Decorrenza termini',
	);
	const ARUBA_FE_EXPIRE_MIN_TIME_LEFT = 20;
	const ARUBA_FE_TOKEN                = '_aruba_fe_token';
	const ARUBA_FE_TOKEN_TYPE           = '_aruba_fe_token_type';
	const ARUBA_FE_TOKEN_USERNAME       = '_aruba_fe_token_username';
	const ARUBA_FE_TOKEN_EXPIRE         = '_aruba_fe_token_expire';

	const ARUBA_FE_TOKEN_LAST_REFRESH      = '_aruba_fe_token_last_refresh';
	const ARUBA_FE_ALLOWED_CURRENCY        = 'EUR';
	const ARUBA_FE_ALLOWED_CURRENCY_SYMBOL = '€';
	const ARUBA_FE_CURTESY_SENT            = '_auba_fe_curtesy_auto_sent';
	const ARUBA_FE_NOTICES                 = array(
		'missing_config'          => '_aruba_fe_missing_hide_config',
		'missing_config_done'     => '_aruba_fe_missing_hide_config_done',
		'missing_payments_config' => '_aruba_fe_missing_hide_payments_config',
		'domain_config'           => '_aruba_fe_hide_domain_config',
		'hide_incompatible'       => '_aruba_fe_hide_incompatible',
		'hide_euro_error'         => '_aruba_fe_euro_hide_config',
        'hide_tax_error'          => '_aruba_fe_tax_hide'

	);
	const ARUBA_FE_ORDER_INELEGGIBLE = '_aruba_fe_ignore_order';

	const ARUBA_FE_CRON_HOOK = 'aruba_fe_cron_job';

	const ARUBA_FE_CRON_HOOK_ORDERS = 'aruba_fe_cron_job_orders';

	const ARUBA_FE_INVOICE_ERROR       = '_aruba_fe_invoice_error';
	const ARUBA_FE_DRAFT_INVOICE_ERROR = '_aruba_fe_draft_invoice_error';
	const ARUBA_FE_DEFAULT_ZERO_RATE   = '_aruba_fe_zero_rate';
	const ARUBA_FE_MIN_ERROR_INTERVAL  = 60;
	const ARUBA_FE_MAX_ERRORS          = 3;
	const ARUBA_FE_IN_ERROR            = '_aruba_fe_in_error';
	const ARUBA_FE_FCDONE              = '_aruba_fe_first_config_done';
	const ARUBA_FE_SENDFDATE           = '_aruba_fe_send_from_date';
	const ARUBA_FE_KEEP_SETTINGS       = '_aruba_fe_keep_settings';
	const ARUBA_FE_CONFIG_BASE         = 'woocommerce_page_aruba-fatturazione-elettronica';
    const ARUBA_FE_ERRORS_LIST         = '_aruba_fe_errors_list';
}