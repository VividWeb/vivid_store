<?php
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use Package;
use Database;
use Concrete\Core\Database\Schema\Schema;
use Config;
use Concrete\Package\VividStore\Src\VividStore\Orders\OrderStatus\OrderStatus;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Installer
{
	public static function installSinglePages(Package $package)
	{
		//install our dashboard singlepages
        $this->installSinglePage('/dashboard/store', $pkg);
		$this->installSinglePage('/dashboard/store/orders/', $pkg);
		$this->installSinglePage('/dashboard/store/products/', $pkg);
		$this->installSinglePage('/dashboard/store/products/attributes', $pkg);
		$this->installSinglePage('/dashboard/store/settings/', $pkg);
		$this->installSinglePage('/dashboard/store/reports', $pkg);
		$this->installSinglePage('/dashboard/store/reports/sales', $pkg);
		$this->installSinglePage('/dashboard/store/reports/products', $pkg);
		$this->installSinglePage('/cart', $pkg);
		$this->installSinglePage('/checkout', $pkg);
		$this->installSinglePage('/checkout/complete', $pkg);
        Page::getByPath('/cart/')->setAttribute('exclude_nav', 1);
        Page::getByPath('/checkout/')->setAttribute('exclude_nav', 1);
        Page::getByPath('/checkout/complete')->setAttribute('exclude_nav', 1);
	}
	public static function installSinglePage($path,$pkg)
	{
		$page = Page::getByPath($path);
		if (!is_object($page) || $page->isError()) {
            SinglePage::add($path, $pkg);
        }
	}
	public static function installProductParentPage(Package $package)
	{
		$productParentPage = Page::getByPath('/product-detail');
        if (!is_object($productParentPage) || $productParentPage->isError()) {
            $productParentPage = Page::getByID(1)->add(
                PageType::getByHandle('page'),
                array(
                    'cName' => t('Product Detail'),
                    'cHandle' => 'product-detail',
                    'pkgID' => $package->pkgID
                ),
                PageTemplate::getByHandle('full')
            );
        }
		$productParentPage->setAttribute('exclude_nav', 1);
	}
	public function installStoreProductPageType(Package $pkg){
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
	public static function updateConfigStorage(Package $pkg)
	{
		$db = Database::get();
        $configitems = $db->GetAll("SELECT * FROM Config WHERE configGroup='vividstore'");
        if (!empty($configitems)) {
            foreach ($configitems as $config) {
                $this->setConfigValue('vividstore.' . $config['configItem'], $config['configValue']);
            }
            $db->Execute("DELETE FROM Config WHERE configGroup='vividstore'");
        }
	}
	public static function setDefaultConfigValues(Package $pkg)
	{
		$this->setConfigValue('vividstore.productPublishTarget',Page::getByPath('/product-detail')->getCollectionID());
		$this->setConfigValue('vividstore.symbol','$');
        $this->setConfigValue('vividstore.whole','.');
        $this->setConfigValue('vividstore.thousand',',');
        $this->setConfigValue('vividstore.sizeUnit','in');
        $this->setConfigValue('vividstore.weightUnit','l');
		$this->setConfigValue('vividstore.taxName',t('Tax'));
		$this->setConfigValue('vividstore.cartOverlay',false);
		$this->setConfigValue('vividstore.sizeUnit', 'in');
        $this->setConfigValue('vividstore.weightUnit');
        $this->setConfigValue('vividstore.weightUnit', 'lb');
	}
	public static function setConfigValue($key,$value)
	{
		$config = Config::get($key);
		if(empty($config)){
			Config::save($key,$avlue);
		}
	}
	public static function installPaymentMethods(Package $pkg)
	{
		$this->installPaymentMethod('auth_net','Authorize .NET',$pkg);
		$this->installPaymentMethod('invoice','Invoice',$pkg,null,true);
        $this->installPaymentMethod('paypal_standard','PayPal',$pkg);
	}
	public static function installPaymentMethod($handle,$name,$pkg=null,$displayName=null,$enabled=false)
	{
		$pm = PaymentMethod::getByHandle($handle);
        if (!is_object($pm)) {
            PaymentMethod::add($handle,$name,$pkg,$displayName,$enabled);
        }
	}
	public static function installBlocks(Package $pkg)
	{
		BlockTypeSet::add("vivid_store","Store", $pkg);
        $this->installBlock('vivid_product_list', $pkg);
        $this->installBlock('vivid_utility_links', $pkg);
        $this->installBlock('vivid_product', $pkg);
	}
	public static function installBlock($handle,$pkg)
	{
		$blockType = BlockType::getByHandle($handle);
        if (!is_object($blockType)) {
            BlockType::installBlockTypeFromPackage($handle, $pkg);
        }
	}
	public static function setPageTypeDefaults(Package $pkg)
	{
		$pageType = PageType::getByHandle('store_product');
        $template = $pageType->getPageTypeDefaultPageTemplateObject();
        $pageObj = $pageType->getPageTypePageTemplateDefaultPageObject($template);

        $bt = BlockType::getByHandle('vivid_product');
        $blocks = $pageObj->getBlocks('Main');
		//only install blocks if there's none on there.
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
	}
	
	public static function installCustomerGroups(Package $pkg)
	{
		$group = Group::getByName('Store Customer');
        if (!$group || $group->getGroupID() < 1) {
            $group = Group::add('Store Customer', t('Registered Customer in your store'));
        }
	}
	
	public static function installUserAttributes(Package $package)
	{
		//user attributes for customers
        $uakc = AttributeKeyCategory::getByHandle('user');
        $uakc->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_MULTIPLE);

        //define attr group, and the different attribute types we'll use
        $custSet = $uakc->addSet('customer_info', t('Store Customer Info'), $pkg);
        $text = AttributeType::getByHandle('text');
        $address = AttributeType::getByHandle('address');
		
		$this->installUserAttribute('email',$text,$pkg,$custSet);
		$this->installUserAttribute('billing_first_name',$text,$pkg,$custSet);
		$this->installUserAttribute('billing_last_name',$text,$pkg,$custSet);
		$this->installUserAttribute('billing_address',$address,$pkg,$custSet);
		$this->installUserAttribute('billing_phone',$text,$pkg,$custSet);
		$this->installUserAttribute('shipping_first_name',$text,$pkg,$custSet);
		$this->installUserAttribute('shipping_last_name',$text,$pkg,$custSet);
		$this->installUserAttribute('shipping_address',$address,$pkg,$custSet);
		
	}
	public static function installUserAttribute($handle,$type,$pkg,$set,$data=null)
	{
		$attr = UserAttributeKey::getByHandle($handle);
        if (!is_object($bFirstname)) {
        	$name = Core::make("helper/text")->camelcase($handle);
        	if(!$data){
        		$data = array(
        			'akHandle' => $handle,
                    'akName' => t($name),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true
                );
        	}
            UserAttributeKey::add($text,$data,$pkg)->setAttributeSet($set);
        }
	}
	
	public static function installOrderAttributes(Package $pkg)
	{
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
		
		$text = AttributeType::getByHandle('text');
        $address = AttributeType::getByHandle('address');
		
		$this->installOrderAttribute('email', $text, $pkg, $orderCustSet);
		$this->installOrderAttribute('billing_first_name', $text, $pkg, $orderCustSet);
		$this->installOrderAttribute('billing_last_name', $text, $pkg, $orderCustSet);
		$this->installOrderAttribute('billing_address', $address, $pkg, $orderCustSet);
		$this->installOrderAttribute('billing_phone', $text, $pkg, $orderCustSet);
		$this->installOrderAttribute('shipping_first_name', $text, $pkg, $orderCustSet);
		$this->installOrderAttribute('shipping_last_name', $text, $pkg, $orderCustSet);
		$this->installOrderAttribute('shipping_address', $address, $pkg, $orderCustSet);
	}
	
	public static function installOrderAttribute($handle,$type,$set,$pkg,$data=null)
	{
		$attr = StoreOrderKey::getByHandle($handle);
        if (!is_object($email)) {
        	$name = Core::make("helper/text")->camelcase($handle);
			if(!$data){
				$data = array(
					'akHandle' => $handle,
                	'akName' => t($name)
				);
			}
            StoreOrderKey::add($text, $data, $pkg)->setAttributeSet($set);
        }
	}
	
	public static function installProductAttributes(Package $pkg)
	{
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
	}
	
	public static function createDDFileset(Package $pkg)
	{
		//create fileset to place digital downloads
        $fs = FileSet::getByName('Digital Downloads');
        if(!is_object($fs)){
            FileSet::add("Digital Downloads");
        }
	}
	
    public static function refreshDatabase(Package $package)
    {
        if (version_compare(APP_VERSION, '5.7.4', '<')) {
            if (file_exists($package->getPackagePath() . '/' . FILENAME_PACKAGE_DB)) {
                $db = Database::get();
                $db->beginTransaction();
                $parser = Schema::getSchemaParser(simplexml_load_file($package->getPackagePath() . '/' . FILENAME_PACKAGE_DB));
                $parser->setIgnoreExistingTables(false);
                $toSchema = $parser->parse($db);
                $fromSchema = $db->getSchemaManager()->createSchema();
                $comparator = new \Doctrine\DBAL\Schema\Comparator();
                $schemaDiff = $comparator->compare($fromSchema, $toSchema);
                $saveQueries = $schemaDiff->toSaveSql($db->getDatabasePlatform());
                foreach ($saveQueries as $query) {
                    $db->query($query);
                }
                $db->commit();
            }
        }
    }

    public static function renameDatabaseTables(Package $package)
    {
        $renameTables = array(
            'VividStoreProduct' => 'VividStoreProducts',
            'VividStoreProductOptionGroup' => 'VividStoreProductOptionGroups',
            'VividStoreProductOptionItem' => 'VividStoreProductOptionItems',
            'VividStoreProductImage' => 'VividStoreProductImages',
            'VividStoreDigitalFile' => 'VividStoreDigitalFiles',
            'VividStoreOrder' => 'VividStoreOrders',
            'VividStoreOrderStatus' => 'VividStoreOrderStatuses',
            'VividStoreOrderStatusHistory' => 'VividStoreOrderStatusHistories',
            'VividStoreOrderItem' => 'VividStoreOrderItems',
            'VividStoreOrderItemOption' => 'VividStoreOrderItemOptions'
        );
        $db = Database::get();
        $oldTableNames = "'" . implode("', '", array_keys($renameTables)) . "'";
        $existingOldTableNames = $db->GetCol(
            "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA=? AND TABLE_NAME IN (" . $oldTableNames . ")",
            array(Config::get('database.connections.concrete.database'))
        );
        $db->beginTransaction();
        foreach ($existingOldTableNames as $existingOldTableName) {
            $newTableName = $renameTables[$existingOldTableName];
            $db->execute("DROP TABLE IF EXISTS " . $newTableName);
            $db->execute("RENAME TABLE " . $existingOldTableName . " TO " . $newTableName);
        }
        $db->commit();

    }

    public static function installOrderStatuses(Package $package)
    {
        $table = OrderStatus::getTableName();
        $db = Database::get();
        $statuses = array(
            array('osHandle' => 'incomplete', 'osName' => t('Incomplete'), 'osInformSite' => 1, 'osInformCustomer' => 0, 'osIsStartingStatus' => 0),
            array('osHandle' => 'pending', 'osName' => t('Pending'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 1),
            array('osHandle' => 'processing', 'osName' => t('Processing'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0),
            array('osHandle' => 'shipped', 'osName' => t('Shipped'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0),
            array('osHandle' => 'complete', 'osName' => t('Complete'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0),
        );
        foreach ($statuses as $status) {
            $row = $db->GetRow("SELECT * FROM " . $table . " WHERE osHandle=?", array($status['osHandle']));
            if (!isset($row['osHandle'])) {
                OrderStatus::add($status['osHandle'], $status['osName'], $status['osInformSite'], $status['osInformCustomer'], $status['osIsStartingStatus']);
            } else {
                $orderStatus = OrderStatus::getByID($row['osID']);
                $orderStatus->update($status, true);
            }
        }
    }


}
