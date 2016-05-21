<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;

use Database;
use Controller;

abstract class PromotionRewardTypeReward extends Controller
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $promotionID;

    public function setPromotionID($id)
    {
        $this->promotionID = $id;
    }

    public function getID()
    {
        return $this->id;
    }
    public function getPromotionID()
    {
        return $this->promotionID;
    }

    abstract public static function getByID($id);
    abstract public function dashboardForm();
    abstract public static function addReward($data);
    abstract public function update($data);
    abstract public function performReward();

    public function save()
    {
        $em = Database::get()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    public function delete()
    {
        $em = Database::get()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}
