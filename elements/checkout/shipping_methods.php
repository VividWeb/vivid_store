<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethod as StoreShippingMethod;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;

$eligibleMethods = StoreShippingMethod::getEligibleMethods();
$i=1;
foreach ($eligibleMethods as $method) {
    $sessionShippingMethodID = Session::get('smID');
    if ($sessionShippingMethodID == $method->getShippingMethodID()) {
        $checked = true;
    } else {
        if ($i==1) {
            $checked = true;
        } else {
            $checked = false;
        }
    }
    ?>
    <div class="radio">
        <label>
            <input type="radio" name="shippingMethod" value="<?=$method->getShippingMethodID()?>"<?php if ($checked) {
    echo " checked";
}
    ?>>
            <?=$method->getName()?> - <?=StorePrice::format($method->getShippingMethodTypeMethod()->getRate())?>
        </label>
    </div>
<?php $i++;
} ?>
