<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionGroup as StoreProductOptionGroup;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

?>
<div class="cart-page-cart">

    <h1><?=t("Shopping Cart")?></h1>

    <?php if (isset($actiondata) and !empty($actiondata)) {
    ?>
        <?php if ($actiondata['action'] =='update') {
    ?>
            <p class="alert alert-success"><?= t('Your cart has been updated');
    ?></p>
        <?php 
}
    ?>

        <?php if ($actiondata['action'] == 'clear') {
    ?>
            <p class="alert alert-warning"><?= t('Your cart has been cleared');
    ?></p>
        <?php 
}
    ?>

        <?php if ($actiondata['action'] == 'remove') {
    ?>
            <p class="alert alert-warning"><?= t('Item removed');
    ?></p>
        <?php 
}
    ?>

        <?php if ($actiondata['quantity'] != $actiondata['added']) {
    ?>
            <p class="alert alert-warning"><?= t('Due to stock levels your quantity has been limited');
    ?></p>
        <?php 
}
    ?>
    <?php 
} ?>

    <input id='cartURL' type='hidden' data-cart-url='<?=View::url("/cart/")?>'>
    
    <ul class="cart-page-cart-list">
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
        
        <li class="cart-page-cart-list-item clearfix<?=$classes?>" data-instance-id="<?=$k?>" data-product-id="<?=$pID?>">
            <div class="cart-list-thumb">
                <a href="<?=URL::page(Page::getByID($product->getProductPageID()))?>">
                    <?=$product->getProductImageThumb()?>
                </a>
            </div>
            <div class="cart-list-product-name">
                <a href="<?=URL::page(Page::getByID($product->getProductPageID()))?>">
                    <?=$product->getProductName()?>
                </a>
            </div>
            
            <div class="cart-list-item-price">
                <?php 
                    $salePrice = $product->getProductSalePrice();
                if (isset($salePrice) && $salePrice != "") {
                    echo '<span class="original-price">'.StorePrice::format($product->getProductPrice()).'</span>';
                    echo '<span class="sale-price">'.StorePrice::format($salePrice).'</span>';
                } else {
                    echo StorePrice::format($product->getProductPrice());
                }
                ?>
            </div>
            <div class="cart-list-product-qty">
                <?php if ($product->allowQuantity()) {
    ?>
                    <form method="post">
                        <input type="hidden" name="instance" value="<?=$k?>" />
                        <span class="cart-item-label"><?=t("Quantity:")?></span>
                        <input type="number" name="pQty" min="1" <?=($product->allowBackOrders() || $product->isUnlimited()  ? '' :'max="' . $product->getProductQty() . '"');
    ?> value="<?=$qty?>" style="width: 50px;">
                        <button name="action" value="update" class="btn-cart-list-update" type="submit"><?=t("Update")?></button>
                    </form>
                <?php 
}
                ?>
            </div>
            <div class="cart-list-item-links">
                <form method="post">
                    <input type="hidden" name="instance" value="<?=$k?>" />
                     <button name="action" value="remove" class="btn-cart-list-remove" type="submit"><?=t("Remove")?></button>
                </form>
            </div>

            <?php if ($cartItem['productAttributes']) {
    ?>
            <div class="cart-list-item-attributes">
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

    <?php if ($cart  && !empty($cart)) {
    ?>
    <div class="cart-page-cart-total">        
        <span class="cart-grand-total-label"><?=t("Sub Total")?>:</span>
        <span class="cart-grand-total-value"><?=StorePrice::format($total)?></span>
    </div>
        
    <div class="cart-page-cart-links">
        <form method="post">
            <button name="action" value="clear" class="btn-cart-list-clear" type="submit"><?=t("Clear Cart")?></button>
        </form>
        <a class="btn-cart-page-checkout" href="<?=View::url('/checkout')?>"><?=t('Checkout')?></a>
    </div>
    <?php 
} else {
    ?>
    <p class="alert alert-info"><?= t('Your cart is empty');
    ?></p>
    <?php 
} ?>
    
</div>
