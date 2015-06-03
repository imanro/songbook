<?php
namespace Ez\Doctrine\DBAL\Types;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Type that maps an Timestamp MySQL to php objects
 *
 * @author manro
 *
 */
class Enum extends Type
{
    const ENUM = 'enum';

    const RANGE = 'range';

    public function getName ()
    {
        return self::ENUM;
    }

    public function getSQLDeclaration (array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::ENUM . ' (\'' . implode( '\', \'', array_map(function($value){ return trim($value); }, explode( ',', $fieldDeclaration[self::RANGE]))) . '\')';
    }
}
