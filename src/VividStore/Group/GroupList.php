<?php 
namespace Concrete\Package\VividStore\Src\VividStore\Groups;

use Database;

class GroupList
{
    
    public function getGroupList()
    {
        $queryBuilder = Database::get()->getEntityManager()->createQueryBuilder();
        return $queryBuilder->select('g')
            ->from('\Concrete\Package\VividStore\Src\VividStore\Groups\Group','g')
            ->getQuery()
            ->getResult();
    }
    
}
