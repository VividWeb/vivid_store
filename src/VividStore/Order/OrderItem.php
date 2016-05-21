<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Order;

use Database;
use Concrete\Package\VividStore\Src\VividStore\Order\Order as StoreOrder;
use Concrete\Package\VividStore\Src\VividStore\Order\OrderItemOption as StoreOrderItemOption;
use Concrete\Package\VividStore\Src\VividStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="VividStoreOrderItems")
 */
class OrderItem
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $oiID;

    /**
     * @Column(type="integer")
     */
    protected $pID;


    /**
     * @ManyToOne(targetEntity="Concrete\Package\VividStore\Src\VividStore\Order\Order")
     * @JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @Column(type="string")
     */
    protected $oiProductName;

    /**
     * @Column(type="string")
     */
    protected $oiSKU;

    /**
     * @Column(type="decimal", precision=10, scale=4)
     */
    protected $oiPricePaid;

    /**
     * @Column(type="decimal", precision=10, scale=4)
     */
    protected $oiTax;

    /**
     * @Column(type="decimal", precision=10, scale=4)
     */
    protected $oiTaxIncluded;

    /**
     * @Column(type="string")
     */
    protected $oiTaxName;

    /**
     * @Column(type="integer")
     */
    protected $oiQty;

    public function getID()
    {
        return $this->oiID;
    }
    public function getProductID()
    {
        return $this->pID;
    }
    public function getOrder()
    {
        return $this->order;
    }
    public function getProductName()
    {
        return $this->oiProductName;
    }
    public function getSKU()
    {
        return $this->oiSKU;
    }
    public function getPricePaid()
    {
        return $this->oiPricePaid;
    }
    public function getTax()
    {
        return $this->oiTax;
    }
    public function getTaxIncluded()
    {
        return $this->oiTaxIncluded;
    }
    public function getTaxName()
    {
        return $this->oiTaxName;
    }
    public function getQty()
    {
        return $this->oiQty;
    }

    public function setProductID($productid)
    {
        $this->pID = $productid;
    }
    public function setOrder($order)
    {
        $this->order = $order;
    }
    public function setProductName($oiProductName)
    {
        $this->oiProductName = $oiProductName;
    }
    public function setSKU($oiSKU)
    {
        $this->oiSKU = $oiSKU;
    }
    public function setPricePaid($oiPricePaid)
    {
        $this->oiPricePaid = $oiPricePaid;
    }
    public function setTax($oitax)
    {
        $this->oiTax = $oitax;
    }
    public function setTaxIncluded($oiTaxIncluded)
    {
        $this->oiTaxIncluded = $oiTaxIncluded;
    }
    public function setTaxName($oiTaxName)
    {
        $this->oiTaxName = $oiTaxName;
    }
    public function setQty($oiQty)
    {
        $this->oiQty = $oiQty;
    }

    public static function getByID($oiID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $oiID);
    }

    public function add($data, $oID, $tax = 0, $taxIncluded = 0, $taxName = '')
    {
        $db = Database::connection();
        $product = StoreProduct::getByID($data['product']['pID']);

        $productName = $product->getProductName();
        $productPrice = $product->getActivePrice();
        $sku = $product->getProductSKU();
        $qty = $data['product']['qty'];

        $inStock = $product->getProductQty();
        $newStock = $inStock - $qty;

        $variation = $product->getVariation();

        if ($variation) {
            if (!$variation->isUnlimited()) {
                $product->updateProductQty($newStock);
            }
        } elseif (!$product->isUnlimited()) {
            $product->updateProductQty($newStock);
        }

        $order = StoreOrder::getByID($oID);

        $orderItem = new self();
        $orderItem->setProductName($productName);
        $orderItem->setSKU($sku);
        $orderItem->setPricePaid($productPrice);
        $orderItem->setTax($tax);
        $orderItem->setTaxIncluded($taxIncluded);
        $orderItem->setTaxName($taxName);
        $orderItem->setQty($qty);
        $orderItem->setOrder($order);

        if ($product) {
            $orderItem->setProductID($product->getID());
        }

        $orderItem->save();

        foreach ($data['productAttributes'] as $optionGroup => $selectedOption) {
            $optionGroupID = str_replace("po", "", $optionGroup);
            $optionGroupName = self::getProductOptionNameByID($optionGroupID);
            $optionValue = self::getProductOptionValueByID($selectedOption);

            $orderItemOption = new StoreOrderItemOption();
            $orderItemOption->setOrderItemOptionKey($optionGroupName);
            $orderItemOption->setOrderItemOptionValue($optionValue);
            $orderItemOption->setOrderItem($orderItem);
            $orderItemOption->save();
        }

        return $orderItem;
    }


    public function getSubTotal()
    {
        $price = $this->getPricePaid();
        $qty = $this->getQty();
        $subtotal = $qty * $price;
        return $subtotal;
    }
    public function getProductOptions()
    {
        return \Database::connection()->GetAll("SELECT * FROM VividStoreOrderItemOptions WHERE oiID=?", $this->oiID);
    }
    public function getProductOptionGroupNameByID($id)
    {
        $db = Database::connection();
        $optionGroup = $db->GetRow("SELECT * FROM VividStoreProductOptionGroups WHERE pogID=?", $id);
        return $optionGroup['pogName'];
    }
    public function getProductOptionValueByID($id)
    {
        $db = Database::connection();
        $optionItem = $db->GetRow("SELECT * FROM VividStoreProductOptionItems WHERE poiID=?", $id);
        return $optionItem['poiName'];
    }
    public function getProductObject($pID = null)
    {
        return StoreProduct::getByID($this->pID);
    }

    public function removeOrderItemsByOrder($order)
    {
        foreach ($order->getOrderItems() as $orderItem) {
            //TODO delete the options with this.
            $orderItem->delete();
        }
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
