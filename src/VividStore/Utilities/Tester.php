<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use \Concrete\Core\Controller\Controller as RouteController;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product;


class Tester extends RouteController
{
    public function test(){
        
        $product = Product::getByID(1);
        $images = \Jimmy::getImagesForProduct($product);
        var_dump($images);
        
    }       
}
    