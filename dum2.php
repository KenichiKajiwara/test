<?php
session_start();

//==============================================================================
// URL 直打ちを抑制するステートメント
// supress_direct_accessのパラメータに羅列したURLからのリクエストは受け付ける
// それ以外はトップページに遷移
// パラメータを指定しないと直打ちOK
//==============================================================================
supress_direct_access("/dum1.php");

function supress_direct_access($allow_referers = null){
	if ((strpos($allow_referers,$_SESSION['referer']) !== false) or ($allow_referers == null)) {
		$_SESSION['referer'] = $_SERVER["REQUEST_URI"];
		return true;
	} else {
		header("location: /index.php");
		exit();
	}
}


?>
<head>


<script>
//-------------------------------------------------------------------------------------------
// ヒストリーバックを禁止するスクリプト、このスクリプトがあるページからはBackボタンで移動できない
//-------------------------------------------------------------------------------------------
window.location.hash="NB";
window.onhashchange=function(){
    window.location.hash="";
}
</script>


<title>バックできないよ</title>
</head>
<br />
DUM2
<br /><a href="dum3.php">DUM3</a>