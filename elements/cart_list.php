<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
?>
<ul class="checkout-cart-list">
    <?php
    if($cart){
        $i=1;
        foreach ($cart as $k=>$cartItem){
            $pID = $cartItem['product']['pID'];
            $qty = $cartItem['product']['qty'];
            $product = VividProduct::getByID($pID);
            if($i%2==0){$classes=" striped"; }else{ $classes=""; }
            if(is_object($product)){
                ?>

                <li class="checkout-cart-item clearfix<?=$classes?>" data-instance-id="<?=$k?>" data-product-id="<?=$pID?>">
                    <div class="cart-list-thumb">
                        <a href="<?=URL::page(Page::getByID($product->getProductPageID()))?>">
                        <?=$product->getProductImageThumb()?>
                        </a>
                    </div>
                    <div class="checkout-cart-product-name">
                        <a href="<?=URL::page(Page::getByID($product->getProductPageID()))?>">
                        <?=$product->getProductName()?>
                        </a>
                    </div>

                    <div class="checkout-cart-item-price">
                        <?=Price::format($product->getProductPrice())?>
                    </div>
                    <div class="checkout-cart-product-qty">
                        <span class="checkout-cart-item-label"><?=t("Quantity:")?></span>
                        <?=$qty?>
                    </div>

                    <?php if($cartItem['productAttributes']){?>
                        <div class="checkout-cart-item-attributes">
                            <?php foreach($cartItem['productAttributes'] as $groupID => $valID){
                                $groupID = str_replace("pog","",$groupID);
                                ?>
                                <div class="cart-list-item-attribute">
                                    <span class="checkout-cart-item-attribute-label"><?=VividProduct::getProductOptionGroupNameByID($groupID)?>:</span>
                                    <span class="checkout-cart-item-attribute-value"><?=VividProduct::getProductOptionValueByID($valID)?></span>
                                </div>
                            <?php }  ?>
                        </div>
                    <?php } ?>


                </li>

                <?php
            }//if is_object
            $i++;
        }//foreach
    }//if cart
    ?>
</ul>
