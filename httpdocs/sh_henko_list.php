<?php
$isAdminMode = TRUE;
// ini_set('display_errors', "On");

include_once "D:/xampp/htdocs/kotei3/SPFW/inc/setting.properties";
include_once _INC_DIR . "carrier.inc";
include_once _INC_DIR . "global.inc";
include_once _CLS_DIR . "SPFWDatabase.cls";
include_once _CLS_DIR . "SPFWLog.cls";
include_once _CLS_DIR . "SPFWTemplate.cls";
include_once _CLS_DIR . "SPFWParameter.cls";
include_once _CLS_DIR . "SPFWListObject.cls";
include_once _CLS_DIR . "SPFWDate.cls";
include_once _CLS_DIR . "SPFWInputCheck.cls";
include_once _CLS_DIR . "SPUSUser.cls";
include_once _CLS_DIR . "SPUSBukken.cls";
// include_once _CLS_DIR . "SPUSKoji.cls";
include_once _CLS_DIR . "SPFWTools.cls";
include_once _CLS_DIR . "SPUSBuilding.cls";

$myDB = new SPFWDatabase(_MAIN_DB, _HOST_NAME, _USER_NAME, _PASSWD, FALSE);
if (!$myDB->Connection)
	trigger_error("SPFWDatabase Failed.", E_USER_ERROR);

########################################################
# 値取得
########################################################
$rKey 			= SPFWParameter::getValues("rKey");
$editBukkenCD 	= SPFWParameter::getValues('editBukkenCD');
$editBuildingCD = SPFWParameter::getValues('editBuildingCD');
$m 				= SPFWParameter::getValues('m');

########################################################
# 認証動作
########################################################
if ($rKey) {
	$myUser = new User($myDB);
	if ($rKey == NULL)
		showSorryPage(_ILLEGAL_ACCESS2);

	if (!$myUser->doAuthenticationByRegistKey($rKey))
		trigger_error("doAuthentication Failed.", E_USER_ERROR);

	if ($myUser->UserCD == -1)
		showSorryPage(_ILLEGAL_ACCESS2);

	$UserCD 		= $myUser->UserCD;
	$ClientCD 		= $myUser->ClientCD; #幹事企業CD
	$UserKbn 		= $myUser->UserKbn;
	unset($myUser);
}

$IfWorker = $UserKbn == 3;
$IfDeveloper	= $UserKbn != 3;
$IfSP 			= $m == 1; // スマホ用
$IfPC 			= $m != 1;
$IfShowSchedule = false;
if($IfSP){
	if($IfDeveloper)
		$IfShowSchedule = true;
	$IfWorker		= true;
	$IfDeveloper	= false;
}

// if ($UserKbn == 3) {
// 	$SHeaderKanri = "<div style='text-align:center;'>";
// 	$SHeaderKanri .= "<img class='logo' src='./images/489work.png' alt='489作業者' width='600' height='73'>";
// 	$SHeaderKanri .= "</div>";
// }

// 棟一覧
function numberToCircled($number) {
    $map = [
        1 => '①', 2 => '②', 3 => '③', 4 => '④', 5 => '⑤',
        6 => '⑥', 7 => '⑦', 8 => '⑧', 9 => '⑨', 10 => '⑩',
        11 => '⑪', 12 => '⑫', 13 => '⑬', 14 => '⑭', 15 => '⑮',
        16 => '⑯', 17 => '⑰', 18 => '⑱', 19 => '⑲', 20 => '⑳'
    ];

    return $map[$number] ?? $number;
}
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
$naviClass = [];
$mainNaviClass = 'active';
$BuildingLoop = $myListObject->Rows;

for ($i = 0; $i < $BuildingLoop; $i++) {
	$BuildingCD[$i] = $myListObject->GetValue($i, 0);
	$BuildingName[$i] = $myListObject->GetValue($i, 1);
	if(!$BuildingName[$i])
		$BuildingName[$i] = '棟'.numberToCircled($i+2);

	if($BuildingCD[$i] == $editBuildingCD){
		$naviClass[$i] = 'active';
		$mainNaviClass = '';
	}
	else{
		$naviClass[$i] = '';
	}

}
unset($myListObject);

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
if(!$wBuildingName){
	if($BuildingLoop > 0){
		$wBuildingName = '棟'.numberToCircled(1);
	}
}
if($wBuildingName)
	$IfBuildingExist = true;
else
	$IfBuildingExist = false;
	
$WakuPattern = $myBukken->WakuPattern;
if($editBuildingCD){
	$WakuPattern = $myBuilding->WakuPattern;
}
$TargetClientCD = $myBukken->ClientCD; #111
$ClientCD = $TargetClientCD;
unset($myBukken);

########################################################
# 担当者リスト表示
########################################################
$myListObject = new SPFWListObject($myDB);

$sql = "SELECT ";
$sql .= "UserCD, ";
$sql .= "LastName ";

$myListObject->SelectSQL = $sql;

$sql = " FROM tUserM";
// if($UserKbn != 2){#ユーザ区分が管理者でなければ　自分の幹事企業のみ表示
$sql .= " WHERE MukouFlg = FALSE AND ClientCD = " . $ClientCD;
$sql .= " AND UserKbn < 4 ";#住人さん以外
$myListObject->Condition	= $sql;
$myListObject->Order 		= "";
$myListObject->Limit 		= "allpage";

if (!($myListObject->GetList(1)))
	trigger_error("Getting Menu List Failed.", E_USER_ERROR);

$UserLoop = $myListObject->Rows;
for ($i = 0; $i < $UserLoop; $i++) {
	$UserCDs[$i] 	= $myListObject->GetValue($i, 0);
	$LastName[$i]	= $myListObject->GetValue($i, 1);
	$LastNameArray[$UserCDs[$i]] = $LastName[$i];
}
unset($myListObject);
########################################################
# お客様問い合わせ一覧
########################################################

if (is_array($WAKUPATTERN[$WakuPattern]['AMPM'])) {
	$WakuKazu = count($WAKUPATTERN[$WakuPattern]['AMPM']);
}
// $WakuKazu = count($WAKUPATTERN[$WakuPattern]['AMPM']);
$sSTime = $WAKUPATTERN[$WakuPattern]['StartTime'][0];
$sETime = $WAKUPATTERN[$WakuPattern]['EndTime'][$WakuKazu - 1];

$TimeLoop  = $WakuKazu;
for ($i = 0; $i < $TimeLoop; $i++) { #時間選択し
	$sSTime = $WAKUPATTERN[$WakuPattern]['StartTime'][$i];
	$sETime = $WAKUPATTERN[$WakuPattern]['EndTime'][$i];
	$wTime[$i] = $sSTime . "～" . $sETime;
}

// SELECT r.ID, r.TimeFrom, r.Memo, u.LastName, u.TEL, u.Updater, u.Updated, u.ReplyFlg
// FROM tUserM u
// LEFT OUTER JOIN tReservationF r 
//   ON r.UserCD = u.UserCD
// WHERE u.MukouFlg = FALSE
//   AND u.BukkenCD = 214
//   AND (r.Created > CURDATE() - INTERVAL 6 MONTH OR r.Created IS NULL)
//   AND (r.Status = 1 OR r.Status IS NULL);

$myListObject = new SPFWListObject($myDB);

$sql = "SELECT ";
$sql .= "r.ID, ";
$sql .= "r.TimeFrom, ";
$sql .= "r.Memo, ";
$sql .= "u.LastName, ";
$sql .= "u.TEL, ";
$sql .= "u.Updater,";
$sql .= "u.Updated,";
$sql .= "u.ReplyFlg,";
$sql .= "s.FilePath,";
$sql .= "r.TimeExact,";
$sql .= "r.TimeMeaning";
$myListObject->SelectSQL = $sql;

$sql = " FROM tUserM u left outer join tReservationF r on r.UserCD = u.UserCD ";
$sql .= " left outer join tSignF s on s.UserCD = u.UserCD and s.MukouFlg = FALSE";

// if($UserKbn != 2){#ユーザ区分が管理者でなければ　自分の幹事企業のみ表示
$sql .= " WHERE u.MukouFlg = FALSE AND u.BukkenCD = " . $editBukkenCD;
if($editBuildingCD){
	$sql .= " AND u.BuildingCD = " . $editBuildingCD;
}else{
	$sql .= " AND u.BuildingCD IS NULL ";
}

$sql .= " AND (r.Created  > CURDATE() - INTERVAL 4 MONTH OR r.Created IS NULL)";
$sql .= " AND (r.Status = 1 OR r.Status IS NULL)";
$myListObject->Condition	= $sql;

$OrderBy 	= SPFWParameter::getValues('OrderBy');

$myListObject->Order 		= str_replace("_", " ", $OrderBy); #"Created desc";
if( $OrderBy == "ID"){
	$OrderBy = " CAST(u.ID AS UNSIGNED)";
	$myListObject->Order 		= $OrderBy; #"Created asc";
}elseif( $OrderBy == "ID_Desc"){
	$OrderBy = " CAST(u.ID AS UNSIGNED) desc";
	$myListObject->Order 		= $OrderBy; #"Created desc";
}else if(!$OrderBy){
	$OrderBy = " CAST(u.ID AS UNSIGNED)";
	$myListObject->Order 		= $OrderBy; #"Created asc";
}

$myListObject->Limit 		= "allpage";

if (!($myListObject->GetList(1)))
	trigger_error("Getting Menu List Failed.", E_USER_ERROR);

$ResidentsFormLoop = $myListObject->Rows;
for ($i = 0; $i < $ResidentsFormLoop; $i++) {
	$ID[$i] 	= $myListObject->GetValue($i, 0);
	$TimeFrom[$i]	= $myListObject->GetValue($i, 1);
	$TimeFromDate[$i]	= str_replace("-","/",substr($TimeFrom[$i],6,5));
	$TimeFromTime[$i]	= obtainWakuTime($WakuPattern, $TimeFrom[$i],$WAKUPATTERN);
	$Memo[$i]	= $myListObject->GetValue($i, 2);
	$LastName[$i] = $myListObject->GetValue($i, 3);

	$TEL[$i] = $myListObject->GetValue($i, 4);
	$Updater[$i] = $myListObject->GetValue($i, 5);
	$Updated[$i] = $myListObject->GetValue($i, 6);
	$ReplyFlg[$i] = $myListObject->GetValue($i, 7);
	$FilePath[$i] = $myListObject->GetValue($i, 8);
	$TimeExact[$i] = $myListObject->GetValue($i, 9);
	$TimeMeaning[$i] = $myListObject->GetValue($i, 10);

	$RowClass[$i] = '';
	if($FilePath[$i])
		$RowClass[$i] = 'signed';
	if($ReplyFlg[$i] > 0 ){
		$DispReply[$i] = "レ";
		if($Updater[$i] <> $ID[$i]){
			$DispUpdater[$i] = $LastNameArray[$Updater[$i]]; 
		}else{
			$DispUpdater[$i] = "WEB";
		}
	}
}

unset($myListObject);


########################################################
# コンテンツ表示
########################################################
$CNT_FILE = "sh_henko_list.tpl";

$myTemplate 	= new SPFWTemplate($CNT_FILE, $MyCarrier);
$HiddenValues 	= $myTemplate->getValuesToPass();

$myTemplate->convertTags();
$myTemplate->outputTemplate();

unset($myTemplate);
unset($myLog);
########################################################
# 関数群
########################################################
#品番一致
function getDeviceData_Hinban($myDB, $Category, $Hinban)
{

	$DeviceData = array();

	$myListObject = new SPFWListObject($myDB);

	$sql = "SELECT ";
	$sql .= "DeviceName, ";
	$sql .= "Kataban, ";
	$sql .= "D003, "; #機器説明
	$sql .= "ShortName ";
	$myListObject->SelectSQL = $sql;

	$sql = " FROM tDeviceM";
	$sql .= " WHERE MukouFlg = FALSE and Category = '" . $Category . "'"; #1:親機 2:子機
	$sql .= " AND Kataban = '" . $Hinban . "' ";

	$myListObject->Condition = $sql;
	$myListObject->Order = "Kataban";
	$myListObject->Limit = "allpage";


	if (!($myListObject->GetList(1)))
		trigger_error("Getting Menu List Failed.", E_USER_ERROR);

	if ($myListObject->Rows == 1) {
		$DeviceData['DeviceName'] 	= $myListObject->GetValue(0, 0);
		$DeviceData['Kataban'] 		= $myListObject->GetValue(0, 1);
		$DeviceData['KikiSetumei'] 	= $myListObject->GetValue(0, 2);
		$DeviceData['ShortName'] 	= $myListObject->GetValue(0, 3);
	}
	unset($myListObject);

	return $DeviceData;
}

function obtainWakuTime($WakuPattern, $TimeFrom,$WAKUPATTERN){
	$TimeFrom = substr($TimeFrom,11,5);#09:20
    $sTimeFrom = strtotime($TimeFrom);
    $WakuSu = isset($WAKUPATTERN[$WakuPattern]['AMPM'])?count($WAKUPATTERN[$WakuPattern]['AMPM']):0;

    for($i=0; $i<$WakuSu; $i++){
        $StartTime = $WAKUPATTERN[$WakuPattern]['StartTime'][$i];
        $sStartTime = strtotime($StartTime);
        $EndTime = $WAKUPATTERN[$WakuPattern]['EndTime'][$i];
        $sEndTime = strtotime($EndTime);

        // $sTimeFromが$sStartTime以上、かつ$sEndTime以下の場合に時間枠に含まれる
        if($sStartTime <= $sTimeFrom && $sTimeFrom < $sEndTime){
            // $WakuTime['STime'] = $StartTime;
            // $WakuTime['ETime'] = $EndTime;
			$WakuTime = $WAKUPATTERN[$WakuPattern]['AMPM'][$i];

			// echo "<br> ".__LINE__." StartTime :".$StartTime;
			// echo "<br> ".__LINE__." EndTime :".$EndTime;

            return $WakuTime;
        }
    }
    return null;  // どの時間枠にも当てはまらない場合はnullを返す

}