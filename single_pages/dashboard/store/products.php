<?php
defined('C5_EXECUTE') or die("Access Denied.");

$listViews = array('view','updated','removed','success');
$addViews = array('add','edit','save');
$groupViews = array('groups','groupadded','addgroup');
$attributeViews = array('attributes','attributeadded','attributeremoved');
$ps = Core::make('helper/form/page_selector');

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;

?>

<?php if (in_array($controller->getTask(), $addViews)) { //if adding or editing a product
    if (!is_object($p)) {
        $p = new StoreProduct(); //does nothing other than shutup errors.}
    }

    $pID = $p->getProductID()
 ?>

    <?php if ($pID > 0) {
    ?>
    <div class="ccm-dashboard-header-buttons">
        <form method="post" id="delete" action="<?php echo View::url('/dashboard/store/products/delete/', $pID)?>" >
            <button class="btn btn-danger"><?php echo t("Delete Product")?></button>
        </form>

        <script type="text/javascript">
        $(function(){
            $('#delete').submit(function() {
                return confirm('<?php echo  t("Are you sure you want to delete this product?");
    ?>');
            });
        });
        </script>
    </div>
    <?php 
}
    ?>

    <form method="post" action="<?php echo $view->action('save')?>">
        <input type="hidden" name="pID" value="<?php echo $p->getProductID()?>"/>

        <div class="row">
            <div class="col-sm-4">
                <div class="vivid-store-side-panel">
                    <ul>
                        <li><a href="#product-overview" data-pane-toggle class="active"><?php echo t('Overview')?></a></li>
                        <li><a href="#product-categories" data-pane-toggle><?php echo t('Categories')?></a></li>
                        <li><a href="#product-shipping" data-pane-toggle><?php echo t('Shipping')?></a></li>
                        <li><a href="#product-images" data-pane-toggle><?php echo t('Images')?></a></li>
                        <li><a href="#product-options" data-pane-toggle><?php echo t('Options')?></a></li>
                        <li><a href="#product-attributes" data-pane-toggle><?php echo t('Attributes')?></a></li>
                        <li><a href="#product-digital" data-pane-toggle><?php echo t("Memberships and Downloads")?></a></li>
                        <li><a href="#product-page" data-pane-toggle><?php echo t('Detail Page')?></a></li>
                    </ul>
                </div>
            </div>

            <div class="col-sm-7 store-pane active" id="product-overview">

                <div class="row">
                    <div class="col-xs-8">
                        <div class="form-group">
                            <?php echo $form->label("pName", t("Product Name"));
    ?>
                            <?php echo $form->text("pName", $p->getProductName());
    ?>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="form-group">
                            <?php echo $form->label("pSKU", t("Code / SKU"));
    ?>
                            <?php echo $form->text("pSKU", $p->getProductSKU());
    ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pActive", t("Active"));
    ?>
                            <?php echo $form->select("pActive", array('1'=>t('Active'), '0'=>t('Inactive')), $p->isActive());
    ?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pFeatured", t("Featured Product"));
    ?>
                            <?php echo $form->select("pFeatured", array('0'=>t('No'), '1'=>t('Yes')), $p->isFeatured());
    ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pPrice", t("Price"));
    ?>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?php echo  Config::get('vividstore.symbol');
    ?>
                                </div>
                                <?php $price = $p->getProductPrice();
    ?>
                                <?php echo $form->text("pPrice", $price?$price:'0');
    ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pSalePrice", t("Sale Price"));
    ?>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?php echo  Config::get('vividstore.symbol');
    ?>
                                </div>
                                <?php $salePrice = $p->getProductSalePrice();
    ?>
                                <?php echo $form->text("pSalePrice", $salePrice, array('placeholder'=>'No Sale Price Set'));
    ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pTaxable", t("Taxable"));
    ?>
                            <?php echo $form->select("pTaxable", array('0'=>t('No'), '1'=>t('Yes')), $p->isTaxable());
    ?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pTaxClass", t("Tax Class"));
    ?>
                            <?php echo $form->select("pTaxClass", $taxClasses, $p->getTaxClassID());
    ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pQty", t("Stock Level"));
    ?>
                            <?php $qty = $p->getProductQty();
    ?>
                            <div class="input-group">
                                <?php echo $form->text("pQty", $qty!==''?$qty:'999', array(($p->isUnlimited() ? 'disabled' : '')=>($p->isUnlimited() ? 'disabled' : '')));
    ?>
                                <div class="input-group-addon">
                                    <?php echo $form->checkbox('pQtyUnlim', '1', $p->isUnlimited())?>
                                    <?php echo $form->label('pQtyUnlim', t('Unlimited'))?>
                                </div>

                                <script>
                                    $(document).ready(function(){
                                        $('#pQtyUnlim').change(function(){
                                            $('#pQty').prop('disabled',this.checked);
                                            $('#backorders').toggle();
                                        });

                                        $('#pVariations').change(function(){
                                            if ($(this).prop('checked')) {
                                                $('#variations,#variationnotice').removeClass('hidden');
                                            } else {
                                                $('#variations,#variationnotice').addClass('hidden');
                                            }
                                        });

                                        $('input[name="pvQtyUnlim[]"]').change(function(){
                                            $(this).closest('.input-group').find('.ccm-input-text').prop('readonly',this.checked);
                                        });

                                    });
                                </script>
                            </div>

                        </div>
                        <div class="form-group" id="backorders" <?php echo($p->isUnlimited() ? 'style="display: none"' : '');
    ?>>
                            <?php echo $form->checkbox('pBackOrder', '1', $p->pBackOrder)?>
                            <?php echo $form->label('pBackOrder', t('Allow Back Orders'))?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pNoQty", t("Offer quantity selection"));
    ?>
                            <?php echo $form->select("pNoQty", array('0'=>t('Yes'), '1'=>t('No, only allow one of this product in a cart')), !$p->allowQuantity());
    ?>
                        </div>
                    </div>

                </div>
                <div class="form-group">
                    <?php echo $form->label("pDesc", t("Short Description"));
    ?><br>
                    <textarea class="redactor-content" name="pDesc" id="pDesc" style="display:none;"><?php echo $p->getProductDesc()?></textarea>
                    <script type="text/javascript">
                        $(function(){
                            $('#pDesc').redactor({
                                minHeight: '100',
                                'concrete5': {
                                    filemanager: <?php echo $fp->canAccessFileManager()?>,
                                    sitemap: <?php echo $tp->canAccessSitemap()?>,
                                    lightbox: true
                                }
                            });
                        });
                    </script>
                </div>

                <div class="form-group">
                    <?php echo $form->label("pDesc", t("Product Details (Long Description)"));
    ?><br>
                    <textarea class="redactor-content" name="pDetail" id="pDetail" style="display:none;"><?php echo $p->getProductDetail()?></textarea>
                    <script type="text/javascript">
                        $(function(){
                            $('#pDetail').redactor({
                                minHeight: '200',
                                'concrete5': {
                                    filemanager: <?php echo $fp->canAccessFileManager()?>,
                                    sitemap: <?php echo $tp->canAccessSitemap()?>,
                                    lightbox: true
                                }
                            });
                        });
                    </script>
                </div>


            </div><!-- #product-overview -->

            <div class="col-sm-7 store-pane" id="product-categories">
                <h4><?php echo t('Categorized under pages')?></h4>

                <div class="form-group" id="page_pickers">
                    <div class="page_picker">
                        <?php echo $ps->selectPage('cID[]', ($locationPages[0] && $locationPages[0]->getCollectionID()) ?  $locationPages[0]->getCollectionID() : false);
    ?>
                    </div>

                    <?php for ($i = 1; $i < 7; $i++) {
    ?>
                        <div class="page_picker <?php echo($locationPages[$i - 1] && $locationPages[$i - 1]->getCollectionID() ? '' : 'picker_hidden');
    ?>">
                            <?php echo $ps->selectPage('cID[]',  ($locationPages[$i] && $locationPages[$i]->getCollectionID()) ?  $locationPages[$i]->getCollectionID() : false);
    ?>
                        </div>

                    <?php 
}
    ?>
                </div>

                <h4><?php echo t('In product groups')?></h4>
                <div class="ccm-search-field-content ccm-search-field-content-select2">
                    <select multiple="multiple" name="pProductGroups[]" class="existing-select2 select2-select" style="width: 100%">
                        <?php
                            if (!empty($productgroups)) {
                                if (!is_array($pgroups)) {
                                    $pgroups = array();
                                }
                                foreach ($productgroups as $pgkey=>$pglabel) {
                                    ?>
                            <option value="<?php echo $pgkey;
                                    ?>" <?php echo(in_array($pgkey, $pgroups) ? 'selected="selected"' : '');
                                    ?>>  <?php echo $pglabel;
                                    ?></option>
                        <?php 
                                }
                            }
    ?>
                    </select>
                </div>


                <script>
                    $(document).ready(function(){
                        $('.existing-select2').select2();

                        Concrete.event.bind('ConcreteSitemap', function(e, instance) {
                            var instance = instance;
                            Concrete.event.bind('SitemapSelectPage', function(e, data) {
                                if (data.instance == instance) {
                                    Concrete.event.unbind(e);

                                    if ($('.page_picker :input[value="0"]').length == $('.picker_hidden :input[value="0"]').length) {
                                        $('#page_pickers .picker_hidden').first().removeClass('picker_hidden');
                                    }


                                }
                            });
                        });

                    });
                </script>

                <style>
                    .picker_hidden {
                        display: none;
                    }
                </style>
            </div><!-- #product-categories -->


            <div class="col-sm-7 store-pane" id="product-shipping">

                <div class="form-group">
                    <?php echo $form->label("pShippable", t("Product is Shippable"));
    ?>
                    <?php echo $form->select("pShippable", array('1'=>t('Yes'), '0'=>t('No')), ($p->isShippable() ? '1' : '0'));
    ?>
                </div>
                
                <div class="alert alert-info">
                    <?php echo t("Keep in mind that the following information is not for the product itself, but for the shipping dimensions and weight")?>
                </div>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pWeight", t("Weight"));
    ?>
                            <div class="input-group" >
                                <?php $weight = $p->getProductWeight();
    ?>
                                <?php echo $form->text('pWeight', $weight?$weight:'0')?>
                                <div class="input-group-addon"><?php echo Config::get('vividstore.weightUnit')?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <div class="form-group">
                                <?php echo $form->label("pLength", t("Length"));
    ?>
                                <div class="input-group" >
                                    <?php $length = $p->getDimensions('l');
    ?>
                                    <?php echo $form->text('pLength', $length?$length:'0')?>
                                    <div class="input-group-addon"><?php echo Config::get('vividstore.sizeUnit')?></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form->label("pWidth", t("Width"));
    ?>
                                <div class="input-group" >
                                    <?php $width = $p->getDimensions('w');
    ?>
                                    <?php echo $form->text('pWidth', $width?$width:'0')?>
                                    <div class="input-group-addon"><?php echo Config::get('vividstore.sizeUnit')?></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form->label("pHeight", t("Height"));
    ?>
                                <div class="input-group">
                                    <?php $height = $p->getDimensions('h');
    ?>
                                    <?php echo $form->text('pHeight', $height?$height:'0')?>
                                    <div class="input-group-addon"><?php echo Config::get('vividstore.sizeUnit')?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div><!-- #product-shipping -->

            <div class="col-sm-7 store-pane" id="product-images">

                <div class="form-group">
                    <?php echo $form->label('pfID', t("Primary Product Image"));
    ?>
                    <?php $pfID = $p->getProductImageID();
    ?>
                    <?php echo $al->image('ccm-image', 'pfID', t('Choose Image'), $pfID?File::getByID($pfID):null);
    ?>
                </div>


                <h4><?php echo t('Additional Images')?></h4>

                <div id="additional-images-container"></div>

                <div class="clearfix">
                    <span class="btn btn-default" id="btn-add-image"><?php echo t('Add Image')?></span>
                </div>

                <!-- THE TEMPLATE WE'LL USE FOR EACH IMAGE -->
                <script type="text/template" id="image-template">
                    <div class="additional-image clearfix" data-order="<%=sort%>">
                        <div class="move-shell pull-left text-center">
                            <i class="fa fa-arrows"></i>
                        </div>
                        <a href="javascript:deleteImage(<%=sort%>)" class="trash-shell bg-danger text-danger pull-right">
                            <i class="fa fa-trash"></i>
                        </a>
                        <a href="javascript:chooseImage(<%=sort%>);" class="select-image pull" id="select-image-<%=sort%>">
                            <% if (thumb.length > 0) { %>
                            <img src="<%= thumb %>" />
                            <% } else { %>
                            <i class="fa fa-picture-o"></i> <?php echo t('Choose Image');
    ?>
                            <% } %>
                        </a>

                        <input type="hidden" name="pifID[]" class="image-fID" value="<%=pifID%>" />
                        <input type="hidden" name="piSort[]" value="<%=sort%>" class="image-sort">
                    </div><!-- .additional-image -->
                </script>
                <script type="text/javascript">
                    var chooseImage = function(i){
                        var imgShell = $('#select-image-'+i);
                        ConcreteFileManager.launchDialog(function (data) {
                            ConcreteFileManager.getFileDetails(data.fID, function(r) {
                                jQuery.fn.dialog.hideLoader();
                                var file = r.files[0];
                                imgShell.html(file.resultsThumbnailImg);
                                imgShell.next('.image-fID').val(file.fID);
                            });
                        });
                    };
                    function deleteImage(id){
                        $(".additional-image[data-order='"+id+"']").remove();
                    }
                    $(function(){
                        function indexItems(){
                            $('#additional-images-container .additional-image').each(function(i) {
                                $(this).find('.image-sort').val(i);
                                $(this).attr("data-order",i);
                            });
                        };

                        //Make items sortable. If we re-sort them, re-index them.
                        $("#additional-images-container").sortable({
                            handle: ".move-shell",
                            update: function(){
                                indexItems();
                            }
                        });

                        //Define container and items
                        var itemsContainer = $('#additional-images-container');
                        var itemTemplate = _.template($('#image-template').html());

                        //load up images
                        <?php
                        if ($images) {
                            $count = 0;
                            foreach ($images as $image) {
                                ?>
                        itemsContainer.append(itemTemplate({

                            pifID: '<?php echo $image->getFileID();
                                ?>',
                            <?php if ($image->getFileID() > 0) {
    ?>
                            thumb: '<?php echo File::getByID($image->getFileID())->getThumbnailURL('file_manager_listing');
    ?>',
                            <?php 
} else {
    ?>
                            thumb: '',
                            <?php 
}
                                ?>
                            sort: '<?php echo $count++ ?>'
                        }));
                        <?php

                            }
                        }
    ?>

                        //add item
                        $('#btn-add-image').click(function(){

                            //Use the template to create a new item.
                            var temp = $(".additional-image").length;
                            temp = (temp);
                            itemsContainer.append(itemTemplate({
                                //vars to pass to the template
                                pifID: '',
                                thumb: '',
                                sort: temp
                            }));

                            //Init Index
                            indexItems();
                        });
                    });

                </script>

            </div><!-- #product-images -->


            <div class="col-sm-7 store-pane" id="product-options">

                <h4><?php echo t('Options')?></h4>
                <div id="product-options-container"></div>

                <div class="clearfix">
                    <span class="btn btn-primary" id="btn-add-option-group"><?php echo t('Add Option Group')?></span>
                </div>
                <!-- THE TEMPLATE WE'LL USE FOR EACH OPTION GROUP -->
                <script type="text/template" id="option-group-template">
                    <div class="panel panel-default option-group clearfix" data-order="<%=sort%>">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3 label-shell">
                                    <label for="pogName<%=sort%>" class="text-right"><i class="fa fa-arrows drag-handle pull-left"></i> <span class="hidden-xs"><?php echo t('Group Name:')?></span></label>
                                </div>
                                <div class="col-xs-6">
                                    <input type="text" class="form-control" name="pogName[]" value="<%=pogName%>">
                                </div>
                                <div class="col-xs-3 text-right">
                                     <a href="javascript:deleteOptionGroup(<%=sort%>)" class="btn btn-delete-item btn-danger"><i data-toggle="tooltip" data-placement="top" title="<?php echo t('Delete the Option Group')?>" class="fa fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div data-group="<%=sort%>" class="option-group-item-container"></div>

                            <a href="javascript:addOptionItem(<%=sort%>)" data-group="<%=sort%>" class="btn btn-default"><?php echo t('Add Option')?></a>

                                </div>
                            <input type="hidden" name="pogID[]" value="<%=pogID%>">
                            <input type="hidden" name="pogSort[]" value="<%=sort%>" class="option-group-sort">
                        </div>

                    </div><!-- .option-group -->
                </script>
                <script type="text/javascript">
                    function deleteOptionGroup(id){
                        $(".option-group[data-order='"+id+"']").remove();
                        $('#variationshider').addClass('hidden');
                        $('#changenotice').removeClass('hidden');
                    }
                    $(function(){
                        function indexOptionGroups(){
                            $('#product-options-container .option-group').each(function(i) {
                                $(this).find('.option-group-sort').val(i);
                                $(this).attr("data-order",i);
                                $(this).find('.optGroupID').attr("name","optGroup"+i+"[]");
                            });
                        };

                        //Make items sortable. If we re-sort them, re-index them.
                        $("#product-options-container").sortable({
                            handle: ".panel-heading",
                            update: function(){
                                indexOptionGroups();
                            }
                        });

                        //Define container and items
                        var optionsContainer = $('#product-options-container');
                        var optionsTemplate = _.template($('#option-group-template').html());

                        //load up existing option groups
                        <?php
                        if ($groups) {
                            foreach ($groups as $group) {
                                ?>
                        optionsContainer.append(optionsTemplate({
                            pogName: '<?php echo $group->getName() ?>',
                            pogID: '<?php echo $group->getID()?>',
                            sort: '<?php echo $group->getSort() ?>'
                        }));
                        <?php

                            }
                        }
    ?>

                        //add item
                        $('#btn-add-option-group').click(function(){

                            //Use the template to create a new item.
                            var temp = $(".option-group").length;
                            temp = (temp);
                            optionsContainer.append(optionsTemplate({
                                //vars to pass to the template
                                pogName: '',
                                pogID: '',
                                sort: temp
                            }));

                            //Init Index
                            indexOptionGroups();

                            $('#variationshider').addClass('hidden');
                            $('#changenotice').removeClass('hidden');
                        });
                    });

                </script>
                <!-- TEMPLATE FOR EACH OPTION ITEM ---->
                <script type="text/template" id="option-item-template">
                    <div class="option-item clearfix form-horizontal" data-order="<%=sort%>" data-option-group="<%=optGroup%>">
                        <div class="form-group">
                            <div class="col-sm-3 text-right">
                                <label class="grabme"><i class="fa fa-arrows drag-handle pull-left"></i><?php echo t('Option')?>:</label>
                            </div>
                            <div class="col-sm-7">
                                <div class="input-group">
                                <input type="text" name="poiName[]" class="form-control" value="<%=poiName%>">
                                    <div class="input-group-addon">
                                        <label><input type="checkbox" name="poiHide[]" value="1" <%=poiHidden%> /> <?php echo t('Hide');
    ?></label>
                                    </div>
                                </div>
                                <input type="hidden" name="poiID[]" class="form-control" value="<%=poiID%>">
                            </div>
                            <div class="col-sm-2">
                                <a href="javascript:deleteOptionItem(<%=optGroup%>,<%=sort%>);" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                            </div>
                        </div>
                        <input type="hidden" name="optGroup<%=optGroup%>[]" class="optGroupID" value="">
                        <input type="hidden" name="poiSort[]" value="<%=sort%>" class="option-item-sort">
                    </div><!-- .option-group -->
                </script>
                <script type="text/javascript">
                    function deleteOptionItem(group,id){
                        $(".option-group[data-order='"+group+"']").find(".option-item[data-order='"+id+"']").remove();

                        $('#variationshider').addClass('hidden');
                        $('#changenotice').removeClass('hidden');
                    }

                    function indexOptionItems(){
                        $('.option-group-item-container').each(function(){
                            $(this).find('.option-item').each(function(i) {
                                $(this).find('.option-item-sort').val(i);
                                $(this).attr("data-order",i);
                            });
                        });

                    };
                    function addOptionItem(group){
                        var optItemsTemplate = _.template($('#option-item-template').html());
                        var optItemsContainer = $(".option-group-item-container[data-group='"+group+"']");

                        //Use the template to create a new item.
                        var temp = $(".option-group-item-container[data-group='"+group+"'] .option-item").length;
                        temp = (temp);
                        optItemsContainer.append(optItemsTemplate({
                            //vars to pass to the template
                            poiName: '',
                            poiID: '',
                            optGroup: group,
                            sort: temp,
                            poiHidden: ''
                        }));

                        //Init Index
                        indexOptionItems();
                        $('#variationshider').addClass('hidden');
                        $('#changenotice').removeClass('hidden');
                    }
                    $(function(){
                        
                        //Make items sortable. If we re-sort them, re-index them.
                        $(".option-group-item-container").sortable({
                            handle: ".grabme",
                            update: function(){
                                indexOptionItems();
                            }
                        });

                        //define template
                        var optItemsTemplate = _.template($('#option-item-template').html());

                        //load up items
                        <?php

                        if ($optItems) {
                            $count = count($groups);
                            for ($i=0;$i<$count;$i++) {
                                foreach ($optItems as $option) {
                                    //go through all options, see if it belongs in the group we're on in the for loop
                                    if ($option->getProductOptionGroupID() == $groups[$i]->getID()) {
                                        ?>
                        var optItemsContainer = $(".option-group-item-container[data-group='<?php echo $i?>']");
                        optItemsContainer.append(optItemsTemplate({
                            poiName: '<?php echo h($option->getName())?>',
                            poiID: '<?php echo $option->getID()?>',
                            optGroup: <?php echo $i?>,
                            sort: <?php echo $option->getSort()?>,
                            poiHidden: <?php echo($option->isHidden() ? '\'checked="checked"\'' : '""');
                                        ?>

                        }));
                        <?php

                                    }//if belongs to group
                                }//foreach opt
                            }
                        }//if items
            ?>

                        //indexOptionItems();


                    });

                </script>

            <br />

            <div class="form-group">
                <label><?php echo $form->checkbox('pVariations', '1', $p->hasVariations())?>
                <?php echo t('Options have different prices, SKUs or stock levels');
    ?></label>

                <?php if (!$pID) {
    ?>
                    <p class="alert alert-info hidden" id="variationnotice"><?php echo t('After creating options add the product to configure product variations.') ?></p>
                <?php 
}
    ?>


            </div>

            <?php if (!empty($comboOptions)) {
    ?>
            <div id="variations" class="<?php echo($p->hasVariations() ? '' : 'hidden');
    ?>">


                <h4><?php echo t('Variations');
    ?></h4>

                <?php if ($pID) {
    ?>
                    <p class="alert alert-info hidden" id="changenotice"><?php echo t('Product options have changed, update the product to configure updated variations') ?></p>
                <?php 
}
    ?>


                <div id="variationshider">

                 <?php
                $count = 0;

    foreach ($comboOptions as $combinedOptions) {
        ?>
                 <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo t('Options') . ':';
        ?>
                        <?php
                         $comboIDs = array();

        foreach ($combinedOptions as $optionItemID) {
            $comboIDs[] = $optionItemID;
            sort($comboIDs);
            $group = $groupLookup[$optionItemLookup[$optionItemID]->getProductOptionGroupID()];
            echo '<span class="label label-primary">' . ($group ? $group->getName() : '') . ': ' . $optionItemLookup[$optionItemID]->getName() . '</span> ';
        }

        ?>
                        <button class="btn btn-xs btn-default pull-right variationdisplaybutton" type="button" data-toggle="collapse">
                            <?php echo t('More options');
        ?>
                        </button>
                    </div>

                     <div class="panel-body">
                         <input type="hidden" name="option_combo[]" value="<?php echo implode('_', $comboIDs);
        ?>"/>

                         <?php if (isset($variationLookup[implode('_', $comboIDs)])) {
    $variation = $variationLookup[implode('_', $comboIDs)];
    $varid = $variation->getID();
} else {
    $variation = null;
    $varid = '';
}
        ?>

                        <div class="row form-group">
                         <div class="col-md-4">
                             <?php echo $form->label("", t("SKU"));
        ?>
                         </div>
                         <div class="col-md-8">
                            <?php echo $form->text("pvSKU[".$varid."]", $variation ? $variation->getVariationSKU() : '', array('placeholder' => t('Base SKU')));
        ?>
                         </div>
                        </div>

                         <div class="row form-group">
                             <div class="col-md-4">
                                 <?php echo $form->label("", t("Stock Level"));
        ?>
                             </div>
                             <div class="col-md-8">
                                 <div class="input-group">
                                     <?php
                                     if ($variation) {
                                         echo $form->text("pvQty[".$varid."]", $variation->getVariationQty(), array(($variation->isUnlimited() ? 'readonly' : '')=>($variation->isUnlimited() ? 'readonly' : '')));
                                     } else {
                                         echo $form->text("pvQty[".$varid."]", '', array('readonly'=>'readonly'));
                                     }
        ?>

                                     <div class="input-group-addon">
                                         <label><?php echo $form->checkbox('pvQtyUnlim['.$varid.']', '1', $variation ? $variation->isUnlimited() : true) ?> <?php echo t('Unlimited');
        ?></label>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <div class="row form-group">
                         <div class="col-md-4">
                            <?php echo $form->label("", t("Price"));
        ?>
                         </div>
                         <div class="col-md-8">
                            <div class="input-group">
                                 <div class="input-group-addon">
                                     <?php echo  Config::get('vividstore.symbol');
        ?>
                                 </div>
                                 <?php echo $form->text("pvPrice[".$varid."]", $variation ? $variation->getVariationPrice() : '', array('placeholder' => t('Base Price')));
        ?>
                            </div>
                        </div>
                        </div>

                         <div class="extrafields hidden">

                         <div class="row form-group">
                         <div class="col-md-4">
                                <?php echo $form->label("pvSalePrice[]", t("Sale Price"));
        ?>
                         </div>
                         <div class="col-md-8">
                             <div class="input-group">
                                 <div class="input-group-addon">
                                     <?php echo  Config::get('vividstore.symbol');
        ?>
                                 </div>
                                 <?php echo $form->text("pvSalePrice[".$varid."]", $variation ? $variation->getVariationSalePrice() : '', array('placeholder' => t('Base Sale Price')));
        ?>
                             </div>
                         </div>
                        </div>


                         <div class="row form-group">
                             <div class="col-md-12">
                                 <?php echo $form->label('pfID[]', t("Primary Image"));
        ?>
                                 <?php
                                 $pvfID = null;
        if ($variation) {
            $pvfID = $variation->getVariationImageID();
        }
        ?>
                                 <?php echo $al->image('ccm-image'.$count++, 'pvfID['.$varid.']', t('Choose Image'), $pvfID?File::getByID($pvfID):null);
        ?>
                             </div>
                         </div>
                        <div class="row form-group">
                        <div class="col-md-4">
                            <?php echo $form->label("", t("Weight"));
        ?>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group" >
                                <?php echo $form->text('pvWeight['.$varid.']', $variation ? $variation->getVariationWeight() : '', array('placeholder'=>t('Base Weight')))?>
                                <div class="input-group-addon"><?php echo Config::get('vividstore.weightUnit')?></div>
                            </div>
                         </div>
                        </div>
                        <div class="row form-group">
                        <div class="col-md-4">
                            <?php echo $form->label("", t("Number of Items"));
        ?>
                        </div>
                        <div class="col-md-8">
                             <?php echo $form->text('pvNumberItems['.$varid.']', $variation ? $variation->getVariationNumberItems() : '', array('min'=>0, 'step'=>1, 'placeholder'=>t('Base Number Of Items')))?>
                         </div>
                        </div>
                        <div class="row form-group">
                        <div class="col-md-4">
                            <?php echo $form->label("", t("Length"));
        ?>
                        </div>
                        <div class="col-md-8">
                             <div class="input-group" >
                                 <?php echo $form->text('pvLength['.$varid.']', $variation ? $variation->getVariationLength() : '', array('placeholder'=>t('Base Length')))?>
                                 <div class="input-group-addon"><?php echo Config::get('vividstore.sizeUnit')?></div>
                             </div>
                        </div>
                        </div>
                        <div class="row form-group">
                         <div class="col-md-4">
                             <?php echo $form->label("", t("Width"));
        ?>
                         </div>

                         <div class="col-md-8">
                             <div class="input-group" >
                                     <?php echo $form->text('pvWidth['.$varid.']', $variation ? $variation->getVariationWidth() : '', array('placeholder'=>t('Base Width')))?>
                                     <div class="input-group-addon"><?php echo Config::get('vividstore.sizeUnit')?></div>
                             </div>
                          </div>
                        </div>
                        <div class="row form-group">
                         <div class="col-md-4">
                             <?php echo $form->label("", t("Height"));
        ?>
                        </div>
                         <div class="col-md-8">
                             <div class="input-group" >
                                     <?php echo $form->text('pvHeight['.$varid.']', $variation ? $variation->getVariationHeight() : '', array('placeholder'=>t('Base Height')))?>
                                     <div class="input-group-addon"><?php echo Config::get('vividstore.sizeUnit')?></div>
                             </div>
                         </div>
                        </div>
                    </div>
                     </div>

                 </div>
                 <?php 
    }
    ?>
                </div>
                </div>
            <?php 
}
    ?>

            </div><!-- #product-options -->

            <div class="col-sm-7 store-pane" id="product-attributes">
                <div class="alert alert-info">
                    <?php echo t("While you can set and assign attributes, they're are currently only able to be accessed programmatically")?>
                </div>
                <?php

                if (count($attribs) > 0) {
                    foreach ($attribs as $ak) {
                        if (is_object($p)) {
                            $caValue = $p->getAttributeValueObject($ak);
                        }
                        ?>
                        <div class="clearfix">
                            <?php echo $ak->render('label');
                        ?>
                            <div class="input">
                                <?php echo $ak->render('composer', $caValue, true)?>
                            </div>
                        </div>
                    <?php 
                    }
                    ?>

                <?php 
                } else {
                    ?>
                    <em><?php echo t('You haven\'t created product attributes')?></em>

                <?php 
                }
    ?>

            </div>



            <div class="col-sm-7 store-pane" id="product-digital">

                <?php if (Config::get('concrete.permissions.model') != 'simple') {
    ?>
                    <?php
                    $files = $p->getProductDownloadFileObjects();
    for ($i=0;$i<1;$i++) {
        $file = $files[$i];
        ?>
                        <div class="form-group">
                            <?php echo $form->label("dffID".$i, t("File to download on purchase"));
        ?>
                            <?php echo $al->file('dffID'.$i, 'dffID[]', t('Choose File'), is_object($file)?$file:null)?>
                        </div>
                    <?php 
    }
} else {
    ?>
                    <div class="alert alert-info">
                        <?php
                        $a = '<a href="'.URL::to('/dashboard/system/permissions/advanced').'"><strong>';
    $aa = '</strong></a>';
    echo t("In order to have digital downloads, you need to %sturn on advanced permissions%s.", $a, $aa);
    ?>
                    </div>
                <?php 
}
    ?>

                <div class="form-group">
                    <?php echo $form->checkbox('pCreateUserAccount', '1', $p->createsLogin())?>
                    <?php echo $form->label('pCreateUserAccount', t('Create user account on purchase'))?>
                    <span class="help-block"><?php echo  t('When checked, if customer is guest, will create a user account on purchase');
    ?></span>
                </div>

                <div class="form-group">
                    <?php echo $form->label("usergroups", t("On purchase add user to user groups"));
    ?>
                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <select multiple="multiple" name="pUserGroups[]" id="groupselect" class="select2-select" style="width: 100%;" placeholder="<?php echo t('Select user groups');
    ?>">
                            <?php
                            $selectedusergroups = $p->getProductUserGroupIDs();
    foreach ($usergroups as $ugkey=>$uglabel) {
        ?>
                                <option value="<?php echo $ugkey;
        ?>" <?php echo(in_array($ugkey, $selectedusergroups) ? 'selected="selected"' : '');
        ?>>  <?php echo $uglabel;
        ?></option>
                            <?php 
    }
    ?>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <?php echo $form->checkbox('pAutoCheckout', '1', $p->autoCheckout())?>
                    <?php echo $form->label('pAutoCheckout', t('Send customer directly to checkout when added to cart'))?>
                </div>

                <div class="form-group">
                    <?php echo $form->checkbox('pExclusive', '1', $p->isExclusive())?>
                    <?php echo $form->label('pExclusive', t('Prevent this item from being in the cart with other items'))?>
                </div>


                <script type="text/javascript">

                </script>


            </div><!-- #product-digital -->

            <div class="col-sm-7 store-pane" id="product-page">

                <?php if ($p->getProductID()) {
    ?>

                    <?php
                    $page = Page::getByID($p->getProductPageID());
    if (!$page->isError()) {
        ?>
                        <strong><?php echo t("Detail Page is set to: ")?><a href="<?php echo $page->getCollectionLink()?>" target="_blank"><?php echo $page->getCollectionName()?></a></strong>

                    <?php 
    } else {
        ?>

                        <div class="alert alert-warning">
                            <?php echo t("We're not sure why, but this product doesn't seem to have a Page that correlates to it.")?>
                        </div>

                        <div class="form-group">
                            <label><?php echo t("Page Template")?></label>
                            <?php echo $form->select('selectPageTemplate', $pageTemplates, null);
        ?>
                        </div>

                        <a href="<?php echo Url::to('/dashboard/store/products/generate/', $p->getProductID())?>" class="btn btn-primary" id="btn-generate-page"><?php echo t("Generate a Product Page")?></a>


                    <?php 
    }
    ?>

                <?php 
} else {
    ?>

                    <div class="alert alert-info">
                        <?php echo t("When you create a product, we'll make a page for that product. Below is the available templates for the Product Page Type. Choose one, and we'll use this to create the Detail page.")?>
                    </div>
                    <div class="form-group">
                        <label><?php echo t("Page Template")?></label>
                        <?php echo $form->select('selectPageTemplate', $pageTemplates, null);
    ?>
                    </div>

                <?php 
}
    ?>

            </div>

        </div><!-- .row -->

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?php echo URL::to('/dashboard/store/products/')?>" class="btn btn-default pull-left"><?php echo t("Cancel / View All Products")?></a>
                <button class="pull-right btn btn-success" disabled="disabled" type="submit" ><?php echo t('%s Product', $actionType)?></button>
            </div>
        </div>

        <script>
            $(window).load(function(){
                setTimeout(
                    function() {
                       $('.ccm-dashboard-form-actions .btn-success').removeAttr('disabled');
                    }, 2000);
            });

            $(function(){
                $('.variationdisplaybutton').click(function(el) {
                   $(this).closest('.panel').find('.extrafields').toggleClass('hidden');
                    el.preventDefault();
                });
            });
        </script>

    </form>



<?php 
} elseif (in_array($controller->getTask(), $listViews)) {
    ?>

    <div class="ccm-dashboard-header-buttons">
        <!--<a href="<?php echo View::url('/dashboard/store/products/', 'attributes')?>" class="btn btn-dark"><?php echo t("Manage Attributes")?></a>-->
        <a href="<?php echo View::url('/dashboard/store/products/', 'groups')?>" class="btn btn-dark"><?php echo t("Manage Groups")?></a>
        <a href="<?php echo View::url('/dashboard/store/products/', 'add')?>" class="btn btn-primary"><?php echo t("Add Product")?></a>
    </div>

    <div class="ccm-dashboard-content-full">
        <form role="form" class="form-inline ccm-search-fields">
            <div class="ccm-search-fields-row">
                <?php if ($grouplist) {
    ?>
                    <ul id="group-filters" class="nav nav-pills">
                        <li><a href="<?php echo View::url('/dashboard/store/products/')?>"><?php echo t('All Groups')?></a></li>
                        <?php foreach ($grouplist as $group) {
    ?>
                            <li><a href="<?php echo View::url('/dashboard/store/products/', $group->getGroupID())?>"><?php echo $group->getGroupName()?></a></li>
                        <?php 
}
    ?>
                    </ul>
                <?php 
}
    ?>
            </div>
            <div class="ccm-search-fields-row ccm-search-fields-submit">
                <div class="form-group">
                    <div class="ccm-search-main-lookup-field">
                        <i class="fa fa-search"></i>
                        <?php echo $form->search('keywords', $searchRequest['keywords'], array('placeholder' => t('Search Products')))?>
                    </div>

                </div>
                <button type="submit" class="btn btn-primary pull-right"><?php echo t('Search')?></button>
            </div>

        </form>

        <table class="ccm-search-results-table">
            <thead>
            <th><a><?php echo t('Primary Image')?></a></th>
            <th><a><?php echo t('Product Name')?></a></th>
            <th><a><?php echo t('Active')?></a></th>
            <th><a><?php echo t('Stock Level')?></a></th>
            <th><a><?php echo t('Price')?></a></th>
            <th><a><?php echo t('Featured')?></a></th>
            <th><a><?php echo t('Groups')?></a></th>
            <th><a><?php echo t('Actions')?></a></th>
            </thead>
            <tbody>

            <?php if (count($products)>0) {
    foreach ($products as $p) {
        ?>
                    <tr>
                        <td><?php echo $p->getProductImageThumb();
        ?></td>
                        <td><strong><a href="<?php echo View::url('/dashboard/store/products/edit/', $p->getProductID())?>"><?php echo  $p->getProductName();
        $sku = $p->getProductSKU();
        if ($sku) {
            echo ' (' .$sku . ')';
        }
        ?>
                                </a></strong></td>
                        <td>
                            <?php
                            if ($p->isActive()) {
                                echo "<span class='label label-success'>" . t('Active') . "</span>";
                            } else {
                                echo "<span class='label label-default'>" . t('Inactive') . "</span>";
                            }
        ?>
                        </td>
                        <td><?php
                            if ($p->hasVariations()) {
                                echo '<span class="label label-info">' . t('Multiple') . '</span>';
                            } else {
                                echo($p->isUnlimited() ? '<span class="label label-default">' . t('Unlimited') . '</span>' : $p->getProductQty());
                            }
        ?></td>
                        <td>
                            <?php
                            if ($p->hasVariations()) {
                                echo '<span class="label label-info">' . t('Multiple') . '</span><br />';
                                echo t('Base price') . ': ' . $p->getFormattedPrice();
                            } else {
                                echo $p->getFormattedPrice();
                            }
        ?></td>
                        <td>
                            <?php
                            if ($p->isFeatured()) {
                                echo "<span class='label label-success'>" . t('Featured') . "</span>";
                            } else {
                                echo "<span class='label label-default'>" . t('Not Featured') . "</span>";
                            }
        ?>
                        </td>
                        <td>
                            <?php $productgroups = $p->getProductGroups();
        foreach ($productgroups as $pg) {
            ?>
                                <span class="label label-primary"><?php echo  $pg->gName;
            ?></span>
                             <?php 
        }
        ?>

                            <?php if (empty($productgroups)) {
    ?>
                                <em><?php echo  t('None');
    ?></em>
                            <?php 
}
        ?>
                        </td>
                        <td>
                            <a class="btn btn-default"
                               href="<?php echo View::url('/dashboard/store/products/edit/', $p->getProductID())?>"><i
                                    class="fa fa-pencil"></i></a>
                        </td>
                    </tr>
                <?php 
    }
}
    ?>
            </tbody>
        </table>

        <?php if ($paginator->getTotalPages() > 1) {
    ?>
            <div class="ccm-search-results-pagination">
                <?php echo  $pagination ?>
            </div>
        <?php 
}
    ?>

    </div>

<?php 
} elseif (in_array($controller->getTask(), $groupViews)) {
    ?>

    <?php if ($grouplist) {
    ?>
        <h3><?php echo t("Groups")?></h3>
        <ul class="list-unstyled group-list" data-delete-url="<?php echo View::url('/dashboard/store/products/deletegroup')?>" data-save-url="<?php echo View::url('/dashboard/store/products/editgroup')?>">
            <?php foreach ($grouplist as $group) {
    ?>
                <li data-group-id="<?php echo $group->getGroupID()?>">
                    <span class="group-name"><?php echo $group->getGroupName()?></span>
                    <input class="hideme edit-group-name" type="text" value="<?php echo $group->getGroupName()?>">
                    <span class="btn btn-default btn-edit-group-name"><i class="fa fa-pencil"></i></span>
                    <span class="hideme btn btn-default btn-cancel-edit"><i class="fa fa-ban"></i></span>
                    <span class="hideme btn btn-warning btn-save-group-name"><i class="fa fa-save"></i></span>
                    <span class="btn btn-danger btn-delete-group"><i class="fa fa-trash"></i></span>
                </li>
            <?php 
}
    ?>
        </ul>

    <?php 
} else {
    ?>

        <div class="alert alert-info"><?php echo t("You have not added a group yet")?></div>

    <?php 
}
    ?>
    <form method="post" action="<?php echo $view->action('addgroup')?>">
        <h4><?php echo t('Add a Group')?></h4>
        <hr>
        <div class="form-group">
            <?php echo $form->label('groupName', t("Group Name"));
    ?>
            <?php echo $form->text('groupName', null, array('style'=>'width:200px'));
    ?>
        </div>
        <input type="submit" class="btn btn-primary" value="<?php echo t('Add Group');
    ?>">
    </form>

<?php 
}  ?>

<style>
    @media (max-width: 992px) {
        div#ccm-dashboard-content div.ccm-dashboard-content-full {
            margin-left: -20px !important;
            margin-right: -20px !important;
        }
    }
</style>
