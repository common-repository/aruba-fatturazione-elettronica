<?php
if (!defined('ABSPATH')) die('No direct access allowed');
$url = admin_url('admin.php?page=aruba-fatturazione-elettronica#taxes');
?>

<div class="fe-text-center">
    <h1><?php echo esc_html__('You have installed the Aruba Electronic Invoicing plugin. You need to configure the taxes on the plugin to ensure successful electronic invoicing.','aruba-fatturazione-elettronica');?></h1>
    <div>
        <a href="<?php echo esc_url($url);?>" class="fe-btn"><?php echo esc_html__('Configure taxes on the Aruba plugin','aruba-fatturazione-elettronica');?></a>
    </div>
</div>

<style type="text/css">#rates-search,#rates-pagination,#rates-bottom-pagination,.wc_tax_rates,p.submit{display:none!important}';</style>
