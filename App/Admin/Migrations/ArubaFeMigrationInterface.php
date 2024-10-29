<?php

namespace ArubaFe\Admin\Migrations;

if (!defined('ABSPATH')) die('No direct access allowed');

interface ArubaFeMigrationInterface
{
    public function migrate();
}