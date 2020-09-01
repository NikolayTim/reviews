<?
namespace SouthCoast\Reviews\Internals;
use Bitrix\Main\Entity,
    Bitrix\Main\Type\DateTime;

class ReviewsBansTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'sc_reviews_bans';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			new Entity\IntegerField('ID_USER', array(
				'validation' => function() {    
					return array(
                            function ($value, $primary, $row, $field)
                            {
                                $res = true;
                                if(intval($value) > 0)
                                {
                                    $arFilterUser = [
                                        'ID_USER' => $value,
                                        'ACTIVE' => 'Y',
                                        '>DATE_TO' => date('d.m.Y H:i:s')
                                    ];

                                    if(array_key_exists("ID", $primary) && intval($primary["ID"]) > 0)
                                        $arFilterUser["!ID"] = $primary["ID"];

                                    $rsUser = self::getList(['filter' => $arFilterUser]);
                                    if($arUser = $rsUser->fetch())
                                        $res = "Пользователь c ID: ".$value." уже забанен!";
                                }
                                return $res;
							}
						);
					}
				)
			),
			new Entity\ReferenceField( 'USER',
					'\Bitrix\Main\UserTable',
					array("=this.USER" => "ref.ID")
			),
            new Entity\StringField( 'BX_USER_ID', array(
                'required' => true,
            ) ),
            new Entity\StringField('IP', array(
				'validation' => function() {
					return array(
								new Entity\Validator\RegExp('/^((25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}|())$/')
						);
				}
			)),
			new Entity\IntegerField('ID_MODERATOR', array(
	            'default_value' => 0
			)),
			new Entity\DatetimeField('DATE_CREATION'),
			new Entity\DatetimeField('DATE_CHANGE', array(
                    'default_value' => new DateTime()
			)),
			new Entity\DatetimeField('DATE_TO'),
			new Entity\TextField('REASON'),
			new Entity\BooleanField('ACTIVE', array(
				'values' => array('N', 'Y'),
			)),
		);
	}
}