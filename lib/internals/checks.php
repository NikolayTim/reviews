<?
namespace SouthCoast\Reviews\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

class ChecksReviewTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'sc_reviews_checks';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            )),
            new Entity\StringField( 'NAME', array(
                'default_value' => ' '
            ) ),
            new Entity\StringField( 'VALUE', array(
                'default_value' => ' '
            ) ),
            new Entity\StringField( 'PATTERN', array(
                'default_value' => ' '
            ) ),
            new Entity\StringField( 'RESULT', array(
                'default_value' => ' '
            ) ),
        );
    }
}