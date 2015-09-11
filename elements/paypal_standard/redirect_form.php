<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
// Here we're setting up the form we're going to submit to paypal.
// This form will automatically submit itself 
?>
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="first_name" value="<?=$customer->getValue("billing_first_name")?>">
<input type="hidden" name="last_name" value="<?=$customer->getValue("billing_last_name")?>">
<input type="hidden" name="address1" value="<?=$customer->getValue("billing_address")->address1?>">
<input type="hidden" name="address2" value="<?=$customer->getValue("billing_address")->address2?>">
<input type="hidden" name="city" value="<?=$customer->getValue("billing_address")->city?>">
<input type="hidden" name="state" value="<?=$customer->getValue("billing_address")->state_province?>">
<input type="hidden" name="zip" value="<?=$customer->getValue("billing_address")->postal_code?>">
<input type="hidden" name="country" value="<?=$customer->getValue("billing_address")->country?>">
<input type="hidden" name="amount" value="<?=$total?>">
<input type="hidden" name="currency_code" value="<?=$currencyCode?>">
<input type="hidden" name="business" value="<?=$paypalEmail?>">
<input type="hidden" name="notify_url" value="<?=$notifyURL?>">
<input type="hidden" name="item_name" value="<?=t('Order from %s', $siteName)?>">
<input type="hidden" name="invoice" value="<?=$orderID?>">
<input type="hidden" name="return" value="<?=$returnURL?>">


