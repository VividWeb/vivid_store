<?php

namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Settings;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Loader;
use Core;
use Package;

use \Concrete\Package\VividStore\Src\VividStore\Tax\Tax as StoreTax;
use \Concrete\Package\VividStore\Src\VividStore\Tax\TaxRate;

defined('C5_EXECUTE') or die("Access Denied.");

class Tax extends DashboardPageController
{
    
    public function view()
    {
        $this->set("taxRates",StoreTax::getTaxRates());
    }
    public function add()
    {
        $this->set('pageTitle',t("Add Tax Rate"));
        $this->set("task",t("Add"));
        $this->set("taxRate",new TaxRate()); //shuts up errors when adding
        $this->loadFormAssets();
    }
    public function edit($trID)
    {
        $this->set('pageTitle',t("Edit Tax Rate"));
        $this->set("task",t("Update"));
        $this->set("taxRate",TaxRate::getByID($trID));
        $this->loadFormAssets();
    }
    public function delete($trID)
    {
        TaxRate::getByID($trID)->delete();
        $this->redirect('/dashboard/store/settings/tax/removed');
    }
    public function loadFormAssets()
    {
        $pkg = Package::getByHandle('vivid_store');
        $packagePath = $pkg->getRelativePath();
        $this->set("countries",Core::make('helper/lists/countries')->getCountries());
        $this->set("states",Core::make('helper/lists/states_provinces')->getStates());
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/vividStoreFunctions.js'));
    }
    public function success()
    {
        $this->view();
        $this->set("message",t("Successfully added a new Tax Rate"));
    }
    public function updated()
    {
        $this->view();
        $this->set("message",t("Successfully updated"));
    }
    public function removed()
    {
        $this->view();
        $this->set("message",t("Successfully removed"));
    }
    public function add_rate()
    {
        $data = $this->post();
        $errors = $this->validate($data);
        $this->error = null; //clear errors
        $this->error = $errors;
        if (!$errors->has()) {
            if($this->post('taxRateID')){
                //update
                TaxRate::add($data);
                $this->redirect('/dashboard/store/settings/tax/updated');
            } else {
                //first we send the data to the shipping method type.
                TaxRate::add($data);
                $this->redirect('/dashboard/store/settings/tax/success');
            }
        } else {
            if($this->post('taxRateID')){
                $this->edit($this->post('taxRateID'));
            } else {
                //first we send the data to the shipping method type.
                $this->add();
            }
        }
                
        
    }
    public function validate($data)
    {
        $this->error = null;
        $e = Loader::helper('validation/error');
        
        if($data['taxLabel']==""){
            $e->add(t("You need a label for this Tax Rate"));
        }
        if($data['taxRate'] != ""){
            if(!is_numeric($data['taxRate'])){
                $e->add(t("Tax Rate must be a number"));
            }
        } else {
            $e->add(t("You need to enter a tax rate"));
        }
        
        return $e;
        
    }
}
