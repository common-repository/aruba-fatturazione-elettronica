<?php

if (!defined('ABSPATH')) die('No direct access allowed');
/**
 * @var $subject
 */
?>
<html>
<head>
    <title><?php echo esc_html($subject); ?></title>
</head>
<body>
<div style="width: 600px; margin: 40px auto">
    <p><?php echo esc_html__('Dear Customer,', 'aruba-fatturazione-elettronica'); ?></p>
    <p><?php echo esc_html__('a new version of the Aruba Electronic Invoicing plugin is available.', 'aruba-fatturazione-elettronica'); ?></p>
    <p><?php echo esc_html__('Update it now on your WooCommerce instance to enable proper integration between the online shop and the Electronic Invoicing service.', 'aruba-fatturazione-elettronica'); ?></p>
    <ul>
        <li><?php echo esc_html__('Download the updated version of the plugin;', 'aruba-fatturazione-elettronica'); ?></li>
        <li><?php echo esc_html__('install it on your WooCommerce instance', 'aruba-fatturazione-elettronica'); ?></li>
        <li><?php echo esc_html__('confirms the replacement of the plugin.', 'aruba-fatturazione-elettronica'); ?></li>
    </ul>
    <p style="text-align: center">
        <a href="https://guide.hosting.aruba.it/hosting/hosting-woocommerce-gestito/plugin-fatturazione-elettronica.aspx#aggiornare"><?php echo esc_html__('UPDATE NOW', 'aruba-fatturazione-elettronica'); ?></a>
    </p>
    <p><?php echo esc_html__('Best Regards', 'aruba-fatturazione-elettronica'); ?></p>
</div>
</body>
</html>