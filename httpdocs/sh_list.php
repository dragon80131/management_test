<?php
$isAdminMode = TRUE;
include_once "D:/xampp/htdocs/kotei3/SPFW/inc/setting.properties";
	include_once _INC_DIR . "carrier.inc";
	include_once _INC_DIR . "global.inc";

	include_once _CLS_DIR . "SPFWDatabase.cls";
	include_once _CLS_DIR . "SPFWLog.cls";
	include_once _CLS_DIR . "SPFWTemplate.cls";
	include_once _CLS_DIR . "SPFWListObject.cls";
	include_once _CLS_DIR . "SPFWParameter.cls";
#	include_once _CLS_DIR . "SPFWDate.cls";
	include_once _CLS_DIR . "SPFWInputCheck.cls";
	include_once _CLS_DIR . "SPUSUser.cls";
	include_once _CLS_DIR . "SPUSReservation.cls";
	include_once _CLS_DIR . "SPUSBukken.cls";
	// include_once _CLS_DIR . "SPUSHenkoRoom.cls";
	// include_once _CLS_DIR . "SPUSHenkoDate.cls";
include_once _CLS_DIR . "SPUSBuilding.cls";

echo("this is a compnay2 file");

	// データベースコネクト
	$myDB = new SPFWDatabase(_MAIN_DB, _HOST_NAME, _USER_NAME, _PASSWD, FALSE);
	if (!$myDB->Connection)
		trigger_error("SPFWDatabase Failed.", E_USER_ERROR);



	########################################################
	# 値取得
	########################################################
	$rKey 			= SPFWParameter::getValues("rKey");
	$editBukkenCD = SPFWParameter::getValues('editBukkenCD');
	$editBuildingCD = SPFWParameter::getValues('editBuildingCD');
	$ClientCD = SPFWParameter::getValues('ClientCD');
	$work = SPFWParameter::getValues('work');
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

			$myUserCD 		= $myUser->UserCD;
			$ClientCD 		= $myUser->ClientCD;
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
// 	$SHeaderKanri .= "<img class='logo'  src='./images/489work.png' alt='489作業者' width='600' height='73'>";
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

#######################################移植
	########################################################
# クライアント取得 tBukkenMからとる
########################################################
$myBukken = new Bukken($myDB);
if (!$myBukken->executeSelect(" BukkenCD = '".$editBukkenCD."' AND MukouFlg = FALSE", "")) {
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


$wWakuPattern = $myBukken->WakuPattern;
$MaxWakuSu = $myBukken->MaxWakuSu;#6-4-4
$wHansu = $myBukken->Hansu;
$SyonitiKouryo = $myBukken->FirstDateFeature; 
$Holiday1 = $myBukken->Holiday1;
$ReserveDay = $myBukken->ReserveDay;
$wFirstDateFeature = $myBukken->FirstDateFeature;

if($editBuildingCD){
	$wWakuPattern = $myBuilding->WakuPattern;
	$MaxWakuSu = $myBuilding->MaxWakuSu;#6-4-4
	$wHansu = $myBuilding->Hansu;
	$SyonitiKouryo = $myBuilding->FirstDateFeature; 
	$Holiday1 = $myBuilding->Holiday1;
	$ReserveDay = $myBuilding->ReserveDay;
	$wFirstDateFeature = $myBuilding->FirstDateFeature;
}

$MaxWaku = explode("-",$MaxWakuSu);
$wWakuAM = $MaxWaku[0];
$wWakuPM = $MaxWaku[1];
$wWakuPM1 = $MaxWaku[1];
if(count($MaxWaku)>2){
	$wWakuPM2 = $MaxWaku[2];
}
$TargetClientCD = $myBukken->ClientCD;#111
// $SenyuStartDate = $myBukken->SenyuStartDate;
// $SenyuEndDate = $myBukken->SenyuEndDate;
$SenyuStartDateGeneral = $myBukken->SenyuStartDate;
$SenyuEndDateGeneral = $myBukken->SenyuEndDate;

$SenyuStartDate = $myBukken->SenyuStartDate1;
$SenyuEndDate = $myBukken->SenyuEndDate1;
if($editBuildingCD){
	$SenyuStartDate = $myBuilding->SenyuStartDate;
	$SenyuEndDate = $myBuilding->SenyuEndDate;
}
if(!$SenyuStartDate || !$SenyuEndDate){
	$SenyuStartDate = $SenyuStartDateGeneral;
	$SenyuEndDate = $SenyuEndDateGeneral;
}

$SenyuDateCnt = ((strtotime($SenyuEndDate) -  strtotime($SenyuStartDate)) / 86400) + 1; #専有部日数
$MinuteTime = $myBukken->MinuteTime; #20ぷん
$wHoliday = SPFWTools::decodePluralValue($Holiday1);
sort($wHoliday);

$wReserveDay = SPFWTools::decodePluralValue($ReserveDay);
sort($wReserveDay);

$beforeReserveDay = [];
$afterReserveDay = [];
foreach($wReserveDay as $aReserveDay){
	$dateReserveDay = new DateTime($aReserveDay);
	$dateSenyuStartDate = new DateTime($SenyuStartDate);
	$dateSenyuEndDate = new DateTime($SenyuEndDate);

	if($dateReserveDay < $dateSenyuStartDate){
		array_push($beforeReserveDay, $aReserveDay);
	}else if($dateReserveDay > $dateSenyuEndDate){
		array_push($afterReserveDay, $aReserveDay);
	}
}

$MaxWaku = explode("-",$MaxWakuSu);
$wWakuAM = $MaxWaku[0];
$wWakuPM = $MaxWaku[1];
$wWakuPM1 = $MaxWaku[1];
if(count($MaxWaku)>2){
	$wWakuPM2 = $MaxWaku[2];
}


if($work){ // 情報登録=1, 確定=2, 変更=3
	$aHenkoDate = SPFWParameter::getValues('HenkoDate');#wHenkoDateにしたら下でダブっておかしくなる
	$aTimeFromTime = SPFWParameter::getValues('TimeFromTime');
	$aHanNo = SPFWParameter::getValues('HanNo');
	$aViewOrderNo = SPFWParameter::getValues('ViewOrderNo');
	$aRoomNo = SPFWParameter::getValues('RoomNo');
	$aLastName = SPFWParameter::getValues('Name');
	$aTEL = SPFWParameter::getValues('TEL');
	$aEMail = SPFWParameter::getValues('EMail');
	$aMemo = SPFWParameter::getValues('Memo');
	$TimeExactHour = SPFWParameter::getValues('TimeExactHour');
	$TimeExactMinutes = SPFWParameter::getValues('TimeExactMinutes');
	if($TimeExactHour || $TimeExactMinutes)
		$aTimeExact = $TimeExactHour.":".$TimeExactMinutes;
	else
		$aTimeExact = "";
	$aTimeMeaning = SPFWParameter::getValues('TimeMeaning');
	###登録処理
	if($aHenkoDate != 'red'){#Javascriptに　HiddenHenkoDate の　ElementByID部分の変更前の値

		$myReservation = new Reservation($myDB);

		if($editBuildingCD){
			if (!$myReservation->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD = '".$editBuildingCD."' AND ID = $aRoomNo AND MukouFlg = FALSE", "")) 
				trigger_error("Getting Reservation Failed.", E_USER_ERROR);
		}else{
			if (!$myReservation->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD IS NULL AND ID = $aRoomNo AND MukouFlg = FALSE", "")) 
				trigger_error("Getting Reservation Failed.", E_USER_ERROR);
		}
		
		if($work == '1'){ // 情報登録
			$myUser = new User($myDB);
			if($editBuildingCD){
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD = '".$editBuildingCD."' AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}else{
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD IS NULL AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}

			$myReservation->TimeFrom = $aHenkoDate." ".$aTimeFromTime;

			// $MinuteTime（20分）後の時刻を計算
			$TimeToTime = date('H:i', strtotime('+'.$MinuteTime.' minutes', strtotime($TimeFromTime)));

			$myReservation->TimeTo = $aHenkoDate." ".$TimeToTime;
			if($myUser->ReplyFlg == '3'){
				$myReservation->Memo = NULL;
				$myReservation->TimeExact = NULL;
				$myReservation->TimeMeaning = NULL;
			}else{
				$myReservation->Memo = $aMemo;
				$myReservation->TimeExact = $aTimeExact;
				$myReservation->TimeMeaning = $aTimeMeaning;
				$myUser->ReplyFlg 	= "2";#TEL受付
			}
			$myReservation->HanNo = $aHanNo;
			$myReservation->ViewOrderNo = $aViewOrderNo;
			$myReservation->Updater = $myUserCD;
			$myReservation->Updated = "NOW()";

			$myUser->Updater = $myUserCD;
			$myUser->LastName = $aLastName;
			$myUser->TEL 	=  $aTEL;
			$myUser->EMail 	=  $aEMail;
			if (!$myUser->executeUpdate())
				trigger_error("Updating myUser Failed.", E_USER_ERROR);

			if (!$myReservation->executeUpdate())
				trigger_error("Updating myReservation Failed.", E_USER_ERROR);

	// // 		####登録処理End
	// // 		####変数ドロップしておく。
			SPFWTemplate::dropValue('work');
			// リダイレクトによるページリロード
			header("Location: " . $_SERVER['PHP_SELF'] . "?ClientCD=" . urlencode($ClientCD)."&editBukkenCD=" . urlencode($editBukkenCD)."&editBuildingCD=" . urlencode($editBuildingCD)."&rKey=" . urlencode($rKey));
			exit;
		}else if($work == '2'){ // 確定
			$myUser = new User($myDB);
			if($editBuildingCD){
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD = '".$editBuildingCD."' AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}else{
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD IS NULL AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}
			$myUser->Updater = $myUserCD;
			$myUser->ReplyFlg 	= "1";
			$myUser->ConfirmFlg = "1";
			if (!$myUser->executeUpdate())
				trigger_error("Updating myUser Failed.", E_USER_ERROR);
			SPFWTemplate::dropValue('work');
		}else if($work == '3'){ // 変更
			$myReservation->TimeFrom = $aHenkoDate." ".$aTimeFromTime;
			// $MinuteTime（20分）後の時刻を計算
			$TimeToTime = date('H:i', strtotime('+'.$MinuteTime.' minutes', strtotime($TimeFromTime)));
			$myReservation->TimeTo = $aHenkoDate." ".$TimeToTime;
			$myReservation->HanNo = $aHanNo;
			$myReservation->ViewOrderNo = $aViewOrderNo;
			$myReservation->Updater = $myUserCD;
			$myReservation->TimeExact = NULL;
			$myReservation->TimeMeaning = NULL;
			$myReservation->Updated = "NOW()";

			if (!$myReservation->executeUpdate())
				trigger_error("Updating myReservation Failed.", E_USER_ERROR);

			$myUser = new User($myDB);
			if($editBuildingCD){
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD = '".$editBuildingCD."' AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}else{
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD IS NULL AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}
			$myUser->Updater = $myUserCD;
			$myUser->ReplyFlg 	= "1";
			$myUser->ConfirmFlg = "1";
			if (!$myUser->executeUpdate())
				trigger_error("Updating myUser Failed.", E_USER_ERROR);
			SPFWTemplate::dropValue('work');
		}else if($work == '4'){ // 辞退
			$myReservation->TimeExact = NULL;
			$myReservation->TimeMeaning = NULL;
			$myReservation->Updated = "NOW()";
			if (!$myReservation->executeUpdate())
				trigger_error("Updating myReservation Failed.", E_USER_ERROR);

			$myUser = new User($myDB);
			if($editBuildingCD){
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD = '".$editBuildingCD."' AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}else{
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD IS NULL AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}
			$myUser->Updater = $myUserCD;
			$myUser->ReplyFlg 	= "3";

			if (!$myUser->executeUpdate())
				trigger_error("Updating myUser Failed.", E_USER_ERROR);
			SPFWTemplate::dropValue('work');
		}else if($work == '5'){ // 復活
			$myReservation->TimeFrom = $aHenkoDate." ".$aTimeFromTime;
			// $MinuteTime（20分）後の時刻を計算
			$TimeToTime = date('H:i', strtotime('+'.$MinuteTime.' minutes', strtotime($TimeFromTime)));
			$myReservation->TimeTo = $aHenkoDate." ".$TimeToTime;
			$myReservation->HanNo = $aHanNo;
			$myReservation->ViewOrderNo = $aViewOrderNo;
			$myReservation->Updater = $myUserCD;
			$myReservation->Updated = "NOW()";

			if (!$myReservation->executeUpdate())
				trigger_error("Updating myReservation Failed.", E_USER_ERROR);

			$myUser = new User($myDB);
			if($editBuildingCD){
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD = '".$editBuildingCD."' AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}else{
				if (!$myUser->executeSelect("  BukkenCD = '".$editBukkenCD."' AND BuildingCD IS NULL AND ID = '".$aRoomNo."' AND MukouFlg = FALSE", "")) 
					trigger_error("Getting Reservation Failed.", E_USER_ERROR);
			}
			$myUser->Updater = $myUserCD;
			$myUser->ReplyFlg 	= "1";
			$myUser->ConfirmFlg = "1";
			if (!$myUser->executeUpdate())
				trigger_error("Updating myUser Failed.", E_USER_ERROR);
			SPFWTemplate::dropValue('work');
		}

	}
}

if(!$wWakuPattern){
	echo ('<script>
if(confirm("作業日程登録がまだ終わっていないようです。\r\nブラウザで戻り、作業日程登録の各項目の入力をお願いします。\r\n作業日程登録ページへ移動しますか？")){
	location.href="./doc/s_make_kanryo2.php?rKey='.$rKey.'&editBukkenCD='.$editBukkenCD.'&editBuildingCD='.$editBuildingCD.'";
}else{
	location.href="./s_menu.php?rKey='.$rKey.'&editBukkenCD='.$editBukkenCD.'";
}
</script>');
}


########################################################
# 詳細工程表表示
########################################################

#Koteihyoに、空きか部屋番号をいれていく。
########################################################
# 日程情報取得
########################################################
// SELECT 
//    DATE(TimeFrom) AS Date,
//     CASE 
//         WHEN TIME(TimeFrom) BETWEEN '09:00:00' AND '12:00:00' THEN 'AM'
//         WHEN TIME(TimeFrom) BETWEEN '13:00:00' AND '15:00:00' THEN 'PM1'
//         WHEN TIME(TimeFrom) BETWEEN '15:00:01' AND '18:00:00' THEN 'PM2'
//         ELSE 'Other'
//     END AS TimePeriod
// FROM tReservationF
// where BukkenCD = 217

$myListObject = new SPFWListObject($myDB);
$sql  = "SELECT ";
$sql .= "r.ReservationCD, "; #0
$sql .= "DATE(r.TimeFrom) AS Date, "; #1
if($wWakuPattern == '0' || $wWakuPattern == '1' || $wWakuPattern == '2'){ // 2枠
	$sql .= "CASE ";
	$sql .= " WHEN TIME(r.TimeFrom) BETWEEN '09:00:00' AND '12:00:00' THEN 'AM'";
	$sql .= " WHEN TIME(r.TimeFrom) BETWEEN '13:00:00' AND '18:00:00' THEN 'PM'";
	$sql .= " ELSE 'Other'";
	$sql .= " END AS AMPM ,"; #2
}else{ // 3枠
	$sql .= "CASE ";
	$sql .= " WHEN TIME(r.TimeFrom) BETWEEN '09:00:00' AND '12:00:00' THEN 'AM'";
	$sql .= " WHEN TIME(r.TimeFrom) BETWEEN '13:00:00' AND '14:59:00' THEN 'PM1'";
	$sql .= " WHEN TIME(r.TimeFrom) BETWEEN '15:00:00' AND '18:00:00' THEN 'PM2'";
	$sql .= " ELSE 'Other'";
	$sql .= " END AS AMPM ,"; #2
}
$sql .= "r.ID, "; #3
$sql .= "r.UserCD, "; #4
$sql .= "r.TimeFrom, "; #5
$sql .= "r.TimeTo, "; #6
$sql .= "u.LastName, "; #7
$sql .= "u.TEL, "; #8
$sql .= "r.Memo, "; #9
$sql .= "r.TimeExact, "; #10
$sql .= "r.TimeMeaning, "; #11
$sql .= "u.ReplyFlg, "; #12
$sql .= "u.ConfirmFlg, "; #13
$sql .= "r.HanNo, "; #14
$sql .= "r.ViewOrderNo, "; #15
$sql .= "u.EMail, "; #16
$sql .= "CASE ";
$sql .= " WHEN u.ReplyFlg = 3 THEN '2'";
$sql .= " ELSE '1'";
$sql .= " END AS SubOrder"; #17


$myListObject->SelectSQL = $sql;
$sql  = " FROM tReservationF r, tUserM u ";
$sql .= " WHERE r.Status = 1 AND r.MukouFlg = FALSE AND r.UserCD = u.UserCD";
$sql .= " AND r.BukkenCD = " . $editBukkenCD;
if($editBuildingCD){
	$sql .= " AND r.BuildingCD = " . $editBuildingCD;
}else{
	$sql .= " AND r.BuildingCD IS NULL ";
}

// $sql .= " AND ClientCD = " . $wClientCD;

$myListObject->Condition = $sql;
$myListObject->Order = "Date, AMPM, r.HanNo, SubOrder, r.TimeFrom, r.Updated, r.ReservationCD";
$myListObject->Limit = "allpage";

if (!($myListObject->GetList(1)))
	trigger_error("Getting Reservation List Failed.", E_USER_ERROR);

$ReservationLoop = $myListObject->Rows;
$TimeExactHour = [];
$TimeExactMinutes = [];
$TimeMeaning = [];
$arrHanNo = [];
$arrViewOrderNo = [];

for ($i = 0; $i < $ReservationLoop; $i++) {
	$ReservationCD[$i] = $myListObject->GetValue($i, 0);
	$tDate = $myListObject->GetValue($i, 1);
	$AMPM = $myListObject->GetValue($i, 2);
	$ID[$i] = $myListObject->GetValue($i, 3);
	$Reserve[$tDate][$AMPM][] = $ID[$i];

	$UserCD[$i] = $myListObject->GetValue($i, 4);

	$UserData['LastName'][$ID[$i]] = $myListObject->GetValue($i, 7);
	$UserData['TEL'][$ID[$i]] 	= $myListObject->GetValue($i, 8);
	$UserData['EMail'][$ID[$i]] 	= $myListObject->GetValue($i, 16);
	$UserData['Memo'][$ID[$i]] 	= $myListObject->GetValue($i, 9);
	$UserData['ReplyFlg'][$ID[$i]] 	= $myListObject->GetValue($i, 12);
	$UserData['ConfirmFlg'][$ID[$i]] 	= $myListObject->GetValue($i, 13);

	$TimeExact 	= $myListObject->GetValue($i, 10);
	$TimeExactPieces = explode(":", $TimeExact);

	if(isset($TimeExactPieces[0]))
		$TimeExactHour[$ID[$i]] 	= $TimeExactPieces[0];
	else
		$TimeExactHour[$ID[$i]] 	= null;

	if(isset($TimeExactPieces[1]))
		$TimeExactMinutes[$ID[$i]] 	= $TimeExactPieces[1];
	else
		$TimeExactMinutes[$ID[$i]] 	= null;

	$TimeMeaning[$ID[$i]] 	= $myListObject->GetValue($i, 11);
	$arrHanNo[$ID[$i]] 	= $myListObject->GetValue($i, 14);
	$arrViewOrderNo[$ID[$i]] 	= $myListObject->GetValue($i, 15);
}

######################################################################################

$declineResult = "なし";
if(isset($UserData['ReplyFlg']) && is_array($UserData['ReplyFlg']) && count($UserData['ReplyFlg']) > 0){
	ksort($UserData['ReplyFlg']);
	foreach($UserData['ReplyFlg'] as $declineNo => $aReplyFlg){
		if($aReplyFlg == '3'){
			if($declineResult == 'なし')
				$declineResult = '';

			$tID = $declineNo;
			$aLastName = $UserData['LastName'][$tID] ;
			$aTEL = $UserData['TEL'][$tID] ;
			$aEMail = $UserData['EMail'][$tID] ;
			$aMemo = $UserData['Memo'][$tID] ;
			$ReplyFlg = $UserData['ReplyFlg'][$tID] ;
			$ConfirmFlg = $UserData['ConfirmFlg'][$tID] ;
			$aTimeExactHour = $TimeExactHour[$tID];
			$aTimeExactMinutes = $TimeExactMinutes[$tID];
			if($aTimeExactHour || $aTimeExactMinutes)
				$aTimeExact = $aTimeExactHour.":".$aTimeExactMinutes;
			else
				$aTimeExact = null;
			$aTimeMeaning = $TimeMeaning[$tID];

			$declineResult .= '<div class="declineNo tooltip-container" onclick="clickBtn8(\''.$tID.'\' ,\''.$aLastName.'\' ,\''.$aTEL.'\',\''.$aMemo.'\' , \''.$SenyuDate.'\', \''.$SenyuDateTime.'\', \''.$WakuName.'\', \''.$aTimeExactHour.'\', \''.$aTimeExactMinutes.'\', \''.$aTimeMeaning.'\', \''.$abanNo.'\', \''.$ViewOrderNo.'\', \''.$ReplyFlg.'\', \''.$ConfirmFlg.'\' ,\''.$aEMail.'\' )">'.$declineNo;
				$declineResult .= '<div class="tooltip-text">';
					$declineResult .= '<span class="decline">「辞退」</span> <br>';
					$declineResult .= '<span class="lb">部屋番号:</span> '.$declineNo.'<br>';
					$declineResult .= '<span class="lb">名前:</span> '.$aLastName.'<br>';
					$declineResult .= '<span class="lb">連絡先:</span> '.$aTEL.'<br>';
					$declineResult .= '<span class="lb">メールアドレス:</span> '.$aEMail.'<br>';
					$declineResult .= '<span class="lb">時間指定:</span> '.$aTimeExact.' '.$aTimeMeaning.'<br>';
					$declineResult .= '<span class="lb">備考:</span> '.nl2br($aMemo).'<br>';
				$declineResult .= '</div>';
			$declineResult .= '</div>';
		}
	}

}

$rowCountforDay = $wHansu;

for ($i = 0; $i < count($WAKUPATTERN[$wWakuPattern]['AMPM']); $i++) {
	$WakuName = $WAKUPATTERN[$wWakuPattern]['AMPM'][$i];
	$tempRowCountforDay = ceil(${'wWaku' . $WakuName} / 5);
	if($tempRowCountforDay > $rowCountforDay)
		$rowCountforDay = $tempRowCountforDay;

	${'wWaku' . $WakuName . 'Col'} = ceil(${'wWaku' . $WakuName} / $wHansu);
	${'wWaku' . $WakuName . 'ColSum'} = ${'wWaku' . $WakuName . 'Col'} * $wHansu;
	$wWakuColSum += ${'wWaku' . $WakuName . 'Col'};

	${'wWaku' . $WakuName . 'col'} = ceil(${'wWaku' . $WakuName} / $wHansu);
	if($WakuName == 'PM')$wWakuPM1col = ${'wWaku' . $WakuName . 'Col'} ;
}

$rowCountforDay = ceil($rowCountforDay / $wHansu) * $wHansu;

for ($i = 0; $i < count($WAKUPATTERN[$wWakuPattern]['AMPM']); $i++) {
	$WakuName = $WAKUPATTERN[$wWakuPattern]['AMPM'][$i];

	$tempRowCountforDay = ceil(${'wWaku' . $WakuName} / 5);
	if($tempRowCountforDay > $wHansu){
		${'wWaku' . $WakuName . 'Col'} = 5;
	}else{
		${'wWaku' . $WakuName . 'Col'} = ceil(${'wWaku' . $WakuName} / $rowCountforDay);
	}

	${'wWaku' . $WakuName . 'ColSum'} = ${'wWaku' . $WakuName . 'Col'} * $rowCountforDay;
	$wWakuColSum += ${'wWaku' . $WakuName . 'Col'};

	${'wWaku' . $WakuName . 'col'} = ceil(${'wWaku' . $WakuName} / $rowCountforDay);
	if($WakuName == 'PM')$wWakuPM1col = ${'wWaku' . $WakuName . 'Col'} ;
}

$EmptyFrameCount = 0;
########################################################
# 初日・土日祝考慮
########################################################

$wWakuSum1 = 0;
$wWakuSum2 = 0;

$week = ['日', '月', '火', '水', '木', '金', '土'];

foreach($beforeReserveDay as $key => $aReserveDay){
	$date = new DateTime($aReserveDay);
	$aSyoniti = false;
	$beforeKojiHoliday[$key] = false;
	$beforeHoliday[$key] = false;

	$SenyuDate = $date->format('Y-m-d');
	$result = array_search($SenyuDate, $SHUKUJITULIST);
	$YoubiCD =  $date->format('w');
	if ($result !== false || $YoubiCD == 0 || $YoubiCD == 6) {
		$beforeHoliday[$key] = true;
	}
	if (count($wHoliday) > 0) { //休工日
		$beforeKojiHoliday[$key] = (array_search($SenyuDate, $wHoliday) === false) ? false : true;
	}
	for ($j = 0; $j < count($WAKUPATTERN[$wWakuPattern]['AMPM']); $j++) {
		$ViewOrderNo = 1;
		$WakuName = $WAKUPATTERN[$wWakuPattern]['AMPM'][$j];
		$SyonitiKouryo = false;
		if ($wFirstDateFeature == 1 && strpos($WakuName, 'AM') !== FALSE && $aSyoniti) {
			$SyonitiKouryo = true;
		}else if($wFirstDateFeature == 2 && (strpos($WakuName, 'AM') !== FALSE || strpos($WakuName, 'PM1') !== FALSE) && $aSyoniti){
			$SyonitiKouryo = true;
		}
#★★★ここから
		$ban_rooms = 1;
		$max_ban = ceil(${'wWaku' . $WakuName} / $wHansu);
		$limit_ban = ceil(${'wWaku' . $WakuName . 'ColSum'} / $wHansu);
		$passed_rooms = 0;
		$x = 0;

		for ($k = 0; $k < ${'wWaku' . $WakuName . 'ColSum'}; $k++) {#10,8,8
			$dis_ban = floor($k / $limit_ban) + 1;
			if (!$beforeKojiHoliday[$key]) { //休工日以外
				if ($SyonitiKouryo) { //初日考慮でAMなら　空を入れる
					${'Waku' . $WakuName . 'Room'}[] = "";
				} else if($ban_rooms > $max_ban && $ban_rooms <= $limit_ban){
					${'Waku' . $WakuName . 'Room'}[] = "";
				}elseif(isset($Reserve[$SenyuDate][$WakuName][$x]) 
					&& (!$arrHanNo[$Reserve[$SenyuDate][$WakuName][$x]] || $arrHanNo[$Reserve[$SenyuDate][$WakuName][$x]] == $dis_ban)){
					// 辞退
					if(isset($UserData['ReplyFlg'][$Reserve[$SenyuDate][$WakuName][$x]]) && $UserData['ReplyFlg'][$Reserve[$SenyuDate][$WakuName][$x]] == '3'){
						${'Waku' . $WakuName . 'Room'}[] = "空き";
						$passed_rooms ++;
						$EmptyFrameCount ++;
					}else{
						${'Waku' . $WakuName . 'Room'}[] = $Reserve[$SenyuDate][$WakuName][$x];
						$passed_rooms ++;
						$x ++;
					}
				}elseif (${'wWaku' . $WakuName} > $passed_rooms) { //残った最大工事枠数分は空き
					${'Waku' . $WakuName . 'Room'}[] = "空き";
					$passed_rooms ++;
					$EmptyFrameCount ++;
				}else{
					${'Waku' . $WakuName . 'Room'}[] = "";
				}	
				$ban_rooms ++;
				if($ban_rooms > $limit_ban){
					$ban_rooms = 1;
				}
				$ViewOrderNo ++;
			}
		}
	}
}

$date = new DateTime($SenyuStartDate);
for ($i = 0; $i < $SenyuDateCnt; $i++) {
	$Syoniti[$i] = false;
	$KojiHoliday[$i] = false;
	$holiday[$i] = false;


	if ($i == 0) {
		$Syoniti[$i] = true;
	}
	$SenyuDate = $date->format('Y-m-d');
	$result = array_search($SenyuDate, $SHUKUJITULIST);
	$YoubiCD =  $date->format('w');
	if ($result !== false || $YoubiCD == 0 || $YoubiCD == 6) {
		$holiday[$i] = true;
	}
	if (count($wHoliday) > 0) { //休工日
		$KojiHoliday[$i] = (array_search($SenyuDate, $wHoliday) === false) ? false : true;
	}
	for ($j = 0; $j < count($WAKUPATTERN[$wWakuPattern]['AMPM']); $j++) {
		$ViewOrderNo = 1;
		$WakuName = $WAKUPATTERN[$wWakuPattern]['AMPM'][$j];
		//空きを考慮した枠数を取得
		// ${'Waku' . $WakuName . 'Su'} = getWakuRoomSu($WakuName, $Syoniti[$i], $holiday[$i], ${'wWaku' . $WakuName}, $wFirstDateFeature);
		//初日考慮
		$SyonitiKouryo = false;
		if ($wFirstDateFeature == 1 && strpos($WakuName, 'AM') !== FALSE && $Syoniti[$i]) {
			$SyonitiKouryo = true;
		}else if($wFirstDateFeature == 2 && (strpos($WakuName, 'AM') !== FALSE || strpos($WakuName, 'PM1') !== FALSE) && $Syoniti[$i]){
			$SyonitiKouryo = true;
		}
#★★★ここから
		$ban_rooms = 1;
		$max_ban = ceil(${'wWaku' . $WakuName} / $wHansu);
		$limit_ban = ceil(${'wWaku' . $WakuName . 'ColSum'} / $wHansu);
		$passed_rooms = 0;
		$x = 0;

		for ($k = 0; $k < ${'wWaku' . $WakuName . 'ColSum'}; $k++) {#10,8,8
			$dis_ban = floor($k / $limit_ban) + 1;
			if (!$KojiHoliday[$i]) { //休工日以外
				if ($SyonitiKouryo) { //初日考慮でAMなら　空を入れる
					${'Waku' . $WakuName . 'Room'}[] = "";
				} else if($ban_rooms > $max_ban && $ban_rooms <= $limit_ban){
					${'Waku' . $WakuName . 'Room'}[] = "";
				}elseif(isset($Reserve[$SenyuDate][$WakuName][$x]) 
					&& (!$arrHanNo[$Reserve[$SenyuDate][$WakuName][$x]] || $arrHanNo[$Reserve[$SenyuDate][$WakuName][$x]] == $dis_ban)){
					// 辞退
					if(isset($UserData['ReplyFlg'][$Reserve[$SenyuDate][$WakuName][$x]]) && $UserData['ReplyFlg'][$Reserve[$SenyuDate][$WakuName][$x]] == '3'){
						${'Waku' . $WakuName . 'Room'}[] = "空き";
						$passed_rooms ++;
						$EmptyFrameCount ++;
						$x ++;
					}else{
						${'Waku' . $WakuName . 'Room'}[] = $Reserve[$SenyuDate][$WakuName][$x];
						$passed_rooms ++;
						$x ++;
					}
				}elseif (${'wWaku' . $WakuName} > $passed_rooms) { //残った最大工事枠数分は空き
					${'Waku' . $WakuName . 'Room'}[] = "空き";
					$passed_rooms ++;
					$EmptyFrameCount ++;
				}else{
					${'Waku' . $WakuName . 'Room'}[] = "";
				}	
				$ban_rooms ++;
				if($ban_rooms > $limit_ban){
					$ban_rooms = 1;
				}
				$ViewOrderNo ++;
			}
			// if (!$KojiHoliday[$i]) { //休工日以外
				// if ($SyonitiKouryo) { //初日考慮でAMなら　空を入れる
				// 	${'Waku' . $WakuName . 'Room'}[] = "";
				// } elseif (${'Waku' . $WakuName . 'Su'} > $k && isset($KaiRoom3[$x])) { //空きを考慮した枠数分　部屋を入れる
				// 	${'Waku' . $WakuName . 'Room'}[] = $KaiRoom3[$x];
				// 	$x++;
				// } elseif (${'wWaku' . $WakuName} > $k) { //残った最大工事枠数分は空き
				// 	${'Waku' . $WakuName . 'Room'}[] = "空き";
				// } else {
				// 	${'Waku' . $WakuName . 'Room'}[] = "";
				// }
			// }
		}
	}
	$date->modify('+1 days');
}

foreach($afterReserveDay as $key => $aReserveDay){
	$date = new DateTime($aReserveDay);
	$aSyoniti = false;
	$afterKojiHoliday[$key] = false;
	$afterHoliday[$key] = false;

	$SenyuDate = $date->format('Y-m-d');
	$result = array_search($SenyuDate, $SHUKUJITULIST);
	$YoubiCD =  $date->format('w');
	if ($result !== false || $YoubiCD == 0 || $YoubiCD == 6) {
		$afterHoliday[$key] = true;
	}
	if (count($wHoliday) > 0) { //休工日
		$afterKojiHoliday[$key] = (array_search($SenyuDate, $wHoliday) === false) ? false : true;
	}
	for ($j = 0; $j < count($WAKUPATTERN[$wWakuPattern]['AMPM']); $j++) {
		$ViewOrderNo = 1;
		$WakuName = $WAKUPATTERN[$wWakuPattern]['AMPM'][$j];
		$SyonitiKouryo = false;
		if ($wFirstDateFeature == 1 && strpos($WakuName, 'AM') !== FALSE && $aSyoniti) {
			$SyonitiKouryo = true;
		}else if($wFirstDateFeature == 2 && (strpos($WakuName, 'AM') !== FALSE || strpos($WakuName, 'PM1') !== FALSE) && $aSyoniti){
			$SyonitiKouryo = true;
		}
#★★★ここから
		$ban_rooms = 1;
		$max_ban = ceil(${'wWaku' . $WakuName} / $wHansu);
		$limit_ban = ceil(${'wWaku' . $WakuName . 'ColSum'} / $wHansu);
		$passed_rooms = 0;
		$x = 0;

		for ($k = 0; $k < ${'wWaku' . $WakuName . 'ColSum'}; $k++) {#10,8,8
			$dis_ban = floor($k / $limit_ban) + 1;
			if (!$afterKojiHoliday[$key]) { //休工日以外
				if ($SyonitiKouryo) { //初日考慮でAMなら　空を入れる
					${'Waku' . $WakuName . 'Room'}[] = "";
				} else if($ban_rooms > $max_ban && $ban_rooms <= $limit_ban){
					${'Waku' . $WakuName . 'Room'}[] = "";
				}elseif(isset($Reserve[$SenyuDate][$WakuName][$x]) 
					&& (!$arrHanNo[$Reserve[$SenyuDate][$WakuName][$x]] || $arrHanNo[$Reserve[$SenyuDate][$WakuName][$x]] == $dis_ban)){
					// 辞退
					if(isset($UserData['ReplyFlg'][$Reserve[$SenyuDate][$WakuName][$x]]) && $UserData['ReplyFlg'][$Reserve[$SenyuDate][$WakuName][$x]] == '3'){
						${'Waku' . $WakuName . 'Room'}[] = "空き";
						$passed_rooms ++;
						$EmptyFrameCount ++;
					}else{
						${'Waku' . $WakuName . 'Room'}[] = $Reserve[$SenyuDate][$WakuName][$x];
						$passed_rooms ++;
						$x ++;
					}
				}elseif (${'wWaku' . $WakuName} > $passed_rooms) { //残った最大工事枠数分は空き
					${'Waku' . $WakuName . 'Room'}[] = "空き";
					$passed_rooms ++;
					$EmptyFrameCount ++;
				}else{
					${'Waku' . $WakuName . 'Room'}[] = "";
				}	
				$ban_rooms ++;
				if($ban_rooms > $limit_ban){
					$ban_rooms = 1;
				}
				$ViewOrderNo ++;
			}
		}
	}
}
########################################################
# 詳細工程表（イメージ）部分
########################################################

$Koteihyou = "<table border='1' width='600px' cellspacing='5' style='text-align:center' cellpadding='7' class='ex_table'>";
$Koteihyou .= "<tr><td class='ex_table2' width='130px'>日程</td>";
$Koteihyou .= "<td class='ex_table2' width='60px'>曜日</td>";
$Koteihyou .= "<td class='ex_table2' width='50px'>班</td>";
for ($i = 0; $i < count($WAKUPATTERN[$wWakuPattern]['AMPM']); $i++) {
	$WakuName = $WAKUPATTERN[$wWakuPattern]['AMPM'][$i];
	$disWakuName = $WakuName;
	$WakuPatternNames = $WAKUPATTERN[$wWakuPattern]['Name'];
	if (preg_match('/\((.*?)\)/', $WakuPatternNames, $matches)) {
		$WakuPatternNamesStr = $matches[1];
		$WakuPatternNamesArr = explode(",", $WakuPatternNamesStr);
		if(isset($WakuPatternNamesArr[$i]) && $WakuPatternNamesArr[$i] != '')
			$disWakuName = trim($WakuPatternNamesArr[$i]);
	}

	$Koteihyou .= "<td colspan = " . ${'wWaku' . $WakuName . 'Col'};
	if ($i % 2 == 0) {
		$Koteihyou .= " style='background-color:#add8e6;' ";
	} else {
		$Koteihyou .= " style='background-color:#e0ffff;' ";
	}
	$Koteihyou .= "class='ex_table2'>" . $disWakuName."</td>";

	${$WakuName . "index"} = 0;
	// echo "<pre>";
	// var_dump(${'Waku' . $WakuName . 'Room'});
	// echo "</pre>";

	${"wWaku".$WakuName."col"} = ${'wWaku' . $WakuName . 'Col'} ;#大文字、小文字がちがう！
	#echo "<br> ".__LINE__." Hensu :".${"wWaku".$WakuName."col"};
	

}
$Koteihyou .= "</tr>";
$holiday = array();

foreach($beforeReserveDay as $key => $aReserveDay){
	$date = new DateTime($aReserveDay);
	$SenyuDate = $date->format('Y-m-d');
	$week_str = $week[$date->format('w')];
	if($week_str == '土')
		$week_str = '<span style="color:#0070c0">'.$week_str.'</span>';
	else if($week_str == '日')
		$week_str = '<span style="color:#ff9999">'.$week_str.'</span>';

	if ($beforeKojiHoliday[$key]) { #★１休工日なら
		if ($beforeHoliday[$key]) { //土日祝なら
			$Koteihyou .= "<tr class='trtop trreserveday' id='row".$SenyuDate."'><td class='ex_table2' style='background-color:pink;'><span class='senyu_date'>" . $SenyuDate . "</span><div class='reserve_day'>予備日</div></td>";
			$Koteihyou .= "<td  class='ex_table2' style='background-color:pink;'>" . $week_str . "</td>";
			$Koteihyou .= "<td  class='ex_table2' style='background-color:pink;'></td>";
		} else { //平日なら
			$Koteihyou .= "<tr class='trtop trreserveday' id='row".$SenyuDate."'><td  class='ex_table2'><span class='senyu_date'>" . $SenyuDate . "</span><div class='reserve_day'>予備日</div></td>";
			$Koteihyou .= "<td class='ex_table2'>" . $week_str . "</td>";
			$Koteihyou .= "<td class='ex_table2'></td>";
		}
		$Koteihyou .= "<td colspan=" . $wWakuColSum . " class='ex_table2'>";
		$Koteihyou .= "休工日</td>";
	} else { #★１休工日でない場合
		if ($beforeHoliday[$key]) { //土日祝なら
			$Koteihyou .= "<tr class='trtop trreserveday' id='row".$SenyuDate."'><td rowspan=" . $rowCountforDay . " class='ex_table2' style='background-color:pink;'><span class='senyu_date'>" . $SenyuDate . "</span><div class='reserve_day'>予備日</div>@@blank_count@@</td>";
			$Koteihyou .= "<td rowspan=" . $rowCountforDay . " class='ex_table2' style='background-color:pink;'>" . $week_str . "</td>";
		} else { //平日なら
			$Koteihyou .= "<tr class='trtop trreserveday' id='row".$SenyuDate."'><td rowspan=" . $rowCountforDay . " class='ex_table2'><span class='senyu_date'>" . $SenyuDate . "</span><div class='reserve_day'>予備日</div>@@blank_count@@</td>";
			$Koteihyou .= "<td rowspan=" . $rowCountforDay . " class='ex_table2'>" . $week_str . "</td>";
		}
		$banNo = 1;
		$abanNo = $banNo;
		$banRowspan = floor($rowCountforDay / $wHansu);
		$arrBlankCount = [];

		for ($j = 0; $j < $rowCountforDay; $j++) {
			if ($j != 0)
				$Koteihyou .= "<tr class='trreserveday'>";

			if($j % $banRowspan == 0){
				if ($beforeHoliday[$key]) { //土日祝なら
					$Koteihyou .= "<td rowspan=" . $banRowspan . " class='ex_table2' style='background-color:pink;'>" . $banNo . "</td>";
				} else { //平日なら
					$Koteihyou .= "<td rowspan=" . $banRowspan . " class='ex_table2'>" . $banNo . "</td>";
				}
				$abanNo = $banNo;
				$banNo ++;
			}
	
			for ($k = 0; $k < count($WAKUPATTERN[$wWakuPattern]['AMPM']); $k++) {
				$WakuName = $WAKUPATTERN[$wWakuPattern]['AMPM'][$k];
				if(!isset($arrBlankCount[$WakuName]))
					$arrBlankCount[$WakuName] = 0;
				$WakuStart = $WAKUPATTERN[$wWakuPattern]['StartTime'][$k];
				for ($l = 0; $l < ${'wWaku' . $WakuName . 'Col'}; $l++) {
					$ViewOrderNo = $l + 1 + $j * ${'wWaku' . $WakuName . 'Col'};
					$Koteihyou_event = "";

					if ($k % 2 == 0) {
						$Koteihyou_temp = "<td class='link_cell' style='background-color:#ffff9e;' @event@>";
					} else {
						$Koteihyou_temp = "<td class='link_cell' style='background-color:#ffffcf;' @event@>";
					}
					if (!${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}]) {
						$Koteihyou_temp = "<td class='link_cell link_cell_none no-action' style='background-color:#d3d3d3; cursor:default'>";
					}

					$addedTime = $MinuteTime * $l ;
					$SenyuDateTime = date('H:i', strtotime("+$addedTime minutes", strtotime($WakuStart)));

					if (${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] == "空き") {
						if ($k % 2 == 0) {
							$Koteihyou_temp = "<td class='link_cell_blank' style='background-color:#ffff9e;' @event@>";
						} else {
							$Koteihyou_temp = "<td class='link_cell_blank' style='background-color:#ffffcf;' @event@>";
						}


						$Koteihyou_temp .= ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}];
						$Koteihyou_event = "onclick=\"clickBtn7('".$SenyuDate."', '".$SenyuDateTime."', '".$WakuName."', '".$abanNo."', '".$ViewOrderNo."')\"";

						$arrBlankCount[$WakuName] ++;
					} else if(${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}]) {
						$tID = ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}];
						$aLastName = $UserData['LastName'][$tID] ;
						$aTEL = $UserData['TEL'][$tID] ;
						$aEMail = $UserData['EMail'][$tID] ;
						$aMemo = $UserData['Memo'][$tID] ;
						$ReplyFlg = $UserData['ReplyFlg'][$tID] ;
						$ConfirmFlg = $UserData['ConfirmFlg'][$tID] ;
						$aTimeExactHour = $TimeExactHour[$tID];
						$aTimeExactMinutes = $TimeExactMinutes[$tID];
						if($aTimeExactHour || $aTimeExactMinutes)
							$aTimeExact = $aTimeExactHour.":".$aTimeExactMinutes;
						else
							$aTimeExact = null;
						$aTimeMeaning = $TimeMeaning[$tID];
						if($aTimeExact){
							$Koteihyou_temp .= '<div class="multi_val tooltip-container">';
								$Koteihyou_temp .= '<font class="haslink" style="font-size:20px; text-decoration:underline;"> <b>' . ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] . '</b></font>';
								$Koteihyou_temp .= '<div class="exact_time">'.$aTimeExact.'</div>';
								$Koteihyou_temp .= '<div class="tooltip-text">';
									if($ReplyFlg == '3')
										$Koteihyou_temp .= '<span class="decline">「辞退」</span> <br>';
									else
										$Koteihyou_temp .= '<span class="confirmed">「確定」</span> <br>';
									$Koteihyou_temp .= '<span class="lb">部屋番号:</span> '.${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}].'<br>';
									$Koteihyou_temp .= '<span class="lb">名前:</span> '.$aLastName.'<br>';
									$Koteihyou_temp .= '<span class="lb">連絡先:</span> '.$aTEL.'<br>';
									$Koteihyou_temp .= '<span class="lb">メールアドレス:</span> '.$aEMail.'<br>';
									$Koteihyou_temp .= '<span class="lb">時間指定:</span> '.$aTimeExact.' '.$aTimeMeaning.'<br>';
									$Koteihyou_temp .= '<span class="lb">備考:</span> '.nl2br($aMemo).'<br>';
								$Koteihyou_temp .= '</div>';
							$Koteihyou_temp .= '</div>';

						}else if($ReplyFlg || $ConfirmFlg){
							$Koteihyou_temp .= '<font class="haslink tooltip-container" style="font-size:20px; text-decoration:underline;"> <b>' . ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] . '</b>';
								$Koteihyou_temp .= '<div class="tooltip-text">';
									if($ReplyFlg == '3')
										$Koteihyou_temp .= '<span class="decline">「辞退」</span> <br>';
									else
										$Koteihyou_temp .= '<span class="confirmed">「確定」</span> <br>';
									$Koteihyou_temp .= '<span class="lb">部屋番号:</span> '.${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}].'<br>';
									$Koteihyou_temp .= '<span class="lb">名前:</span> '.$aLastName.'<br>';
									$Koteihyou_temp .= '<span class="lb">連絡先:</span> '.$aTEL.'<br>';
									$Koteihyou_temp .= '<span class="lb">メールアドレス:</span> '.$aEMail.'<br>';
									$Koteihyou_temp .= '<span class="lb">時間指定:</span> '.$aTimeExact.' '.$aTimeMeaning.'<br>';
									$Koteihyou_temp .= '<span class="lb">備考:</span> '.nl2br($aMemo).'<br>';
								$Koteihyou_temp .= '</div>';
							$Koteihyou_temp .= '</font>';
						
						}else{
							$Koteihyou_temp .= '<font class="tooltip-container" style="font-size:20px"> <b>' . ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] . '</b>';
								$Koteihyou_temp .= '<div class="tooltip-text">';
									$Koteihyou_temp .= '<span class="temporary">「仮日程」</span> <br>';
									$Koteihyou_temp .= '<span class="lb">部屋番号:</span> '.${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}].'<br>';
								$Koteihyou_temp .= '</div>';
							$Koteihyou_temp .= '</font>';
						}
						$Koteihyou_event = "onclick=\"clickBtn8('".$tID."' ,'".$aLastName."'  ,'".$aTEL."','".$aMemo."' , '".$SenyuDate."', '".$SenyuDateTime."', '".$WakuName."', '".$aTimeExactHour."', '".$aTimeExactMinutes."', '".$aTimeMeaning."', '".$abanNo."', '".$ViewOrderNo."', '".$ReplyFlg."', '".$ConfirmFlg."' ,'".$aEMail."' )\"";
					}
					// $KoteihyouEX[] = ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}];

					// echo "<br>" . $WakuName . ":" . ${$WakuName . "index"};
					$Koteihyou_temp = str_replace("@event@", $Koteihyou_event, $Koteihyou_temp);
					$Koteihyou .= $Koteihyou_temp;

					$Koteihyou .= "</td>";
					${$WakuName . "index"}++;
				}
			}
			$Koteihyou .= "</tr>";
		}
		$strBlankCount = '';
		foreach($arrBlankCount as $keyBlank => $valBlank){
			if($strBlankCount != '')
				$strBlankCount .= ' ';
			$strBlankCount .= $keyBlank.':'.$valBlank;
		}
		$Koteihyou = str_replace("@@blank_count@@", '<div class="blank_count">'.$strBlankCount.'</div>', $Koteihyou);

	}
}

$date = new DateTime($SenyuStartDate);
for ($i = 0; $i < $SenyuDateCnt; $i++) {
	$SenyuDate = $date->format('Y-m-d');
	$week_str = $week[$date->format('w')];
	if($week_str == '土')
		$week_str = '<span style="color:#0070c0">'.$week_str.'</span>';
	else if($week_str == '日')
		$week_str = '<span style="color:#ff9999">'.$week_str.'</span>';

	if ($KojiHoliday[$i]) { #★１休工日なら
		if ($holiday[$i]) { //土日祝なら
			$Koteihyou .= "<tr class='trtop trholiday' id='row".$SenyuDate."'><td class='ex_table2' style='background-color:pink;'><span class='senyu_date'>" . $SenyuDate . "</span></td>";
			$Koteihyou .= "<td  class='ex_table2' style='background-color:pink;'>" . $week_str . "</td>";
			$Koteihyou .= "<td  class='ex_table2' style='background-color:pink;'></td>";
		} else { //平日なら
			$Koteihyou .= "<tr class='trtop trholiday' id='row".$SenyuDate."'><td  class='ex_table2'><span class='senyu_date'>" . $SenyuDate . "</span></td>";
			$Koteihyou .= "<td class='ex_table2'>" . $week_str . "</td>";
			$Koteihyou .= "<td class='ex_table2'></td>";
		}
		$Koteihyou .= "<td colspan=" . $wWakuColSum . " class='ex_table2'>";
		$Koteihyou .= "休工日</td>";
	} else { #★１休工日でない場合
		if ($holiday[$i]) { //土日祝なら
			$Koteihyou .= "<tr class='trtop' id='row".$SenyuDate."'><td rowspan=" . $rowCountforDay . " class='ex_table2' style='background-color:pink;'><span class='senyu_date'>" . $SenyuDate . "</span>@@blank_count@@</td>";
			$Koteihyou .= "<td rowspan=" . $rowCountforDay . " class='ex_table2' style='background-color:pink;'>" . $week_str . "</td>";
		} else { //平日なら
			$Koteihyou .= "<tr class='trtop' id='row".$SenyuDate."'><td rowspan=" . $rowCountforDay . " class='ex_table2'><span class='senyu_date'>" . $SenyuDate . "</span>@@blank_count@@</td>";
			$Koteihyou .= "<td rowspan=" . $rowCountforDay . " class='ex_table2'>" . $week_str . "</td>";
		}
		$banNo = 1;
		$abanNo = $banNo;
		$banRowspan = floor($rowCountforDay / $wHansu);
		$arrBlankCount = [];

		for ($j = 0; $j < $rowCountforDay; $j++) {
			if ($j != 0)
				$Koteihyou .= "<tr>";

			if($j % $banRowspan == 0){
				if ($holiday[$i]) { //土日祝なら
					$Koteihyou .= "<td rowspan=" . $banRowspan . " class='ex_table2' style='background-color:pink;'>" . $banNo . "</td>";
				} else { //平日なら
					$Koteihyou .= "<td rowspan=" . $banRowspan . " class='ex_table2'>" . $banNo . "</td>";
				}
				$abanNo = $banNo;
				$banNo ++;
			}
	
			for ($k = 0; $k < count($WAKUPATTERN[$wWakuPattern]['AMPM']); $k++) {
				$WakuName = $WAKUPATTERN[$wWakuPattern]['AMPM'][$k];
				if(!isset($arrBlankCount[$WakuName]))
					$arrBlankCount[$WakuName] = 0;
				$WakuStart = $WAKUPATTERN[$wWakuPattern]['StartTime'][$k];
				for ($l = 0; $l < ${'wWaku' . $WakuName . 'Col'}; $l++) {
					$ViewOrderNo = $l + 1 + $j * ${'wWaku' . $WakuName . 'Col'};
					$Koteihyou_event = "";

					if ($k % 2 == 0) {
						$Koteihyou_temp = "<td class='link_cell' style='background-color:#ffff9e;' @event@>";
					} else {
						$Koteihyou_temp = "<td class='link_cell' style='background-color:#ffffcf;' @event@>";
					}
					if (!${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}]) {
						$Koteihyou_temp = "<td class='link_cell link_cell_none no-action' style='background-color:#d3d3d3; cursor:default'>";
					}

					$addedTime = $MinuteTime * $l ;
					$SenyuDateTime = date('H:i', strtotime("+$addedTime minutes", strtotime($WakuStart)));

					if (${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] == "空き") {
						if ($k % 2 == 0) {
							$Koteihyou_temp = "<td class='link_cell_blank' style='background-color:#ffff9e;' @event@>";
						} else {
							$Koteihyou_temp = "<td class='link_cell_blank' style='background-color:#ffffcf;' @event@>";
						}


						$Koteihyou_temp .= ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}];
						$Koteihyou_event = "onclick=\"clickBtn7('".$SenyuDate."', '".$SenyuDateTime."', '".$WakuName."', '".$abanNo."', '".$ViewOrderNo."')\"";

						$arrBlankCount[$WakuName] ++;
					} else if(${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}]) {
						$tID = ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}];
						$aLastName = $UserData['LastName'][$tID] ;
						$aTEL = $UserData['TEL'][$tID] ;
						$aEMail = $UserData['EMail'][$tID] ;
						$aMemo = $UserData['Memo'][$tID] ;
						$ReplyFlg = $UserData['ReplyFlg'][$tID] ;
						$ConfirmFlg = $UserData['ConfirmFlg'][$tID] ;
						$aTimeExactHour = $TimeExactHour[$tID];
						$aTimeExactMinutes = $TimeExactMinutes[$tID];
						if($aTimeExactHour || $aTimeExactMinutes)
							$aTimeExact = $aTimeExactHour.":".$aTimeExactMinutes;
						else
							$aTimeExact = null;
						$aTimeMeaning = $TimeMeaning[$tID];
						if($aTimeExact){
							$Koteihyou_temp .= '<div class="multi_val tooltip-container">';
								$Koteihyou_temp .= '<font class="haslink" style="font-size:20px; text-decoration:underline;"> <b>' . ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] . '</b></font>';
								$Koteihyou_temp .= '<div class="exact_time">'.$aTimeExact.'</div>';
								$Koteihyou_temp .= '<div class="tooltip-text">';
									if($ReplyFlg == '3')
										$Koteihyou_temp .= '<span class="decline">「辞退」</span> <br>';
									else
										$Koteihyou_temp .= '<span class="confirmed">「確定」</span> <br>';
									$Koteihyou_temp .= '<span class="lb">部屋番号:</span> '.${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}].'<br>';
									$Koteihyou_temp .= '<span class="lb">名前:</span> '.$aLastName.'<br>';
									$Koteihyou_temp .= '<span class="lb">連絡先:</span> '.$aTEL.'<br>';
									$Koteihyou_temp .= '<span class="lb">メールアドレス:</span> '.$aEMail.'<br>';
									$Koteihyou_temp .= '<span class="lb">時間指定:</span> '.$aTimeExact.' '.$aTimeMeaning.'<br>';
									$Koteihyou_temp .= '<span class="lb">備考:</span> '.nl2br($aMemo).'<br>';
								$Koteihyou_temp .= '</div>';
							$Koteihyou_temp .= '</div>';

						}else if($ReplyFlg || $ConfirmFlg){
							$Koteihyou_temp .= '<font class="haslink tooltip-container" style="font-size:20px; text-decoration:underline;"> <b>' . ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] . '</b>';
								$Koteihyou_temp .= '<div class="tooltip-text">';
									if($ReplyFlg == '3')
										$Koteihyou_temp .= '<span class="decline">「辞退」</span> <br>';
									else
										$Koteihyou_temp .= '<span class="confirmed">「確定」</span> <br>';
									$Koteihyou_temp .= '<span class="lb">部屋番号:</span> '.${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}].'<br>';
									$Koteihyou_temp .= '<span class="lb">名前:</span> '.$aLastName.'<br>';
									$Koteihyou_temp .= '<span class="lb">連絡先:</span> '.$aTEL.'<br>';
									$Koteihyou_temp .= '<span class="lb">メールアドレス:</span> '.$aEMail.'<br>';
									$Koteihyou_temp .= '<span class="lb">時間指定:</span> '.$aTimeExact.' '.$aTimeMeaning.'<br>';
									$Koteihyou_temp .= '<span class="lb">備考:</span> '.nl2br($aMemo).'<br>';
								$Koteihyou_temp .= '</div>';
							$Koteihyou_temp .= '</font>';
						
						}else{
							$Koteihyou_temp .= '<font class="tooltip-container" style="font-size:20px"> <b>' . ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] . '</b>';
								$Koteihyou_temp .= '<div class="tooltip-text">';
									$Koteihyou_temp .= '<span class="temporary">「仮日程」</span> <br>';
									$Koteihyou_temp .= '<span class="lb">部屋番号:</span> '.${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}].'<br>';
								$Koteihyou_temp .= '</div>';
							$Koteihyou_temp .= '</font>';
						}
						$Koteihyou_event = "onclick=\"clickBtn8('".$tID."' ,'".$aLastName."'  ,'".$aTEL."','".$aMemo."' , '".$SenyuDate."', '".$SenyuDateTime."', '".$WakuName."', '".$aTimeExactHour."', '".$aTimeExactMinutes."', '".$aTimeMeaning."', '".$abanNo."', '".$ViewOrderNo."', '".$ReplyFlg."', '".$ConfirmFlg."','".$aEMail."' )\"";
					}
					// $KoteihyouEX[] = ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}];

					// echo "<br>" . $WakuName . ":" . ${$WakuName . "index"};
					$Koteihyou_temp = str_replace("@event@", $Koteihyou_event, $Koteihyou_temp);
					$Koteihyou .= $Koteihyou_temp;

					$Koteihyou .= "</td>";
					${$WakuName . "index"}++;
				}
			}
			$Koteihyou .= "</tr>";
		}
		$strBlankCount = '';
		foreach($arrBlankCount as $keyBlank => $valBlank){
			if($strBlankCount != '')
				$strBlankCount .= ' ';
			$strBlankCount .= $keyBlank.':'.$valBlank;
		}
		$Koteihyou = str_replace("@@blank_count@@", '<div class="blank_count">'.$strBlankCount.'</div>', $Koteihyou);

	}#休工日

	$date->modify('+1 days');
}

foreach($afterReserveDay as $key => $aReserveDay){
	$date = new DateTime($aReserveDay);
	$SenyuDate = $date->format('Y-m-d');
	$week_str = $week[$date->format('w')];
	if($week_str == '土')
		$week_str = '<span style="color:#0070c0">'.$week_str.'</span>';
	else if($week_str == '日')
		$week_str = '<span style="color:#ff9999">'.$week_str.'</span>';

	if ($afterKojiHoliday[$key]) { #★１休工日なら
		if ($afterHoliday[$key]) { //土日祝なら
			$Koteihyou .= "<tr class='trtop trreserveday' id='row".$SenyuDate."'><td class='ex_table2' style='background-color:pink;'><span class='senyu_date'>" . $SenyuDate . "</span><div class='reserve_day'>予備日</div></td>";
			$Koteihyou .= "<td  class='ex_table2' style='background-color:pink;'>" . $week_str . "</td>";
			$Koteihyou .= "<td  class='ex_table2' style='background-color:pink;'></td>";
		} else { //平日なら
			$Koteihyou .= "<tr class='trtop trreserveday' id='row".$SenyuDate."'><td  class='ex_table2'><span class='senyu_date'>" . $SenyuDate . "</span><div class='reserve_day'>予備日</div></td>";
			$Koteihyou .= "<td class='ex_table2'>" . $week_str . "</td>";
			$Koteihyou .= "<td class='ex_table2'></td>";
		}
		$Koteihyou .= "<td colspan=" . $wWakuColSum . " class='ex_table2'>";
		$Koteihyou .= "休工日</td>";
	} else { #★１休工日でない場合
		if ($afterHoliday[$key]) { //土日祝なら
			$Koteihyou .= "<tr class='trtop trreserveday' id='row".$SenyuDate."'><td rowspan=" . $rowCountforDay . " class='ex_table2' style='background-color:pink;'><span class='senyu_date'>" . $SenyuDate . "</span><div class='reserve_day'>予備日</div>@@blank_count@@</td>";
			$Koteihyou .= "<td rowspan=" . $rowCountforDay . " class='ex_table2' style='background-color:pink;'>" . $week_str . "</td>";
		} else { //平日なら
			$Koteihyou .= "<tr class='trtop trreserveday' id='row".$SenyuDate."'><td rowspan=" . $rowCountforDay . " class='ex_table2'><span class='senyu_date'>" . $SenyuDate . "</span><div class='reserve_day'>予備日</div>@@blank_count@@</td>";
			$Koteihyou .= "<td rowspan=" . $rowCountforDay . " class='ex_table2'>" . $week_str . "</td>";
		}
		$banNo = 1;
		$abanNo = $banNo;
		$banRowspan = floor($rowCountforDay / $wHansu);
		$arrBlankCount = [];

		for ($j = 0; $j < $rowCountforDay; $j++) {
			if ($j != 0)
				$Koteihyou .= "<tr class='trreserveday'>";

			if($j % $banRowspan == 0){
				if ($afterHoliday[$key]) { //土日祝なら
					$Koteihyou .= "<td rowspan=" . $banRowspan . " class='ex_table2' style='background-color:pink;'>" . $banNo . "</td>";
				} else { //平日なら
					$Koteihyou .= "<td rowspan=" . $banRowspan . " class='ex_table2'>" . $banNo . "</td>";
				}
				$abanNo = $banNo;
				$banNo ++;
			}
	
			for ($k = 0; $k < count($WAKUPATTERN[$wWakuPattern]['AMPM']); $k++) {
				$WakuName = $WAKUPATTERN[$wWakuPattern]['AMPM'][$k];
				if(!isset($arrBlankCount[$WakuName]))
					$arrBlankCount[$WakuName] = 0;
				$WakuStart = $WAKUPATTERN[$wWakuPattern]['StartTime'][$k];
				for ($l = 0; $l < ${'wWaku' . $WakuName . 'Col'}; $l++) {
					$ViewOrderNo = $l + 1 + $j * ${'wWaku' . $WakuName . 'Col'};
					$Koteihyou_event = "";

					if ($k % 2 == 0) {
						$Koteihyou_temp = "<td class='link_cell' style='background-color:#ffff9e;' @event@>";
					} else {
						$Koteihyou_temp = "<td class='link_cell' style='background-color:#ffffcf;' @event@>";
					}
					if (!${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}]) {
						$Koteihyou_temp = "<td class='link_cell link_cell_none no-action' style='background-color:#d3d3d3; cursor:default'>";
					}

					$addedTime = $MinuteTime * $l ;
					$SenyuDateTime = date('H:i', strtotime("+$addedTime minutes", strtotime($WakuStart)));

					if (${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] == "空き") {
						if ($k % 2 == 0) {
							$Koteihyou_temp = "<td class='link_cell_blank' style='background-color:#ffff9e;' @event@>";
						} else {
							$Koteihyou_temp = "<td class='link_cell_blank' style='background-color:#ffffcf;' @event@>";
						}


						$Koteihyou_temp .= ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}];
						$Koteihyou_event = "onclick=\"clickBtn7('".$SenyuDate."', '".$SenyuDateTime."', '".$WakuName."', '".$abanNo."', '".$ViewOrderNo."')\"";

						$arrBlankCount[$WakuName] ++;
					} else if(${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}]) {
						$tID = ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}];
						$aLastName = $UserData['LastName'][$tID] ;
						$aTEL = $UserData['TEL'][$tID] ;
						$aEMail = $UserData['EMail'][$tID] ;
						$aMemo = $UserData['Memo'][$tID] ;
						$ReplyFlg = $UserData['ReplyFlg'][$tID] ;
						$ConfirmFlg = $UserData['ConfirmFlg'][$tID] ;
						$aTimeExactHour = $TimeExactHour[$tID];
						$aTimeExactMinutes = $TimeExactMinutes[$tID];
						if($aTimeExactHour || $aTimeExactMinutes)
							$aTimeExact = $aTimeExactHour.":".$aTimeExactMinutes;
						else
							$aTimeExact = null;
						$aTimeMeaning = $TimeMeaning[$tID];
						if($aTimeExact){
							$Koteihyou_temp .= '<div class="multi_val tooltip-container">';
								$Koteihyou_temp .= '<font class="haslink" style="font-size:20px; text-decoration:underline;"> <b>' . ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] . '</b></font>';
								$Koteihyou_temp .= '<div class="exact_time">'.$aTimeExact.'</div>';
								$Koteihyou_temp .= '<div class="tooltip-text">';
									if($ReplyFlg == '3')
										$Koteihyou_temp .= '<span class="decline">「辞退」</span> <br>';
									else
										$Koteihyou_temp .= '<span class="confirmed">「確定」</span> <br>';
									$Koteihyou_temp .= '<span class="lb">部屋番号:</span> '.${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}].'<br>';
									$Koteihyou_temp .= '<span class="lb">名前:</span> '.$aLastName.'<br>';
									$Koteihyou_temp .= '<span class="lb">連絡先:</span> '.$aTEL.'<br>';
									$Koteihyou_temp .= '<span class="lb">メールアドレス:</span> '.$aEMail.'<br>';
									$Koteihyou_temp .= '<span class="lb">時間指定:</span> '.$aTimeExact.' '.$aTimeMeaning.'<br>';
									$Koteihyou_temp .= '<span class="lb">備考:</span> '.nl2br($aMemo).'<br>';
								$Koteihyou_temp .= '</div>';
							$Koteihyou_temp .= '</div>';

						}else if($ReplyFlg || $ConfirmFlg){
							$Koteihyou_temp .= '<font class="haslink tooltip-container" style="font-size:20px; text-decoration:underline;"> <b>' . ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] . '</b>';
								$Koteihyou_temp .= '<div class="tooltip-text">';
									if($ReplyFlg == '3')
										$Koteihyou_temp .= '<span class="decline">「辞退」</span> <br>';
									else
										$Koteihyou_temp .= '<span class="confirmed">「確定」</span> <br>';
									$Koteihyou_temp .= '<span class="lb">部屋番号:</span> '.${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}].'<br>';
									$Koteihyou_temp .= '<span class="lb">名前:</span> '.$aLastName.'<br>';
									$Koteihyou_temp .= '<span class="lb">連絡先:</span> '.$aTEL.'<br>';
									$Koteihyou_temp .= '<span class="lb">メールアドレス:</span> '.$aEMail.'<br>';
									$Koteihyou_temp .= '<span class="lb">時間指定:</span> '.$aTimeExact.' '.$aTimeMeaning.'<br>';
									$Koteihyou_temp .= '<span class="lb">備考:</span> '.nl2br($aMemo).'<br>';
								$Koteihyou_temp .= '</div>';
							$Koteihyou_temp .= '</font>';
						
						}else{
							$Koteihyou_temp .= '<font class="tooltip-container" style="font-size:20px"> <b>' . ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}] . '</b>';
								$Koteihyou_temp .= '<div class="tooltip-text">';
									$Koteihyou_temp .= '<span class="temporary">「仮日程」</span> <br>';
									$Koteihyou_temp .= '<span class="lb">部屋番号:</span> '.${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}].'<br>';
								$Koteihyou_temp .= '</div>';
							$Koteihyou_temp .= '</font>';
						}
						$Koteihyou_event = "onclick=\"clickBtn8('".$tID."' ,'".$aLastName."'  ,'".$aTEL."','".$aMemo."' , '".$SenyuDate."', '".$SenyuDateTime."', '".$WakuName."', '".$aTimeExactHour."', '".$aTimeExactMinutes."', '".$aTimeMeaning."', '".$abanNo."', '".$ViewOrderNo."', '".$ReplyFlg."', '".$ConfirmFlg."' ,'".$aEMail."' )\"";
					}
					// $KoteihyouEX[] = ${'Waku' . $WakuName . 'Room'}[${$WakuName . "index"}];

					// echo "<br>" . $WakuName . ":" . ${$WakuName . "index"};
					$Koteihyou_temp = str_replace("@event@", $Koteihyou_event, $Koteihyou_temp);
					$Koteihyou .= $Koteihyou_temp;

					$Koteihyou .= "</td>";
					${$WakuName . "index"}++;
				}
			}
			$Koteihyou .= "</tr>";
		}
		$strBlankCount = '';
		foreach($arrBlankCount as $keyBlank => $valBlank){
			if($strBlankCount != '')
				$strBlankCount .= ' ';
			$strBlankCount .= $keyBlank.':'.$valBlank;
		}
		$Koteihyou = str_replace("@@blank_count@@", '<div class="blank_count">'.$strBlankCount.'</div>', $Koteihyou);

	}
}

$Koteihyou .= "</table>";

#######################################移植終わり

	########################################################
	# コンテンツ表示
	########################################################

	$CNT_FILE = "sh_list.tpl";
	$myTemplate = new SPFWTemplate($CNT_FILE, $MyCarrier);
	$HiddenValues = $myTemplate->getValuesToPass();
	$myTemplate->convertTags();
	$myTemplate->outputTemplate();
	unset($myTemplate);
	unset($myLog);

########################################################
# 関数群
########################################################
//最大工事枠数から空きの数を引いた枠数を取得
function getWakuRoomSu($WakuName, $Syoniti, $holiday, $MaxWakuSu, $FirstDateFeature)
{
	// 第一引数：$WakuName string
	//  AM・PMなど

	// 第二引数： $Syoniti boolean
	//  初日かどうか

	// 第三引数：$holiday boolean
	//  土日祝かどうか

	// 第四引数：$MaxWakuSu int
	//  枠ごとの最大工事枠数

	// 第五引数：$FirstDateFeature int
	//  初日考慮
	//  1：午前中NG
	//  2：15時までNG


	//最大工事枠数から空きの数を引く計算をする
	if ($Syoniti == true && $holiday) { //初日・土日祝　(最大工事枠数/2)-1

		$WakuRoomSu = ceil($MaxWakuSu / 2) - 1;#切り上げて1引く。　

		if ($FirstDateFeature == 1 && strpos($WakuName, 'AM') !== FALSE) { //初日午前NG
			$WakuRoomSu = 0;
		} elseif ($FirstDateFeature == 2 && (strpos($WakuName, 'AM') !== FALSE || strpos($WakuName, 'PM1') !== FALSE)) { //初日15：00以降OK
			$WakuRoomSu = 0;
		}
	} elseif ($Syoniti == true) { //初日・平日　最大工事枠数-2

		$WakuRoomSu = $MaxWakuSu - 2;

		if ($FirstDateFeature == 1 && strpos($WakuName, 'AM') !== FALSE) { //初日午前NG
			$WakuRoomSu = 0;
		} elseif ($FirstDateFeature == 2 && (strpos($WakuName, 'AM') !== FALSE || strpos($WakuName, 'PM1') !== FALSE)) { //初日15：00以降OK
			$WakuRoomSu = 0;
		}
	} elseif ($holiday) { //土日祝 最大工事枠数/2
		$WakuRoomSu = ceil($MaxWakuSu / 2);
	} else { //平日 最大工事枠数-1
		$WakuRoomSu = $MaxWakuSu - 1;
	}
	if ($WakuRoomSu < 0) {
		$WakuRoomSu = 0;
	}
	return $WakuRoomSu;
}


?>
