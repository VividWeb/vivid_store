<?php  
defined('C5_EXECUTE') or die(_("Access Denied."));
$addViews = array('add');
$editViews = array('edit');

if(in_array($controller->getTask(),$addViews)){
/// Add Shipping Method View    
?>
    
    
    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2">
            <h2><?php echo $smt->getName(); ?></h2>
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <?php echo $form->label('methodName',t("Method Name")); ?>
                        <?php echo $form->text('methodName'); ?>
                    </div>
                    <div class="form-group">
                        <?php echo $form->label('methodEnabled',t("Enabled")); ?>
                        <?php echo $form->select('methodEnabled',array(true=>"Enabled",false=>"Disabled")); ?>
                    </div>
                </div>                
            </div>    
            <hr>
            <?php $smt->renderDashboardForm(); ?>    
        </div>
    </div>

    
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-success" type="submit" ><?=t('Add Shipping Method')?></button>
        </div>
    </div>
    
<?php } elseif(in_array($controller->getTask(),$editViews)){
/// Edit Shipping Method View    
?>
    
<?php } else { ?>
<div class="ccm-dashboard-header-buttons">
    <?php 
    if(count($methodTypes) > 0){?>
    <div class="btn-group">
        <a href="" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><?=t('Add Method')?> <span class="caret"></span></a>
        <ul class="dropdown-menu" role="menu">
            <?php foreach($methodTypes as $smt){?>
            <li><a href="<?=URL::to('/dashboard/store/settings/shipping/add',$smt->getPaymentMethodTypeID())?>"><?=$smt->getName()?></a></li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
    <a href="<?php echo View::url('/dashboard/store/settings')?>" class="btn btn-default"><i class="fa fa-gear"></i> <?php echo t("General Settings")?></a>
</div>
<?php } ?>