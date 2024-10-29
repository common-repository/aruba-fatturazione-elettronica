<?php

namespace ArubaFe\Admin\Traits;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\CustomOptions;

trait ArubaFeLogTrait
{

    private function getWpFilesystem()
    {

        global $wp_filesystem;

        if (!function_exists('request_filesystem_credentials')) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
        }

        ob_start();
        $credentials = request_filesystem_credentials('');
        ob_end_clean();

        if (false === $credentials || !WP_Filesystem($credentials)) {
            return false;
        }

        return $wp_filesystem;

    }

    private function getRandomName(int $length = 32)
    {

        $length = max($length, 32);

        return bin2hex(random_bytes($length));

    }

    private function getLogFolder()
    {

        if (!defined('ARUBA_FE_LOG_ENABLED') || ARUBA_FE_LOG_ENABLED != 1)
            return false;

        $folder = CustomOptions::get_option('_aruba_fe_log_folder');

        if (!$folder) {

            $folder = $this->getRandomName(16);

            CustomOptions::add_option('_aruba_fe_log_folder', sanitize_text_field($folder));

        }

        if (wp_mkdir_p(ARUBA_FE_PATH . '/App/log/' . $folder)) {

            $wp_filesystem = $this->getWpFilesystem();

            try {

                $htaccess = ARUBA_FE_PATH . '/App/log/.htaccess';

                if (!$wp_filesystem->exists($htaccess) || !$wp_filesystem->size($htaccess)) {

                    if (!$wp_filesystem->put_contents($htaccess, "deny from all\n")) {

                        return false;

                    }
                }

                $htaccess = ARUBA_FE_PATH . '/App/log/' . sanitize_file_name($folder) . '/.htaccess';

                if (!$wp_filesystem->exists($htaccess) || !$wp_filesystem->size($htaccess)) {

                    if ($wp_filesystem->put_contents($htaccess, "deny from all\n")) {

                        return ARUBA_FE_PATH . '/App/log/' . $folder;

                    } else {

                        return false;

                    }

                } else {

                    return ARUBA_FE_PATH . '/App/log/' . $folder;

                }

            } catch (\Exception $e) {
                return false;
            }

        }

        return false;

    }

    protected function writeLog($httpStatus, $data, $endpoint, $args, $falg = FILE_APPEND)
    {

        $log_folder = $this->getLogFolder();

        if (!$log_folder) {
            return;
        }

        try {

            $wp_filesystem = $this->getWpFilesystem();

            $filename = CustomOptions::get_option('_aruba_fe_response_log');

            if (!$filename) {

                $filename = $this->getRandomName() . '_rs_log';

                CustomOptions::add_option('_aruba_fe_response_log', sanitize_text_field($filename));

            }

            $log_file = $log_folder . '/' . sanitize_file_name($filename) . '.txt';

            $endpoint = esc_url_raw($endpoint);
            $httpStatus = sanitize_text_field($httpStatus);

            $time = gmdate('Y-m-d H:i:s');
            $text = "##################\n";
            $text .= "[$time]\n";
            $text .= "[$endpoint]\n";
            $text .= "[HTTP: $httpStatus]\n";
            $text .= "[ARGS]\n";
            $text .= !empty($args) ? wp_json_encode($args) : null;
            $text .= "\n[RESPONSE]\n";
            $text .= is_object($data) ? wp_json_encode($data) : wp_kses_post($data);
            $text .= "\n##################\n";


            $contents = $wp_filesystem->get_contents($log_file) . "\n" . $text;
            $wp_filesystem->put_contents($log_file, $contents);

        } catch (\Exception $e) {
            return;
        }
    }

    protected function writeCallLog($args, $endpoint, $falg = FILE_APPEND)
    {
        $log_folder = $this->getLogFolder();

        if (!$log_folder) {
            return;
        }

        try {

            $wp_filesystem = $this->getWpFilesystem();

            $filename = CustomOptions::get_option('_aruba_fe_call_log');

            if (!$filename) {

                $filename = $this->getRandomName() . '_call_log';

                CustomOptions::add_option('_aruba_fe_call_log', sanitize_text_field($filename));

            }

            $log_file = $log_folder . '/' . sanitize_file_name($filename) . '.txt';

            $endpoint = esc_url_raw($endpoint);

            $time = gmdate('Y-m-d H:i:s');
            $text = "##################\n";
            $text .= "[$time]\n";
            $text .= "[$endpoint]\n";
            $text .= "[ARGS]\n";
            $text .= !empty($args) ? wp_json_encode($args) : null;
            $text .= "\n##################\n";

            $contents = $wp_filesystem->get_contents($log_file) . "\n" . $text;
            $wp_filesystem->put_contents($log_file, $contents);

        } catch (\Exception $e) {
            return;
        }
    }

    protected function writeOrderLog($args, $orderID, $falg = FILE_APPEND)
    {
        $log_folder = $this->getLogFolder();

        if (!$log_folder) {
            return;
        }

        try {

            $wp_filesystem = $this->getWpFilesystem();

            $filename = CustomOptions::get_option('_aruba_fe_orders_log');

            if (!$filename) {

                $filename = $this->getRandomName() . '_ord_log';

                CustomOptions::add_option('_aruba_fe_orders_log', sanitize_text_field($filename));

            }

            $log_file = $log_folder . '/' . sanitize_file_name($filename) . '.txt';

            $time = gmdate('Y-m-d H:i:s');
            $text = "##################\n";
            $text .= "[$time]\n";
            $text .= "[$orderID]\n";
            $text .= "[ARGS]\n";
            $text .= !empty($args) ? wp_json_encode($args) : null;
            $text .= "\n##################\n";

            $contents = $wp_filesystem->get_contents($log_file) . "\n" . $text;
            $wp_filesystem->put_contents($log_file, $contents);

        } catch (\Exception $e) {
            return;
        }
    }

    protected function writeGenericLog($args, $falg = FILE_APPEND)
    {

        $log_folder = $this->getLogFolder();

        if (!$log_folder) {
            return;
        }

        try {

            $wp_filesystem = $this->getWpFilesystem();

            $filename = CustomOptions::get_option('_aruba_fe_orders_log');

            if (!$filename) {

                $filename = $this->getRandomName() . '_ord_log';

                CustomOptions::add_option('_aruba_fe_orders_log', sanitize_text_field($filename));

            }

            $log_file = $log_folder . '/' . sanitize_file_name($filename) . '.txt';

            $time = gmdate('Y-m-d H:i:s');
            $text = "##################\n";
            $text .= "[$time]\n";
            $text .= "[ARGS]\n";

            if (is_object($args) || is_array($args)) {

                $text .= wp_json_encode($args);

            } else {

                $text .= wp_kses_post($args);

            }

            $text .= "\n##################\n";

            $contents = $wp_filesystem->get_contents($log_file) . "\n" . $text;

            $wp_filesystem->put_contents($log_file, $contents);

        } catch (\Exception $e) {
            return false;
        }
    }


}
