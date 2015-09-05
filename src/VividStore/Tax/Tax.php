<?php
namespace Concrete\Package\VividStore\Src\VividStore\Tax;

use \Concrete\Package\VividStore\Src\VividStore\Tax\TaxRate;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;
use Database;

class Tax
{
    public static function getTaxRates()
    {
        $em = Database::get()->getEntityManager();
        $taxRates = $em->createQuery('select u from \Concrete\Package\VividStore\Src\VividStore\Tax\TaxRate u')->getResult();
        return $taxRates;
    }

    public static function getTaxes($format = false)
    {
        $taxRates = self::getTaxRates();
        $taxes = array();
        if (count($taxRates) > 0) {
            foreach ($taxRates as $taxRate) {
                if($taxRate->isTaxable()){
                    $taxAmount = $taxRate->calculate();
                    if (intval($taxAmount) > 0) {
                        $tax = true;
                    }
                    if ($format == true) {
                        $taxAmount = Price::format($taxAmount);
                    }
                    $taxes[] = array(
                        'name' => $taxRate->getTaxLabel(),
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn(),
                        'taxed' => $tax
                    );
                }
            }
        }
        return $taxes;
    }

    public function getTaxForProduct($cartItem)
    {
        $product = VividProduct::getByID($productID);
        $qty = $cartItem['product']['qty'];
        $taxRates = self::getTaxRates();
        $taxes = array();
        if (count($taxRates) > 0) {
            foreach ($taxRates as $taxRate) {
                if($taxRate->isTaxable()){
                    $taxAmount = $taxRate->calculateProduct($product,$qty);
                    $taxes[] = array(
                        'name' => $taxRate->getTaxLabel(),
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn()
                    );
                }
            }
        }
        return $taxes;
        
    }
}