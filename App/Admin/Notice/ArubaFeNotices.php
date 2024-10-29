<?php

namespace ArubaFe\Admin\Notice;

if (!defined('ABSPATH')) {
    die('No direct access allowed');
}

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\Session\ArubaFeSession;

class ArubaFeNotices
{

    protected static $instance;
    protected $session;
    private $allowedStates = array('success', 'warning', 'error', 'info', 'important');

    public function __construct()
    {
        $this->session = ArubaFeSession::getInstance();
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function renderNotices()
    {

        if ($this->session->get('fe_notices')) {

            wp_enqueue_script('aruba_fe_notices', ARUBA_FE_URL . 'assets/js/aruba-fe-notices.js',array(),'1.1',['in_footer' => true]);

            $nonce = wp_create_nonce('_aruba_fe_dismiss_notice');

            foreach ($this->session->get('fe_notices') as $notice) {

                $state = in_array($notice['state'], $this->allowedStates) ? $notice['state'] : '';

                $dismiss = $notice['dismiss'] ? 'is-dismissible' : '';

                echo '<div data-nonce="' . esc_attr($nonce) . '" class="notice notice-' . esc_attr($state) . ' aruba_fe_notice ' . esc_attr($dismiss) . '" ' .
                    ($notice['option'] ? 'data-dismiss="true" data-dismiss-key="' . esc_attr($notice['option']) . '"' : '')
                    . '>';
                if (!$notice['isHtml']) {
                    echo '<p>';
                }

                if ($notice['isHtml']) {
                    echo wp_kses_post($notice['text']);
                } else {
                    echo esc_html($notice['text']);
                }

                if (!$notice['isHtml']) {
                    echo '</p>';
                }

                if ($notice['dismiss']) {
                    echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">X</span></button>';
                }

                echo '</div>';

            }
        }

        unset($this->session->fe_notices);
    }

    public
    function addNotice($message, $key, $dismiss = true, $option = null)
    {

        if ($dismiss && $option && CustomOptions::get_option(sanitize_key($option)) == 1) {
            return;
        }

        $notices = (array)$this->session->get('fe_notices');

        $notices[] = array(
            'state' => $key,
            'text' => $message,
            'isHtml' => false,
            'dismiss' => $dismiss,
            'option' => sanitize_key($option),
        );

        $this->session->set('fe_notices', $notices);
    }

    public
    function addNoticeHtml($message, $key, $dismiss = true, $option = null)
    {
        if ($dismiss && $option && CustomOptions::get_option(sanitize_key($option)) == 1) {
            return;
        }

        $notices = (array)$this->session->get('fe_notices');

        $notices[] = array(
            'state' => $key,
            'text' => $message,
            'isHtml' => true,
            'dismiss' => $dismiss,
            'option' => sanitize_key($option),
        );

        $this->session->set('fe_notices', $notices);
    }

    public
    function hideNotice($notice_id)
    {
        if (!in_array($notice_id, ArubaFeConstants::ARUBA_FE_NOTICES)) {
            return false;
        }

        CustomOptions::add_option($notice_id, 1);

        return true;
    }
}
