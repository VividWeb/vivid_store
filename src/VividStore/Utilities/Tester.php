<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use \Concrete\Core\Controller\Controller as RouteController;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product;
use \Concrete\Package\VividStore\Src\VividStore\Product\Image;

class Tester extends RouteController
{
    public function test(){
        
        $product = Product::getByID(1);
        $images = Image::getImagesForProduct($product);
        var_dump($images);
        
    }       
}
    