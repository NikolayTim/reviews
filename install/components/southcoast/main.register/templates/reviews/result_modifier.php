<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;
	global $USER;
	if(!is_object($USER)) $USER=new CUser;
$arResult['SHOW_FIELDS'] = array('EMAIL','NAME','PASSWORD','LAST_NAME','CONFIRM_PASSWORD','LOGIN');
$arResult['SHOW_FIELDS'] = array_unique (array_merge ($arParams['SHOW_FIELDS'],$arResult['SHOW_FIELDS']));
?>