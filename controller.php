<?php      

namespace Concrete\Package\VividStore;
use Package;
use BlockType;
use BlockTypeSet;
use SinglePage;
use Core;
use Page;
use PageTemplate;
use PageType;
use Route;
use Group;
use View;
use Database;
use FileSet;
use Loader;
use Config;
use Concrete\Core\Database\Schema\Schema;
use \Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use \Concrete\Core\Attribute\Key\UserKey as UserAttributeKey;
use \Concrete\Core\Attribute\Type as AttributeType;
use AttributeSet;
use \Concrete\Package\VividStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as PaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Orders\OrderStatus\OrderStatus;
use \Concrete\Core\Utility\Service\Text;
use \Concrete\Core\Page\Type\PublishTarget\Type\AllType as PageTypePublishTargetAllType;
use \Concrete\Core\Page\Type\PublishTarget\Configuration\AllConfiguration as PageTypePublishTargetAllConfiguration;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\Installer;


defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends Package
{
    protected $pkgHandle = 'vivid_store';
    protected $appVersionRequired = '5.7.3';
    protected $pkgVersion = '2.2';

    public function getPackageDescription()
    {
        return t("Add a Store to your Site");
    }

    public function getPackageName()
    {
        return t("Vivid Store");
    }
    
    public function install()
    {
        $pkg = parent::install();
        
        //install our dashboard singlepages
        SinglePage::add('/dashboard/store/',$pkg);
        SinglePage::add('/dashboard/store/orders/',$pkg);
        SinglePage::add('/dashboard/store/products/',$pkg);
        SinglePage::add('/dashboard/store/products/attributes',$pkg);
        SinglePage::add('/dashboard/store/settings/',$pkg);
        
        //install our cart/checkout pages
        SinglePage::add('/cart/',$pkg);
        SinglePage::add('/checkout/',$pkg);
        SinglePage::add('/checkout/complete',$pkg);
        Page::getByPath('/cart/')->setAttribute('exclude_nav', 1);
        Page::getByPath('/checkout/')->setAttribute('exclude_nav', 1);
        Page::getByPath('/checkout/complete')->setAttribute('exclude_nav', 1);
        
        //install a default page to pushlish products under
        $productParentPage = Page::getByPath('/product-detail');
        if ($productParentPage->isError()) {
            $productParentPage = Page::getByID(1)->add(
                PageType::getByHandle('page'),
                array(
                    'cName' => t('Product Detail'),
                    'cHandle' => 'product-detail',
                    'pkgID' => $pkg->pkgID
                ),
                PageTemplate::getByHandle('full')
            );
        }
        Page::getByPath('/product-detail')->setAttribute('exclude_nav', 1);
        
        $this->installStoreProductPageType($pkg);

        Config::save('vividstore.productPublishTarget',$productParentPage->getCollectionID());

        //install our blocks
        BlockTypeSet::add("vivid_store","Store", $pkg);
        BlockType::installBlockTypeFromPackage('vivid_product_list', $pkg); 
        BlockType::installBlockTypeFromPackage('vivid_utility_links', $pkg);
        BlockType::installBlockTypeFromPackage('vivid_product', $pkg);
        
        //install some default blocks for page type.
        $pageType = PageType::getByHandle('store_product');
        $template = $pageType->getPageTypeDefaultPageTemplateObject();
        $pageObj = $pageType->getPageTypePageTemplateDefaultPageObject($template);
        
        $bt = BlockType::getByHandle('vivid_product');
        $blocks = $pageObj->getBlocks('Main');
        if(count($blocks)<1){
            $data = array(
                'productLocation'=>'page',
                'showProductName'=>1,
                'showProductDescription'=>1,
                'showProductDetails'=>1,
                'showProductPrice'=>1,
                'showImage'=>1,
                'showCartButton'=>1,
                'showGroups'=>1
            );
            $pageObj->addBlock($bt, 'Main', $data);
        }
        
        //set our default currency configs
        Config::save('vividstore.symbol','$');
        Config::save('vividstore.whole','.');
        Config::save('vividstore.thousand',',');

        //set defaults for shipping
        Config::save('vividstore.sizeUnit','in');
        Config::save('vividstore.weightUnit','l');
        
        //tax label
        $pkg->getconfig()->save('vividstore.taxName',t('Tax'));
        
        //user attributes for customers
        $uakc = AttributeKeyCategory::getByHandle('user');
        $uakc->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_MULTIPLE);
        
        //define attr group, and the different attribute types we'll use
        $custSet = $uakc->addSet('customer_info', t('Store Customer Info'), $pkg);
        $text = AttributeType::getByHandle('text');
        $address = AttributeType::getByHandle('address');

        //email
        $bFirstname = UserAttributeKey::getByHandle('email');
        if (!is_object($bFirstname)) {
            UserAttributeKey::add($text,
                array('akHandle' => 'email',
                    'akName' => t('Email'),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true,
                    'displayOrder' => '1',
                ), $pkg)->setAttributeSet($custSet);
        }

        //billing first name
        $bFirstname = UserAttributeKey::getByHandle('billing_first_name');
        if (!is_object($bFirstname)) {
            UserAttributeKey::add($text,
                array('akHandle' => 'billing_first_name',
                    'akName' => t('Billing First Name'),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true,
                    'displayOrder' => '1',
                ), $pkg)->setAttributeSet($custSet);
        }

        //billing last name
        $bLastname = UserAttributeKey::getByHandle('billing_last_name');
        if (!is_object($bLastname)) {
            UserAttributeKey::add($text,
                array('akHandle' => 'billing_last_name',
                    'akName' => t('Billing Last Name'),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true,
                    'displayOrder' => '2',
                ), $pkg)->setAttributeSet($custSet);
        }

        //billing address
        $bAddress = UserAttributeKey::getByHandle('billing_address');
        if (!is_object($bAddress)) {
            UserAttributeKey::add($address,
                array('akHandle' => 'billing_address',
                    'akName' => t('Billing Address'),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true,
                    'displayOrder' => '3',
                ), $pkg)->setAttributeSet($custSet);
        }

        //billing Phone
        $bPhone = UserAttributeKey::getByHandle('billing_phone');
        if (!is_object($bPhone)) {
            UserAttributeKey::add($text,
                array('akHandle' => 'billing_phone',
                    'akName' => t('Billing Phone'),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true,
                    'displayOrder' => '4',
                ), $pkg)->setAttributeSet($custSet);
        }

        //shipping first name
        $sFirstname = UserAttributeKey::getByHandle('shipping_first_name');
        if (!is_object($sFirstname)) {
            UserAttributeKey::add($text,
                array('akHandle' => 'shipping_first_name',
                    'akName' => t('Shipping First Name'),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true,
                    'displayOrder' => '1',
                ), $pkg)->setAttributeSet($custSet);
        }

        //shipping last name
        $bLastname = UserAttributeKey::getByHandle('shipping_last_name');
        if (!is_object($bLastname)) {
            UserAttributeKey::add($text,
                array('akHandle' => 'shipping_last_name',
                    'akName' => t('Shipping Last Name'),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true,
                    'displayOrder' => '2',
                ), $pkg)->setAttributeSet($custSet);
        }

        //shipping address
        $sAddress = UserAttributeKey::getByHandle('shipping_address');
        if (!is_object($sAddress)) {
            UserAttributeKey::add($address,
                array('akHandle' => 'shipping_address',
                    'akName' => t('Shipping Address'),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true,
                    'displayOrder' => '3',
                ), $pkg)->setAttributeSet($custSet);
        }

        //create user group    
        $group = Group::getByName('Store Customer');
        if (!$group || $group->getGroupID() < 1) {
            $group = Group::add('Store Customer', t('Registered Customer in your store'));
        }
        
        
        //create custom attribute category for orders
        $oakc = AttributeKeyCategory::getByHandle('store_order');
        if (!is_object($oakc)) {
            $oakc = AttributeKeyCategory::add('store_order', AttributeKeyCategory::ASET_ALLOW_SINGLE, $pkg);
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('text'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('textarea'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('number'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('address'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('boolean'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('date_time'));

            $orderCustSet = $oakc->addSet('order_customer', t('Store Customer Info'), $pkg);        
        }
        $email = StoreOrderKey::getByHandle('email');
        if (!is_object($email)) {
            StoreOrderKey::add($text, array(
                'akHandle' => 'email',
                'akName' => t('Email')
            ), $pkg)->setAttributeSet($orderCustSet);
        }
        $bFirstname = StoreOrderKey::getByHandle('billing_first_name');
        if (!is_object($bFirstname)) {
            StoreOrderKey::add($text, array(
                'akHandle' => 'billing_first_name',
                'akName' => t('Billing First Name')
            ), $pkg)->setAttributeSet($orderCustSet);
        }
        $bLastname = StoreOrderKey::getByHandle('billing_last_name');
        if (!is_object($bLastname)) {
            StoreOrderKey::add($text, array(
                'akHandle' => 'billing_last_name',
                'akName' => t('Billing Last Name')
            ), $pkg)->setAttributeSet($orderCustSet);
        }
        $bAddress = StoreOrderKey::getByHandle('billing_address');
        if (!is_object($bAddress)) {
            StoreOrderKey::add($address, array(
                'akHandle' => 'billing_address',
                'akName' => t('Billing Address')
            ), $pkg)->setAttributeSet($orderCustSet);
        }
        $bPhone = StoreOrderKey::getByHandle('billing_phone');
        if (!is_object($bPhone)) {
            StoreOrderKey::add($text, array(
                'akHandle' => 'billing_phone',
                'akName' => t('Billing Phone')
            ), $pkg)->setAttributeSet($orderCustSet);
        }
        $sFirstname = StoreOrderKey::getByHandle('shipping_first_name');
        if (!is_object($sFirstname)) {
            StoreOrderKey::add($text, array(
                'akHandle' => 'shipping_first_name',
                'akName' => t('Shipping First Name')
            ), $pkg)->setAttributeSet($orderCustSet);
        }
        $sLastname = StoreOrderKey::getByHandle('shipping_last_name');
        if (!is_object($sLastname)) {
            StoreOrderKey::add($text, array(
                'akHandle' => 'shipping_last_name',
                'akName' => t('Shipping Last Name')
            ), $pkg)->setAttributeSet($orderCustSet);
        }
        $sAddress = StoreOrderKey::getByHandle('shipping_address');
        if (!is_object($sAddress)) {
            StoreOrderKey::add($address, array(
                'akHandle' => 'shipping_address',
                'akName' => t('Shipping Address')
            ), $pkg)->setAttributeSet($orderCustSet);
        }
        
        //create custom attribute category for products
        $pakc = AttributeKeyCategory::getByHandle('store_product');
        if (!is_object($pakc)) {
            $pakc = AttributeKeyCategory::add('store_product', AttributeKeyCategory::ASET_ALLOW_SINGLE, $pkg);
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('text'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('textarea'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('number'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('address'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('boolean'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('date_time'));       
        }
        
        //install payment gateways 
        PaymentMethod::add('auth_net','Authorize .NET',$pkg);
        PaymentMethod::add('invoice','Invoice',$pkg,null,true);
        
        //create fileset to place digital downloads
        $fs = FileSet::getByName('Digital Downloads');
        if(!is_object($fs)){
            FileSet::add("Digital Downloads");
        }

        Installer::addOrderStatusesToDatabase($pkg);
    }

    public function upgrade()
    {

        $pkg = Package::getByHandle('vivid_store');

        Installer::renameDatabaseTables($pkg);
        Installer::refreshDatabase($pkg);


        /** Version 1.1 ***********************************************/
        /**************************************************************/
        /*
         * 1. Installs new payment method: Invoice
         * 
         */
        
        $invoicePM = PaymentMethod::getByHandle('invoice');
        if(!is_object($invoicePM)){
            PaymentMethod::add('invoice','Invoice',$pkg);
        }
        
        
        /** Version 2.0 ***********************************************/
        /**************************************************************/
        /*
         * 1. Installs new PageType: store_product
         * 2. Installs a parent page to publish products under
         * 3. Install Product block
         * 4. Set pagetype defaults
         * 5. Give default for measurement units
         * 6. Install a fileset for digital downloads
         * 7. Install product attributes
         * 
         */        
        
        /*
         * 1. Installs new PageType: store_product
         */
        $this->installStoreProductPageType($pkg);

        /*
         * 2. Installs a parent page to publish products under
         */
        
        //first check and make sure the config isn't set. 
        $publishTarget = Config::get('vividstore.productPublishTarget');
        if($publishTarget < 1 || empty($publishTarget)){
            //if not, install the proudct detail page if needed.    
            $productParentPage = Page::getByPath('/product-detail');
            if ($productParentPage->isError()) {
                $home = Page::getByID(HOME_CID);
                $pageType = PageType::getByHandle('page');
                $pageTemplate = PageTemplate::getByHandle('full');
                $productParentPage = $home->add(
                    $pageType,
                    array(
                        'cName' => t('Product Detail'),
                        'cHandle' => 'product-detail',
                        'pkgID' => $pkg->pkgID
                    ),
                    $pageTemplate
                );
                Page::getByPath('/product-detail')->setAttribute('exclude_nav', 1);
            }
            //set the config to publish under the new page.            
            Config::save('vividstore.productPublishTarget',$productParentPage->getCollectionID());
        }
        
        /*
         * 3. Install Product Block
         */
        $productBlock = BlockType::getByHandle("vivid_product");
        if(!is_object($productBlock)){
            BlockType::installBlockTypeFromPackage('vivid_product', $pkg);
        }
        
        /*
         * 3. Install Product PageType Defaults
         */
        $pageType = PageType::getByHandle('store_product');
        $template = $pageType->getPageTypeDefaultPageTemplateObject();
        $pageObj = $pageType->getPageTypePageTemplateDefaultPageObject($template);
        
        $bt = BlockType::getByHandle('vivid_product');
        $blocks = $pageObj->getBlocks('Main');
        if($blocks[0] && $blocks[0]->getBlockTypeHandle()=="content"){
            $blocks[0]->deleteBlock();
        }
        if(count($blocks)<1){
            $data = array(
                'productLocation'=>'page',
                'showProductName'=>1,
                'showProductDescription'=>1,
                'showProductDetails'=>1,
                'showProductPrice'=>1,
                'showImage'=>1,
                'showCartButton'=>1,
                'showGroups'=>1
            );
            $pageObj->addBlock($bt, 'Main', $data);
        }
        
        /*
         * 5. Measurement Units.
         */
        $sizeUnits = Config::get('vividstore.sizeUnit');
        if(empty($sizeUnits)){
            Config::save('vividstore.sizeUnit','in');
        }
        $weightUnits = Config::get('vividstore.weightUnit');
        if(empty($weightUnits)){
            Config::save('vividstore.weightUnit','lb');
        }
        
        /*
         * 6. Fileset for digital downloads
         */
        $fs = FileSet::getByName('Digital Downloads');
        if(!is_object($fs)){
            FileSet::add("Digital Downloads");
        }     
        
        /*
         * 7. Product Attributes page 
         */   
         
        $attrPage = Page::getByPath('/dashboard/store/products/attributes');
        if(!is_object($attrPage) || $attrPage->isError()){
            SinglePage::add('/dashboard/store/products/attributes',$pkg);
        }

        /*
        *  Add email order attribute
        */
        $text = AttributeType::getByHandle('text');

        $oakc = AttributeKeyCategory::getByHandle('store_order');
        $orderCustSet = AttributeSet::getByHandle('order_customer');

        $email = StoreOrderKey::getByHandle('email');
        if (!is_object($email)) {
            StoreOrderKey::add($text, array(
                'akHandle' => 'email',
                'akName' => t('Email')
            ), $pkg)->setAttributeSet($orderCustSet);
        }
        parent::upgrade();
        Installer::addOrderStatusesToDatabase($pkg);

        // convert legacy config items to current config storage
        // applies for version 2.1.1 and below
        $db = Database::get();
        $configitems = $db->GetAll("SELECT * FROM Config WHERE configGroup='vividstore'");

        if (!empty($configitems)) {
            foreach($configitems as $config) {
                Config::save('vividstore.' . $config['configItem'],  $config['configValue']);
            }

            $db->Execute("DELETE FROM Config WHERE configGroup='vividstore'");
        }

    }

    private function installStoreProductPageType($pkg){
        //install product detail page type
        $pageType = PageType::getByHandle('store_product');
        if(!is_object($pageType)){
            $template = PageTemplate::getByHandle('full');
            PageType::add(
                array(
                    'handle' => 'store_product',
                    'name' => 'Product Page',
                    'defaultTemplate' => $template,
                    'allowedTemplates' => 'C',
                    'templates' => array($template),
                    'ptLaunchInComposer' => 0,
                    'ptIsFrequentlyAdded' => 0,
                ),
                $pkg
            )->setConfiguredPageTypePublishTargetObject(new PageTypePublishTargetAllConfiguration(PageTypePublishTargetAllType::getByHandle('all')));
        }

    }

    public function registerRoutes()
    {        
        Route::register('/cart/getSubTotal', '\Concrete\Package\VividStore\Src\VividStore\Cart\CartTotal::getSubTotal');
        Route::register('/cart/getTaxTotal', '\Concrete\Package\VividStore\Src\VividStore\Cart\CartTotal::getTaxTotal');
        Route::register('/cart/getTotal', '\Concrete\Package\VividStore\Src\VividStore\Cart\CartTotal::getTotal');
        Route::register('/cart/getTotalItems', '\Concrete\Package\VividStore\Src\VividStore\Cart\CartTotal::getTotalItems');
        Route::register('/productmodal', '\Concrete\Package\VividStore\Src\VividStore\Product\ProductModal::getProductModal');
        Route::register('/checkout/getstates', '\Concrete\Package\VividStore\Src\VividStore\Utilities\States::getStateList');
        Route::register('/checkout/updater','\Concrete\Package\VividStore\Src\VividStore\Utilities\Checkout::updater');
        Route::register('/productfinder','\Concrete\Package\VividStore\Src\VividStore\Utilities\ProductFinder::getProductMatch');
    }
    public function on_start()
    {
        $this->registerRoutes();
    }
    public function uninstall()
    {
        $authpm = PaymentMethod::getByHandle('auth_net');
        if(is_object($authpm)){
            $authpm->delete();
        }
        $invoicepm = PaymentMethod::getByHandle('invoice');
        if(is_object($invoicepm)){
            $invoicepm->delete();
        }
        parent::uninstall();
    }


}
?>
