<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>
<div class="form-group">
    <label><?=t('Test Mode')?></label>
    <?php echo $form->select('authnetTestmode', array(false=>'No', true=>'Yes'), $authnetTestmode); ?>
</div>

<div class="form-group">
    <label><?=t("API Login ID")?></label>
    <input type="text" name="authnetLoginID" value="<?=$authnetLoginID?>" class="form-control">
</div>

<div class="form-group">
    <label><?=t("Auth.net Transaction Key")?></label>
    <input type="text" name="authnetTransactionKey" value="<?=$authnetTransactionKey?>" class="form-control">
</div>
