<?php
    date_default_timezone_set('Europe/Moscow');

    require("conf.php");
    session_start();
    require("links.php");
    require("editor.php");
    require("cashbox.php");

    // проверка на разлогинивание и другие перезагрузки страницы в самом начале
    if (isset($_SESSION['uid']) && isset($_GET['action'])) {
	$uid = $_SESSION['uid'];
        if ($_GET['action'] == 'logout') {
	    unset($_SESSION['uid']);
	    header('Location:index.php'); 
	    die();
        }

	// редактировать документ
        if ($_GET['action'] == 'edit') {
	    if (!isset($_GET['doc'])) {
		// редактирование, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['doc'] == 0) {
		// редактирование, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }


	// привязка доборки к основному производству (Доборный цех)
        if ($_GET['action'] == 'linksp') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (LinkSPCheckNonVisual() != 0) {
		// что-то задано криво
    		header('Location:index.php'); 
		die();
	    }
        }

	// сохранение (Доборный цех)
        if ($_GET['action'] == 'savespd') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (SaveSPDNonVisual() != 0) {
		// что-то задано криво
    		header('Location:index.php'); 
		die();
	    }
	    //добавляем новую строку, если она была заполнена
	    AddStrSPDNonVisual();
	    //возвращаемся к редактированию
	    header("Location:index.php?action=linksp&doc={$doc}&line={$line}");
	    die();
        }
        
	//добавление строки в суб-производстве (Доборный цех)
        if ($_GET['action'] == 'addstr2') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (AddStrSPDNonVisual() != 0) {
		// что-то задано криво
    		header('Location:index.php'); 
		die();
	    }
	    //возвращаемся к редактированию
	    header("Location:index.php?action=linksp&doc={$doc}&line={$line}");
	    die();
        };

	//удаление строки в суб-производстве (Доборный цех)
        if ($_GET['action'] == 'delstr2') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (DelStrSPDNonVisual() != 0) {
		// что-то задано криво
    		header('Location:index.php'); 
		die();
	    }
	    //возвращаемся к редактированию
	    header("Location:index.php?action=linksp&doc={$doc}&line={$line}");
	    die();
        };

	//Редактор доборных элементов
        if ($_GET['action'] == 'editdrw') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!EditorCheckNonVisual()) {
		// что-то задано криво
    		header('Location:index.php?action=drawings'); 
		die();
	    }
        };


	//Подтверждение отправки чертежа в работу
        if ($_GET['action'] == 'drwprod') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!isset($_GET['state']) || ($_GET['state'] != 'ok')) {
		if (!EditorCheckConfirmDrwToProd()) {
		    // что-то задано криво
    		    header('Location:index.php?action=drawings'); 
		    die();
		}
	    };
	    if (isset($_GET['state']) && ($_GET['state'] == 'ok')) { 
		EditorConfirmed(); 
    		header('Location:index.php?action=drawings'); 
		die();
    	    }
        };

	//Подтверждение отправки чертежа в работу
        if ($_GET['action'] == 'deldrw') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!isset($_GET['state']) || ($_GET['state'] != 'ok')) {
		if (!EditorCheckConfirmDelDrw()) {
		    // что-то задано криво
    		    header('Location:index.php?action=drawings'); 
		    die();
		}
	    };
	    if (isset($_GET['state']) && ($_GET['state'] == 'ok')) { 
		EditorDeleted(); 
    		header('Location:index.php?action=drawings'); 
		die();
    	    }
        };


	// Сохранение чертежа
        if ($_GET['action'] == 'savedrw') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!EditorSaveAll()) {
		// что-то задано криво
    		header('Location:index.php?action=drawings'); 
		die();
	    }
	    //возвращаемся к редактированию
	    header("Location:index.php?action=editdrw&doc={$drw}");
	    die();
        }

	// Печать чертежа
        if ($_GET['action'] == 'printdrw') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!EditorCheckPrintDrw()) {
		// что-то задано криво
    		header('Location:index.php?action=drawings'); 
		die();
	    }
        }

	// Печать инвойса для чертежа
        if ($_GET['action'] == 'prdrwprod') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!EditorCheckPrintDrwProd()) {
		// что-то задано криво
    		header('Location:index.php?action=drawings'); 
		die();
	    }
        }

	// удаление плоскости чертежа
        if ($_GET['action'] == 'delplane') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!EditorDeletePlane()) {
		// что-то задано криво
    		header('Location:index.php?action=drawings'); 
		die();
	    }
	    //возвращаемся к редактированию
	    header("Location:index.php?action=editdrw&doc={$drw}");	
	    die();
        }

	// добавление плоскости чертежа
        if ($_GET['action'] == 'addplane') {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!EditorAddPlanes()) {
		// что-то задано криво
    		header('Location:index.php?action=drawings'); 
		die();
	    }
	    //возвращаемся к редактированию
	    header("Location:index.php?action=editdrw&doc={$drw}");		
	    die();
        }

	// запрос удаления документа
        if ($_GET['action'] == 'delete') {
	    if (!isset($_GET['doc'])) {
		// удаление, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['doc'] == 0) {
		// удаление, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }

	// запрос на отправку в производство
        if ($_GET['action'] == 'submit') {
	    if (!isset($_GET['doc'])) {
		// отправка в производство, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['doc'] == 0) {
		// отправка в производство, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }


	// запрос на печать документов для производства
        if ($_GET['action'] == 'inv') {
	    if (!isset($_GET['doc'])) {
		// печать документа, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['doc'] == 0) {
		// печать документа, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }

	// запрос на редактирование прайс-листов
        if ($_GET['action'] == 'price') {
	    if (!isset($_SESSION['gue_f'])) {
		// флаг администратора не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_SESSION['gue_f'] != 0) {
		// доступ запрещен
    		header('Location:index.php'); 
		die();
	    }
	}

	// запрос на доступ к кассовой книге
        if ($_GET['action'] == 'cbook') {
	    if (!isset($_SESSION['gue_f'])) {
		// флаг администратора не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_SESSION['gue_f'] != 0) {
		// доступ запрещен
    		header('Location:index.php'); 
		die();
	    }
	}

	// запрос на запись в кассовую книгу (приход)
        if ($_GET['action'] == 'cbplus') {
	    if (!isset($_SESSION['gue_f'])) {
		// флаг администратора не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_SESSION['gue_f'] != 0) {
		// доступ запрещен
    		header('Location:index.php'); 
		die();
	    }
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!CBController(0) ) { // 0 - приходная операция
		// проверка не пройдена
    		header('Location:index.php?action=cbook'); 
		die();
	    } else {
		// проверка пройдена
    		header('Location:index.php?action=cbook'); 
		die();
	    }
	}

	// запрос на запись в кассовую книгу (расход)
        if ($_GET['action'] == 'cbminus') {
	    if (!isset($_SESSION['gue_f'])) {
		// флаг администратора не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_SESSION['gue_f'] != 0) {
		// доступ запрещен
    		header('Location:index.php'); 
		die();
	    }
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    if (!CBController(1) ) { // 1 - расходная операция
		// проверка не пройдена
    		header('Location:index.php?action=cbook'); 
		die();
	    } else {
		// проверка пройдена
    		header('Location:index.php?action=cbook'); 
		die();
	    }
	}



	// запрос на печать консолидированного заказа
        if ($_GET['action'] == 'showcons') {
	    if (!isset($_SESSION['gue_f'])) {
		// флаг администратора не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_SESSION['gue_f'] != 0) {
		// доступ запрещен
    		header('Location:index.php'); 
		die();
	    }
	    if (!isset($_GET['mt'])) {
		// материал для отбора не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['mt'] == 0) {
		// материал задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }


	// запрос на печать консолидированного заказа
        if ($_GET['action'] == 'showcons2') {
	    if (!isset($_SESSION['gue_f'])) {
		// флаг администратора не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_SESSION['gue_f'] != 0) {
		// доступ запрещен
    		header('Location:index.php'); 
		die();
	    }
	    if (!isset($_GET['pr'])) {
		// продукция для отбора не задана
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['pr'] == 0) {
		// продукция задана "криво"
    		header('Location:index.php'); 
		die();
	    }
        }

	// запрос на печать товарного чека
        if ($_GET['action'] == 'tc') {
	    if (!isset($_GET['doc'])) {
		// печать документа, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['doc'] == 0) {
		// печать документа, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }

	// запрос на печать товарного чека при оплате картой
        if ($_GET['action'] == 'tc2') {
	    if (!isset($_GET['doc'])) {
		// печать документа, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['doc'] == 0) {
		// печать документа, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }



	// запрос на вывод состояния резервов
        if ($_GET['action'] == 'rsrvd') {
	    if (!isset($_GET['doc'])) {
		// печать документа, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['doc'] == 0) {
		// печать документа, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }


	// запрос на печать заказ-наряда
        if ($_GET['action'] == 'zn') {
	    if (!isset($_GET['doc'])) {
		// печать документа, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['doc'] == 0) {
		// печать документа, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }

	// страница "Производство"
        if ($_GET['action'] == 'manage') {
	    if (!isset($_SESSION['gue_f'])) {
		// флаг администратора не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_SESSION['gue_f'] != 0) {
		// доступ запрещен
    		header('Location:index.php'); 
		die();
	    }
        }

	// страница "Финансы"
        if ($_GET['action'] == 'finance') {
	    if (!isset($_SESSION['gue_f'])) {
		// флаг администратора не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_SESSION['gue_f'] != 0) {
		// доступ запрещен
    		header('Location:index.php'); 
		die();
	    }
        }

	// запрос на печать доставочного листа
        if ($_GET['action'] == 'ld') {
	    if (!isset($_GET['doc'])) {
		// печать документа, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    if ((int)$_GET['doc'] == 0) {
		// печать документа, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }
        }


	// удаление строки документа
        if ($_GET['action'] == 'delstr') {
	    if (!isset($_GET['doc']) || !isset($_GET['line'])) {
		// удаление строки, документ или строка не заданы
    		header('Location:index.php'); 
		die();
	    }
	    $doc = (int)$_GET['doc'];
	    $line = (int)$_GET['line'];
	    if ($doc == 0 || $line == 0 ) {
		// удаление строки, документ задан "криво" или строка для удаления задана криво
    		header('Location:index.php'); 
		die();
	    }
	    
	    $user_restr1 = $_SESSION['gue_f'] == 0 ? "" : "and u.registr_id={$uid} ";
	    $user_restr2 = $_SESSION['gue_f'] == 0 ? "" : "and ord.state_id=1 ";
	    $que_dels = "delete from odata where id = (select odt.id from odata odt ".
			"join orders ord on odt.order_id=ord.id and ord.id={$doc} {$user_restr2}".
			"join registr u on u.registr_id=ord.user_id {$user_restr1}".
			"where odt.id={$line})";
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

	    // удаление строки, запрос проверяет связь пользователя, документа и строки из него
	    $trn = ibase_trans($dbh);
	    ibase_query($trn, $que_dels);
	    ibase_commit($trn);
	    header("Location:index.php?action=edit&doc={$doc}");
	    die();
        }

	// изменение флага готовности
        if ($_GET['action'] == 'mark') {
	    if (!isset($_GET['doc']) || !isset($_GET['line'])) {
		// пометка готовности строки, документ или строка не заданы
    		header('Location:index.php'); 
		die();
	    }
	    $doc = (int)$_GET['doc'];
	    $line = (int)$_GET['line'];
	    if ($doc == 0 || $line == 0 ) {
		// пометка готовности, документ задан "криво" или строка для удаления задана криво
    		header('Location:index.php'); 
		die();
	    }
	    if ( $_SESSION['gue_f'] != 0) {
		// пометка готовности, разрешено только администратору 
    		header('Location:index.php'); 
		die();
	    }
	    
	    $que_mark = "execute procedure cmn_markline (0, {$doc}, {$line})";
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

	    // процедура пометки, все проверки в ней 
	    $trn = ibase_trans($dbh);
	    ibase_query($trn, $que_mark);
	    ibase_commit($trn);
	    header("Location:index.php?action=edit&doc={$doc}");
	    die();
        }


	//подтверждение удаления документа
        if ($_GET['action'] == 'sure') {
	    if (!isset($_GET['doc'])) {
		// удаление документа, окончательное, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    $doc = (int)$_GET['doc'];

	    if ($doc == 0) {
		// удаление документа, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }

	    // удаление документа, запрос проверяет связь текущего пользователя и документа
	    $user_restr = $_SESSION['gue_f'] == 0 ? "" : "and user_id={$uid}";
	    $que_delo = "update orders set client_id={$uid}, user_id=0 where id={$doc} {$user_restr} and state_id=1";

	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

	    $trn = ibase_trans($dbh);
	    ibase_query($trn, $que_delo);
	    ibase_commit($trn);
	    header("Location:index.php");
	    die();	    
        }

	//отправка в производство
        if ($_GET['action'] == 'send') {
	    if (!isset($_GET['doc'])) {
		// отправка заказа в производство, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    $doc = (int)$_GET['doc'];

	    if ($doc == 0) {
		// отправка заказа в производство, документ задан криво
    		header('Location:index.php'); 
		die();
	    }

	    // обновление статуса документа, запрос проверяет связь пользователя-документа и возможность изменения статуса
	    $que_upds = "update orders set state_id=2 where id={$doc} and user_id={$uid} and state_id=1";
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    $trn = ibase_trans($dbh);
	    ibase_query($trn, $que_upds);
	    ibase_commit($trn);
	    header("Location:index.php?action=edit&doc={$doc}");
	    die();
        }

	//добавление строки	
        if ($_GET['action'] == 'addstr') {
	    if (!isset($_GET['doc'])) {
		// добавление строки, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    $doc = (int)$_GET['doc'];
	    if ($doc == 0 ) {
		// добавление строки, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }


	    if ((isset($_POST['mt_new']) && isset($_POST['pr_new']) && isset($_POST['len_new']) && isset($_POST['amount_new'])) ||
	       (isset($_POST['nm_new']) && isset($_POST['amount2_new']))) {
	    
		$mt = (int)$_POST['mt_new'];
		$pr = (int)$_POST['pr_new'];
		$len = (int)$_POST['len_new'];
		$amount = (int)$_POST['amount_new'];
		
		if ($len == 0 || $pr == 0 || $mt == 0 || $amount == 0) {
		    $pr = 10; // добор
		    $mt = (int)$_POST['nm_new'];
		    $len = 0;
		    $amount = (int)$_POST['amount2_new'];
		}
	    
		$que_adds = "execute procedure addstr({$doc},{$uid},{$mt},{$pr},{$len},{$amount})";
	    
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		// добавление строки, запрос проверяет связь пользователя, документа и строки из него, увеличивает количество в случае дублирующихся записей

		$trn = ibase_trans($dbh);
		$row = ibase_fetch_row(ibase_query($trn, $que_adds));
	    
		if ($row[0] < 0) $doc = -$row[0];
	    
		ibase_commit($trn);

		// возврат к редактированию документа
		if ($doc > 0) header("Location:index.php?action=edit&doc={$doc}");
		else header("Location:index.php?action=new");
		die();
	    }  else   {
		// добавление строки, параметры не заданы
		if ($doc > 0) header("Location:index.php?action=edit&doc={$doc}");
		else header("Location:index.php?action=new");
		die();
	    }
	}

	//сохранение прайс-листа
        if ($_GET['action'] == 'saveprice') {
	    if (!isset($_GET['user'])) {
		// сохранение прайса, идентификатор пользователя не задан
    		header('Location:index.php?action=price'); 
		die();
	    }
	    $p_user = (int)$_GET['user'];
	    if ($p_user == 0 ) {
		// сохранение прайса, идентификатор пользователя задан криво
    		header('Location:index.php?action=price'); 
		die();
	    }
	    if ( $_SESSION['gue_f'] != 0 ) {
		// сохранение прайса, разрешено только администратору
    		header('Location:index.php?action=price'); 
		die();
	    }

	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    $que_plp = "select id, product_id, mtgroup_id, thickness, price from plprint ".
		       "where user_id={$p_user} order by id";
	    $sth = ibase_query($dbh, $que_plp);

	    while ($row = ibase_fetch_row ($sth)) {
		$cell = "pr_{$row[1]}_{$row[2]}_".($row[3]*100);
		if (!isset($_POST[$cell])) { continue; };
		//получаем цену из формы
		$new_pr = (float)$_POST[$cell];
		// если в базе такая же - то ничего не делаем
		if ((float)($row[4]) == $new_pr) { continue; };
		$que_upd = "update plprint set price={$new_pr} where id={$row[0]}";
		// если транзакция не создана, создаем
		if (!isset($trn)) { $trn = ibase_trans($dbh); }
		ibase_query($trn, $que_upd);
	    };
	
	    if (isset($trn)) {ibase_commit($trn);}
	    // возвращаемся к редактированию
    	    header("Location:index.php?action=price&user={$p_user}"); 
	    die();
        }


	//сохранение документа
        if ($_GET['action'] == 'save') {
	    if (!isset($_GET['doc'])) {
		// сохранение изменений, документ не задан
    		header('Location:index.php'); 
		die();
	    }
	    $doc = (int)$_GET['doc'];
	    if ($doc == 0 ) {
		// сохранение изменений, документ задан "криво"
    		header('Location:index.php'); 
		die();
	    }

	    if ($doc == -1) {
		// сохраняем новый документ
		$que_newo = "execute procedure addorder({$uid})";
    		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		$trn = ibase_trans($dbh);
		if ($row = ibase_fetch_row(ibase_query($trn, $que_newo))) $doc = $row[0];
		ibase_commit($trn);
	    }

	    // запрос по всем строкам документа, там где есть отличия - обновляем
	    // для нового документа запрос ничего выдавать не будет, поэтому в цикл захода не будет
	    $user_restr = $_SESSION['gue_f'] == 0 ? "" : "and o.user_id={$uid} and od.state_id=1 ";
	    
	    $que_odts = "select od.id, od.material_id, od.lng, od.amount, od.product_id, op.price from odata od ".
			"join orders o on o.id = od.order_id ".
			"left join orprlinks op on op.order_id=o.id and op.material_id=od.material_id ".
			"where od.order_id={$doc} {$user_restr}".
			"order by od.id";

	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    $sth = ibase_query($dbh, $que_odts);
	    
	    $upd = false;
	    $trn = NULL;

	    while ($row = ibase_fetch_row ($sth)) {
		$old_mt = (int)$row[1];
		$old_len = (int)$row[2];
		$old_amn = (int)$row[3];
		$old_pr = (int)$row[4];
		$old_prc = (float)$row[5];
		if ($old_pr < 10) {
		    // профлист и другая продукция произвольного размера
		    if (!isset($_POST["pr{$row[0]}"]) || !isset($_POST["mt{$row[0]}"]) || !isset($_POST["len{$row[0]}"]) || !isset($_POST["amount{$row[0]}"])) continue;
		    $new_mt = (int)$_POST["mt{$row[0]}"];
		    $new_len = (int)$_POST["len{$row[0]}"];
		    $new_amn = (int)$_POST["amount{$row[0]}"];
		    $new_pr = (int)$_POST["pr{$row[0]}"]; 
		} else {
		    // товар в штуках
		    if (!isset($_POST["nm{$row[0]}"]) || !isset($_POST["amount{$row[0]}"]) || (!isset($_POST["prdb_{$row[0]}"]) && $_SESSION['gue_f'] == 0)) continue;
		    $new_mt = (int)$_POST["nm{$row[0]}"];
		    $new_amn = (int)$_POST["amount{$row[0]}"];
		    $new_pr = 10; 
		    $new_len = 0;
		    $new_prc = (float)$_POST["prdb_{$row[0]}"];
		}
		if ($old_mt != $new_mt || $old_len != $new_len || $old_amn != $new_amn || $new_pr != $old_pr ) {
		    // найдены отличия, обновляем информацию в базе
		    if (!$upd) {
			$trn = ibase_trans($dbh);
			$upd = true;
		    }
		    $que_updl = "execute procedure UpdStr($row[0], $doc, $uid, $new_mt, $new_pr, $new_len, $new_amn)";

		    ibase_query($trn, $que_updl);
		}
		
		if ($old_pr == 10 && $new_prc != $old_prc && $_SESSION['gue_f'] == 0) {
		    // администратор должен! может менять цену на добор
		    if (!$upd ) {
			$trn = ibase_trans($dbh);
			$upd = true;
		    }
		    $que_uprc = "update or insert into orprlinks (order_id, material_id, price) values ({$doc}, {$new_mt}, {$new_prc}) matching (order_id, material_id)";
		    ibase_query($trn, $que_uprc);
		}
		
	    }
	    if ($upd) ibase_commit($trn);

	    if ($_SESSION['gue_f'] == 0) {
		// модификация цен только администратором
		$que_prcl = "select distinct od.material_id, pl.price, op.price from orders o ".
	    		    "join odata od on o.id=od.order_id and od.product_id < 10 ".
			    "left join pricelist pl on pl.material_id = od.material_id and pl.user_id = o.user_id ".
			    "left join orprlinks op on op.order_id=o.id and op.material_id=od.material_id ".
			    "where o.id={$doc} ".
			    "union ".
			    "select distinct od.material_id+1000, pl.price, op.price from orders o ".
	    		    "join odata od on o.id=od.order_id and od.product_id < 10 ".
			    "left join pricelist pl on pl.material_id = od.material_id+1000 and pl.user_id = o.user_id ".
			    "left join orprlinks op on op.order_id=o.id and op.material_id=od.material_id+1000 ".
			    "where o.id={$doc}";

		$sth = ibase_query($dbh, $que_prcl);
		$upd2 = 0;
		while ($row = ibase_fetch_row ($sth)) {
		    // если цена не задана в передаваемой форме - переходим к следующему материалу
		    if (!isset($_POST["price_{$row[0]}"])) continue;
		    // получаем цену, заданную в форме
		    $user_price = $_POST["price_{$row[0]}"];
		    // получаем цену из базы для владельца документа, если вручную не задана - берем из прайс-листа
		    if ((float)$row[2] != (float)0) $price = (float)$row[2]; else $price = (float)$row[1];
		    // если цены не совпадают - надо обновить таблицу ORPRLINKS
		    if ($price != $user_price) {
			if ($upd2 == 0) $trn = ibase_trans($dbh);
			$upd2 = 1;
			$que_uprc = "update or insert into orprlinks (order_id, material_id, price) values ({$doc}, {$row[0]}, {$user_price}) matching (order_id, material_id)";
			ibase_query($trn, $que_uprc);		    
		    }
    		}

		// если были внесены изменения в цену - подтверждаем транзакцию и выставляем флаг для смены статуса
		if ($upd2 > 0) {
		    ibase_commit($trn);
		    $upd = 1;
		}
	    }


	    if (isset($_POST["comment"])) $cmn = substr(str_replace("'","",$_POST["comment"]),0,254); else $cmn = '';
	    if (isset($_POST["ostate"])) $s_id = (int)$_POST["ostate"];

	    $dsc = 0;
	    if (isset($_POST["discount"]) && ($_SESSION['gue_f'] == 0 || $_SESSION['our'] > 0)) $dsc = (float)$_POST["discount"];
	    
	    // флаг оплаты
	    $fc_flag = -1;
	    if ($_SESSION['gue_f'] == 0) {
		if (isset($_POST["fc"]) && ($_POST["fc"] == 'on')) $fc_flag = 1; else $fc_flag = 0;
	    }

	    //смена владельца возможна только администратором
	    $doc_owner = 0;
	    if (isset($_POST["user"])) $doc_owner = (int)$_POST["user"];

	    $add_info = "";
	    if ($upd > 0) $s_id = 1;
	    if (($_SESSION['gue_f'] == 0) &&( $s_id > 0 && $s_id < 6)) $add_info = ", state_id={$s_id}";
	    $add_info = $doc_owner > 0 ? $add_info.", user_id={$doc_owner}" : $add_info;
	    $add_info = $fc_flag >= 0 ? $add_info.",fc_flag={$fc_flag}" : $add_info;
	    
	    $user_restr = $_SESSION['gue_f'] == 0 ? "" : " and user_id={$uid} and state_id=1";
	    
            $que_updc = "update orders set comments='{$cmn}', discount={$dsc} {$add_info} where id={$doc}{$user_restr}";
            
    	    $trn = ibase_trans($dbh);
	    ibase_query($trn, $que_updc);
	    ibase_commit($trn);
            
	    // возврат к редактированию документа
	    header("Location:index.php?action=edit&doc={$doc}");
	    die();
        }
    }

// ===================================================================================================================================
// графическая часть
// ===================================================================================================================================

?>
<html><head><link rel="stylesheet" href="css/mstyle.css" type="text/css"></head><body>
<?php
    if (!isset($_SESSION['uid'])) {
        // не залогинился, рисуем форму
?>
<table bgcolor="EEEEFF" align=center height=100% style="width:400px; margin-top:120px; height:300px;" cellspacing="0" cellpadding="0" border="0">
<tr><td>&nbsp;</td></tr>
<tr><td width=100% align=center>
<form action=login.php method=post>
<table cellspacing="0" cellpadding="0" border="0" align=center><tr><td align=center>Вход в систему</td></tr></table>
<table cellspacing="0" cellpadding="0" border="0">
<tr><td width=30%>Логин</td><td><input name=user size=13 value=''></td></tr>
<tr><td>Пароль</td><td><input name=pwd size=15 type=password value=''></td></tr>
<tr><td>&nbsp;</td><td align=right><input type=submit value='Вход' align=center></td></tr></table>
</form>
</td></tr></table>
<?php 
    } else { 
    // Авторизован, можно работать
	$nomenuitems = array('inv', 'tc', 'tc2', 'ld', 'rsrvd', 'showcons', 'showcons2', 'pprint', 'zn', 'printdrw', 'prdrwprod');
    
	$menu = true;
	if (isset($_GET['action'])) {
	    foreach($nomenuitems as $item) {if ($_GET['action'] == $item) {$menu = false;} }
	}

	if ( $menu) {
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

	    $que_cmt =  "select constvalue from constant where constant_id=10";
	    $sth = ibase_query($dbh, $que_cmt);
	    if ($row = ibase_fetch_row ($sth)) { echo $row[0]; }

?>
<a href="index.php?action=news">Новости</a>&nbsp;&nbsp;
<a href="index.php">В работе</a>&nbsp;&nbsp;
<a href="index.php?action=arch">Все заказы</a>&nbsp;&nbsp;
<a href="index.php?action=new">Новый заказ</a>&nbsp;&nbsp;
<a href="index.php?action=remains">Остатки</a>&nbsp;&nbsp;
<a href="index.php?action=balance">Баланс</a>&nbsp;&nbsp;
<a href="index.php?action=drawings">Чертежи</a>&nbsp;&nbsp;
<?php
	    if ($_SESSION['gue_f'] == 0) {
?>
<a href="index.php?action=manage">Производство</a>&nbsp;&nbsp;
<a href="index.php?action=finance">Финансы</a>&nbsp;&nbsp;
<a href="index.php?action=cbook">Касса</a>&nbsp;&nbsp;
<a href="index.php?action=price">Прайс-листы</a>&nbsp;&nbsp;
<?php	    
	    } else {
?>
<a href="index.php?action=pprint" target="_blank">Прайс-листы</a>&nbsp;&nbsp;
<?php	    
	    }
?>
<a href="index.php?action=delivery">Доставка</a>&nbsp;&nbsp;
<a href="index.php?action=logout">Выход</a>
<br>
<?php
	}
	$uid = $_SESSION['uid'];
	if (!isset($_GET['action'])) {
	    // рисуем список документов	
	    echo "<h4>Заказы в работе</h4>";

?>   
<table cellspacing="3" cellpadding="3" border="0" width=840px>
<tr bgcolor="AAAAAA"><td width=14%># заказа</td><td width=14%>Дата заказа</td><td width=19%>Состояние</td><td width=37%>Примечание</td><td></td></tr>
<?php
	    $dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
	    
	    if (isset($_GET['filt'])) {
		$filt = (int)$_GET['filt'];
	    } else { $filt = 0; }
	    $_SESSION['filt'] = $filt;
	    
	    if (isset($_GET['state'])) {
		$showstate = (int)$_GET['state'];
		if ($showstate < 1 || $showstate > 5) {$showstate = 0;};
	    } else { $showstate = 0; }
	    $_SESSION['state'] = $showstate;


	    $user_restr = $_SESSION['gue_f'] == 0 ? ($filt == 0 ? "o.user_id>0" : "o.user_id=".$filt ) : "o.user_id = {$uid}";
	    $state_restr = $showstate == 0 ? "" : " and o.state_id={$showstate}";
	    
	    $que_allorders = "select o.id, o.creation_ts, r.descr, o.comments, o.state_id, rg.name, o.fc_flag, sum(od.lng*od.amount)/1000, gds.sumstr, o.user_id ".
			     "from orders o ".
			     "join ref_ostates r on o.state_id = r.id ".
			     "join registr rg on rg.registr_id=o.user_id ".
			     "left join odata od on o.id=od.order_id and od.product_id < 10 ".
			     "left join getdocsums(o.id) gds on 1=1 ".
			     "where {$user_restr} {$state_restr} and ((o.state_id < 5) or (o.fc_flag=0)) ".
			     "group by o.id, o.creation_ts, r.descr, o.comments, o.state_id, rg.name, o.fc_flag, gds.sumstr, o.user_id ".
			     "order by o.id descending";
			     
	    $i = 0;
	    $sth = ibase_query($dbh, $que_allorders);
	    while ($row = ibase_fetch_row ($sth)) {
		if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
		$d = date('d.m.Y H:i',strtotime($row[1]));
		if ($row[4] == 2) $c = ' bgcolor="FF5555"';	// согласован
		if ($row[4] == 3) $c = ' bgcolor="FFBA00"';	// в работе
		if ($row[4] == 4) $c = ' bgcolor="55FF55"';	// готов
		if ($row[4] == 5) $c = ' bgcolor="8080FF"';	// выполнен, не оплачен
		
    		echo "<tr$c><td><a href='index.php?action=edit&doc={$row[0]}'>{$row[0]}</a>";
    		if ($_SESSION['gue_f'] == 0) {
    		    $f1 = $filt == 0 ? "":"filt={$row[9]}";
    		    $f2 = $showstate == 0 ? "":"state={$row[4]}";
    		    
    		    
    		    $hr = $filt == 0 ? "<a href='index.php?filt={$row[9]}&{$f2}'><img src='imgs/sf.png'>" : "<a href='index.php?{$f2}'><img src='imgs/se.png'>";
    		    $hr2 = $showstate == 0 ? "<a href='index.php?state={$row[4]}&{$f1}'><img src='imgs/s_e.png'>" : "<a href='index.php?{$f1}'><img src='imgs/s_d.png'>";
    		    echo "&nbsp;&nbsp;&nbsp;{$hr}</a>{$hr2}</a><br><b>{$row[5]}</b>";
    		};
    		echo "</td><td>{$d}</td><td>{$row[2]}";
    		if (($_SESSION['gue_f'] == 0) && ($row[6] == 0)) echo ", не оплачен";
    		echo "</td><td>".htmlspecialchars($row[3]);
    		
    		if ($_SESSION['gue_f'] == 0) {
    		    // если под админом рисуем кнопки для доставки и производства
		    echo "<br><table cellspacing=0 cellpadding=0 border=0 width=100%px><tr align=right>";
    		    if ($row[7] != '' ) { echo "<td align=left><b>{$row[7]} м</b>;</td>"; };
    		    echo "<td><a href='index.php?action=ld&doc={$row[0]}' target='_blank'><img src='imgs/prnn.png'></a></td></tr></table>";
    		} else {
        	    if ($row[7] != '' ) { echo "<br><b>{$row[7]} м</b>; "; };
    		}
    		
    		

    		echo "</td><td>";
    		if ($row[4] == 1) { 
    		    echo "<a href=index.php?action=delete&doc={$row[0]}>Удалить</a>";
    		} else {
//    		    if (($row[8] != '') && ($_SESSION['gue_f'] == 0)) {
    		    if ($row[8] != '') {
    			$s = explode(",",$row[8]);
    			$f = 0;
    			foreach ($s as $p) {$f += (float)$p; }; //стоимость проката + стоимость добора

			preg_match("/.*pr:(\d+)/i", $row[3], $matches);
			if (isset($matches[1])) {
			    $pr = (float)$matches[1]; 
			    echo "Опл. <b>".money_format('%.2n',$pr)."</b> р.<br>из <b>".money_format('%.2n',$f)."</b> р.";
			} else {$pr = 0;}
			echo "<br>Остаток <b>".money_format('%.2n',$f - $pr)."</b> р.";
    		    }
    		};
    		echo "</td></tr>";
	    }
?>
</table>
<?    
	} else {
	    // если есть действие

    	    if ($_GET['action'] == 'arch') {
		// рисуем полный список документов	
		echo "<h4>Архив заказов</h4>";
?>   
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr bgcolor="AAAAAA"><td width=15%># заказа</td><td width=15%>Дата заказа</td><td width=20%>Состояние</td><td width=42%>Примечание</td><td></td></tr>
<?php
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		$user_restr = $_SESSION['gue_f'] == 0 ? "o.user_id > 0" : "o.user_id = $uid ";
		$que_allorders = "select o.id, o.creation_ts, r.descr, o.comments, o.state_id, rg.name, o.fc_flag from orders o ".
	    	    		 "join ref_ostates r on o.state_id = r.id ".
	    		         "join registr rg on rg.registr_id=o.user_id ".
	    	    		 "where {$user_restr}".
			         "order by o.id descending";
		$i = 0;
		$sth = ibase_query($dbh, $que_allorders);
		while ($row = ibase_fetch_row ($sth)) {
		    if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
		    $d = date('d.m.Y H:i',strtotime($row[1]));
		    if ($row[4] == 2) $c = ' bgcolor="FF5555"';
		    if ($row[4] == 3) $c = ' bgcolor="FFBA00"';	// в работе
		    if ($row[4] == 4) $c = ' bgcolor="55FF55"';
		    if (($row[4] == 5) && ($row[6] == 0)) $c = ' bgcolor="8080FF"';	// выполнен, не оплачен
		    
		    
    		echo "<tr$c><td><a href='index.php?action=edit&doc={$row[0]}'>{$row[0]}</a>";
    		if ($_SESSION['gue_f'] == 0) echo "<br><b>{$row[5]}</b>";
    		echo "</td><td>{$d}</td><td>{$row[2]}";
    		if (($_SESSION['gue_f'] == 0) && ($row[6] == 0)) echo ", не оплачен";
		echo "</td><td>".htmlspecialchars($row[3])."</td><td>";
    		    //echo "<tr$c><td><a href='index.php?action=edit&doc={$row[0]}'>{$row[0]}</a></td><td>{$d}</td><td>{$row[2]}</td><td>".htmlspecialchars($row[3])."</td><td>";
    		    if ($row[4] == 1) echo "<a href=index.php?action=delete&doc={$row[0]}>Удалить</a>";
    		    echo "</td></tr>";
		}
?>
</table>
<?    
	    }



    	    if ($_GET['action'] == 'news') {
		// рисуем новости
		echo "<h4>Наши новости</h4><br>";
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

		$que_news = "select stmp, txtdata from messages order by id descending";

		$i = 0;
		$sth = ibase_query($dbh, $que_news);
		while ($row = ibase_fetch_row ($sth)) {
		    $d = date('d.m.Y',strtotime($row[0]));
		    echo '<table cellspacing="3" cellpadding="3" border="0" width=800px>';
		    echo "<tr bgcolor='AAAAAA'><td>{$d}</td></tr><tr><td>{$row[1]}</td></tr></table><br>";
		}
	    }

    	    if ($_GET['action'] == 'balance') {
		// рисуем баланс
		echo "<h4>Баланс агента</h4>";
?>   
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr bgcolor="AAAAAA"><td width=15%>Дата операции</td><td width=49%>Операция</td><td width=12%>Приход</td><td width=12%>Расход</td><td>Сальдо</td></tr>
<?php
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

		$que_bln = "select datein, incomesum, outcomesum, note, suminout from printbalance({$uid})";

		$i = 0;
		$sth = ibase_query($dbh, $que_bln);
		while ($row = ibase_fetch_row ($sth)) {
		    $d = date('d.m.Y H:i',strtotime($row[0]));
		    $r1 = "";
		    $r2 = "";
		    if ($row[2] > 0) {
			$c = ' bgcolor="FFCCCC"';
			$r2 = $row[2];
		    }
		    if ($row[1] > 0) {
			$r1 = $row[1];
			$c = ' bgcolor="CCFFCC"';
		    }
    		    echo "<tr$c><td>{$d}</td><td>".htmlspecialchars($row[3])."</td><td>{$r1}</td><td>{$r2}</td><td>{$row[4]}</td></tr>";
		}
?>
</table>
<?    
	    }


    	    if ($_GET['action'] == 'remains') {
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		// если администратор - выводим в начале данные для автозаказа

    		if ($_SESSION['gue_f'] == 0) {
		    $que_rmn = "select n.name, ao.req_amnt, case when r.amount is null then 0 else r.amount end  from aorders ao ".
			       "join nom n on  ao.nom_id = n.nom_id ".
			       "left join remains r on r.nom_id = ao.nom_id and r.registr_id = 1 ".
			       "where ao.nom_id > 0 order by n.group_id, n.name";
			       
		    $sth = ibase_query($dbh, $que_rmn);
?><h4>Контроль товарных запасов</h4><table cellspacing="3" cellpadding="3" border="0" width=600px>
<tr align=right><td align=left width=70%><b>Наименование</td><td><b>Минимум</td><td><b>Имеется</td><?
		    while ($row = ibase_fetch_row ($sth)) {
			$col = '';
			$row[2] = (int)$row[2];
			if ($row[1] > $row[2]) {$col = '<font color=#FF0000><b>'; }
			echo "<tr align=right><td align=left>{$col}{$row[0]}</td><td>{$col}{$row[1]}</td><td>{$col}{$row[2]}</td></tr>";
		    }
?></table><br><?
    		}

		// сначала метраж 
		echo "<h4>Остатки сырья в рулонах</h4>";
		$que_rmn = "select m.id, m.color, r2.remains from remains2 r2 ".
			   "join materials m on m.id = r2.mat_id ".
			   "where r2.remains > 0 order by m.color";

		$sth = ibase_query($dbh, $que_rmn);
?>
<table cellspacing="3" cellpadding="3" border="0" width=350px>
<tr><td width=75%>Наименование</td><td>Остаток, м</td></tr>
<?php

		while ($row = ibase_fetch_row ($sth)) {
		    echo "<tr><td>{$row[1]}</td><td>{$row[2]}</td></tr>";
		}
?>
</table><br>
<?php
		// рисуем остатки
		echo "<h4>Склад готовой продукции</h4>";
		$que_rmn = "select distinct g.groupname, n.name, r1.amount, r3.amount from nom n ".
			   "join groups g on g.groups_id = n.group_id and g.groups_id <> 1700 ".
			   "join remains r on r.nom_id = n.nom_id and (r.registr_id = 1 or r.registr_id = 3 ) ".
			   "left join remains r1 on r1.nom_id = n.nom_id and r1.registr_id = 1 ".
			   "left join remains r3 on r3.nom_id = n.nom_id and r3.registr_id = 3 ".
			   "order by g.groupname";

		$i = 0;
		$sth = ibase_query($dbh, $que_rmn);
		$grpname = "";

		while ($row = ibase_fetch_row ($sth)) {
		    if ($grpname != $row[0]) {
			if ($grpname != '') echo "</table>";
			$grpname = $row[0];
?>
<table cellspacing="3" cellpadding="3" border="0" width=500px>
<?php
			echo "<tr bgcolor=\"AAAAAA\"><td><b>{$grpname}</b></td></tr>";
?>
<table cellspacing="3" cellpadding="3" border="0" width=500px>
<?php

    			echo "<tr><td width=60%>Наименование</td><td>Основной склад</td><td>Склад брака</td></tr>";
		    }
		    echo "<tr><td>{$row[1]}</td><td>";
		    if ($row[2] > 0) echo $row[2]; else echo "-";
		    echo "</td><td>";
		    if ($row[3] > 0) echo $row[3]; else echo "-";
		}
?>
</table>
<?    
	    }

    	    if ($_GET['action'] == 'price') {
		// редактирование прайс-листа
		echo "<h4>Редактирование прайс-листа</h4>";
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

    		// заполнение списка групп материалов
		$que_mtg = "select id, descr from mtgroups where id < 1000 order by id";
		$sth = ibase_query($dbh, $que_mtg);
		$mtgroups = array();
    		while ($row = ibase_fetch_row ($sth)) {$mtgroups[$row[0]] = $row[1];}

    		// заполнение списка продукции 
		$que_prd = "select * from products where id<10 order by id";
		$sth = ibase_query($dbh, $que_prd);
		$products = array();
    		while ($row = ibase_fetch_row ($sth)) {$products[$row[0]] = $row[1];}

    		//заполнение символьного обозначения толщины
    		$thk = array( '0.40' => 'МТ', '0.45' => 'СТ', '0.50' => 'УТ' );


		// в режиме администратора можно выбирать пользователя, если он задан - то используем, если не задан - берем текущего
		if (isset($_GET['user']) && ($_SESSION['gue_f'] == 0)) {
		    $p_user = (int)$_GET['user'];
		} else {
		    $p_user = $uid;
		}


		// получаем список пользователей
    		$users = array();
    		$sth = ibase_query($dbh, "select r.registr_id, r.name from registr r where r.groupregistr_id=9");
    		while ($row = ibase_fetch_row ($sth)) $users[$row[0]] = $row[1];
		
		$que_plp = "select pp.product_id, pp.mtgroup_id, pp.thickness, pr.sizes, pr.mult, pp.price from plprint pp ".
			   "join products pr on pp.product_id=pr.id and pr.id<10 ".
			   "join mtgroups mg on pp.mtgroup_id=mg.id and mg.id<1000 ".
			   "where pp.user_id={$p_user} order by pr.descr, pp.mtgroup_id, pp.thickness";

		$sth = ibase_query($dbh, $que_plp);
?>		
Прайс-лист контрагента <b>
<?php 		
		echo $users[$p_user];
?>
</b><br>Также можно редактировать прайс-листы контрагентов:<br>
<table cellspacing="3" cellpadding="3" border="0" width=754px>
<?php
		$tmp = 0;
		foreach ($users as $id => $u) {
		    if ($tmp % 8 == 0 ) { echo "<tr>"; }
		    echo "<td width=12.5%><a href=index.php?action=price&user={$id}>$u</a></td>"; 
		    $tmp++;
		    if ($tmp % 8 == 0 ) { echo "</tr>"; }
		}
		if ($tmp % 8 != 0 ) {
		    while ($tmp % 8 != 0) {
			echo "<td width=12.5%>&nbsp;</td>";
			$tmp++;
		    }
		    echo "</tr>";
		}
		echo "</table><form name=prices action=index.php?action=saveprice&user={$p_user} method=post>";
 ?>
<table cellspacing="0" cellpadding="0" border="1" width=754px><tr bgcolor="E0E0E0" align=center><td width=18%>Наименование</td><td width=12%>Размеры</td><td width=10%>Толщина металла</td>
<?php		
		foreach ($mtgroups as $id => $mt) if ($id > 0) { echo "<td width=12%>{$mt}</td>"; }
?>
</tr>
<?php		
		$c_pr = -1;
		$c_mt = -1;
		while ($row = ibase_fetch_row ($sth)) {
		    if ($c_pr != $row[0]) { // новый тип продукции
			if ($c_pr > 0) { // и при этом уже не первый в таблице 
			    echo "</table></td></tr>";
			}
			echo "<tr align=center><td><b>".$products[$row[0]]."<b></td><td>{$row[3]}</td>";
			$c_pr = $row[0];
			$c_mt = -1;
		    }

		    if ($c_mt != $row[1]) { // новая группа материалов
			if ($c_mt >= 0) { // и при этом уже не первая в таблице 
			    echo "</table></td>";
			}
			$c_mt = $row[1];
			echo "<td><table cellspacing=0 cellpadding=0 border=0 width=100%>";
		    }
		    
		    if ($row[1] == 0) {
			echo "<tr align=center><td width=100%>{$row[2]}</td></tr>";
		    } else {
		        $pr1 = (float)$row[5];
			echo "<tr align=center><td width=50%><input name=pr_{$row[0]}_{$row[1]}_".($row[2]*100)." size=3 value='{$pr1}'></td><td>{$pr1}</td></tr>";
		    }
		}
?>
</table></td></tr></table><table border=0 width=754px><tr><td></td>
<td width=100px align=center><a href=index.php?action=pprint&user=<?php echo $p_user; ?> target='_blank'><img src='imgs/price.png'><br>Печать</a></td>
<td width=100px align=center><a onclick="window.document.forms['prices'].submit();" href=#><img src='imgs/save.png'><br>Сохранить</a></td>
</tr></table>
<?php		
	    }

    	    if ($_GET['action'] == 'printdrw') {
		// Печать чертежа
    		EditorPrintDrw();
    	    }

    	    if ($_GET['action'] == 'prdrwprod') {
		// Печать чертежа
    		EditorPrintDrwProd();
    	    }

    	    if ($_GET['action'] == 'delivery') {
		// печать стоимости доставки нашей машиной
		echo "<h4>Стоимость доставки на ".date('d.m.Y')."</h4>";
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

    		// заполнение списка близлежащих населенных пунктов
		$que_pts = "select point,  price from logistics order by substring(point from position ('. ' in point)+2)";
		$sth = ibase_query($dbh, $que_pts);
?>
<table cellspacing="0" cellpadding="4" border="0" width=464px><tr bgcolor="E0E0E0" align=center><td width=80%>Населенный пункт</td><td>Цена</td></tr>
<?php		
		$i = 0;
    		while ($row = ibase_fetch_row ($sth)) {
    		    if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
    		    echo "<tr{$c}><td>{$row[0]}</td><td align=right>$row[1]</td></tr>";
    		}
?>
</table>
<?php		
    	
	    };



    	    if ($_GET['action'] == 'pprint') {
		// печать прайс-листа
		echo "<h4>Прайс-лист от ".date('d.m.Y')."</h4>";
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

    		// заполнение списка групп материалов
		$que_mtg = "select id, descr from mtgroups where id<1000 order by id";
		$sth = ibase_query($dbh, $que_mtg);
		$mtgroups = array();
    		while ($row = ibase_fetch_row ($sth)) {$mtgroups[$row[0]] = $row[1];}

    		// заполнение списка продукции 
		$que_prd = "select * from products where id<10 order by id";
		$sth = ibase_query($dbh, $que_prd);
		$products = array();
    		while ($row = ibase_fetch_row ($sth)) {$products[$row[0]] = $row[1];}
    		
    		//заполнение символьного обозначения толщины
    		$thk = array( '0.35' => 'МТ', '0.40' => 'СТ', '0.45' => 'УТ' );

		// в режиме администратора можно выбирать пользователя, если он задан - то используем, если не задан - берем текущего
		if (isset($_GET['user']) && ($_SESSION['gue_f'] == 0)) {
		    $p_user = (int)$_GET['user'];
		} else {
		    $p_user = $uid;
		}

		$que_plp = "select pp.product_id, pp.mtgroup_id, pp.thickness, pr.sizes, pr.mult, pp.price from plprint pp ".
			   "join products pr on pp.product_id=pr.id and pr.id<10 ".
			   "join mtgroups mg on pp.mtgroup_id=mg.id and mg.id<1000 ".
			   "where pp.user_id={$p_user} order by pr.descr, pp.mtgroup_id, pp.thickness";
		$sth = ibase_query($dbh, $que_plp); 
 ?>
<b>Профилированный и гладкий металл</b><br><br><table cellspacing="0" cellpadding="0" border="1" width=754px><tr bgcolor="E0E0E0" align=center><td width=18%>Наименование</td><td width=12%>Размеры</td><td width=10%>Толщина металла</td>
<?php		
		
		foreach ($mtgroups as $id => $mt) if ($id > 0) { echo "<td width=12%>{$mt}<br><br><b>кв.м/пог.м</b></td>"; }
?>
</tr>
<?php		
		$c_pr = -1;
		$c_mt = -1;
		while ($row = ibase_fetch_row ($sth)) {
		    if ($c_pr != $row[0]) { // новый тип продукции
			if ($c_pr > 0) { // и при этом уже не первый в таблице 
			    echo "</table></td></tr>";
			}
			echo "<tr align=center><td><b>".$products[$row[0]]."<b></td><td>{$row[3]}</td>";
			$c_pr = $row[0];
			$c_mt = -1;
		    }

		    if ($c_mt != $row[1]) { // новая группа материалов
			if ($c_mt >= 0) { // и при этом уже не первая в таблице 
			    echo "</table></td>";
			}
			$c_mt = $row[1];
			echo "<td><table cellspacing=0 cellpadding=0 border=0 width=100%>";
		    }
		    
		    if ($row[1] == 0) {
		        $th = isset($thk[$row[2]]) ? $thk[$row[2]] : $row[2];
			echo "<tr align=center><td width=100%>{$th}</td></tr>";
		    } else {
		        $pr1 = (float)$row[5];
		        $pr2 = round($pr1 / $row[4]);
		        if ($pr1 < 1) {
			    echo "<tr align=center><td colspan=2>-</td></tr>";
		        } else {
			    echo "<tr align=center><td width=50%>{$pr2}</td><td>{$pr1}</td></tr>";
		        }
		        
		    }
		}
?>
</table></td></tr></table><br><b>Доборные элементы</b><br><br><table cellspacing="0" cellpadding="5" border="1" width=464px><tr bgcolor="E0E0E0" align=center><td width=60%>Наименование</td>
<?php
		echo "<td width=20%>{$mtgroups[1]}</td><td>{$mtgroups[2]}</td></tr>";
		$que_plp = "select mt.id, mt.descr, pp.mtgroup_id, pp.price from plprint pp ".
	    		   "join mtgroups mt on mt.id=cast(pp.thickness as integer) ".
			   "where pp.user_id={$p_user} and pp.product_id=10 order by mt.id, pp.mtgroup_id";
		
		$sth = ibase_query($dbh, $que_plp); 

		$c_pr = -1;
		$c_mt = -1;
		while ($row = ibase_fetch_row ($sth)) {

		    if ($c_pr != $row[0]) { // новый тип доборных элементов
			if ($c_pr > 0) { // и при этом уже не первый в таблице 
			    echo "</tr>";
			}
			echo "<tr align=center><td align=left><b>{$row[1]}</b></td>";
			$c_pr = $row[0];
			$c_mt = -1;
		    }
		    echo "<td>{$row[3]}</td>";
		}
/*
?>
</table><br><b>Профиль для гипсокартона</b><br><br><table cellspacing="0" cellpadding="5" border="1" width=464px><tr bgcolor="E0E0E0" align=center><td width=75%>Наименование</td><td>Цена</td></tr>
<?php
		$que_plp = "select nm.name, pp.price from plprint pp join nom nm on nm.nom_id=cast(pp.thickness as integer) where pp.user_id={$p_user} and pp.product_id=11 order by nm.name";
		
		$sth = ibase_query($dbh, $que_plp); 

		while ($row = ibase_fetch_row ($sth)) {
		    echo "<tr><td><b>{$row[0]}</b></td><td align=center>{$row[1]}</td></tr>";
		};
		echo "</table>";
*/
	    };

    	    if ($_GET['action'] == 'manage') {
		// управление производством
		echo "<h4>Консолидированный заказ в производство</h4>";
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

    		// заполнение списка материалов
		$sth = ibase_query($dbh, "select m.id, m.color from materials m where m.af=1 order by m.color");
		$materials = array();
    		while ($row = ibase_fetch_row ($sth)) $materials[$row[0]] = $row[1];
?><table border="0" cellspacing="3" cellpadding="10"><tr  valign="top"><td><b>Выбор материала:</b><br><?php
		foreach ($materials as $id => $mt) echo "<a href=index.php?action=showcons&mt={$id} target='_blank'>{$mt}</a><br>";

    		// заполнение списка продукции
		$sth = ibase_query($dbh, "select p.id, p.descr from products p where p.id < 10 order by p.descr");
		$prods = array();
    		while ($row = ibase_fetch_row ($sth)) $prods[$row[0]] = $row[1];
?></td><td><b>Выбор Продукции:</b><br><?php
		foreach ($prods as $id => $pr) echo "<a href=index.php?action=showcons2&pr={$id} target='_blank'>{$pr}</a><br>";
		echo "</td></tr></table><br><h4>Заказ на добор</h4>";

		$que_ord =  "select * from HLP_PRODORDERADD";
		$sth = ibase_query($dbh, $que_ord);
?>
<table cellspacing="3" cellpadding="3" border="0" width=700px><tr font=bold color=#EEEEEE><td width=65%>Элемент</td><td>Требуется</td><td width=15%>Заказы</td><td>Свободно</td></tr>
<?php
		while ($row = ibase_fetch_row ($sth)) {
		    echo "<tr><td width=65%>{$row[0]}</td><td>{$row[1]} шт</td><td>";
		    $ol = explode(",",$row[2]);
    		    foreach ($ol as $tmp) { echo "<a href=index.php?action=edit&doc={$tmp}>{$tmp}</a><br>"; };
		    echo "</td><td>{$row[3]} шт</td></tr>";
		}		    
?>
</table>
<?php
		echo "<br><h4>Добор уже в резерве (справочно)</h4>";

		$que_ord =  "select n.name, cast(sum(dd.amount) as integer) from nom n ".
			    "join docsdata dd on dd.nom_id = n.nom_id ".
			    "join documents dc on dc.docs_id = dd.docs_id and dc.tipdocs_id = 5 and dc.subtype_id=10 and (dc.statedocs_id <8 and dc.statedocs_id >3) ".
			    "join applyrec ar on ar.docsdata_id = dd.docsdata_id and ar.flag = 1 ".
			    "where n.group_id in (1801,1839,1701,1912,1828) ".
			    "group by n.name order by n.name";
		$sth = ibase_query($dbh, $que_ord);
?>
<table cellspacing="3" cellpadding="3" border="0" width=700px><tr font=bold color=#EEEEEE><td width=65%>Элемент</td><td>В резерве</td><td>&nbsp;</td></tr>
<?php
		while ($row = ibase_fetch_row ($sth)) {
		    echo "<tr><td>{$row[0]}</td><td>{$row[1]} шт</td><td>&nbsp;</td></tr>";
		}		    
?>
</table>


<?php
	    }

    	    if ($_GET['action'] == 'finance') {
		// финансы
		echo "<h4>Финансы</h4>";
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');

    		// формирование баланса
		$que_bln = "select rg.name, sum(bl.outcomesum)-sum(bl.incomesum) as fff from balance bl ".
			   "join registr rg on rg.registr_id=bl.contractor_id ".
			   "group by rg.name order by fff";
		
		$sth = ibase_query($dbh, $que_bln);
		$bl = 0;
?>
<table cellspacing="3" cellpadding="3" border="0" width=500px>
<?php
		while ($row = ibase_fetch_row ($sth)) {
		    if ($row[1] == 0) {
			if ($bl != 0) echo "</table></td></tr>";
			$bl = 0;
			continue; 
		    }
		    if ($bl == 0 && $row[1] < 0) {
			echo '<tr><td bgcolor="00FF00"><b>Мы должны:<br><table cellspacing="3" cellpadding="3" border="0" width=500px>';
			$bl = 1;
		    };
		    if ($bl == 0 && $row[1] > 0) {
			echo '<tr><td bgcolor="FF0000"><b>Нам должны:<br><table cellspacing="3" cellpadding="3" border="0" width=500px>';
			$bl = 1;
		    };
		    if ($row[1] < 0) $row[1] = - $row[1];
    		    echo "<tr><td width=75%>{$row[0]}</td><td align=right>".money_format('%.2n',$row[1])." р.</td></tr>";		    
		}
		if ($bl != 0) echo "</table></td></tr>";
?>
</table>
<?php
	    }

    	    if ($_GET['action'] == 'cbook') {
		// Касса
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		DrawCashBox();
	    }


    	    if ($_GET['action'] == 'linksp') {
		// редактирование связей документов доборного и основного производств
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		LinkSPDrawVisual();
	    }

    	    if ($_GET['action'] == 'drawings') {
		// список чертежей доборных элементов
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		ShowDrwList();
	    }

    	    if (($_GET['action'] == 'editdrw') || ($_GET['action'] == 'newdrw')) {
		// редактирование чертежей доборных элементов
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		EditorDrawVisual();
	    }
	    
	    if ($_GET['action'] == 'drwprod') {
		// подтверждение отправки чертежа в производство
		EditorConfirmDrwToProd();
    	    }

	    if ($_GET['action'] == 'deldrw') {
		// подтверждение отправки чертежа в производство
		EditorConfirmDelDrw();
    	    }

    	    if ($_GET['action'] == 'edit' || $_GET['action'] == 'new') {
		// редактирование документа    	    
    	    
    		$doc = $_GET['action'] == 'new'? -1:(int)$_GET['doc'];

		// заполнение шапочки 
    		$hdr = $_GET['action'] == 'new'? "Новый заказ":"Редактирование заказа";
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		
		$user_restr = $_SESSION['gue_f'] == 0 ? "":"o.user_id ={$uid} and";
		// услуги для продажи может добавлять только администратор
		$gg_restr = $_SESSION['gue_f'] == 0 ? "1701,1912":"1701";

		$docsumm = 0.0;
		
		$sth = ibase_query($dbh, "select o.id, o.creation_ts, o.comments, o.state_id, o.user_id, o.fc_flag, o.discount from orders o where {$user_restr} o.id={$doc}");
		
    		echo "<h4>{$hdr}";
    		if ($row = ibase_fetch_row ($sth)) echo " $doc от ".date('d.m.Y',strtotime($row[1]));
    		echo "</h4>";
    		$notice = $row[2];
    		$state_id = $row[3]==0 ? 1:$row[3];
    		$doc_owner = $row[4];
    		$fc_flag = $row[5];
    		$discount = (float)$row[6];

		// особый порядок работы с комиссией при оплате с карт
		$sth = ibase_query($dbh, "select cast(c.constvalue as integer) from constant c where c.constant_id = 14");
    		if ($row = ibase_fetch_row ($sth)) { $cc = $row[0]; }

    		// заполнение списка материалов
		$sth = ibase_query($dbh, "select m.id, m.color from materials m where m.af > 0 order by m.mtype, m.id");
		$materials = array();
    		while ($row = ibase_fetch_row ($sth)) $materials[$row[0]] = $row[1];

    		// заполнение справочника статусов
		$states = array();
		$users = array();
		 
		if ($_SESSION['gue_f'] == 0) {
    		    $sth = ibase_query($dbh, "select r.id, r.descr from ref_ostates r order by r.id");
    		    while ($row = ibase_fetch_row ($sth)) $states[$row[0]] = $row[1];

    		    $sth = ibase_query($dbh, "select r.registr_id, r.name from registr r where r.groupregistr_id=9");
    		    while ($row = ibase_fetch_row ($sth)) $users[$row[0]] = $row[1];
    		}

    		// заполнение типа продукции
		$sth = ibase_query($dbh, "select p.id, p.descr from products p where p.id < 10 order by p.id");
		$products = array();
    		while ($row = ibase_fetch_row ($sth)) $products[$row[0]] = $row[1];
    		
    		// заполнение списка возможных элементов добора
		$sth = ibase_query($dbh, "select n.nom_id, n.name from nom n where n.group_id in ({$gg_restr},1801,1828,1839,1840) order by n.name");
		$nom = array();
    		while ($row = ibase_fetch_row ($sth)) $nom[$row[0]] = $row[1];


		if (($state_id == 1) || ($_SESSION['gue_f'] == 0)) {
		    echo "<form name=ddata action=index.php?action=save&doc={$doc} method=post>";
		}    		
    		
		// для документов с любым статусом в режиме "администратор" можно сменить пользователя
		if ($_SESSION['gue_f'] == 0) {	
        	    echo "Агент: <select name=user>";
    		    foreach ($users as $id => $usr){
    		        if ($id == $doc_owner) $o = 'selected'; else $o = '';
    		        echo "<option {$o} value={$id}>{$usr}</option>";
    		    }
    		    echo "</select>&nbsp;<input type='checkbox' name='fc'";
    		    if ($fc_flag == 1) echo " checked";
    		    echo ">Заказ оплачен</input>";
    		}
    		
		//заполнение тела документа, производственная часть, запрос будет различаться для документа со статусом "принят" и всеми остальными
		if (($state_id == 1) || ($_SESSION['gue_f'] == 0)) {
		    $user_restr = $_SESSION['gue_f'] == 0 ? "" : " and u.registr_id={$uid}";
		    $que_odata = "select od.id, od.material_id, od.lng, od.amount, r1.descr, od.product_id, od.state_id from odata od ".
				 "join orders o on o.id = od.order_id ".
				 "join registr u on u.registr_id=o.user_id ".
		    		 "join ref_ostates r1 on od.state_id=r1.id ".
			    	 "where od.order_id={$doc}{$user_restr} and od.product_id < 10".
			    	 "order by od.id";
?>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr bgcolor="AAAAAA"><td width=15%>Продукция</td><td width=30%>Материал / толщина, мм</td><td width=15%>Длина, мм</td><td width=15%>Кол-во, шт</td><td width=15%>Состояние</td><td></td></tr>
<?php    	    
	    	 
		} else {
		    $que_odata = "select od.id, mt.color, od.lng, od.amount, r1.descr, pr.descr from odata od ".
				 "join materials mt on mt.id = od.material_id ".
				 "join products pr on pr.id = od.product_id and pr.id < 10".
				 "join orders o on o.id = od.order_id ".
				 "join registr u on o.user_id = u.registr_id ".
				 "join ref_ostates r1 on od.state_id=r1.id ".
				 "where od.order_id={$doc} and u.registr_id={$uid}".
				 "order by od.id";
?>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr bgcolor="AAAAAA"><td width=15%>Продукция</td><td width=40%>Материал / толщина, мм</td><td width=15%>Длина, мм</td><td width=15%>Кол-во, шт</td><td width=15%>Состояние</td></tr>
<?php    	    
		}
		$sth = ibase_query($dbh, $que_odata);
		$i = 0;
		while ($row = ibase_fetch_row ($sth)) {
		    if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
		    if (($state_id == 1) || ($_SESSION['gue_f'] == 0)) {
			// колонка выбора продукции
        		echo "<tr$c><td><select name=pr{$row[0]}>";
    			foreach ($products as $id => $pr){
    			    if ($id == $row[5]) $o = 'selected'; else $o = '';
    			    echo "<option {$o} value={$id}>{$pr}</option>";
    			}
    			echo "</select></td>";
			// колонка выбора материала
        		echo "<td><select name=mt{$row[0]}>";
    			foreach ($materials as $id => $mt){
    			    if ($id == $row[1]) $o = 'selected'; else $o = '';
    			    echo "<option {$o} value={$id}>{$mt}</option>";
    			}
    			echo "</select></td><td><input name=len{$row[0]} size=7 value={$row[2]}></td><td><input name=amount{$row[0]} size=3 value={$row[3]}></td><td>$row[4]";
    			if ((($row[6] == 2) || ($row[6] == 3)) && ($_SESSION['gue_f'] == 0)) {
    			    echo "&nbsp;&nbsp<a href='index.php?action=mark&doc={$doc}&line={$row[0]}'><img src=imgs/mark.png></a>";
    			}
    			echo "</td><td><a href='index.php?action=delstr&doc={$doc}&line={$row[0]}'>Удалить</a></td></tr>";
    		    } else {
			if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
    			echo "<tr$c><td>$row[5]</td><td>$row[1]</td><td>{$row[2]}</td><td>{$row[3]}</td><td>$row[4]</td></tr>";
    		    }	
    		}
		if (($state_id == 1) || ($_SESSION['gue_f'] == 0)) { // возможность добавить новую строку в редактируемый документ
		    if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
		    // колонка выбора продукции
    	    	    echo "<tr$c><td><select name=pr_new><option selected disabled>Продукция</option>";
    		    foreach ($products as $id => $pr) echo "<option value={$id}>{$pr}</option>";
    		    echo "</select></td>";

		    // колонка выбора материала
    	    	    echo "<td><select name=mt_new><option selected disabled>Материал</option>";
    		    foreach ($materials as $id => $mt) echo "<option value={$id}>{$mt}</option>";
    		    echo "</select></td><td><input name=len_new size=7 value=''></td><td><input name=amount_new size=3 value=''></td><td></td><td><a onclick=\"window.document.forms['ddata'].action='index.php?action=addstr&doc={$doc}'; window.document.forms['ddata'].submit();\" href=#>Добавить</a></td></tr>";
		}
?>
</table>
<?php
		$user_restr = $_SESSION['gue_f'] == 0 ? "0" : "{$uid}";
		$que_summ = "select o_mtid, o_flen, o_price, o_reserved, o_total, o_prc_recmnd, o_prc_base from HLP_GETORDERTOTALS({$doc},{$user_restr})";
		$sth = ibase_query($dbh, $que_summ);
?>
<br>
<table cellspacing="3" cellpadding="3" border="0" width=800px><tr bgcolor="AAAAAA">
  <td width=30%>Материал</td>
  <td width=12%>Кол-во, м</td>
  <td width=12%>Цена</td>
  <td width=12%>Стоимость</td>
  <td>Дополнительно</td>
</tr>
<?php
		if ($discount > 0) {
		    echo "<tr><td><b>Розничная скидка</b> {$discount} р. с метра</td></tr>";
		};

		while ($row = ibase_fetch_row($sth)){
		    $postfix = '';
		    $mt_id = $row[0];
		    if ($mt_id > 1000) {
			$mt_id -= 1000;
			$postfix = ' (черепица)';
		    };
		    $tmp = ((int)(($row[4]-$row[3])*1000))/1000;
		    echo "<tr align=right><td align=left>{$materials[$mt_id]}{$postfix}</td><td>{$row[1]}м<br>{$tmp}/{$row[4]}</td>";
		    $price = (float)$row[2];
		    //в режиме администратора даем возможность выставлять цену для материалов, но цена базовая, без скидки
		    if ($_SESSION['gue_f'] == 0) {
			echo "<td align=left><input name=price_{$row[0]} size=7 value='{$price}'>";
			if ($price > 0 && $discount > 0){
			    echo "(-{$discount})";
			    $price -= $discount;
			};
		        echo "</td><td>".round($price * (float)$row[1],2)."<font color=red><b> (".round($row[5] * (float)$row[1],2).")</b></font></td>";
			echo "<td>По прайсу: <b>{$row[6]}</b>; Розница: <b>{$row[5]}</b></td>";
			echo "</tr>";
		    } else {
	    		if ($price > 0)	{$price -= $discount;};
			echo "<td>".$price."</td><td>".($price * (float)$row[1])."</td><td></td></tr>";

		    }
		    $docsumm += (float)$row[1] * $price;
		}
		echo "</table><br>";

		//заполнение тела документа, доборная часть, запрос будет различаться для документа со статусом "принят" и всеми остальными
		if (($state_id == 1) || ($_SESSION['gue_f'] == 0)) {
		    $user_restr = $_SESSION['gue_f'] == 0 ? "" : " and o.user_id={$uid}";
		    $que_odata = "select od.id, od.material_id, od.amount, op.price, pl.price, n.price from odata od ".
				 "join orders o on o.id = od.order_id ".
				 "join registr u on u.registr_id=o.user_id ".
				 "join nom n on n.nom_id=od.material_id ".
				 "left join pricelist pl on pl.user_id=o.user_id and pl.material_id=od.material_id ".
				 "left join orprlinks op on op.order_id=o.id and op.material_id=od.material_id ".
				 "where od.order_id={$doc}{$user_restr} and od.product_id=10 ".
				 "order by od.id";
?>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr bgcolor="AAAAAA"><td width=60%>Наименование</td><td width=10%>Количество</td><td width=10%>Цена</td><td width=10%>Стоимость</td><td></td></tr>
<?php    	    
	    	 
		} else {

		    $que_odata = "select od.id, n.name, od.amount, op.price, pl.price, n.price from odata od ".
				 "join orders o on o.id = od.order_id ".
				 "join registr u on u.registr_id=o.user_id ".
				 "join nom n on n.nom_id=od.material_id ".
				 "left join pricelist pl on pl.user_id=o.user_id and pl.material_id=od.material_id ".
				 "left join orprlinks op on op.order_id=o.id and op.material_id=od.material_id ".
				 "where od.order_id={$doc} and o.user_id={$uid} and od.product_id=10 ".
				 "order by od.id";
?>
<table cellspacing="3" cellpadding="3" border="0" width=500px>
<tr bgcolor="AAAAAA"><td width=60%>Наименование</td><td width=10%>Количество</td><td width=10%>Цена</td><td width=10%>Стоимость</td></tr>
<?php    	    
		}
		$sth = ibase_query($dbh, $que_odata);
		$i = 0;
		while ($row = ibase_fetch_row ($sth)) {
		    if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
		    if ((float)$row[3] == 0)
			if ((float)$row[4] == 0) $price = (float)$row[5]; else $price = (float)$row[4];
		    else $price = (float)$row[3];
		    
		    if (($state_id == 1) || ($_SESSION['gue_f'] == 0)) {
			// колонка выбора наименования
        		echo "<tr$c align=right><td align=left><select name=nm{$row[0]}>";
    			foreach ($nom as $id => $nm){
    			    if ($id == $row[1]) $o = 'selected'; else $o = '';
    			    echo "<option {$o} value={$id}>{$nm}</option>";
    			}
    			echo "</select></td>";
    			echo "<td align=left><input name=amount{$row[0]} size=4 value={$row[2]}></td>";
    			if ($_SESSION['gue_f'] == 0) 
    			    // администратор может менять цену на добор
    			    echo "<td align=left><input name=prdb_{$row[0]} size=7 value='{$price}'>";
    			else echo "<td>{$price}";
    			
    			echo "</td><td>".$price*(int)$row[2]."</td><td><a href='index.php?action=delstr&doc={$doc}&line={$row[0]}'>Удалить</a></td></tr>";
    		    } else {
			if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
    			echo "<tr$c align=right><td align=left>{$row[1]}</td><td>{$row[2]}</td>";
    			echo "<td>{$price}</td><td>".$price*(int)$row[2]."</td></tr>";
    		    }	
		    if ($cc != $row[1]) { $docsumm += (float)($price*(int)$row[2]); } // Комиссию при оплате с карты не учитываем в общей сумме
    		}

		if (($state_id == 1) || ($_SESSION['gue_f'] == 0)) { // возможность добавить новую строку в редактируемый документ
		    if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
		    // колонка выбора продукции
    	    	    echo "<tr$c><td><select name=nm_new><option selected disabled>Наименование</option>";
    		    foreach ($nom as $id => $nm) echo "<option value={$id}>{$nm}</option>";
    		    echo "</select></td>";

    		    echo "<td><input name=amount2_new size=5 value=''></td><td><a onclick=\"window.document.forms['ddata'].action='index.php?action=addstr&doc={$doc}'; window.document.forms['ddata'].submit();\" href=#>Добавить</a></td></tr>";
		}
?>
</table><br>
<table cellspacing="0" cellpadding="3" border="0" width=800px><tr><td width=50%>
<?php
		$docsumm = round($docsumm*100) / 100;
		if ($_SESSION['gue_f'] == 0) {
            	    $que_cc = "execute procedure hlp_recalc_cc({$doc})";             
            	    $sth = ibase_query($dbh, $que_cc);
            	    $cc = ibase_fetch_row($sth);
            	    
		    $card = "картой: <b><font color=red>".money_format('%.2n',$cc[2])."</font></b>, наличными: "; 
		} else {
		    $card = "";
		}
		echo "Общая стоимость: {$card}<b>{$docsumm}</b> руб.<br>";
		
		$prep = 100;
		if ($docsumm < 2000) $prep = 50; else
		    if ($docsumm < 5000) $prep = 20; else
			if ($docsumm < 10000) $prep = 40; else
			    if ($docsumm < 50000) $prep = 60; else
				if ($docsumm < 100000) $prep = 80;

		$prep = ((round($docsumm * (float)$prep / 100 / 1000) + 1)*1000);
		
		$prep = $prep > $docsumm ? $docsumm : $prep;
		
		echo "Рекомендуемый размер предоплаты: <b>$prep</b> руб.";
    		echo "</td>";
    		
		if ($_SESSION['our'] > 0 || $_SESSION['gue_f'] == 0) {
		    // для администратора или филиала можно выставлять розничную скидку
    		    if ($_SESSION['our'] > 0) {
    			// филиал, доступен только в режиме редактирования
    			echo "<td>Розничная скидка с метра, руб: <input name='discount' size='5' value='{$discount}'";
    			if ($state_id > 1) {echo " disabled";};
    			echo "></td>";
    		    } else {
    			// администратор, поле для ввода, доступно всегда
    			echo "<td>Розничная скидка с метра, руб: <input name='discount' size='5' value='{$discount}'></td>";
    		    };
    		    echo "</tr><tr><td colspan=2>";
		} else {
		    echo "</tr><tr><td>";
		};

		// для документов со статусом принят, в режиме "пользователь"
		if (($state_id == 1) && ($_SESSION['gue_f'] != 0)) {	
		    echo "Комментарий:<input name=comment size=136 value='".htmlspecialchars($notice)."'>";
?>
</td></tr><tr align="right" bgcolor="CCCCCC"><td colspan=2>
<table cellspacing="0" cellpadding="3" border="0" width=800px><tr><td></td>
<td width=100px align=center><a href=index.php?action=submit&doc=<?php  echo $doc ?>><img src='imgs/prod.png'><br>Запустить в производство</a></td>
<?php
		    if ($_SESSION['our'] > 0) {
?>		
<td width=100px align=center><a href=index.php?action=zn&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/order.png'><br>Заказ-наряд</a></td>
<td width=100px align=center><a href=index.php?action=tc&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/tc.png'><br>Товарный чек</a></td>
<?php
		    }
?>
<td width=100px align=center><a onclick="window.document.forms['ddata'].submit();" href=#><img src='imgs/save.png'><br>Сохранить</a></td></tr></table>
</td></tr></table></form>
<?php
		}
?>

<?php
		// для документов с любым статусом, в режиме "администратор"
		if ($_SESSION['gue_f'] == 0) {	
		    echo "Комментарий:<input name=comment size=136 value='".htmlspecialchars($notice)."'></td></tr>";
?>
<tr align="right" bgcolor="CCCCCC"><td colspan=2><br>
<?php
        	    echo "Статус заказа: <select name=ostate>";
    		    foreach ($states as $id => $st){
    		        if ($id == $state_id) $o = 'selected'; else $o = '';
    		        echo "<option {$o} value={$id}>{$st}</option>";
    		    }
    		    
    		    echo "</select><br>";
?>    		    
<table cellspacing="0" cellpadding="3" border="0" width=800px><tr><td></td>
<?php
		    if (($state_id > 1) && ($state_id < 4)) {
?>
<td width=100px align=center><a href=index.php?action=rsrvd&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/rsrvd.png'><br>Состояние резервов</a></td>
<?php
		    }
		    if (!LinkSPCheckNonVisual()) {
?>		
<td width=100px align=center><a href=index.php?action=linksp&doc=<?php echo $doc; ?>><img src='imgs/hmr.png'><br>Доборный цех</a></td>
<?php
		    }
?>
<td width=100px align=center><a href=index.php?action=inv&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/invoice.png'><br>Задание на производство</a></td>
<td width=100px align=center><a href=index.php?action=zn&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/order.png'><br>Заказ-наряд</a></td>
<td width=100px align=center><a href=index.php?action=tc&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/tc.png'><br>Товарный чек</a></td>
<td width=100px align=center><a href=index.php?action=tc2&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/card.png'><br>Товарный чек по карте</a></td>
<td width=100px align=center><a href=index.php?action=ld&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/dost.png'><br>Доставка</a></td>


<td width=100px align=center><a onclick="window.document.forms['ddata'].submit();" href=#><img src='imgs/save.png'><br>Сохранить</a></td></tr></table>
</td></tr></table></form>
<?php
		}		
		
		// для документов со статусом "согласован" и выше, в режиме "пользователь"
		if (($state_id != 1) && ($_SESSION['gue_f'] != 0)) {
		    echo "Комментарий: {$notice}</td></tr>";
?>
<tr align="right" bgcolor="CCCCCC"><td colspan=2><br>
<table cellspacing="0" cellpadding="3" border="0" width=800px><tr><td></td>
<?php
		    if (($state_id > 1) && ($state_id < 4)) {
?>
<td width=100px align=center><a href=index.php?action=rsrvd&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/rsrvd.png'><br>Состояние резервов</a></td>
<?php
		    }
		    if ($_SESSION['our'] > 0) {
?>		
<td width=100px align=center><a href=index.php?action=tc&doc=<?php echo $doc; ?> target="_blank"><img src='imgs/tc.png'><br>Товарный чек</a></td>
<?php

		    }
		    echo "</tr></table>";
		}
	    }


	    // форма подтверждения удаления документа
    	    if ($_GET['action'] == 'delete') {
    		$doc = (int)$_GET['doc'];
    		
		// заполнение шапочки 
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		$sth = ibase_query($dbh, "select o.id, o.creation_ts, o.comments from orders o where o.user_id =$uid and o.id=$doc");
		$row = ibase_fetch_row($sth);
    		echo "<h4>Удаление заказа {$doc} от ".date('d.m.Y',strtotime($row[1]))."</h4>";
    		echo "Примечание: {$row[2]}";

		//заполнение тела документа
		echo "<br>";		
		$que_odata = "select od.id, mt.color, od.lng, od.amount, r1.descr from odata od ".
			     "join materials mt on mt.id = od.material_id ".
			     "join orders o on o.id = od.order_id ".
			     "join registr u on u.registr_id=o.user_id and u.registr_id={$uid} ".
			     "join ref_ostates r1 on od.state_id=r1.id ".
			     "where od.order_id={$doc} ".
			     "order by od.id";
?>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr bgcolor="AAAAAA"><td width=55%>Материал / толщина, мм</td><td width=15%>Длина, мм</td><td width=15%>Кол-во, шт</td><td width=15%>Состояние</td></tr>
<?php    	    
		$sth = ibase_query($dbh, $que_odata);
		$i = 0;
		while ($row = ibase_fetch_row ($sth)) {
		    if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
    		    echo "<tr$c><td>$row[1]</td><td>{$row[2]}</td><td>{$row[3]}</td><td>$row[4]</td></tr>";
    		}
?>
</table>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr align="center" bgcolor="EEEEE"><td><h5><a href="index.php?action=sure&doc=
<?php 
		echo $doc; 
?>
"><img src=imgs/yes.png><br>Да, я хочу удалить этот заказ</a></h5></td><td><h5><a href="index.php"><img src=imgs/no.png><br>Нет, я хочу оставить этот заказ</a></h5></td></tr>
</table>
<?php
	    }

    	    if ($_GET['action'] == 'submit') {
    		$doc = (int)$_GET['doc'];
    		
		// заполнение шапочки 
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		$sth = ibase_query($dbh, "select o.id, o.creation_ts, o.comments from orders o where o.user_id =$uid and o.id=$doc");
		$row = ibase_fetch_row($sth);
    		echo "<h4>Запуск в производство заказа {$doc} от ".date('d.m.Y',strtotime($row[1]))."</h4>";
    		echo "Примечание: {$row[2]}";

		//заполнение тела документа
		echo "<br>";		
		$que_odata = "select od.id, mt.color, od.lng, od.amount, r1.descr from odata od ".
			     "join materials mt on mt.id = od.material_id ".
			     "join orders o on o.id = od.order_id ".
			     "join registr u on u.registr_id=o.user_id and u.registr_id={$uid} ".
			     "join ref_ostates r1 on od.state_id=r1.id ".
			     "where od.order_id={$doc} ".
			     "order by od.id";
?>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr bgcolor="AAAAAA"><td width=55%>Материал / толщина, мм</td><td width=15%>Длина, мм</td><td width=15%>Кол-во, шт</td><td width=15%>Состояние</td></tr>
<?php    	    
		$sth = ibase_query($dbh, $que_odata);
		$i = 0;
		while ($row = ibase_fetch_row ($sth)) {
		    if ((($i++) & 1) == 1) $c = ' bgcolor="DDDDDD"'; else $c='';
    		    echo "<tr$c><td>$row[1]</td><td>{$row[2]}</td><td>{$row[3]}</td><td>$row[4]</td></tr>";
    		}
?>
</table>
<table cellspacing="3" cellpadding="3" border="0" width=800px>
<tr align="center" bgcolor="EEEEE"><td><h5><a href="index.php?action=send&doc=
<?php 
		echo $doc; 
?>
"><img src=imgs/yes.png><br>Да, я хочу запустить заказ в производство</a></h5></td><td><h5><a href="index.php?action=edit&doc=
<?php 
		echo $doc; 
?>
"><img src=imgs/no.png><br>Нет, вернуться к редактированию заказа</a></h5></td></tr>
</table>
<?php
	    }	    
	    
    	    if ($_GET['action'] == 'inv') {
    		$doc = (int)$_GET['doc'];
    		
		// заполнение шапочки 
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		
		$user_restr = $_SESSION['gue_f'] == 0 ? "" : " and o.user_id={$uid}";
		$que_invc = "select n.name, cast(dd.amount as integer), ar.flag from orders o ".
			    "join dlinks d on d.doc1=o.id ".
    			    "join documents dc on dc.docs_id = d.doc2 and dc.tipdocs_id=5 and dc.note like 'Резервирование готовой продукции%' ".
			    "join docsdata dd on dd.docs_id = dc.docs_id ".
			    "join nom n on dd.nom_id=n.nom_id ".
			    "join applyrec ar on ar.docsdata_id=dd.docsdata_id ".
    			    "where o.id={$doc}{$user_restr} order by ar.flag, n.nom_id descending";
		
		$sth = ibase_query($dbh, $que_invc);
		$ar = -1;
?>
<h4>Заказ <?php echo $doc; ?></h4>
<table cellspacing="3" cellpadding="3" border="0" width=500px><tr bgcolor="AAAAAA">
<?php    	    

		while ($row = ibase_fetch_row($sth)) {
		    if ($row[2] != $ar){
			// начало нового раздела
			if ($row[2] == 0) {
			    // производство
			    echo "<td><b>Задание на производство</b></td></tr></table>";
			    echo "<table cellspacing=\"3\" cellpadding=\"3\" border=\"0\" width=500px><tr><td width=65%></td><td></td></tr>";
			} else {
			    // склад
			    if ($ar == 0) echo "</table><br><table cellspacing=\"3\" cellpadding=\"3\" border=\"0\" width=500px><tr bgcolor=\"AAAAAA\">";
			    echo "<td><b>Отгрузка со склада</b></td></tr></table>";
			    echo "<table cellspacing=\"3\" cellpadding=\"3\" border=\"0\" width=500px><tr><td width=65%></td><td></td></tr>";//<tr><td width=65%>Наименование</td><td>Количество, шт</td></tr>";
			}
			$ar = $row[2];
		    }
		    echo "<tr><td>{$row[0]}</td><td>{$row[1]} шт</td></tr>";
		};
		if ($ar >= 0) echo "</table>";
	    }	    	    

    	    if ($_GET['action'] == 'tc') {	// товарный чек
    		$doc = (int)$_GET['doc'];
    		
		// заполнение шапочки 
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		
		$user_restr = $_SESSION['gue_f'] == 0 ? "" : " and o.user_id={$uid}";
		$que_cmnt = "select comments from orders where id={$doc}";
		$sth = ibase_query($dbh, $que_cmnt);
		if ($row = ibase_fetch_row($sth)) $cmnt = $row[0]; 
		
		
		$que_rgdt = "select null, rg.fullname from registr rg where rg.registr_id={$DocTitleRegID} union ".
			    "select rt.name, rd.datastr from registrdata rd ".
			    "join registrtypedata rt on rd.registrtypedata_id=rt.registrtypedata_id ".
			    "where rd.registr_id={$DocTitleRegID}";

		$sth = ibase_query($dbh, $que_rgdt);
?>
<table cellspacing="0" cellpadding="0" border="0" width=600px><tr><tr><td>
<?php
		if ($row = ibase_fetch_row($sth)) {
		    // здесь вывод реквизитов для товарного чека
		    echo "<b>{$row[1]}</b>";
		    while ($row = ibase_fetch_row($sth)) {
			echo "<br><b>{$row[0]}</b> {$row[1]}";
		    }
		}
		
?>
<br><br></td></tr><tr><td align=center><h4>Товарный чек <?php echo $doc." от ".date('d.m.Y'); ?></h4></td></tr><tr><td>
<table cellspacing="0" cellpadding="3" border="1" width=100%><tr bgcolor="AAAAAA">
<tr><td><b>Наименование</td><td><b>Кол-во</td><td><b>Цена</td><td><b>Сумма</td></tr>
<?php    	    
		// табличная часть				
		$que_tc = "select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname, od.amount, (ol.price-ord.discount)*od.lng/1000 as price1, (pl.price-ord.discount)*od.lng/1000 as price0, nm.price from odata od ".
			  "join materials mt on od.material_id=mt.id ".
			  "join products pr on od.product_id=pr.id and pr.id < 10 and pr.id <> 4 ".
			  "join orders ord on ord.id=od.order_id and ord.id={$doc} ".
			  "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id ".
			  "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id ".
			  "left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' ".
			  "union ".
			  "select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname, od.amount, (ol.price-ord.discount)*od.lng/1000 as price1, (pl.price-ord.discount)*od.lng/1000 as price0, nm.price from odata od ".
			  "join materials mt on od.material_id=mt.id ".
			  "join products pr on od.product_id=pr.id and pr.id = 4 ".
			  "join orders ord on ord.id=od.order_id and ord.id={$doc} ".
			  "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id+1000 ".
			  "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id+1000 ".
			  "left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' ".
			  "union ".
			  "select nm.name, od.amount, ol.price, pl.price, nm.price from odata od ".
			  "join nom nm on od.material_id=nm.nom_id ".
			  "join products pr on od.product_id=pr.id and pr.id = 10 ".
			  "join orders ord on ord.id=od.order_id and ord.id = {$doc} ".
			  "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = od.material_id ".
			  "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id ".
			  "join constant c on c.constant_id = 14 and nm.nom_id <> cast(c.constvalue as integer)";
		
		$sth = ibase_query($dbh, $que_tc);

		$fsum = 0;
		while ($row = ibase_fetch_row($sth)) {
		    if ($row[2] != 0) {
			$pr = $row[2];
		    } else if ($row[3] != 0) {
			$pr = $row[3];
		    } else {
			$pr = $row[4];
		    };
		    
		    $sm = $row[1] * $pr;
		    $fsum += $sm;
		    echo "<tr align=right><td align=left>{$row[0]}</td><td>{$row[1]}</td><td>".money_format('%.2n',$pr)."</td><td>".money_format('%.2n',$sm)."</td></tr>";
		};
		
		echo "</table></td></tr><tr><td><br><p align=right>";"Итого: ".money_format('%.2n',$fsum);

		preg_match("/.*pr:(\d+)/i", $cmnt, $matches);
		if (isset($matches[1])) {
		    $pr = (int)$matches[1]; 
		} else {
		    $pr = 0;
		}
		
		//preg_match("/.*fs:(\d+)/i", $cmnt, $matches);
		//$fs = (int)$matches[1] == 0 ? $fsum : (int)$matches[1];
		
	
		//if ($fs < $fsum)  echo "Скидка: ".money_format('%.2n',$fsum - $fs)."<br>";
		//if ($fs > $fsum)  echo "Дополнительные расходы: ".money_format('%.2n',$fs - $fsum)."<br>";
		if ($pr > 0)   echo "Предоплата: ".money_format('%.2n',$pr)." руб.<br>";
		echo "Итого к оплате: ".money_format('%.2n',$fsum - $pr)." руб.";
		echo"</td></tr><tr><td><br>Продавец ____________________________<br><br>";
		echo"<font size=0px>Примечание<br>";
		echo"1.Погрузка осуществляется бесплатно только <b>в пустой грузовой транспорт с ровным полом и шириной кузова не менее 1,6м и высотой не менее 1,8м</b><br>";
		echo"2.Стоимость доставки не включена в стоимость товара и оплачивается отдельно<br>";		
		echo"3.При заказе доставки <b>выгрузка осуществляется силами заказчика</b>";		
		echo"</font></td></tr></table>";

		//логгирование
		$que_log = "execute procedure LOG_PRINTCHECK({$doc},{$fsum})";
		$trn = ibase_trans($dbh);
		ibase_query($trn, $que_log);
		ibase_commit($trn);
	    }	    	    

    	    if ($_GET['action'] == 'tc2') {	// товарный чек
    		$doc = (int)$_GET['doc'];
    		
		// заполнение шапочки 
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		
		$user_restr = $_SESSION['gue_f'] == 0 ? "" : " and o.user_id={$uid}";

		$que_cmnt = "select comments from orders where id={$doc}";
		$sth = ibase_query($dbh, $que_cmnt);
		if ($row = ibase_fetch_row($sth)) $cmnt = $row[0]; 
		
		
		$que_rgdt = "select null, rg.fullname from registr rg where rg.registr_id={$DocTitleRegID} union ".
			    "select rt.name, rd.datastr from registrdata rd ".
			    "join registrtypedata rt on rd.registrtypedata_id=rt.registrtypedata_id ".
			    "where rd.registr_id={$DocTitleRegID}";

		$sth = ibase_query($dbh, $que_rgdt);
?>
<table cellspacing="0" cellpadding="0" border="0" width=600px><tr><tr><td>
<?php
		if ($row = ibase_fetch_row($sth)) {
		    // здесь вывод реквизитов для товарного чека
		    echo "<b>{$row[1]}</b>";
		    while ($row = ibase_fetch_row($sth)) {
			echo "<br><b>{$row[0]}</b> {$row[1]}";
		    }
		}
		
?>
<br><br></td></tr><tr><td align=center><h4>Товарный чек <?php echo $doc." от ".date('d.m.Y'); ?></h4></td></tr><tr><td>
<table cellspacing="0" cellpadding="3" border="1" width=100%><tr bgcolor="AAAAAA">
<tr><td><b>Наименование</td><td><b>Кол-во</td><td><b>Цена</td><td><b>Сумма</td></tr>
<?php    	    
		// табличная часть				
	        $que_tc = "select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname,
	                          od.amount,
                	          coalesce((ol.price-ord.discount)*od.lng/1000,
                            		   (pl.price-ord.discount)*od.lng/1000,
                                    	   nm.price)
                             from odata od
                             join materials mt on od.material_id=mt.id
                             join products pr on od.product_id=pr.id and pr.id < 10 and pr.id <> 4
                             join orders ord on ord.id=od.order_id and ord.id= {$doc}
                             left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id
                             left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id
                             left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м'
                           union
                           select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname,
                                  od.amount,
                                  coalesce((ol.price-ord.discount)*od.lng/1000,
                                           (pl.price-ord.discount)*od.lng/1000,
                                           nm.price)
                             from odata od
                             join materials mt on od.material_id=mt.id
                             join products pr on od.product_id=pr.id and pr.id = 4
                             join orders ord on ord.id=od.order_id and ord.id= {$doc}
                             left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id+1000
                             left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id+1000
                             left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м'
                           union
                           select nm.name, od.amount, coalesce(ol.price, pl.price, nm.price) from odata od
                             join nom nm on od.material_id=nm.nom_id
                             join products pr on od.product_id=pr.id and pr.id = 10
                             join orders ord on ord.id=od.order_id and ord.id = {$doc}
                             left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = od.material_id
                             left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id
                             join constant c on c.constant_id = 14 and nm.nom_id <> cast(c.constvalue as integer) ";
                             
                $que_cc = "execute procedure hlp_recalc_cc({$doc})";             
                $sth = ibase_query($dbh, $que_cc);
                $cc = ibase_fetch_row($sth);
                $percent = round(($cc[0] * 100) / ($cc[2] - $cc[0]), 1);
            
		$sth = ibase_query($dbh, $que_tc);


		$fsum = 0;
		while ($row = ibase_fetch_row($sth)) {
		    $pr = round(($row[2] * (100 + $percent))/100, 2);	    
		    //$pr = $percent; // 1.025;	//скидка при оплате наличными - 2,5%
		    $sm = $row[1] * $pr;
		    $fsum += $sm;
		    echo "<tr align=right><td align=left>{$row[0]}</td><td>{$row[1]}</td><td>".money_format('%.2n',$pr)."</td><td>".money_format('%.2n',$sm)."</td></tr>";
		};
		echo "</table></td></tr><tr><td><br><p align=right>";"Итого: ".money_format('%.2n',$fsum);

		preg_match("/.*pr:(\d+)/i", $cmnt, $matches);
		if (isset($matches[1])) {
		    $pr = (int)$matches[1]; 
		} else {
		    $pr = 0;
		}
		
		if ($pr > 0)   echo "Предоплата: ".money_format('%.2n',$pr)." руб.<br>";
		echo "Итого к оплате: ".money_format('%.2n',$fsum - $pr)." руб.";
		echo"</td></tr><tr><td><br>Продавец ____________________________<br><br>";
		echo"<font size=0px>Примечание<br>";
		echo"1.Погрузка осуществляется бесплатно только <b>в пустой грузовой транспорт с ровным полом и шириной кузова не менее 1,6м и высотой не менее 1,8м</b><br>";
		echo"2.Стоимость доставки не включена в стоимость товара и оплачивается отдельно<br>";		
		echo"3.При заказе доставки <b>выгрузка осуществляется силами заказчика</b>";		
		echo"</font></td></tr></table>";

		//логгирование
		$que_log = "execute procedure LOG_PRINTCHECK({$doc},{$fsum})";
		$trn = ibase_trans($dbh);
		ibase_query($trn, $que_log);
		ibase_commit($trn);
	    }	    	    


    	    if ($_GET['action'] == 'rsrvd') {	// состояние резервов
    		$doc = (int)$_GET['doc'];
    		
		// заполнение шапочки 
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		
		$user_restr = $_SESSION['gue_f'] == 0 ? 0 : $uid;
		$que_rsrvd = "select ICLASS, ITEM, AMNT, AR from hlp_getreservationinfo({$doc},{$user_restr})";
		$sth = ibase_query($dbh, $que_rsrvd);

		
?>
<table cellspacing="0" cellpadding="0" border="0" width=600px><tr><tr><td>
<br><br></td></tr><tr><td align=center><h4>Состояние резервов для заказа <?php echo $doc; ?></h4></td></tr><tr><td>
<table cellspacing="0" cellpadding="3" border="0" width=100%><tr bgcolor="AAAAAA">
<tr><td><b>Класс</td><td><b>Наименование</td><td><b>Кол-во</td><td><b>Резерв</td></tr>
<?php    	    
		while ($row = ibase_fetch_row($sth)) {
		    echo "<tr><td>";
		    $iclass = $row[0] == 1 ? "Готовая продукция" : "Сырье";
		    echo $iclass."</td><td>{$row[1]}</td><td>{$row[2]}</td>";
		    $rclass = $row[3] == 1 ? "Ok" : "--";
		    echo "<td>{$rclass}</td>";
		}
		echo "</td></tr></table>";
	    }	    	    


    	    if ($_GET['action'] == 'zn') {	// заказ-наряд
    		$doc = (int)$_GET['doc'];
    		
		// заполнение шапочки 
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		
		$user_restr = $_SESSION['gue_f'] == 0 ? "" : " and o.user_id={$uid}";
		$que_cmnt = "select comments from orders where id={$doc}";
		$sth = ibase_query($dbh, $que_cmnt);
		if ($row = ibase_fetch_row($sth)) $cmnt = $row[0]; 
		
		$que_rgdt = "select null, rg.fullname from registr rg where rg.registr_id={$DocTitleRegID} union ".
			    "select rt.name, rd.datastr from registrdata rd ".
			    "join registrtypedata rt on rd.registrtypedata_id=rt.registrtypedata_id ".
			    "where rd.registr_id={$DocTitleRegID}";

		$sth = ibase_query($dbh, $que_rgdt);
?>
<table cellspacing="0" cellpadding="0" border="0" width=600px><tr><tr><td>
<?php
		if ($row = ibase_fetch_row($sth)) {
		    // здесь вывод реквизитов для товарного чека
		    echo "<b>{$row[1]}</b>";
		    while ($row = ibase_fetch_row($sth)) {
			echo "<br><b>{$row[0]}</b> {$row[1]}";
		    }
		}
		
?>
<br><br></td></tr><tr><td align=center><h4>Заказ-наряд <?php echo $doc." от ".date('d.m.Y'); ?></h4></td></tr><tr><td>
<table cellspacing="0" cellpadding="3" border="1" width=100%><tr bgcolor="AAAAAA">
<tr><td><b>Наименование</td><td><b>Кол-во</td><td><b>Цена</td><td><b>Сумма</td></tr>
<?php    	    
		// табличная часть				
		$que_tc = "select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname, od.amount, (ol.price-ord.discount)*od.lng/1000 as price1, (pl.price-ord.discount)*od.lng/1000 as price0, nm.price from odata od ".
			  "join materials mt on od.material_id=mt.id ".
			  "join products pr on od.product_id=pr.id and pr.id < 10 and pr.id <> 4 ".
			  "join orders ord on ord.id=od.order_id and ord.id={$doc} ".
			  "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id ".
			  "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id ".
			  "left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' ".
			  "union ".
			  "select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname, od.amount, (ol.price-ord.discount)*od.lng/1000 as price1, (pl.price-ord.discount)*od.lng/1000 as price0, nm.price from odata od ".
			  "join materials mt on od.material_id=mt.id ".
			  "join products pr on od.product_id=pr.id and pr.id = 4 ".
			  "join orders ord on ord.id=od.order_id and ord.id={$doc} ".
			  "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id+1000 ".
			  "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id+1000 ".
			  "left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' ".
			  "union ".
			  "select nm.name, od.amount, ol.price, pl.price, nm.price from odata od ".
			  "join nom nm on od.material_id=nm.nom_id ".
			  "join products pr on od.product_id=pr.id and pr.id = 10 ".
			  "join orders ord on ord.id=od.order_id and ord.id = {$doc} ".
			  "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = od.material_id ".
			  "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id";
		
		$sth = ibase_query($dbh, $que_tc);

		$fsum = 0;
		while ($row = ibase_fetch_row($sth)) {
		    if ($row[2] != 0) {
			$pr = $row[2];
		    } else if ($row[3] != 0) {
			$pr = $row[3];
		    } else {
			$pr = $row[4];
		    };
		    
		    $sm = $row[1] * $pr;
		    $fsum += $sm;
		    echo "<tr align=right><td align=left>{$row[0]}</td><td>{$row[1]}</td><td>".money_format('%.2n',$pr)."</td><td>".money_format('%.2n',$sm)."</td></tr>";
		};
		echo "</table></td></tr><tr><td><br><p align=right>";"Итого: ".money_format('%.2n',$fsum);

		preg_match("/.*pr:(\d+)/i", $cmnt, $matches);
		if (isset($matches[1])) {
		    $pr = (int)$matches[1]; 
		} else {
		    $pr = 0;
		}
		
		//preg_match("/.*fs:(\d+)/i", $cmnt, $matches);
		//$fs = (int)$matches[1] == 0 ? $fsum : (int)$matches[1];
		
	
		//if ($fs < $fsum)  echo "Скидка: ".money_format('%.2n',$fsum - $fs)."<br>";
		//if ($fs > $fsum)  echo "Дополнительные расходы: ".money_format('%.2n',$fs - $fsum)."<br>";
		if ($pr > 0)   echo "Предоплата: ".money_format('%.2n',$pr)." руб.<br>";
		echo "Итого к оплате: ".money_format('%.2n',$fsum - $pr)." руб.";
		echo"</td></tr><tr><td><br>Продавец ____________________________</td></tr></table>";
	    }	    	    

	    
    	    if ($_GET['action'] == 'showcons') {
    		$mt = (int)$_GET['mt'];
    		
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		
		$que_cor = "select o.id, pr.id, pr.descr, pr.descr||' '||mt.color||' '||cast(od.lng as integer)||' мм', od.amount, o.comments, r.name from orders o ".
			   "join odata od on od.order_id=o.id and od.product_id in (1,2,3,4,5) and od.material_id={$mt} and od.state_id in (2,3) ".
			   "join products pr on od.product_id=pr.id ".
			   "join materials mt on od.material_id=mt.id ".
			   "join registr r on r.registr_id=o.user_id ".
			   "where (o.state_id = 2 or o.state_id = 3) and o.user_id > 0 ".
			   "order by pr.id, o.id";
		
		$sth = ibase_query($dbh, $que_cor);
		
		$pr = 0;
		$doc = 0;		
?><table cellspacing="0" cellpadding="0" border="0" width=800px> <?php
		while ($row = ibase_fetch_row($sth)) {
		    if (($row[0] != $doc && $doc != 0) || ($row[1] != $pr && $doc != 0)) {
			// конец документа, надо закрыть таблицу
			echo "</table><br>&nbsp;</td></tr>";		    
		    }
		    if ($row[1] != $pr) {
			// новый тип продукции
			echo "<tr><td><b>{$row[2]}</td></tr>";
			$pr = $row[1];
			$doc = 0;
		    }
		    if ($row[0] != $doc) {
			$doc = $row[0];
			echo "<tr><td bgcolor=#DDDDDD><b><a href=index.php?action=edit&doc={$row[0]} target='_blank'>Заказ {$row[0]}</a></b>({$row[6]})<br>{$row[5]}</td></tr><tr><td>";
			echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=300px><tr><td width=80%></td><td></td></tr>";
		    }
		    echo "<tr><td>{$row[3]}</td><td>{$row[4]}</td></tr>";
		};
	    }	    	    

    	    if ($_GET['action'] == 'showcons2') {
    		$pr = (int)$_GET['pr'];
    		
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		
		$que_cor = "select o.id, mt.id, mt.color, pr.descr||' '||mt.color||' '||cast(od.lng as integer)||' мм', od.amount, o.comments, r.name from orders o ".
			   "join odata od on od.order_id=o.id and od.product_id = {$pr} and od.state_id in (2,3) ".
			   "join products pr on od.product_id=pr.id ".
		           "join materials mt on od.material_id=mt.id ".
			   "join registr r on r.registr_id=o.user_id ".
		           "where (o.state_id = 2 or o.state_id = 3) and o.user_id > 0 ".
			   "order by mt.color, o.id";
		
		$sth = ibase_query($dbh, $que_cor);
		
		$mt = 0;
		$doc = 0;		
?><table cellspacing="0" cellpadding="0" border="0" width=800px> <?php
		while ($row = ibase_fetch_row($sth)) {
		    if (($row[0] != $doc && $doc != 0) || ($row[1] != $mt && $doc != 0)) {
			// конец документа, надо закрыть таблицу
			echo "</table><br>&nbsp;</td></tr>";		    
		    }
		    if ($row[1] != $mt) {
			// новый тип продукции
			echo "<tr><td><b>{$row[2]}</td></tr>";
			$mt = $row[1];
			$doc = 0;
		    }
		    if ($row[0] != $doc) {
			$doc = $row[0];
			echo "<tr><td bgcolor=#DDDDDD><b><a href=index.php?action=edit&doc={$row[0]} target='_blank'>Заказ {$row[0]}</a></b>({$row[6]})<br>{$row[5]}</td></tr><tr><td>";
			echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=300px><tr><td width=80%></td><td></td></tr>";
		    }
		    echo "<tr><td>{$row[3]}</td><td>{$row[4]}</td></tr>";
		};
	    }	    	    

    	    if ($_GET['action'] == 'ld') {
    		// печать документа для доставки
    		$doc = (int)$_GET['doc'];
    		
		// заполнение шапочки 
		$dbh = ibase_pconnect($DB, $USER, $PASS, 'UTF8');
		
		$user_restr = $_SESSION['gue_f'] == 0 ? "" : " and o.user_id={$uid}";
		$que_cmnt = "select comments from orders where id={$doc}";
		$sth = ibase_query($dbh, $que_cmnt);
		if ($row = ibase_fetch_row($sth)) $cmnt = $row[0]; 

		//табличная часть		
		$que_ld = "select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname, od.amount, (ol.price - ord.discount)*od.lng/1000 as price1, (pl.price - ord.discount)*od.lng/1000 as price0, nm.price from odata od ".
			  "join materials mt on od.material_id=mt.id ".
			  "join products pr on od.product_id=pr.id and pr.id < 10 and pr.id <> 4 ".
			  "join orders ord on ord.id=od.order_id and ord.id={$doc} ".
			  "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id ".
			  "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id ".
			  "left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' ".
			  "union ".
			  "select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname, od.amount, (ol.price - ord.discount)*od.lng/1000 as price1, (pl.price - ord.discount)*od.lng/1000 as price0, nm.price from odata od ".
			  "join materials mt on od.material_id=mt.id ".
			  "join products pr on od.product_id=pr.id and pr.id = 4 ".
			  "join orders ord on ord.id=od.order_id and ord.id={$doc} ".
			  "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id+1000 ".
			  "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id+1000 ".
			  "left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' ".
			  			  
			  "union ".
			  "select nm.name, od.amount, ol.price, pl.price, nm.price from odata od ".
			  "join nom nm on od.material_id=nm.nom_id ".
			  "join products pr on od.product_id=pr.id and pr.id = 10 ".
			  "join orders ord on ord.id=od.order_id and ord.id = {$doc} ".
			  "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = od.material_id ".
			  "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id";

		$sth = ibase_query($dbh, $que_ld);
?>
<table cellspacing="0" cellpadding="0" border="0" width=600px><tr><tr><td><h4>Доставка заказа <?php echo $doc." от ".date('d.m.Y'); ?></h4></td></tr><tr><td>
<table cellspacing="0" cellpadding="3" border="1" width=100%><tr bgcolor="AAAAAA">
<tr><td><b>Наименование</td><td><b>Кол-во</td></tr>
<?php    	    
		(float)$fsum = 0;
		
		while ($row = ibase_fetch_row($sth)) {
		    if ((float)$row[2] != 0.0) {
			$pr = $row[2];
		    } else if ((float)$row[3] != 0.0) {
			$pr = $row[3];
		    } else {
			$pr = $row[4];
		    };

		    $sm = (float)$row[1] * $pr;
		    $fsum += $sm;
		    echo "<tr><td>{$row[0]}</td><td>{$row[1]}</td></tr>";
		};
		echo "</table></td></tr><tr><td><br>";
		
		preg_match("/.*pr:(\d+)/i", $cmnt, $matches);
		$pr = (int)$matches[1];
		echo "<b>Остаток расчетный: </b>".money_format('%.2n',$fsum - $pr)."<br><br>";
		echo"<b>Примечание: </b>{$cmnt}</td></tr></table>";
	    }	    
	}
    }; ?>
</body></html>