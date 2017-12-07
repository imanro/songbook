<?php
/**
 * Created by PhpStorm.
 * User: manro
 * Date: 18.08.17
 * Time: 15:07
 */
namespace Songbook\Service;


use User\Entity\User;

class Setting extends AbstractService
{
    public function getValue(User $user, $name)
    {
        $item = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Setting')
            ->findOneBy(array('name' => $name, 'user' => $user));

        if($item) {
            return $item->value;
        } else {
            return null;
        }
    }


}
