<?php
include("dbvars.inc.php");
$couponList = array(0=>'100.00', 1=>'300.00', 2=>'500.00', 3=>'1000.00');
$sqlSelect="select BalanceAction, BalanceAmount, BalanceDate, ID from account_balance order by BalanceDate ASC;";
$rsSelect=mysql_query($sqlSelect);
$count=1;
while($Select=mysql_fetch_row($rsSelect)){
    $sqlInsert="UPDATE account_balance SET ID=".intval($count)." where BalanceAction=".$Select[0]." and BalanceAmount='".$Select[1]."' and BalanceDate=".$Select[2].";";
    $rsInsert=mysql_query($sqlInsert);
    $count++;
}

?>