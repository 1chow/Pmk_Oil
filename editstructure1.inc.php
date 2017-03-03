<?php
include("dbvars.inc.php");
$couponList = array(0=>'100.00', 1=>'300.00', 2=>'500.00', 3=>'1000.00');
$sqlSelect="select CouponUsedID, PaymentID, payments.SaveBy, payments.Date from (".$db_name.".payments inner join ".$db_name.".coupon_used on CouponUsedID=UsedID) where payments.Type='Coupon' group by PaymentID;";
$rsSelect=mysql_query($sqlSelect);
while($Select=mysql_fetch_row($rsSelect)){
    $DateCheck = date("Y-m-d", $Select[3]);
    $sqlGetCustID="select CouponCode, CustomerID, Price, RealUse from ".$db_name.".coupon where UseHistoryID='".$Select[0]."';";
    $rslGetCustID=mysql_query($sqlGetCustID);
    while($GetCustID=mysql_fetch_row($rslGetCustID)){
    	if(!isset($cusID[$GetCustID[2]][$GetCustID[1]])){
            $cusID[$GetCustID[2]][$GetCustID[1]]="<br>คูปองมูลค่า ".$GetCustID[2]." เลขที่ ".$GetCustID[0];
    		$cusIDUsed[$GetCustID[2]][$GetCustID[1]]=$GetCustID[3];
    	}
    	else{
    		$cusID[$GetCustID[2]][$GetCustID[1]].=",".$GetCustID[0];
            $cusIDUsed[$GetCustID[2]][$GetCustID[1]]+=round($GetCustID[3], 2);
    	}
    }

    foreach($couponList as $key => $price) {
    	if(isset($cusID[$price])){
		    foreach($cusID[$price] as $CustomerID => $textSave) {
		        $sqlChk="select Total from ".$db_name.".coupon_history where LockReason='".$Select[0]."' and CustomerID=".intval($CustomerID).";";
		        $rsChk=mysql_query($sqlChk);
		        if(!mysql_num_rows($rsChk)){
		            $sqlHistory="INSERT INTO ".$db_name.".coupon_history (EmpID, Total, CustomerID, ProcessDate, HistoryNote, ChangeNote, LockReason) VALUES (".intval($Select[2]).", '".floatval($cusIDUsed[$price][$CustomerID])."', ".intval($CustomerID).", '".mysql_real_escape_string(trim($DateCheck))."', 'ใช้คูปอง', '".substr($textSave, 4)."', ".intval($Select[0]).");";
		            echo $sqlHistory."<br>";
		            $rsHistory=mysql_query($sqlHistory);
		        }
		    }
		}
	}
	unset($cusID);
	unset($cusIDUsed);
}
?>