<?php
namespace Songbook\Entity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class ConcertRepository extends EntityRepository
{
    // rename to findByUserWithHeaders

    /**
     * @param Profile $profile
     * @param array $criteria
     * @param array $orderBy
     * @param string $limit
     * @param string $offset
     */
    public function findByProfile (Profile $profile, array $criteria = null,
            array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('t')->select(
                array(
                    't'
                ));

        $qb->where('t.profile=:profileId');
        $qb->setParameters(
                array(
                    'profileId' => $profile->id
                ));

        if (! is_null($criteria)) {
            foreach ($criteria as $column => $value) {
                $qb->where(
                        $qb->expr()
                            ->eq('t.' . $column, $value));
            }
        }

        if (! is_null($orderBy)) {
            foreach ($orderBy as $k => $s) {
                $qb->addOrderBy($k, $s);
            }
        }

        $qb->setFirstResult($offset)->setMaxResults($limit);
        $query = $qb->getQuery();

        //var_dump($query);

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }
}
