<?php
namespace Songbook\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use User\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="content")
 *
 * @property int $id
 *
 */
class Content implements InputFilterAwareInterface
{

    /**
     * Header
     */
    const TYPE_HEADER = 'header';

    /**
     * Inline content
     */
    const TYPE_INLINE = 'inline';

    /**
     * Link
     */
    const TYPE_LINK = 'link';

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
     * @ORM\Column(type="enum", options={\Ez\Doctrine\DBAL\Types\Enum::RANGE:"header,inline,link"})
     */
    protected $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * @ORM\Column(type="string", length=65535, nullable=true)
     */
    protected $content;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $is_favorite;

    /**
     * @ORM\ManyToOne(targetEntity="Song")
     */
    protected $song;

    /**
     * @ORM\ManyToOne(targetEntity="\User\Entity\User")
     */
    protected $user;

    //protected $songDefaultHeader;
    //protected $songFavoriteHeader;

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
        $this->type = $data['type'];
        $this->url = $data['url'];
        $this->content = $data['content'];
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
        }
    }
}
