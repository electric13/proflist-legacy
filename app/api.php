<?php
    require("conf.php");
    

    
    // класс ApiFunc - абстрактный вызов API, от него будут наследоваться функции с результатом и без
    // содержит проверку на валидность пользователя и набор абстрактных методов
    abstract class ApiFunc {

	protected $uid;
	protected $params;
	private $adm;
	
	function __construct($user, $token) { 
	    // конструктор ожидает на входе два параметра: пользователя и мд5 его пароля
	    // параметр func определяет вызываемую функцию, а params - ее параметры
	    $this->adm = false;
	    $this->CheckUserValid($user, $token);
        }
        
	function CheckUserValid($id, $token){
	    global $DB, $USER, $PASS;

	    $dbh = ibase_pconnect($DB, $USER, $PASS,'UTF8');

	    $t = "select rg.registr_id, rd3.datastr, rd4.datastr from registr rg ".
	    	     "join registrdata rd1 on rg.registr_id = rd1.registr_id and rd1.registrtypedata_id = 34 ".
	    	     "join registrdata rd2 on rg.registr_id = rd2.registr_id and rd2.registrtypedata_id = 35 ".
	    	     "left join registrdata rd3 on rg.registr_id = rd3.registr_id and rd3.registrtypedata_id = 29 ".
	    	     "left join registrdata rd4 on rg.registr_id = rd4.registr_id and rd4.registrtypedata_id = 28 ".
	    	     "where rd1.datastr = cast(? as varchar(10)) and rd2.datastr=?";

	    $q = ibase_prepare($dbh, $t);
	    $sth = ibase_execute($q, (int)$id, $token);
	
	    if ($row = ibase_fetch_row ($sth)) {
	        $this->uid = $row[0];
	        if ($row[1] == 'Y') { $this->adm = true; }
	    }
	    return;
	}

	function CheckDocAccess($doc){
	    global $DB, $USER, $PASS;
	    
	    // если пользователь администратор - значит можно смотреть любые документы	    
	    if ($this->adm && $this->uid != 0) { return true; }
	    // если пользователь не задан - значит нельзя смотреть любые документы
	    if ($this->uid == 0) {return false; }
	    
	    $dbh = ibase_pconnect($DB, $USER, $PASS,'UTF8');
	    
	    $t = "select o.id from orders o where o.id=? and o.user_id=?";
	    $q = ibase_prepare($dbh, $t);
	    $sth = ibase_execute($q, (int)$doc, $this->uid);
	
	    if ($row = ibase_fetch_row ($sth)) {
	        if ($row[0] == $doc) { return true; } 
	    }
	    return false;
	}


        abstract function ApiName();
	abstract function SetParams($p);
	abstract function HasResult();

	function GetResult(){
	    return "";
	}
	
	function DebugData(){
	    echo var_dump($this);
	}
    }
   

    // класс ApiFuncWResults - абстрактная функция API с возвращаемым результатом, от него 
    // будут наследоваться уже реальные функции с результатом
    abstract class ApiFuncWResults extends ApiFunc {
	function HasResult(){
	    return true;
	}
    }

    // класс ApiFuncWOResults - абстрактная функция API без возвращения результата, от него 
    // будут наследоваться уже реальные функции без результата
    abstract class ApiFuncWOResults extends ApiFunc {
	function HasResult(){
	    return false;
	}
    }

    
    // класс для отображения содержимого документа
    class ApiShowDoc extends ApiFuncWResults {

	private $docValid;

        function ApiName(){
    	    return "showdoc";
        }

	function SetParams($p){
	    // параметр "номер документа" обязателен, если не задан или задан криво - выходим
	    $this->docValid = false;
	    $this->params[0] = 0;
	    if ( !isset($p[0]) ) { return; }
	    if ( (int)$p[0] == 0 ) { return; }
	    $this->params[0] = $p[0];
	    if ( !$this->CheckDocAccess($this->params[0])) { return; }
	    $this->docValid = true;
	}

	function GetResult(){
	    global $DB, $USER, $PASS;
	    if (! $this->docValid) { return "Document is invalid or access denied"; }

	    $dbh = ibase_pconnect($DB, $USER, $PASS,'UTF8');

    	    $t = "select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname, od.amount, (ol.price-ord.discount)*od.lng/1000 as price1, ".
    		    "(pl.price-ord.discount)*od.lng/1000 as price0, nm.price from odata od ".
		    "join materials mt on od.material_id=mt.id ".
		    "join products pr on od.product_id=pr.id and pr.id < 10 and pr.id <> 4 ".
		    "join orders ord on ord.id=od.order_id and ord.id=? ".
		    "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id ".
		    "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id ".
		    "left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' ".
		    "union ".
		    "select pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' as nname, od.amount, (ol.price-ord.discount)*od.lng/1000 as price1, (pl.price-ord.discount)*od.lng/1000 as price0, nm.price from odata od ".
		    "join materials mt on od.material_id=mt.id ".
		    "join products pr on od.product_id=pr.id and pr.id = 4 ".
		    "join orders ord on ord.id=od.order_id and ord.id=? ".
		    "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = mt.id+1000 ".
		    "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id+1000 ".
		    "left join nom nm on nm.name = pr.descr||' '||mt.color||' '||cast(od.lng/1000 as varchar(10))||' м' ".
		    "union ".
		    "select nm.name, od.amount, ol.price, pl.price, nm.price from odata od ".
		    "join nom nm on od.material_id=nm.nom_id ".
		    "join products pr on od.product_id=pr.id and pr.id = 10 ".
		    "join orders ord on ord.id=od.order_id and ord.id=? ".
		    "left join pricelist pl on pl.user_id=ord.user_id and pl.material_id = od.material_id ".
		    "left join orprlinks ol on ol.order_id=ord.id and ol.material_id=od.material_id";
	    $q = ibase_prepare($dbh, $t);
	    $sth = ibase_execute($q, $this->params[0], $this->params[0], $this->params[0]);
	    $r = array();
	    $fsum = 0;
	    $i = 0;
	    while ($row = ibase_fetch_row($sth)) {
		// выбираем правильную цену, приоритет: 1.цена явно прописана в заказе; 
		// 2.цена в прайс-листе для данного контрагента; 3. Цена в справочнике товаров
    	        if ($row[2] != 0) {
		    $pr = $row[2];
		} else if ($row[3] != 0) {
		    $pr = $row[3];
		} else { $pr = $row[4]; };
		    
		$r[$i][0] = $row[0]; //наименование
		$r[$i][1] = $row[1]; //количество
		$r[$i][2] = $row[2]; //цена за единицу
		$i++;
	    };
	    return serialize($r);
	}
    }
?>