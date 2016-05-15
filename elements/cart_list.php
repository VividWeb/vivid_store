<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionGroup as StoreProductOptionGroup;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

?>
<ul class="checkout-cart-list">
    <?php
    if ($cart) {
        $i=1;
        foreach ($cart as $k=>$cartItem) {
            $pID = $cartItem['product']['pID'];
            $qty = $cartItem['product']['qty'];
            $product = StoreProduct::getByID($pID);

            if ($cartItem['product']['variation']) {
                $product->setVariation($cartItem['product']['variation']);
            }

            if ($i%2==0) {
                $classes=" striped";
            } else {
                $classes="";
            }
            if (is_object($product)) {
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
                        <?=StorePrice::format($product->getActivePrice())?>
                    </div>

                    <?php if ($product->allowQuantity()) {
    ?>
                    <div class="checkout-cart-product-qty">
                        <span class="checkout-cart-item-label"><?=t("Quantity:")?></span>
                        <?=$qty?>
                    </div>
                    <?php 
}
                ?>

                    <?php if ($cartItem['productAttributes']) {
    ?>
                        <div class="checkout-cart-item-attributes">
                            <?php foreach ($cartItem['productAttributes'] as $groupID => $valID) {
    $groupID = str_replace("pog", "", $groupID);
    $optiongroup = StoreProductOptionGroup::getByID($groupID);
    $optionvalue = StoreProductOptionItem::getByID($valID);

    ?>
                                <div class="cart-list-item-attribute">
                                    <span class="cart-list-item-attribute-label"><?= ($optiongroup ? $optiongroup->getName() : '')?>:</span>
                                    <span class="cart-list-item-attribute-value"><?= ($optionvalue ? $optionvalue->getName(): '')?></span>
                                </div>
                            <?php 
}
    ?>
                        </div>
                    <?php 
}
                ?>


                </li>

                <?php

            }//if is_object
            $i++;
        }//foreach
    }//if cart
    ?>
</ul>