<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Core;
use View;
use Package;
use FilePermissions;
use TaskPermission;
use Database;
use File;
use Loader;
use PageType;
use GroupList;

use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductList as VividProductList;
use \Concrete\Package\VividStore\Src\VividStore\Groups\ProductGroup as VividProductGroup;
use \Concrete\Package\VividStore\Src\VividStore\Groups\GroupList as VividProductGroupList;
use \Concrete\Package\VividStore\Src\Attribute\Key\StoreProductKey;


defined('C5_EXECUTE') or die("Access Denied.");

class Products extends DashboardPageController
{

    public function view($gID=null){
        $products = new VividProductList();
        $products->setItemsPerPage(10);
        $products->setGroupID($gID);
        $products->activeOnly(false);

        if ($this->get('keywords')) {
            $products->setSearch($this->get('keywords'));
        }

        $paginator = $products->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('products',$paginator->getCurrentPageResults());  
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);
        $pkg = Package::getByHandle('vivid_store');
        $packagePath = $pkg->getRelativePath();
        $this->addHeaderItem(Core::make('helper/html')->css($packagePath.'/css/vividStoreDashboard.css'));
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/vividStoreFunctions.js'));
        $grouplist = VividProductGroupList::getGroupList();
        $this->set("grouplist",$grouplist);
        
    }
    public function success(){
        $this->set("success",t("Product Added"));
        $this->view();
    }
    
    public function updated()
    {
        $this->set("success",t("Product Updated"));
        $this->view();
    }
    public function removed(){
        $this->set("success",t("Product Removed"));
        $this->view();
    }
    public function add()
    {
        $this->loadFormAssets();       
        $this->set("actionType",t("Add")); 
        
        $grouplist = VividProductGroupList::getGroupList();            
        $this->set("grouplist",$grouplist);   
        $productgroups = array("0"=>t("None"));
        foreach($grouplist as $productgroup){
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }     
        $this->set("productgroups",$productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = array();

        foreach($usergroups as $ug) {
            if ( $ug->gName != 'Administrators') {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

        $this->set('pageTitle', t('Add Product'));
        $this->set('usergroups', $usergrouparray);
    }
    public function edit($pID)
    {
        $this->loadFormAssets();     
        $this->set("actionType",t("Update")); 
        
        //get the product
        $product = VividProduct::getByID($pID);
        $this->set('p',$product);
        $this->set("images",$product->getProductImages());
        $this->set("groups",$product->getProductOptionGroups()); 
        $this->set('optItems',$product->getProductOptionItems());
        
        //populate "Groups" select box options
        $grouplist = VividProductGroupList::getGroupList();         
        $productgroups = array("0"=>t("None"));
        foreach($grouplist as $productgroup){
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }     
        $this->set("productgroups",$productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = array();

        foreach($usergroups as $ug) {
            if ( $ug->gName != 'Administrators') {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

        $this->set('pageTitle', t('Edit Product'));
        $this->set('usergroups', $usergrouparray);
        
    }
    public function generate($pID,$templateID=null)
    {
        VividProduct::getByID($pID)->generatePage($templateID);
        $this->redirect('/dashboard/store/products/edit',$pID);
    }
    public function delete($pID)
    {
        $product = VividProduct::getByID($pID);
        $product->remove();
        $this->redirect('/dashboard/store/products/removed');
    }
    public function loadFormAssets()
    {
        $this->requireAsset('redactor');
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        
        $this->set('fp',FilePermissions::getGlobal());
        $this->set('tp', new TaskPermission());
        $this->set('al', Core::make('helper/concrete/asset_library'));
                
        $pkg = Package::getByHandle('vivid_store');
        $packagePath = $pkg->getRelativePath();
        $this->addHeaderItem('<style type="text/css">.redactor_editor{padding:20px}</style>');
        $this->addHeaderItem(Core::make('helper/html')->css($packagePath.'/css/vividStoreDashboard.css'));
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/vividStoreFunctions.js'));
        
        $attrList = StoreProductKey::getList();
		$this->set('attribs',$attrList);
        
        $pageType = PageType::getByHandle("store_product");
        $pageTemplates = $pageType->getPageTypePageTemplateObjects();
        $templates = array();
        foreach($pageTemplates as $pt){
            $templates[$pt->getPageTemplateID()] = $pt->getPageTemplateName();
        }
        $this->set('pageTemplates',$templates);
    }
    public function save()
    {
        $data = $this->post();
        if($data['pID']){
            $this->edit($data['pID']);
        } else{
            $this->add();
        }
        if ($this->isPost()) {
            $errors = $this->validate($data);
            $this->error = null; //clear errors
            $this->error = $errors;
            if (!$errors->has()) {
                
                $product = VividProduct::save($data); 
        		$aks = StoreProductKey::getList();
        		foreach($aks as $uak) {
        			$uak->saveAttributeForm($product);				
        		}
                if($data['pID']){
                    $this->redirect('/dashboard/store/products/', 'updated');
                } else {
                    $this->redirect('/dashboard/store/products/', 'success');
                }       
            }//if no errors
        }//if post
    }
    public function validate($args)
    {
        $e = Loader::helper('validation/error');
        
        if($args['pName']==""){
            $e->add(t('You must have a Product Name'));
        }
        if(strlen($args['pName']) > 255){
            $e->add(t('Keep the Product name under 255 Characters'));
        }
        if(!is_numeric($args['pPrice'])){
            $e->add(t('The Price must be set, and numeric'));
        }
        if(!is_numeric($args['pQty'])){
            $e->add(t('The Quantity must be set, and numeric'));
        }
        if(!is_numeric($args['pWidth'])){
            $e->add(t('The Product Width must be a number'));
        }
        if(!is_numeric($args['pHeight'])){
            $e->add(t('The Product Height must be a number'));
        }
        if(!is_numeric($args['pLength'])){
            $e->add(t('The Product Length must be a number'));
        }
        if(!is_numeric($args['pWeight'])){
            $e->add(t('The Product Weight must be a number'));
        }
        
        return $e;
        
    }
    
    // GROUPS PAGE
    public function groups()
    {
        $grouplist = VividProductGroupList::getGroupList();            
        $this->set("grouplist",$grouplist);
        $pkg = Package::getByHandle('vivid_store');
        $packagePath = $pkg->getRelativePath();
        $this->addHeaderItem(Core::make('helper/html')->css($packagePath.'/css/vividStoreDashboard.css'));
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/vividStoreFunctions.js'));
    }
    public function groupadded()
    {
        $this->set('success',"Group Successfully Added!");
        $this->groups();
    }
    public function addgroup()
    {
        $this->groups();
        $data = $this->post();
        $this->error = null; //clear errors
        $errors = $this->validateGroup($data);
        $this->error = $errors;
        if (!$errors->has()) {
            VividProductGroup::add($data);
            $this->redirect('/dashboard/store/products/', 'groupadded');
        }
    }
    public function editgroup($gID)
    {
        $data = $this->post();
        VividProductGroup::getByID($gID)->update($data);
    }
    public function validateGroup($args)
    {
        $e = Loader::helper('validation/error');
        
        if($args['groupName']==""){
            $e->add(t('You did not enter anything for the Group Name'));
        }
        if(strlen($args['groupName']) > 100){
            $e->add(t('Keep the Group Name under 100 Characters'));
        }
        return $e;
    }
    public function deletegroup($gID)
    {
        VividProductGroup::getByID($gID)->remove();
    }
}
