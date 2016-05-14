<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
use \Concrete\Package\VividStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;

?>
<link href="/concrete/css/app.css" rel="stylesheet" type="text/css" media="all">
<div class="ccm-ui">
    <div class="container">
<h3><?=t("Customer Overview")?></h3>
    <hr>
    
    <div class="row">
        <div class="col-xs-12">
            <?php $orderemail = $order->getAttribute("email");

            if ($orderemail) {
                ?>
            <h4><?=t("Email")?></h4>
            <p><a href="mailto:<?=$order->getAttribute("email");
                ?>"><?=$order->getAttribute("email");
                ?></a></p>
            <?php 
            } ?>

            <?php
            $ui = UserInfo::getByID($order->getCustomerID());
            if ($ui) {
                ?>
            <h4><?=t("User")?></h4>
            <p><a href="<?= View::url('/dashboard/users/search/view/' . $ui->getUserID());
                ?>"><?= $ui->getUserName();
                ?></a></p>
            <?php 
            } ?>
        </div>

        <div class="col-xs-6">
            <h4><?=t("Billing Information")?></h4>
            <p>
                <?=$order->getAttribute("billing_first_name"). " " . $order->getAttribute("billing_last_name")?><br>
                <?=$order->getAttributeValueObject(StoreOrderKey::getByHandle('billing_address'))->getValue('displaySanitized', 'display'); ?>
                <br /> <br /><?php echo t('Phone'); ?>: <?=$order->getAttribute("billing_phone")?>
            </p>
        </div>
        <div class="col-xs-6">
            <?php if ($order->getAttribute("shipping_address")->address1) {
    ?>
            <h4><?=t("Shipping Information")?></h4>
            <p>
                <?=$order->getAttribute("shipping_first_name"). " " . $order->getAttribute("shipping_last_name")?><br>
                <?=$order->getAttributeValueObject(StoreOrderKey::getByHandle('shipping_address'))->getValue('displaySanitized', 'display');
    ?>
            </p>
            <?php 
} ?>
        </div>
    </div>
    
    <h3><?=t("Order Info")?></h3>
    <hr>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><strong><?=t("Product Name")?></strong></th>
                <th><?=t("Product Options")?></th>
                <th><?=t("Price")?></th>
                <th><?=t("Quantity")?></th>
                <th><?=t("Subtotal")?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $items = $order->getOrderItems();

                if ($items) {
                    foreach ($items as $item) {
                        ?>
                <tr>
                    <td><?=$item->getProductName()?></td>
                    <td>
                        <?php
                            $options = $item->getProductOptions();
                        if ($options) {
                            echo "<ul class='list-unstyled'>";
                            foreach ($options as $option) {
                                echo "<li>";
                                echo "<strong>".$option['oioKey'].": </strong>";
                                echo $option['oioValue'];
                                echo "</li>";
                            }
                            echo "</ul>";
                        }
                        ?>
                    </td>
                    <td><?=Price::format($item->getPricePaid())?></td>
                    <td><?=$item->getQty()?></td>
                    <td><?=Price::format($item->getSubTotal())?></td>
                </tr>
              <?php

                    }
                }
            ?>
        </tbody>
    </table>
    
    <p>
        <strong><?=t("Subtotal")?>: </strong><?=Price::format($order->getSubTotal())?><br>
        <?php foreach ($order->getTaxes() as $tax) {
    ?>
            <strong><?=$tax['label']?>:</strong> <?=$tax['amount']?><br>
        <?php 
} ?>
        <strong><?=t("Shipping")?>: </strong><?=Price::format($order->getShippingTotal())?><br>
        <strong><?=t("Grand Total")?>: </strong><?=Price::format($order->getTotal())?>
    </p>
    <p>
        <strong><?=t("Payment Method")?>: </strong><?=$order->getPaymentMethodName()?><br>
        <strong><?=t("Shipping Method")?>: </strong><?=$order->getShippingMethodName()?>
    </p>

    <?php $applieddiscounts = $order->getAppliedDiscounts();

    if (!empty($applieddiscounts)) {
        ?>
        <h3><?=t("Discounts Applied")?></h3>
        <hr />
        <table class="table table-striped">
            <thead>
            <tr>
                <th><strong><?=t("Name")?></strong></th>
                <th><?=t("Displayed")?></th>
                <th><?=t("Deducted From")?></th>
                <th><?=t("Amount")?></th>
                <th><?=t("Triggered")?></th>
            </tr>

            </thead>
            <tbody>
            <?php foreach ($applieddiscounts as $discount) {
    ?>
                <tr>
                    <td><?= h($discount['odName']);
    ?></td>
                    <td><?= h($discount['odDisplay']);
    ?></td>
                    <td><?= h($discount['odDeductFrom']);
    ?></td>
                    <td><?= ($discount['odValue'] > 0 ? $discount['odValue'] : $discount['odPercentage'] . '%');
    ?></td>
                    <td><?= ($discount['odCode'] ? t('by code'). ' ' .$discount['odCode']: t('Automatically'));
    ?></td>
                </tr>
            <?php 
}
        ?>

            </tbody>
        </table>
    
    <p>
        <strong><?=t("Items Subtotal")?>:</strong>  <?= Price::format($order->getSubTotal())?><br>
        <?php $shipping = $order->getShippingTotal();
        if ($shipping > 0) {
            ?>
        <strong><?=t("Shipping")?>:</strong>  <?= Price::format($shipping)?><br>
        <?php 
        }
        ?>
        <?php foreach ($order->getTaxes() as $tax) {
    ?>
            <strong><?=$tax['label']?>:</strong> <?=$tax['amount']?>
        <?php 
}
        ?>
        <strong class="text-large"><?=t("Total")?>:</strong>  <?= Price::format($order->getTotal())?><br>
        <strong><?=t("Payment Method")?>:</strong> <?=$order->getPaymentMethodName()?>
    </p>




    <?php 
    } ?>
    </div>
    </div>