<?php
namespace Concrete\Package\VividStore\Src\VividStore\Utilities;

use Package;
use BlockType;
use BlockTypeSet;
use SinglePage;
use Core;
use Page;
use PageTemplate;
use PageType;
use Group;
use Database;
use FileSet;
use Config;
use Localization;
use Concrete\Core\Database\Schema\Schema;
use \Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use \Concrete\Core\Attribute\Key\UserKey as UserAttributeKey;
use \Concrete\Core\Attribute\Type as AttributeType;
use AttributeSet;
use \Concrete\Core\Page\Type\PublishTarget\Type\AllType as PageTypePublishTargetAllType;
use \Concrete\Core\Page\Type\PublishTarget\Configuration\AllConfiguration as PageTypePublishTargetAllConfiguration;
use \Concrete\Package\VividStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;
use \Concrete\Package\VividStore\Src\VividStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardType as StorePromotionRewardType;
use \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRuleType as StorePromotionRuleType;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethod as StoreShippingMethod;
use \Concrete\Package\VividStore\Src\VividStore\Shipping\ShippingMethodType as StoreShippingMethodType;
use \Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use \Concrete\Package\VividStore\Src\VividStore\Tax\TaxClass as StoreTaxClass;
use \Concrete\Package\VividStore\Src\VividStore\Tax\TaxRate as StoreTaxRate;

class Installer
{
    public static function installSinglePages(Package $pkg)
    {
        //install our dashboard singlepages
        Installer::installSinglePage('/dashboard/store', $pkg);
        Installer::installSinglePage('/dashboard/store/orders/', $pkg);
        Installer::installSinglePage('/dashboard/store/products/', $pkg);
        //Installer::installSinglePage('/dashboard/store/promotions/', $pkg);
        //Installer::installSinglePage('/dashboard/store/promotions/manage', $pkg);
        Installer::installSinglePage('/dashboard/store/products/attributes', $pkg);
        Installer::installSinglePage('/dashboard/store/settings/', $pkg);
        Installer::installSinglePage('/dashboard/store/settings/shipping', $pkg);
        Installer::installSinglePage('/dashboard/store/settings/shipping/clerk', $pkg);
        Installer::installSinglePage('/dashboard/store/settings/tax', $pkg);
        Installer::installSinglePage('/dashboard/store/reports', $pkg);
        Installer::installSinglePage('/dashboard/store/reports/sales', $pkg);
        Installer::installSinglePage('/dashboard/store/reports/products', $pkg);
        Installer::installSinglePage('/cart', $pkg);
        Installer::installSinglePage('/checkout', $pkg);
        Installer::installSinglePage('/checkout/complete', $pkg);
        Page::getByPath('/cart/')->setAttribute('exclude_nav', 1);
        Page::getByPath('/checkout/')->setAttribute('exclude_nav', 1);
        Page::getByPath('/checkout/complete')->setAttribute('exclude_nav', 1);
    }
    public static function installSinglePage($path, $pkg)
    {
        $page = Page::getByPath($path);
        if (!is_object($page) || $page->isError()) {
            SinglePage::add($path, $pkg);
        }
    }
    public static function removeLegacySinglePages(Package $pkg)
    {
        Installer::removeLegacySinglePage('/dashboard/store/discounts/', $pkg);
    }
    public static function removeLegacySinglePage($path, $pkg)
    {
        $page = Page::getByPath($path);
        if (is_object($page)) {
            $page->delete();
        }
    }
    public static function installProductParentPage(Package $pkg)
    {
        $productParentPage = Page::getByPath('/product-detail');
        if (!is_object($productParentPage) || $productParentPage->isError()) {
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
        $productParentPage->setAttribute('exclude_nav', 1);
    }
    public static function installStoreProductPageType(Package $pkg)
    {
        //install product detail page type
        $pageType = PageType::getByHandle('store_product');
        if (!is_object($pageType)) {
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
                Installer::setConfigValue('vividstore.' . $config['configItem'], $config['configValue']);
            }
            $db->Execute("DELETE FROM Config WHERE configGroup='vividstore'");
        }
    }
    public static function setDefaultConfigValues(Package $pkg)
    {
        Installer::setConfigValue('vividstore.productPublishTarget', Page::getByPath('/product-detail')->getCollectionID());
        Installer::setConfigValue('vividstore.symbol', '$');
        Installer::setConfigValue('vividstore.whole', '.');
        Installer::setConfigValue('vividstore.thousand', ',');
        Installer::setConfigValue('vividstore.sizeUnit', 'in');
        Installer::setConfigValue('vividstore.weightUnit', 'lb');
        Installer::setConfigValue('vividstore.taxName', t('Tax'));
        Installer::setConfigValue('vividstore.cartOverlay', false);
        Installer::setConfigValue('vividstore.sizeUnit', 'in');
        Installer::setConfigValue('vividstore.weightUnit', 'lb');
    }
    public static function setConfigValue($key, $value)
    {
        $config = Config::get($key);
        if (empty($config)) {
            Config::save($key, $value);
        }
    }
    public static function installPaymentMethods(Package $pkg)
    {
        Installer::installPaymentMethod('auth_net', 'Authorize .NET', $pkg);
        Installer::installPaymentMethod('invoice', 'Invoice', $pkg, null, true);
        Installer::installPaymentMethod('paypal_standard', 'PayPal', $pkg);
    }
    public static function installPaymentMethod($handle, $name, $pkg, $displayName=null, $enabled=false)
    {
        $pm = StorePaymentMethod::getByHandle($handle);
        if (!is_object($pm)) {
            StorePaymentMethod::add($handle, $name, $pkg, $displayName, $enabled);
        }
    }
    public static function installPromotionRewardTypes(Package $pkg)
    {
        Installer::installPromotionRewardType('discount', 'Discount', $pkg);
        Installer::installPromotionRewardType('free_product', 'Free Product', $pkg);
    }
    public static function installPromotionRewardType($handle, $name, $pkg)
    {
        $promotionRewardType = StorePromotionRewardType::getByHandle($handle);
        if (!is_object($promotionRewardType)) {
            StorePromotionRewardType::add($handle, $name, $pkg);
        }
    }
    public static function installPromotionRuleTypes(Package $pkg)
    {
        Installer::installPromotionRuleType('subtotal_minimum', 'Subtotal Minimum', $pkg);
        Installer::installPromotionRuleType('product_exists', 'Product X is in Cart', $pkg);
        Installer::installPromotionRuleType('date_restriction', 'Date Limit', $pkg);
        Installer::installPromotionRuleType('qty_in_cart', 'Number of Items in Cart', $pkg);
        Installer::installPromotionRuleType('user_group', 'Specific User Group', $pkg);
    }
    public static function installPromotionRuleType($handle, $name, $pkg)
    {
        $promotionRuleType = StorePromotionRuleType::getByHandle($handle);
        if (!is_object($promotionRuleType)) {
            StorePromotionRuleType::add($handle, $name, $pkg);
        }
    }
    public static function installShippingMethods(Package $pkg)
    {
        Installer::installShippingMethod('flat_rate', 'Flat Rate', $pkg);
        Installer::installShippingMethod('free_shipping', 'Free Shipping', $pkg);
    }
    
    public static function installShippingMethod($handle, $name, $pkg)
    {
        $smt = StoreShippingMethodType::getByHandle($handle);
        if (!is_object($smt)) {
            StoreShippingMethodType::add($handle, $name, $pkg);
        }
    }
    
    public static function migrateOldShippingMethod(Package $pkg)
    {
        $shippingMethodEnabled = Config::get('vividstore.shippingenabled');
        //if it wasn't even enabled, then why bother.
        if ($shippingMethodEnabled) {
            $basePrice = Config::get('vividstore.shippingbase');
            $perItem = Config::get('vividstore.shippingitem');
            $data = array(
                'baseRate' => $basePrice,
                'rateType' => 'quantity',
                'perItemRate' => $perItem,
                'minimumAmount' => 0,
                'maximumAmount' => 0,
                'minimumWeight' => 0,
                'maximumWeight' => 0,
                'countries' => 'all'
            );
            $shippingMethodType = StoreShippingMethodType::getByHandle('flat_rate');
            $shippingMethodTypeMethod = $shippingMethodType->addMethod($data);
            StoreShippingMethod::add($shippingMethodTypeMethod, $shippingMethodType, 'Flat Rate', true);
        }
    }
    
    public static function migrateOldTaxRates(Package $pkg)
    {
        $taxEnabled = Config::get('vividstore.taxenabled');
        //if it wasn't even enabled, then why bother.
        if ($taxEnabled) {
            $taxCountry = Config::get('vividstore.taxcountry');
            $taxState = Config::get('vividstore.taxstate');
            $taxCity = Config::get('vividstore.taxcity');
            $taxAddress = Config::get('vividstore.taxAddress');
            $taxMatch = Config::get('vividstore.taxMatch');
            $taxbased = Config::get('vividstore.taxBased');
            $taxrate = Config::get('vividstore.taxrate');
            $taxCaculation = Config::get('vividstore.calculation');
            $taxName = Config::get('vividstore.taxName');
                       
            $data = array(
                'taxEnabled' => true,
                'taxLabel' => $taxName,
                'taxRate' => $taxrate,
                'taxBased' => $taxbased,
                'taxAddress' => $taxAddress,
                'taxCountry' => $taxCountry,
                'taxState' => $taxState,
                'taxCity' => $taxCity
            );
            $taxRate = StoreTaxRate::add($data);
            $taxClass = StoreTaxClass::getByHandle('default');
            $taxClass->addTaxClassRate($taxRate->getTaxRateID());
        }
    }
    
    public static function installBlocks(Package $pkg)
    {
        $bts = BlockTypeSet::getByHandle('vivid_store');
        if (!is_object($bts)) {
            BlockTypeSet::add("vivid_store", "Store", $pkg);
        }
        Installer::installBlock('vivid_product_list', $pkg);
        Installer::installBlock('vivid_utility_links', $pkg);
        Installer::installBlock('vivid_product', $pkg);
    }
    public static function installBlock($handle, $pkg)
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
        if (count($blocks)<1) {
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
    
    public static function installUserAttributes(Package $pkg)
    {
        //user attributes for customers
        $uakc = AttributeKeyCategory::getByHandle('user');
        $uakc->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_MULTIPLE);

        //define attr group, and the different attribute types we'll use
        $custSet = AttributeSet::getByHandle('customer_info');
        if (!is_object($custSet)) {
            $custSet = $uakc->addSet('customer_info', t('Store Customer Info'), $pkg);
        }
        $text = AttributeType::getByHandle('text');
        $address = AttributeType::getByHandle('address');
        
        Installer::installUserAttribute('email', $text, $pkg, $custSet);
        Installer::installUserAttribute('billing_first_name', $text, $pkg, $custSet);
        Installer::installUserAttribute('billing_last_name', $text, $pkg, $custSet);
        Installer::installUserAttribute('billing_company_name', $text, $pkg, $custSet);
        Installer::installUserAttribute('billing_address', $address, $pkg, $custSet);
        Installer::installUserAttribute('billing_phone', $text, $pkg, $custSet);
        Installer::installUserAttribute('shipping_first_name', $text, $pkg, $custSet);
        Installer::installUserAttribute('shipping_last_name', $text, $pkg, $custSet);
        Installer::installUserAttribute('shipping_company_name', $text, $pkg, $custSet);
        Installer::installUserAttribute('shipping_address', $address, $pkg, $custSet);
    }
    public static function installUserAttribute($handle, $type, $pkg, $set, $data=null)
    {
        $attr = UserAttributeKey::getByHandle($handle);
        if (!is_object($attr)) {
            $name = Core::make("helper/text")->camelcase($handle);
            if (!$data) {
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
            UserAttributeKey::add($type, $data, $pkg)->setAttributeSet($set);
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
        
        Installer::installOrderAttribute('email', $text, $pkg, $orderCustSet);
        Installer::installOrderAttribute('billing_first_name', $text, $pkg, $orderCustSet);
        Installer::installOrderAttribute('billing_last_name', $text, $pkg, $orderCustSet);
        Installer::installOrderAttribute('billing_address', $address, $pkg, $orderCustSet);
        Installer::installOrderAttribute('billing_phone', $text, $pkg, $orderCustSet);
        Installer::installOrderAttribute('shipping_first_name', $text, $pkg, $orderCustSet);
        Installer::installOrderAttribute('shipping_last_name', $text, $pkg, $orderCustSet);
        Installer::installOrderAttribute('shipping_address', $address, $pkg, $orderCustSet);
    }
    
    public static function installOrderAttribute($handle, $type, $pkg, $set, $data=null)
    {
        $attr = StoreOrderKey::getByHandle($handle);
        if (!is_object($attr)) {
            $name = Core::make("helper/text")->camelcase($handle);
            if (!$data) {
                $data = array(
                    'akHandle' => $handle,
                    'akName' => t($name)
                );
            }
            StoreOrderKey::add($type, $data, $pkg)->setAttributeSet($set);
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
        if (!is_object($fs)) {
            FileSet::add("Digital Downloads");
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
        $statuses = array(
            array('osHandle' => 'incomplete', 'osName' => t('Incomplete'), 'osInformSite' => 1, 'osInformCustomer' => 0, 'osIsStartingStatus' => 0),
            array('osHandle' => 'pending', 'osName' => t('Pending'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 1),
            array('osHandle' => 'processing', 'osName' => t('Processing'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0),
            array('osHandle' => 'shipped', 'osName' => t('Shipped'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0),
            array('osHandle' => 'complete', 'osName' => t('Complete'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0),
        );
        foreach ($statuses as $status) {
            $orderStatus = StoreOrderStatus::getByHandle($status['osHandle']);
            if (!is_object($orderStatus)) {
                StoreOrderStatus::add($status['osHandle'], $status['osName'], $status['osInformSite'], $status['osInformCustomer'], $status['osIsStartingStatus']);
            }
        }
    }

    public static function installDefaultTaxClass($pkg)
    {
        $defaultTaxClass = StoreTaxClass::getByHandle("default");
        if (!is_object($defaultTaxClass)) {
            $data = array(
                'taxClassName' => t('Default'),
                'taxClassLocked' => true
            );
            $defaultTaxClass = StoreTaxClass::add($data);
        }
        //for older versions of store, we need to make sure all products have some sort of tax class.
        $db = Database::get();
        $productsWithNoTaxClass = $db->GetAll("SELECT * FROM VividStoreProducts WHERE pTaxClass = ''");
        $tcID = $defaultTaxClass->getTaxClassID();
        foreach ($productsWithNoTaxClass as $p) {
            $db->Query("UPDATE VividStoreProducts SET pTaxClass=? WHERE pID = ?", array($tcID, $p['pID']));
        }
    }

    //The following is copied from 7.5.1's upgrade method. 
    //upgradeDatabase() has been changed to not drop tables unrelated to ORM.
    public function upgrade(Package $pkg)
    {
        $this->upgradeDatabase($pkg);

        // now we refresh all blocks
        $items = $pkg->getPackageItems();
        if (is_array($items['block_types'])) {
            foreach ($items['block_types'] as $item) {
                $item->refresh();
            }
        }
        Localization::clearCache();
    }
    
    public static function upgradeDatabase($pkg)
    {
        $dbm = $pkg->getDatabaseStructureManager();
        $pkg->destroyProxyClasses();
        if ($dbm->hasEntities()) {
            $dbm->generateProxyClasses();
            //$dbm->dropObsoleteDatabaseTables(camelcase($this->getPackageHandle()));
            $dbm->installDatabase();
        }

        if (file_exists($pkg->getPackagePath() . '/' . FILENAME_PACKAGE_DB)) {
            // Legacy db.xml
            // currently this is just done from xml
            $db = Database::get();
            $db->beginTransaction();

            $parser = Schema::getSchemaParser(simplexml_load_file($pkg->getPackagePath() . '/' . FILENAME_PACKAGE_DB));
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
