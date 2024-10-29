<?php
/**
 * EnqueueScript Class
 **/

namespace ArubaFe\Publics;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Initialization\Info;

class EnqueueScript
{

    protected $version;
    protected $plugin_url;
    protected $plugin_path;

    public function __construct($version, $plugin_url, $plugin_path)
    {
        $this->version = $version;
        $this->plugin_url = $plugin_url;
        $this->plugin_path = $plugin_path;

        $this->init();
    }

    public function init()
    {

        add_action('wp_enqueue_scripts', array($this, 'addFrontendScripts'), 100);

    }

    public function addFrontendScripts()
    {

    }

}
