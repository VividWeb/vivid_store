<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>
<div class="rule-type-form" data-complete-function="onNewDateRestrictionRuleType">
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?php echo $form->label('dateFrom', t("Date From")); ?>
                <?php echo $form->text('dateFrom'); ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?php echo $form->label('dateTo', t("Date Till")); ?>
                <?php echo $form->text('dateTo'); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function onNewDateRestrictionRuleType(){
        var dateFrom = $('.vivid-store-dialog #dateFrom').val();
        var dateTo = $('.vivid-store-dialog #dateTo').val();
        var params = {
            dateFrom: dateFrom,
            dateTo: dateTo
        }
        var listItemTemplate = _.template($('#date-restriction-list-item-template').html());
        return listItemTemplate(params);
    }
</script>

<script type="text/x-template" id="date-restriction-list-item-template">
    <?=t('If ordered between %s and %s', '<strong><%=dateFrom%></strong>', '<strong><%=dateTo%></strong>')?>
</script>
