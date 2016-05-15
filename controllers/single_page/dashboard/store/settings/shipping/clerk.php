<?php
namespace Concrete\Package\VividStore\Controller\SinglePage\Dashboard\Store\Settings\Shipping;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Core;
use Config;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\Clerk\ClerkPackage as StoreClerkPackage;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Calculator as StoreCalculator;

class clerk extends DashboardPageController
{
    public function view()
    {
        $packages = StoreClerkPackage::getPackages();
        $this->set('packages', $packages);
        $this->set('sizeUnit', Config::get('vividstore.sizeUnit'));
        $this->set('weightUnit', Config::get('vividstore.weightUnit'));
        $this->set('calculator', new StoreCalculator());
    }
    public function add()
    {
        $this->set('task', t("Add"));
        $this->set('sizeUnit', Config::get('vividstore.sizeUnit'));
        $this->set('weightUnit', Config::get('vividstore.weightUnit'));
    }
    public function edit($id)
    {
        $package = StoreClerkPackage::getByID($id);
        $this->set('reference', $package->getReference());
        $this->set('outerWidth', StoreCalculator::convertFromMM($package->getOuterWidth()));
        $this->set('outerLength', StoreCalculator::convertFromMM($package->getOuterLength()));
        $this->set('outerDepth', StoreCalculator::convertFromMM($package->getOuterDepth()));
        $this->set('innerDepth', StoreCalculator::convertFromMM($package->getInnerDepth()));
        $this->set('innerWidth', StoreCalculator::convertFromMM($package->getInnerWidth()));
        $this->set('innerLength', StoreCalculator::convertFromMM($package->getInnerLength()));
        $this->set('maxWeight', StoreCalculator::convertFromGrams($package->getMaxWeight()));
        $this->set('emptyWeight', StoreCalculator::convertFromGrams($package->getEmptyWeight()));
        $this->set('sizeUnit', Config::get('vividstore.sizeUnit'));
        $this->set('weightUnit', Config::get('vividstore.weightUnit'));
        $this->set('id', $id);
        $this->set('task', t("Update"));
    }
    public function save()
    {
        $errors = $this->validate($this->post());
        $this->error = null; //clear errors
        $this->error = $errors;
        if (!$errors->has()) {
            if ($this->post('id') > 0) {
                StoreClerkPackage::getByID($this->post('id'))->update($this->post());
                $this->redirect('/dashboard/store/settings/shipping/clerk/updated');
            } else {
                StoreClerkPackage::add($this->post());
                $this->redirect('/dashboard/store/settings/shipping/clerk/success');
            }
        }
        if ($this->post('id') > 0) {
            $this->add();
        } else {
            $this->edit($this->post('id'));
        }
    }
    public function delete($id)
    {
        StoreClerkPackage::getByID($id)->delete();
        $this->redirect('/dashboard/store/settings/shipping/clerk/removed');
    }
    public function success()
    {
        $this->view();
        $this->set('success', t("Package Added"));
    }
    public function updated()
    {
        $this->view();
        $this->set('success', t("Package Updated"));
    }
    public function removed()
    {
        $this->view();
        $this->set('success', t("Package Removed"));
    }
    public function validate($data)
    {
        $e = Core::make('helper/validation/error');
        $numbers = new \Punic\Number;
        if (!$numbers->isInteger($data['outerWidth'])) {
            $e->add(t('Outer Width must be a whole number'));
        }
        if (!$numbers->isInteger($data['outerLength'])) {
            $e->add(t('Outer Length must be a whole number'));
        }
        if (!$numbers->isInteger($data['outerDepth'])) {
            $e->add(t('Outer Depth must be a whole number'));
        }
        if (!$numbers->isInteger($data['innerWidth'])) {
            $e->add(t('Inner Width must be a whole number'));
        }
        if (!$numbers->isInteger($data['innerLength'])) {
            $e->add(t('Inner Length must be a whole number'));
        }
        if (!$numbers->isInteger($data['innerDepth'])) {
            $e->add(t('Inner Depth must be a whole number'));
        }
        if (!$numbers->isInteger($data['maxWeight'])) {
            $e->add(t('Max Weight must be a whole number'));
        }
        if (!$numbers->isInteger($data['emptyWeight'])) {
            $e->add(t('Empty Weight must be a whole number'));
        }
        return $e;
    }
}
