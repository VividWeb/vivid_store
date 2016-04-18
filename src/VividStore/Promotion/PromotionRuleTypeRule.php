<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;

use Database;
use Controller;

abstract class PromotionRuleTypeRule extends Controller
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    protected $promotion;

    /**
     * @Column(type="string")
     */
    protected $label;

    abstract public static function getByID($id);

    abstract public function dashboardForm();

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
