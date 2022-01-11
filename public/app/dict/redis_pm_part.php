<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../redis/function.php";

if (PHP_SAPI == "cli") {
	$redis = redis_connect();
	if ($redis != false) {
		$dbh = new PDO(_DICT_DB_PM_, "", "", array(PDO::ATTR_PERSISTENT => true));
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$query = "SELECT pali,parts from "._TABLE_DICT_PM_." where 1 group by pali";
		$stmt = $dbh->query($query);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			# code...
			if(!empty($row["parts"])){
				$redis->hSet("dict://pm/part",$row["pali"],$row["parts"]);
			}
		}
	}
	echo "all done ".$redis->hLen("dict://pm/part");
}

?>