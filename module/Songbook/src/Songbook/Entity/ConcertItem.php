<?php
namespace Songbook\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="concert_item")
 */
class ConcertItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="timestamp")
     */
    protected $create_time;

    /**
     * @ORM\Column(name="`order`", type="integer", options={"unsigned"=true})
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="Concert", inversedBy="items")
     **/
    private $concert;

    /**
     * @ORM\ManyToOne(targetEntity="Song", cascade="ALL")
     **/
    private $song;


    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get ($property)
    {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set ($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy ()
    {
        return get_object_vars($this);
    }

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
