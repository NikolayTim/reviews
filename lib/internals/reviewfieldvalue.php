<?
namespace SouthCoast\Reviews\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use SouthCoast\Reviews\Internals\ReviewsFieldsTable;

class ReviewsFieldsValuesTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'sc_reviews_fields_value';
	}

	public static function getMap() {
		return array(
				new Entity\IntegerField( 'ID', array(
						'primary' => true,
						'autocomplete' => true 
				) ),
				new Entity\IntegerField( 'REVIEW_ID', array (
						'required' => true,
				) ),
				new Entity\ReferenceField( 'REVIEW',
						'\SouthCoast\Reviews\Internals\ReviewsTable',
						array("=this.REVIEW" => "ref.ID")
				),
				new Entity\IntegerField( 'FIELD_ID', array (
						'required' => true,
				) ),
				new Entity\ReferenceField( 'FIELD',
						'\SouthCoast\Reviews\Internals\ReviewsFieldsTable',
						array("=this.REVIEW" => "ref.ID")
				),
				new Entity\StringField( 'VALUE', array(
                    'save_data_modification' => function () {
                        return array(
                            function ($value) {
                                if(preg_match('/^(0[1-9]|[12][0-9]|3[01])[\.](0[1-9]|1[012])[\.](19|20)\d\d$/', $value))
                                {
                                    $dateSplit = explode('.',$value);
                                    $value = $dateSplit[2] . "-" . $dateSplit[1] . "-" . $dateSplit[0];
                                }
                                return $value;
                            }
                        );
                    },
                    'fetch_data_modification' => function () {
                        return array(
                            function ($value) {
                                if(preg_match('/^[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])$/', $value))
                                {
                                    $dateSplit = explode('-',$value);
                                    $value = $dateSplit[2] . "." . $dateSplit[1] . "." . $dateSplit[0];
                                }
                                return $value;
                            }
                        );
                    },
                    'validation' => function() {
                        return array(
                            function ($value, $primary, $row, $field) {
                                $nameField = ReviewsFieldsTable::getList([
                                    'select' => ['NAME'],
                                    'filter' => ['ID' => $row['FIELD_ID']]
                                ])->fetch()["NAME"];

                                if(strpos($nameField, "DATE") !== false)
                                {
                                    if(!preg_match('/^(0[1-9]|[12][0-9]|3[01])[\.](0[1-9]|1[012])[\.](19|20)\d\d$/', $value))
                                        return 'Некорректное значение даты пользовательского поля!';
                                }
                                return true;
                            }
                        );
                    }
                ) ),
		);
	}

    public static function checkFields($result, $primary, array $data)
    {
        parent::checkFields($result, $primary, $data);
        $nameField = ReviewsFieldsTable::getList([
            'select' => ['NAME'],
            'filter' => ['ID' => $data['FIELD_ID']]
        ])->fetch()["NAME"];

        if(strpos($nameField, "DATE") === false)
        {
            $rsChecks = \SouthCoast\Reviews\Internals\ChecksReviewTable::getList([
                'select' => ['ID', 'NAME', 'VALUE', 'PATTERN', 'RESULT']
            ]);
            while($arCheck = $rsChecks->fetch())
                $arChecks[$arCheck['ID']] = $arCheck;

            $arErrors = checkTextField($nameField, $data['VALUE'], $arChecks);
            foreach($arErrors as $txtError)
                $result->addError(new Entity\EntityError($txtError));

            if(intval($_REQUEST['ID']) <= 0 && $arErrors)
            {
                deleteReviewWithErrors($data['REVIEW_ID']);
            }
        }
        return $result;
    }
}