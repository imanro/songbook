<?php
namespace Songbook\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity(repositoryClass="Songbook\Entity\ConcertRepository")
 * @ORM\Table(name="concert")
 *
 * @property $id
 */
class Concert implements InputFilterAwareInterface
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
     * @ORM\Column(name="`time`", type="timestamp")
     */
    protected $time;

    /**
     * @ORM\ManyToOne(targetEntity="Profile")
     */
    protected $profile;

    /**
     * @ORM\OneToMany(targetEntity="ConcertItem", mappedBy="concert")
     * @ORM\OrderBy({"order" = "ASC", "id" = "ASC"})
     */
    protected $items;

    /**
     * @var \Songbook\Service\Concert
     */
    protected $concertService;

    /**
     * @var \Songbook\Service\Song
     */
    protected $songService;

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
        $this->time = $data['time'];
        $this->profile = $data['profile'];

        return $this;
    }

    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception('Not used');
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
        }
    }

    /**
     * @return \Sonbook\Model\ConcertService
     */
    protected function getConcertService ()
    {
        if (! $this->concertService) {
            $sm = $this->getServiceLocator();
            $this->concertService = $sm->get('Songbook\Service\Concert');
        }

        return $this->concertService;
    }

    /**
     * @return \Sonbook\Model\SongService
     */
    protected function getSongService ()
    {
        if (! $this->songService) {
            $sm = $this->getServiceLocator();
            $this->songService = $sm->get('Songbook\Service\Song');
        }

        return $this->songService;
    }

}
