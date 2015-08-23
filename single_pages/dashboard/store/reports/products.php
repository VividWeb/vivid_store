<?php
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use \Concrete\Package\VividStore\Src\VividStore\Report\SalesReport;
?>
<div id="sales-chart">
	
</div>
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
							echo "'".$report['total']."'";
						} else {
							echo "'".$report['total']."',";
						}
					}
				?>				
			],
			[
				<?php 
					for($i=0;$i<6;$i++){
						$report = SalesReport::getByMonth($months[$i]->format('Y-M'));
						if($i==5){
							echo "'".$report['productTotal']."'";
						} else {
							echo "'".$report['productTotal']."',";
						}
					}
				?>				
			],
			[
				<?php 
					for($i=0;$i<6;$i++){
						$report = SalesReport::getByMonth($months[$i]->format('Y-M'));
						if($i==5){
							echo "'".$report['shippingTotal']."'";
						} else {
							echo "'".$report['shippingTotal']."',";
						}
					}
				?>				
			],
			[
				<?php 
					for($i=0;$i<6;$i++){
						$report = SalesReport::getByMonth($months[$i]->format('Y-M'));
						if($i==5){
							echo "'".$report['taxTotal']."'";
						} else {
							echo "'".$report['taxTotal']."',";
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
  		}
	}
	);
	

});
</script>
<div class="row">
	<div class="col-xs-6 col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">Sales Today</h2>
			</div>
			<div class="panel-body">
				<?php 
				$ts = SalesReport::getTodaysSales();
				?>
				<p>
				<strong>Total: </strong> <?=Price::format($ts['total'])?><br>
				<strong>Products: </strong> <?=Price::format($ts['productTotal'])?><br>
				<strong>Tax: </strong> <?=Price::format($ts['taxTotal'])?><br>
				<strong>Shipping: </strong> <?=Price::format($ts['shippingTotal'])?>
				</p>
			</div>
		</div>
	</div>
	<div class="col-xs-6 col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">Past 30 Days</h2>
			</div>
			<div class="panel-body">
				<?php 
				$td = SalesReport::getThirtyDays();
				?>
				<p>
				<strong>Total: </strong> <?=Price::format($td['total'])?><br>
				<strong>Products: </strong> <?=Price::format($td['productTotal'])?><br>
				<strong>Tax: </strong> <?=Price::format($td['taxTotal'])?><br>
				<strong>Shipping: </strong> <?=Price::format($td['shippingTotal'])?>
				</p>
			</div>
		</div>
	</div>
	<div class="col-xs-6 col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">Year to Date</h2>
			</div>
			<div class="panel-body">
				<?php 
				$ytd = SalesReport::getYearToDate();
				?>
				<p>
				<strong>Total: </strong> <?=Price::format($ytd['total'])?><br>
				<strong>Products: </strong> <?=Price::format($ytd['productTotal'])?><br>
				<strong>Tax: </strong> <?=Price::format($ytd['taxTotal'])?><br>
				<strong>Shipping: </strong> <?=Price::format($ytd['shippingTotal'])?>
				</p>
			</div>
		</div>
	</div>

<h2>Custom Date Range</h2>
<form action="<?=URL::to('/dashboard/store/reports/sales')?>" method="post" class="form form-inline">
	<div class="form-group">
		<?php echo Core::make('helper/form/date_time')->date('dateFrom', $dateFrom); ?>
	</div>
	<div class="form-group">
		<?php echo Core::make('helper/form/date_time')->date('dateTo', $dateTo); ?>
	</div>
	<input type="submit" class="btn btn-primary">
</form>
<p>
	<strong>Product Total: </strong><?=Price::format($ordersTotals['productTotal'])?> | 
	<strong>Tax Total:</strong> <?=Price::format($ordersTotals['taxTotal'])?> |
	<strong>Shipping Total:</strong> <?=Price::format($ordersTotals['shippingTotal'])?> |
	<strong>Total:</strong> <?=Price::format($ordersTotals['total'])?>
	<a href="" class="btn btn-default"><?=t('Export to CSV')?></a>
</p>

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
			<td><?=$o->getSubTotal()?></td>
			<td><?=$o->getTaxTotal()?></td>
			<td><?=$o->getShippingTotal()?></td>
			<td><?=$o->getTotal()?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
