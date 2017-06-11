<?php
namespace Songbook\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


class ConcertGroup extends AbstractEntity
{
    /**
     * @var int
     */
    protected $id;

    protected $name;
    protected $concert;
    private $concertItems;

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function exchangeArray ($data = array())
    {
        if (! empty($data['name'])) {
            $this->order = $data['name'];
        }

        if (! empty($data['concert'])) {
            $this->concert = $data['concert'];
        }

        if (! empty($data['concertItems'])) {
            $this->concertItems = $data['concertItems'];
        }

        return $this;
    }

}
