<?
namespace SouthCoast\Reviews\Internals;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Loader;

class ReviewsTable extends Entity\DataManager
{
    public static function getTableName()
	{
		return 'sc_reviews_reviews';
	}

	public static function getMap() {
		return array(
				new Entity\IntegerField( 'ID', array(
						'primary' => true,
						'autocomplete' => true 
				) ),
				new Entity\IntegerField( 'ID_ELEMENT', array(
						'required' => true,
				) ),
				new Entity\StringField( 'XML_ID_ELEMENT' ),
				new Entity\IntegerField( 'ID_USER', array(
						'required' => true,
				) ),
				new Entity\ReferenceField( 'USER',
						'\Bitrix\Main\UserTable',
						array("=this.USER" => "ref.ID")
				),
                new Entity\StringField( 'BX_USER_ID', array(
                    'required' => true,
              ) ),
				new Entity\IntegerField( 'RATING', array(
				    'required' => true,
				) ),
				new Entity\StringField( 'TITLE', array(
				    'default_value' => ' '
                ) ),
				new Entity\TextField( 'PLUS' ),
				new Entity\TextField( 'MINUS' ),
				new Entity\TextField( 'ANSWER' ),
				new Entity\IntegerField( 'LIKES', array(
					'required' => true,
                    'default_value' => 0
				) ),
				new Entity\IntegerField( 'DISLIKES', array(
					'required' => true,
                    'default_value' => 0
                ) ),
				new Entity\DatetimeField( 'DATE_CREATION', array(
                    'default_value' => new DateTime(),
                ) ),
				new Entity\DatetimeField( 'DATE_CHANGE' ),
				new Entity\BooleanField( 'MODERATED', array(
						'values' => array(
								'N',
								'Y' 
						),
                        'default_value' => 'N'
				) ),
				new Entity\IntegerField( 'MODERATED_BY', array(
				    'default_value' => 0
                ) ),
				new Entity\BooleanField( 'ACTIVE', array(
                    'values' => array(
                            'N',
                            'Y'
                    ),
                    'default_value' => 'Y'
				) ),
				new Entity\BooleanField( 'RECOMMENDATED', array(
						'values' => array(
								'N',
								'Y' 
						),
				) ),
				new Entity\IntegerField( 'ID_FORUM_MESSAGE' ),
				new Entity\StringField( 'IP_USER', array(
				    'validation' => function () {
									return array(
											new Entity\Validator\RegExp( '/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/' ) 
									);
								},
				) ),
				new Entity\TextField( 'MULTIMEDIA', array(
						'serialized' => true 
				) ),
                new Entity\TextField( 'FILES', array(
                    'serialized' => true,
                    'save_data_modification' => function () {
                        return array(
                            function ($value) {
                                $arNewValue = [];
                                $arUnserializedValue = unserialize($value);
                                foreach($arUnserializedValue as $valueFile)
                                {
                                    if(intval($valueFile) <= 0)
                                    {
                                        $valueFile = str_replace('/upload/', '', $valueFile);
                                        $arPathFile = explode('/', $valueFile);
                                        $cntPathFile = count($arPathFile);
                                        $fileName = $arPathFile[$cntPathFile - 1];

                                        $filePath = "";
                                        for($j = 0; $j < $cntPathFile - 2; $j++)
                                            $filePath = $filePath . $arPathFile[$j] . "/";

                                        $filePath = $filePath . $arPathFile[$cntPathFile - 2]; 

                                        $fileID = \Bitrix\Main\FileTable::getList([
                                            'select' => ['ID'],
                                            'filter' => ["SUBDIR" => $filePath, "ORIGINAL_NAME" => $fileName]
                                        ])->fetch()["ID"];
                                        if(intval($fileID) > 0) 
                                            $arNewValue[] = $fileID;
                                    }
                                    else
                                        $arNewValue[] = $valueFile;
                                }
                                $newValue = serialize($arNewValue);

                                return $newValue;
                            }
                        );
                    },
                ) ),
				new Entity\IntegerField( 'SHOWS', array(
				    'default_value' => 0
                ) ),
		);
	}

    public static function checkFields($result, $primary, array $data)
    {
        parent::checkFields($result, $primary, $data);
        $arErrors = checkReview($data); 
        foreach($arErrors as $txtError)
            $result->addError(new Entity\EntityError($txtError));
        return $result;
    }

    public static function OnAfterAdd(Entity\Event $event)
    {
        $reviewID = $event->getParameter("primary");
        $data = $event->getParameter("fields");
        clearCacheElement($data["ID_ELEMENT"], $reviewID['ID']);
    }

    public static function OnAfterUpdate(Entity\Event $event)
    {
        $reviewID = $event->getParameter("primary");
        $data = $event->getParameter("fields");
        clearCacheElement($data["ID_ELEMENT"], $reviewID['ID']);
    }

    public static function OnDelete(Entity\Event $event)
    {
        $reviewID = $event->getParameter("primary");
        $elementID = self::getList([
            'select' => ['ID_ELEMENT'],
            'filter' => ['ID' => $reviewID]
        ])->fetch()["ID_ELEMENT"];
        clearCacheElement($elementID, $reviewID);
    }
}