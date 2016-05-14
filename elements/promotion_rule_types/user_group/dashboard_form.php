<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>
<div class="rule-type-form" data-complete-function="onNewUserGroupRuleType">
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
            <?php echo $form->label('userGroup', t("If user belongs in the following user group")); ?>
            <?php echo $form->text('userGroup'); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function onNewUserGroupRuleType(){
        var userGroupID = $('.vivid-store-dialog #userGroup').val();

        var params = {
            userGroupID: userGroupID
        }
        var listItemTemplate = _.template($('#user-group-list-item-template').html());
        return listItemTemplate(params);
    }
</script>

<script type="text/x-template" id="user-group-list-item-template">
    <?=t("If the User belongs in the User Group: %s", "<strong><%=userGroupID%></strong>")?>
</script>
