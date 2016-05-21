<?php
namespace Concrete\Package\VividStore\Src\Attribute\Value;

use Database;
use \Concrete\Core\Attribute\Value\Value as Value;

/**
 * @Entity
 * @Table(name="VividStoreProductAttributeValues")
 */
class StoreProductValue extends Value
{
    /**
     * @Id @Column(type="integer")
     */
    protected $oID;

    /**
     * @Id @Column(type="integer")
     */
    protected $akID;

    /**
     * @Id @Column(type="integer")
     */
    protected $avID;

    public function setProduct($product)
    {
        $this->product = $product;
    }
    
    public static function getByID($avID)
    {
        $cav = new StoreProductValue();
        $cav->load($avID);
        if ($cav->getAttributeValueID() == $avID) {
            return $cav;
        }
    }

    public function delete()
    {
        $db = Database::get();
        $db->Execute('delete from VividStoreProductAttributeValues where pID = ? and akID = ? and avID = ?', array(
            $this->product->getProductID(),
            $this->attributeKey->getAttributeKeyID(),
            $this->getAttributeValueID()
        ));

        // Before we run delete() on the parent object, we make sure that attribute value isn't being referenced in the table anywhere else
        $num = $db->GetOne('select count(avID) from VividStoreProductAttributeValues where avID = ?', array($this->getAttributeValueID()));
        if ($num < 1) {
            parent::delete();
        }
    }
}
