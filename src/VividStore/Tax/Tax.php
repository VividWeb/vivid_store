<?php
namespace Concrete\Package\VividStore\Src\VividStore\Tax;

use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price as StorePrice;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use Database;

class Tax
{
    public static function getTaxRates()
    {
        $em = Database::get()->getEntityManager();
        $taxRates = $em->createQuery('select tr from \Concrete\Package\VividStore\Src\VividStore\Tax\TaxRate tr')->getResult();
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
                    if ($taxAmount > 0) {
                        $tax = true;
                    } else { $tax = false; }
                    if ($format == true) {
                        $taxAmount = StorePrice::format($taxAmount);
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
        $product = StoreProduct::getByID($cartItem['product']['pID']);
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
