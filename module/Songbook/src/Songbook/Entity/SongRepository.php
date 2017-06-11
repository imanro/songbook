<?php

namespace Songbook\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class SongRepository extends EntityRepository
{

    public function findOneWithHeaders (\User\Entity\User $user, $id)
    {
        $qb = $this->createQueryBuilderCommon();
        $this->modifyQueryForHeaders($qb, $user);

        $qb->where('t.id=:id');
        $qb->setParameter('id', $id);

        $query = $qb->getQuery();

        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new \Exception('Not found');
        }
    }

    public function findByHeaderWithHeaders(\User\Entity\User $user, $string, array $orderBy, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilderCommon(null, $orderBy, $limit, $offset);
        $this->modifyQueryForHeaders($qb, $user);

        $qb->where('h.content LIKE :string');
        $qb->setParameter('string', '%' . $string . '%');

        $qb->groupBy('t.id');

        $query = $qb->getQuery();

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findByConcertWithHeaders(\Songbook\Entity\Concert $concert, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilderCommon($criteria, $orderBy, $limit, $offset);
        $this->modifyQueryForHeaders($qb, $concert->profile->user);

        $qb->addSelect('i');

        $qb->innerJoin('t.currentConcertItem', 'i', 'WITH', 'i.concert=:concertId');
        $qb->setParameter('concertId', $concert->id);

        $query = $qb->getQuery();

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findLongNotUsedWithHeaders(\Songbook\Entity\Profile $profile, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilderCommon($criteria, $orderBy, null, $offset);
        $this->modifyQueryForHeaders($qb, $profile->user);

        $qb->innerJoin('t.concertItem', 'i');
        $qb->innerJoin('i.concert', 'c');

        $qb->andWhere('c.profile = :profileId');
        $qb->andWhere('c.time < :interval');

        // not in range query
        $qb2 = $this->createQueryBuilder('s2');
        $qb->andWhere(
                $qb->expr()
                 ->notIn('t.id',
                        $qb2->select('s2.id')
                            ->innerJoin('s2.concertItem', 'i2')
                            ->innerJoin('i2.concert', 'c2')
                            ->where('c2.time >= :interval')
                            ->getDQL()));


        $qb->setParameter('profileId', $profile->id);
        $qb->setParameter('interval', new \DateTime('- 2 months'));

        $qb->groupBy('t.id');

        $query = $qb->getQuery();

        try {
            $data = $query->getResult();
            shuffle($data);
            if (! is_null($limit)) {
                return array_slice($data, 0, $limit);
            } else {
                return $data;
            }
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findUsedLastMonthsWithHeaders(\Songbook\Entity\Profile $profile, array $criteria = null, array $orderBy = null, $monthsAmount = 2, $limit = null)
    {
        $qb = $this->createQueryBuilderCommon($criteria, $orderBy, null, 0);
        $this->modifyQueryForHeaders($qb, $profile->user);

        $qb->innerJoin('t.concertItem', 'i');
        $qb->innerJoin('i.concert', 'c');

        $qb->andWhere('c.profile = :profileId');
        $qb->andWhere('c.time > :interval');

        $qb->setParameter('profileId', $profile->id);
        $qb->setParameter('interval', new \DateTime('- ' . (int) $monthsAmount . ' months'));

        $qb->groupBy('t.id');

        $query = $qb->getQuery();

        try {
            $data = $query->getResult();
            shuffle($data);
            if (! is_null($limit)) {
                return array_slice($data, 0, $limit);
            } else {
                return $data;
            }
        } catch (NoResultException $e) {
            return array();
        }
    }

    // rename to findByUserWithHeaders
    public function findByUserWithHeaders(\User\Entity\User $user, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('t')->select(array(
            't',
            'f',
            'h'
        ));

        if (! is_null($criteria)) {
            foreach ($criteria as $column => $value) {
                $qb->where(
                        $qb->expr()
                            ->eq('t.' . $column, $value));
            }
        }

        $qb->leftJoin('t.favoriteHeader', 'f', 'WITH',
                'f.is_favorite=1 AND f.type=:typeHeader and f.user = :userId');

        $qb->innerJoin('t.defaultHeader', 'h', 'WITH',
                'h.type=:typeHeader and h.user = :userId');

        $qb->setParameters(
                array(
                    'typeHeader' => Content::TYPE_HEADER,
                    'userId' => $user->id
                ));

        if (! is_null($orderBy)) {
            foreach ($orderBy as $k => $s) {
                $qb->addOrderBy($k, $s);
            }
        }

        $qb->setFirstResult($offset)
        ->setMaxResults($limit);
        $query = $qb->getQuery();

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function createQueryBuilderCommon($criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('t')->select(
                array(
                    't',
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

        $qb->setFirstResult($offset)
        ->setMaxResults($limit);

        return $qb;
    }

    /**
     * @param \Doctrine\Orm\QueryBuilder $query
     *
     * @return \Doctrine\Orm\QueryBuilder
     */
        /**
     * @param \Doctrine\Orm\QueryBuilder $query
     *
     * @return \Doctrine\Orm\QueryBuilder
     */
    public function modifyQueryForHeaders(\Doctrine\ORM\QueryBuilder $qb, \User\Entity\User $user)
    {
        $qb->addSelect(array('f', 'h'));

        $qb->leftJoin('t.favoriteHeader', 'f', 'WITH',
                'f.is_favorite=1 AND f.type=:typeHeader and f.user = :userId');

        $qb->innerJoin('t.defaultHeader', 'h', 'WITH',
                'h.type=:typeHeader and h.user = :userId');
        $qb->setParameter('userId', $user->id);
        $qb->setParameter('typeHeader', Content::TYPE_HEADER);

        return $qb;
    }

    public function getSqlSelectPartForHeaders()
    {
        $sql = 'f.id as f_id, f.content as f_content, f.is_favorite as f_is_favorite, h.id as h_id, h.content as h_content, h.is_favorite as h_is_favorite';
        return $sql;
    }


    public function getSqlJoinPartForHeaders()
    {
        $sql = 'LEFT JOIN content f ON a.id=f.song_id AND f.is_favorite=1 AND f.type=:typeHeader AND f.user_id=:userId
                INNER JOIN content h ON a.id=h.song_id AND h.type=:typeHeader and h.user_id=:userId';

        return $sql;
    }

    public function setNativeQueryParametersForHeaders(\Doctrine\ORM\NativeQuery $query, \Doctrine\ORM\Query\ResultSetMappingBuilder $rsm, \User\Entity\User $user)
    {
        $query->setParameter(':typeHeader', Content::TYPE_HEADER);
        $query->setParameter(':userId', $user->id);

        $rsm->addJoinedEntityFromClassMetadata('Songbook\Entity\Content', 'f',
                'a', 'favoriteHeader',
                 array(
                   'id' => 'f_id',
                    'create_time' => 'f_create_time',
                    'type' => 'f_type',
                    'url' => 'f_url',
                    'content' => 'f_content',
                    'is_favorite' => 'f_is_favorite',
                    'song_id' => 'f_song_id',
                    'user_id' => 'f_user_id'
                ) );
        $rsm->addJoinedEntityFromClassMetadata('Songbook\Entity\Content', 'h',
                'a', 'defaultHeader',
                array(
                    'id' => 'h_id',
                    'create_time' => 'h_create_time',
                    'type' => 'h_type',
                    'url' => 'h_url',
                    'content' => 'h_content',
                    'is_favorite' => 'h_is_favorite',
                    'song_id' => 'h_song_id',
                    'user_id' => 'h_user_id'
                ));
    }

    public function findWithHeaders(\User\Entity\User $user, $id)
    {
        $qb = $this->createQueryBuilder('t')->select(array(
            't',
            'f',
            'h'
        ));

        $qb->leftJoin('t.favoriteHeader', 'f', 'WITH',
                'f.is_favorite=1 AND f.type=:typeHeader and f.user = :userId');

        $qb->innerJoin('t.defaultHeader', 'h', 'WITH',
                'h.type=:typeHeader and h.user = :userId');

        $qb->where('t.id = :id');
        $qb->setParameters(
                array(
                    'typeHeader' => Content::TYPE_HEADER,
                    'userId' => $user->id,
                    'id' => $id
                ));

        $query = $qb->getQuery();

        try {
            return $query->getOneOrNullResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function findSimilarWithHeaders(Song $master, \User\Entity\User $user, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $titles = array();
        if ($master->favoriteHeader) {
            $titles[] = $master->favoriteHeader->content;
        }

        if ($master->defaultHeader && $master->defaultHeader !== $master->favoriteHeader) {
            $titles[] = $master->defaultHeader->content;
        }

        $string = implode(' ', $titles);

        if( !mb_strlen($string)) {
            throw new \Exception('song has empty title');
        }

        $string = trim( mb_strtolower( preg_replace('/[\W]+/u', ' ', $string)) );
        $stopwords = $this->getStopWords();

        $pairs = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ж' => 'j',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'stch',
            'ы' => 'y',
            'э' => 'e',
            'ю' => 'ju',
            'я' => 'ja',
            'і' => 'i',
            'ї' => 'ji',
            'є' => 'e',
        );

        $pairsFlip = array(
            'a' => 'а',
            'b' => 'б',
            'c' => 'ц',
            'd' => 'д',
            'e' => 'е',
            'f' => 'ф',
            'g' => 'г',
            'h' => 'х',
            'i' => 'и',
            'j' => 'й',
            'k' => 'к',
            'l' => 'л',
            'm' => 'м',
            'n' => 'н',
            'o' => 'о',
            'p' => 'п',
            'q' => 'к',
            'r' => 'р',
            's' => 'с',
            't' => 'т',
            'u' => 'у',
            'v' => 'в',
            'w' => 'в',
            'x' => 'кс',
            'y' => 'ы',
            'z' => 'з',
        );

        //$pairsFlip = array_flip($pairs);


        $pairs2 = $pairs;
        foreach($pairs as $key => $value ){
            $pairs2[strtoupper($key)] = $value;
        }

        $pairsFlip2 = $pairsFlip;

        foreach($pairsFlip as $key => $value ){
            $pairsFlip2[mb_strtoupper($key)] = $value;
        }


        $string2 = strtr($string, $pairs2);
        $string3 = strtr($string, $pairsFlip2);

        $stringOrig = $string;

        if($string2 != $stringOrig && mb_strlen($string2)){
            $string .= ' ' . $string2;
        }

        if ($string3 != $stringOrig && mb_strlen($string3)) {
            $string .= ' ' . $string3;
        }

        $words = preg_split('/\s+/u', $string );

        if (! count($words)) {
            throw new \Exception('wrong string param given');
        }

        // lc

        // remove duplicates
        array_unique($words);

        $array = array();

        foreach($words as $word) {
            if(isset($stopwords[$word])){
                continue;
            }
            $array[] = vsprintf(
                    'CASE WHEN (h.content LIKE \'%%%s%%\') THEN 1 ELSE 0 END',
                    array(
                        $word
                    ));

        }

        if( !count( $array )) {
            $array []= '0';
        }

        $qb = $this->createQueryBuilder('t')->select(array(
            't',
            'f',
            'h'
        ));

        if (! is_null($criteria)) {
            foreach ($criteria as $column => $value) {
                $qb->where(
                        $qb->expr()
                            ->eq('t.' . $column, $value));
            }
        }

        $qb->andWhere('t.id != :masterId');

        $qb->orderBy('score', 'DESC');

        if (! is_null($orderBy)) {
            foreach ($orderBy as $k => $s) {
                $qb->addOrderBy($k, $s);
            }
        }


        $qb->leftJoin('t.favoriteHeader', 'f', 'WITH',
                'f.is_favorite=1 AND f.type=:typeHeader and f.user = :userId');

        $qb->innerJoin('t.defaultHeader', 'h', 'WITH', 'h.type=:typeHeader');

        $qb->addSelect(array(
            '(' . implode('+', $array) . ')' . ' score'
        ));

        $qb->setParameters(
                array(
                    'typeHeader' => Content::TYPE_HEADER,
                    'userId' => $user->id,
                    'masterId' => $master->id,
                ));

        $qb->setFirstResult($offset)->setMaxResults($limit);

        $query = $qb->getQuery();

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    protected function getStopWords()
    {
        $inline = <<<EOD
а
алло
без
бы
был
была
были
было
быть
в
вам
вами
вас
ваш
ваше
ваши
вверх
вдали
вдруг
ведь
везде
весь
виде
вниз
внизу
во
вокруг
вон
восемь
восьмой
вот
все
всего
всем
всеми
всему
всех
всею
всю
всюду
вся
всё
второй
вы
где
да
дел
для
до
его
ее
ей
ему
если
есть
еще
ещё
ею
её
ж
же
за
и
из
или
им
ими
имя
их
к
как
какая
какой
кем
когда
кого
ком
кому
кто
куда
лет
ли
лишь
лучше
люди
м
меня
мне
мной
мною
мог
мож
мой
мор
мочь
моя
моё
мы
на
над
надо
назад
нам
нас
наш
наша
наше
наши
не
него
нее
ней
нельзя
нем
немного
нему
нет
нею
неё
ни
нибудь
ниже
низко
никогда
никуда
ними
них
ничего
но
ну
нужно
нх
о
об
оба
обычно
он
она
они
оно
оный
опять
от
ото
по
под
пожалуйста
позже
пока
пор
пора
после
посреди
потом
потому
почему
почти
прекрасно
при
про
пятый
пять
раз
разве
раньше
рядом
с
сам
сама
сами
самим
самими
самих
само
самой
самом
самому
саму
свое
своего
своей
свои
своих
свой
свою
своя
себе
себя
сих
со
собой
собою
совсем
т
та
так
такая
также
такие
такое
такой
там
твой
твоя
твоё
те
тебе
тебя
тем
теми
тех
то
тобой
тобою
тогда
того
только
том
тому
тот
тою
твой
твоя
твоей
твою
ту
тут
ты
тысяч
у
уж
уже
уметь
хорошо
хотеть
хоть
хотя
хочешь
часто
чаще
чего
чем
чему
через
что
чтоб
чтобы
чуть
эта
эти
этим
этими
это
этого
этой
этом
этому
этот
эту
я
бог
бога
богу
боге
боже
божий
божая
божее
господь
господа
господу
господе
господний
господня
иисус
иисуса
иисусу
иисуса
иисусе
EOD;
        $array = explode("\n", $inline);
        $assoc = array();
        foreach($array as $value ) {
            $assoc[trim($value)] = true;
        }

        return $assoc;
    }
}
