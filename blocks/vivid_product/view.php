<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

if (is_object($product)) {
    ?>

<form class="product-detail-block" id="form-add-to-cart-<?php echo $product->getProductID()?>">
    
    <div class="col-container">
        <?php if ($showImage) {
    ?>
        <div class="vivid-store-col-2 product-image">
            <?php
                $imgObj = $product->getProductImageObj();
    if (is_object($imgObj)) {
        $thumb = Core::make('helper/image')->getThumbnail($imgObj, 600, 800, true);
        ?>
            <div class="product-primary-image">
                <a href="<?php echo $imgObj->getRelativePath()?>" class="product-thumb">
                    <img src="<?php echo $thumb->src?>">
                </a>
            </div>
            <?php 
    }
    ?>

            <?php
            $images = $product->getProductImagesObjects();
    if (count($images)>0) {
        echo '<div class="product-additional-images">';
        foreach ($images as $secondaryimage) {
            if (is_object($secondaryimage)) {
                $thumb = Core::make('helper/image')->getThumbnail($secondaryimage, 300, 300, true);
                ?>
                      
                      <a class="product-thumb" href="<?php echo $secondaryimage->getRelativePath()?>"><img src="<?php echo $thumb->src?>"></a>
                      
                    <?php 
            }
        }
        echo '</div>';
    }
    ?>
        </div>
        <div class="vivid-store-col-2">
        <?php 
} else {
    ?>
        <div class="vivid-store-col-1">
        <?php 
}
    ?>
                   
            <?php if ($showGroups) {
    ?>
                <ul>
                <?php
                $productgroups = $product->getProductGroups();
    foreach ($productgroups as $pg) {
        ?>
                    <li class="product-group"><?php echo  $pg->gName;
        ?> </li>
                <?php 
    }
    ?>
                </ul>
            <?php 
}
    ?>
            
            <?php if ($showIsFeatured) {
    if ($product->isFeatured()) {
        ?> 
                <span class="product-featured"><?php echo t("Featured Item")?></span>
               <?php 
    }
}
    ?>
            
            <?php if ($showProductName) {
    ?>
            <h1 class="product-name"><?php echo $product->getProductName()?></h1>
            <?php 
}
    ?>
            
            <?php if ($showProductPrice) {
    ?>
            <span class="product-price">
                <?php
                    $salePrice = $product->getProductSalePrice();
    if (isset($salePrice) && $salePrice != "") {
        echo '<span class="sale-price">'.t("On Sale: ").$product->getFormattedSalePrice().'</span>';
        echo '<span class="original-price">'.$product->getFormattedOriginalPrice().'</span>';
    } else {
        echo $product->getFormattedPrice();
    }
    ?>
            </span>
            <?php 
}
    ?>
            
            <?php if ($showProductDescription) {
    ?>
            <div class="product-description">
                <?php echo $product->getProductDesc()?>
            </div>
            <?php 
}
    ?>
            
            <?php if ($showDimensions) {
    ?>
            <div class="product-dimensions">
                <strong><?php echo t("Dimensions")?>:</strong>
                <?php echo $product->getDimensions()?>
                <?php echo Config::get('vividstore.sizeUnit');
    ?>
            </div>
            <?php 
}
    ?>
            
            <?php if ($showWeight) {
    ?>
            <div class="product-weight">
                <strong><?php echo t("Weight")?>:</strong>
                <?php echo $product->getProductWeight()?>
                <?php echo Config::get('vividstore.weightUnit');
    ?>
            </div>
            <?php 
}
    ?>
            
            <div class="clearfix col-container product-options" id="product-options-<?php echo $bID;
    ?>">
                <?php if ($product->allowQuantity()) {
    ?>
                <div class="product-modal-option-group vivid-store-col-2">
                    <label class="option-group-label"><?php echo t('Quantity')?></label>
                    <input type="number" name="quantity" class="product-qty" value="1" min="1" step="1" <?php echo($product->allowBackOrders() ? '' :'max="' . $product->getProductQty() . '"');
    ?>>
                </div>
                    <?php 
} else {
    ?>
                    <input type="hidden" name="quantity" class="product-qty" value="1">
                <?php 
}
    ?>
                <?php

                foreach ($optionGroups as $optionGroup) {
                    $groupoptions = array();
                    foreach ($optionItems as $option) {
                        if ($option->getProductOptionGroupID() == $optionGroup->getID()) {
                            $groupoptions[] = $option;
                        }
                    }
                    ?>
                    <?php if (!empty($groupoptions)) {
    ?>
                        <div class="product-option-group vivid-store-col-2">
                            <label class="option-group-label"><?php echo $optionGroup->getName() ?></label>
                            <select name="pog<?php echo $optionGroup->getID() ?>">
                                <?php
                                foreach ($groupoptions as $option) {
                                    ?>
                                    <option value="<?php echo $option->getID() ?>"><?php echo $option->getName() ?></option>
                                    <?php
                                    // below is an example of a radio button, comment out the <select> and <option> tags to use instead
                                    //echo '<input type="radio" name="pog'.$optionGroup->getID().'" value="'. $option->getID(). '" />' . $option->getName() . '<br />'; ?>
                                <?php 
                                }
    ?>
                            </select>
                        </div>
                    <?php 
}
                }
    ?>
            </div>

            <?php if ($showCartButton) {
    ?>
            <div class="product-button-shell">
                <input type="hidden" name="pID" value="<?php echo $product->getProductID()?>">
                    <a href="javascript:vividStore.addToCart(<?php echo $product->getProductID()?>,false)" class="btn btn-primary btn-add-to-cart <?php echo($product->isSellable() ? '' : 'hidden');
    ?> "><?php echo($btnText ? h($btnText) : t("Add to Cart"))?></a>
                    <span class="out-of-stock-label <?php echo($product->isSellable() ? 'hidden' : '');
    ?>"><?php echo t("Out of Stock")?></span>
            </div>
            <?php 
}
    ?>
            
        </div>
        <?php if ($showProductDetails) {
    ?>
        <div class="vivid-store-col-1 product-detailed-description">
            <h2><?php echo t("Product Details")?></h2>
            <?php echo $product->getProductDetail()?>
        </div>
        <?php 
}
    ?>
    </div>
    
</form>

    <script type="text/javascript">
    $(function() {
    $('.product-thumb').magnificPopup({
        type:'image',
        gallery:{enabled:true}
    });

    <?php if ($product->hasVariations() && !empty($variationLookup)) {
    ?>

        <?php
        $varationData = array();
    foreach ($variationLookup as $key=>$variation) {
        $product->setVariation($variation);

        $imgObj = $variation->getVariationImageObj();

        if ($imgObj) {
            $thumb = Core::make('helper/image')->getThumbnail($imgObj, 600, 800, true);
        }

        $varationData[$key] = array(
            'price'=>$product->getFormattedOriginalPrice(),
            'saleprice'=>$product->getFormattedSalePrice(),
            'available'=>($variation->isSellable()),
            'imageThumb'=>$thumb ? $thumb->src : '',
            'image'=>$imgObj ? $imgObj->getRelativePath() : ''

            );
    }
    ?>

        $('#product-options-<?php echo $bID;
    ?> select, #product-options-<?php echo $bID;
    ?> input').change(function(){
            var variationdata = <?php echo json_encode($varationData);
    ?>;
            var ar = [];

            $('#product-options-<?php echo $bID;
    ?> select, #product-options-<?php echo $bID;
    ?> input:checked').each(function(){
                ar.push($(this).val());
            })

            ar.sort();
            var pdb = $(this).closest('.product-detail-block');

            if (variationdata[ar.join('_')]['saleprice']) {
                var pricing =  '<span class="sale-price"><?php echo t("On Sale: ");
    ?>'+ variationdata[ar.join('_')]['saleprice']+'</span>' +
                    '<span class="original-price">' + variationdata[ar.join('_')]['price'] +'</span>';

                pdb.find('.product-price').html(pricing);
            } else {
                pdb.find('.product-price').html(variationdata[ar.join('_')]['price']);
            }

            if (variationdata[ar.join('_')]['available']) {
                pdb.find('.out-of-stock-label').addClass('hidden');
                pdb.find('.btn-add-to-cart').removeClass('hidden');
            } else {
                pdb.find('.out-of-stock-label').removeClass('hidden');
                pdb.find('.btn-add-to-cart').addClass('hidden');
            }

            if (variationdata[ar.join('_')]['imageThumb']) {
                var image = pdb.find('.product-primary-image img');

                if (image) {
                    image.attr('src', variationdata[ar.join('_')]['imageThumb']);
                    var link = image.parent();

                    if (link) {
                        link.attr('href', variationdata[ar.join('_')]['image'])
                    }
                }
            }

        });
    <?php 
}
    ?>

});
</script>
   
<?php 
} else {
    ?>
    <div class="alert alert-info"><?php echo t("We can't seem to find this product at the moment")?></div>
<?php 
} ?>
