<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation as StoreProductVariation;

if ($products) {
    echo "<div class='product-list clearfix'>";

    $i=1;
    foreach ($products as $product) {
        $optionGroups = $product->getProductOptionGroups();
        $optionItems = $product->getProductOptionItems(true);

        if ($product->hasVariations()) {
            $variations = StoreProductVariation::getVariationsForProduct($product);

            $variationLookup = array();

            if (!empty($variations)) {
                foreach ($variations as $variation) {
                    // returned pre-sorted
                    $ids = $variation->getOptionItemIDs();
                    $variationLookup[implode('_', $ids)] = $variation;
                }
            }
        }

        //this is done so we can get a type of active class if there's a product list on the product page
        $class = "product-list-item vivid-store-col-".$productsPerRow;
        if (Page::getCurrentPage()->getCollectionID()==$product->getProductPageID()) {
            $class = $class." on-product-page";
        }
        ?>
    
        <div class="<?php echo $class?>">
            
            <form class="product-list-item-inner" id="form-add-to-cart-list-<?php echo $product->getProductID()?>">
                
                <?php 
                    $imgObj = $product->getProductImageObj();
        if (is_object($imgObj)) {
            $thumb = $ih->getThumbnail($imgObj, 400, 280, true);
            ?>
                        <div class="product-list-thumbnail">
                            <?php if ($showQuickViewLink) {
    ?>
                            <a class="product-quick-view" href="javascript:vividStore.productModal(<?php echo $product->getProductID()?>);">
                                <?php echo t("Quick View")?>
                            </a>
                            <?php 
}
            ?>
                            <img src="<?php echo $thumb->src?>" class="img-responsive">
                        </div>
                <?php

        }// if is_obj
                ?>
                <h2 class="product-list-name"><?php echo $product->getProductName()?></h2>
                <span class="product-list-price">
                    <?php
                        $salePrice = $product->getProductSalePrice();
        if (isset($salePrice) && $salePrice != "") {
            echo '<span class="sale-price">'.$product->getFormattedSalePrice().'</span>';
            echo '<span class="original-price">'.$product->getFormattedOriginalPrice().'</span>';
        } else {
            echo $product->getFormattedPrice();
        }
        ?>
                </span>
                <?php if ($showDescription) {
    ?>
                <div class="product-list-description"><?php echo $product->getProductDesc()?></div>
                <?php 
}
        ?>
                <?php if ($showPageLink) {
    ?>
                <a href="<?php echo URL::page(Page::getByID($product->getProductPageID()))?>" class="btn btn-default btn-sm btn-more-details"><?php echo t("More Details")?></a>
                <?php 
}
        ?>
                <?php if ($showAddToCart) {
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
                        <div class="product-option-group">
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

                <input type="hidden" name="pID" value="<?php echo $product->getProductID()?>">
                <input type="hidden" name="quantity" class="product-qty" value="1">
                <a href="javascript:vividStore.addToCart(<?php echo $product->getProductID()?>,'list')" class="btn btn-primary btn-add-to-cart <?php echo($product->isSellable() ? '' : 'hidden');
    ?> "><?php echo($btnText ? h($btnText) : t("Add to Cart"))?></a>
                <span class="out-of-stock-label <?php echo($product->isSellable() ? 'hidden' : '');
    ?>"><?php echo t("Out of Stock")?></span>

                <?php 
}
        ?>

            </form><!-- .product-list-item-inner -->
            
        </div><!-- .product-list-item -->


        <?php if ($product->hasVariations() && !empty($variationLookup)) {
    ?>
            <script>
                $(function() {
                    <?php
                    $varationData = array();
    foreach ($variationLookup as $key=>$variation) {
        $product->setVariation($variation);

        $imgObj = $variation->getVariationImageObj();

        if ($imgObj) {
            $thumb = Core::make('helper/image')->getThumbnail($imgObj, 400, 280, true);
        }

        $varationData[$key] = array(
                        'price'=>$product->getFormattedOriginalPrice(),
                        'saleprice'=>$product->getFormattedSalePrice(),
                        'available'=>($variation->isSellable()),
                        'imageThumb'=>$thumb ? $thumb->src : '',
                        'image'=>$imgObj ? $imgObj->getRelativePath() : '');
    }
    ?>


                    $('.product-list #form-add-to-cart-list-<?php echo $product->getProductID()?> select').change(function(){
                        var variationdata = <?php echo json_encode($varationData);
    ?>;
                        var ar = [];

                        $('.product-list #form-add-to-cart-list-<?php echo $product->getProductID()?> select').each(function(){
                            ar.push($(this).val());
                        })

                        ar.sort();

                        var pli = $(this).closest('.product-list-item-inner');

                        if (variationdata[ar.join('_')]['saleprice']) {
                            var pricing =  '<span class="sale-price">'+ variationdata[ar.join('_')]['saleprice']+'</span>' +
                                '<span class="original-price">' + variationdata[ar.join('_')]['price'] +'</span>';

                            pli.find('.product-list-price').html(pricing);

                        } else {
                            pli.find('.product-list-price').html(variationdata[ar.join('_')]['price']);
                        }

                        if (variationdata[ar.join('_')]['available']) {
                            pli.find('.out-of-stock-label').addClass('hidden');
                            pli.find('.btn-add-to-cart').removeClass('hidden');
                        } else {
                            pli.find('.out-of-stock-label').removeClass('hidden');
                            pli.find('.btn-add-to-cart').addClass('hidden');
                        }

                        if (variationdata[ar.join('_')]['imageThumb']) {
                            var image = pli.find('.product-list-thumbnail img');

                            if (image) {
                                image.attr('src', variationdata[ar.join('_')]['imageThumb']);
                            }
                        }

                    });
                });
            </script>
        <?php 
}
        ?>
        
        <?php 
            if ($i%$productsPerRow==0) {
                echo "</div>";
                echo "<div class='product-list clearfix'>";
                //this helps to keep rows straight (products from floating under smaller height products).
            }
        
        $i++;
    }// foreach    
    echo "</div><!-- .product-list -->";
    
    if ($showPagination) {
        if ($paginator->getTotalPages() > 1) {
            echo $pagination;
        }
    }
} //if products
else {
    ?>
    <div class="alert alert-info"><?php echo t("No Products Available")?></div>
<?php 
} ?>
