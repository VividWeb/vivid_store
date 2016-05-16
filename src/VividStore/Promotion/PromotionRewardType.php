<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;

use Database;
use Core;
use Package;
use View;

/**
 * @Entity
 * @Table(name="VividStorePromotionRewardTypes")
 */
class PromotionRewardType
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $handle;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="integer")
     */
    protected $pkgID;

    private $controller;


    public function setHandle($handle)
    {
        $this->handle = $handle;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setPackageID($pkgID)
    {
        $this->pkgID = $pkgID;
    }
    public function setController()
    {
        $package = Package::getByID($this->pkgID);
        if (!$package) {
            return false;
        }
        $th = Core::make("helper/text");
        $namespace = "Concrete\\Package\\".$th->camelcase($package->getPackageHandle())."\\Src\\VividStore\\Promotion\\PromotionRewards";

        $className = $th->camelcase($this->handle)."PromotionReward";
        $obj = $namespace.'\\'.$className;
        $this->controller = new $obj();
    }
    public function getID()
    {
        return $this->id;
    }
    public function getHandle()
    {
        return $this->handle;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getPackageID()
    {
        return $this->pkgID;
    }
    public function getController()
    {
        return $this->controller;
    }

    public static function getByID($id)
    {
        $db = Database::get();
        $em = $db->getEntityManager();
        $obj = $em->find('Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardType', $id);
        $obj->setController();
        return $obj;
    }

    public static function getByHandle($handle)
    {
        $db = Database::get();
        $em = $db->getEntityManager();
        $obj = $em
            ->getRepository('Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardType')
            ->findOneBy(array('handle' => $handle));
        if (is_object($obj)) {
            $obj->setController();
            return $obj;
        }
    }
    public static function add($handle, $name, $pkg)
    {
        $smt = new self();
        $smt->setHandle($handle);
        $smt->setName($name);
        $pkgID = $pkg->getPackageID();
        $smt->setPackageID($pkgID);
        $smt->save();
        $smt->setController();
        return $smt;
    }
    public function save()
    {
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    public function delete()
    {
        $promotions = StorePromotion::getPromotions($this->getID());
        foreach ($promotions as $promotion) {
            $promotion->delete();
        }
        $em = Database::get()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
    public static function getPromotionRewardTypes()
    {
        $em = Database::get()->getEntityManager();
        $rewardTypes = $em->createQuery('select rt from \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardType rt')->getResult();
        foreach ($rewardTypes as $rewardType) {
            $rewardType->setController();
        }
        return $rewardTypes;
    }
    public function renderDashboardForm($promotion=null)
    {
        $controller = $this->getController();
        $controller->dashboardForm($promotion);
        $pkg = Package::getByID($this->pkgID);
        View::element('promotion_reward_types/'.$this->handle.'/dashboard_form', array('vars'=>$controller->getSets()), $pkg->getPackageHandle());
    }
    public function addReward($data)
    {
        $rewardTypeReward = $this->getController()->addReward($data);
        return $rewardTypeReward;
    }
}
