<?php
namespace Concrete\Package\VividStore\Src\VividStore\Tax;

use \Concrete\Package\VividStore\Src\VividStore\Tax\TaxRate;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use Database;

class Tax
{
    public static function getTaxRates()
    {
        $em = Database::get()->getEntityManager();
        $taxRates = $em->createQuery('select u from \Concrete\Package\VividStore\Src\VividStore\Tax\TaxRate u')->getResult();
        return $taxRates;
    }
    public static function getTaxes($format=false)
    {
        $taxRates = self::getTaxRates();
        $taxes = array();
        if(count($taxRates)>0){
            foreach ($taxRates as $taxRate) {
                $taxAmount = $taxRate->calculate();
                if(intval($taxAmount) > 0){
                    $tax = true;
                }
                if($format==true){
                    $taxAmount = Price::format($taxAmount);
                }
                $taxes[] = array(
                    'name' => $taxRate->getTaxLabel(),
                    'calculation' => $taxRate->getTaxIncluded(),
                    'taxamount' => $taxAmount,
                    'based' => $taxRate->getTaxBasedOn(),
                    'taxed' => $tax
                );
            }
        }
        return $taxes;
    }   
}
