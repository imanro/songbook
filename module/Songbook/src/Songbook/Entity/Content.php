<?php
namespace Songbook\Entity;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


/**
 *
 * @property int $id
 * @property int $create_time
 * @property string $type
 * @property string $url
 * @property string $content
 * @property bool $is_favorite
 * @property string $file_name
 * @property string $mime_type
 *
 * @property \Songbook\Entity\Song $song
 * @property \User\Entity\User $user
 */
class Content extends AbstractEntity implements InputFilterAwareInterface
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
     * Link
     */
    const TYPE_GDRIVE_CLOUD_FILE = 'gdrive_cloud_file';

    const MIME_ICON_NAME_DEFAULT = 'text-plain.svg';

    const FUNCTIONAL_TYPE_ALL = 1;

    const FUNCTIONAL_TYPE_LYRICS = 2;

    const FUNCTIONAL_TYPE_PRESENTATION = 3;

    const FUNCTIONAL_TYPE_AUDIO = 4;

    const FUNCTIONAL_TYPE_VIDEO = 5;

    const FUNCTIONAL_TYPE_LYRICS_PDFS = 6;

    protected $id;

    protected $create_time;

    protected $type;

    protected $url;

    protected $content;

    protected $is_favorite = 0;

    protected $file_name;

    protected $mime_type;

    protected $song;

    protected $user;

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

    public static function getFunctionalTypes()
    {
        return array(
            self::FUNCTIONAL_TYPE_ALL => 'Все',
            self::FUNCTIONAL_TYPE_LYRICS => 'Тексты',
            self::FUNCTIONAL_TYPE_PRESENTATION => 'Слайды',
            self::FUNCTIONAL_TYPE_AUDIO => 'Аудио',
            self::FUNCTIONAL_TYPE_VIDEO => 'Видео',
            self::FUNCTIONAL_TYPE_LYRICS_PDFS => 'PDF',
        );
    }

    public static function getKnownMimeTypes()
    {
        return array(
            'application/msword' => true,
            'application/vnd.ms-powerpoint' => true,
            'application/pdf' => true,
            'text-plain' => true
        );
    }

    public static function getMimeTypeSubstitute($mimeType)
    {
        $subst = array(
            'application/vnd.google-apps.presentation' => 'application/vnd.ms-powerpoint',
            'application/vnd.google-apps.document' => 'application/msword',
            'application/vnd.oasis.opendocument.text' => 'application/msword',
        );

        if(isset($subst[$mimeType])){
            return $subst[$mimeType];
        } else {
            return null;
        }
    }

    public function getMimeIconName()
    {
        if(!is_null($this->mime_type)){

            if(is_null($name = self::getMimeTypeSubstitute($this->mime_type))){
                $name = $this->mime_type;
            }

            if(isset(self::getKnownMimeTypes()[$name])){
                return str_replace('/', '-', $name) . '.svg';
            } else {
                return self::MIME_ICON_NAME_DEFAULT;
            }


        } else {
            return self::MIME_ICON_NAME_DEFAULT;
        }
    }
}
