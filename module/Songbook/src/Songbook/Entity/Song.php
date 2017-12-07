<?php

namespace Songbook\Entity;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity(repositoryClass="Songbook\Entity\SongRepository")
 * @ORM\Table(name="song")
 *
 * @property int $id
 * @property string $title
 * @property string $copyright
 * @property int $create_time
 * @property int $cloud_content_sync_time
 * @property \Songbook\Entity\ConcertItem $concertItem
 * @property \Songbook\Entity\ConcertItem $currentConcertItem
 * @property \Songbook\Entity\ConcertItem[] $concertItems
 * @property \Doctrine\ORM\PersistentCollection $content
 * @property \Songbook\Entity\Content $defaultHeader
 * @property \Songbook\Entity\Content $favoriteHeader
 */
class Song extends AbstractEntity implements InputFilterAwareInterface
{

    protected $id;

    protected $title;

    protected $author;

    protected $copyright;

    protected $create_time;

    protected $cloud_content_sync_time;

    protected $favoriteHeader;

    protected $defaultHeader;

    protected $content;

    protected $concertItem;

    protected $concertItems;

    protected $currentConcertItem;

    protected $inputFilter;

    private $tags;

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function exchangeArray($data = array())
    {
        $this->title = $data['title'];
        $this->author = $data['author'];
        $this->copyright = $data['copyright'];
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
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
