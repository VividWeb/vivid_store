<?php
namespace Concrete\Package\VividStore\Src\VividStore\Product\ProductOption;

use Database;

use \Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionGroup as StoreProductOptionGroup;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

class ProductOption
{
    public static function addProductOptions($data,$product)
    {
        $count = count($data['pogSort']);
        $ii=0;//set counter for items
        if($count>0){
            for($i=0;$i<count($data['pogSort']);$i++){
                $optionGroup = StoreProductOptionGroup::add($product,$data['pogName'][$i],$data['pogSort'][$i]);
                $pogID = $optionGroup->getID();         
                //add option items
                $itemsInGroup = count($data['optGroup'.$i]);
                if($itemsInGroup>0){
                    for($gi=0;$gi<$itemsInGroup;$gi++,$ii++){
                        StoreProductOptionItem::add($product,$pogID,$data['poiName'][$ii],$data['poiSort'][$ii]);
                    }
                }
            }
        }
    }
    public static function getProductOptions($product)
    {
        //TODO
    }
}
