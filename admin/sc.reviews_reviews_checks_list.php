<?
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use SouthCoast\Reviews\Internals\ChecksReviewTable;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile( __FILE__ );
if(!Loader::includeModule('sc.reviews'))
    return false;

$accessLevel = $APPLICATION->GetGroupRight('sc.reviews');
if($accessLevel == "D")
    $APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

$sTableID = "sc_reviews_checks";
$oSort = new CAdminSorting( $sTableID, "ID", "desc" );
$lAdmin = new CAdminList( $sTableID, $oSort );

function CheckFilter()
{
    global $arrFilter, $lAdmin;
    foreach( $arrFilter as $f )
        global $$f;
    return count( $lAdmin->arFilterErrors )==0;
}

$arrFilter = Array (
    "find_id",
    "find_name",
    "find_value",
    "find_pattern"
);
$lAdmin->InitFilter( $arrFilter );
$arFilter = array ();

if(CheckFilter())
{
    if(isset($find_id) && strlen($find_id) > 0)
        $arFilter["ID"] = $find_id;

    if(isset($find_name) && strlen($find_name) > 0)
        $arFilter["NAME"] = $find_name;

    if(isset($find_value) && strlen($find_value) > 0)
        $arFilter["VALUE"] = $find_value;

    if(isset($find_pattern) && strlen($find_pattern) > 0)
        $arFilter["PATTERN"] = $find_pattern;

    if(empty( $arFilter['ID'] ))
        unset( $arFilter['ID'] );

    if(empty( $arFilter['NAME'] ))
        unset( $arFilter['NAME'] );

    if(empty( $arFilter['VALUE'] ))
        unset( $arFilter['VALUE'] );

    if(empty( $arFilter['PATTERN'] ))
        unset( $arFilter['PATTERN'] );
}

if($lAdmin->EditAction() && $accessLevel == "W")
{
    foreach($FIELDS as $ID => $arFields )
    {
        if(!$lAdmin->IsUpdated( $ID ))
            continue;

        $ID = IntVal( $ID );
        if($ID > 0)
        {
            foreach( $arFields as $key => $value )
                $arData[$key] = $value;

            $result = ChecksReviewTable::update( $ID, $arData );
            unset( $arData );
            if(!$result->isSuccess())
            {
                $lAdmin->AddGroupError(GetMessage("SC_REVIEWS_CHECKS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_CHECKS_NOT_FOUND"), $ID);
            }
            unset( $result );
        }
        else
            $lAdmin->AddGroupError(GetMessage("SC_REVIEWS_CHECKS_SAVE_ERROR")." ".GetMessage("SC_REVIEWS_CHECKS_NOT_FOUND"), $ID);
    }
}

if($arID = $lAdmin->GroupAction())
{
    if($_REQUEST['action_target']=='selected')
    {
        $rsData = ChecksReviewTable::getList( array (
            'select' => ['ID', 'NAME', 'VALUE', 'PATTERN', 'RESULT'],
            'filter' => $arFilter,
            'order' => [$by => $order]
        ) );
        while( $arRes = $rsData->Fetch() )
            $arID[] = $arRes['ID'];
    }

    foreach( $arID as $ID )
    {
        if(strlen($ID) <= 0)
            continue;

        $ID = IntVal( $ID );
        switch($_REQUEST['action'])
        {
            case "delete" :
                $result = ChecksReviewTable::delete( $ID );
                if(!$result->isSuccess())
                {
                    $lAdmin->AddGroupError(GetMessage("SC_REVIEWS_CHECKS_DEL_ERROR")." ".GetMessage("SC_REVIEWS_CHECKS_NOT_FOUND"), $ID);
                }
                unset( $result );
                break;
        }
    }
}

$rsData = ChecksReviewTable::getList( array (
    'select' => ['ID', 'NAME', 'VALUE', 'PATTERN', 'RESULT'],
    'filter' => $arFilter,
    'order' => [$by => $order]
) );
$rsData = new CAdminResult( $rsData, $sTableID );
$rsData->NavStart();
$lAdmin->NavText( $rsData->GetNavPrint( GetMessage( "SC_REVIEWS_CHECKS_NAV" ) ) );

$lAdmin->AddHeaders( array (
    array (
        "id" => "ID",
        "content" => GetMessage( "SC_REVIEWS_CHECKS_TABLE_ID" ),
        "sort" => "ID",
        "align" => "right",
        "default" => true
    ),
    array (
        "id" => "NAME",
        "content" => GetMessage( "SC_REVIEWS_CHECKS_TABLE_NAME" ),
        "sort" => "NAME",
        "align" => "right",
        "default" => true
    ),
    array (
        "id" => "VALUE",
        "content" => GetMessage( "SC_REVIEWS_CHECKS_TABLE_VALUE" ),
        "sort" => "VALUE",
        "align" => "right",
        "default" => true
    ),
    array (
        "id" => "PATTERN",
        "content" => GetMessage( "SC_REVIEWS_CHECKS_TABLE_PATTERN" ),
        "sort" => "PATTERN",
        "default" => true
    ),
    array (
        "id" => "RESULT",
        "content" => GetMessage( "SC_REVIEWS_CHECKS_TABLE_RESULT" ),
        "sort" => "RESULT",
        "default" => true
    )
) );

while( $arRes = $rsData->NavNext( true, "f_" ) )
{
    $row = & $lAdmin->AddRow( $f_ID, $arRes );

    if($accessLevel >= "W")
    {
        $row->AddViewField( "ID", '<a href="sc.reviews_reviews_checks_edit.php?ID='.$f_ID.'&lang='.LANG.'"">'.$f_ID.'</a>' );
        $row->AddInputField("NAME", $f_NAME);
        $row->AddInputField("VALUE", $f_VALUE);
        $row->AddInputField("PATTERN", $f_PATTERN);
        $row->AddInputField("RESULT", $f_RESULT);
    }
    else
    {
        $row->AddViewField("ID", $f_ID);
        $row->AddViewField("NAME", $f_NAME);
        $row->AddViewField("VALUE", $f_VALUE);
        $row->AddViewField("PATTERN", $f_PATTERN);
        $row->AddViewField("RESULT", $f_RESULT);
    }

    $arActions = Array ();
    if($accessLevel >= "W")
    {
        $arActions[] = array (
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => GetMessage( "SC_REVIEWS_CHECKS_EDIT" ),
            "ACTION" => $lAdmin->ActionRedirect( "sc.reviews_reviews_checks_edit.php?ID=".$f_ID )
        );

        $arActions[] = array (
            "ICON" => "delete",
            "TEXT" => GetMessage( "SC_REVIEWS_CHECKS_DEL" ),
            "ACTION" => "if(confirm('".GetMessage( 'SC_REVIEWS_CHECKS_DEL_CONF' )."')) ".$lAdmin->ActionDoGroup( $f_ID, "delete" )
        );
    }

    $arActions[] = array (
        "SEPARATOR" => true
    );

    if(is_set( $arActions[count( $arActions )-1], "SEPARATOR" ))
        unset( $arActions[count( $arActions )-1] );

    $row->AddActions( $arActions );
}

$lAdmin->AddFooter( array (
    array (
        "title" => GetMessage( "SC_REVIEWS_CHECKS_LIST_SELECTED" ),
        "value" => $rsData->SelectedRowsCount()
    ),
    array (
        "counter" => true,
        "title" => GetMessage( "SC_REVIEWS_CHECKS_LIST_CHECKED" ),
        "value" => "0"
    )
) );

$arGroupActions = [];
if($accessLevel >= "W")
    $arGroupActions["delete"] = GetMessage("SC_REVIEWS_CHECKS_LIST_DELETE");

$lAdmin->AddGroupActionTable($arGroupActions);

$arContextMenu = [];
if($accessLevel >= "W")
    $arContextMenu = array (
        array (
            "TEXT" => GetMessage( "SC_REVIEWS_CHECKS_POST_ADD_TEXT" ),
            "LINK" => "sc.reviews_reviews_checks_edit.php?lang=".LANG,
            "TITLE" => GetMessage( "SC_REVIEWS_CHECKS_POST_ADD_TITLE" ),
            "ICON" => "btn_new"
        )
    );

$lAdmin->AddAdminContextMenu($arContextMenu);

$lAdmin->CheckListMode();
$APPLICATION->SetTitle( GetMessage( "SC_REVIEWS_CHECKS_TITLE" ) );

require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter( $sTableID."_filter", array (
    GetMessage( "SC_REVIEWS_CHECKS_ID" ),
    GetMessage( "SC_REVIEWS_CHECKS_NAME" ),
    GetMessage( "SC_REVIEWS_CHECKS_VALUE" ),
    GetMessage( "SC_REVIEWS_CHECKS_PATTERN" )
) );
?>

    <form name="find_form" method="get"	action="<?echo $APPLICATION->GetCurPage();?>">
        <?$oFilter->Begin();?>
        <tr>
            <td><?=GetMessage("SC_REVIEWS_CHECKS_ID")?>:</td>
            <td><input type="text" name="find_id" size="47"	value="<?echo htmlspecialchars($find_id)?>">
                <?unset( $find_id );?>
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("SC_REVIEWS_CHECKS_NAME")?>:</td>
            <td><input type="text" name="find_name" size="47"	value="<?echo htmlspecialchars($find_name)?>">
                <?unset( $find_name );?>
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("SC_REVIEWS_CHECKS_VALUE")?>:</td>
            <td><input type="text" name="find_value" size="47" value="<?echo htmlspecialchars($find_value)?>">
                <?unset( $find_value );?>
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("SC_REVIEWS_CHECKS_PATTERN")?>:</td>
            <td><input type="text" name="find_pattern" size="47" value="<?echo htmlspecialchars($find_pattern)?>">
                <?unset( $find_pattern );?>
            </td>
        </tr>
        <?
        $oFilter->Buttons( array (
            "table_id" => $sTableID,
            "url" => $APPLICATION->GetCurPage(),
            "form" => "find_form"
        ) );
        $oFilter->End();
        ?>
    </form>

<?

$lAdmin->DisplayList();

require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
