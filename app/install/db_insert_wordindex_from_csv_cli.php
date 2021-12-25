<?php
/*
生成 巴利原文段落表
 */
require_once __DIR__."/../config.php";
require_once __DIR__.'/../public/_pdo.php';


echo "Insert Word Index To DB".PHP_EOL;


$dirLog = _DIR_LOG_ . "/";
$log = "";

$dns = _FILE_DB_WORD_INDEX_;
$dbh_word_index = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_word_index->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

#删除
$query = "DELETE FROM "._TABLE_WORD_INDEX_." WHERE true";
$stmt = $dbh_word_index->prepare($query);
$stmt->execute();

    $scan = scandir(_DIR_CSV_PALI_CANON_WORD_INDEX_);
    foreach($scan as $filename) {
        $filename = _DIR_CSV_PALI_CANON_WORD_INDEX_."/".$filename;
        if (is_file($filename)) {
            echo "doing ".$filename.PHP_EOL;
            if (($fpoutput = fopen($filename, "r")) !== false) {

                // 开始一个事务，关闭自动提交
                $dbh_word_index->beginTransaction();
                $query = "INSERT INTO "._TABLE_WORD_INDEX_." (id , word , word_en , count , normal , bold , is_base , len ) VALUES (?,?,?,?,?,?,?,?)";
        
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
                    echo "error - $error[2]".PHP_EOL;
                    $log .= "$filename, error, $error[2] \r\n";
                } else {
                    echo "updata $count recorders.".PHP_EOL;
                    $log .= "updata $count recorders.\r\n";
                }
            }else{
                echo "open file error".PHP_EOL;
            }
        
        }
    }

echo "齐活！功德无量！all done!".PHP_EOL;



?>
