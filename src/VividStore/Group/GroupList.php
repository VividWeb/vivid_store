<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Group;

use Database;

class GroupList
{
    public static function getGroupList()
    {
        $em = Database::get()->getEntityManager();
        return $em->createQuery('select g from \Concrete\Package\VividStore\Src\VividStore\Group\Group g')->getResult();
    }
}
