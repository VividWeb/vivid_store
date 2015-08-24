<?php
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use \Concrete\Package\VividStore\Src\VividStore\Report\SalesReport;
?>

<div class="row">
	<div class="col-xs-12 col-md-4">
		<div class="panel panel-sale">
			<?php $ts = SalesReport::getTodaysSales(); ?>
			<div class="panel-heading">
				<h2 class="panel-title"><?=t("Today's Sales")?></h2>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Total </strong> <?=Price::format($ts['total'])?>
					</div>
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Products</strong> <?=Price::format($ts['productTotal'])?>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Tax</strong> <?=Price::format($ts['taxTotal'])?>
					</div>
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Shipping</strong> <?=Price::format($ts['shippingTotal'])?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-md-4">
		<div class="panel panel-sale">
			<?php $td = SalesReport::getThirtyDays(); ?>
			<div class="panel-heading">
				<h2 class="panel-title">Past 30 Days</h2>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Total </strong> <?=Price::format($td['total'])?>
					</div>
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Products</strong> <?=Price::format($td['productTotal'])?>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Tax</strong> <?=Price::format($td['taxTotal'])?>
					</div>
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Shipping</strong> <?=Price::format($td['shippingTotal'])?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-md-4">
		<div class="panel panel-sale">
			<?php $ytd = SalesReport::getYearToDate(); ?>
			<div class="panel-heading">
				<h2 class="panel-title"><?=t("Year to Date")?></h2>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Total </strong> <?=Price::format($ytd['total'])?>
					</div>
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Products</strong> <?=Price::format($ytd['productTotal'])?>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Tax</strong> <?=Price::format($ytd['taxTotal'])?>
					</div>
					<div class="col-xs-12 col-sm-6 stat">
						<strong>Shipping</strong> <?=Price::format($ytd['shippingTotal'])?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<hr>
<div id="sales-chart"></div>
<hr>
<script type="text/javascript">
$(function(){
	new Chartist.Line('#sales-chart', {
	    <?php
	    	$months = array(
				new DateTime(date('Y-M', strtotime('-5 months'))),
				new DateTime(date('Y-M', strtotime('-4 months'))),
				new DateTime(date('Y-M', strtotime('-3 months'))),
				new DateTime(date('Y-M', strtotime('-2 months'))),
				new DateTime(date('Y-M', strtotime('-1 month'))),
				new DateTime(date('Y-M'))
			);
	    ?>
	    
	    labels: [ <?php for($i=0;$i<6;$i++){
	    		if($i!=5){
	    			echo "'".$months[$i]->format("M")."',";
				} else {
					echo "'".$months[$i]->format("M")."'";
				}
	    	} ?> ],
		// Our series array that contains series objects or in this case series data arrays
	    series: [
	    	[
				<?php 
					for($i=0;$i<6;$i++){
						$report = SalesReport::getByMonth($months[$i]->format('Y-M'));
						if($i==5){
							echo "{meta: '".t('Total')."', value: ".$report['total']."}";
						} else {
							echo "{meta: '".t('Total')."', value: ".$report['total']."},";
						}
					}
				?>				
			],
			[
				<?php 
					for($i=0;$i<6;$i++){
						$report = SalesReport::getByMonth($months[$i]->format('Y-M'));
						if($i==5){
							echo "{meta: '".t('SubTotal')."', value: ".$report['productTotal']."}";
						} else {
							echo "{meta: '".t('SubTotal')."', value: ".$report['productTotal']."},";
						}
					}
				?>				
			],
			[
				<?php 
					for($i=0;$i<6;$i++){
						$report = SalesReport::getByMonth($months[$i]->format('Y-M'));
						if($i==5){
							echo "{meta: '".t('Shipping')."', value: ".$report['shippingTotal']."}";
						} else {
							echo "{meta: '".t('Shipping')."', value: ".$report['shippingTotal']."},";
						}
					}
				?>				
			],
			[
				<?php 
					for($i=0;$i<6;$i++){
						$report = SalesReport::getByMonth($months[$i]->format('Y-M'));
						if($i==5){
							echo "{meta: '".t('Tax')."', value: ".$report['taxTotal']."}";
						} else {
							echo "{meta: '".t('Tax')."', value: ".$report['taxTotal']."},";
						}
					}
				?>				
			]
	  	]
	},
	{
  		axisY: {
		    offset: 80,
		    labelInterpolationFnc: function(value) {
		      return "$" + value;
		    }
  		},
  		plugins: [
  			Chartist.plugins.tooltip()
  		]
	}
	);
	

});
</script>

<div class="well">
	<div class="row">
		<div class="col-xs-12 col-sm-5">
			<h3><?=t("View Orders by Date")?></h3>
		</div>
		<div class="col-xs-12 col-sm-7">
			<form action="<?=URL::to('/dashboard/store/reports/sales')?>" method="post" class="form form-inline order-report-form">
				<div class="form-group">
					<?php echo Core::make('helper/form/date_time')->date('dateFrom', $dateFrom); ?>
				</div>
				<div class="form-group">
					<?php echo Core::make('helper/form/date_time')->date('dateTo', $dateTo); ?>
				</div>
				<input type="submit" class="btn btn-primary">
			</form>
		</div>
	</div>
	<hr>
	<h4><?=t("Summary")?></h4>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?=t("SubTotal")?></th>
				<th><?=t("Tax")?></th>
				<th><?=t("Shipping")?></th>
				<th><?=t("Total")?></th>
				<th><?=t("Export")?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?=Price::format($ordersTotals['productTotal'])?></td>
				<td><?=Price::format($ordersTotals['taxTotal'])?></td>
				<td><?=Price::format($ordersTotals['shippingTotal'])?></td>
				<td><?=Price::format($ordersTotals['total'])?></td>
				<td><a href="" class="btn btn-default"><?=t('Export to CSV')?></a></td>
			</tr>
		</tbody>
	</table>
</div>
<table class="table table-striped">
	<thead>
		<tr>
			<th><?=t("Order #")?></th>
			<th><?=t("Date")?></th>
			<th><?=t("SubTotal")?></th>
			<th><?=t("Tax Total")?></th>
			<th><?=t("Shipping")?></th>
			<th><?=t("Total")?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($orders as $o){?>
		<tr>
			<td><a href="<?=URL::to('/dashboard/store/orders/order',$o->getOrderID())?>"><?=$o->getOrderID()?></a></td>
			<td><?=$o->getOrderDate()?></td>
			<td><?=Price::format($o->getSubTotal())?></td>
			<td><?=Price::format($o->getTaxTotal())?></td>
			<td><?=Price::format($o->getShippingTotal())?></td>
			<td><?=Price::format($o->getTotal())?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
