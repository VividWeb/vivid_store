<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Report;

use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderList;

defined('C5_EXECUTE') or die(_("Access Denied."));

class SalesReport 
{
	
	public function __construct()
	{
		$ol = new OrderList();
		$ol->setFromDate();
		$ol->setToDate();
		$ol->setLimit();
		return $ol;
	}
	
	
}
