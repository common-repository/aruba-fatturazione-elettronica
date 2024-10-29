<?php

namespace ArubaFe\Admin\RestApi\Api;

if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\RestApi\Interfaces\ArubaFeApiResponseInterface;
use ArubaFe\Admin\Traits\ArubaFeLogTrait;

class ArubaFeBaseApi
{

    use ArubaFeLogTrait;

    protected $endpoint = ARUBA_FE_EP;
    protected $connetctionTimeout = 120;
    protected $authType;

    protected function buildHttpAuthHeader($autorization)
    {
        return [
            'Authorization' => $autorization,
            'aru-sub' => CustomOptions::get_option(ArubaFeConstants::ARUBA_FE_TOKEN_USERNAME)
        ];

    }

    protected function doGetCurl(string $endpoint, $options = [], $autorization = null, $customBuild = null, $log = false): ArubaFeApiResponseInterface
    {

        if ($customBuild) {

            $query = $options;

        } else {

            $query = $options ? '?' . http_build_query($options) : '';

        }

        $httpRequest = new \WP_Http();

        $args = [
            'user-agent' => 'aruba-fe',
            'httpversion' => '1.1',
            'timeout' => $this->connetctionTimeout
        ];

        if ($autorization) {
            $args['headers'] = $this->buildHttpAuthHeader($autorization);
        }

        $url = esc_url_raw("{$this->endpoint}/{$endpoint}{$query}", ['https']);

        $this->writeCallLog($args, $url);

        try {

            $response = $httpRequest->request($url, $args);

            $http_status = wp_remote_retrieve_response_code($response);

            if (is_wp_error($response)) {

                $body = wp_json_encode(['ErrorList' => [['ErrorMessage' => $response->get_error_message()]]]);

            } else {

                $body = wp_remote_retrieve_body($response);

            }

            $this->writeLog($http_status, $body, $url, $args);

            return (new ArubaFeApiResponse($body, $http_status));

        } catch (\Exception $e) {

            $this->writeLog(500, $e->getMessage(), $url, $args);

            return (new ArubaFeApiResponse(wp_json_encode(['error' => $e->getMessage()]), 500));
        }
    }

    /**
     * @param string $endpoint
     * @param array $options
     * @param $autorization
     * @return ArubaFeApiResponseInterface
     */

    public function doPostCurl(string $endpoint, array $options, $autorization = null, $altMode = "POST"): ArubaFeApiResponseInterface
    {

        $data_string = wp_json_encode($options);

        $httpRequest = new \WP_Http();

        $args = [
            'user-agent' => 'aruba-fe',
            'httpversion' => '1.1',
            'data_format' => 'body',
            'method' => $altMode,
            'body' => $data_string,
            'timeout' => $this->connetctionTimeout,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($data_string)
            ]
        ];

        if ($autorization) {
            foreach ($this->buildHttpAuthHeader($autorization) as $key => $value)
                $args['headers'][$key] = $value;
        }

        $url = esc_url_raw("$this->endpoint/$endpoint", ['https']);

        $this->writeCallLog($args, $url);

        try {

            $response = $httpRequest->request($url, $args);
            $http_status = wp_remote_retrieve_response_code($response);

            if (is_wp_error($response)) {

                $body = wp_json_encode(['ErrorList' => [['ErrorMessage' => $response->get_error_message()]]]);

            } else {

                $body = wp_remote_retrieve_body($response);

            }

            $this->writeLog($http_status, $body, $url, $args);

            return (new ArubaFeApiResponse($body, $http_status));

        } catch (\Exception $e) {

            $this->writeLog(500, $e->getMessage(), $url, $args);

            return (new ArubaFeApiResponse(wp_json_encode(['error' => $e->getMessage()]), 500));
        }

    }

    public function doPatchCurl(string $endpoint, array $options, $autorization = null): ArubaFeApiResponseInterface
    {
        return $this->doPostCurl($endpoint, $options, $autorization, 'PUT');
    }

    /**
     * @param string $endpoint
     * @param array $options
     * @param $autorization
     * @return ArubaFeApiResponseInterface
     */
    public function doDeleteCurl(string $endpoint, $autorization = null): ArubaFeApiResponseInterface
    {

        $httpRequest = new \WP_Http();

        $args = [
            'user-agent' => 'aruba-fe',
            'httpversion' => '1.1',
            'method' => 'DELETE',
            'timeout' => $this->connetctionTimeout,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        if ($autorization) {
            foreach ($this->buildHttpAuthHeader($autorization) as $key => $value)
                $args['headers'][$key] = $value;
        }

        $url = esc_url_raw("$this->endpoint/$endpoint", ['https']);

        $this->writeCallLog($args, $url);

        try {
            $response = $httpRequest->request($url, $args);

            $http_status = wp_remote_retrieve_response_code($response);

            if (is_wp_error($response)) {

                $body = wp_json_encode(['ErrorList' => [['ErrorMessage' => $response->get_error_message()]]]);

            } else {

                $body = wp_remote_retrieve_body($response);

            }

            $this->writeLog($http_status, $body, $url, $args);

            return (new ArubaFeApiResponse($body, $http_status));

        } catch (\Exception $e) {

            $this->writeLog(500, $e->getMessage(), $url, $args);

            return (new ArubaFeApiResponse(wp_json_encode(['error' => $e->getMessage()]), 500));
        }

    }

}
