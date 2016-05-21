<?php
namespace Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewards;

use Database;
use Core;
use \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardType as StorePromotionRewardType;
use \Concrete\Package\VividStore\Src\VividStore\Promotion\PromotionRewardTypeReward as StorePromotionRewardTypeReward;
use \Concrete\Package\VividStore\Src\VividStore\Utilities\ProductFinder as StoreProductFinder;

/**
 * @Entity
 * @Table(name="VividStorePromotionFreeProductRewards")
 */
class FreeProductPromotionReward extends StorePromotionRewardTypeReward
{
    /**
     * @ManyToOne(targetEntity="Concrete\Package\VividStore\Src\VividStore\Product\Product")
     * @JoinColumn(name="product_id", referencedColumnName="pID")
     */
    private $product;

    public function setProduct($product)
    {
        $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public static function getByID($id)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find(get_class(), $id);
    }
    public function dashboardForm()
    {
        $this->set('form', Core::make("helper/form"));
        $this->set('productFinder', StoreProductFinder::renderProductSearchForm());
        $this->set("rewardType", StorePromotionRewardType::getByHandle('free_product'));
    }
    public static function addReward($data)
    {
        $reward = new self();
        $reward->setPromotionID($data['promotionID']);
        $reward->setProduct($data['productID']);
        $reward->save();
        return $reward;
    }
    public function update($data)
    {
        // TODO: Implement update() method.
    }
    public function performReward()
    {
        // TODO: Implement performReward() method.
    }
}
