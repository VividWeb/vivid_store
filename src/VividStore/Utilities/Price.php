<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use Config;

class Price
{
    public static function format($price)
    {
        $price = floatval($price);
        $symbol = Config::get('vividstore.symbol');
        $wholeSep = Config::get('vividstore.whole');
        $thousandSep = Config::get('vividstore.thousand');
        $price = $symbol . number_format($price, 2, $wholeSep, $thousandSep);
        return $price;
    }
    public static function formatFloat($price)
    {
        $price = floatval($price);
        $price = number_format($price, 2, ".", "");
        return $price;
    }
    public function getFloat($price)
    {
        $symbol = Config::get('vividstore.symbol');
        $wholeSep = Config::get('vividstore.whole');
        $thousandSep = Config::get('vividstore.thousand');

        $price = str_replace($symbol, "", $price);
        $price = str_replace($thousandSep, "", $price); //no commas, or spaces or whatevz
        $price = str_replace($wholeSep, ".", $price); // replace whole separator with '.' 

        return $price;
    }
}
