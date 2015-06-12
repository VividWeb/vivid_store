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

    public static function addOrderStatusesToDatabase(Package $package)
    {
        $table = OrderStatus::getTableName();
        $db = Database::get();
        $statuses = array(
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
