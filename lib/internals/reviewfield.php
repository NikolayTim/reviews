<?
namespace SouthCoast\Reviews\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

class ReviewsFieldsTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'sc_reviews_fields';
	}

	public static function getMap()
	{
		return array (
				new Entity\IntegerField( 'ID', array (
						'primary' => true,
						'autocomplete' => true 
				) ),
				new Entity\IntegerField( 'SORT', array (
						'required' => true,
				) ),
				new Entity\StringField( 'NAME', array (
						'required' => true,
						'unique' => true 
				) ),
				new Entity\StringField( 'TITLE', array (
						'required' => true,
				) ),
				new Entity\StringField( 'TYPE', array (
						'required' => true,
				) ),
				new Entity\TextField( 'SETTINGS' ),
				new Entity\BooleanField( 'ACTIVE', array (
						'values' => array (
								'N',
								'Y' 
						),
				) ) 
		);
	}
}
