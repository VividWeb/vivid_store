<?php
namespace Concrete\Package\VividStore\Src\VividStore\Tax;

use Package;
use Core;
use Database;

use \Concrete\Package\VividStore\Src\VividStore\Tax\Tax;

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @Entity
 * @Table(name="VividStoreTaxClasses")
 */
class TaxClass
{
    
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $tcID;
        
    /**
     * @Column(type="string")
     */
    protected $taxClassName;
    
    /**
     * @Column(type="string",nullable=true)
     */
    protected $taxClassRates;
    
    public function setTaxClassName($name){ $this->taxClassName = $name; }
    public function setTaxClassRates(array $rates = null){
        if($rates){
            $rates = implode(',',$rates);
            $this->taxClassRates = $rates;
        }
    }
    
    public function getTaxClassID(){ return $this->tcID; }
    public function getTaxClassName(){ return $this->taxClassName; }
    public function getTaxClassRates(){
        $taxRates =  explode(',',$this->taxClassRates); 
        $taxes = array();
        foreach($taxRates as $tr){
            $taxes[] = TaxRate::getByID($tr);
        }
        return $taxes;
    }
    
    public static function getByID($tcID) 
    {
        $db = Database::get();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\VividStore\Src\VividStore\Tax\TaxClass', $tcID);
    }
    
    public static function getTaxClasses()
    {
        return Database::get()->getEntityManager()->createQuery('select u from \Concrete\Package\VividStore\Src\VividStore\Tax\TaxClass u')->getResult();
    }
    
    public static function add($data)
    {
        $tc = new self();
        $tc->setTaxClassName($data['taxClassName']);
        $tc->setTaxClassRates($data['taxClassRates']);
        $tc->save();
    }
    
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