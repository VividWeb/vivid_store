<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as Price;
if($products){
    echo "<div class='product-list clearfix'>";

    $i=1;
    foreach($products as $product){ 
    ?>
    
        <div class="product-list-item vivid-store-col-<?=$productsPerRow?>">
            
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
                <span class="product-list-price"><?=Price::format($product->getProductPrice())?></span>
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
                <input type="hidden" name="pID" value="<?=$product->getProductID()?>">
                <input type="hidden" name="quantity" class="product-qty" value="1">
                <a href="javascript:vividStore.addToCart(<?=$product->getProductID()?>,false)" class="btn btn-primary btn-sm btn-add-to-cart"><?=t("Add to Cart")?></a>
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
