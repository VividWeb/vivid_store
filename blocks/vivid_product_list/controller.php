<?php
namespace Concrete\Package\VividStore\Block\VividProductList;
use \Concrete\Core\Block\BlockController;
use Package;
use Core;
use View;
use Page;
use \Concrete\Package\VividStore\Src\VividStore\Product\ProductList as VividProductList;
use \Concrete\Package\VividStore\Src\VividStore\Groups\ProductGroup as VividProductGroup;
use \Concrete\Package\VividStore\Src\VividStore\Groups\GroupList as VividProductGroupList;

defined('C5_EXECUTE') or die("Access Denied.");
class Controller extends BlockController
{
    protected $btTable = 'btVividStoreProductList';
    protected $btInterfaceWidth = "450";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "400";
    protected $btDefaultSet = 'vivid_store';

    public function getBlockTypeDescription()
    {
        return t("Add a Product List for Vivid Store");
    }

    public function getBlockTypeName()
    {
        return t("Product List");
    }
    public function add()
    {
        $this->getGroupList();
    }
    public function edit()
    {
        $this->getGroupList();
    }
    public function getGroupList()
    {
        $grouplist = VividProductGroupList::getGroupList();            
        $this->set("grouplist",$grouplist);
    }
    public function view()
    {
        
        $products = new VividProductList();

        if ($this->filter == 'page' || $this->filter == 'page_children') {
            $page = Page::getCurrentPage();
            $products->setCID($page->getCollectionID());

            if ($this->filter == 'page_children') {
                $products->setCIDs($page->getCollectionChildrenArray());
            }
        }

        $products->setItemsPerPage($this->maxProducts);
        $products->setGroupID($this->gID);
        $products->setFeatureType($this->showFeatured);
        $paginator = $products->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('products',$paginator->getCurrentPageResults());  
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);

        //load some helpers
        $this->set('ih',Core::make('helper/image'));
        $this->set('th',Core::make('helper/text'));
        
        $this->requireAsset("css","font-awesome");
                
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
        $this->addFooterItem(Core::make('helper/html')->javascript($packagePath.'/js/vivid-store.js','vivid-store'));
        $this->addHeaderItem(Core::make('helper/html')->css($packagePath.'/css/vivid-store.css','vivid-store'));    
    }
    public function save($args)
    {
        $args['showQuickViewLink'] = isset($args['showQuickViewLink']) ? 1 : 0;
        $args['showPageLink'] = isset($args['showPageLink']) ? 1 : 0;
        $args['showAddToCart'] = isset($args['showAddToCart']) ? 1 : 0;
        $args['showLink'] = isset($args['showLink']) ? 1 : 0;
        $args['showButton'] = isset($args['showButton']) ? 1 : 0;
        $args['truncateEnabled'] = isset($args['truncateEnabled']) ? 1 : 0;
        $args['showPagination'] = isset($args['showPagination']) ? 1 : 0;
        parent::save($args);
    }
    public function validate($args)
    {
        $e = Core::make("helper/validation/error"); 
        $nh = Core::make("helper/number");
        if($args['maxProducts'] < 1){
            $e->add(t('Max Products must be at least 1'));
        }
        if(!$nh->isInteger($args['maxProducts'])){
            $e->add(t('Max Product must be a whole number'));
        }
        return $e;
    }
}