<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class sc_reviews extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__ . "/version.php");
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        $this->MODULE_ID = 'sc.reviews';
        $this->MODULE_NAME = Loc::getMessage("SC_RV_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("SC_RV_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("SC_RV_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("SC_RV_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = "Y";
    }

    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        if (!Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsTable::getConnectionName())->isTableExists(
            Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsTable')->getDBTableName()
        )
        ) {
            Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsTable')->createDbTable();
        }
		
        if (!Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsFieldsTable::getConnectionName())->isTableExists(
            Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsFieldsTable')->getDBTableName()
        )
        ) {
            Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsFieldsTable')->createDbTable();

            Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsFieldsTable::getConnectionName())->
            queryExecute('insert into ' . Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsFieldsTable')->getDBTableName() .
                '(`SORT`, `NAME`, `TITLE`, `TYPE`, `SETTINGS`, `ACTIVE`) values(100, "FIO", "ФИО", "textbox", "", "Y")');
            Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsFieldsTable::getConnectionName())->
            queryExecute('insert into ' . Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsFieldsTable')->getDBTableName() .
                '(`SORT`, `NAME`, `TITLE`, `TYPE`, `SETTINGS`, `ACTIVE`) values(100, "REST_DATE_FROM", "Отдыхал(а) с", "textbox", "", "Y")');
            Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsFieldsTable::getConnectionName())->
            queryExecute('insert into ' . Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsFieldsTable')->getDBTableName() .
                '(`SORT`, `NAME`, `TITLE`, `TYPE`, `SETTINGS`, `ACTIVE`) values(100, "REST_DATE_TO", "по", "textbox", "", "Y")');
        }

        if (!Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable::getConnectionName())->isTableExists(
            Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable')->getDBTableName()
        )
        ) {
            Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable')->createDbTable();
        }	
		
        if (!Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsBansTable::getConnectionName())->isTableExists(
            Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsBansTable')->getDBTableName()
        )
        ) {
            Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsBansTable')->createDbTable();
        }

        if (!Application::getConnection(\SouthCoast\Reviews\Internals\ChecksReviewTable::getConnectionName())->isTableExists(
            Base::getInstance('\SouthCoast\Reviews\Internals\ChecksReviewTable')->getDBTableName()
        )
        ) {
            Base::getInstance('\SouthCoast\Reviews\Internals\ChecksReviewTable')->createDbTable();

            Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsFieldsTable::getConnectionName())->
            queryExecute('insert into ' . Base::getInstance('\SouthCoast\Reviews\Internals\ChecksReviewTable')->getDBTableName() .
                '(`NAME`, `VALUE`, `PATTERN`, `RESULT`) values("Автобан", "", "\'^бан$\'", "BANED")');
            Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsFieldsTable::getConnectionName())->
            queryExecute('insert into ' . Base::getInstance('\SouthCoast\Reviews\Internals\ChecksReviewTable')->getDBTableName() .
                '(`NAME`, `VALUE`, `PATTERN`, `RESULT`) values("Предупреждение", "", "\'^слово$\'", "WARNING")');
        }
    }

    function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsTable::getConnectionName())->
        queryExecute('drop table if exists ' . Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsTable')->getDBTableName());

        Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsFieldsTable::getConnectionName())->
        queryExecute('drop table if exists ' . Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsFieldsTable')->getDBTableName());

        Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable::getConnectionName())->
        queryExecute('drop table if exists ' . Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsFieldsValuesTable')->getDBTableName());

        Application::getConnection(\SouthCoast\Reviews\Internals\ReviewsBansTable::getConnectionName())->
        queryExecute('drop table if exists ' . Base::getInstance('\SouthCoast\Reviews\Internals\ReviewsBansTable')->getDBTableName());

        Application::getConnection(\SouthCoast\Reviews\Internals\ChecksReviewTable::getConnectionName())->
        queryExecute('drop table if exists ' . Base::getInstance('\SouthCoast\Reviews\Internals\ChecksReviewTable')->getDBTableName());

        Option::delete($this->MODULE_ID);
    }

    function InstallFiles($arParams = array())
    {
		$resCopy = CopyDirFiles( $_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true );
		if($resCopy)
        {
            CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/local/components/".$this->MODULE_ID, true);
            $resCopy = CopyDirFiles( $_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/components',
                $_SERVER['DOCUMENT_ROOT'].'/local/components/', true, true );
        }

        return $resCopy;
    }

    function UnInstallFiles()
    {
		DeleteDirFiles( $_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin' );
        $resDel = DeleteDirFilesEx( '/local/components/'.$this->MODULE_ID );

        return $resDel;
    }

    function InstallEvents()
    {
        $obEventType = new CEventType();
        $obEventType->Add( array(
				"EVENT_NAME" => Loc::getMessage('SC_RV_ADD_MAILING_EVENT_TYPE_NAME'),
				"NAME" => Loc::getMessage('SC_RV_ADD_MAILING_EVENT_TYPE'),
				"LID" => "ru",
				"DESCRIPTION" => Loc::getMessage('SC_RV_ADD_MAILING_EVENT_TYPE_DESCRIPTION')
		) );

        $obEventType->Add( array(
            "EVENT_NAME" => Loc::getMessage('SC_RV_REPEAT_MAILING_EVENT_TYPE_NAME'),
            "NAME" => Loc::getMessage('SC_RV_REPEAT_MAILING_EVENT_TYPE'),
            "LID" => "ru",
            "DESCRIPTION" => Loc::getMessage('SC_RV_ADD_MAILING_EVENT_TYPE_DESCRIPTION')
        ) );

        $rsSites = CSite::GetList($by = "sort", $order = "desc", Array("ACTIVE" => "Y"));
		while($arSite = $rsSites->Fetch())
		{
			$oEventMessage = new CEventMessage();
			$oEventMessage->Add( array(
					'ACTIVE' => 'Y',
					'EVENT_NAME' => Loc::getMessage('SC_RV_ADD_MAILING_EVENT_TYPE_NAME'),
					'LID' => $arSite['LID'],
					'EMAIL_FROM' => '#EMAIL_FROM#',
					'EMAIL_TO' => '#EMAIL_TO#',
					'SUBJECT' => Loc::getMessage('SC_RV_ADD_MAILING_EVENT_TYPE'),
					'MESSAGE' => Loc::getMessage('SC_RV_ADD_MAILING_EVENT_MESSAGE'),
					'BODY_TYPE' => 'html'
			) );

            $oEventMessage = new CEventMessage();
            $oEventMessage->Add( array(
                'ACTIVE' => 'Y',
                'EVENT_NAME' => Loc::getMessage('SC_RV_REPEAT_MAILING_EVENT_TYPE_NAME'),
                'LID' => $arSite['LID'],
                'EMAIL_FROM' => '#EMAIL_FROM#',
                'EMAIL_TO' => '#EMAIL_TO#',
                'SUBJECT' => Loc::getMessage('SC_RV_REPEAT_MAILING_EVENT_TYPE'),
                'MESSAGE' => Loc::getMessage('SC_RV_REPEAT_MAILING_EVENT_MESSAGE'),
                'BODY_TYPE' => 'html'
            ) );
        }
    }

    function UnInstallEvents()
    {
		$arFilter = Array(
				'ACTIVE' => 'Y',
                [
                    'LOGIC' => 'OR',
                    ['EVENT_NAME' => Loc::getMessage('SC_RV_ADD_MAILING_EVENT_TYPE_NAME')],
                    ['EVENT_NAME' => Loc::getMessage('SC_RV_REPEAT_MAILING_EVENT_TYPE_NAME')],
                ]
		);
		$rsMess = CEventMessage::GetList($by = $Site, $order = "desc", $arFilter);
		while($arMess = $rsMess->fetch())
		{
			$oEventMessage = new CEventMessage();
			$oEventMessage->Delete(intval($arMess['ID']));
		}

		$obEventType = new CEventType();
		$obEventType->Delete(Loc::getMessage('SC_RV_ADD_MAILING_EVENT_TYPE_NAME'));
        $obEventType->Delete(Loc::getMessage('SC_RV_REPEAT_MAILING_EVENT_TYPE_NAME'));
    }
	
	function InstallOptions()
	{
		$arDefaultOptions = \Bitrix\Main\Config\Option::getDefaults($this->MODULE_ID);

		$rsSites = \Bitrix\Main\SiteTable::GetList(["filter" => Array("ACTIVE" => "Y")]);
		while($arSite = $rsSites->Fetch())
		{
			foreach($arDefaultOptions as $nameOption => $valueDefault)
				\Bitrix\Main\Config\Option::set($this->MODULE_ID, $nameOption, $valueDefault, $arSite['LID']);
		}
	}
	
	function UnInstallOptions()
	{
		COption::RemoveOption($this->MODULE_ID);
	}

    public function doInstall()
    {
        global $APPLICATION;
        if ($this->isVersionD7()) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();
			
			$this->InstallOptions();
			
        } else {
            $APPLICATION->ThrowException(Loc::getMessage("SC_RV_INSTALL_ERROR_VERSION"));
        }
    }

    public function doUninstall()
    {
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        if ($request["step"] < 2)
            $APPLICATION->IncludeAdminFile("", $this->GetPath() . "/install/unstep.php");
        elseif ($request["step"] == 2) 
		{
			$this->UnInstallOptions();

            $this->UnInstallEvents();
            $this->UnInstallFiles();

            if ($request["savedata"] != "Y")
                $this->UnInstallDB();
            
            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        }
		
    }
	
	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","R","T","V","W"),
			"reference" => array(
				"[D] ".GetMessage("SC_RV_RIGHT_DENIED"),
				"[R] ".GetMessage("SC_RV_RIGHT_SHOW"),
				"[T] ".GetMessage("SC_RV_RIGHT_MODERATION"),
				"[V] ".GetMessage("SC_RV_RIGHT_EDIT"),
				"[W] ".GetMessage("SC_RV_RIGHT_ADMIN"))
			);
		return $arr;
	}
}
?>