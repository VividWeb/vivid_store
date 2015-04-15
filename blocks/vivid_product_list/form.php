<?php defined('C5_EXECUTE') or die("Access Denied.");?>
<fieldset>
    <legend><?=t('Product Arrangement')?></legend>
    <?php
        $productgroups = array("0"=>t("None"));
        foreach($grouplist as $productgroup){
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        } 
    ?>
    <div class="row">
        <div class="col-xs-6">
            <div class="form-group">
                <?php echo $form->label('sortOrder',t('Sort Order'));?>
                <?php echo $form->select('sortOrder',array('alpha'=>t("Alphabetical"),'date'=>t('Recently Added')),$sortOrder);?>
            </div>
        </div>
        <?php if($productgroups){?>
        <div class="col-xs-6">
            <div class="form-group">
                <?php echo $form->label('gID',t('Filter by Group'));?>
                <?php echo $form->select('gID',$productgroups,$gID);?>
            </div>
        </div>
        <?php } ?>
    </div>
    <legend><?=t('Pagination')?></legend>
    <div class="row">
        <div class="col-xs-6">
            <div class="form-group">
                <?php echo $form->label('productsPerRow',t('Products per Row')); ?>
                <?php echo $form->select('productsPerRow', array(1=>1,2=>2,3=>3,4=>4,5=>5),$productsPerRow?$productsPerRow:3, array('style'=>'width:70px')); ?>
            </div>
        </div>
        <div class="col-xs-6">
            <div class="form-group">
                <?php echo $form->label('maxProducts',t('Max Number of Products')); ?>
                <?php echo $form->text('maxProducts', isset($maxProducts)?$maxProducts:"9", array('style'=>'width:50px')); ?>
            </div>
        </div>
    </div>
    <div class="checkbox">
        <label>
            <?php echo $form->checkbox('showPagination',1,$showPagination);?>
            <?=t('Show Pagination')?>
        </label>
    </div>  
    
    <legend><?=t('Display Options')?></legend>
    
    <label><?=t('Show Featured')?></label>
    
    <div class="radio">
        <label>
            <?php echo $form->radio('showFeatured','all',$showFeatured=='all' || !isset($showFeatured)?true:false);?>
            <?=t('Show Both Featured &amp; Non-featured')?>
        </label>
    </div>
    <div class="radio">
        <label>
            <?php echo $form->radio('showFeatured','featured',$showFeatured=='featured'?true:false);?>
            <?=t('Featured Only')?>
        </label>
    </div>
    <div class="radio">
        <label>
            <?php echo $form->radio('showFeatured','nonfeatured',$showFeatured=='nonfeatured'?true:false);?>
            <?=t('Non-Featured Only')?>
        </label>
    </div>
    
    <label><?=t('Show:')?></label>
    <div class="checkbox">
        <label>
            <?php if($showQuickViewLink!=0){
                $showQuickViewLink=1;
            }?>
            <?php echo $form->checkbox('showQuickViewLink',1,$showQuickViewLink);?>
            <?=t('Quickview Link (Modal Window)')?>
        </label>
    </div>
    <div class="checkbox">
        <label>
            <?php echo $form->checkbox('showPageLink',1,$showPageLink);?>
            <?=t('Page Link')?>
        </label>
    </div>
    <div class="checkbox">
        <label>
            <?php echo $form->checkbox('showAddToCart',1,$showAddToCart);?>
            <?=t('Add to Cart Button')?>
        </label>
    </div>
    
</fieldset>