<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<form class="product-modal clearfix" id="form-add-to-cart-modal-<?=$product->getProductID()?>">
    <div class="product-modal-thumb">
        <?php
            $imgObj = $product->getProductImageObj();
            $ih = Core::make("helper/image");
            $thumb = $ih->getThumbnail($imgObj,400,999,false);
        ?>
        <img src="<?=$thumb->src?>">
    </div>
    <div class="product-modal-info-shell">
        <a href="javascript:vividStore.exitModal()" class="product-modal-exit">x</a>
        <span class="product-modal-title"><?=$product->getProductName()?></span>
        <span class="product-modal-price"><?=$product->getFormattedPrice()?></span>
        <div class="product-modal-details">
            <?=$product->getProductDesc()?>
        </div>
        <div class="product-modal-options clearfix">
            <div class="product-modal-option-group vivid-store-col-2">
                <span class="option-group-label"><?=t('Quantity')?></span>
                <input type="number" name="quantity" class="product-qty" value="1" max="<?=$product->getProductQty()?>">
            </div>
            <?php
            $optionGroups = $product->getProductOptionGroups();
            $optionItems = $product->getProductOptionItems();
            foreach($optionGroups as $optionGroup){
            ?>
            <div class="product-modal-option-group vivid-store-col-2">
                <span class="option-group-label"><?=$optionGroup['pogName']?></span>
                <select name="pog<?=$optionGroup['pogID']?>">
                    <?php
                    foreach($optionItems as $option){
                        if($option['pogID']==$optionGroup['pogID']){?>
                            <option value="<?=$option['poiID']?>"><?=$option['poiName']?></option>   
                        <?php }
                    }//foreach    
                    ?>
                </select>
            </div>
            <?php } ?>
        </div>
        <input type="hidden" name="pID" value="<?=$product->getProductID()?>">
        <div class="product-modal-buttons">
            <?php if($product->isSellable()){?>
            <a href="javascript:vividStore.addToCart(<?=$product->getProductID()?>,'modal')"><?=t("Add to Cart")?></a>
            <?php } else {?>
                <span class="out-of-stock-label"><?=t("Out of Stock")?></span>
            <?php } ?>
        </div>
    </div>
</form>
