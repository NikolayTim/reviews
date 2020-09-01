<?
define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/123.txt");
use Bitrix\Main\Mail\Event;
use \Bitrix\Main\Type\DateTime;

\Bitrix\Main\Loader::registerAutoLoadClasses(
    'sc.reviews',
    array(
        '\SouthCoast\Reviews\Internals\ReviewsTable' => 'lib/internals/review.php',
        '\SouthCoast\Reviews\Internals\ReviewsFieldsTable' => 'lib/internals/reviewfield.php',
        '\SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable' => 'lib/internals/reviewfieldvalue.php',	
        '\SouthCoast\Reviews\Internals\ReviewsBansTable' => 'lib/internals/bans.php',
        '\SouthCoast\Reviews\Internals\ChecksReviewTable' => 'lib/internals/checks.php',
    )
);

function bannedUser($arUser)
{
    global $CACHE_MANAGER;

    $errorsBan = [];
    $banDaysOption = \Bitrix\Main\Config\Option::getRealValue("sc.reviews", "REVIEWS_BAN_DAYS", SITE_ID);
    if(empty($banDaysOption) || intval($banDaysOption) <= 0)
        $banDaysOption = 12;

    $arFields = Array(
        "DATE_CREATION" => new DateTime(),
        "DATE_CHANGE" => new DateTime(),
        "DATE_TO" => new DateTime( date('d.m.Y H:i:s',strtotime('+' . $banDaysOption . ' day')) ),
        "ACTIVE" => 'Y',
        "ID_MODERATOR" => $arUser['ID_MODERATOR'],
        "ID_USER" => $arUser['ID_USER'],
        "IP" => $arUser['IP_USER'],
        "BX_USER_ID" => $arUser['BX_USER_ID']
    );
    $resBan = SouthCoast\Reviews\Internals\ReviewsBansTable::add($arFields);

    /*  Ошибка добавления пользователя в черный список! Отправить письмо или написать в журнал событий??? */
    if (!$resBan->isSuccess())
        $errBan = $resBan->getErrorMessages();

    foreach($errBan as $strBan)
        $errorsBan["BANED"] = $errorsBan["BANED"] . $strBan . "<br>";

    return $errorsBan;
}

/*  Проверки текстовых полей, т.е. "PLUS", "MINUS" в sc_reviews_reviews и
    все пользовательские поля в названии которых нет строки "DATE"
    Проверка на SQL-инъекцию, нецензурные выражения, может быть что-то еще */
function checkTextField($nameField, $valueField, $arChecks)
{
    $arRes = [];
    if(isset($valueField) && strlen($valueField) > 0)
    {
        foreach($arChecks as $arCheck)
            if(preg_match($arCheck["PATTERN"], $valueField))
                $arRes[$arCheck["RESULT"]] = $arRes[$arCheck["RESULT"]] . "Поле: " . $nameField . " не прошло проверку: " . $arCheck["NAME"] . "<br>";
    }
    return $arRes;
}

function findCountReviews($idElement, $codeField, $valueField)
{
    $countReviews = SouthCoast\Reviews\Internals\ReviewsTable::getList([
        'select' => ['ID'],
        'filter' => ['ID_ELEMENT' => $idElement, $codeField => $valueField, 'ACTIVE' => 'Y']
    ])->getSelectedRowsCount();
    return $countReviews;
}

function sendRepeatMessage($arReview)
{
    $arElement = \Bitrix\Iblock\ElementTable::getList([
        'select' => ['NAME', 'CODE'],
        'filter' => ['ID' => $arReview['ID_ELEMENT']]
    ])->fetch();

    $arEventFields = [
        "PLUS"         => $arReview["PLUS"],
        "MINUS"        => $arReview["MINUS"],
        "RATING"       => $arReview["RATING"],
        "ELEMENT_NAME" => $arElement["NAME"],
        "ELEMENT_LINK" => SITE_SERVER_NAME . '/rus/' . $arElement["CODE"] . '.html',
        "ID_USER"      => $arReview["ID_USER"] . ' (BX_USER_ID: ' . $arReview["BX_USER_ID"] . ')',
    ];
    Event::send([
        "EVENT_NAME" => "SC_REVIEW_REPEAT_MAILING_EVENT_SEND",
        "LID" => SITE_ID,
        "C_FIELDS" => $arEventFields
    ]);
}

/*  Проверка отзыва: проверка на повторный отзыв, добавление в черный список,
    может быть что-то еще */
function checkReview($arReview)
{
    $arErrors = [];
    $cntReviews = 0;

    global $USER;
    if(!is_object($USER))
        $USER = new CUser;

    if(isset($arReview['BX_USER_ID']) && strlen($arReview['BX_USER_ID']) > 0)
        $bx_user_id = $arReview['BX_USER_ID'];
    else
        $bx_user_id = $_COOKIE['BX_USER_ID'];

    if(isset($arReview['IP_USER']) && strlen($arReview['IP_USER']) > 0)
        $ip_user = $arReview['IP_USER'];
    else
        $ip_user = $_SERVER["REMOTE_ADDR"];

    // Получаем все проверки для текстовых полей, чтобы не повторять один и тот же запрос несколько раз
    $rsChecks = SouthCoast\Reviews\Internals\ChecksReviewTable::getList([
        'select' => ['ID', 'NAME', 'VALUE', 'PATTERN', 'RESULT']
    ]);
    while($arCheck = $rsChecks->fetch())
        $arChecks[$arCheck['ID']] = $arCheck;

    // Получаем все поля отзывов, кроме пользовательских полей
    $arObjReviewFields = SouthCoast\Reviews\Internals\ReviewsTable::getMap();
    foreach($arObjReviewFields as $keyField => $objField)
        $arReviewFields[$keyField] = $objField->getName();

    // Цикл по всем полям отзыва, в том числе пользовательским:
    // выбираем "PLUS", "MINUS" и все текстовые пользовательские поля и проверяем их
    // Пользовательские поля оканчиваются на "_VAL" и не содержат "DATE"
    foreach($arReview as $codeField => $valueField)
    {
        if( in_array($codeField, ["PLUS", "MINUS"]) )
        {
            $arErr = checkTextField($codeField, $valueField, $arChecks);
            foreach($arErr as $keyErr => $strErr)
                $arErrors[$keyErr] = $arErrors[$keyErr] . $strErr;
        }
    }

    // Повторный отзыв ищем: если пользователь авторизован и ID_USER > 0, то по ID-пользователя, иначе - по $_COOKIE['BX_USER_ID']
    if($USER->IsAuthorized())
        $cntReviews = findCountReviews($arReview['ID_ELEMENT'], 'ID_USER', $USER->GetID());

    if($cntReviews > 0)
        sendRepeatMessage($arReview);
    else
    {
        $cntReviews = findCountReviews($arReview['ID_ELEMENT'], 'BX_USER_ID', $bx_user_id); //$_COOKIE['BX_USER_ID']);
        if($cntReviews > 0)
            sendRepeatMessage($arReview);
    }

    // После всех проверок анализируем результаты проверок и если надо - баним пользователя или что-то еще делаем
    if(array_key_exists('BANED', $arErrors))
    {
        $arUser = [
            "BX_USER_ID" => $bx_user_id,
            "IP_USER" => $ip_user,
            "ID_MODERATOR" => $arReview["MODERATED_BY"] 
        ];

        if(intval($arReview["MODERATED_BY"]) <= 0)
            $arUser["ID_MODERATOR"] = 0;

        if($USER->IsAuthorized())
            $arUser["ID_USER"] = $USER->GetID();
        else
            $arUser["ID_USER"] = 0;

        $arErrors = array_merge($arErrors, bannedUser($arUser));

        // Если автоматически баним пользователя, то никаких ошибок не показываем???
        // Может быть отправлять сообщение или запись в журнал событий???
        unset($arErrors);
        $arErrors["BANED"] = "BANED";
    }
    return $arErrors;
}

/* Сброс тегированного кеша и html-кеша компонента (reviews.list) по элементу (объекту) */
function clearCacheElement($elementID, $reviewID)
{
    global $CACHE_MANAGER;

    CModule::IncludeModule('main');

    $componentCachePathList = "/" . SITE_ID . "/sc.reviews/reviews.list/" . $elementID . "/";
    $obCacheList = new CPHPCache;
    $obCacheList->CleanDir($componentCachePathList, "cache");
    BXClearCache(true, $componentCachePathList);

    $componentCachePathAdd = "/" . SITE_ID . "/sc.reviews/reviews.add/" . $reviewID . "/";
    $obCacheAdd = new CPHPCache;
    $obCacheAdd->CleanDir($componentCachePathAdd, "cache");
    BXClearCache(true, $componentCachePathAdd);

    if(defined("BX_COMP_MANAGED_CACHE"))
    {
        $CACHE_MANAGER->ClearByTag('sc.reviews_element_id_' . $elementID);
        $CACHE_MANAGER->ClearByTag('sc.reviews_review_id_' . $reviewID);
    }
}

/* Удаление "частично" сохраненного отзыва (новый отзыв, при сохранении которого произошли ошибки в проверках) */
function deleteReviewWithErrors($reviewID)
{
    // Сначала удаляем дополнительные поля
    $dbReviewFieldsValues = \SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable::getList([
        'select' => ['ID'],
        'filter' => ['REVIEW_ID' => $reviewID]
    ]);
    while($arReviewFieldsValues = $dbReviewFieldsValues->fetch())
        \SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable::delete($arReviewFieldsValues['ID']);

    // Удаляем сам отзыв
    \SouthCoast\Reviews\Internals\ReviewsTable::delete($reviewID);
}
