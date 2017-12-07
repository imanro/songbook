<?php
namespace Songbook\Entity;

/**
 * Class ConcertGroup
 *
 * @package Songbook\Entity
 *
 * @property int $id
 * @property string $name
 * @property \Songbook\Entity\Concert $concert
 * @property \Songbook\Entity\ConcertItem[] $concertItems
 */
class ConcertGroup extends AbstractEntity
{
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
            $this->name = $data['name'];
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
