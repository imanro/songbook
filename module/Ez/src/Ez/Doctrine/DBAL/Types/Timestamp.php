<?php
namespace Ez\Doctrine\DBAL\Types;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Type that maps an Timestamp MySQL to php objects
 *
 * @author Jordan Samouh
 *
 */
class Timestamp extends Type
{
    const TIMESTAMP = 'timestamp';

    public function getName ()
    {
        return self::TIMESTAMP;
    }

    public function getSQLDeclaration (array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::TIMESTAMP;
    }

    public function convertToDatabaseValue ($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToPHPValue ($value, AbstractPlatform $platform)
    {
        return $value;
    }
}