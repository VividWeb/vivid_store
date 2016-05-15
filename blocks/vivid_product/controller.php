<?php
namespace Concrete\Package\VividStore\Block\VividProduct;

use \Concrete\Core\Block\BlockController;
use Core;
use View;
use Page;
use \Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductVariation\ProductVariation as StoreProductVariation;

defined('C5_EXECUTE') or die("Access Denied.");
class controller extends BlockController
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
        if ($this->productLocation == 'page') {
            $cID = Page::getCurrentPage()->getCollectionID();
            $product = StoreProduct::getByCollectionID($cID);
        } else {
            $product = StoreProduct::getByID($this->pID);
        }

        if ($product) {
            if ($product->hasVariations()) {
                $variations = StoreProductVariation::getVariationsForProduct($product);

                $variationLookup = array();

                if (!empty($variations)) {
                    foreach ($variations as $variation) {
                        // returned pre-sorted
                        $ids = $variation->getOptionItemIDs();
                        $variationLookup[implode('_', $ids)] = $variation;
                    }
                }

                $product->setInitialVariation();
                $this->set('variationLookup', $variationLookup);
            }

            $this->set('product', $product);
            $this->set('optionGroups', $product->getProductOptionGroups());
            $this->set('optionItems', $product->getProductOptionItems(true));
        }
        $js = \Concrete\Package\VividStore\Controller::returnHeaderJS();
        $this->requireAsset('javascript', 'jquery');
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'vivid-store');
        $this->requireAsset('css', 'vivid-store');
    }
    public function registerViewAssets($outputContent = '')
    {
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
        if ($args['productLocation']=='search') {
            if (!is_numeric($args['pID']) || $args['pID']<1) {
                $args['productLocation'] = "page";
            }
        }
        parent::save($args);
    }
}
