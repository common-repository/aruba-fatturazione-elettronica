<?php

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\Label\ArubaFeStatusesLabel;
use ArubaFe\Admin\RestApi\Helper\ArubaFeHelper;

if (!defined('ABSPATH')) {
    die('No direct access allowed');
}

/**
 * @var $ignore int|null
 * @var $orderId string
 * @var $invoices array|null
 * @var $drafts array|null
 * @var $hasError boolean
 * @var $regenerateLink string|null
 * @var $hasErrorDraft string|null
 * @var $regenerateLinkDraft string|null
 * @var $errorList
 */

?>
<hr>
<div class="mt-1 aruba-fe-order-links">

    <h3><?php echo esc_html__('Electronic invoice status', 'aruba-fatturazione-elettronica'); ?></h3>

    <?php if ($ignore) : ?>
        <div class="mt-1">
			<span class="aruba-fe-label label-warning">
				<?php echo esc_html__('Not applicable', 'aruba-fatturazione-elettronica'); ?>
			</span>
        </div>
    <?php else : ?>

        <?php if (empty($invoices) && empty($drafts) && $errorList) :

            $errorList = @unserialize($errorList);

            ?>

            <div class="mt-1">
                <span class="aruba-fe-label label-error">
                    <?php echo esc_html__('Incomplete order', 'aruba-fatturazione-elettronica'); ?>
                </span>
            </div>

            <div class="mt-1 errors-list">
                <p>
                    <?php echo esc_html__('Order cannot be generated due to the following errors:', 'aruba-fatturazione-elettronica'); ?>
                </p>
                <ul>
                    <?php foreach ($errorList as $error): ?>
                        <li><?php echo (esc_html($error)); ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="mt-1">
                    <a href="#" id="aruba-fe-check-order" class="fe-btn fe-btn-primary"><?php echo esc_html__( 'Correct Order', 'aruba-fatturazione-elettronica' ); ?></a>
                </div>

            </div>

        <?php endif; ?>

        <?php if (empty($invoices)) : ?>
            <div class="mt-1">
                <b><?php echo esc_html__('Electronic invoice not generated', 'aruba-fatturazione-elettronica'); ?></b>
            </div>
            <?php if ($hasError && !$errorList) : ?>
                <div class="mt-1">
                    <a href="<?php echo esc_url($regenerateLink); ?>"
                       class="fe-btn fe-btn-primary"><?php echo esc_html__('Reinvia fattura', 'aruba-fatturazione-elettronica'); ?></a>
                </div>
            <?php endif; ?>
        <?php
        else :

            $ndc = array();

            ?>

            <?php
            foreach ($invoices as $invoice) :

                $class = (ArubaFeHelper::getClassByState($invoice->status));


                if (strtolower($invoice->type) == 'ftx') :
                    ?>

                    <div class="mt-1">
				<span class="aruba-fe-label <?php echo esc_attr($class); ?>">
					<?php echo esc_html( ArubaFeStatusesLabel::getInvoiceLabel( $invoice->status ) ); ?>
				</span>
                    </div>
                <?php
                elseif (strtolower($invoice->type) == 'ndc') :
                    $ndc[] = '<div class="mt-1">
                <span class="aruba-fe-label ' . esc_attr($class) . '">
                    ' . esc_html( ArubaFeStatusesLabel::getInvoiceLabel( $invoice->status ) ) . '
                </span>
            </div>';

                    ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (!empty($ndc)) : ?>

            <h3><?php echo esc_html__('Credit note status', 'aruba-fatturazione-elettronica'); ?></h3>

            <?php echo wp_kses_post(implode("\n", $ndc)); ?>

        <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>

</div>
