<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
$addViews = array('add','add_rate','edit');

if(in_array($controller->getTask(),$addViews)){
/// Add Tax Method View    
?>
    
    
<form id="settings-tax" action="<?=URL::to('/dashboard/store/settings/tax','add_rate')?>" method="post" data-states-utility="<?=View::url('/checkout/getstates')?>">

    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2">
            <input type="hidden" name="taxRateID" value="<?=$taxRate->getTaxRateID()?>">
            <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <?php echo $form->label('taxEnabled',t('Enable Tax Rate')); ?>
                                <?php echo $form->select('taxEnabled',array(false=>t('No'),true=>t('Yes')),$taxRate->isEnabled()); ?>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <?php echo $form->label('taxLabel',t('Tax Label')); ?>
                                <?php echo $form->text('taxLabel',$taxRate->getTaxLabel());?>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <?php echo $form->label('taxRate',t('Tax Rate %')); ?>
                                <div class="input-group">
                                    <?php echo $form->text('taxRate',$taxRate->getTaxRate()); ?>
                                    <div class="input-group-addon">%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="calculation"><?=t("Are Prices Entered with Tax Included?")?></label>
                        <?php echo $form->select('taxIncluded',array('add'=>t("No, I will enter product prices EXCLUSIVE of tax"),'extract'=>t("Yes, I will enter product prices INCLUSIVE of tax")),$taxRate->getTaxIncluded()); ?>
                    </div>

                    <div class="form-group">
                        <label for="taxBased"><?=t("Tax is Based on the")?></label>
                        <?php echo $form->select('taxBased',array('subtotal'=>t("Product Total"),'grandtotal'=>t("Product Total + Shipping")),$taxRate->getTaxBasedOn()); ?>
                    </div>
                    
                    <h3><?=t("When to Charge Tax")?></h3>
                    <div class="form-group">                    
                    <?php echo $form->select('addOrExtract',array('add'=>t("Calculated from total and added to order"),'extract'=>t("Already in product prices, only display as component of total")),$taxRate->getAddOrExtract()); ?>
                    </div>
                    
                    <div class="row">
                        
                        <div class="col-sm-5">
                   
                            <div class="form-group">
                                <label for="taxAddress" class="control-label"><?=t("If the Customers...")?></label>
                                <?php echo $form->select('taxAddress',array('shipping'=>t("Shipping Address"),'billing'=>t("Billing Address")),$taxRate->getTaxAddress()); ?>
                            </div>
                        
                        </div>
                        
                        <div class="col-sm-7">
                        <div class="form-horizontal">
                            <p><strong><?=t("Matches...")?></strong></p>
                            <div class="form-group">
                                <label for="taxCountry" class="col-sm-5 control-label"><?=t("Country")?> <small class="text-muted"><?=t("Required")?></small></label>
                                <div class="col-sm-7">    
                                    <?php $country = $taxRate->getTaxCountry(); ?>
                                    <?php echo $form->select('taxCountry',$countries,$country?$country:'US',array("onchange"=>"updateTaxStates()")); ?>    
                                </div>
                            </div>
                            
                            
                            <div class="form-group">
                                <label for="taxState" class="col-sm-5 control-label"><?=t("Region")?> <small class="text-muted"><?=t("Optional")?></small></label>
                                <div class="col-sm-7"> 
                                    <?php $state = $taxRate->getTaxState(); ?>
                                    <?php echo $form->select('taxState',$states,$state?$state:""); ?>
                                    <?php echo $form->hidden("savedTaxState",$state); ?>
                                </div>
                            </div>
        
                            <div class="form-group">
                                <label for="taxState" class="col-sm-5 control-label"><?=t("City")?> <small class="text-muted"><?=t("Optional")?></small></label>
                                <div class="col-sm-7"> 
                                    <?php echo $form->text('taxCity',$taxRate->getTaxCity());?>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
            
        </div>
    </div>

    
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-success" type="submit" ><?=t('%s Tax Rate',$task)?></button>
        </div>
    </div>
    
</form>
     
<?php } else { ?>
<div class="ccm-dashboard-header-buttons">
    <a href="<?php echo View::url('/dashboard/store/settings/tax','add')?>" class="btn btn-primary"><?php echo t("Add Tax Rate")?></a>
    <a href="<?php echo View::url('/dashboard/store/settings')?>" class="btn btn-default"><i class="fa fa-gear"></i> <?php echo t("General Settings")?></a>
</div>

<div class="dashboard-tax-rates">
	
	<table class="table table-striped">
		<thead>
			<th><?=t("Tax Rates")?></th>
			<th class="text-right"><?=t("Actions")?></th>
		</thead>
		<tbody>
		    <?php if(count($taxRates)>0){?>
		        <?php foreach($taxRates as $tr){?>
        			<tr>
        				<td><?=$tr->getTaxLabel()?></td>
        				<td class="text-right">
        					<a href="<?=URL::to('/dashboard/store/settings/tax/edit',$tr->getTaxRateID())?>" class="btn btn-default"><?=t("Edit")?></a>
        					<a href="<?=URL::to('/dashboard/store/settings/tax/delete',$tr->getTaxRateID())?>" class="btn btn-danger"><?=t("Delete")?></a>
        				</td>
        			</tr>
			     <?php } ?>
			<?php } ?>
		</tbody>
	</table>
	
</div>

<?php } ?>