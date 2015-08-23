<?php
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use \Concrete\Package\VividStore\Src\VividStore\Report\SalesReport;
?>
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
