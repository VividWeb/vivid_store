<?php
namespace Concrete\Package\VividStore\Src\VividStore\Order\OrderStatus;

use \Concrete\Core\Utility\Service\Text as TextHelper;
use Database;

/**
 * @Entity
 * @Table(name="VividStoreOrderStatuses")
 */
class OrderStatus
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $osID;

    /** @Column(type="text") */
    protected $osHandle;

    /** @Column(type="text") */
    protected $osName;

    /** @Column(type="boolean") */
    protected $osInformSite;

    /** @Column(type="boolean") */
    protected $osInformCustomer;

    /** @Column(type="boolean") */
    protected $osIsStartingStatus;

    /** @Column(type="integer",nullable=true) */
    protected $osSortOrder;

    public function setHandle($handle)
    {
        $this->osHandle = $handle;
    }
    public function setName($name)
    {
        $this->osName = $name;
    }
    public function setInformSite($bool)
    {
        $this->osInformSite = $bool;
    }
    public function setInformCustomer($bool)
    {
        $this->osInformCustomer = $bool;
    }
    public function setIsStartingStatus($bool)
    {
        $this->osIsStartingStatus = $bool;
    }
    public function setSortOrder($order)
    {
        $this->osSortOrder = $order;
    }
    
    public function getID()
    {
        return $this->osID;
    }
    public function getHandle()
    {
        return $this->osHandle;
    }
    public function getReadableHandle()
    {
        $textHelper = new TextHelper();
        return $textHelper->unhandle($this->osHandle);
    }
    public function getName()
    {
        return $this->osName;
    }
    public function informsSite()
    {
        return $this->osInformSite;
    }
    public function informsCustomer()
    {
        return $this->osInformCustomer;
    }
    public function isStartingStatus()
    {
        return $this->osIsStartingStatus;
    }
    public function getSortOrder()
    {
        return $this->osSortOrder;
    }

    public static function getByID($osID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $osID);
    }

    public static function getByHandle($osHandle)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository(get_class())->findOneBy(array('osHandle' => $osHandle));
    }

    public static function getAll()
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();
        return $em->createQuery('select os from '.get_class().' os')->getResult();
    }

    public static function getList()
    {
        $statuses = array();
        foreach (self::getAll() as $status) {
            $statuses[$status->getHandle()] = t($status->getName());
        }
        return $statuses;
    }

    public static function add($osHandle, $osName = null, $osInformSite = 1, $osInformCustomer = 1, $osIsStartingStatus = 0)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();
        if (is_null($osName)) {
            $textHelper = new TextHelper();
            $osName = $textHelper->unhandle($osHandle);
        }
        if ($osIsStartingStatus) {
            $existingStartingStatus = $em->getRepository(get_class())->findOneBy(array('osIsStartingStatus' => 1));
            if (is_object($existingStartingStatus)) {
                $existingStartingStatus->setIsStartingStatus(false);
                $existingStartingStatus->save();
            }
        }
        $orderStatus = new self();
        $orderStatus->setHandle($osHandle);
        $orderStatus->setName($osName);
        $orderStatus->setInformSite($osInformSite ? 1 : 0);
        $orderStatus->setInformCustomer($osInformCustomer ? 1 : 0);
        $orderStatus->setIsStartingStatus($osIsStartingStatus ? 1 : 0);
        $orderStatus->save();
    }

    public static function getStartingStatus()
    {
        $em = \Database::connection()->getEntityManager();
        $startingStatus = $em->getRepository(get_class())->findOneBy(array('osIsStartingStatus' => 1));
        return $startingStatus;
    }

    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    public function delete()
    {
        $em = \Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}
