<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Group;

use Database;

class GroupList
{
    
    public static function getGroupList()
    {
        $queryBuilder = Database::get()->getEntityManager()->createQueryBuilder();
        return $queryBuilder->select('g')
            ->from('\Concrete\Package\VividStore\Src\VividStore\Group\Group','g')
            ->getQuery()
            ->getResult();
    }
    
}
