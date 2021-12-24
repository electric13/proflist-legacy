<?php
    function DrawCashBox(){
		// Касса
	global $dbh;
	echo "<h4>Касса за сегодня</h4>";
	$que = "select cb.descr, case cb.rtype when 0 then cb.amount else 0 end, case cb.rtype when 1 then cb.amount else 0 end ".
	       "from cashbox cb where cb.cb_day = 'now' and cb.hide = 0 order by id";

	$sth = ibase_query($dbh, $que);
	$bl = 0;
	$cl = 'trw';
?>
<div id="middle">
<table cellspacing="3" cellpadding="3" border="0" width=100%>
<tr><td id="tbh"><b>Наименование</td><td class="tbh_g">Приход</td><td class="tbh_r">Расход</td></tr>
<?php
	while ($row = ibase_fetch_row ($sth)) {
	    $bl += $row[1] - $row[2]; 
	    echo "<tr class=\"{$cl}\"><td class=\"tdn\">{$row[0]}</td><td>";
	    if ($cl == 'trw') {$cl = 'trg';} else {$cl = 'trw';}
	    if ($row[1] > 0) {echo $row[1];}
	    echo "</td><td>";
	    if ($row[2] > 0) {echo $row[2];}
	    echo "</td></tr>";
	}
	echo "</table>";

	// получаем список пользователей
	$users = array();
	$sth = ibase_query($dbh, "select r.registr_id, r.name from registr r where r.groupregistr_id=9 order by r.name");
	while ($row = ibase_fetch_row ($sth)) $users[$row[0]] = $row[1];
	
	echo "<div class=\"addbl\">Итого в кассе: <br><h2>{$bl}</h2></div>";
	
?>
<script type="text/javascript"> /*
<div class="addbl">
<b>Новое поступление</b><br><br>
<form name=cbplus action=index.php?action=cbplus method=post>
<div class=sel1>
  <input type=radio name=cbs value=1 checked>Оплата продукции
    <div class=sel1>
       <input type=radio name=cosel value=0 checked>По номеру заказа:
         <input name=ordn size=5>
       </input>
    </div>
    <div class=sel1>
       <input type=radio name=cosel value=1>По контрагенту:
	 <select name=clid><option disabled>Контрагент</option>
	 <?php
	foreach ($users as $id => $u) {
	    echo "<option value={$id}>{$u}</option>";
	}
	 ?>
	 </select>
       </input>
    </div>
  </input>
</div>
<div class=sel1><input type=radio name=cbs value=2>Возврат аванса</input></div>
<div class=sel1><input type=radio name=cbs value=3>Выручка от доставки</input></div>
<div class=sel1><input type=radio name=cbs value=4>Выручка от крана</input></div>
<div class=sel1><input type=radio name=cbs value=5>Прочие доходы</input></div>
<div class=sel1>Сумма:<input name=summ size=8></div>
<div class=sel1>Примечание:<input name=dscr size=35></div>
<div class=subm><a onclick="window.document.forms['cbplus'].submit();" href=#><img src='imgs/save.png'><br>Сохранить</a></div>
</form></div>

<div class="addbl">
<b>Новый расход</b><br><br>
<form name=cbminus action=index.php?action=cbminus method=post>
<div class=sel1>
  <input type=radio name=cbs value=10 checked>Возврат
    <div class=sel1>
       <input type=radio name=cosel value=0 checked>По номеру заказа:
         <input name=ordn size=5>
       </input>
    </div>
    <div class=sel1>
       <input type=radio name=cosel value=1>С баланса:
	 <select name=clid><option disabled>Контрагент</option>
	 <?php
	foreach ($users as $id => $u) {
	    echo "<option value={$id}>{$u}</option>";
	}
	 ?>
	 </select>
       </input>
    </div>
  </input>
</div>
<div class=sel1><input type=radio name=cbs value=11>Выдача под отчет</input></div>
<div class=sel1><input type=radio name=cbs value=12>Оплата рекламы</input></div>
<div class=sel1><input type=radio name=cbs value=13>Оплата доставки</input></div>
<div class=sel1><input type=radio name=cbs value=14>Затраты на кран</input></div>
<div class=sel1><input type=radio name=cbs value=15>Затраты на доставку</input></div>
<div class=sel1><input type=radio name=cbs value=16>Затраты на топливо</input></div>
<div class=sel1><input type=radio name=cbs value=17>Прочие расходы</input></div>
<div class=sel1><input type=radio name=cbs value=18>Зарплата</input></div>
<div class=sel1>Сумма:<input name=summ size=8></div>
<div class=sel1>Примечание:<input name=dscr size=35></div>
<div class=subm><a onclick="window.document.forms['cbminus'].submit();" href=#><img src='imgs/save.png'><br>Сохранить</a></div>
</form>

</div>
*/ </script>
<?php
?>
</div>
<?php		
    }

    function CBController($rtype){
	global $dbh, $uid;
	
	return false; // - касса только через API

	if ( ! isset($_POST['cbs']) ||  	//тип поступления
	     ! isset($_POST['cosel']) || 	//тип идентификации поступления
	     ! isset($_POST['summ']) || 	//сумма
	     ! isset($_POST['dscr']) || 	//примечание
	     ! isset($_POST['ordn']) ||		//код контрагента
	     ! isset($_POST['clid']) 		//код заказа
	     ) {return false;}			// обязательные параметры отсутствуют, выходим
		
        $cbs = (int) $_POST['cbs'];
	if ($rtype == 0) {
	    if ( !(($cbs >= 1) && ($cbs <= 5))) { return false; }  //неверно задан тип поступления
	} else {
	    if ( !(($cbs >= 10) && ($cbs <= 18))) { return false; }  //неверно задан тип расходной операции
	}
	
	$cosel = (int) $_POST['cosel'];
	if ( !(($cosel >= 0) && ($cosel <= 1))) { return false; }  //неверно задан тип идентификации поступления

	$ordn = 0;
	$clid = 0;

	$dscr = substr(str_replace("'","",$_POST["dscr"]),0,254);

	if (($cbs == 1) || ($cbs == 10)) {
	    // оплата/возврат
	    if ($cosel == 0) {
		// по номеру заказа
		$ordn = (int) $_POST['ordn'];
		if ( $ordn <= 0 ) { return false; }  //неверно задан номер заказа для идентификации

  	        // если выбрана идентификация по заказу, сохраняем номер со знаком минус
	        $clid = - $ordn;
	    } else {
		// по номеру клиента
		$clid = (int) $_POST['clid'];
		if ( $clid <= 0 ) { return false; }  //неверно задан номер клиента для идентификации
	    }
	}
	
	$summ = (float) $_POST['summ'];
	if ( !(($summ >= 0) && ($summ <= 1000000))) { return false; }  //неверно задана сумма
	
	$que_cbi = "insert into cashbox (amount, rtype, client_id, user_id, cbtype_id, descr) values (?,?,?,?,?,?)";
	$trn = ibase_trans($dbh);
	$que = ibase_prepare($trn,$que_cbi);
	$sth = ibase_execute($que, $summ, $rtype, $clid, $uid, $cbs, $dscr);
	ibase_commit($trn);

	return true;
    }        

    
?>