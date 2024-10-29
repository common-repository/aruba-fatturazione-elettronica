<?php

namespace ArubaFe\Admin\Cron;

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\RestApi\ArubaFeApiRestManager;
use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;
use ArubaFe\Admin\Traits\ArubaFeLogTrait;
use WC_Order_Query;
use WP_Query;

class ArubaFeCronExec
{
    use ArubaFeLogTrait;

    public function __construct()
    {

        $this->options = new ArubaFeOptionParser();

    }

    public function every_five_minute($schedules)
    {

        $schedules['aruba_fe_five_minutes'] = array(
            'interval' => 60 * 5,
            'display' => esc_html__('Every 5 minutes', 'aruba-fatturazione-elettronica')
        );

        return $schedules;

    }

    public function every_eleven_minutes($schedules)
    {

        $schedules['aruba_fe_eleven_minutes'] = array(
            'interval' => 60 * 11,
            'display' => esc_html__('Every 11 minutes', 'aruba-fatturazione-elettronica')
        );

        return $schedules;

    }

    public function executeFeCronOrders()
    {

    // @codingStandardsIgnoreStart

        $args = array(
            'post_type' => 'shop_order',
            'post_status' => ['wc-completed', 'wc-on-hold', 'wc-processing'],
            'posts_per_page' => 20,
            'orderby' => 'ID',
            'order' => 'DESC',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => ArubaFeConstants::ARUBA_FE_ORDER_ID,
                    'value' => '',
                    'compare' => '!='
                ],
                [
                    'relation' => 'OR',
                    [
                        'key' => ArubaFeConstants::ARUBA_FE_INVOICE_DATA,
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key' => ArubaFeConstants::ARUBA_FE_INVOICE_DATA,
                        'value' => '',
                        'compare' => '='
                    ],
                    [
                        'key' => ArubaFeConstants::ARUBA_FE_INVOICE_DATA,
                        'value' => 'a:0:{}',
                        'compare' => '='
                    ],

                ]
            ]
        );



        $custom_query = new WP_Query($args);

        // @codingStandardsIgnoreEnd

        $arrayOfOrdersId = [];

        if ($custom_query->have_posts()) {

            while ($custom_query->have_posts()) {

                $custom_query->the_post();

                $order_id = get_the_ID();

                $arrayOfOrdersId[] = $order_id;

            }

        }

        if ($arrayOfOrdersId) {

            ArubaFeApiRestManager::getInstance()->getOrdersStatus($arrayOfOrdersId);

        }

    }

    public function executeFeCronMail()
    {

        $enabled = $this->options->getConfigOption('send_coutesy_copy') === 'automatic_send_coutesy_copy';

        if (!$enabled)
            return true;

        $fromDate = CustomOptions::get_option('_aruba_fe_send_from_date', '0000-00-00 00:00:00');

        $date = \DateTime::createFromFormat("Y-m-d H:i:s", $fromDate);

        // @codingStandardsIgnoreStart

        $args = array(
            'post_type' => 'shop_order',
            'post_status' => ['wc-completed', 'wc-on-hold', 'wc-processing'],
            'posts_per_page' => 5,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => ArubaFeConstants::ARUBA_FE_INVOICE_DATA,
                    'value' => '',
                    'compare' => '!='
                ],
                [
                    'key' => ArubaFeConstants::ARUBA_FE_INVOICE_DATA,
                    'value' => 'a:0:{}',
                    'compare' => '!='
                ],
                [
                    'relation' => 'OR',
                    [
                        'key' => ArubaFeConstants::ARUBA_FE_CURTESY_SENT,
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key' => ArubaFeConstants::ARUBA_FE_CURTESY_SENT,
                        'compare' => 'IN',
                        'value' => ['retry_1','retry_2','retry_3']
                    ],
                ]
            ]
        );

        if ($date && $date->format("Y-m-d H:i:s") === $fromDate) {
            $args['date_query'] = array(
                'column' => 'post_date_gmt',
                array(
                    'after' => $fromDate,
                    'inclusive' => true,
                )
            );
        }

        $custom_query = new WP_Query($args);

        // @codingStandardsIgnoreEnd

        if ($custom_query->have_posts()) {

            while ($custom_query->have_posts()) {

                $custom_query->the_post();

                $mailer = WC()->mailer()->get_emails()['ArubaFeCurtesycopyEmail'];

                $order_id = get_the_ID();

                try {

                    $order = wc_get_order($order_id);

                    if ($order && !is_wp_error($order)) {

                        if ($mailer->sendEmail($order_id, false)) {

                            $order->update_meta_data(ArubaFeConstants::ARUBA_FE_CURTESY_SENT, 'yes');
                            $order->save_meta_data();

                        } else {

                            $currentSendMeta = $order->get_meta(ArubaFeConstants::ARUBA_FE_CURTESY_SENT);

                            $setMeta = '';

                            if(!$currentSendMeta)
                                $setMeta = 'retry_1';

                            if($currentSendMeta == 'retry_1')
                                $setMeta = 'retry_2';

                            if($currentSendMeta == 'retry_2')
                                $setMeta = 'retry_3';

                            if($currentSendMeta == 'retry_3'){
                                $setMeta = 'no';
                                $order->add_order_note(esc_html__('The copy of the invoice can`t be sent', 'aruba-fatturazione-elettronica'));
                            }

                            $order->update_meta_data(ArubaFeConstants::ARUBA_FE_CURTESY_SENT, $setMeta);

                            $order->save_meta_data();

                        }

                    }

                } catch (\Exception $e) {

                    $order->update_meta_data(ArubaFeConstants::ARUBA_FE_CURTESY_SENT, 'no');
                    $order->save_meta_data();
                    $order->add_order_note(esc_html__('The copy of the invoice can`t be sent', 'aruba-fatturazione-elettronica'));

                }

            }

            wp_reset_postdata();

        }

    }


}