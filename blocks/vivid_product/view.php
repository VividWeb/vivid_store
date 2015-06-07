<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

if(is_object($p)){?>

<form class="product-detail-block" id="form-add-to-cart-<?=$p->getProductID()?>">
    
    <div class="col-container">
        <?php if($showImage){ ?>
        <div class="vivid-store-col-2 product-image">
            <?php
                $imgObj = $p->getProductImageObj();
                if(is_object($imgObj)){
                    $thumb = Core::make('helper/image')->getThumbnail($imgObj,600,800,true);
            ?>
            <img src="<?=$thumb->src?>">
            <?php } ?>
        </div>
        <div class="vivid-store-col-2">
        <?php } else { ?>
        <div class="vivid-store-col-1">
        <?php } ?>
                   
            <?php if($showGroups){?>
            <span class="product-group"><?=$p->getGroupName()?></span>
            <?php } ?>
            
            <?php if($showIsFeatured){
               if($p->isFeatured()){?> 
                <span class="product-featured"><?=t("Featured Item")?></span>
               <?php }
            }?>
            
            <?php if($showProductName){?>
            <h1 class="product-name"><?=$p->getProductName()?></h1>
            <?php } ?>
            
            <?php if($showProductPrice){?>
            <span class="product-price"><?=$p->getFormattedPrice()?></span>
            <?php } ?>
            
            <?php if($showProductDescription){?>
            <div class="product-description">
                <?=$p->getProductDesc()?>
            </div>
            <?php } ?>
            
            <?php if($showDimensions){?>
            <div class="product-dimensions">
                <strong><?=t("Dimensions")?>:</strong>
                <?=$p->getDimensions()?>
                <?php echo Config::get('vividstore.sizeUnit'); ?>
            </div>
            <?php } ?>
            
            <?php if($showWeight){?>
            <div class="product-weight">
                <strong><?=t("Weight")?>:</strong>
                <?=$p->getProductWeight()?>
                <?php echo Config::get('vividstore.weightUnit'); ?>
            </div>
            <?php } ?>
            
            <div class="clearfix col-container product-options">
                <div class="product-modal-option-group vivid-store-col-2">
                    <label class="option-group-label"><?=t('Quantity')?></label>
                    <input type="number" name="quantity" class="product-qty" value="1" max="<?=$p->getProductQty()?>">
                </div>
                <?php
                $optionGroups = $p->getProductOptionGroups();
                $optionItems = $p->getProductOptionItems();
                foreach($optionGroups as $optionGroup){
                ?>
                <div class="product-option-group vivid-store-col-2">
                    <label class="option-group-label"><?=$optionGroup['pogName']?></label>
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
            
            <?php if($showCartButton) {?>
            <div class="product-button-shell">
                <input type="hidden" name="pID" value="<?=$p->getProductID()?>">
                <a href="javascript:vividStore.addToCart(<?=$p->getProductID()?>,false)" class="btn btn-primary"><?=t("Add to Cart")?></a>
            </div>
            <?php } ?>
            
        </div>
        
        <div class="vivid-store-col-1 product-detailed-description">
            <h2><?=t("Product Details")?></h2>
            <?=$p->getProductDetail()?>
        </div>
    </div>
    
</form>
   
<?php } else { ?>
    <div class="alert alert-info"><?=t("We can't seem to find this product at the moment")?></div>
<?php } ?>