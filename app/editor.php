<?php
    $drdata = array();
    $drw = 0;
    $lrow = array();
    define(DR_LOCKED, 1);
    define(DR_AMOUNT, 3);
    define(DR_FLEN, 4);
    define(DR_COLPOS,5);
    define(DR_MTID, 2);

    function LoadFromDB(){
	global $dbh, $drdata, $drw, $uid, $lrow;
	$user_restr = $_SESSION['gue_f'] == 0 ? "":"user_id={$uid} and";
	$que_drw = "select algo,locked,mt_id,amount,flen,colpos from drawings where {$user_restr} id={$drw}";
	$sth = ibase_query($dbh, $que_drw);
    	if ($lrow = ibase_fetch_row ($sth)) {$drdata = json_decode($lrow[0],true);} else { return false;}
    	return true;
    };
    
    function SaveIntoDB($locked,$mt_id,$amount,$flen,$colpos){
	global $dbh, $drdata, $drw, $uid;
	$j = json_encode($drdata, JSON_FORCE_OBJECT);
	$user_restr = $_SESSION['gue_f'] == 0 ? "":"user_id={$uid} and locked=0 and";
	$trn = ibase_trans($dbh);
	if (isset($locked)) {
	    // обновляем запись целиком
	    $que_drw = "update drawings set algo=?, locked=?, mt_id=?, amount=?, flen=?, colpos=? where {$user_restr} id=?";
	    $q = ibase_prepare($trn,$que_drw);
	    ibase_execute($q, $j, (int)$locked, (int)$mt_id, (int)$amount, (int)$flen, (int)$colpos, (int)$drw);
	} else {
	    // обновляем только чертеж
	    $que_drw = "update drawings set algo=? where {$user_restr} id=?";
	    $q = ibase_prepare($trn,$que_drw);
	    ibase_execute($q, $j, $drw);
	}
	ibase_commit($trn);
    };
    
    function PrintLP(){
	global $drdata, $drw, $lrow;
        if ($lrow[DR_LOCKED] == 1 && $_SESSION['gue_f'] != 0) { $ena = " disabled"; }
	$lp = $drdata[1];
	echo "<table border=0 width=100% valign=center>";
	foreach ($lp as $id=>$pair){
	    if (!isset($pair["L"]) || !isset($pair["A"])) break;
	    echo "<tr><td>";
	    echo "Ширина, мм: <input name=lw_{$id} size=7 value={$pair['L']}{$ena}><br>";
	    echo "Угол, град.: <input name=la_{$id} size=7 value={$pair['A']}{$ena}><br><br>";
	    if (strlen($ena) > 0) {
		echo "</td><td>&nbsp;</td></tr>";
	    } else {
		echo "</td><td><a href=index.php?action=delplane&doc={$drw}&plane=l{$id}><img src=imgs/minus.png></a></td></tr>";
	    }
	}

	if (strlen($ena) == 0) {
	    echo "<tr><td>";
	    echo "Ширина, мм: <input name=lw_new size=7><br>";
	    echo "Угол, град.: <input name=la_new size=7>";
	    echo "</td><td><a onclick=\"window.document.forms['dd'].action='index.php?action=addplane&doc={$drw}&plane=l';window.document.forms['dd'].submit();\"href=#><img src=imgs/plus.png></a></td></tr>";
	}
	echo "</table>";
    };
    
    function PrintRP(){
	global $drdata, $drw, $lrow;
        if ($lrow[DR_LOCKED] == 1 && $_SESSION['gue_f'] != 0) { $ena = " disabled"; }
	$rp = $drdata[2];
	echo "<table border=0 width=100% valign=center>";
	foreach ($rp as $id=>$pair){
	    if (!isset($pair["L"]) || !isset($pair["A"])) break;
	    echo "<tr><td>";
	    echo "Ширина, мм: <input name=rw_{$id} size=7 value={$pair['L']}{$ena}><br>";
	    echo "Угол, град.: <input name=ra_{$id} size=7 value={$pair['A']}{$ena}><br><br>";
	    if (strlen($ena) > 0) {
		echo "</td><td>&nbsp;</td></tr>";
	    } else {
		echo "</td><td><a href=index.php?action=delplane&doc={$drw}&plane=r{$id}><img src=imgs/minus.png></a></td></tr>";
	    }
	}
	if (strlen($ena) == 0) {
	    echo "<tr><td>";
	    echo "Ширина, мм: <input name=rw_new size=7><br>";
	    echo "Угол, град.: <input name=ra_new size=7>";
	    echo "</td><td><a onclick=\"window.document.forms['dd'].action='index.php?action=addplane&doc={$drw}&plane=r';window.document.forms['dd'].submit();\"href=#><img src=imgs/plus.png></a></td></tr>";
	}
	echo "</table>";
    };
    
    function EditorCheckNonVisual(){
	global $drw, $dbh, $drdata;
	
	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	$drw = $_GET['doc'];
	return true;    
    };

    function EditorAddPlane($plane){
	global $drdata;
	if ($plane == 'l') {
	    if (isset($_POST["lw_new"]) && isset($_POST["la_new"])) {
		$pair = array();
		$pair['L'] = (int)$_POST["lw_new"];
		if ($pair['L'] > 2000) {$pair['L'] = 2000;}
		if ($pair['L'] <= 0) {return false;}
		$pair['A'] = (int)$_POST["la_new"];
		if ($pair['A'] > 360) {$pair['A'] %= 360;}
		if ($pair['A'] < 0) {$pair['A'] = 360 + $pair['A']%360;}
		$drdata[1][] = $pair;
	    }
	} else {
	    if (isset($_POST["rw_new"]) && isset($_POST["ra_new"])) {
		$pair = array();
		$pair['L'] = (int)$_POST["rw_new"];
		if ($pair['L'] > 2000) {$pair['L'] = 2000;}
		if ($pair['L'] <= 0) {return false;}
		$pair['A'] = (int)$_POST["ra_new"];
		if ($pair['A'] > 360) {$pair['A'] %= 360;}
		if ($pair['A'] < 0) {$pair['A'] = 360 + $pair['A']%360;}
		$drdata[2][] = $pair;
	    }
	}
	return true;
    }

    function EditorAddPlanes(){
	global $drw, $dbh, $drdata;
	
	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	$drw = $_GET['doc'];
	if (!LoadFromDB($drw)) {return false;}
	
	//проверяем, задана ли плоскость для добавления
	if (!isset($_GET['plane'])) {
	    // строка не задана
	    return false;
	}
	$pl = (string)$_GET['plane'];
	if (strlen($pl) > 1) $pl = substr($pl,0,1);
	if (strlen($pl) < 1) {
	    // строка задана "криво"
	    return false;
	}
	if (!isset($pl[0])) {return false;}
	if ($pl[0]!='l' && $pl[0]!='r') {return false;}
	if (!EditorAddPlane($pl[0])) {return false;}
	SaveIntoDB();
	return true;    
    };

    function EditorSaveAll(){
	global $dbh, $drdata, $drw, $uid, $lrow;
	// берем запись из базы, сравниваем данные с полученными, если есть изменения - вносим их,
	// если есть дополнения - добавляем их, все что получилось записываем обратно в базу.

	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	$drw = $_GET['doc'];
	if ($drw == -1) {
	    // сохраняем новый документ
	    $que_newo = "execute procedure addorder2({$uid})";
	    $trn = ibase_trans($dbh);
	    if ($row = ibase_fetch_row(ibase_query($trn, $que_newo))) $drw = $row[0];
	    ibase_commit($trn);
	    $drdata = array();
	    $drdata[0] = array();
	    $drdata[1] = array();
	    $drdata[2] = array();
	    $lrow[DR_LOCKED] = 0;
	    $lrow[DR_MTID] = 1;
	    $lrow[DR_AMOUNT] = 1;
	    $lrow[DR_FLEN] = 1;
	    $lrow[DR_COLPOS] = 1;
	} else {      	
    	    // получаем сначала имеющиеся данные
	    if (!LoadFromDB()) {return false;}
	}
	
	// основная часть
	if (!isset($drdata[0])) {return false;}
	$cp = $drdata[0];
	if (!isset($cp["L"])) {$cp["L"] = 100;}
	if (!isset($_POST["cwidth"])) {	return false; } else {
    	    if ((int)$_POST["cwidth"] == 0) {$_POST["cwidth"] = 100;} else {
    		 $drdata[0]["L"] = (int)$_POST["cwidth"]; 
    		 if ($drdata[0]["L"] > 2000) {$drdata[0]["L"] = 2000;}
    		 if ($drdata[0]["L"] <= 0) {$drdata[0]["L"] = 1;}
	    }
    	}
	
	// основные параметры: материал, кол-во, положение цвета, длина изделия
	if (!isset($_POST["mt"])) { return false;}
	$mt = (int)$_POST["mt"];
	if (!isset($_POST["amnt"])) {$amnt = 1;} else {	$amnt = (int)$_POST["amnt"];}
	if (!isset($_POST["color"])) {$col = 0;} else {
	    $col = (int)$_POST["color"];
	    if ($col != 0 && $col != 1) {return false;}
	}
	if (!isset($_POST["wdth"])) {$wdth = 1;} else {
	    $wdth = (int)$_POST["wdth"];
	    if ($wdth != 0 && $wdth != 1) {return false;}
	}

	// левая сторона
	if (isset($drdata[1])) {
	    $lp = $drdata[1];
	    foreach ($lp as $id=>$pair){
		if (!isset($pair["L"]) || !isset($pair["A"])) break;
		if (isset($_POST["lw_".$id]) && isset($_POST["la_".$id])) {
		    $drdata[1][$id]["L"] = (int)$_POST["lw_".$id];
		    if ($drdata[1][$id]["L"] > 2000) {$drdata[1][$id]["L"] = 2000;}
		    if ($drdata[1][$id]["L"] <= 0) {$drdata[1][$id]["L"] = 1;}
		    $drdata[1][$id]["A"] = (int)$_POST["la_".$id];
		    if ($drdata[1][$id]["A"] > 360) {$drdata[1][$id]["A"] %= 360;}
		    if ($drdata[1][$id]["A"] < 0) {$drdata[1][$id]["A"] = 360 + $drdata[1][$id]["A"]%360;}
		}
	    }
	}
	// правая сторона
	if (isset($drdata[2])) {
	    $lp = $drdata[2];
	    foreach ($lp as $id=>$pair){
		if (!isset($pair["L"]) || !isset($pair["A"])) break;
		if (isset($_POST["rw_".$id]) && isset($_POST["ra_".$id])) {
		    $drdata[2][$id]["L"] = (int)$_POST["rw_".$id];
		    if ($drdata[2][$id]["L"] > 2000) {$drdata[2][$id]["L"] = 2000;}
		    if ($drdata[2][$id]["L"] <= 0) {$drdata[2][$id]["L"] = 1;}
		    $drdata[2][$id]["A"] = (int)$_POST["ra_".$id];
		    if ($drdata[2][$id]["A"] > 360) {$drdata[2][$id]["A"] %= 360;}
		    if ($drdata[2][$id]["A"] < 0) {$drdata[2][$id]["A"] = 360 + $drdata[2][$id]["A"]%360;}
		}
	    }
	}
	// и поля для добавления тоже
	EditorAddPlane('l');
	EditorAddPlane('r');
	// сохраняем в бд изменения	
	SaveIntoDB($lrow[DR_LOCKED],$mt,$amnt,$wdth,$col);
	return true;    
    };
    
    
    function EditorDeletePlane(){
	global $drw, $dbh, $drdata;
	
	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	$drw = $_GET['doc'];
	if (!LoadFromDB($drw)) {return false;}
	
	//проверяем, задана ли плоскость для удаления 
	if (!isset($_GET['plane'])) {
	    // строка не задана
	    return false;
	}
	$pl = (string)$_GET['plane'];
	if (strlen($pl) > 2) $pl = substr($pl,0,2);
	if (strlen($pl) < 2) {
	    // строка задана "криво"
	    return false;
	}
	if (!isset($pl[0])) {return false;}
	if ($pl[0]!='l' && $pl[0]!='r') {return false;}
	if (ord($pl[1]) < ord('0') || ord($pl[1]) > ord('9')) {return false;}
	$pl_id = ord($pl[1]) - ord('0');
	if ($pl[0] == 'l') { unset($drdata[1][$pl_id]);	} else { unset($drdata[2][$pl_id]);}
	SaveIntoDB();
	return true;
    };
    
    function ShowDrwList(){

	global $dbh, $uid;
	echo "<h4>Чертежи нестандартных доборных элементов</h4>";
	if ($_SESSION['gue_f'] == 0) {
	    //Администратор
	    $que_drw = "select drid, rname, color, rgb, locked, docs from HLP_GETDRAWINGSLIST(0)";
	    $t1 = "Контрагент и заказы";
	} else {
	    //Пользователь
	    $que_drw = "select drid, rname, color, rgb, locked, docs from HLP_GETDRAWINGSLIST({$uid})";
	    $t1 = "Связанный документ";
	}
	?>
	<div id="calc">
	    <b>Калькулятор стоимости</b>&nbsp;&nbsp;&nbsp;
	    <input v-model.num="len" placeholder="Длина, мм" @change="recalcPrices()">&nbsp;&nbsp;
	    <b>Цена 1</b>: {{ pr1 }}&nbsp;&nbsp;
	    <b>Цена 2</b>: {{ pr2 }}
	</div>
    	<script src="js/vue.min.js"></script>
	<script>
	    const app = new Vue({
		el: '#calc',
		data: {pr1: 0, pr2: 0, len: '' },
    	    methods: {
		recalcPrices:function() {
		    this.len = this.len * 1;
		    if (this.len < 185) {
	    		this.pr1 = Math.round((this.len * 1.05 + 125)/5)*5;
	    		this.pr2 = Math.round((this.len * 1.25 + 150)/5)*5;
		    } else {
	    		this.pr1 = Math.round((this.len * 1.80)/5)*5;
	    		this.pr2 = Math.round((this.len * 1.95)/5)*5;
		    }
	    
    		}
	    }
    
	})
	</script>  

	<?php
	echo "<table cellspacing=0 cellpadding=3 border=0 width=800px><tr bgcolor=#CCCCCC><td width=30%>Чертеж</td><td width=30%>{$t1}</td><td width=30% colspan=2>Материал</td><td>&nbsp;</td></tr>";
	echo "<tr bgcolor=#DDDDDD><td><br><a href=index.php?action=newdrw>Создать новый чертеж<a><br>&nbsp;</td><td></td><td></td><td></td><td></td></tr>";
	
	$sth = ibase_query($dbh, $que_drw);
	$i = 0;
	while ($row = ibase_fetch_row ($sth)) {
	    if ((($i++) & 1) == 1) {$c = ' bgcolor="DDDDDD"';} else {$c='';}
    	    echo "<tr$c><td><a href='index.php?action=editdrw&doc={$row[0]}'>Чертеж {$row[0]}</a></td><td><b>{$row[1]}</b>";
    	    if (strlen((string)$row[5]) > 0) {
    		echo "<br>";
		$l = explode(",",$row[5]);    	    
		foreach($l as $id) {
		    echo "<a href=index.php?action=edit&doc={$id}>{$id}</a>&nbsp;";		
		}
    	    }
    	    echo "</td>";
    	    if ($row[2] != "") {
    		echo "<td width=20px bgcolor=#{$row[3]}>&nbsp;</td><td>{$row[2]}</td><td>"; 
    	    } else {
    		echo "<td width=20px>&nbsp;</td><td>- не задан -</td><td>"; 
    	    }
    	    if ($row[4] > 0) {echo "<img src=imgs/lockc.png>";} else {
    		echo "<img src=imgs/locko.png>";
    		echo "<a href=index.php?action=deldrw&doc={$row[0]}><img src=imgs/del.png></a>";
    	    }
    	    echo "</td></tr>";
	}
	echo "</table>";
	
    };

    function EditorDrawVisual(){    
	global $drw, $dbh, $drdata, $lrow, $uid;

    	$drw = $_GET['action'] == 'newdrw'? -1:(int)$_GET['doc'];
    	
	// заполнение шапочки 
    	$hdr = $_GET['action'] == 'newdrw'? "Новый чертеж":"Редактирование чертежа {$drw}";
    	echo "<h4>{$hdr}</h4>";

    	// заполнение списка материалов
	$materials = array();
	$sth = ibase_query($dbh, "select m.id, m.color from materials m order by m.id");
    	while ($row = ibase_fetch_row ($sth)) $materials[$row[0]] = $row[1];

        if ( !LoadFromDB($drw) && ($drw > 0) ) {return false;}

        $ena = "";
        if ($lrow[DR_LOCKED] == 1 && $_SESSION['gue_f'] != 0) { $ena = " disabled"; }
        
        
?>
<table cellspacing="0" cellpadding="3" border="0" width=960px><tr><td colspan=3 valign=top><b>Параметры</b></td><tr>
<td colspan=3><form name=dd action=index.php?action=savedrw&doc=<?php echo $drw;?> method=post>
<table width=100% cellspacing="0" cellpadding="0" border="0"><tr>
<td>Материал: <select name=mt<?php echo $ena; ?>><option disabled>Материал</option>
<?php
	foreach ($materials as $id=>$mt){
	    if ($id == $lrow[DR_MTID]) {$opt = " selected";} else { $opt = "";}
	    echo "<option value={$id}{$opt}>{$mt}</option>";
	}
	if ($lrow[DR_FLEN] == 1) {
	    $l1250 = "checked";
	    $l2000 = "";
	} else {
	    $l1250 = "";
	    $l2000 = "checked";
	}
	if ($lrow[DR_COLPOS] == 1) {
	    $colup = "checked";
	    $coldown = "";
	} else {
	    $colup = "";
	    $coldown = "checked";
	}
?></select></td>
<td><input type=radio name=color value=1 <?php echo $colup."{$ena}"; ?>>Цвет сверху<br><input type=radio name=color value=0 <?php echo $coldown."{$ena}"; ?>>Цвет снизу<br>&nbsp;</td>
<td><input type=radio name=wdth value=1 <?php echo $l1250."{$ena}"; ?>>Длина 1250 мм<br><input type=radio name=wdth value=0 <?php echo $l2000."{$ena}"; ?>>Длина 2000мм<br>&nbsp;</td>
<td>Основная ширина, мм:<input name=cwidth size=7 value=<?php echo "{$drdata[0]['L']}{$ena}"; ?>>&nbsp;</td><td>&nbsp;Количество, шт.:<input name=amnt size=7 value=<?php echo $lrow[DR_AMOUNT]."{$ena}";?>></td>
</tr></table></td></tr>
<tr><td valign=top><b>Левая сторона</b><br><br>
<?php
	PrintLP();
	echo "</td><td width=510px><img src=render.php?drw={$drw}></td><td valign=top><b>Правая сторона</b><br><br>";
	PrintRP();
	
	$fw = $drdata[0]["L"];
	foreach($drdata[1] as $pair) {$fw += $pair["L"]; }
	foreach($drdata[2] as $pair) {$fw += $pair["L"]; }
	if ($lrow[DR_FLEN] == 1) {$w = 1250;} else {$w = 2000;}
	echo "</td></tr><tr><td colspan=3><b>Суммарно по чертежу:</b><br><br>Развертка: {$fw}х{$w} мм";
	$t = 0;
	if ($w == 2000 ) {
	    $c = (int)(1250/$fw);			//количество изделий в листе
	    $r = (int)1250 % (int) $fw;			//остаток с одного полного листа
	    $f = (int)($lrow[DR_AMOUNT] / $c);		
	    $f = $lrow[DR_AMOUNT] % $c > 0 ? $f + 1 : $f; //количество листов
	    $a = $f * 1250 - $fw * $lrow[DR_AMOUNT];	//общий остаток 
	    $t = $lrow[DR_AMOUNT] % $c > 0 ? 1250 - ($lrow[DR_AMOUNT] % $c) * $fw : $r;   //остаток с последнего листа
	    echo "<br>";
	    if ($r == 0) {
		echo "Изделий из одного листа: {$c} шт<br>Необходимо листов: {$f} шт";
		if ($a != 0) echo "<br>Общий остаток: {$a}x2000 мм";
	    } else {
		echo "Изделий из одного листа: {$c} шт<br>Остаток с одного листа: {$r}x2000 мм<br>Необходимо листов: {$f} шт<br>Общий остаток: {$a}x2000 мм";
	    }
	};
	$l = $w * $lrow[DR_AMOUNT]/1000;
	echo "<br>Общая длина: {$l} пог.м<br>";
	$que_cost = "select cost, cost2 from HLP_CALCCOST(?, ?, ?, ?, ?)";
	$q = ibase_prepare($dbh,$que_cost);
	$sth = ibase_execute($q, $uid, (int)$lrow[DR_MTID], $w, $fw, $t );
	if ($row = ibase_fetch_row($sth)) {
	    echo "Стоимость изделия: ".money_format('%.2n',$row[0])." руб.";
	    if ($row[1] > 0) {echo "<br>Оплачиваемый остаток: ".money_format('%.2n',$row[1])." руб.";}
	    $fs = money_format('%.2n', $row[0] * $lrow[DR_AMOUNT] + $row[1]);
	    $pr = money_format('%.2n', $fs / ($lrow[DR_AMOUNT] * $w / 1000));
	    echo "<br>Общая стоимость: {$fs} руб.";
	    echo "<br>Средняя стоимость пог.метра: {$pr} руб.";
	}
	echo "</td></tr>";
?>


<tr><td colspan=3>
<table cellspacing="0" cellpadding="3" border="0" width=100% bgcolor=#CCCCCC><tr><td></td>
<?php
	if ($_SESSION['gue_f'] == 0) {
	    echo "<td width=100px align=center><a href=index.php?action=prdrwprod&doc=$drw target='_blank'><img src='imgs/invoice.png'><br>Задание на производство</a></td>";
	}
?>
<td width=100px align=center><a href=index.php?action=printdrw&doc=<?php echo $drw; ?> target="_blank"><img src='imgs/order.png'><br>Приложение к заказу</a></td>
<?php
	if ($lrow[DR_LOCKED] == 0) {echo "<td width=100px align=center><a href=index.php?action=drwprod&doc={$drw}><img src='imgs/prod.png'><br>Отправить в производство</a></td>"; }
	if (($_SESSION['gue_f'] == 0) || ($lrow[DR_LOCKED] == 0)){
	    echo "<td width=100px align=center><a href=index.php?action=deldrw&doc={$drw}><img src='imgs/no.png'><br>Удалить чертеж</a></td>";
	    echo "<td width=100px align=center><a onclick=\"window.document.forms['dd'].submit();\" href=#><img src='imgs/save.png'><br>Сохранить</a></td></tr></table></form>";
	}
	echo "</td></tr></table>";
    };

    function EditorCheckConfirmDelDrw(){    
	global $drw, $dbh, $drdata, $lrow, $uid;

	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	$drw = $_GET['doc'];
        if ($drw > 0) {LoadFromDB($drw);}
        return true;
    }

    function EditorCheckPrintDrw(){    
	global $drw, $dbh, $drdata, $lrow, $uid;

	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	$drw = $_GET['doc'];
        if ($drw > 0) {LoadFromDB($drw);}
        return true;
    }

    function EditorCheckPrintDrwProd(){    
	global $drw, $dbh, $drdata, $lrow, $uid;

	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	if ($_SESSION['gue_f'] != 0) {
	    // печать задания на производство разрешена только администратору
	    return false;
	}
	$drw = $_GET['doc'];
        if ($drw > 0) {LoadFromDB($drw);}
        return true;
    }



    function EditorCheckConfirmDrwToProd(){    
	global $drw, $dbh, $drdata, $lrow, $uid;

	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	$drw = $_GET['doc'];
        if ( !LoadFromDB($drw) && ($drw > 0) ) {return false;}
        return true;
    }

    function EditorConfirmed(){    
	global $drw, $dbh, $drdata, $uid;

	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	
	$drw = (int)$_GET['doc'];
	
	$user_restr = $_SESSION['gue_f'] == 0 ? "":"user_id={$uid} and locked=0 and";
	
	$trn = ibase_trans($dbh);
	$que_drw = "update drawings set locked=1 where {$user_restr} id={$drw}";
	ibase_query($trn,$que_drw);
	ibase_commit($trn);
        return true;
    }

    function EditorDeleted(){    
	global $drw, $dbh, $drdata, $uid;

	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return false;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return false;
	}
	
	$drw = (int)$_GET['doc'];

	$trn = ibase_trans($dbh);
	
	if ( $_SESSION['gue_f'] == 0 ) {
	    // Администратор может удалять всегда: сначала отменяем проведение
	    $que_drw = "update drawings set locked=0 where id={$drw}";
	    ibase_query($trn,$que_drw);
	    // потом удаляем
	    $que_drw = "update drawings set user_id = 0 where locked=0 and id={$drw}";
	    ibase_query($trn,$que_drw);
	} else {
	    // Пользователь может удалять только не запущенные в производство чертежи
	    $que_drw = "update drawings set user_id = 0 where user_id={$uid} and locked=0 and id={$drw}";
	    ibase_query($trn,$que_drw);
	}
	ibase_commit($trn);
        return true;
    }
    
    function EditorConfirmDrwToProd(){    
	global $drw, $dbh, $drdata, $lrow, $uid;

	// заполнение шапочки 
    	echo "<h4>Отправка в производство чертежа {$drw}</h4>";

    	// заполнение списка материалов
	$materials = array();
	$sth = ibase_query($dbh, "select m.id, m.color from materials m order by m.id");
    	while ($row = ibase_fetch_row ($sth)) $materials[$row[0]] = $row[1];
?>        
<table cellspacing="0" cellpadding="3" border="0" width=960px><tr valign=center bgcolor=#CCCCCC><td width=510px><br><b>Чертеж</b><br>&nbsp;</td><td><br><b>Параметры</b><br>&nbsp;</td></tr>
<?php        
	echo "<tr valign=top><td><img src=render.php?drw={$drw}></td><td>";
	$fw = $drdata[0]["L"];
	foreach($drdata[1] as $pair) {$fw += $pair["L"]; }
	foreach($drdata[2] as $pair) {$fw += $pair["L"]; }
	if ($lrow[DR_FLEN] == 1) {$w = 1250;} else {$w = 2000;}
	echo "Материал: {$materials[$lrow[DR_MTID]]}<br>";
	echo "Развертка: {$fw}х{$w} мм";
	$t = 0;
	if ($w == 2000 ) {
	    $c = (int)(1250/$fw);			//количество изделий в листе
	    $r = (int)1250 % (int) $fw;			//остаток с одного полного листа
	    $f = (int)($lrow[DR_AMOUNT] / $c);		
	    $f = $lrow[DR_AMOUNT] % $c > 0 ? $f + 1 : $f; //количество листов
	    $a = $f * 1250 - $fw * $lrow[DR_AMOUNT];	//общий остаток 
	    $t = $lrow[DR_AMOUNT] % $c > 0 ? 1250 - ($lrow[DR_AMOUNT] % $c) * $fw : $r;   //остаток с последнего листа
	    echo "<br>";
	    if ($r == 0) {
		echo "Изделий из одного листа: {$c} шт<br>Необходимо листов: {$f} шт";
		if ($a != 0) echo "<br>Общий остаток: {$a}x2000 мм";
	    } else {
		echo "Изделий из одного листа: {$c} шт<br>Остаток с одного листа: {$r}x2000 мм<br>Необходимо листов: {$f} шт<br>Общий остаток: {$a}x2000 мм";
	    }
	};
	$l = $w * $lrow[DR_AMOUNT]/1000;
	echo "<br>Общая длина: {$l} пог.м<br>";
	$que_cost = "select cost, cost2 from HLP_CALCCOST(?, ?, ?, ?, ?)";
	$q = ibase_prepare($dbh,$que_cost);
	$sth = ibase_execute($q, $uid, (int)$lrow[DR_MTID], $w, $fw, $t );
	if ($row = ibase_fetch_row($sth)) {
	    echo "Стоимость изделия: ".money_format('%.2n',$row[0])." руб.";
	    if ($row[1] > 0) {echo "<br>Оплачиваемый остаток: ".money_format('%.2n',$row[1])." руб.";}
	    $fs = money_format('%.2n', $row[0] * $lrow[DR_AMOUNT] + $row[1]);
	    $pr = money_format('%.2n', $fs / ($lrow[DR_AMOUNT] * $w / 1000));
	    echo "<br>Общая стоимость: {$fs} руб.";
	    echo "<br>Средняя стоимость пог.метра: {$pr} руб.";
	}
	echo "</td></tr>";
?>
<td colspan=2 bgcolor=#CCCCCC><br>&nbsp;</td></tr><td align=center colspan=2>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr align="center" bgcolor="EEEEE"><td><h5><a href=index.php?action=drwprod&doc=<?php echo $drw;?>&state=ok><img src=imgs/yes.png><br>Да, я хочу запустить чертеж в производство</a></h5></td>
<td><h5><a href=index.php?action=editdrw&doc=<?php echo $drw;?>><img src=imgs/no.png><br>Нет, вернуться к редактированию чертежа</a></h5></td></tr></table></td></tr></table>
<?php
	return true;
    };

    function EditorPrintDrw(){    
	// Печать чертежа - приложения к заказу (для клиента)
	global $drw, $dbh, $drdata, $lrow, $uid;

	// заполнение шапочки 
    	echo "<table cellspacing=0 cellpadding=0 border=0 width=620px><tr><td valign=center align=center><h4>Чертеж нестандартного элемента {$drw} (приложение к заказу)</h4></td></tr>";

    	// заполнение списка материалов
	$materials = array();
	$sth = ibase_query($dbh, "select m.id, m.color from materials m order by m.id");
    	while ($row = ibase_fetch_row ($sth)) $materials[$row[0]] = $row[1];

	echo "<tr><td><table cellspacing=0 cellpadding=0 border=1 width=100%><tr><td><img src=render.php?drw={$drw}&mode=w></td></tr></table></td></tr><tr><td>";

	$fw = $drdata[0]["L"];
	foreach($drdata[1] as $pair) {$fw += $pair["L"]; }
	foreach($drdata[2] as $pair) {$fw += $pair["L"]; }
	if ($lrow[DR_FLEN] == 1) {$w = 1250;} else {$w = 2000;}
	echo "<br>Справочно:<br>";
	echo "<b>Материал:</b> {$materials[$lrow[DR_MTID]]}<br>";
	echo "<b>Развертка:</b> {$fw}х{$w} мм";
	echo "<p align=right>________________________ Клиент</td></tr></table>";
	return true;
    };

    function EditorPrintDrwProd(){    
	// Печать чертежа для производства
	global $drw, $dbh, $drdata, $lrow, $uid;

	// заполнение шапочки 
    	echo "<table cellspacing=0 cellpadding=0 border=0 width=620px><tr><td valign=center align=center><h4>Задание на производство (чертеж {$drw})</h4></td></tr>";

    	// заполнение списка материалов
	$materials = array();
	$sth = ibase_query($dbh, "select m.id, m.color from materials m order by m.id");
    	while ($row = ibase_fetch_row ($sth)) $materials[$row[0]] = $row[1];

	echo "<tr><td><table cellspacing=0 cellpadding=0 border=1 width=100%><tr><td><img src=render.php?drw={$drw}&mode=w></td></tr></table></td></tr><tr><td>";

	$fw = $drdata[0]["L"];
	foreach($drdata[1] as $pair) {$fw += $pair["L"]; }
	foreach($drdata[2] as $pair) {$fw += $pair["L"]; }
	if ($lrow[DR_FLEN] == 1) {$w = 1250;} else {$w = 2000;}
	echo "<font size=3><b>Материал:</b> {$materials[$lrow[DR_MTID]]}<br>";
	echo "<b>Развертка:</b> {$fw}х{$w} мм";
	echo "<br><b>Требуется произвести:</b> {$lrow[DR_AMOUNT]} шт.";
	$t = 0;
	if ($w == 2000 ) {
	    $c = (int)(1250/$fw);			//количество изделий в листе
	    $r = (int)1250 % (int) $fw;			//остаток с одного полного листа
	    $f = (int)($lrow[DR_AMOUNT] / $c);		
	    $f = $lrow[DR_AMOUNT] % $c > 0 ? $f + 1 : $f; //количество листов
	    $a = $f * 1250 - $fw * $lrow[DR_AMOUNT];	//общий остаток 
	    $t = $lrow[DR_AMOUNT] % $c > 0 ? 1250 - ($lrow[DR_AMOUNT] % $c) * $fw : $r;   //остаток с последнего листа
	    echo "<br><b>Необходимо листов:</b> {$f} шт";
	};
	echo "</td></tr></table>";
	return true;
    };

    
    function EditorConfirmDelDrw(){    
	global $drw, $dbh, $drdata, $lrow, $uid;

	// заполнение шапочки 
    	echo "<h4>Подтверждение удаления чертежа {$drw}</h4>";

    	// заполнение списка материалов
	$materials = array();
	$sth = ibase_query($dbh, "select m.id, m.color from materials m order by m.id");
    	while ($row = ibase_fetch_row ($sth)) $materials[$row[0]] = $row[1];
?>        
<table cellspacing="0" cellpadding="3" border="0" width=960px><tr valign=center bgcolor=#CCCCCC><td width=510px><br><b>Чертеж</b><br>&nbsp;</td><td><br><b>Параметры</b><br>&nbsp;</td></tr>
<?php        
	echo "<tr valign=top><td><img src=render.php?drw={$drw}></td><td>";
	$fw = $drdata[0]["L"];
	foreach($drdata[1] as $pair) {$fw += $pair["L"]; }
	foreach($drdata[2] as $pair) {$fw += $pair["L"]; }
	if ($lrow[DR_FLEN] == 1) {$w = 1250;} else {$w = 2000;}
	echo "Материал: {$materials[$lrow[DR_MTID]]}<br>";
	echo "Развертка: {$fw}х{$w} мм";
	$t = 0;
	if ($w == 2000 ) {
	    $c = (int)(1250/$fw);			//количество изделий в листе
	    $r = (int)1250 % (int) $fw;			//остаток с одного полного листа
	    $f = (int)($lrow[DR_AMOUNT] / $c);		
	    $f = $lrow[DR_AMOUNT] % $c > 0 ? $f + 1 : $f; //количество листов
	    $a = $f * 1250 - $fw * $lrow[DR_AMOUNT];	//общий остаток 
	    $t = $lrow[DR_AMOUNT] % $c > 0 ? 1250 - ($lrow[DR_AMOUNT] % $c) * $fw : $r;   //остаток с последнего листа
	    echo "<br>";
	    if ($r == 0) {
		echo "Изделий из одного листа: {$c} шт<br>Необходимо листов: {$f} шт";
		if ($a != 0) echo "<br>Общий остаток: {$a}x2000 мм";
	    } else {
		echo "Изделий из одного листа: {$c} шт<br>Остаток с одного листа: {$r}x2000 мм<br>Необходимо листов: {$f} шт<br>Общий остаток: {$a}x2000 мм";
	    }
	};
	$l = $w * $lrow[DR_AMOUNT]/1000;
	echo "<br>Общая длина: {$l} пог.м<br>";
	$que_cost = "select cost, cost2 from HLP_CALCCOST(?, ?, ?, ?, ?)";
	$q = ibase_prepare($dbh,$que_cost);
	$sth = ibase_execute($q, $uid, (int)$lrow[DR_MTID], $w, $fw, $t );
	if ($row = ibase_fetch_row($sth)) {
	    echo "Стоимость изделия: ".money_format('%.2n',$row[0])." руб.";
	    if ($row[1] > 0) {echo "<br>Оплачиваемый остаток: ".money_format('%.2n',$row[1])." руб.";}
	    $fs = money_format('%.2n', $row[0] * $lrow[DR_AMOUNT] + $row[1]);
	    $pr = money_format('%.2n', $fs / ($lrow[DR_AMOUNT] * $w / 1000));
	    echo "<br>Общая стоимость: {$fs} руб.";
	    echo "<br>Средняя стоимость пог.метра: {$pr} руб.";
	}
	echo "</td></tr>";
?>
<td colspan=2 bgcolor=#CCCCCC><br>&nbsp;</td></tr><td align=center colspan=2>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr align="center" bgcolor="EEEEE"><td><h5><a href=index.php?action=deldrw&doc=<?php echo $drw;?>&state=ok><img src=imgs/yes.png><br>Да, я хочу удалить этот чертеж</a></h5></td>
<td><h5><a href=index.php?action=editdrw&doc=<?php echo $drw;?>><img src=imgs/no.png><br>Нет, вернуться к редактированию чертежа</a></h5></td></tr></table></td></tr></table>


<?php
	return true;
    };
?>
    