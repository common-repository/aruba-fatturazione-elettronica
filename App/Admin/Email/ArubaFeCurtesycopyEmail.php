<?php

namespace ArubaFe\Admin\Email;
if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('WC_Email', false)) {
    include_once WC_ABSPATH . 'includes/emails/class-wc-email.php';
}

use ArubaFe\Admin\Documents\ArubaFeDocument;
use ArubaFe\Admin\RestApi\ArubaFeApiRestManager;
use ArubaFe\Admin\RestApi\Helper\ArubaFeHelper;
use Automattic\WooCommerce\Admin\Notes\Notes;

class ArubaFeCurtesycopyEmail extends \WC_Email
{
    public function __construct()
    {
        $this->id = 'aruba_fe_curtesy_copy_email';
        $this->customer_email = true;
        $this->title = wp_strip_all_tags(__('Courtesy copy', 'aruba-fatturazione-elettronica'));
        $this->description = esc_html__('Personalised email for sending copy of invoice', 'aruba-fatturazione-elettronica');
        $this->template_html = 'woocommerce-custom-emails/html-courtesy-copy.php';
//        $this->template_plain = 'woocommerce-custom-emails/plain-courtesy-copy.php';
        $this->template_base = ARUBA_FE_PATH . 'templates/';
        $this->placeholders = array(
            '{order_date}' => '',
            '{order_number}' => '',
        );

        parent::__construct();

    }


    /**
     * Get email subject.
     *
     * @return string
     * @since  3.1.0
     */
    public function get_default_subject()
    {
        return esc_html__('Order invoice {order_number}', 'aruba-fatturazione-elettronica');
    }

    /**
     * Get email heading.
     *
     * @return string
     * @since  3.1.0
     */
    public function get_default_heading()
    {
        return esc_html__('Dear customer, we are sending you the invoice for your order {order_number}.', 'aruba-fatturazione-elettronica');
    }



    public function sendEmail($order,$massive = false)
    {
        $this->setup_locale();

        if (!$order){
            $this->object->add_order_note(esc_html__('ORDINE VUOTO!!!','aruba-fatturazione-elettronica'));
            return false;
            return false;
        }

        if(!is_a($order,'WC_Order'))
            $order = wc_get_order($order);

        if (!$order) {

            $this->object->add_order_note(esc_html__('non ricevo ORDINE!!!','aruba-fatturazione-elettronica'));
            return false;

        }

        $this->object = $order;

        $attachments = $this->get_attachments();

        if(empty($attachments)){
            $this->object->add_order_note(esc_html__('non ricevo allegato!!!','aruba-fatturazione-elettronica'));
            return false;
        }

        $this->recipient = $order->get_billing_email();
        $this->placeholders['{order_date}'] = wc_format_datetime($order->get_date_created());
        $this->placeholders['{order_number}'] = $order->get_order_number();
        $this->placeholders['{site_title}'] = get_bloginfo('name');

        $state = $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content(),
            $this->get_headers(),
            $attachments
        );

        if($state) {

            $this->object->add_order_note(esc_html__('The copy of the invoice was sent','aruba-fatturazione-elettronica'));

            if($attachments) {

                foreach ($attachments as $attachment) {

                    if (strpos($attachment, ARUBA_FE_PATH . 'tmp_invoices/') === 0 && (dirname($attachment) === (ARUBA_FE_PATH . 'tmp_invoices'))) {

                        wp_delete_file($attachment);

                    }
                }
            }

            return true;

        }else{

            $this->object->add_order_note(esc_html__('The courtesy copy for the order could not be sent. Please check the email sending options of your shop.','aruba-fatturazione-elettronica'));

        }

        return false;

    }

    /**
     * Get content html.
     *
     * @return string
     */
    public function get_content_html()
    {

        return wc_get_template_html(
            $this->template_html,
            array(
                'order' => $this->object,
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin' => false,
                'plain_text' => false,
                'email' => $this,
            ),
            $this->template_base,
            $this->template_base
        );
    }

    public function get_attachments()
    {

        $arubaFe = ArubaFeHelper::getInvoiceDocumentByOrderId($this->object->get_id());

        $attachments = [];

        if (is_object($arubaFe)  && $arubaFe->fileName && $arubaFe->fileContent) {

            $path = (new ArubaFeDocument())->writeAttachment($arubaFe->fileName,$arubaFe->fileContent);

            if($path){
                $attachments[] = $path;
            }

            return $attachments;

        } else {
            return null;
        }

    }

}
