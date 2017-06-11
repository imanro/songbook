<?php
namespace Ez\Doctrine\DBAL\Types;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Type that maps an Timestamp MySQL to php objects
 *
 * @author manro
 * Oops, timestamps supported by native datetime if "version" annotation given, see MysqlPlatform::getDateTimeTypeDeclaration.
 * But, this type adds -> int conversion :)
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
        return 'TIMESTAMP';
    }

    public function convertToDatabaseValue ($value, AbstractPlatform $platform)
    {
        if(is_int($value)){
            return date('Y-m-d H:i:s', $value );
        } else {
            return $value;
        }
    }

    public function convertToPHPValue ($value, AbstractPlatform $platform)
    {
        return strtotime($value);
    }
}