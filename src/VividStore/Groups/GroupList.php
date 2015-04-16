<?php 
namespace Concrete\Package\VividStore\src\VividStore\Groups;
use Database;
use Concrete\Package\VividStore\Src\VividStore\Groups\ProductGroup as ProductGroup;
defined('C5_EXECUTE') or die(_("Access Denied."));
class GroupList
{
    
    public function getGroupList()
    {
        $db = Database::get();
        $data = $db->GetAll("SELECT gID FROM VividStoreGroups");
        $groupList = array();
        foreach ($data as $group){
           // $groupList[] = $group['gID'];    
           // echo $group['gID'];
            $groupList[] = ProductGroup::getByID($group['gID']);
        }
        return $groupList;
    }
    
}