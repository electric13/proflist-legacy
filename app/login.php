<?php
    include("conf.php");
    if (isset($_POST['user']) && isset($_POST['pwd'])) {
	$user = (int) $_POST['user'];		//код клиента
	$pwd  = md5($_POST['pwd']);		//md5 от пароля

	$dbh = ibase_pconnect($DB, $USER, $PASS,'UTF8');

	$q = "select rg.registr_id, rd3.datastr, rd4.datastr from registr rg ".
	     "join registrdata rd1 on rg.registr_id = rd1.registr_id and rd1.registrtypedata_id = 34 ".
	     "join registrdata rd2 on rg.registr_id = rd2.registr_id and rd2.registrtypedata_id = 35 ".
	     "left join registrdata rd3 on rg.registr_id = rd3.registr_id and rd3.registrtypedata_id = 29 ".
	     "left join registrdata rd4 on rg.registr_id = rd4.registr_id and rd4.registrtypedata_id = 28 ".
	     "where rd1.datastr = cast({$user} as varchar(10)) and rd2.datastr = '{$pwd}'";
	
	$sth = ibase_query($dbh, $q);
	
	if ($row = ibase_fetch_row ($sth)) {
	    session_start();
	    $uid = $row[0];
	    $_SESSION['uid'] = $uid;
	    if ($row[1] == 'Y') $_SESSION['gue_f'] = 0; else $_SESSION['gue_f'] = 1;
	    $_SESSION['our'] = (int) $row[2];
	}
    }
    header('Location:index.php'); 
?>
