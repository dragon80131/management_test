<?php

/**
 * Bukkenクラス
 *
 * メニューマスタを扱うクラスです。
 *
 * @package
 * @access  public
 * @author  MasahitoSAMEKAWA <masahito@souppot.jp>
 * @create  2003/01/31
 * @version 0.1
 **/

// 依存
include_once _CLS_DIR . "SPFWLog.cls";

class Bukken
{
	/**
	 * データベースクラスのインスタンス
	 */
	public $myDB;
	/**
	 * レコードカウント
	 */
	public $RecCnt;
	/**
	 * クエリ実行結果セット
	 */
	public $tmprtn;
	/**
	 * 対象テーブル名称
	 */
	public $TableName;
	/**
	 * メニューコード
	 *
	 * 連番
	 */
	public $BukkenCD;
	public $ClientCD;
	public $BukkenName;
	public $SagyoName;
	public $TantoCD;

	public $Hansu;
	public $Kojijun;
	public $Address;
	public $FormatType;
	public $FormatType2;
	public $Memo;
	public $SenyuStartDate;
	public $SenyuEndDate;
	public $SenyuStartDate1;
	public $SenyuEndDate1;
	public $KyoyobuStartDate;
	public $KyoyobuEndDate;
	public $SenyubuStartDate;
	public $SenyubuEndDate;
	public $YoyakuEndDate;
	public $CloseTime;
	public $Kosu;
	public $Kaidaka;
	public $MaxWakuSu;
	public $BukkenNameEn;
	public $Owner_name;
	public $FirstDateFeature;
	public $ReceptionDate;
	public $KanriKinmu;
	public $KanriTEL;
	public $Sokai;
	public $WakuPattern;

	public $BukkenMemo;
	public $BuildingName;

	public $Same_kiki_sougou_flg;

	public $Biko_forReport;
	public $BoukaBikou;
	public $Biko;
	public $BousaiStatus;
	public $WorkPlace;
	public $BousaiFlg;
	public $BousaiEndDate;
	public $BousaiStartDate;
	public $BousaiLastUpdater;
	public $BousaiLastUpdated;

	public $KanriCompanyBousaiCD;
	public $GyosyaBousaiCD;
	public $BousaiKojiTime;
	public $BousaiTenkenMonth;
	public $TatoFlg;

	public $Date1;
	public $Date2;
	public $Date3;
	public $Date4;
	public $Date5;
	public $Date6;
	public $SagyoLastUpdater;
	public $SagyoLastUpdated;

	public $KanriCompanyCD;
	public $HaifuDownloadDate;
	public $ReportSubmitted;
	public $TantoCD1;
	public $TantoCD2;
	public $TantoCD3;
	public $TantoCD4;
	public $TantoCD5;

	public $ExistTenkenKikan_Sougou;
	public $BukkenStatus;
	public $ExistTenkenKikan;
	public $GyosyaCD;
	public $SougouAMKojiTime;
	public $SougouPMKojiTime;
	public $SougouTenkenKikan;
	public $KikiAMKojiTime;
	public $KikiPMKojiTime;
	public $KikiTenkenKikan;
	public $SougouTenkenMonth;
	public $KikiTenkenMonth;
	public $MinuteTime;
	public $Holiday1;
	public $ReserveDay;
	public $FirstKojiDate;
	public $LastKojiDate;

	public $KenmeiNo;
	public $LatestIraiKojiKind;
	public $KikiStatus;
	public $SougouStatus;
	public $UketsukeShimekiriDate;
	/**
	 * 無効フラグ
	 */
	public $MukouFlg;
	/**
	 * 作成日時
	 */
	public $Created;
	/**
	 * 作成者
	 */
	public $Creator;
	/**
	 * 更新日時
	 */
	public $Updated;
	/**
	 * 更新者
	 */
	 public $Updater;
	 public $Err;

	function __construct($myDB = "", $BukkenCD = -1)
	{
		// ログ準備
		$myLog = new SPFWLog();

		$this->Err = "";

		if ($myDB == "") {
			$this->Err = "データベースへのコネクションがありません";
			$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
			unset($myLog);
			return FALSE;
		}

		// 整数変換
		settype($BukkenCD, "integer");
		// 整数チェック
		if (!is_long($BukkenCD) or $BukkenCD == 0) {
			$this->Err = "ユーザーコード が不正です";
			$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
			unset($myLog);
			return FALSE;
		}

		$this->myDB = $myDB;
		$this->TableName = "tBukkenM";
		$this->BukkenCD = $BukkenCD;

		return TRUE;
	}

	function initialize()
	{
		$this->Err = "";

		$this->BukkenCD = "";
		$this->ClientCD = "";
		$this->BukkenName = "";
		$this->SagyoName = "";
		$this->TantoCD = "";
		$this->Hansu = "";
		$this->Kojijun = "";
		$this->Address = "";
		$this->FormatType = "";
		$this->FormatType2 = "";
		$this->Memo = "";
		$this->SenyuStartDate = "";
		$this->SenyuEndDate = "";
		$this->SenyuStartDate1 = "";
		$this->SenyuEndDate1 = "";
		$this->KyoyobuStartDate = "";
		$this->KyoyobuEndDate = "";
		$this->SenyubuStartDate = "";
		$this->SenyubuEndDate = "";
		$this->YoyakuEndDate = "";
		$this->CloseTime = "";
		$this->Kosu = "";
		$this->Kaidaka = "";
		$this->MaxWakuSu = "";
		$this->BukkenNameEn = "";
		$this->Owner_name = "";
		$this->FirstDateFeature = "";
		$this->ReceptionDate = "";
		$this->KanriKinmu = "";
		$this->KanriTEL = "";
		$this->Sokai = "";
		$this->WakuPattern = "";


		$this->BukkenMemo = "";
		$this->BuildingName = "";
		
		$this->Same_kiki_sougou_flg = "";
		$this->Biko_forReport = "";
		$this->BoukaBikou = "";
		$this->Biko = "";
		$this->BousaiStatus = "";
		$this->WorkPlace = "";
		$this->BousaiFlg = "";
		$this->BousaiEndDate = "";
		$this->BousaiStartDate = "";
		$this->BousaiLastUpdater = "";
		$this->BousaiLastUpdated = "";
		$this->KanriCompanyBousaiCD = "";
		$this->GyosyaBousaiCD = "";
		$this->BousaiKojiTime = "";
		$this->BousaiTenkenMonth = "";
		$this->TatoFlg = "";
		$this->Date1 = "";
		$this->Date2 = "";
		$this->Date3 = "";
		$this->Date4 = "";
		$this->Date5 = "";
		$this->Date6 = "";
		$this->SagyoLastUpdater = "";
		$this->SagyoLastUpdated = "";
		$this->KanriCompanyCD = "";
		$this->HaifuDownloadDate = "";
		$this->ReportSubmitted = "";
		$this->TantoCD1 = "";
		$this->TantoCD2 = "";
		$this->TantoCD3 = "";
		$this->TantoCD4 = "";
		$this->TantoCD5 = "";
		$this->ExistTenkenKikan_Sougou = "";
		$this->BukkenStatus = "";
		$this->ExistTenkenKikan = "";
		$this->GyosyaCD = "";
		$this->SougouAMKojiTime = "";
		$this->SougouPMKojiTime = "";
		$this->SougouTenkenKikan = "";
		$this->KikiAMKojiTime = "";
		$this->KikiPMKojiTime = "";
		$this->KikiTenkenKikan = "";
		$this->SougouTenkenMonth = "";
		$this->KikiTenkenMonth = "";
		$this->MinuteTime = "";
		$this->Holiday1 = "";
		$this->ReserveDay = "";
		$this->FirstKojiDate = "";
		$this->KenmeiNo = "";
		$this->LastKojiDate = "";
		$this->LatestIraiKojiKind = "";
		$this->KikiStatus = "";
		$this->SougouStatus = "";
		$this->UketsukeShimekiriDate = "";

		$this->MukouFlg = "";
		$this->Created = "";
		$this->Creator = "";
		$this->Updated = "";
		$this->Updater = "";
	}

	//抽出
	function executeSelect($WhereStr = "", $OrderByStr = "")
	{
		// ログ準備
		$myLog = new SPFWLog();

		$this->Err = "";

		//設定
		if ($this->myDB == "") {
			$this->Err = "データベースへのコネクションがありません";
			$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
			unset($myLog);
			return FALSE;
		}

		// ページプロパティ取得
		$sqlMoji = "select ";

		$sqlMoji .= " BukkenCD ";
		$sqlMoji .= ", ClientCD ";
		$sqlMoji .= ", BukkenName ";
		$sqlMoji .= ", SagyoName ";
		$sqlMoji .= ", TantoCD ";

		$sqlMoji .= ", Hansu ";
		$sqlMoji .= ", Kojijun ";
		$sqlMoji .= ", Address ";
		$sqlMoji .= ", FormatType ";
		$sqlMoji .= ", FormatType2 ";
		$sqlMoji .= ", Memo ";
		$sqlMoji .= ", SenyuStartDate ";
		$sqlMoji .= ", SenyuEndDate ";
		$sqlMoji .= ", SenyuStartDate1 ";
		$sqlMoji .= ", SenyuEndDate1 ";
		$sqlMoji .= ", KyoyobuStartDate ";
		$sqlMoji .= ", KyoyobuEndDate ";
		$sqlMoji .= ", SenyubuStartDate ";
		$sqlMoji .= ", SenyubuEndDate ";
		$sqlMoji .= ", YoyakuEndDate ";
		$sqlMoji .= ", CloseTime ";
		$sqlMoji .= ", Kosu ";
		$sqlMoji .= ", Kaidaka ";
		$sqlMoji .= ", MaxWakuSu ";
		$sqlMoji .= ", BukkenNameEn ";
		$sqlMoji .= ", Owner_name ";
		$sqlMoji .= ", FirstDateFeature ";
		$sqlMoji .= ", ReceptionDate ";
		$sqlMoji .= ", KanriKinmu ";
		$sqlMoji .= ", KanriTEL ";
		$sqlMoji .= ", Sokai ";
		$sqlMoji .= ", WakuPattern ";
		
		$sqlMoji .= ", BukkenMemo ";
		$sqlMoji .= ", BuildingName ";

		
		$sqlMoji .= ", Same_kiki_sougou_flg ";
		$sqlMoji .= ", Biko_forReport ";
		$sqlMoji .= ", BoukaBikou ";
		$sqlMoji .= ", Biko ";
		$sqlMoji .= ", BousaiStatus ";
		$sqlMoji .= ", WorkPlace ";
		$sqlMoji .= ", BousaiFlg ";
		$sqlMoji .= ", BousaiEndDate ";
		$sqlMoji .= ", BousaiStartDate ";
		$sqlMoji .= ", BousaiLastUpdater ";
		$sqlMoji .= ", BousaiLastUpdated ";
		$sqlMoji .= ", KanriCompanyBousaiCD ";
		$sqlMoji .= ", GyosyaBousaiCD ";
		$sqlMoji .= ", BousaiKojiTime ";
		$sqlMoji .= ", BousaiTenkenMonth ";
		$sqlMoji .= ", TatoFlg ";
		$sqlMoji .= ", Date1 ";
		$sqlMoji .= ", Date2 ";
		$sqlMoji .= ", Date3 ";
		$sqlMoji .= ", Date4 ";
		$sqlMoji .= ", Date5 ";
		$sqlMoji .= ", Date6 ";
		$sqlMoji .= ", SagyoLastUpdater ";
		$sqlMoji .= ", SagyoLastUpdated ";
		$sqlMoji .= ", KanriCompanyCD ";
		$sqlMoji .= ", HaifuDownloadDate ";
		$sqlMoji .= ", ReportSubmitted ";
		$sqlMoji .= ", TantoCD1 ";
		$sqlMoji .= ", TantoCD2 ";
		$sqlMoji .= ", TantoCD3 ";
		$sqlMoji .= ", TantoCD4 ";
		$sqlMoji .= ", TantoCD5 ";
		$sqlMoji .= ", ExistTenkenKikan_Sougou ";
		$sqlMoji .= ", BukkenStatus ";
		$sqlMoji .= ", ExistTenkenKikan ";
		$sqlMoji .= ", GyosyaCD ";
		$sqlMoji .= ", SougouAMKojiTime ";
		$sqlMoji .= ", SougouPMKojiTime ";
		$sqlMoji .= ", SougouTenkenKikan ";

		$sqlMoji .= ", KikiAMKojiTime ";
		$sqlMoji .= ", KikiPMKojiTime ";
		$sqlMoji .= ", KikiTenkenKikan ";
		$sqlMoji .= ", SougouTenkenMonth ";
		$sqlMoji .= ", KikiTenkenMonth ";
		$sqlMoji .= ", MinuteTime ";
		$sqlMoji .= ", Holiday1 ";
		$sqlMoji .= ", ReserveDay ";
		$sqlMoji .= ", FirstKojiDate ";
		$sqlMoji .= ", KenmeiNo ";
		$sqlMoji .= ", LastKojiDate ";
		$sqlMoji .= ", LatestIraiKojiKind ";
		$sqlMoji .= ", KikiStatus ";
		$sqlMoji .= ", SougouStatus ";
		$sqlMoji .= ", UketsukeShimekiriDate";

		$sqlMoji .= ", MukouFlg";
		$sqlMoji .= ", Created";
		$sqlMoji .= ", Creator";
		$sqlMoji .= ", Updated";
		$sqlMoji .= ", Updater";
		$sqlMoji .= " from $this->TableName";

		if ($WhereStr != "")
			$sqlMoji = $sqlMoji . " where " . $WhereStr;

		if ($OrderByStr != "")
			$sqlMoji = $sqlMoji . " order by " . $OrderByStr;

		$rtn = $this->myDB->executeQuery($sqlMoji);
		if (!$rtn) {
			$this->Err = "レコード取得に失敗しました";
			$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
			unset($myLog);
			return FALSE;
		} else {
			$this->RecCnt = $this->myDB->getNumberOfRows($rtn);
			if ($this->RecCnt == 1) {
				$this->tmprtn = $rtn;
				$this->getDataSet(0);
			} else if ($this->RecCnt > 1) {
				$this->tmprtn = $rtn;
			}
		}

		return TRUE;
	}

	//タプル取得
	function getDataSet($RowNo)
	{
		if ($RowNo >= $this->RecCnt) {
			$this->Err = "行番号が不正です";
			return FALSE;
		} else {
			list(

				$this->BukkenCD,
				$this->ClientCD,
				$this->BukkenName,
				$this->SagyoName,
				$this->TantoCD,

				$this->Hansu,
				$this->Kojijun,
				$this->Address,
				$this->FormatType,
				$this->FormatType2,
				$this->Memo,
				$this->SenyuStartDate,
				$this->SenyuEndDate,
				$this->SenyuStartDate1,
				$this->SenyuEndDate1,
				$this->KyoyobuStartDate,
				$this->KyoyobuEndDate,
				$this->SenyubuStartDate,
				$this->SenyubuEndDate,
				$this->YoyakuEndDate,
				$this->CloseTime,
				$this->Kosu,
				$this->Kaidaka,
				$this->MaxWakuSu,
				$this->BukkenNameEn,
				$this->Owner_name,
				$this->FirstDateFeature,
				$this->ReceptionDate,
				$this->KanriKinmu,
				$this->KanriTEL,
				$this->Sokai,
				$this->WakuPattern,

				$this->BukkenMemo,
				$this->BuildingName,
				$this->Same_kiki_sougou_flg,
				$this->Biko_forReport,
				$this->BoukaBikou,
				$this->Biko,
				$this->BousaiStatus,
				$this->WorkPlace,
				$this->BousaiFlg,
				$this->BousaiEndDate,
				$this->BousaiStartDate,
				$this->BousaiLastUpdater,
				$this->BousaiLastUpdated,
				$this->KanriCompanyBousaiCD,
				$this->GyosyaBousaiCD,
				$this->BousaiKojiTime,
				$this->BousaiTenkenMonth,
				$this->TatoFlg,
				$this->Date1,
				$this->Date2,
				$this->Date3,
				$this->Date4,
				$this->Date5,
				$this->Date6,
				$this->SagyoLastUpdater,
				$this->SagyoLastUpdated,
				$this->KanriCompanyCD,
				$this->HaifuDownloadDate,
				$this->ReportSubmitted,
				$this->TantoCD1,
				$this->TantoCD2,
				$this->TantoCD3,
				$this->TantoCD4,
				$this->TantoCD5,
				$this->ExistTenkenKikan_Sougou,
				$this->BukkenStatus,
				$this->ExistTenkenKikan,
				$this->GyosyaCD,
				$this->SougouAMKojiTime,
				$this->SougouPMKojiTime,
				$this->SougouTenkenKikan,

				$this->KikiAMKojiTime,
				$this->KikiPMKojiTime,
				$this->KikiTenkenKikan,
				$this->SougouTenkenMonth,
				$this->KikiTenkenMonth,
				$this->MinuteTime,
				$this->Holiday1,
				$this->ReserveDay,
				$this->FirstKojiDate,
				$this->KenmeiNo,
				$this->LastKojiDate,
				$this->LatestIraiKojiKind,
				$this->KikiStatus,
				$this->SougouStatus,
				$this->UketsukeShimekiriDate,

				$this->MukouFlg,
				$this->Created,
				$this->Creator,
				$this->Updated,
				$this->Updater
			) = $this->myDB->fetchRow($this->tmprtn, $RowNo);

			$this->ClientCD = stripslashes($this->ClientCD);
			$this->BukkenName = stripslashes($this->BukkenName);
			$this->SagyoName = stripslashes($this->SagyoName);
			$this->TantoCD = stripslashes($this->TantoCD);
			$this->Hansu = stripslashes($this->Hansu);
			$this->Kojijun = stripslashes($this->Kojijun);
			$this->Address = stripslashes($this->Address);
			$this->FormatType = stripslashes($this->FormatType);
			$this->FormatType2 = stripslashes($this->FormatType2);
			$this->Memo = stripslashes($this->Memo);
			$this->SenyuStartDate = stripslashes($this->SenyuStartDate);
			$this->SenyuEndDate = stripslashes($this->SenyuEndDate);
			$this->SenyuStartDate1 = stripslashes($this->SenyuStartDate1);
			$this->SenyuEndDate1 = stripslashes($this->SenyuEndDate1);
			$this->KyoyobuStartDate = stripslashes($this->KyoyobuStartDate);
			$this->KyoyobuEndDate = stripslashes($this->KyoyobuEndDate);
			$this->SenyubuStartDate = stripslashes($this->SenyubuStartDate);
			$this->SenyubuEndDate = stripslashes($this->SenyubuEndDate);
			$this->YoyakuEndDate = stripslashes($this->YoyakuEndDate);
			$this->CloseTime = stripslashes($this->CloseTime);
			$this->Kosu = stripslashes($this->Kosu);
			$this->Kaidaka = stripslashes($this->Kaidaka);
			$this->MaxWakuSu = stripslashes($this->MaxWakuSu);
			$this->BukkenNameEn = stripslashes($this->BukkenNameEn);
			$this->Owner_name = stripslashes($this->Owner_name);
			$this->FirstDateFeature = stripslashes($this->FirstDateFeature);
			$this->ReceptionDate = stripslashes($this->ReceptionDate);
			$this->KanriKinmu = stripslashes($this->KanriKinmu);
			$this->KanriTEL = stripslashes($this->KanriTEL);
			$this->Sokai = stripslashes($this->Sokai);
			$this->WakuPattern = stripslashes($this->WakuPattern);
			$this->BukkenMemo = stripslashes($this->BukkenMemo);
			$this->BuildingName = stripslashes($this->BuildingName);
			$this->Same_kiki_sougou_flg = stripslashes($this->Same_kiki_sougou_flg);
			$this->Biko_forReport = stripslashes($this->Biko_forReport);
			$this->BoukaBikou = stripslashes($this->BoukaBikou);
			$this->Biko = stripslashes($this->Biko);
			$this->BousaiStatus = stripslashes($this->BousaiStatus);
			$this->WorkPlace = stripslashes($this->WorkPlace);
			$this->BousaiFlg = stripslashes($this->BousaiFlg);
			$this->BousaiEndDate = stripslashes($this->BousaiEndDate);
			$this->BousaiStartDate = stripslashes($this->BousaiStartDate);
			$this->BousaiLastUpdater = stripslashes($this->BousaiLastUpdater);
			$this->BousaiLastUpdated = stripslashes($this->BousaiLastUpdated);
			$this->KanriCompanyBousaiCD = stripslashes($this->KanriCompanyBousaiCD);
			$this->GyosyaBousaiCD = stripslashes($this->GyosyaBousaiCD);
			$this->BousaiKojiTime = stripslashes($this->BousaiKojiTime);
			$this->BousaiTenkenMonth = stripslashes($this->BousaiTenkenMonth);
			$this->TatoFlg = stripslashes($this->TatoFlg);
			$this->Date1 = stripslashes($this->Date1);
			$this->Date2 = stripslashes($this->Date2);
			$this->Date3 = stripslashes($this->Date3);
			$this->Date4 = stripslashes($this->Date4);
			$this->Date5 = stripslashes($this->Date5);
			$this->Date6 = stripslashes($this->Date6);
			$this->SagyoLastUpdater = stripslashes($this->SagyoLastUpdater);
			$this->SagyoLastUpdated = stripslashes($this->SagyoLastUpdated);
			$this->KanriCompanyCD = stripslashes($this->KanriCompanyCD);
			$this->HaifuDownloadDate = stripslashes($this->HaifuDownloadDate);
			$this->ReportSubmitted = stripslashes($this->ReportSubmitted);
			$this->TantoCD1 = stripslashes($this->TantoCD1);
			$this->TantoCD2 = stripslashes($this->TantoCD2);
			$this->TantoCD3 = stripslashes($this->TantoCD3);
			$this->TantoCD4 = stripslashes($this->TantoCD4);
			$this->TantoCD5 = stripslashes($this->TantoCD5);
			$this->ExistTenkenKikan_Sougou = stripslashes($this->ExistTenkenKikan_Sougou);
			$this->BukkenStatus = stripslashes($this->BukkenStatus);
			$this->ExistTenkenKikan = stripslashes($this->ExistTenkenKikan);
			$this->GyosyaCD = stripslashes($this->GyosyaCD);
			$this->SougouAMKojiTime = stripslashes($this->SougouAMKojiTime);
			$this->SougouPMKojiTime = stripslashes($this->SougouPMKojiTime);
			$this->SougouTenkenKikan = stripslashes($this->SougouTenkenKikan);

			$this->KikiAMKojiTime = stripslashes($this->KikiAMKojiTime);
			$this->KikiPMKojiTime = stripslashes($this->KikiPMKojiTime);
			$this->KikiTenkenKikan = stripslashes($this->KikiTenkenKikan);
			$this->SougouTenkenMonth = stripslashes($this->SougouTenkenMonth);
			$this->KikiTenkenMonth = stripslashes($this->KikiTenkenMonth);
			$this->MinuteTime = stripslashes($this->MinuteTime);
			$this->Holiday1 = stripslashes($this->Holiday1);
			$this->ReserveDay = stripslashes($this->ReserveDay);
			$this->FirstKojiDate = stripslashes($this->FirstKojiDate);
			$this->KenmeiNo = stripslashes($this->KenmeiNo);
			$this->LastKojiDate = stripslashes($this->LastKojiDate);
			$this->LatestIraiKojiKind = stripslashes($this->LatestIraiKojiKind);
			$this->KikiStatus = stripslashes($this->KikiStatus);
			$this->SougouStatus = stripslashes($this->SougouStatus);
			$this->UketsukeShimekiriDate = stripslashes($this->UketsukeShimekiriDate);
		}
	}

	/* アップデート */
	function executeUpdate()
	{
		// ログ準備
		$myLog = new SPFWLog();

		// エラークリア
		$this->Err = "";

		//設定
		if ($this->myDB == "") {
			$this->Err = "データベースへのコネクションがありません";
			$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
			unset($myLog);
			return FALSE;
		}

		if ($this->BukkenCD < 0) {
			// 新規
			// step 1. 新規番号取得のためトランザクション開始
			if (!($this->myDB->executeQuery("BEGIN"))) {
				$this->Err = "追加処理に失敗しました(BEGIN)";
				$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
				unset($myLog);
				return FALSE;
			} else {
				// step 2. ロック
				if (!($this->myDB->lockTable($this->TableName))) {
					$this->Err = "追加処理に失敗しました(LOCK)";
					$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
					$this->myDB->executeQuery("ROLLBACK");
					unset($myLog);
					return FALSE;
				} else {
					// step 3. 取得
					$sqlMoji = "select BukkenCD from $this->TableName order by BukkenCD desc limit 1";
					$maxCD = $this->myDB->getOneValue($sqlMoji);
					$maxCD = ($maxCD == -1) ? 0 : $maxCD;
					$maxCD++;

					// step 4. インサート
					$sqlMoji = "Insert into $this->TableName (";
					$sqlMoji .= "BukkenCD";

					if ($this->ClientCD != "") $sqlMoji .= ", ClientCD";
					if ($this->BukkenName != "") $sqlMoji .= ", BukkenName";
					if ($this->SagyoName != "") $sqlMoji .= ", SagyoName";
					if ($this->TantoCD != "") $sqlMoji .= ", TantoCD";

					if ($this->Hansu != "") $sqlMoji .= ", Hansu";
					if ($this->Kojijun != "") $sqlMoji .= ", Kojijun";
					if ($this->Address != "") $sqlMoji .= ", Address";
					if ($this->FormatType != "") $sqlMoji .= ", FormatType";
					if ($this->FormatType2 != "") $sqlMoji .= ", FormatType2";
					if ($this->Memo != "") $sqlMoji .= ", Memo";
					if ($this->SenyuStartDate != "") $sqlMoji .= ", SenyuStartDate";
					if ($this->SenyuEndDate != "") $sqlMoji .= ", SenyuEndDate";
					if ($this->SenyuStartDate1 != "") $sqlMoji .= ", SenyuStartDate1";
					if ($this->SenyuEndDate1 != "") $sqlMoji .= ", SenyuEndDate1";
					if ($this->YoyakuEndDate != "") $sqlMoji .= ", YoyakuEndDate";
					if ($this->KyoyobuStartDate != "") $sqlMoji .= ", KyoyobuStartDate";
					if ($this->KyoyobuEndDate != "") $sqlMoji .= ", KyoyobuEndDate";
					if ($this->SenyubuStartDate != "") $sqlMoji .= ", SenyubuStartDate";
					if ($this->SenyubuEndDate != "") $sqlMoji .= ", SenyubuEndDate";
					if ($this->CloseTime != "") $sqlMoji .= ", CloseTime";
					if ($this->Kosu != "") $sqlMoji .= ", Kosu";
					if ($this->Kaidaka != "") $sqlMoji .= ", Kaidaka";
					if ($this->MaxWakuSu != "") $sqlMoji .= ", MaxWakuSu";
					if ($this->BukkenNameEn != "") $sqlMoji .= ", BukkenNameEn";
					if ($this->Owner_name != "") $sqlMoji .= ", Owner_name";
					if ($this->FirstDateFeature != "") $sqlMoji .= ", FirstDateFeature";
					if ($this->ReceptionDate != "") $sqlMoji .= ", ReceptionDate";
					if ($this->KanriKinmu != "") $sqlMoji .= ", KanriKinmu";
					if ($this->KanriTEL != "") $sqlMoji .= ", KanriTEL";
					if ($this->Sokai != "") $sqlMoji .= ", Sokai";
					if ($this->WakuPattern != "") $sqlMoji .= ", WakuPattern";
					
					if ($this->BukkenMemo != "") $sqlMoji .= ", BukkenMemo";
					if ($this->BuildingName != "") $sqlMoji .= ", BuildingName";
					
					if ($this->Same_kiki_sougou_flg != "") $sqlMoji .= ", Same_kiki_sougou_flg";
					if ($this->Biko_forReport != "") $sqlMoji .= ", Biko_forReport";
					if ($this->BoukaBikou != "") $sqlMoji .= ", BoukaBikou";
					if ($this->Biko != "") $sqlMoji .= ", Biko";
					if ($this->BousaiStatus != "") $sqlMoji .= ", BousaiStatus";
					if ($this->WorkPlace != "") $sqlMoji .= ", WorkPlace";
					if ($this->BousaiFlg != "") $sqlMoji .= ", BousaiFlg";
					if ($this->BousaiEndDate != "") $sqlMoji .= ", BousaiEndDate";
					if ($this->BousaiStartDate != "") $sqlMoji .= ", BousaiStartDate";
					if ($this->BousaiLastUpdater != "") $sqlMoji .= ", BousaiLastUpdater";
					if ($this->BousaiLastUpdated != "") $sqlMoji .= ", BousaiLastUpdated";
					if ($this->KanriCompanyBousaiCD != "") $sqlMoji .= ", KanriCompanyBousaiCD";
					if ($this->GyosyaBousaiCD != "") $sqlMoji .= ", GyosyaBousaiCD";
					if ($this->BousaiKojiTime != "") $sqlMoji .= ", BousaiKojiTime";
					if ($this->BousaiTenkenMonth != "") $sqlMoji .= ", BousaiTenkenMonth";
					if ($this->TatoFlg != "") $sqlMoji .= ", TatoFlg";
					if ($this->Date1 != "") $sqlMoji .= ", Date1";
					if ($this->Date2 != "") $sqlMoji .= ", Date2";
					if ($this->Date3 != "") $sqlMoji .= ", Date3";
					if ($this->Date4 != "") $sqlMoji .= ", Date4";
					if ($this->Date5 != "") $sqlMoji .= ", Date5";
					if ($this->Date6 != "") $sqlMoji .= ", Date6";
					if ($this->SagyoLastUpdater != "") $sqlMoji .= ", SagyoLastUpdater";
					if ($this->SagyoLastUpdated != "") $sqlMoji .= ", SagyoLastUpdated";
					if ($this->KanriCompanyCD != "") $sqlMoji .= ", KanriCompanyCD";
					if ($this->HaifuDownloadDate != "") $sqlMoji .= ", HaifuDownloadDate";
					if ($this->ReportSubmitted != "") $sqlMoji .= ", ReportSubmitted";
					if ($this->TantoCD1 != "") $sqlMoji .= ", TantoCD1";
					if ($this->TantoCD2 != "") $sqlMoji .= ", TantoCD2";
					if ($this->TantoCD3 != "") $sqlMoji .= ", TantoCD3";
					if ($this->TantoCD4 != "") $sqlMoji .= ", TantoCD4";
					if ($this->TantoCD5 != "") $sqlMoji .= ", TantoCD5";
					if ($this->ExistTenkenKikan_Sougou != "") $sqlMoji .= ", ExistTenkenKikan_Sougou";
					if ($this->BukkenStatus != "") $sqlMoji .= ", BukkenStatus";
					if ($this->ExistTenkenKikan != "") $sqlMoji .= ", ExistTenkenKikan";
					if ($this->GyosyaCD != "") $sqlMoji .= ", GyosyaCD";
					if ($this->SougouAMKojiTime != "") $sqlMoji .= ", SougouAMKojiTime";
					if ($this->SougouPMKojiTime != "") $sqlMoji .= ", SougouPMKojiTime";
					if ($this->SougouTenkenKikan != "") $sqlMoji .= ", SougouTenkenKikan";

					if ($this->KikiAMKojiTime != "") $sqlMoji .= ", KikiAMKojiTime";
					if ($this->KikiPMKojiTime != "") $sqlMoji .= ", KikiPMKojiTime";
					if ($this->KikiTenkenKikan != "") $sqlMoji .= ", KikiTenkenKikan";
					if ($this->SougouTenkenMonth != "") $sqlMoji .= ", SougouTenkenMonth";
					if ($this->KikiTenkenMonth != "") $sqlMoji .= ", KikiTenkenMonth";
					if ($this->MinuteTime != "") $sqlMoji .= ", MinuteTime";
					if ($this->Holiday1 != "") $sqlMoji .= ", Holiday1";
					if ($this->ReserveDay != "") $sqlMoji .= ", ReserveDay";
					if ($this->FirstKojiDate != "") $sqlMoji .= ", FirstKojiDate";
					if ($this->KenmeiNo != "") $sqlMoji .= ", KenmeiNo";
					if ($this->LastKojiDate != "") $sqlMoji .= ", LastKojiDate";
					if ($this->LatestIraiKojiKind != "") $sqlMoji .= ", LatestIraiKojiKind";
					if ($this->KikiStatus != "") $sqlMoji .= ", KikiStatus ";
					if ($this->SougouStatus != "") $sqlMoji .= ", SougouStatus ";
					if ($this->UketsukeShimekiriDate != "") $sqlMoji .= ",UketsukeShimek";

					if ($this->MukouFlg != "") $sqlMoji .= ", MukouFlg";
					$sqlMoji .= ", Created";
					if ($this->Creator != "") $sqlMoji .= ", Creator";
					$sqlMoji .= ", Updated";
					if ($this->Updater != "") $sqlMoji .= ", Updater";
					$sqlMoji .= ") values (";
					$sqlMoji .= "$maxCD";

					if ($this->ClientCD != "") $sqlMoji .= ", '" . addslashes($this->ClientCD) . "'";
					if ($this->BukkenName != "") $sqlMoji .= ", '" . addslashes($this->BukkenName) . "'";
					if ($this->SagyoName != "") $sqlMoji .= ", '" . addslashes($this->SagyoName) . "'";
					if ($this->TantoCD != "") $sqlMoji .= ", '" . addslashes($this->TantoCD) . "'";

					if ($this->Hansu != "") $sqlMoji .= ", '" . addslashes($this->Hansu) . "'";
					if ($this->Kojijun != "") $sqlMoji .= ", '" . addslashes($this->Kojijun) . "'";
					if ($this->Address != "") $sqlMoji .= ", '" . addslashes($this->Address) . "'";
					if ($this->FormatType != "") $sqlMoji .= ", '" . addslashes($this->FormatType) . "'";
					if ($this->FormatType2 != "") $sqlMoji .= ", '" . addslashes($this->FormatType2) . "'";
					if ($this->Memo != "") $sqlMoji .= ", '" . addslashes($this->Memo) . "'";
					if ($this->SenyuStartDate != "") $sqlMoji .= ", '" . addslashes($this->SenyuStartDate) . "'";
					if ($this->SenyuEndDate != "") $sqlMoji .= ", '" . addslashes($this->SenyuEndDate) . "'";
					if ($this->SenyuStartDate1 != "") $sqlMoji .= ", '" . addslashes($this->SenyuStartDate1) . "'";
					if ($this->SenyuEndDate1 != "") $sqlMoji .= ", '" . addslashes($this->SenyuEndDate1) . "'";
					if ($this->KyoyobuStartDate != "") $sqlMoji .= ", '" . addslashes($this->KyoyobuStartDate) . "'";
					if ($this->KyoyobuEndDate != "") $sqlMoji .= ", '" . addslashes($this->KyoyobuEndDate) . "'";
					if ($this->SenyubuStartDate != "") $sqlMoji .= ", '" . addslashes($this->SenyubuStartDate) . "'";
					if ($this->SenyubuEndDate != "") $sqlMoji .= ", '" . addslashes($this->SenyubuEndDate) . "'";
					if ($this->YoyakuEndDate != "") $sqlMoji .= ", '" . addslashes($this->YoyakuEndDate) . "'";
					if ($this->CloseTime != "") $sqlMoji .= ", '" . addslashes($this->CloseTime) . "'";
					if ($this->Kosu != "") $sqlMoji .= ", '" . addslashes($this->Kosu) . "'";
					if ($this->Kaidaka != "") $sqlMoji .= ", '" . addslashes($this->Kaidaka) . "'";
					if ($this->MaxWakuSu != "") $sqlMoji .= ", '" . addslashes($this->MaxWakuSu) . "'";
					if ($this->BukkenNameEn != "") $sqlMoji .= ", '" . addslashes($this->BukkenNameEn) . "'";
					if ($this->Owner_name != "") $sqlMoji .= ", '" . addslashes($this->Owner_name) . "'";
					if ($this->FirstDateFeature != "") $sqlMoji .= ", '" . addslashes($this->FirstDateFeature) . "'";
					if ($this->ReceptionDate != "") $sqlMoji .= ", '" . addslashes($this->ReceptionDate) . "'";
					if ($this->KanriKinmu != "") $sqlMoji .= ", '" . addslashes($this->KanriKinmu) . "'";
					if ($this->KanriTEL != "") $sqlMoji .= ", '" . addslashes($this->KanriTEL) . "'";
					if ($this->Sokai != "") $sqlMoji .= ", '" . addslashes($this->Sokai) . "'";
					if ($this->WakuPattern != "") $sqlMoji .= ", '" . addslashes($this->WakuPattern) . "'";

					if ($this->BukkenMemo != "") $sqlMoji .= ", '" . addslashes($this->BukkenMemo) . "'";
					if ($this->BuildingName != "") $sqlMoji .= ", '" . addslashes($this->BuildingName) . "'";
					if ($this->Same_kiki_sougou_flg != "") $sqlMoji .= ", '" . addslashes($this->Same_kiki_sougou_flg) . "'";
					if ($this->Biko_forReport != "") $sqlMoji .= ", '" . addslashes($this->Biko_forReport) . "'";
					if ($this->BoukaBikou != "") $sqlMoji .= ", '" . addslashes($this->BoukaBikou) . "'";
					if ($this->Biko != "") $sqlMoji .= ", '" . addslashes($this->Biko) . "'";
					if ($this->BousaiStatus != "") $sqlMoji .= ", '" . addslashes($this->BousaiStatus) . "'";
					if ($this->WorkPlace != "") $sqlMoji .= ", '" . addslashes($this->WorkPlace) . "'";
					if ($this->BousaiFlg != "") $sqlMoji .= ", '" . addslashes($this->BousaiFlg) . "'";
					if ($this->BousaiEndDate != "") $sqlMoji .= ", '" . addslashes($this->BousaiEndDate) . "'";
					if ($this->BousaiStartDate != "") $sqlMoji .= ", '" . addslashes($this->BousaiStartDate) . "'";
					if ($this->BousaiLastUpdater != "") $sqlMoji .= ", '" . addslashes($this->BousaiLastUpdater) . "'";
					if ($this->BousaiLastUpdated != "") $sqlMoji .= ", '" . addslashes($this->BousaiLastUpdated) . "'";
					if ($this->KanriCompanyBousaiCD != "") $sqlMoji .= ", '" . addslashes($this->KanriCompanyBousaiCD) . "'";
					if ($this->GyosyaBousaiCD != "") $sqlMoji .= ", '" . addslashes($this->GyosyaBousaiCD) . "'";
					if ($this->BousaiKojiTime != "") $sqlMoji .= ", '" . addslashes($this->BousaiKojiTime) . "'";
					if ($this->BousaiTenkenMonth != "") $sqlMoji .= ", '" . addslashes($this->BousaiTenkenMonth) . "'";
					if ($this->TatoFlg != "") $sqlMoji .= ", '" . addslashes($this->TatoFlg) . "'";
					if ($this->Date1 != "") $sqlMoji .= ", '" . addslashes($this->Date1) . "'";
					if ($this->Date2 != "") $sqlMoji .= ", '" . addslashes($this->Date2) . "'";
					if ($this->Date3 != "") $sqlMoji .= ", '" . addslashes($this->Date3) . "'";
					if ($this->Date4 != "") $sqlMoji .= ", '" . addslashes($this->Date4) . "'";
					if ($this->Date5 != "") $sqlMoji .= ", '" . addslashes($this->Date5) . "'";
					if ($this->Date6 != "") $sqlMoji .= ", '" . addslashes($this->Date6) . "'";
					if ($this->SagyoLastUpdater != "") $sqlMoji .= ", '" . addslashes($this->SagyoLastUpdater) . "'";
					if ($this->SagyoLastUpdated != "") $sqlMoji .= ", '" . addslashes($this->SagyoLastUpdated) . "'";
					if ($this->KanriCompanyCD != "") $sqlMoji .= ", '" . addslashes($this->KanriCompanyCD) . "'";
					if ($this->HaifuDownloadDate != "") $sqlMoji .= ", '" . addslashes($this->HaifuDownloadDate) . "'";
					if ($this->ReportSubmitted != "") $sqlMoji .= ", '" . addslashes($this->ReportSubmitted) . "'";
					if ($this->TantoCD1 != "") $sqlMoji .= ", '" . addslashes($this->TantoCD1) . "'";
					if ($this->TantoCD2 != "") $sqlMoji .= ", '" . addslashes($this->TantoCD2) . "'";
					if ($this->TantoCD3 != "") $sqlMoji .= ", '" . addslashes($this->TantoCD3) . "'";
					if ($this->TantoCD4 != "") $sqlMoji .= ", '" . addslashes($this->TantoCD4) . "'";
					if ($this->TantoCD5 != "") $sqlMoji .= ", '" . addslashes($this->TantoCD5) . "'";
					if ($this->ExistTenkenKikan_Sougou != "") $sqlMoji .= ", '" . addslashes($this->ExistTenkenKikan_Sougou) . "'";
					if ($this->BukkenStatus != "") $sqlMoji .= ", '" . addslashes($this->BukkenStatus) . "'";
					if ($this->ExistTenkenKikan != "") $sqlMoji .= ", '" . addslashes($this->ExistTenkenKikan) . "'";
					if ($this->GyosyaCD != "") $sqlMoji .= ", '" . addslashes($this->GyosyaCD) . "'";
					if ($this->SougouAMKojiTime != "") $sqlMoji .= ", '" . addslashes($this->SougouAMKojiTime) . "'";
					if ($this->SougouPMKojiTime != "") $sqlMoji .= ", '" . addslashes($this->SougouPMKojiTime) . "'";
					if ($this->SougouTenkenKikan != "") $sqlMoji .= ", '" . addslashes($this->SougouTenkenKikan) . "'";
					if ($this->KikiAMKojiTime != "") $sqlMoji .= ", '" . addslashes($this->KikiAMKojiTime) . "'";
					if ($this->KikiPMKojiTime != "") $sqlMoji .= ", '" . addslashes($this->KikiPMKojiTime) . "'";
					if ($this->KikiTenkenKikan != "") $sqlMoji .= ", '" . addslashes($this->KikiTenkenKikan) . "'";
					if ($this->SougouTenkenMonth != "") $sqlMoji .= ", '" . addslashes($this->SougouTenkenMonth) . "'";
					if ($this->KikiTenkenMonth != "") $sqlMoji .= ", '" . addslashes($this->KikiTenkenMonth) . "'";
					if ($this->MinuteTime != "") $sqlMoji .= ", '" . addslashes($this->MinuteTime) . "'";
					if ($this->Holiday1 != "") $sqlMoji .= ", '" . addslashes($this->Holiday1) . "'";
					if ($this->ReserveDay != "") $sqlMoji .= ", '" . addslashes($this->ReserveDay) . "'";
					if ($this->FirstKojiDate != "") $sqlMoji .= ", '" . addslashes($this->FirstKojiDate) . "'";
					if ($this->KenmeiNo != "") $sqlMoji .= ", '" . addslashes($this->KenmeiNo) . "'";
					if ($this->LastKojiDate != "") $sqlMoji .= ", '" . addslashes($this->LastKojiDate) . "'";
					if ($this->LatestIraiKojiKind != "") $sqlMoji .= ", '" . addslashes($this->LatestIraiKojiKind) . "'";
					if ($this->KikiStatus != "") $sqlMoji .= ", '" . addslashes($this->KikiStatus) . "'";
					if ($this->SougouStatus != "") $sqlMoji .= ", '" . addslashes($this->SougouStatus) . "'";
					if ($this->UketsukeShimekiriDate != "") $sqlMoji .= ", '" . addslashes($this->UketsukeShimekiriDate) . "'";


					$sqlMoji .= ", NOW()";
					if ($this->Creator != "") $sqlMoji .= ", $this->Creator";
					$sqlMoji .= ", NOW()";
					if ($this->Updater != "") $sqlMoji .= ", $this->Updater";
					$sqlMoji .= ")";

					$rtn = $this->myDB->executeQuery($sqlMoji);
					if (!$rtn) {
						$this->Err = "追加処理に失敗しました";
						$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
						unset($myLog);
						return FALSE;
					} else {
						// step 5. コミット
						if (!($this->myDB->executeQuery("COMMIT"))) {
							$this->Err = "追加処理に失敗しました(COMMIT)";
							$this->myDB->executeQuery("ROLLBACK");
							return FALSE;
						} else {
							$this->BukkenCD = $maxCD;
						}
					}
				}
			}
		} else {
			// アップデート
			$sqlMoji = "Update $this->TableName set ";
			$sqlMoji .= "BukkenCD = $this->BukkenCD";

			$sqlMoji .= ", ClientCD = " . $this->convertNullStringValue($this->ClientCD);
			$sqlMoji .= ", BukkenName = " . $this->convertNullStringValue($this->BukkenName);
			$sqlMoji .= ", SagyoName = " . $this->convertNullStringValue($this->SagyoName);
			$sqlMoji .= ", TantoCD = " . $this->convertNullStringValue($this->TantoCD);
			$sqlMoji .= ", Hansu = " . $this->convertNullStringValue($this->Hansu);
			$sqlMoji .= ", Kojijun = " . $this->convertNullStringValue($this->Kojijun);
			$sqlMoji .= ", Address = " . $this->convertNullStringValue($this->Address);
			$sqlMoji .= ", FormatType = " . $this->convertNullStringValue($this->FormatType);
			$sqlMoji .= ", FormatType2 = " . $this->convertNullStringValue($this->FormatType2);
			$sqlMoji .= ", Memo = " . $this->convertNullStringValue($this->Memo);
			$sqlMoji .= ", SenyuStartDate = " . $this->convertNullStringValue($this->SenyuStartDate);
			$sqlMoji .= ", SenyuEndDate = " . $this->convertNullStringValue($this->SenyuEndDate);
			$sqlMoji .= ", SenyuStartDate1 = " . $this->convertNullStringValue($this->SenyuStartDate1);
			$sqlMoji .= ", SenyuEndDate1 = " . $this->convertNullStringValue($this->SenyuEndDate1);
			$sqlMoji .= ", KyoyobuStartDate = " . $this->convertNullStringValue($this->KyoyobuStartDate);
			$sqlMoji .= ", KyoyobuEndDate = " . $this->convertNullStringValue($this->KyoyobuEndDate);
			$sqlMoji .= ", SenyubuStartDate = " . $this->convertNullStringValue($this->SenyubuStartDate);
			$sqlMoji .= ", SenyubuEndDate = " . $this->convertNullStringValue($this->SenyubuEndDate);
			$sqlMoji .= ", YoyakuEndDate = " . $this->convertNullStringValue($this->YoyakuEndDate);
			$sqlMoji .= ", CloseTime = " . $this->convertNullStringValue($this->CloseTime);
			$sqlMoji .= ", Kosu = " . $this->convertNullStringValue($this->Kosu);
			$sqlMoji .= ", Kaidaka = " . $this->convertNullStringValue($this->Kaidaka);
			$sqlMoji .= ", MaxWakuSu = " . $this->convertNullStringValue($this->MaxWakuSu);
			$sqlMoji .= ", BukkenNameEn = " . $this->convertNullStringValue($this->BukkenNameEn);
			$sqlMoji .= ", Owner_name = " . $this->convertNullStringValue($this->Owner_name);
			$sqlMoji .= ", FirstDateFeature = " . $this->convertNullStringValue($this->FirstDateFeature);
			$sqlMoji .= ", ReceptionDate = " . $this->convertNullStringValue($this->ReceptionDate);
			$sqlMoji .= ", KanriKinmu = " . $this->convertNullStringValue($this->KanriKinmu);
			$sqlMoji .= ", KanriTEL = " . $this->convertNullStringValue($this->KanriTEL);
			$sqlMoji .= ", Sokai = " . $this->convertNullStringValue($this->Sokai);
			$sqlMoji .= ", WakuPattern = " . $this->convertNullStringValue($this->WakuPattern);

			$sqlMoji .= ", BukkenMemo = " . $this->convertNullStringValue($this->BukkenMemo);
			$sqlMoji .= ", BuildingName = " . $this->convertNullStringValue($this->BuildingName);
			$sqlMoji .= ", Same_kiki_sougou_flg = " . $this->convertNullStringValue($this->Same_kiki_sougou_flg);
			$sqlMoji .= ", Biko_forReport = " . $this->convertNullStringValue($this->Biko_forReport);
			$sqlMoji .= ", BoukaBikou = " . $this->convertNullStringValue($this->BoukaBikou);
			$sqlMoji .= ", Biko = " . $this->convertNullStringValue($this->Biko);
			$sqlMoji .= ", BousaiStatus = " . $this->convertNullStringValue($this->BousaiStatus);
			$sqlMoji .= ", WorkPlace = " . $this->convertNullStringValue($this->WorkPlace);
			$sqlMoji .= ", BousaiFlg = " . $this->convertNullStringValue($this->BousaiFlg);
			$sqlMoji .= ", BousaiEndDate = " . $this->convertNullStringValue($this->BousaiEndDate);
			$sqlMoji .= ", BousaiStartDate = " . $this->convertNullStringValue($this->BousaiStartDate);
			$sqlMoji .= ", BousaiLastUpdater = " . $this->convertNullStringValue($this->BousaiLastUpdater);
			$sqlMoji .= ", BousaiLastUpdated = " . $this->convertNullStringValue($this->BousaiLastUpdated);
			$sqlMoji .= ", KanriCompanyBousaiCD = " . $this->convertNullStringValue($this->KanriCompanyBousaiCD);
			$sqlMoji .= ", GyosyaBousaiCD = " . $this->convertNullStringValue($this->GyosyaBousaiCD);
			$sqlMoji .= ", BousaiKojiTime = " . $this->convertNullStringValue($this->BousaiKojiTime);
			$sqlMoji .= ", BousaiTenkenMonth = " . $this->convertNullStringValue($this->BousaiTenkenMonth);
			$sqlMoji .= ", TatoFlg = " . $this->convertNullStringValue($this->TatoFlg);
			$sqlMoji .= ", Date1 = " . $this->convertNullStringValue($this->Date1);
			$sqlMoji .= ", Date2 = " . $this->convertNullStringValue($this->Date2);
			$sqlMoji .= ", Date3 = " . $this->convertNullStringValue($this->Date3);
			$sqlMoji .= ", Date4 = " . $this->convertNullStringValue($this->Date4);
			$sqlMoji .= ", Date5 = " . $this->convertNullStringValue($this->Date5);
			$sqlMoji .= ", Date6 = " . $this->convertNullStringValue($this->Date6);
			$sqlMoji .= ", SagyoLastUpdater = " . $this->convertNullStringValue($this->SagyoLastUpdater);
			$sqlMoji .= ", SagyoLastUpdated = " . $this->convertNullStringValue($this->SagyoLastUpdated);
			$sqlMoji .= ", KanriCompanyCD = " . $this->convertNullStringValue($this->KanriCompanyCD);
			$sqlMoji .= ", HaifuDownloadDate = " . $this->convertNullStringValue($this->HaifuDownloadDate);
			$sqlMoji .= ", ReportSubmitted = " . $this->convertNullStringValue($this->ReportSubmitted);
			$sqlMoji .= ", TantoCD1 = " . $this->convertNullStringValue($this->TantoCD1);
			$sqlMoji .= ", TantoCD2 = " . $this->convertNullStringValue($this->TantoCD2);
			$sqlMoji .= ", TantoCD3 = " . $this->convertNullStringValue($this->TantoCD3);
			$sqlMoji .= ", TantoCD4 = " . $this->convertNullStringValue($this->TantoCD4);
			$sqlMoji .= ", TantoCD5 = " . $this->convertNullStringValue($this->TantoCD5);
			$sqlMoji .= ", ExistTenkenKikan_Sougou = " . $this->convertNullStringValue($this->ExistTenkenKikan_Sougou);
			$sqlMoji .= ", BukkenStatus = " . $this->convertNullStringValue($this->BukkenStatus);
			$sqlMoji .= ", ExistTenkenKikan = " . $this->convertNullStringValue($this->ExistTenkenKikan);
			$sqlMoji .= ", GyosyaCD = " . $this->convertNullStringValue($this->GyosyaCD);
			$sqlMoji .= ", SougouAMKojiTime = " . $this->convertNullStringValue($this->SougouAMKojiTime);
			$sqlMoji .= ", SougouPMKojiTime = " . $this->convertNullStringValue($this->SougouPMKojiTime);
			$sqlMoji .= ", SougouTenkenKikan = " . $this->convertNullStringValue($this->SougouTenkenKikan);
			$sqlMoji .= ", KikiAMKojiTime = " . $this->convertNullStringValue($this->KikiAMKojiTime);
			$sqlMoji .= ", KikiPMKojiTime = " . $this->convertNullStringValue($this->KikiPMKojiTime);
			$sqlMoji .= ", KikiTenkenKikan = " . $this->convertNullStringValue($this->KikiTenkenKikan);
			$sqlMoji .= ", SougouTenkenMonth = " . $this->convertNullStringValue($this->SougouTenkenMonth);
			$sqlMoji .= ", KikiTenkenMonth = " . $this->convertNullStringValue($this->KikiTenkenMonth);
			$sqlMoji .= ", MinuteTime = " . $this->convertNullStringValue($this->MinuteTime);
			$sqlMoji .= ", Holiday1 = " . $this->convertNullStringValue($this->Holiday1);
			$sqlMoji .= ", ReserveDay = " . $this->convertNullStringValue($this->ReserveDay);
			$sqlMoji .= ", FirstKojiDate = " . $this->convertNullStringValue($this->FirstKojiDate);
			$sqlMoji .= ", KenmeiNo = " . $this->convertNullStringValue($this->KenmeiNo);
			$sqlMoji .= ", LastKojiDate = " . $this->convertNullStringValue($this->LastKojiDate);
			$sqlMoji .= ", LatestIraiKojiKind = " . $this->convertNullStringValue($this->LatestIraiKojiKind);
			$sqlMoji .= ", KikiStatus = " . $this->convertNullStringValue($this->KikiStatus);
			$sqlMoji .= ", SougouStatus = " . $this->convertNullStringValue($this->SougouStatus);
			$sqlMoji .= ", UketsukeShimekiriDate = " . $this->convertNullStringValue($this->UketsukeShimekiriDate);

			$sqlMoji .= ", MukouFlg = " . $this->convertNullStringValue($this->MukouFlg);
			$sqlMoji .= ", Created = " . $this->convertNullStringValue($this->Created);
			$sqlMoji .= ", Creator = " . $this->convertNullIntValue($this->Creator);
			$sqlMoji .= ", Updated = NOW()";
			$sqlMoji .= ", Updater = " . $this->convertNullIntValue($this->Updater);
			$sqlMoji .= " where BukkenCD = $this->BukkenCD";

			$rtn = $this->myDB->executeQuery($sqlMoji);
			if (!$rtn) {
				$this->Err = "更新処理に失敗しました";
				$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
				unset($myLog);
				return FALSE;
			}
		}

		return TRUE;
	}

	function convertFlgToString($flg)
	{
		if ($flg == "true" || $flg == "t")
			return "true";
		else if ($flg == "false" || $flg == "f")
			return "false";

		if ($flg == true || $flg == 1)
			return "true";
		else if ($flg == false || $flg == 0)
			return "false";

		return "false";
	}

	function convertFlgToBoolean($flg)
	{
		if ($flg == "t")
			return TRUE;
		else if ($flg == "f")
			return FALSE;
	}

	function convertNullIntValue($value)
	{
		if ($value == "")
			return "NULL";
		else
			return $value;
	}

	function convertNullStringValue($value)
	{
		if ($value == "")
			return "NULL";
		else
			return "'" . addslashes($value) . "'";
	}

	function setTransaction($lockLevel = "EXCLUSIVE")
	{
		// ログ準備
		$myLog = new SPFWLog();

		$lockString = "LOCK TABLE " . $this->TableName . " IN SHARE ROW " . $lockLevel . " MODE";
		$rtn = $this->myDB->setTransaction($lockString);
		if (!$rtn) {
			$this->Err = $this->myDB->Err;
			$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
			unset($myLog);
			return FALSE;
		}
		return TRUE;
	}

	function rollbackTransaction()
	{
		// ログ準備
		$myLog = new SPFWLog();

		$rtn = $this->myDB->rollbackTransaction();
		if (!$rtn) {
			$this->Err = $this->myDB->Err;
			$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
			unset($myLog);
			return FALSE;
		}
		return TRUE;
	}

	function commitTransaction()
	{
		// ログ準備
		$myLog = new SPFWLog();

		$rtn = $this->myDB->commitTransaction();
		if (!$rtn) {
			$this->Err = $this->myDB->Err;
			$myLog->error("ClassName : " . get_class($this) . " : " . $this->Err);
			unset($myLog);
			return FALSE;
		}
		return TRUE;
	}
}
