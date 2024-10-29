<?php
if (!defined('ABSPATH')) die('No direct access allowed');
?>
<script type="text/x-handlebars-template" id="fe-disable">
    <div class="aruba-fe-overlay-js">
        <div>
            <h1>{{aruba_fe_disable_title}}</h1>
            <div class="modal-message">
                <p>{{aruba_fe_disable_messagge}}</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="fe-btn fe-empty-btn" data-close>
                    {{aruba_fe_abort}}
                </button>
                <button type="button" class="fe-btn fe-btn-primary" data-mantain>
                    {{aruba_fe_mantain}}
                </button>
                <button type="button" class="fe-btn fe-btn-primary" data-nomantain>
                    {{aruba_fe_nomantain}}
                </button>
            </div>
        </div>
    </div>
</script>