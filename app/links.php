<?php
    // модуль для привязки готовой продукции доборного цеха к основному производству
    $created = '29.08.1980';
    $line = 0;
    
    function LinkSPCheckNonVisual(){

	global $doc, $SP_ID, $dbh, $created, $line;
	
	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return -1;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return -1;
	}
	$doc = $_GET['doc'];
	
	if (!isset($_SESSION['gue_f'])) {
	    // флаг администратора не задан
	    return -1;
	}
	if ((int)$_SESSION['gue_f'] != 0) {
	    // доступ запрещен
	    return -1;
	}
	// определяем владельца документа
	$doc_owner = 0;
	$sth = ibase_query($dbh, "select o.user_id, o.creation_ts from orders o where o.id={$doc}");
	if ($row = ibase_fetch_row ($sth)) {
    	    $doc_owner = $row[0];
	    $created = $row[1];    	    
	}
	if ( $doc_owner != $SP_ID) {
	    // документ не относится к производству добора
	    return -1;
	}
	//проверяем, что документ не содержит ничего, кроме сырья для производства доборки (2 - гладкий лист), и при этом он не пустой!
	$que_chdoc = "with subsel as ( select ".
		     "case when o.product_id = 2 then 1 else 0 end as trues, ".
	    	     "case when o.product_id <> 2 then 1 else 0 end as falses ".
		     "from odata o where o.order_id = {$doc}) ".
		     "select sum(s.trues), sum(s.falses) from subsel s";
	$sth = ibase_query($dbh, $que_chdoc);
	if ($row = ibase_fetch_row ($sth)) {
	    if (($row[0] < 1) || ($row[1] > 0) ) {
    	    // есть что-то еще, кроме гладкого листа, либо пусто
    		return -1;
    	    }
	} else {
	    // ни одной записи нету, ошибка
	    return -1;
	}
	
	//проверяем, задана ли строка для редактирования и относится ли она к этому документу
	if (!isset($_GET['line'])) {
	    // строка не задана, проверки пройдены, выход
	    return 0;
	}
	
	$line = (int)$_GET['line'];
	if ($line == 0) {
	    // строка задана "криво"
	    return -1;
	}
	$sth = ibase_query($dbh, "select o.id from odata o where o.order_id={$doc} and o.id={$line}");
	if (!(ibase_fetch_row ($sth))) {
	    // строка не принадлежит документу
	    return -1;
	}
	// все проверки пройдены
	return 0;
    };
    
// ---------------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------------------------

    function LinkSPDrawVisual(){

	global $doc, $dbh, $created, $line;
	echo "<h4>Производство из материалов по заказу {$doc} от ".date('d.m.Y',strtotime($created))."</h4>";

	//заполнение списка продукции
	$que_prs = "select sm.nom_id, n.name from spmetrics sm join nom n on n.nom_id=sm.nom_id where sm.nom_id > 0 order by n.name";
	$products = array();
	$sth = ibase_query($dbh, $que_prs);
    	while ($row = ibase_fetch_row ($sth)) $products[$row[0]] = $row[1];

	// табличная часть				
	$que_tc = "select od.id, pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname, od.amount from odata od ".
		  "join materials mt on od.material_id=mt.id ".
		  "join products pr on od.product_id=pr.id ".
		  "where od.order_id={$doc}";
?>	
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr bgcolor="AAAAAA"><td width=1%><td width=85%>Наименование</td><td>Кол-во, шт</td></tr>
<?php    	    
	$sth = ibase_query($dbh, $que_tc);
	$i = 0;
	while ($row = ibase_fetch_row ($sth)) {
	    if (($i++) & 1) $c = ' bgcolor="DDDDDD"'; else $c='';
	    //если строка не задана явно - берем первую
	    if ($line == 0) {$line = $row[0];}
	    //выбранную строку рисуем жирно, невыбранную - в виде ссылки
	    if ($line == $row[0]) {
		echo "<tr bgcolor=#00FFAA><td>&rarr;</td><td>$row[1]</td><td>$row[2]</td></tr>"; 
	    } else {
    		echo "<tr$c><td></td><td><a href=index.php?action=linksp&doc={$doc}&line={$row[0]}>$row[1]</a></td><td>$row[2]</td></tr>";
    	    }
	};
?>
</table><br><br><br><b>Произведено из выделенного материала</b><br>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr bgcolor="AAAAAA"><td width=75%>Наименование</td><td width=13%>Кол-во, шт</td><td></td></tr>
<?php	
	$que_spl = "select sd.id, sd.nom_id, sd.amnt from spdata sd where sd.line_id={$line}";
	$sth = ibase_query($dbh, $que_spl);
	
	echo "<form name=spdata action=index.php?action=savespd&doc={$doc}&line={$line} method=post>";

	while ($row = ibase_fetch_row ($sth)) {
	    if (($i++) & 1) $c = ' bgcolor="DDDDDD"'; else $c='';
	    // колонка выбора продукции
	    echo "<tr$c><td><select name=pr{$row[0]}>";
    	    foreach ($products as $id => $pr){
    		if ($id == $row[1]) $o = 'selected'; else $o = '';
    		echo "<option {$o} value={$id}>{$pr}</option>";
    	    }
	    echo "</select></td>";
    	    echo "<td><input name=amount{$row[0]} size=7 value={$row[2]}></td>";
    	    echo "<td><a href='index.php?action=delstr2&doc={$doc}&line={$line}&str={$row[0]}'>Удалить</a></td></tr>";
	};
	// возможность добавлять новую строку
	if (($i++) & 1) $c = ' bgcolor="DDDDDD"'; else $c='';
    	echo "<tr$c><td><select name=pr_new><option selected disabled>Продукция</option>";
    	foreach ($products as $id => $pr) echo "<option value={$id}>{$pr}</option>";
    	echo "</select></td>";
    	echo "<td><input name=amount_new size=7 value=0></td>";
    	echo "<td><a onclick=\"window.document.forms['spdata'].action='index.php?action=addstr2&doc={$doc}&line={$line}'; window.document.forms['spdata'].submit();\" href=#>Добавить</a></td></tr>";
?>
<table cellspacing="0" cellpadding="3" border="0" width=800px bgcolor=#CCCCCC><tr><td></td>
<td width=100px align=center><a href=index.php?action=edit&doc=<?php echo $doc; ?>><img src='imgs/ret.png'><br>Вернуться к документу</a></td>
<td width=100px align=center><a onclick="window.document.forms['spdata'].submit();" href=#><img src='imgs/save.png'><br>Сохранить</a></td></tr></table>
</td></tr></table></form>
<?php	
	return;
    }

// ---------------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------------------------

    function SaveSPDNonVisual(){
	global $doc, $SP_ID, $dbh, $line;
	
	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return -1;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return -1;
	}
	$doc = $_GET['doc'];
	
	if (!isset($_SESSION['gue_f'])) {
	    // флаг администратора не задан
	    return -1;
	}
	if ((int)$_SESSION['gue_f'] != 0) {
	    // доступ запрещен
	    return -1;
	}
	// определяем владельца документа
	$doc_owner = 0;
	$sth = ibase_query($dbh, "select o.user_id, o.creation_ts from orders o where o.id={$doc}");
	if ($row = ibase_fetch_row ($sth)) { $doc_owner = $row[0]; }
	if ( $doc_owner != $SP_ID) {
	    // документ не относится к производству добора
	    return -1;
	}
	//проверяем, что документ не содержит ничего, кроме сырья для производства доборки (2 - гладкий лист), и при этом он не пустой!
	$que_chdoc = "with subsel as ( select ".
		     "case when o.product_id = 2 then 1 else 0 end as trues, ".
	    	     "case when o.product_id <> 2 then 1 else 0 end as falses ".
		     "from odata o where o.order_id = {$doc}) ".
		     "select sum(s.trues), sum(s.falses) from subsel s";
	$sth = ibase_query($dbh, $que_chdoc);
	if ($row = ibase_fetch_row ($sth)) {
	    if (($row[0] < 1) || ($row[1] > 0) ) {
    	    // есть что-то еще, кроме гладкого листа, либо пусто
    		return -1;
    	    }
	} else {
	    // ни одной записи нету, ошибка
	    return -1;
	}
	
	//проверяем, задана ли строка для редактирования и относится ли она к этому документу
	if (!isset($_GET['line'])) {
	    // строка не задана
	    return -1;
	}
	$line = (int)$_GET['line'];
	if ($line == 0) {
	    // строка задана "криво"
	    return -1;
	}
	$sth = ibase_query($dbh, "select o.id from odata o where o.order_id={$doc} and o.id={$line}");
	if (!(ibase_fetch_row ($sth))) {
	    // строка не принадлежит документу
	    return -1;
	}

	// все проверки пройдены, попытаемся сохранить полученные данные
	$que_spl = "select sd.id, sd.nom_id, sd.amnt from spdata sd where sd.line_id={$line}";
	$sth = ibase_query($dbh, $que_spl);

	$upd = 0;
	$trn = NULL;

	while ($row = ibase_fetch_row ($sth)) {
	    if (!isset($_POST["pr{$row[0]}"]) || !isset($_POST["amount{$row[0]}"])) continue;
	    $old_nom = (int)$row[1];
	    $old_amn = (int)$row[2];
	    $new_amn = (int)$_POST["amount{$row[0]}"];
	    $new_nom = (int)$_POST["pr{$row[0]}"]; 

	    if ($old_nom != $new_nom || $old_amn != $new_amn) {
	        // найдены отличия, обновляем информацию в базе
		if ($upd == 0) {
		    $trn = ibase_trans($dbh);
		    $upd = 1;
		}
		$que_updl = "execute procedure UpdStr2({$row[0]}, {$doc}, {$line}, {$SP_ID}, {$new_nom}, {$new_amn})";
		ibase_query($trn, $que_updl);
	    }
	}
	if ($upd > 0) ibase_commit($trn);
    };

// ---------------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------------------------

    function AddStrSPDNonVisual(){
	global $doc, $SP_ID, $dbh, $line;
	
	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return -1;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return -1;
	}
	$doc = $_GET['doc'];
	
	if (!isset($_SESSION['gue_f'])) {
	    // флаг администратора не задан
	    return -1;
	}
	if ((int)$_SESSION['gue_f'] != 0) {
	    // доступ запрещен
	    return -1;
	}
	//проверяем, задана ли строка для редактирования и относится ли она к этому документу
	if (!isset($_GET['line'])) {
	    // строка не задана
	    return -1;
	}
	$line = (int)$_GET['line'];
	if ($line == 0) {
	    // строка задана "криво"
	    return -1;
	}

	// все проверки пройдены, попытаемся сохранить полученные данные
	// принадлежность документа пользователю, правильность заполнения задающего документа,
	// и принадлежность строки этому документу проверяются внутри хранимой процедуры

	if (isset($_POST["pr_new"]) && isset($_POST["amount_new"])) {
	    $new_amn = (int)$_POST["amount_new"];
	    $new_nom = (int)$_POST["pr_new"]; 
	    $trn = ibase_trans($dbh);
	    $que_add = "execute procedure AddStr2({$line}, {$doc}, {$SP_ID}, {$new_nom}, {$new_amn})";
	    ibase_query($trn, $que_add);
	    ibase_commit($trn);
	}
    };

// ---------------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------------------------

    function DelStrSPDNonVisual(){
	global $doc, $SP_ID, $dbh, $line;
	
	if (!isset($_GET['doc'])) {
	    // документ не задан
	    return -1;
	}
	if ((int)$_GET['doc'] == 0) {
	    // документ задан "криво"
	    return -1;
	}
	$doc = $_GET['doc'];
	
	if (!isset($_SESSION['gue_f'])) {
	    // флаг администратора не задан
	    return -1;
	}
	if ((int)$_SESSION['gue_f'] != 0) {
	    // доступ запрещен
	    return -1;
	}
	//проверяем, задана ли строка для редактирования и относится ли она к этому документу
	if (!isset($_GET['line'])) {
	    // строка не задана
	    return -1;
	}
	$line = (int)$_GET['line'];
	if ($line == 0) {
	    // строка задана "криво"
	    return -1;
	}
	//проверяем, задана ли строка для в суб-документе и относится ли она к этому документу
	if (!isset($_GET['str'])) {
	    // строка не задана
	    return -1;
	}
	$str_id = (int)$_GET['str'];
	if ($str_id == 0) {
	    // строка задана "криво"
	    return -1;
	}

	// все проверки пройдены, попытаемся сохранить полученные данные
	// принадлежность документа пользователю, правильность заполнения задающего документа,
	// и принадлежность строки этому документу проверяются внутри хранимой процедуры
	
	$que_del = "delete from spdata where id=(select sd.id from spdata sd join odata od on sd.line_id=od.id join orders o on o.id=od.order_id ".
		   "where sd.id={$str_id} and sd.line_id={$line} and od.order_id={$doc} and o.user_id={$SP_ID})";
	
//	echo $que_del;
//	die();
	
	$trn = ibase_trans($dbh);
	ibase_query($trn, $que_del);
	ibase_commit($trn);
    };
?>