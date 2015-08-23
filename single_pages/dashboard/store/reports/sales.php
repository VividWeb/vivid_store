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
<h2>Sales Today</h2>
<?php 
	$ts = SalesReport::getTodaysSales();
?>
<p>
	<strong>Total: </strong> <?=Price::format($ts['total'])?><br>
	<strong>Products: </strong> <?=Price::format($ts['productTotal'])?><br>
	<strong>Tax: </strong> <?=Price::format($ts['taxTotal'])?><br>
	<strong>Shipping: </strong> <?=Price::format($ts['shippingTotal'])?>
</p>

<h2>Sales In the past 30 days</h2>
<?php $td = SalesReport::getThirtyDays(); ?>
<p>
	<strong>Total: </strong> <?=Price::format($td['total'])?><br>
	<strong>Products: </strong> <?=Price::format($td['productTotal'])?><br>
	<strong>Tax: </strong> <?=Price::format($td['taxTotal'])?><br>
	<strong>Shipping: </strong> <?=Price::format($td['shippingTotal'])?>
</p>

<h2>Year to date</h2>
<?php $ytd = SalesReport::getYearToDate(); ?>
<p>
	<strong>Total: </strong> <?=Price::format($ytd['total'])?><br>
	<strong>Products: </strong> <?=Price::format($ytd['productTotal'])?><br>
	<strong>Tax: </strong> <?=Price::format($ytd['taxTotal'])?><br>
	<strong>Shipping: </strong> <?=Price::format($ytd['shippingTotal'])?>
</p>
