<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Reports;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Core;
use Package;
use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderStatus\OrderStatus;

use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderList;
use \Concrete\Package\VividStore\Src\VividStore\Orders\Order as VividOrder;
use \Concrete\Package\VividStore\Src\VividStore\Report\ProductReport;
use \Concrete\Core\Search\Pagination\Pagination;

defined('C5_EXECUTE') or die("Access Denied.");
class Products extends DashboardPageController
{

    public function view()
    {
    	$dateFrom = $this->post('dateFrom');
		$dateTo = $this->post('dateTo');
		
		if(!$dateFrom){
			$dateFrom = OrderList::getDateOfFirstOrder();
		}		
		if(!$dateTo){
			$dateTo = date('Y-m-d');
		}
		$pr = new ProductReport($dateFrom,$dateTo);
		$orderBy = $this->post('orderBy');
		if(!$orderBy){
			$orderBy = 'quantity';
		}
		if($orderBy=='quantity'){
			$pr->sortByPopularity();	
		} else {
			$pr->sortByTotal();
		}
		
		//$products = $pr->getProducts();
		
		$this->set('dateFrom',$dateFrom);
		$this->set('dateTo',$dateTo);
		
		$pr->setItemsPerPage(10);

        $paginator = $pr->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('products',$paginator->getCurrentPageResults());  
		$this->set('pagination',$pagination);
        $this->set('paginator', $paginator);
	}
    
}
