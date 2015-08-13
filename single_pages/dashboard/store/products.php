<?php
defined('C5_EXECUTE') or die("Access Denied.");

$listViews = array('view','updated','removed','success');
$addViews = array('add','edit','save');
$groupViews = array('groups','groupadded','addgroup');
$attributeViews = array('attributes','attributeadded','attributeremoved');
$ps = Core::make('helper/form/page_selector');


use \Config;
use \Concrete\Package\VividStore\Src\VividStore\Groups\ProductGroup as VividProductGroup;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;

?>

<?php if (in_array($controller->getTask(),$addViews)){ //if adding or editing a product
    if(!is_object($p)) {
        $p = new VividProduct(); //does nothing other than shutup errors.}
    }

    $pID = $p->getProductID()
 ?>

    <?php if ($pID > 0) { ?>
    <div class="ccm-dashboard-header-buttons">
        <form method="post" id="delete" action="<?php echo View::url('/dashboard/store/products/delete/', $pID)?>" >
            <button class="btn btn-danger"><?php echo t("Delete Product")?></button>
        </form>

        <script type="text/javascript">
        $(function(){
            $('#delete').submit(function() {
                return confirm('<?= t("Are you sure you want to delete this product?"); ?>');
            });
        });
        </script>
    </div>
    <?php } ?>

    <form method="post" action="<?=$view->action('save')?>">
        <input type="hidden" name="pID" value="<?=$p->getProductID()?>"/>

        <div class="row">
            <div class="col-sm-4">
                <div class="vivid-store-side-panel">
                    <ul>
                        <li><a href="#product-overview" data-pane-toggle class="active"><?=t('Overview')?></a></li>
                        <li><a href="#product-categories" data-pane-toggle><?=t('Categories')?></a></li>
                        <li><a href="#product-shipping" data-pane-toggle><?=t('Shipping')?></a></li>
                        <li><a href="#product-images" data-pane-toggle><?=t('Images')?></a></li>
                        <li><a href="#product-options" data-pane-toggle><?=t('Options')?></a></li>
                        <li><a href="#product-attributes" data-pane-toggle><?=t('Attributes')?></a></li>
                        <li><a href="#product-digital" data-pane-toggle><?=t("Downloads and User Groups")?></a></li>
                        <li><a href="#product-page" data-pane-toggle><?=t('Detail Page')?></a></li>
                    </ul>
                </div>
            </div>

            <div class="col-sm-7 store-pane active" id="product-overview">

                <div class="form-group">
                    <?php echo $form->label("pName", t("Product Name"));?>
                    <?php echo $form->text("pName", $p->getProductName());?>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pPrice", t("Price"));?>
                            <?php $price = $p->getProductPrice(); ?>
                            <?php echo $form->text("pPrice", $price?$price:'0');?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pQty", t("Quantity"));?>
                            <?php $qty = $p->getProductQty(); ?>
                            <?php echo $form->text("pQty", $qty?$qty:'999');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pTaxable", t("Taxable"));?>
                            <?php echo $form->select("pTaxable",array('0'=>t('No'),'1'=>t('Yes')), $p->isTaxable());?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pActive", t("Active"));?>
                            <?php echo $form->select("pActive", array('1'=>t('Active'),'0'=>t('Inactive')), $p->isActive());?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pFeatured", t("Featured Product"));?>
                            <?php echo $form->select("pFeatured",array('0'=>t('No'),'1'=>t('Yes')), $p->isFeatured());?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <?php echo $form->label("pDesc", t("Short Description"));?><br>
                    <textarea class="redactor-content" name="pDesc" id="pDesc" style="display:none;"><?=$p->getProductDesc()?></textarea>
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
                    <?php echo $form->label("pDesc", t("Product Details (Long Description)"));?><br>
                    <textarea class="redactor-content" name="pDetail" id="pDetail" style="display:none;"><?=$p->getProductDetail()?></textarea>
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
                <h4><?=t('Categorized under pages')?></h4>

                <div class="form-group" id="page_pickers">
                    <div class="page_picker">
                        <?php echo $ps->selectPage('cID[]', $pages[0]['cID'] ?  $pages[0]['cID'] : false); ?>
                    </div>

                    <?php for($i = 1; $i < 7; $i++) { ?>
                        <div class="page_picker <?= ($pages[$i -1]['cID']  ? '' : 'picker_hidden' ); ?>">
                            <?php echo $ps->selectPage('cID[]',  $pages[$i]['cID'] ?  $pages[$i]['cID'] : false); ?>
                        </div>

                    <?php } ?>
                </div>

                <h4><?=t('In product groups')?></h4>
                <div class="ccm-search-field-content ccm-search-field-content-select2">
                    <select multiple="multiple" name="pProductGroups[]" class="existing-select2 select2-select" style="width: 100%">
                        <?php foreach ($productgroups as $pgkey=>$pglabel) { ?>
                            <option value="<?php echo $pgkey;?>" <?php echo (in_array($pgkey, $pgroups) ? 'selected="selected"' : ''); ?>>  <?php echo $pglabel; ?></option>
                        <?php } ?>
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
                    <?php echo $form->label("pShippable", t("Product is Shippable"));?>
                    <?php echo $form->select("pShippable",array('1'=>t('Yes'),'0'=>t('No')), $p->isShippable());?>
                </div>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo $form->label("pWeight", t("Weight"));?>
                            <div class="input-group" >
                                <?php $weight = $p->getProductWeight(); ?>
                                <?=$form->text('pWeight',$weight?$weight:'0')?>
                                <div class="input-group-addon"><?=Config::get('vividstore.weightUnit')?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <div class="form-group">
                                <?php echo $form->label("pLength", t("Length"));?>
                                <div class="input-group" >
                                    <?php $length = $p->getDimensions('l'); ?>
                                    <?=$form->text('pLength',$length?$length:'0')?>
                                    <div class="input-group-addon"><?=Config::get('vividstore.sizeUnit')?></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form->label("pWidth", t("Width"));?>
                                <div class="input-group" >
                                    <?php $width = $p->getDimensions('w'); ?>
                                    <?=$form->text('pWidth',$width?$width:'0')?>
                                    <div class="input-group-addon"><?=Config::get('vividstore.sizeUnit')?></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form->label("pHeight", t("Height"));?>
                                <div class="input-group">
                                    <?php $height = $p->getDimensions('h'); ?>
                                    <?=$form->text('pHeight',$height?$height:'0')?>
                                    <div class="input-group-addon"><?=Config::get('vividstore.sizeUnit')?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div><!-- #product-shipping -->

            <div class="col-sm-7 store-pane" id="product-images">

                <div class="form-group">
                    <?php echo $form->label('pfID',t("Primary Product Image")); ?>
                    <?php $pfID = $p->getProductImageID(); ?>
                    <?php echo $al->image('ccm-image', 'pfID', t('Choose Image'), $pfID?File::getByID($pfID):null); ?>
                </div>


                <h4><?=t('Additional Images')?></h4>

                <div id="additional-images-container"></div>

                <div class="clearfix">
                    <span class="btn btn-default" id="btn-add-image"><?=t('Add Image')?></span>
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
                            <i class="fa fa-picture-o"></i> <?=t('Choose Image');?>
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
                        if($images) {
                            $count = 0;
                            foreach ($images as $image) {
                        ?>
                        itemsContainer.append(itemTemplate({

                            pifID: '<?php echo $image['pifID'] ?>',
                            <?php if($image['pifID']) { ?>
                            thumb: '<?php echo File::getByID($image['pifID'])->getThumbnailURL('file_manager_listing');?>',
                            <?php } else { ?>
                            thumb: '',
                            <?php } ?>
                            sort: '<?=$count++ ?>'
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

                <h4><?=t('Options')?></h4>
                <div id="product-options-container"></div>

                <div class="clearfix">
                    <span class="btn btn-primary" id="btn-add-option-group"><?=t('Add Option Group')?></span>
                </div>
                <!-- THE TEMPLATE WE'LL USE FOR EACH OPTION GROUP -->
                <script type="text/template" id="option-group-template">
                    <div class="panel panel-default option-group clearfix" data-order="<%=sort%>">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3 label-shell">
                                    <label for="pogName<%=sort%>" class="text-right"><i class="fa fa-arrows drag-handle pull-left"></i> <span class="hidden-xs"><?=t('Group Name:')?></span></label>
                                </div>
                                <div class="col-xs-5">
                                    <input type="text" class="form-control" name="pogName[]" value="<%=pogName%>">
                                </div>
                                <div class="col-xs-4 text-right">
                                    <a href="javascript:addOptionItem(<%=sort%>)" data-group="<%=sort%>" class="btn btn-default btn-add-option-item"><i data-toggle="tooltip" data-placement="top" title="<?=t('Add Option to the Group')?>" class="fa fa-plus"></i></span>
                                        <a href="javascript:deleteOptionGroup(<%=sort%>)" class="btn btn-delete-item btn-danger"><i data-toggle="tooltip" data-placement="top" title="<?=t('Delete the Option Group')?>" class="fa fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div data-group="<%=sort%>" class="option-group-item-container"></div>
                        </div>
                        <input type="hidden" name="pogID" value="<%=pogID%>">
                        <input type="hidden" name="pogSort[]" value="<%=sort%>" class="option-group-sort">
                    </div><!-- .option-group -->
                </script>
                <script type="text/javascript">
                    function deleteOptionGroup(id){
                        $(".option-group[data-order='"+id+"']").remove();
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

                        //load up images
                        <?php
                        if($groups) {
                            foreach ($groups as $group) {
                        ?>
                        optionsContainer.append(optionsTemplate({
                            pogName: '<?php echo $group['pogName'] ?>',
                            pogID: '<?php echo $group['pogID']?>',
                            sort: '<?=$group['pogSort'] ?>'
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
                        });
                    });

                </script>
                <!-- TEMPLATE FOR EACH OPTION ITEM ---->
                <script type="text/template" id="option-item-template">
                    <div class="option-item clearfix form-horizontal" data-order="<%=sort%>" data-option-group="<%=optGroup%>">
                        <div class="form-group">
                            <div class="col-sm-3 text-right">
                                <label class="grabme"><?=t('Option')?>:</label>
                            </div>
                            <div class="col-sm-5">
                                <input type="text" name="poiName[]" class="form-control" value="<%=poiName%>">
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
                            optGroup: group,
                            sort: temp
                        }));

                        //Init Index
                        indexOptionItems();
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
                        if($optItems) {
                            $count = count($groups);
                            for($i=0;$i<$count;$i++){
                                foreach($optItems as $option){
                                    //go through all options, see if it belongs in the group we're on in the for loop
                                    if($option['pogID'] == $groups[$i]['pogID']){?>
                        var optItemsContainer = $(".option-group-item-container[data-group='<?=$i?>']");
                        optItemsContainer.append(optItemsTemplate({
                            poiName: '<?=$option['poiName']?>',
                            optGroup: <?=$i?>,
                            sort: <?=$option['poiSort']?>

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

            </div><!-- #product-options -->

            <div class="col-sm-7 store-pane" id="product-attributes">
                <div class="alert alert-info">
                    <?=t("While you can set and assign attributes, they're are currently only able to be accessed programmatically")?>
                </div>
                <?php

                if (count($attribs) > 0) {
                    foreach($attribs as $ak) {
                        if (is_object($p)) {
                            $caValue = $p->getAttributeValueObject($ak);
                        }
                        ?>
                        <div class="clearfix">
                            <?php echo $ak->render('label');?>
                            <div class="input">
                                <?php echo $ak->render('composer', $caValue, true)?>
                            </div>
                        </div>
                    <?php  } ?>

                <?php  } else {?>
                    <em><?php echo t('You have\'t created product attributes')?></em>

                <?php }?>

            </div>



            <div class="col-sm-7 store-pane" id="product-digital">

                <?php if (Config::get('concrete.permissions.model') != 'simple') { ?>
                    <?php
                    $files = $p->getProductDownloadFileObjects();
                    for($i=0;$i<1;$i++){
                        $file = $files[$i];
                        ?>
                        <div class="form-group">
                            <?php echo $form->label("dffID".$i, t("File to download on purchase"));?>
                            <?php echo $al->file('dffID'.$i, 'dffID[]', t('Choose File'), is_object($file)?$file:null)?>
                        </div>
                    <?php }
                } else { ?>
                    <div class="alert alert-info">
                        <?php
                        $a = '<a href="'.URL::to('/dashboard/system/permissions/advanced').'"><strong>';
                        $aa = '</strong></a>';
                        echo t("In order to have digital downloads, you need to %sturn on advanced permissions%s.",$a,$aa);
                        ?>
                    </div>
                <?php } ?>

                <div class="form-group">
                    <?php echo $form->label("usergroups", t("On purchase add user to user groups"));?>
                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <select multiple="multiple" name="pUserGroups[]" id="groupselect" class="select2-select" style="width: 100%;" placeholder="<?php echo t('Select user groups');?>">
                            <?php
                            $selectedusergroups = $p->getProductUserGroups();
                            foreach ($usergroups as $ugkey=>$uglabel) { ?>
                                <option value="<?php echo $ugkey;?>" <?php echo (in_array($ugkey, $selectedusergroups) ? 'selected="selected"' : ''); ?>>  <?php echo $uglabel; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <script type="text/javascript">
                    $(function() {
                        $('#groupselect').select2();
                    });
                </script>


            </div><!-- #product-digital -->

            <div class="col-sm-7 store-pane" id="product-page">

                <?php if($p->getProductID()){ ?>

                    <?php
                    $page = Page::getByID($p->getProductPageID());
                    if(!$page->isError()){ ?>
                        <strong><?=t("Detail Page is set to: ")?><a href="<?=$page->getCollectionLink()?>" target="_blank"><?=$page->getCollectionName()?></a></strong>

                    <?php } else { ?>

                        <div class="alert alert-warning">
                            <?=t("We're not sure why, but this product doesn't seem to have a Page that correlates to it.")?>
                        </div>

                        <div class="form-group">
                            <label><?=t("Page Template")?></label>
                            <?php echo $form->select('selectPageTemplate',$pageTemplates,null);?>
                        </div>

                        <a href="<?=Url::to('/dashboard/store/products/generate/',$p->getProductID())?>" class="btn btn-primary" id="btn-generate-page"><?=t("Generate a Product Page")?></a>


                    <?php } ?>

                <?php } else { ?>

                    <div class="alert alert-info">
                        <?=t("When you create a product, we'll make a page for that product. For now though, there's nothing to see here.")?>
                    </div>

                <?php } ?>

            </div>

        </div><!-- .row -->

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?php echo URL::to('/dashboard/store/products/')?>" class="btn btn-default pull-left"><?php echo t("Cancel")?></a>
                <button class="pull-right btn btn-success" type="submit" ><?=t('%s Product',$actionType)?></button>
            </div>
        </div>

    </form>



<?php } else if(in_array($controller->getTask(),$listViews)) { ?>

    <div class="ccm-dashboard-header-buttons">
        <!--<a href="<?php echo View::url('/dashboard/store/products/', 'attributes')?>" class="btn btn-dark"><?php echo t("Manage Attributes")?></a>-->
        <a href="<?php echo View::url('/dashboard/store/products/', 'groups')?>" class="btn btn-dark"><?php echo t("Manage Groups")?></a>
        <a href="<?php echo View::url('/dashboard/store/products/', 'add')?>" class="btn btn-primary"><?php echo t("Add Product")?></a>
    </div>

    <div class="ccm-dashboard-content-full">
        <form role="form" class="form-inline ccm-search-fields">
            <div class="ccm-search-fields-row">
                <?php if($grouplist){?>
                    <ul id="group-filters" class="nav nav-pills">
                        <li><a href="<?php echo View::url('/dashboard/store/products/')?>"><?=t('All Groups')?></a></li>
                        <?php foreach($grouplist as $group){ ?>
                            <li><a href="<?php echo View::url('/dashboard/store/products/', $group->getGroupID())?>"><?=$group->getGroupName()?></a></li>
                        <?php } ?>
                    </ul>
                <?php } ?>
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
            <th><a><?=t('Primary Image')?></a></th>
            <th><a><?=t('Product Name')?></a></th>
            <th><a><?=t('Quantity')?></a></th>
            <th><a><?=t('Price')?></a></th>
            <th><a><?=t('Featured')?></a></th>
            <th><a><?=t('Groups')?></a></th>
            <th><a><?=t('Actions')?></a></th>
            </thead>
            <tbody>

            <?php if(count($products)>0) {
                foreach ($products as $p) {
                    ?>
                    <tr>
                        <td><?php echo $p->getProductImageThumb();?></td>
                        <td><strong><?= $p->getProductName() ?></strong></td>
                        <td><?= $p->getProductQty() ?></td>
                        <td><?= $p->getFormattedPrice() ?></td>
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
                            foreach($productgroups as $pg) { ?>
                                <span class="label label-primary"><?= $pg; ?></span>
                             <?php } ?>

                            <?php if (empty($productgroups)) { ?>
                                <em><?= t('None');?></em>
                            <?php } ?>
                        </td>
                        <td>
                            <a class="btn btn-default"
                               href="<?php echo View::url('/dashboard/store/products/edit/', $p->getProductID())?>"><i
                                    class="fa fa-pencil"></i></a>
                        </td>
                    </tr>
                <?php }
            }?>
            </tbody>
        </table>

        <?php if ($paginator->getTotalPages() > 1) { ?>
            <?= $pagination ?>
        <?php } ?>

    </div>

<?php } else if (in_array($controller->getTask(),$groupViews)){ ?>

    <?php if($grouplist){?>
        <h3><?=t("Groups")?></h3>
        <ul class="list-unstyled group-list" data-delete-url="<?php echo View::url('/dashboard/store/products/deletegroup')?>" data-save-url="<?php echo View::url('/dashboard/store/products/editgroup')?>">
            <?php foreach($grouplist as $groupItem){
                $group = VividProductGroup::getByID($groupItem->getGroupID());
                ?>

                <li data-group-id="<?=$group->getGroupID()?>">
                    <span class="group-name"><?=$group->getGroupName()?></span>
                    <input class="hideme edit-group-name" type="text" value="<?=$group->getGroupName()?>">
                    <span class="btn btn-default btn-edit-group-name"><i class="fa fa-pencil"></i></span>
                    <span class="hideme btn btn-default btn-cancel-edit"><i class="fa fa-ban"></i></span>
                    <span class="hideme btn btn-warning btn-save-group-name"><i class="fa fa-save"></i></span>
                    <span class="btn btn-danger btn-delete-group"><i class="fa fa-trash"></i></span>
                </li>

            <?php } ?>
        </ul>

    <?php } else { ?>

        <div class="alert alert-info"><?=t("You have not added a group yet")?></div>

    <?php } ?>
    <form method="post" action="<?=$view->action('addgroup')?>">
        <h4><?=t('Add a Group')?></h4>
        <hr>
        <div class="form-group">
            <?php echo $form->label('groupName',t("Group Name")); ?>
            <?php echo $form->text('groupName',null,array('style'=>'width:200px')); ?>
        </div>
        <input type="submit" class="btn btn-primary" value="<?=t('Add Group');?>">
    </form>

<?php }  ?>

<style>
    @media (max-width: 992px) {
        div#ccm-dashboard-content div.ccm-dashboard-content-full {
            margin-left: -20px !important;
            margin-right: -20px !important;
        }
    }
</style>
