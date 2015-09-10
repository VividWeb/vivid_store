<?php
namespace Concrete\Package\VividStore\Block\VividProduct;

use \Concrete\Core\Block\BlockController;
use Package;
use Core;
use View;
use Page;
use URL;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as VividProduct;

defined('C5_EXECUTE') or die("Access Denied.");
class Controller extends BlockController
{
    protected $btTable = 'btVividStoreProduct';
    protected $btInterfaceWidth = "450";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "400";
    protected $btDefaultSet = 'vivid_store';

    public function getBlockTypeDescription()
    {
        return t("Add a Product to the Page");
    }

    public function getBlockTypeName()
    {
        return t("Product");
    }
    public function view()
    {
            
        if($this->productLocation == 'page'){
            $cID = Page::getCurrentPage()->getCollectionID();
            $p = VividProduct::getByCollectionID($cID);
        } else {
            $p = VividProduct::getByID($this->pID);
        }
        $this->set('p',$p);
    }
    public function registerViewAssets()
    {
        
        $pkg = Package::getByHandle('vivid_store');
        $packagePath = $pkg->getRelativePath();
        $this->addHeaderItem("
            <script type=\"text/javascript\">
                var PRODUCTMODAL = '".View::url('/productmodal')."';
                var CARTURL = '".View::url('/cart')."';
                var CHECKOUTURL = '".View::url('/checkout')."';
                var QTYMESSAGE = '".t('Quantity must be greater than zero')."';
            </script>
        ");
        $this->requireAsset('javascript', 'vivid-store');
        $this->requireAsset('css', 'vivid-store');
        $this->requireAsset('core/lightbox');
    }
    public function save($args)
    {
        $args['showProductName'] = isset($args['showProductName']) ? 1 : 0;
        $args['showProductDescription'] = isset($args['showProductDescription']) ? 1 : 0;
        $args['showProductDetails'] = isset($args['showProductDetails']) ? 1 : 0;
        $args['showProductPrice'] = isset($args['showProductPrice']) ? 1 : 0;
        $args['showWeight'] = isset($args['showWeight']) ? 1 : 0;
        $args['showImage'] = isset($args['showImage']) ? 1 : 0;
        $args['showCartButton'] = isset($args['showCartButton']) ? 1 : 0;
        $args['showIsFeatured'] = isset($args['showIsFeatured']) ? 1 : 0;
        $args['showGroups'] = isset($args['showGroups']) ? 1 : 0;
        $args['showDimensions'] = isset($args['showDimensions']) ? 1 : 0;
        if($args['productLocation']=='search'){
            if(!is_numeric($args['pID']) || $args['pID']<1){
                $args['productLocation'] = "page";
            }
        }
        parent::save($args);
    }
}
