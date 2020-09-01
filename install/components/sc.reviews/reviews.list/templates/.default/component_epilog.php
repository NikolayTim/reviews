<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if($USER->IsAuthorized())
    $user_id = $USER->GetID();
else
    $user_id = 0;

$accessLevel = $arResult["USERTYPE"];
?>

<script>
    var BX_USER_ID = '';
    var USER_ID = parseInt('<?=$user_id?>');
    var accessLevel = '<?=$accessLevel?>';

    $(document).ready(function() {
        BX_USER_ID = '<?=$_COOKIE["BX_USER_ID"]?>';
        sendRequestToUpdateReviews();
    });
</script>



