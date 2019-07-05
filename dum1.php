<?php
session_start();

supress_direct_access("");


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



<br />
DUM1
<br /><a href="dum2.php">DUM2</a>