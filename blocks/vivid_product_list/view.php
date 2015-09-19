<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
if($products){
    echo "<div class='product-list clearfix'>";

    $i=1;
    foreach($products as $product){
        //this is done so we can get a type of active class if there's a product list on the product page
        $class = "product-list-item vivid-store-col-".$productsPerRow;
        if(Page::getCurrentPage()->getCollectionID()==$product->getProductPageID()){
            $class = $class." on-product-page";
        }
    ?>
    
        <div class="<?=$class?>">
            
            <form class="product-list-item-inner" id="form-add-to-cart-<?=$product->getProductID()?>">
                
                <?php 
                    $imgObj = $product->getProductImageObj();
                    if(is_object($imgObj)){
                        $thumb = $ih->getThumbnail($imgObj,400,280,true);?>
                        <div class="product-list-thumbnail">
                            <?php if($showQuickViewLink){ ?>
                            <a class="product-quick-view" href="javascript:vividStore.productModal(<?=$product->getProductID()?>);">
                                <?=t("Quick View")?>
                            </a>
                            <?php } ?>
                            <img src="<?=$thumb->src?>" class="img-responsive">
                        </div>
                <?php
                    }// if is_obj
                ?>
                <h2 class="product-list-name"><?=$product->getProductName()?></h2>
                <span class="product-list-price">
                    <?php
                        $salePrice = $product->getProductSalePrice();
                        if(isset($salePrice) && $salePrice != ""){
                            echo '<span class="sale-price">'.$product->getFormattedSalePrice().'</span>';
                            echo '<span class="original-price">'.$product->getFormattedPrice().'</span>';
                        } else {
                            echo $product->getFormattedPrice();
                        }
                    ?>
                </span>
                <?php if($showDescription){ ?>
                <div class="product-list-description"><?=$product->getProductDesc()?></div>
                <?php } ?>
                <?php if($showPageLink){?>
                <a href="<?=URL::page(Page::getByID($product->getProductPageID()))?>" class="btn btn-default btn-sm btn-more-details"><?=t("More Details")?></a>
                <?php } ?>
                <?php if($showAddToCart){
                    /*
                     * If we have an add to cart button, 
                     * we at least need to have the Product ID
                     * and a default quanity (1)
                     */
                ?>

                <?php
                $optionGroups = $product->getProductOptionGroups();
                $optionItems = $product->getProductOptionItems();
                foreach($optionGroups as $optionGroup){
                    ?>
                    <div class="product-option-group">
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

                <input type="hidden" name="pID" value="<?=$product->getProductID()?>">
                <input type="hidden" name="quantity" class="product-qty" value="1">
                <?php if($product->isSellable()){?>
                <a href="javascript:vividStore.addToCart(<?=$product->getProductID()?>,false)" class="btn btn-primary btn-sm btn-add-to-cart"><?=t("Add to Cart")?></a>
                <?php } else { ?>
                    <span class="out-of-stock-label"><?=t("Out of Stock")?></span>
                <?php } ?>
                <?php } ?>
            
            </form><!-- .product-list-item-inner -->
            
        </div><!-- .product-list-item -->
        
        <?php 
            if($i%$productsPerRow==0){
                echo "</div>";
                echo "<div class='product-list clearfix'>";
                //this helps to keep rows straight (products from floating under smaller height products).
            }
        
        $i++;
    
    }// foreach    
    echo "</div><!-- .product-list -->";
    
    if($showPagination){
        if ($paginator->getTotalPages() > 1) {
            echo $pagination;
        }
    }
    
} //if products
else { ?>
    <div class="alert alert-info"><?=t("No Products Available")?></div>
<?php } ?>
