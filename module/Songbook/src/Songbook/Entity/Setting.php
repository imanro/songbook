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
class Setting extends AbstractEntity
{
    const VAR_GDRIVE_ROOT_FOLDER_NAME = 'gdriveRootFolderName';

    protected $name;

    protected $value;

    protected $user;

    public function setValue(\User\Entity\User $user, $name, $value)
    {
        $this->user = $user;
        $this->name = $name;
        $this->value = $value;
        return $this;
    }
}