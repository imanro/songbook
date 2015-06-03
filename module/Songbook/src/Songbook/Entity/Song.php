<?php

namespace Songbook\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity(repositoryClass="Songbook\Entity\SongRepository")

 * @ORM\Table(name="song")
 *
 * @property int $id
 * @property string $title
 *
 **/
class Song implements InputFilterAwareInterface {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @deprecated
     */
    protected $title;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $author;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $copyright;

    /**
     * @ORM\Column(type="timestamp")
     */
    protected $create_time;

    /**
     * @ORM\OneToOne(targetEntity="Content", mappedBy="song", cascade="ALL" fetch="EXTRA_LAZY")
     */
    protected $favoriteHeader;

    /**
     * @ORM\OneToOne(targetEntity="Content", mappedBy="song", cascade="ALL")
     */
    protected $defaultHeader;

    /**
     * @ORM\OneToMany(targetEntity="Content", mappedBy="song", cascade="ALL")
     */
    protected $content;

     /**
     * @ORM\OneToMany(targetEntity="ConcertItem", mappedBy="song", cascade="ALL")
     */
    protected $concertItem;

    /**
     * @ORM\OneToOne(targetEntity="ConcertItem", mappedBy="song", cascade="ALL")
     */
    protected $currentConcertItem;

    /**
     * @var InputFilter
     */
    protected $inputFilter;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", indexBy="id")
     * @ORM\JoinTable(name="tag_song",
     *      joinColumns={@ORM\JoinColumn(name="song_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *      )
     **/
    private $tags;

     /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
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
        $this->title = $data['title'];
        $this->author = $data['author'];
        $this->copyright = $data['copyright'];
    }

    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter ()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(
                    array(
                        'name' => 'id',
                        'required' => false,
                        'filters' => array(
                            array(
                                'name' => 'Int'
                            )
                        )
                    ));

            $inputFilter->add(
                    array(
                        'name' => 'title',
                        'required' => true,
                        'filters' => array(
                            array(
                                'name' => 'StripTags'
                            ),
                            array(
                                'name' => 'StringTrim'
                            )
                        ),
                        'validators' => array(
                            array(
                                'name' => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min' => 2,
                                    'max' => 100
                                )
                            )
                        )
                    ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function addTag(Tag $tag)
    {
        $this->tags[$tag->id] = $tag;
    }

    public function importDb()
    {

    }

    public function importCsv($filename)
    {

    }
}
