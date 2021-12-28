<?php
//获取书的单词信息
require_once "../config.php";
require_once "../public/_pdo.php";

$get_book = (int)$_GET["book"];
$get_par_begin = (int)$_GET["begin"];
$get_par_end = (int)$_GET["end"];


//open database
PDO_Connect(_FILE_DB_PALICANON_TEMPLET_);
if ($get_par_end == -1 || ($get_par_end - $get_par_begin) > 50000) {
    echo "0,0,0,0";
    exit;
} else 
{
    $query1 = "SELECT count(*) FROM "._TABLE_PALICANON_TEMPLET_." WHERE book = $get_book and  paragraph BETWEEN $get_par_begin AND $get_par_end";
    $query2 = "SELECT count(*) FROM (SELECT count(*),real FROM "._TABLE_PALICANON_TEMPLET_." WHERE book = $get_book and (paragraph BETWEEN $get_par_begin AND $get_par_end ) group by real ) T";

    $query3 = "SELECT sum(length(real)) FROM "._TABLE_PALICANON_TEMPLET_." WHERE book = $get_book and paragraph BETWEEN $get_par_begin AND $get_par_end";
    $query4 = "SELECT sum(length(real)) FROM (SELECT count(*),real FROM "._TABLE_PALICANON_TEMPLET_." WHERE book = $get_book and (paragraph BETWEEN $get_par_begin AND $get_par_end ) group by real ) T";

    $allword = PDO_FetchOne($query1);
    $allword_token = PDO_FetchOne($query2);
    $allwordLen = PDO_FetchOne($query3);
    $allword_tokenLen = PDO_FetchOne($query4);

    echo $allword . "," . $allword_token . "," . $allwordLen . "," . $allword_tokenLen;
}
?>