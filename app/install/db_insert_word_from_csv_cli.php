<?php
/*
生成 巴利原文段落表
 */
require_once __DIR__."/../config.php";
require_once __DIR__.'/../public/_pdo.php';


echo "Insert Word To DB".PHP_EOL;

if ($argc != 3) {
	echo "help".PHP_EOL;
	echo $argv[0]." from to".PHP_EOL;
	echo "from = 1-217".PHP_EOL;
	echo "to = 1-217".PHP_EOL;
	exit;
}
$_from = (int) $argv[1];
$_to = (int) $argv[2];
if ($_to > 217) {
	$_to = 217;
}

$dirLog = _DIR_LOG_ . "/";
$dirXmlBase = _DIR_PALI_CSV_ . "/";

$filelist = array();
$fileNums = 0;
$log = "";


global $dbh_word_index;
$dns = _FILE_DB_PALI_INDEX_;
$dbh_word_index = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_word_index->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

if (($handle = fopen(__DIR__."/filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($_to == 0 || $_to >= $fileNums) {
    $to = $fileNums ;
}

for ($from=$_from-1; $from < $_to; $from++) { 
    echo "doing ".($from+1).PHP_EOL;
    #删除
    $query = "DELETE FROM "._TABLE_WORD_." WHERE book = ?";
    $stmt = $dbh_word_index->prepare($query);
    $stmt->execute(array($from+1));


    if (($fpoutput = fopen(_DIR_CSV_PALI_CANON_WORD_ . "/{$from}_words.csv", "r")) !== false) {
        // 开始一个事务，关闭自动提交
        $dbh_word_index->beginTransaction();
        $query = "INSERT INTO "._TABLE_WORD_." ( sn , book , paragraph , wordindex , bold ) VALUES (?,?,?,?,?)";
        $stmt = $dbh_word_index->prepare($query);

        $count = 0;
        while (($data = fgetcsv($fpoutput, 0, ',')) !== false) {
            $stmt->execute($data);
            $count++;
        }
        // 提交更改
        $dbh_word_index->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = $dbh_word_index->errorInfo();
            echo "error - $error[2] ".PHP_EOL;
            $log .= "$from, $FileName, error, $error[2] \r\n";
        } else {
            echo "updata $count recorders.".PHP_EOL;
            $log .= "updata $count recorders.\r\n";
        }
    }
/*
    $myLogFile = fopen($dirLog . "insert_index.log", "a");
    fwrite($myLogFile, $log);
    fclose($myLogFile);
    */
}
    echo "齐活！功德无量！all done!".PHP_EOL;

?>