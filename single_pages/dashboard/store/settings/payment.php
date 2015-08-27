<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
$addViews = array('add','add_method','edit');
$editViews = array('edit');

if(in_array($controller->getTask(),$addViews)){
/// Add Tax Method View    
?>
    
    
<form action="<?=URL::to('/dashboard/store/settings/tax','add_rate')?>" method="post">

    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2">
            
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
    <a href="<?php echo View::url('/dashboard/store/settings')?>" class="btn btn-default"><i class="fa fa-gear"></i> <?php echo t("General Settings")?></a>
</div>

<div class="dashboard-tax-rates">
	
	<table class="table table-striped">
		<thead>
			<th><?=t("Tax Rates")?></th>
			<th class="text-right"><?=t("Actions")?></th>
		</thead>
		<tbody>
			<tr>
				<td>VAT</td>
				<td class="text-right">
					<a href="<?=URL::to('/dashboard/store/settings/tax/edit')?>" class="btn btn-default"><?=t("Edit")?></a>
					<a href="" class="btn btn-danger"><?=t("Delete")?></a>
				</td>
			</tr>
		</tbody>
	</table>
	
</div>

<?php } ?>