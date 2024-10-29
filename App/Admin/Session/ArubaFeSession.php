<?php

namespace ArubaFe\Admin\Session;
if (!defined('ABSPATH')) die('No direct access allowed');

class ArubaFeSession
{

    static array $sessions = [];
    protected int $current_user;
    protected string $transient_key;
    protected int $transientLifetime = 120;
    protected array $transientData = array();

    /**
     * @throws \Exception
     */

    public function __construct($current_user = 0)
    {

        $this->init($current_user);

    }

    public static function getInstance()
    {

        $current_user = get_current_user_id();

        if (!$current_user)
            return null;


        if (!isset(self::$sessions[$current_user]))
            self::$sessions[$current_user] = new ArubaFeSession($current_user);

        return self::$sessions[$current_user];

    }

    /**
     * Init hooks and session data. Extended by child classes.
     *
     * @throws \Exception
     * @since 3.3.0
     */
    public function init($current_user)
    {

        $this->current_user = $current_user || get_current_user_id();

        if (!$this->current_user) {
            throw new \Exception(esc_html__('ArubaFeSession could be used only by logged in users','aruba-fatturazione-elettronica'), 403);
        }

        $this->transient_key = 'aruba_fe_user_' . $this->current_user;

        $data = get_transient($this->transient_key);

        $this->transientData = is_array($data) ? $data : array();

    }

    /**
     * Cleanup session data. Extended by child classes.
     */
    public function cleanup_sessions()
    {

        delete_transient($this->transient_key);

    }

    /**
     * Magic get method.
     *
     * @param mixed $key Key to get.
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Magic set method.
     *
     * @param mixed $key Key to set.
     * @param mixed $value Value to set.
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Magic isset method.
     *
     * @param mixed $key Key to check.
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->transientData[sanitize_key($key)]);
    }

    /**
     * Magic unset method.
     *
     * @param mixed $key Key to unset.
     */
    public function __unset($key)
    {

        if (isset($this->transientData[sanitize_key($key)])) {
            unset($this->transientData[sanitize_key($key)]);
            $this->updateTransient();
        }
    }

    /**
     * Get a session variable.
     *
     * @param string $key Key to get.
     * @param mixed $default used if the session variable isn't set.
     * @return array|string value of session variable
     */
    public function get($key, $default = null)
    {

        $key = sanitize_key($key);

        if (isset($this->transientData[sanitize_key($key)])) {

            $data = wp_kses_post($this->transientData[sanitize_key($key)]);

            if (is_serialized($this->transientData[sanitize_key($key)])) {

                return unserialize($this->transientData[sanitize_key($key)]);

            } else {

                return $data;

            }

        } else {

            return $default;

        }


    }

    /**
     * Set a session variable.
     *
     * @param string $key Key to set.
     * @param mixed $value Value to set.
     */
    public function set($key, $value)
    {

        if ($value !== $this->get($key)) {
            $this->transientData[sanitize_key($key)] = maybe_serialize($value);
            $this->updateTransient();
        }
    }

    /**
     * Update the transient data
     */
    public function updateTransient(): void
    {
        set_transient($this->transient_key, $this->transientData, $this->transientLifetime);

    }

}