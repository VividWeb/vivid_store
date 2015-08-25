<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
$subject = t("New Order Notification");


/**
 * HTML BODY START
 */
ob_start();

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'> 
<html>

<body bgcolor='#ddd'>
    
    <div style="font-family: Arial; background: #fff; box-shadow: 0 3px 3px #ccc; width: 600px; max-width: 100%;color: #444;">
        <div style="background: #444; padding: 20px; color: #fff; font-size: 24px;"><?=t('An order has been placed!')?></div>
        <div style="padding: 20px;">
            <p><strong><?=t("Order")?>#:</strong> <?=$order->getOrderID()?></p>
            <p><?=t('An order has been placed on your website.')?></p>
            <table border="0" width="100%">
                <tr>
                    <td width="50%">
                        <strong><?=t('Billing Information')?></strong>
                        <p>
                            <?=$order->getAttribute("billing_first_name"). " " . $order->getAttribute("billing_last_name")?><br>
                            <?=$order->getAttribute("shipping_address")->address1?><br>
                            <?php if($order->getAttribute("billing_address")->address2){
                                echo $order->getAttribute("billing_address")->address2 . "<br>";
                            } ?>
                            <?=$order->getAttribute("billing_address")->city?>, <?=$order->getAttribute("billing_address")->state_province?> <?=$order->getAttribute("billing_address")->postal_code?><br>
                            <?=$order->getAttribute("billing_phone")?>
                        </p>
                    </td>
                    <td>
                        <strong><?=t('Shipping Information')?></strong>
                        <p>
                            <?=$order->getAttribute("shipping_first_name"). " " . $order->getAttribute("shipping_last_name")?><br>
                            <?=$order->getAttribute("shipping_address")->address1?><br>
                            <?php if($order->getAttribute("shipping_address")->address2){
                                echo $order->getAttribute("shipping_address")->address2 . "<br>";
                            } ?>
                            <?=$order->getAttribute("shipping_address")->city?>, <?=$order->getAttribute("shipping_address")->state_province?> <?=$order->getAttribute("shipping_address")->postal_code?><br>
                            
                        </p>
                    </td>
                </tr>
            </table>
            
            <p style="font-size: 18px;"><?=t('Order Details')?></p>
            <table border="0" cellpawidth="0" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th style="border-bottom: 1px solid #aaa;"><?=t('Product Name')?></th>
                        <th style="border-bottom: 1px solid #aaa;"><?=t('Options')?></th>
                        <th style="border-bottom: 1px solid #aaa;"><?=t('Qty')?></th>
                        <th style="border-bottom: 1px solid #aaa;"><?=t('Price')?></th>
                        <th style="border-bottom: 1px solid #aaa;"><?=t('Subtotal')?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $items = $order->getOrderItems();
                        if($items){
                            foreach($items as $item){
                      ?>
                        <tr>
                            <td><?=$item->getProductName()?></td>
                            <td>
                                <?php
                                    $options = $item->getProductOptions();
                                    if($options){
                                        echo "<ul class='list-unstyled'>";
                                        foreach($options as $option){
                                            echo "<li>";
                                            echo "<strong>".$option['oioKey'].": </strong>";
                                            echo $option['oioValue'];
                                            echo "</li>";
                                        }
                                        echo "</ul>";
                                    }
                                ?>
                            </td>
                            <td><?=$item->getQty()?></td>
                            <td><?=Price::format($item->getPricePaid())?></td>
                            <td><?=Price::format($item->getSubTotal())?></td>
                        </tr>
                      <?php
                            }
                        }
                    ?>
                </tbody>
            </table>
            
            <p>
                <?php
                $taxtotal = $order->getTaxTotal();

                if($taxtotal > 0 && $taxbased == 'subtotal') { ?>
                    <strong><?=($taxlabel ? $taxlabel : t("Tax"))?>:</strong>  <?=Price::format($order->getTaxTotal())?><br>
                <?php } ?>

                <strong><?=t("Shipping")?>:</strong>  <?=Price::format($order->getShippingTotal())?><br>

                <?php if($taxtotal > 0 && $taxbased == 'grandtotal') { ?>
                <strong><?=($taxlabel ? $taxlabel : t("Tax"))?>:</strong>  <?=Price::format($order->getTaxTotal())?><br>
                <?php } ?>

                <strong class="text-large"><?=t("Total")?>:</strong>  <?=Price::format($order->getTotal())?>
            </p>
            
        </div>
    </div>
    
</body>
</html>

<?php 
$bodyHTML = ob_get_clean();
/**
 * HTML BODY END
 *
 * ======================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>

<?=t("Order #:")?> <?=$order->getOrderID()?>

<?=t("A new order has been placed on your website")?>

<?php 

$body = ob_get_clean(); ?>