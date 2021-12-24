<?php
    session_start();
    require("conf.php");
    
    $scf = 1;		//Scale Factor
    $ofs = 24;		//смещение в файле (0 для инверсии при печати)
    

    $drw = 0;
    $uid = 0;
    
    $l_pts = array();
    $r_pts = array();
    $drdata = array();
    $colpos = 0;
    $mtype = 1;
    
    function AddLeftPart(){
	global $l_pts, $drdata;
	$gan = 0;		//Global Angle
	$prv = $l_pts[0];
	$pt = array();

	$r = 4;			//радиус скругления
	
	foreach($drdata[1] as $id => $plr){
	    //в переменной $plr "полярные" параметры следующего элемента: длина и угол наклона по отношению к предыдущему 
	    if ($plr["A"] == 0 || $plr["A"] == 360) {
		if ($plr["A"] == 0) {$da = 90;} else {$da = 270;}
		//рисуем два куска вместо одного
		$gan += $da - 180;
		$pt["x"] = $prv["x"] - (int)(cos(deg2rad($gan)) * $r);
		$pt["y"] = $prv["y"] - (int)(sin(deg2rad($gan)) * $r);
		$pt["i"] = -1;
		$l_pts[] = $pt;
		$prv = $pt;

		$gan += $da - 180;
		$pt["x"] = $prv["x"] - (int)(cos(deg2rad($gan)) * $plr["L"]);
		$pt["y"] = $prv["y"] - (int)(sin(deg2rad($gan)) * $plr["L"]);
		$pt["i"] = $id;
		$l_pts[] = $pt;
		$prv = $pt;

	    } else {
		$gan += $plr["A"] - 180;
		$pt["x"] = $prv["x"] - (int)(cos(deg2rad($gan)) * $plr["L"]);
		$pt["y"] = $prv["y"] - (int)(sin(deg2rad($gan)) * $plr["L"]);
		$pt["i"] = $id;
		$l_pts[] = $pt;
		$prv = $pt;
	    }
	}
    
	return;
    };

    function AddRightPart(){
	global $r_pts, $drdata;
	$gan = 0;
	$prv = $r_pts[0];
	$pt = array();

	$r = 4;			//радиус скругления
	
	foreach($drdata[2] as $id => $plr){
	    //в переменной $plr "полярные" параметры следующего элемента: длина и угол наклона по отношению к предыдущему 
	    if ($plr["A"] == 0 || $plr["A"] == 360) {
		if ($plr["A"] == 0) {$da = 90;} else {$da = 270;}
		//рисуем два куска вместо одного
		$gan += $da - 180;
		$pt["x"] = $prv["x"] + (int)(cos(deg2rad($gan)) * $r);
		$pt["y"] = $prv["y"] - (int)(sin(deg2rad($gan)) * $r);
		$pt["i"] = -1;
		$r_pts[] = $pt;
		$prv = $pt;

		$gan += $da - 180;
		$pt["x"] = $prv["x"] + (int)(cos(deg2rad($gan)) * $plr["L"]);
		$pt["y"] = $prv["y"] - (int)(sin(deg2rad($gan)) * $plr["L"]);
		$pt["i"] = $id;
		$r_pts[] = $pt;
		$prv = $pt;

	    } else {
		$gan += $plr["A"] - 180;
		$pt["x"] = $prv["x"] + (int)(cos(deg2rad($gan)) * $plr["L"]);
		$pt["y"] = $prv["y"] - (int)(sin(deg2rad($gan)) * $plr["L"]);
		$pt["i"] = $id;
		$r_pts[] = $pt;
		$prv = $pt;
	    }
	}
	return;
    };

    function StartCalc(){
	//загружаем данные из БД и начинаем рендеринг с центральной позиции
	global $DB, $USER, $PASS, $uid, $drw, $drdata, $l_pts, $r_pts, $colpos, $mtype;
	$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	$user_restr = $_SESSION['gue_f'] == 0 ? "":"user_id={$uid} and";
	$que_drw = "select algo, colpos, mtype from drawings d join materials m on d.mt_id=m.id where {$user_restr} d.id={$drw}";
	$sth = ibase_query($dbh, $que_drw);
    	if ($row = ibase_fetch_row ($sth)) {
    	    $drdata = json_decode($row[0],true);
    	    $colpos = $row[1];
    	    $mtype = $row[2];    
    	}
    	$pt = array();
    	$pt["x"] = -(int)$drdata[0]["L"]/2;
    	$pt["y"] = 0;
    	$l_pts[] = $pt;
    	$pt["x"] = -$pt["x"];
    	$r_pts[] = $pt;
	return;    
    };

    $font = imagecreatefrompng('imgs/digits2.png');
    

    function OutText($d_img, $x,$y, $num){
	global $font, $ofs;
	$num = (string)$num;
	$col = imagecolorallocate($d_img, 255, 255, 255);
	for ($i = 0; $i < strlen($num); $i++){
	    if (ord($num[$i]) < ord('0') || ord($num[$i]) > ord('9')) {continue;}
	    imagecopy($d_img, $font, $x + $i*15, $y, (ord($num[$i])- ord('0'))*15, $ofs,15,24);
	}
    };


    function CorrectScaleFactor(){
	global $scf, $l_pts, $r_pts;
	$minx = 0;
	$miny = 0;
	$maxx = 0;
	$maxy = 0;
	foreach ($l_pts as $i => $pt) {
	    if ($pt["x"] < $minx) {$minx = $pt["x"];}
	    if ($pt["y"] < $miny) {$miny = $pt["y"];}
	    if ($pt["x"] > $maxx) {$maxx = $pt["x"];}
	    if ($pt["y"] > $maxy) {$maxy = $pt["y"];}
	}
	foreach ($r_pts as $i => $pt) {
	    if ($pt["x"] < $minx) {$minx = $pt["x"];}
	    if ($pt["y"] < $miny) {$miny = $pt["y"];}
	    if ($pt["x"] > $maxx) {$maxx = $pt["x"];}
	    if ($pt["y"] > $maxy) {$maxy = $pt["y"];}
	}

	$k1 = 500 / ($maxx - $minx);

	$k21 = $miny == 0 ? 100 : -200 / $miny;
	$k22 = $maxy == 0 ? 100 : 200 / $maxy;
	$k2 = ($maxy - $miny) == 0 ? 100 : min($k21, $k22);
	
	$mink = min($k1,$k2);
	if ($mink < 1 ) {$scf = $mink * 0.9;} else {
	    if ($mink > 2) {$scf = $mink * 0.9; }
	}
    }


    function Render(){
	global $l_pts, $r_pts, $scf, $mtype, $colpos, $drdata, $font, $ofs;
	$img = imagecreatetruecolor(600, 500);
	if ($ofs == 24) {
	    $col = imagecolorallocate($img, 255, 255, 255); 
	} else {
	    $col = imagecolorallocate($img, 0, 0, 0); 
	    $wht = imagecolorallocate($img, 255, 255, 255); 
	    imagefilledrectangle($img,0,0,600,500, $wht);
	}
	$gr = imagecolorallocate($img, 128, 128, 128);
	imageantialias($img, true);

	$cx = 300;
	$cy = 250;

	imagesetthickness($img, 1);
	
	//рисуем центральный элемент
	imageline($img, (int)($l_pts[0]["x"]*$scf) + $cx, $cy - (int)($l_pts[0]["y"]*$scf),
			(int)($r_pts[0]["x"]*$scf) + $cx, $cy - (int)($r_pts[0]["y"]*$scf), $col);

	$old = $l_pts[0];
	$c = 1;
	unset($l_pts[0]);
	foreach($l_pts as $id => $pt) {
	    imageline($img, (int)($old["x"]*$scf) + $cx, $cy - (int)($old["y"]*$scf),
			    (int)($pt["x"]*$scf) + $cx, $cy - (int)($pt["y"]*$scf), $col);
	    // размеры
	    if ($pt["i"] >= 0) {
		$x = (int)(($old["x"] + $pt["x"]) * $scf / 2) + $cx;
		$y = $cy - (int)(($old["y"] + $pt["y"]) * $scf / 2);
		imageline($img, 55, $c * 30, $x, $y, $gr);
		$dx = $drdata[1][$pt["i"]]["L"] > 99 ? 5 : 20;
		OutText($img, $dx, $c * 30 - 12, $drdata[1][$pt["i"]]["L"]);
		$c++;
	    }
			    
	    $old = $pt;
	}
	$old = $r_pts[0];
	unset($r_pts[0]);
	foreach($r_pts as $id => $pt) {
	    imageline($img, (int)($old["x"]*$scf) + $cx, $cy - (int)($old["y"]*$scf),
			    (int)($pt["x"]*$scf) + $cx, $cy - (int)($pt["y"]*$scf), $col);
	    // размеры
	    if ($pt["i"] >= 0) {
		$x = (int)(($old["x"] + $pt["x"]) * $scf / 2) + $cx;
		$y = $cy - (int)(($old["y"] + $pt["y"]) * $scf / 2);
		imageline($img, 545, $c * 30, $x, $y, $gr);
		OutText($img, 545, $c * 30 - 12, $drdata[2][$pt["i"]]["L"]);
		$c++;
	    }

	    $old = $pt;
	}
	
	//справочная информация - масштаб
	imageline($img, 20,480,20+(int)(40*$scf),480, $col);
	imageline($img, 20+(int)(10*$scf),476,20+(int)(10*$scf),484, $col);
	imageline($img, 20+(int)(20*$scf),476,20+(int)(20*$scf),484, $col);
	imageline($img, 20+(int)(30*$scf),476,20+(int)(30*$scf),484, $col);
	imagecopy($img, $font, 20, 450, 0,48 + $ofs,50,24);
	
	
	
	//положение цвета имеет значение
	if ($colpos == 1) {
	    imageline($img, $cx + 30, $cy + 200, $cx, $cy, $gr);
	    OutText($img, $cx + 30, $cy + 200, $drdata[0]["L"]);
	    if ($mtype == 2) {
	        imageline($img, $cx, $cy - 100, $cx, $cy - 20, $col);
	        imageline($img, $cx, $cy - 20, $cx+10, $cy - 40, $col);
	        imageline($img, $cx, $cy - 20, $cx-10, $cy - 40, $col);
	        imagecopy($img, $font, $cx - 30, $cy - 125, 55,48 + $ofs,60,24);
	    }
	} else {
	    imageline($img, $cx + 30, $cy - 200, $cx, $cy, $gr);
	    OutText($img, $cx + 30, $cy - 225, $drdata[0]["L"]);
	    if ($mtype == 2) {
	        imageline($img, $cx, $cy + 100, $cx, $cy + 20, $col);
	        imageline($img, $cx, $cy + 20, $cx+10, $cy + 40, $col);
	        imageline($img, $cx, $cy + 20, $cx-10, $cy + 40, $col);
	        imagecopy($img, $font, $cx - 30, $cy + 100, 55,48 + $ofs,60,24);
	    }
	}	
	// выводим изображение в браузере
	header("Content-type: image/png");
	imagepng($img);

	// освобождаем память
	imagedestroy($img);
	return;
    };


    // Основная программа
    if (!isset($_GET['drw']) || !isset($_SESSION['uid'])) { die(); }

    $drw = (int)$_GET['drw'];
    $uid = $_SESSION['uid'];
    if ($drw == 0 || $uid == 0) { die(); }
    
    if (isset($_GET['mode'])) {
	if ($_GET['mode'] == 'w') { $ofs = 0; }
    }
    
    StartCalc();
    AddLeftPart();
    AddRightPart();
    CorrectScaleFactor();
    Render();
    
    imagedestroy($font);
?>