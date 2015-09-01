<?php
namespace Concrete\Package\VividStore\Src\VividStore\Tax;

use \Concrete\Package\VividStore\Src\VividStore\Tax\TaxRate;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Price;
use \Concrete\Package\VividStore\Src\VividStore\Cart\Cart;
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
                $taxAmount = $taxRate->calculate();
                if (intval($taxAmount) > 0) {
                    $tax = true;
                }
                if ($format == true) {
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

    public function getTaxProduct($pID)
    {
        $product = VividProduct::getByID($productID);
        foreach (self::getTaxes() as $tax) {


            $cart = Cart::getCart();
            if ($cart) {
                //foreach TaxRate, see if the product is taxable
                foreach (self::getTaxes() as $tax) {

                }
                $taxCalc = Config::get('vividstore.calculation');
                if ($taxCalc == 'extract') {
                    $taxrate = 10 / (Config::get('vividstore.taxrate') + 100);
                } else {
                    $taxrate = Config::get('vividstore.taxrate') / 100;
                }
                foreach ($cart as $cartItem) {
                    if ($cartItem['product']['pID'] == $productID) {
                        $product = VividProduct::getByID($productID);
                    }
                    if (is_object($product)) {
                        if ($product->isTaxable()) {
                            //the product is "Taxable", but is the customer?
                            if (self::isCustomerTaxable()) {
                                $tax = $taxrate * $product->getProductPrice();
                                return $tax;
                            }//if customer is taxable
                        }//if product is taxable
                    }//if obj
                }//foreach
            }//if cart
            return 0;
        }

    }
}