<?php
$isAdminMode = TRUE;
include_once "D:/xampp/htdocs/kotei3/SPFW/inc/setting.properties";
include_once _INC_DIR . "carrier.inc";
include_once _INC_DIR . "global.inc";

include_once _CLS_DIR . "SPFWDatabase.cls";
include_once _CLS_DIR . "SPFWLog.cls";
include_once _CLS_DIR . "SPFWTemplate.cls";
include_once _CLS_DIR . "SPFWListObject.cls";
include_once _CLS_DIR . "SPFWDate.cls";
include_once _CLS_DIR . "SPFWInputCheck.cls";
include_once _CLS_DIR . "SPFWParameter.cls";
// include_once _CLS_DIR . "reload.cls";
include_once _CLS_DIR . "SPUSUser.cls";
include_once _CLS_DIR . "SPUSReservation.cls";
include_once _CLS_DIR . "SPUSBukken.cls";
include_once _CLS_DIR . "SPUSClient.cls";
// include_once _CLS_DIR . "SPUSHearingContent.cls";
include_once _CLS_DIR . "SPUSBuilding.cls";

include_once "./include/common_489.php";
echo("this is common file change thank you");

echo ("this is company 1's modification");
echo ("ok");
// データベースコネクト
$myDB = new SPFWDatabase(_MAIN_DB, _HOST_NAME, _USER_NAME, _PASSWD, FALSE);
if (!$myDB->Connection)
	trigger_error("SPFWDatabase Failed.", E_USER_ERROR);


########################################################
# 入力チェック
########################################################
$rKey = SPFWParameter::getValues('rKey');
$m 				= SPFWParameter::getValues('m');

if ($rKey == NULL) {
	$URL = _MAIN_URL . 'login_form.php';
	header('Location: ' . $URL);
	exit;
}

#アドレス取得　スマホからの本WEBアクセスを不正と表示する。
$YourDomain = $_SERVER["REMOTE_ADDR"];
$Today = date("Y-m-d");
########################################################
# 認証チェック
########################################################
$myUser = new User($myDB);

if ($rKey == NULL)
	showSorryPage(_ILLEGAL_ACCESS2);

if (!$myUser->doAuthenticationByRegistKey($rKey))
	trigger_error("doAuthentication Failed.", E_USER_ERROR);

if ($myUser->UserCD == -1)
	showSorryPage(_ILLEGAL_ACCESS2);

$MyUserCD = $myUser->UserCD;

unset($myUser);

########################################################
# クライアント取得
########################################################

$editBukkenCD = SPFWParameter::getValues('editBukkenCD');
$editBuildingCD = SPFWParameter::getValues('editBuildingCD');

$wID = SPFWParameter::getValues('ID');

// #### マンション名　をもってくる。
$myBukken = new Bukken($myDB);

if (!$myBukken->executeSelect(" BukkenCD = $editBukkenCD AND MukouFlg = FALSE", "")) {
	$ErrorString = array();
	$ErrorString[] = "設定ファイル情報の抽出に失敗しました。";
	$ErrorLoop = count($ErrorString);
	$myTemplate = new SPFWTemplate(_ADMIN_ERROR_TPL, $MyCarrier, TRUE);
	unset($myTemplate);
	exit;
}
$myBuilding = new Building($myDB);
if($editBuildingCD){
	if (!$myBuilding->executeSelect("BukkenCD = " . $editBukkenCD . " AND BuildingCD = ".$editBuildingCD." AND MukouFlg = FALSE", "") || $myBuilding->RecCnt != 1) {
		$ErrorString = array();
		$ErrorString[] = "設定ファイル情報の抽出に失敗しました。";
		$ErrorLoop = count($ErrorString);
		$myTemplate = new SPFWTemplate(_ADMIN_ERROR_TPL, $MyCarrier, TRUE);
		unset($myTemplate);
		exit;
	}
}


$MansionName = $myBukken->BukkenName;
$wBuildingName = $myBukken->BuildingName;
if($editBuildingCD){
	$wBuildingName = $myBuilding->BuildingName;
}

// 棟一覧
$myListObject = new SPFWListObject($myDB);

$sql = "SELECT ";
$sql .= "u.BuildingCD, ";
$sql .= "u.BuildingName ";

$myListObject->SelectSQL = $sql;

$sql = " FROM tBuildingM u ";
$sql .= " WHERE u.MukouFlg = FALSE AND BukkenCD='".$editBukkenCD."'";

$myListObject->Condition = $sql;
$myListObject->Order = "u.BuildingCD ASC";
$myListObject->Limit = "allpage";

if (!($myListObject->GetList(1)))
	trigger_error("Getting User List Failed.", E_USER_ERROR);

$BuildingCD = [];
$BuildingName = [];
$BuildingLoop = $myListObject->Rows;

for ($i = 0; $i < $BuildingLoop; $i++) {
	$BuildingCD[$i] = $myListObject->GetValue($i, 0);
	$BuildingName[$i] = $myListObject->GetValue($i, 1);
}
unset($myListObject);

// 棟名称が空の場合、例外処理
function numberToCircled($number) {
    $map = [
        1 => '①', 2 => '②', 3 => '③', 4 => '④', 5 => '⑤',
        6 => '⑥', 7 => '⑦', 8 => '⑧', 9 => '⑨', 10 => '⑩',
        11 => '⑪', 12 => '⑫', 13 => '⑬', 14 => '⑭', 15 => '⑮',
        16 => '⑯', 17 => '⑰', 18 => '⑱', 19 => '⑲', 20 => '⑳'
    ];

    return $map[$number] ?? $number;
}
if(!$wBuildingName){
	if($editBuildingCD){
		$wBuildingName = '棟'.numberToCircled(2);
		for ($i = 0; $i < $BuildingLoop; $i++) {
			if($BuildingCD[$i] == $editBuildingCD){
				$wBuildingName = '棟'.numberToCircled($i+2);
			}
		}

	}else{
		if($BuildingLoop > 0){
			$wBuildingName = '棟'.numberToCircled(1);
		}
	}
}
unset($myBukken);



	#######################################################
	# パラメータ取得
	########################################################

	// for ($i = 1; $i < count($PRESET_QUESTION_ID); $i++) {
	// 	$MyID = $PRESET_QUESTION_ID[$i];
	// 	${'w' . $MyID} = SPFWParameter::getValues('w' . $MyID);
	// }

	// for ($i = 0; $i < _MAX_QUESTIONS; $i++) {
	// 	$Index = $i + 1;
	// 	${'wExtra' . $Index} = SPFWParameter::getValues('wExtra' . $Index);
	// }

	// // 区分（所有/賃貸)
	// $wKubun = SPFWParameter::getValues('wKubun');
	// // 備考・特記事項
	// $wFreeMemo = SPFWParameter::getValues('wFreeMemo');

	// ヒアリング



########################################################
# 部屋表示
########################################################
// #### マンション名　をもってくる。
$myUser = new User($myDB);

if($editBuildingCD){
	if (!$myUser->executeSelect(" BukkenCD = ".$editBukkenCD." AND BuildingCD = ".$editBuildingCD." AND ID = '".$wID."' AND MukouFlg = FALSE", "")) {
		$ErrorString = array();
		$ErrorString[] = "設定ファイル情報の抽出に失敗しました。";
		$ErrorLoop = count($ErrorString);
		$myTemplate = new SPFWTemplate(_ADMIN_ERROR_TPL, $MyCarrier, TRUE);
		unset($myTemplate);
		exit;
	}
}else{
	if (!$myUser->executeSelect(" BukkenCD = ".$editBukkenCD." AND BuildingCD IS NULL AND ID = '".$wID."' AND MukouFlg = FALSE", "")) {
		$ErrorString = array();
		$ErrorString[] = "設定ファイル情報の抽出に失敗しました。";
		$ErrorLoop = count($ErrorString);
		$myTemplate = new SPFWTemplate(_ADMIN_ERROR_TPL, $MyCarrier, TRUE);
		unset($myTemplate);
		exit;
	}
}

$wUserCD = $myUser->UserCD ;

unset($myUser);


########################################################
# コンテンツ表示
########################################################

	$CNT_FILE = "s_sign.tpl";

$myTemplate = new SPFWTemplate($CNT_FILE, $MyCarrier);

// $myReload = new Reload();
// $myTemplate->setValue("_r_e_l_o_a_d_", $myReload->embedValue());
// if ($IfASP)
// 	$myTemplate->setValue("vC", $TargetClientCD);
if ($IfNew)
	$myTemplate->setValue("vN", 't');
else
	$myTemplate->setValue("vN", NULL);
$HiddenValues = $myTemplate->getValuesToPass();
$myTemplate->convertTags();
$myTemplate->outputTemplate();

$myDB->close();

unset($myTemplate);
unset($myLog);
