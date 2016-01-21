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
            <div class="product-primary-image">
                <a href="<?=$imgObj->getRelativePath()?>" class="product-thumb">
                    <img src="<?=$thumb->src?>">
                </a>
            </div>
            <?php } ?>

            <?php
            $images = $p->getProductImagesObjects();
            if(count($images)>0){
                echo '<div class="product-additional-images">';
                foreach($images as $secondaryimage) {
                     if(is_object($secondaryimage)) {
                         $thumb = Core::make('helper/image')->getThumbnail($secondaryimage, 300, 300, true);
                      ?>
                      
                      <a class="product-thumb" href="<?=$secondaryimage->getRelativePath()?>"><img src="<?=$thumb->src?>"></a>
                      
                    <?php }
                }
                echo '</div>';
            }
            ?>
        </div>
        <div class="vivid-store-col-2">
        <?php } else { ?>
        <div class="vivid-store-col-1">
        <?php } ?>
                   
            <?php if($showGroups){?>
                <ul>
                <?php
                $productgroups = $p->getProductGroups();
                foreach($productgroups as $pg) { ?>
                    <li class="product-group"><?= $pg->gName;?> </li>
                <?php } ?>
                </ul>
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
            <span class="product-price">
                <?php
                    $salePrice = $p->getProductSalePrice();
                    if(isset($salePrice) && $salePrice != ""){
                        echo '<span class="sale-price">'.t("On Sale: ").$p->getFormattedSalePrice().'</span>';
                        echo '<span class="original-price">'.$p->getFormattedPrice().'</span>';
                    } else {
                        echo $p->getFormattedPrice();
                    }
                ?>
            </span>
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
            
            <div class="clearfix col-container product-options" id="product-options-<?php echo $bID; ?>">
                <?php if ($p->allowQuantity()) { ?>
                <div class="product-modal-option-group vivid-store-col-2">
                    <label class="option-group-label"><?=t('Quantity')?></label>
                    <input type="number" name="quantity" class="product-qty" value="1" min="1" step="1" <?=($p->allowBackOrders() ? '' :'max="' . $p->getProductQty() . '"' );?>>
                </div>
                    <?php } else { ?>
                    <input type="hidden" name="quantity" class="product-qty" value="1">
                <?php } ?>
                <?php
                $optionGroups = $p->getProductOptionGroups();
                $optionItems = $p->getProductOptionItems();
                foreach($optionGroups as $optionGroup){
                ?>
                <div class="product-option-group vivid-store-col-2">
                    <label class="option-group-label"><?=$optionGroup->getName()?></label>
                    <select name="pog<?=$optionGroup->getID()?>">
                        <?php
                        foreach($optionItems as $option){
                            if($option->getProductOptionGroupID()==$optionGroup->getID()){?>
                                <option value="<?=$option->getID()?>"><?=$option->getName()?></option>
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
                    <a href="javascript:vividStore.addToCart(<?=$p->getProductID()?>,false)" class="btn btn-primary add-to-cart-button <?php echo ($p->isSellable() ? '' : 'hidden');?> "><?= ($btnText ? h($btnText) : t("Add to Cart"))?></a>
                    <span class="out-of-stock-label <?php echo ($p->isSellable() ? 'hidden' : '');?>"><?=t("Out of Stock")?></span>
            </div>
            <?php } ?>
            
        </div>
        <?php if($showProductDetails){?>
        <div class="vivid-store-col-1 product-detailed-description">
            <h2><?=t("Product Details")?></h2>
            <?=$p->getProductDetail()?>
        </div>
        <?php } ?>
    </div>
    
</form>

    <script type="text/javascript">
    $(function() {
    $('.product-thumb').magnificPopup({
        type:'image',
        gallery:{enabled:true}
    });

    <?php if ($p->hasVariations()) {?>

    <?php
    $varationData = array();
    foreach($variationLookup as $key=>$variation) {
        $varationData[$key] = array('price'=>$variation->getFormattedVariationPrice(), 'available'=>($variation->isSellable()));
    } ?>

    $('#product-options-<?php echo $bID; ?> select').change(function(){
        var variationdata = <?php echo json_encode($varationData); ?>;
        var ar = [];

        $('#product-options-<?php echo $bID; ?> select').each(function(){
            ar.push($(this).val());
        })

        ar.sort();
        var pdb = $(this).closest('.product-detail-block');

        pdb.find('.product-price').html(variationdata[ar.join('_')]['price']);

        if (variationdata[ar.join('_')]['available']) {
            pdb.find('.out-of-stock-label').addClass('hidden');
            pdb.find('.add-to-cart-button').removeClass('hidden');
        } else {
            pdb.find('.out-of-stock-label').removeClass('hidden');
            pdb.find('.add-to-cart-button').addClass('hidden');
        }

    });
    <?php } ?>

});
</script>
   
<?php } else { ?>
    <div class="alert alert-info"><?=t("We can't seem to find this product at the moment")?></div>
<?php } ?>
