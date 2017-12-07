<?php
namespace Songbook\Entity;

/**
 * Class ConcertItem
 * @package Songbook\Entity
 *
 * @property int $id
 * @property int $create_time
 * @property int $order
 * @property \Songbook\Entity\Concert $concert
 * @property \Songbook\Entity\Song $song
 * @property \Songbook\Entity\ConcertGroup $concertGroup
 */
class ConcertItem extends AbstractEntity
{
    protected $id;

    protected $create_time;

    protected $order;

    protected $concert;

    protected $song;

    protected $concertGroup;

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function exchangeArray ($data = array())
    {
        if (! empty($data['concert'])) {
            $this->concert = $data['concert'];
        }

        if (! empty($data['song'])) {
            $this->song = $data['song'];
        }

        if (! empty($data['order'])) {
            $this->order = $data['order'];
        }

        return $this;
    }
}
