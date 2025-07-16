<?php

/**
 * SPFWErrorクラス
 *
 * 各種エラーハンドリングを担います。
 *
 * @package
 * @access  public
 * @author  MasahitoSAMEKAWA
 * @create  2004/10/22
 * @version 1.1
 **/

define("EXIST_CLASS_SPFWError", "yes");

// 動作モード定義
define('_MODE_NORMAL', 1);
define('_MODE_ADMIN', 2);
define('_MODE_MODULE', 3);

// エラー区分と対応テンプレート(ユーザ向け)
define("_ILLEGAL_ACCESS", 1);
define("_ILLEGAL_ACCESS2", 12);
define("_SORRY_TERMINAL", 2);
define("_UNIVERSAL_ERROR", 3);
define("_SORRY_ENQ_YET", 4);
define("_SORRY_ENQ_FINISHED", 5);
define("_SORRY_ENQ_ALREADY_1", 6);
define("_SORRY_ENQ_ALREADY_2", 7);
define("_SORRY_COUPON", 8);
define("_SORRY_COUPON_LIMIT", 9);
define("_SORRY_BACKNUMBER", 10);
define("_ADMIN_ERROR", 11);

$TEMPLATE[_ILLEGAL_ACCESS] = "_sorry_illegal.tpl";
$TEMPLATE[_ILLEGAL_ACCESS2] = "_sorry_illegal2.tpl";
$TEMPLATE[_SORRY_TERMINAL] = "redirect:corres.php";
$TEMPLATE[_UNIVERSAL_ERROR] = "_sorry_busy.tpl";
$TEMPLATE[_ADMIN_ERROR] = "_sorry_admin.tpl";
$TEMPLATE[_SORRY_ENQ_YET] = "_sorry_enq_yet.tpl";
$TEMPLATE[_SORRY_ENQ_FINISHED] = "_sorry_enq_finished.tpl";
$TEMPLATE[_SORRY_ENQ_ALREADY_1] = "_sorry_enq_already_1.tpl";
$TEMPLATE[_SORRY_ENQ_ALREADY_2] = "_sorry_enq_already_2.tpl";
$TEMPLATE[_SORRY_COUPON] = "_sorry_coupon.tpl";
$TEMPLATE[_SORRY_COUPON_LIMIT] = "_sorry_coupon_limit.tpl";
$TEMPLATE[_SORRY_BACKNUMBER] = "_sorry_backnumber.tpl";

// エラー時の挙動定義
define('_SORRY_USER', 1);
define('_SORRY_ADMIN', 2);
define('_SORRY_MAIL', 3);
$BEHAVIER[_MODE_NORMAL] = array(NULL, TRUE, FALSE, FALSE);
$BEHAVIER[_MODE_ADMIN] = array(NULL, FALSE, TRUE, FALSE);
$BEHAVIER[_MODE_MODULE] = array(NULL, FALSE, FALSE, TRUE);

/**
 * 前バージョンのモード定義と互換性を持たせるため、モード判定関数を用意
 **/
if ($isAdminMode)
	$ERROR_HANDLING_MODE = _MODE_ADMIN;
else if ($isModuleMode)
	$ERROR_HANDLING_MODE = _MODE_MODULE;
else
	$ERROR_HANDLING_MODE = _MODE_NORMAL;

// エラーハンドリング後終了するかどうか
$TERMINATE = TRUE;

class SPFWError
{
	/**
	 * コンストラクタ
	 * エラー処理のトリガとなります。
	 *
	 * @param		Integer		$errNo		エラー番号
	 * @param		String		$errStr		エラー文字列
	 * @param		String		$errFile	エラー発生ファイル
	 * @param		String		$errLine	エラー発生行番号
	 * @access		public
	 */
	function __construct($errNo, $errStr, $errFile, $errLine)
	{
		include_once _CLS_DIR . "SPFWLog.cls";
		include_once _CLS_DIR . "SPFWTemplate.cls";


		global $ERROR_HANDLING_MODE, $TERMINATE, $BEHAVIER;

		// 出力メッセージの生成
		$ErrorMessage = SPFWError::processError($errNo, $errStr, $errFile, $errLine);

		if ($ErrorMessage == NULL)
			return TRUE;

		// エラー内容のログへの記録
		SPFWError::recordError($ErrorMessage);

		// 複数の告知処理を行いたい場合に、各告知処理の最後でexitされてしまうのを回避するため、$TERMINATEを一度FALSEにして退避させる
		$HideTerminate = $TERMINATE;
		$TERMINATE = FALSE;

		// エラー告知処理
		if ($BEHAVIER[$ERROR_HANDLING_MODE][_SORRY_USER]){
			SPFWError::showSorryPage(_UNIVERSAL_ERROR);
		}
		if ($BEHAVIER[$ERROR_HANDLING_MODE][_SORRY_ADMIN]) {
/*			
			$ErrorString[0] = "システムエラーが発生しました。以下のメッセージをコピーしてシステム管理者にメール等でご連絡ください。";
			$ErrorString[1] = "[" . $ErrorMessage . "]";
			SPFWError::showAdminSorryPage($ErrorString);
*/
			SPFWError::showSorryPage(_ADMIN_ERROR);
		}
		if ($BEHAVIER[$ERROR_HANDLING_MODE][_SORRY_MAIL])
			SPFWError::notifyByMail($ErrorMessage, $errFile);

		$TERMINATE = $HideTerminate;

		// 終了処理
		if ($TERMINATE)
			exit;
	}

	/**
	 * エラーレベルによってエラー文字列を生成して返します。
	 *
	 * @param		Integer		$errNo		エラー番号
	 * @param		String		$errStr		エラー文字列
	 * @param		String		$errFile	エラー発生ファイル
	 * @param		String		$errLine	エラー発生行番号
	 * @return		String		生成エラーメッセージ
	 * @access		public
	 */
	function processError($errNo, $errStr, $errFile, $errLine)
	{
		switch ($errNo) {
			case E_USER_ERROR:
				$ErrorMessage = "ERROR: [" . $errNo . "] " . $errStr . " at line " . $errLine . " in " . $errFile;
				break;
			case E_USER_WARNING:
				$ErrorMessage = "ERROR: [" . $errNo . "] " . $errStr . " at line " . $errLine . " in " . $errFile;
				break;
			case E_USER_NOTICE:
				$ErrorMessage = "ERROR: [" . $errNo . "] " . $errStr . " at line " . $errLine . " in " . $errFile;
				break;
			case E_PARSE:
				$ErrorMessage = "ERROR: [" . $errNo . "] " . $errStr . " at line " . $errLine . " in " . $errFile;
				break;
			case E_NOTICE:
				//$ErrorMessage = "ERROR: [" . $errNo . "] " . $errStr . " at line " . $errLine . " in " . $errFile;
				break;
			case E_WARNING:
				$ErrorMessage = "ERROR: [" . $errNo . "] " . $errStr . " at line " . $errLine . " in " . $errFile;
				break;
			case E_ERROR:
				$ErrorMessage = "ERROR: [" . $errNo . "] " . $errStr . " at line " . $errLine . " in " . $errFile;
				break;
			case E_STRICT:
				//$ErrorMessage = "ERROR: [" . $errNo . "] " . $errStr . " at line " . $errLine . " in " . $errFile;
				break;
			default:
				$ErrorMessage = "ERROR: [" . $errNo . "] " . $errStr . " at line " . $errLine . " in " . $errFile;
				break;
		}

		if ($ErrorMessage != NULL) {
			$errinfo = "\n>>>>> DEBUG TRACE OUTPUT IS FOLLOWING. <<<<<\n";
			$da = debug_backtrace();
			$dstep = count($da);
			for ($i = 3; $i < $dstep; $i++) {
				$errinfo .= sprintf("[%d]%s%s%s():%s:%d \n%s \n", $i - 3, $da[$i]['class'], $da[$i]['type'], $da[$i]['function'], basename($da[$i - 1]['file']), $da[$i - 1]['line'], var_export($da[$i]['args'], true));
			}
			$errinfo .= sprintf("[%d]%s:%d \n", $dstep - 3, basename($da[$dstep - 1]['file']), $da[$dstep - 1]['line']);
			$argvCnt = is_countable($GLOBALS['argv']) ? count($GLOBALS['argv']) : 0;
			$requestCnt = is_countable($_REQUEST) ? count($_REQUEST) : 0;
			if ($argvCnt > 0) {
				$errinfo .= "== argv == \n" . var_export($GLOBALS['argv'], true) . "== argv == \n";
			} else if ($requestCnt > 0) {
				$errinfo .= "== _REQUEST == \n" . var_export($_REQUEST, true) . "== _REQUEST == \n";
			}
			$ErrorMessage .= $errinfo;
		}

		return $ErrorMessage;
	}

	/**
	 * ログへの記録を行います。
	 *
	 * @param		String		$ErrorMessage	生成エラーメッセージ
	 * @access		public
	 */
	function recordError($ErrorMessage)
	{

		$myLog = new SPFWLog();
		if (isset($GLOBALS['ExecuteError']) && is_array($GLOBALS['ExecuteError'])) {
		for ($i = 0; $i < count($GLOBALS['ExecuteError']); $i++)
			$Errors .= $GLOBALS['ExecuteError'][$i] . "\n";
		}
		$Errors .= $ErrorMessage;
		$myLog->error($Errors);
		unset($myLog);
	}

	/**
	 * エラーの告知処理を行います(ユーザ向け)
	 *
	 * @param		String		$errType	エラー画面の種類
	 * @access		public
	 */
	static function showSorryPage($errType)
	{
		global $TEMPLATE, $MyCarrier, $ErrorMessage, $TERMINATE;
		if (strstr($TEMPLATE[$errType], 'redirect:')) {
			$URL = _MAIN_URL . str_replace('redirect:', NULL, $TEMPLATE[$errType]);
			header('Location: ' . $URL);
			exit;
		} else {
			$CNT_FILE = $TEMPLATE[$errType];
			$errHtml = new SPFWTemplate($CNT_FILE, $MyCarrier);
			$errHtml->convertTags();
			$errHtml->outputTemplate();
			unset($errHtml);
		}

		// 終了処理
		if ($TERMINATE)
			exit;
	}

	/**
	 * エラーの告知処理を行います(管理者向け)
	 *
	 * @param		String		$ErrorMessage	生成エラーメッセージ
	 * @access		public
	 */
	function showAdminSorryPage($ErrorMessage, $CloseFlg = FALSE)
	{
		global $TEMPLATE, $MyCarrier, $ErrorLoop, $ErrorString, $TERMINATE;

		// エラー出力
		$ErrorString = array();
		if (is_array($ErrorMessage))
			$ErrorString = $ErrorMessage;
		else
			$ErrorString[] = $ErrorMessage;
		$ErrorLoop = count($ErrorString);

		$GLOBALS['IfClose'] = $CloseFlg;
		$GLOBALS['IfNotClose'] = !$CloseFlg;

		$errHtml = new SPFWTemplate('admin/error.tpl', $MyCarrier);
		$errHtml->convertTags();
		$errHtml->outputTemplate();
		unset($errHtml);

		// 終了処理
		if ($TERMINATE)
			exit;
	}

	/**
	 * エラーのメール送信を行います。
	 *
	 * @param		String		$ErrorMessage	生成エラーメッセージ
	 * @param		String		$errFile		エラー発生ファイル
	 * @access		public
	 */
	function notifyByMail($ErrorMessage, $errFile)
	{
		global $TERMINATE;

		// エラー出力
		$MailText = $errFile . "での処理中に以下のエラーが発生しました。処理は中断しています。\n\n";
		$MailText .= "" . $ErrorMessage . "";

		mb_send_mail('info@nespe.jp', "Hosyu System Mail Notice Modules Error Notice - " . _MAIN_URL, $MailText);

		// 終了処理
		if ($TERMINATE)
			exit;
	}
}

/**
 * エラーが発生した際に呼び出される関数
 **/

function error_handling($errNo, $errStr, $errFile, $errLine)
{
	$myError = new SPFWError($errNo, $errStr, $errFile, $errLine);
}

/**
 * 前バージョンのフレームワークが関数として呼び出しているため、互換性保持用のステップ関数を用意
 **/

function showSorryPage($errType)
{
	SPFWError::showSorryPage($errType);
}

function showAdminSorryPage($errorMessage, $CloseFlg = FALSE)
{
	SPFWError::showAdminSorryPage($errorMessage, $CloseFlg);
}

function myErrorHandler($errNo, $errStr, $errFile, $errLine)
{
	SPFWError::SPFWError($errNo, $errStr, $errFile, $errLine);
}

function moduleErrorHandler($errNo, $errStr, $errFile, $errLine)
{
	SPFWError::SPFWError($errNo, $errStr, $errFile, $errLine);
}

function adminErrorHandler($errNo, $errStr, $errFile, $errLine)
{
	SPFWError::SPFWError($errNo, $errStr, $errFile, $errLine);
}

// エラー通知レベル制御
//error_reporting (E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);
error_reporting(E_ALL ^ E_NOTICE);
set_error_handler("error_handling");
