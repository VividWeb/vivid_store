<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion;

use Package;
use Database;


/**
 * @Entity
 * @Table(name="VividStorePromotionRules")
 */
class PromotionRule
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $name;
}
