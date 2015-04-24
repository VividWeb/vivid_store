<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Groups;
use Database;
use Concrete\Core\Foundation\Object as Object;
defined('C5_EXECUTE') or die(_("Access Denied."));
class ProductGroup extends Object
{
    
    public static function getByID($gID) {
        $db = Database::get();
        $data = $db->GetRow("SELECT * FROM VividStoreGroups WHERE gID=?",$gID);
        if(!empty($data)){
            $group = new ProductGroup();
            $group->setPropertiesFromArray($data);
        }
        return($group instanceof ProductGroup) ? $group : false;
    } 
    
    public function getGroupName(){ return $this->groupName; }    
    public function getGroupID() { return $this->gID; }
    
    public function add($data)
    {
        $db = Database::get();
        $db->Execute("INSERT into VividStoreGroups(groupName) values(?)",$data['groupName']);
    }
    public function remove()
    {
        $db = Database::get();
        $db->Execute("DELETE FROM VividStoreGroups WHERE gID=?",$this->gID);
    }
    public function update($data)
    {
        $db = Database::get();
        $db->Execute("UPDATE VividStoreGroups SET groupName=? WHERE gID=?",array($data['gName'],$this->gID));
    }
    
}