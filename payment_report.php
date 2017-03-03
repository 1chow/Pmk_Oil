<?php
include("dbvars.inc.php");
if(!preg_match('/-13-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

if(!isset($_REQUEST['oilPaidDate'])){
    $_REQUEST['oilPaidDate']=date("d/m/Y", time());
}
$canSubmit=0;
if(isset($_REQUEST["TimeForCheck"]) && (!isset($_COOKIE["TimeCheck"]) || $_COOKIE["TimeCheck"]!=$_REQUEST["TimeForCheck"])){
    setcookie("TimeCheck", $_REQUEST["TimeForCheck"], 0, "/");
    $_COOKIE["TimeCheck"]=$_REQUEST["TimeForCheck"];
    $canSubmit=1;
}

$SuccessText='';
$SetDate=explode("/", $_REQUEST['oilPaidDate']);
$startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
$endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
if(isset($_POST["DoAction"]) && intval($_REQUEST["DoAction"])==1){
    if($_REQUEST["RecordType"]==1){
        $MoreCondition=" and Type='Coupon'";
    }
    else if($_REQUEST["RecordType"]==2){
        $MoreCondition=" and Type in ('Cash', 'Card')";
    }
    else{
        $MoreCondition=" and Type='Credit'";
    }
    $sqlConfirm="update ".$db_name.".payments set Confirmed=1 where TimeRound=".intval($_REQUEST["TimeRound"]).$MoreCondition." and SendBy=".intval($_REQUEST["CashierID"])." and payments.Date>=".intval($startDate)." and payments.Date<=".intval($endDate).";";
    $rsConfirm=mysql_query($sqlConfirm);

    if($_REQUEST["RecordType"]==3){
        // check over credit for each credit customer
        $sqlCust="SELECT CustID, DayBeforePay, UnofficialBalance, CreditLimit, CreditLock, CreditTerm, SpecialTerm from ".$db_name.".customer where Deleted=0 and CreditLimit>0 and FromService=0;";
        $rsCust=mysql_query($sqlCust);
        while($CustInfo=mysql_fetch_row($rsCust)){
            $sqlUse="SELECT sum(RealUsed) from ".$db_name.".credit_billing where Status=0 and CustID=".$CustInfo[0]." and Confirmed=1;";
            $rsUse=mysql_query($sqlUse);
            $UseTotal=mysql_fetch_row($rsUse);
            // วางบิลทันทีที่วงเงินเหลือน้อยกว่ายอดแจ้งเตือน
            if($CustInfo[5]==3 && $UseTotal[0]>=$CustInfo[6]){ // billing by warning credit and used total over than credit limit
                $BillingDate=time();
                if($CustInfo[1]){
                    $CollectDate=strtotime(date('Y-m-d', $BillingDate).' +'.$CustInfo[1].' day');
                }
                else{
                    $CollectDate=0;
                }
                if($canSubmit){
                    $sqlInsert="INSERT INTO ".$db_name.".billing_history (BillingDate, CollectSchedule, PaidDate, Total, CustID) VALUES (".$BillingDate.", ".$CollectDate.", 0, '".floatval($UseTotal[0])."', ".$CustInfo[0].");";
                    $rsInsert=mysql_query($sqlInsert);
                    $HistoryID=mysql_insert_id($Conn);

                    $sqlUpdate="UPDATE ".$db_name.".credit_billing SET Status=".$HistoryID." WHERE Status=0 and CustID=".$CustInfo[0]." and Confirmed>0;";
                    $rsUpdate=mysql_query($sqlUpdate);
                }
            }
        }
    }
    $SuccessText='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>ยืนยันข้อมูลเรียบร้อยแล้ว.</div>';
}
else if(isset($_POST["Amount"]) && $_REQUEST["RecordType"]==2){
    foreach($_REQUEST["Amount"] as $key => $value) {
        if(floatval($_REQUEST["Amount"][$key])){
            $_REQUEST["Amount"][$key]=preg_replace("/,/", "", $_REQUEST["Amount"][$key]);
            $sqlConfirm="update ".$db_name.".payments set Type='".mysql_real_escape_string(trim($_REQUEST["PaymentType"][$key]))."', Amount=".floatval($_REQUEST["Amount"][$key]).", CardSlip=".intval($_REQUEST["CardSlip"][$key])." where PaymentID=".intval($key).";";
            $rsConfirm=mysql_query($sqlConfirm);
        }
        else{
            $sqlConfirm="delete from ".$db_name.".payments where PaymentID=".intval($key).";";
            $rsConfirm=mysql_query($sqlConfirm);
        }
    }
    $SuccessText='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>แก้ไขข้อมูลเรียบร้อยแล้ว.</div>';
}
else if(isset($_REQUEST["PaymentIDList"]) && $_REQUEST["RecordType"]==1 && $canSubmit){
    $NewPaymentList=0;
    $OldPaymentList=$_REQUEST["PaymentIDList"];
    $sqlCoupon="select coupon.ID, RealUse, Price, CustomerID from (".$db_name.".payments inner join ".$db_name.".coupon on payments.CouponUsedID=coupon.UseHistoryID) where payments.PaymentID in (".$_REQUEST["PaymentIDList"].");";
    $rsCoupon=mysql_query($sqlCoupon);
    while($Coupon=mysql_fetch_row($rsCoupon)){
        $FindOldDiff = round($Coupon[2]-$Coupon[1], 2);
        $sqlUpdate="UPDATE ".$db_name.".coupon SET RealUse='0.00', Status=1, UseHistoryID=0 where ID=".intval($Coupon[0]).";";
        $rsUpdate=mysql_query($sqlUpdate);
        $sqlUpdate="UPDATE ".$db_name.".customer SET CouponBalance=(CouponBalance-(".round($FindOldDiff, 2).")) where CustID=".intval($Coupon[3]).";";
        $rsUpdate=mysql_query($sqlUpdate);
    }

    $couponList = array();
    $couponCount = array();
    asort($_POST["useCouponNo"]);
    $couponusedList=""; // price*couponcode-price*couponcode-...
    foreach($_POST["useCouponNo"] as $key => $value) {
        $_POST["couponRealUse"][$key]=preg_replace("/,/", "", $_POST["couponRealUse"][$key]);
        $couponForCheck=$_POST["couponRealUse"][$key]."~".trim($value);
        if(trim($value) && intval($_POST["couponRealUse"][$key]) && !preg_match("/".$couponForCheck."/", $couponusedList)){
            $couponusedList.=", ".$couponForCheck;
            $FindDiff = round($_POST["useCouponPrice"][$key]-$_POST["couponRealUse"][$key], 2);
            $sqlUpdate="UPDATE ".$db_name.".coupon SET RealUse='".$_POST["couponRealUse"][$key]."', Status=2";
            if(isset($_REQUEST['Cust4Coupon'][$key])){
                $sqlUpdate.=", CustomerID=".intval($_REQUEST['Cust4Coupon'][$key]);
            }
            $sqlUpdate.=" where Price='".floatval($_POST["useCouponPrice"][$key])."' and CouponCode='".mysql_real_escape_string(trim($value))."';";
            $rsUpdate=mysql_query($sqlUpdate);
            if(!isset($couponList[$_POST["useCouponPrice"][$key]])){
                $couponList[$_POST["useCouponPrice"][$key]]="";
                $couponCount[$_POST["useCouponPrice"][$key]]=0;
                $realCouponUse[$_POST["useCouponPrice"][$key]]=0;
            }
            $couponList[$_POST["useCouponPrice"][$key]].=", ".mysql_real_escape_string(trim($value));
            $couponCount[$_POST["useCouponPrice"][$key]]++;
            $realCouponUse[$_POST["useCouponPrice"][$key]]+=$_POST["couponRealUse"][$key];

            $sqlgetCustID="select CustomerID from ".$db_name.".coupon where Price='".floatval($_POST["useCouponPrice"][$key])."' and CouponCode=".intval($value).";";
            $rsGetCustID=mysql_query($sqlgetCustID);
            if(!mysql_num_rows($rsGetCustID)){
                $sqlInsert="INSERT INTO ".$db_name.".coupon (BookNo, BookCodeNo, CouponCode, Price, Status, RealUse, CustomerID) VALUES ('', '', ".intval($value).",  '".floatval($_POST["useCouponPrice"][$key])."', 2, '".floatval($_POST["couponRealUse"][$key])."', ".intval($_POST["Cust4Coupon"][$key]).");";
                $rsInsert=mysql_query($sqlInsert);
                $GetCustID[0]=$_POST["Cust4Coupon"][$key];
            }
            else{
                $GetCustID=mysql_fetch_row($rsGetCustID);
            }
            $sqlUpdate="UPDATE ".$db_name.".customer SET CouponBalance=(CouponBalance+(".round($FindDiff, 2).")) where CustID=".intval($GetCustID[0]).";";
            $rsUpdate=mysql_query($sqlUpdate);
            $sqlUpdate."<br>";
            // เก็บข้อมูลคูปองใส่ตาราง history
            $couponPrice=$_POST["useCouponPrice"][$key];
            if(!isset($cusID[$couponPrice][$GetCustID[0]])){
                $cusIDUsed[$couponPrice][$GetCustID[0]]=$_POST["couponRealUse"][$key];
                $cusID[$couponPrice][$GetCustID[0]]="<br>คูปองมูลค่า ".$couponPrice." เลขที่ ".$value;
            }
            else{
                $cusID[$couponPrice][$GetCustID[0]].=", ".$value;
                $cusIDUsed[$couponPrice][$GetCustID[0]]+=round($_POST["couponRealUse"][$key], 2);
            }
        }
    }
    $setDateArr = explode("/", $_POST["oilPaidDate"]);
    $setDate = $setDateArr[2].'-'.$setDateArr[1].'-'.$setDateArr[0];
    $setDateSave=mktime(date('H', time()), date('i', time()), date('s', time()), $setDateArr[1], intval($setDateArr[0]), $setDateArr[2]);
    $setDateText = $setDateArr[0].' '.$monthList[($setDateArr[1]-1)].' '.$setDateArr[2];
    $DateCheck = $setDateArr[2].'-'.$setDateArr[1].'-'.$setDateArr[0];

    foreach($couponList as $price => $value) {
        $sqlSelect="select CouponUsedID, PaymentID from (".$db_name.".payments inner join ".$db_name.".coupon_used on CouponUsedID=UsedID) where payments.Type='Coupon' and payments.TimeRound=".intval($_POST["TimeRound"])." and coupon_used.CouponPrice='".$price."' and payments.SendBy=".intval($_REQUEST["CashierID"])." and payments.Date>=".$startDate." and payments.Date<=".$endDate." and payments.PaymentID in (".$_REQUEST["PaymentIDList"].") group by PaymentID;";
        $rsSelect=mysql_query($sqlSelect);
        $Select=mysql_fetch_row($rsSelect);
        if(intval($Select[0])){
            $NewPaymentList.=", ".$Select[1];
            $sqlUpdate="UPDATE ".$db_name.".coupon_used SET CouponIDList='".substr($value, 2)."', CouponCount=".intval($couponCount[$price])." where UsedID='".$Select[0]."';";
            $rsUpdate=mysql_query($sqlUpdate);
            $sqlUpdate="UPDATE ".$db_name.".coupon SET UseHistoryID=".$Select[0]." where Price='".$price."' and UseHistoryID=0 and CouponCode in (".substr($value, 2).");";
            $rsUpdate=mysql_query($sqlUpdate);
            $sqlUpdate="UPDATE ".$db_name.".payments SET Amount='".floatval($realCouponUse[$price])."' where PaymentID='".$Select[1]."';";
            $rsUpdate=mysql_query($sqlUpdate);
            foreach ($cusID[$price] as $CustID => $textSave) {
                $sqlChk="select Total from ".$db_name.".coupon_history where LockReason='".$Select[0]."' and CustomerID=".intval($CustID).";";
                $rsChk=mysql_query($sqlChk);
                $ChkRecNum=mysql_num_rows($rsChk);
                if($ChkRecNum){
                    $sqlHistoryUpdate="UPDATE ".$db_name.".coupon_history SET Total='".floatval($cusIDUsed[$price][$CustID])."', ChangeNote='".substr($textSave, 4)."' where LockReason='".$Select[0]."' and CustomerID=".intval($CustID).";";
                    $rsHistoryUpdate=mysql_query($sqlHistoryUpdate);
                }
                else{
                    $sqlHistory="INSERT INTO ".$db_name.".coupon_history (EmpID, Total, CustomerID, ProcessDate, HistoryNote, ChangeNote, LockReason) VALUES (".intval($UserID).", '".floatval($cusIDUsed[$price][$CustID])."', ".intval($CustID).", '".mysql_real_escape_string(trim($DateCheck))."', 'ใช้คูปอง', '".substr($textSave, 4)."', ".intval($Select[0]).");";
                    $rsHistory=mysql_query($sqlHistory);
                }
            }
        }
        else{
            $sqlHistoryID="select sum(UseHistoryID) from ".$db_name.".coupon where Price='".$price."' and CouponCode in (".substr($value, 2).");";
            $rsHistoryID=mysql_query($sqlHistoryID);
            $GetHistoryID=mysql_fetch_row($rsHistoryID);

            $sqlUsedID="select UsedID from ".$db_name.".coupon_used where CouponPrice='".$price."' and CouponIDList='".substr($value, 2)."';";
            $rsUsedID=mysql_query($sqlUsedID);
            $UsedIDNum=mysql_num_rows($rsUsedID);
            if(!intval($GetHistoryID[0]) && !intval($UsedIDNum)){
                $sqlHistory="INSERT INTO ".$db_name.".coupon_used (ProcessDate, CouponPrice, CouponIDList, SendUserID, Confirmed, SaveBy, CouponCount) VALUES ('".$setDate."', '".$price."', '".substr($value, 2)."', ".intval($_REQUEST["CashierID"]).", 1, ".intval($UserID).", ".intval($couponCount[$price]).");";
                $rsHistory=mysql_query($sqlHistory);
                $HistoryID=mysql_insert_id($Conn);
                $sqlUpdate="UPDATE ".$db_name.".coupon SET UseHistoryID=".$HistoryID." where Price='".$price."' and UseHistoryID=0 and CouponCode in (".substr($value, 2).");";
                $rsUpdate=mysql_query($sqlUpdate);
                $sqlInsertPayment="INSERT INTO ".$db_name.".payments (Date, Type, CardSlip, Amount, SaveBy, SendBy, CouponUsedID, TimeRound) VALUES ('".$setDateSave."', 'Coupon', '0', '".floatval($realCouponUse[$price])."', ".$UserID.", ".intval($_REQUEST["CashierID"]).", ".intval($HistoryID).", ".intval($_POST["TimeRound"]).");";
                $rsInsertPayment=mysql_query($sqlInsertPayment);
                $LastPaymentID=mysql_insert_id($Conn);
                $NewPaymentList.=", ".$LastPaymentID;
                foreach($cusID[$price] as $CustID => $textSave) {
                    $sqlChk="select HistoryID from ".$db_name.".coupon_history where LockReason='".intval($HistoryID)."' and CustomerID=".intval($CustID).";";
                    $rsChk=mysql_query($sqlChk);
                    $ChkRecNum=mysql_num_rows($rsChk);
                    if(!intval($ChkRecNum)){
                        $sqlHistory="INSERT INTO ".$db_name.".coupon_history (EmpID, Total, CustomerID, ProcessDate, HistoryNote, ChangeNote, LockReason) VALUES (".intval($UserID).", '".floatval($cusIDUsed[$price][$CustID])."', ".intval($CustID).", '".mysql_real_escape_string(trim($DateCheck))."', 'ใช้คูปอง', '".substr($textSave, 4)."', '".intval($HistoryID)."');";
                        $rsHistory=mysql_query($sqlHistory);
                    }
                }
            }
        }
    }

    $sqlSelect="select CouponUsedID, PaymentID from (".$db_name.".payments inner join ".$db_name.".coupon_used on CouponUsedID=UsedID) where payments.Type='Coupon' and payments.TimeRound=".intval($_POST["TimeRound"])." and payments.SendBy=".intval($_REQUEST["CashierID"])." and payments.Date>=".$startDate." and payments.Date<=".$endDate." and payments.PaymentID in (".$OldPaymentList.") and payments.PaymentID not in (".$NewPaymentList.") group by CouponUsedID;";
    $rsSelect=mysql_query($sqlSelect);
    while($rowCouponUsedID=mysql_fetch_row($rsSelect)){
        $sqlConfirm1="delete from ".$db_name.".payments where PaymentID=".intval($rowCouponUsedID[1]).";";
        $rsConfirm1=mysql_query($sqlConfirm1);
        $sqlConfirm2="delete from ".$db_name.".coupon_used where UsedID=".intval($rowCouponUsedID[0]).";";
        $rsConfirm2=mysql_query($sqlConfirm2);
        $sqlConfirm3="delete from ".$db_name.".coupon_history where LockReason=".intval($rowCouponUsedID[0]).";";
        $rsConfirm3=mysql_query($sqlConfirm3);
    }
    $SuccessText='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>แก้ไขข้อมูลเรียบร้อยแล้ว.</div>';
}
else if(isset($_REQUEST["PaymentIDList"]) && $_REQUEST["RecordType"]==3 && $canSubmit){
    $NewPaymentList=0;
    $BillingList=0;
    $OldPaymentList=$_REQUEST["PaymentIDList"];
    $sqlCreditID="select CreditBilling, credit_billing.CustID, RealUsed, Status from (".$db_name.".payments inner join ".$db_name.".credit_billing on credit_billing.PaymentID=payments.PaymentID) where Type='Credit' and payments.PaymentID in (".$_REQUEST["PaymentIDList"].");";
    $rsCreditID=mysql_query($sqlCreditID);
    while($CreditID=mysql_fetch_row($rsCreditID)){
        if($CreditID[3]){
            $BillingList.=",".$CreditID[3];
        }
        $sqlUpdate="UPDATE ".$db_name.".customer SET UnofficialBalance=(UnofficialBalance+(".round($CreditID[2], 2).")) where CustID=".intval($CreditID[1]).";";
        $rsUpdate=mysql_query($sqlUpdate);
        $sqlUpdate="UPDATE ".$db_name.".credit_billing SET Amount='0.00', RealUsed='0.00', Date=0, SendUserID=0, OilID=0, OilPrice='0.00', CarID=0, Confirmed=0, Note='' where CreditBilling=".intval($CreditID[0]).";";
        $rsUpdate=mysql_query($sqlUpdate);
    }

    $AllIDList=array();
    $TotalUsed=array();
    $CredetCount=0;
    $SetDate=explode("/", $_REQUEST['oilPaidDate']);
    foreach($_POST['CreditCodeNo'] as $key => $value) {
        if(trim($value) && floatval($_POST['Amount'][$key])){
            $sqlAllID="select CreditBilling, CustID from ".$db_name.".credit_billing WHERE BookNo='".mysql_real_escape_string(trim($_POST['CreditBookNo'][$key]))."' and credit_billing.CodeNo=".intval($value).";";
            $rsAllID=mysql_query($sqlAllID);
            $AllIDNum=mysql_num_rows($rsAllID);
            $AllID=mysql_fetch_row($rsAllID);
            if(!isset($AllIDList[$_POST['OldPaymentID'][$key]])){
                $AllIDList[$_POST['OldPaymentID'][$key]]=0;
            }
            if(!intval($AllID[0])){
                $sqlInsert="INSERT INTO ".$db_name.".credit_billing (CustID, BookNo, CodeNo, Date) VALUES (".intval($_REQUEST['Cust4Credit'][$key]).", '".mysql_real_escape_string(trim($_POST['CreditBookNo'][$key]))."', ".intval($value).", ".intval($_REQUEST['OldDate'][$key]).");";
                $rsInsert=mysql_query($sqlInsert);
                $GetCreditID=mysql_insert_id($Conn);
                $AllIDList[$_POST['OldPaymentID'][$key]].=", ".$GetCreditID;
            }
            else{
                $_REQUEST['Cust4Credit'][$key]=$AllID[1];
                $AllIDList[$_POST['OldPaymentID'][$key]].=", ".$AllID[0];
            }
            $sqlCar="select CarID from ".$db_name.".customer_car where CarCode='".mysql_real_escape_string(trim($_POST['CreditCar'][$key]))."';";
            $rsCar=mysql_query($sqlCar);
            $row=mysql_fetch_row($rsCar);
            $GetCarID=$row[0];
            $SetNote="";
            if(!$GetCarID && trim($_POST["CreditCar"][$key])){
                if(!preg_match("#ใส่ถัง#", $_POST["CreditCar"][$key])){
                    $sqlCustomerCar="INSERT INTO ".$db_name.".customer_car (CustomerID, CarCode) VALUES (".intval($_REQUEST['Cust4Credit'][$key]).", '".mysql_real_escape_string(trim($_POST["CreditCar"][$key]))."');";
                    $rsCustomerCar=mysql_query($sqlCustomerCar);
                    $GetCarID=mysql_insert_id($Conn);
                }
                else{
                    $SetNote=$_POST["CreditCar"][$key];
                }
            }

            $_POST['Amount'][$key]=preg_replace("/,/", "", $_POST['Amount'][$key]);
            $sqlOil2="select oil_price.Prices from ".$db_name.".oil_price where OilID=".intval($_POST["CreditOilType"][$key])." and oil_price.RecordDate<='".$SetDate[2]."-".$SetDate[1]."-".$SetDate[0]."' order by RecordDate DESC, RecordTime DESC;";
            $rsOil2=mysql_query($sqlOil2);
            $Oil2=mysql_fetch_row($rsOil2);
            $sqlUpdate="UPDATE ".$db_name.".credit_billing SET Amount='".floatval($_POST['Amount'][$key])."', RealUsed='".floatval($_POST['Amount'][$key])."', Date=".intval($_REQUEST['OldDate'][$key]).", SendUserID=".intval($_REQUEST["CashierID"]).", OilID=".intval($_POST["CreditOilType"][$key]).", OilPrice='".$Oil2[0]."', CarID=".intval($GetCarID).", Confirmed=1, Note='".mysql_real_escape_string(trim($SetNote))."' WHERE BookNo='".mysql_real_escape_string(trim($_POST['CreditBookNo'][$key]))."' and credit_billing.CodeNo=".intval($value).";";
            $rsUpdate=mysql_query($sqlUpdate);

            if(!isset($TotalUsed[$_POST['OldPaymentID'][$key]])){
                $TotalUsed[intval($_POST['OldPaymentID'][$key])]=0;
            }
            $TotalUsed[intval($_POST['OldPaymentID'][$key])]+=round($_POST['Amount'][$key], 2);
            $CredetCount++;

            // update balance
            $sqlUpdate="update ".$db_name.".customer set UnofficialBalance=(UnofficialBalance-".floatval($_POST['Amount'][$key]).") where CustID=".intval($_REQUEST['Cust4Credit'][$key]).";";
            $rsUpdate=mysql_query($sqlUpdate);
        }
    }
    $PaymentIDListArr = explode(",", $_REQUEST["PaymentIDList"]);
    foreach($PaymentIDListArr as $key => $PaymentID) {
        $PaymentID=intval($PaymentID);
        if($canSubmit && isset($TotalUsed[$PaymentID]) && intval($TotalUsed[$PaymentID])){
            $sqlUpdate="UPDATE ".$db_name.".payments SET Amount='".floatval($TotalUsed[$PaymentID])."' where PaymentID='".intval($PaymentID)."';";
            $rsUpdate=mysql_query($sqlUpdate);
            $sqlUpdate="UPDATE ".$db_name.".credit_billing SET PaymentID=".intval($PaymentID)." WHERE credit_billing.CreditBilling in (".$AllIDList[$PaymentID].");";
            $rsUpdate=mysql_query($sqlUpdate);
        }
        else if(!isset($TotalUsed[$PaymentID]) || !intval($TotalUsed[$PaymentID])){
            $sqlConfirm1="delete from ".$db_name.".payments where PaymentID=".intval($PaymentID).";";
            $rsConfirm1=mysql_query($sqlConfirm1);
        }
    }
    $sqlCreditID="select sum(credit_billing.RealUsed), credit_billing.Status from ".$db_name.".credit_billing where credit_billing.Status in (".$BillingList.") group by credit_billing.Status;";
    $rsCreditID=mysql_query($sqlCreditID);
    while($SumCredit=mysql_fetch_row($rsCreditID)){
        $sqlUpdate="UPDATE ".$db_name.".billing_history SET Total=".floatval($SumCredit[0])." WHERE billing_history.ID=".intval($SumCredit[1])." and PaidDate=0;";
        $rsUpdate=mysql_query($sqlUpdate);
    }
    $SuccessText='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>แก้ไขข้อมูลเรียบร้อยแล้ว.</div>';
}

include("header.php");
if(!isset($_REQUEST["RecordType"])){
    $_REQUEST["RecordType"]=1;
}


$Time4In = array(1 => 7.30, 2 => 18.00, 3 => 4.00);
$Time4Out = array(1 => 18.00, 2 => 1.00, 3 => 7.30);
$TimeRec = date("H.i", time());
if(isset($_REQUEST["TimeRound"])){
    $MayBeRound=$_REQUEST["TimeRound"];
}
else if($TimeRec >= 7.3 && $TimeRec<=18){
    $MayBeRound=1;
    $_REQUEST["TimeRound"]=1;
}
else if($TimeRec >= 4 && $TimeRec<=7.3){
    $MayBeRound=3;
    $_REQUEST["TimeRound"]=3;
}
else{
    $MayBeRound=2;
    $_REQUEST["TimeRound"]=2;
}
$TimeRoundOption='<b>กะ:</b> <select id="TimeRound" name="TimeRound" class="form-control inline_input input-sm" style="width:50px;">
    <option value="1"';
if($MayBeRound==1){
    $TimeRoundOption.=" selected";
}
$TimeRoundOption.='>1</option>
    <option value="2"';
if($MayBeRound==2){
    $TimeRoundOption.=" selected";
}
$TimeRoundOption.='>2</option>
    <option value="3"';
if($MayBeRound==3){
    $TimeRoundOption.=" selected";
}
$TimeRoundOption.='>3</option></select>';
$EmpList="";
$sqlCashier="select concat(FirstName, ' ', LastName), EmpID from employee where Deleted=0 and Cashier=1 order by FirstName ASC, LastName ASC;";
$rsCashier=mysql_query($sqlCashier);
while($Cashier=mysql_fetch_row($rsCashier)){
    $EmpList.="<option value=\"".$Cashier[1]."\"";
    if(!isset($_REQUEST["CashierID"])){
        $_REQUEST["CashierID"]=$Cashier[1];
    }
    if(isset($_REQUEST["CashierID"]) && $Cashier[1]==$_REQUEST["CashierID"]){
        $EmpList.=" selected";
    }
    $EmpList.=">".$Cashier[0]."</option>";
}
$AllHidden='<input type="hidden" name="RecordType" value="'.$_REQUEST['RecordType'].'">
<input type="hidden" name="oilPaidDate" value="'.$_REQUEST['oilPaidDate'].'">
<input type="hidden" name="CashierID" value="'.$_REQUEST['CashierID'].'">
<input type="hidden" name="TimeRound" value="'.$_REQUEST['TimeRound'].'">
<input type="hidden" name="TimeForCheck" value="'.time().'">';
?>
     <section class="pageContent">
        <div class="title-body">
            <h2>รายการขายน้ำมัน</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;">
                        <form action="#" method="post" class="form-horizontal" role="form" name="record_info">
                        บันทึกรายการ โดย &nbsp; <select id="RecordType" name="RecordType" class="form-control inline_input input-sm" style="width:170px;" onchange="javascript:document.forms['record_info'].submit();">
                            <option value="1"<?php if($_REQUEST["RecordType"]==1){print(" selected");} ?>>คูปอง</option>
                            <option value="2"<?php if($_REQUEST["RecordType"]==2){print(" selected");} ?>>บัตรเครดิต/เงินสด</option>
                            <option value="3"<?php if($_REQUEST["RecordType"]==3){print(" selected");} ?>>ใบสั่งน้ำมัน</option>
                            </select>
                        </form>
                    </h3>
                </div>

                <div class="panel-body">
                    <?php
                    if($SuccessText){ print($SuccessText); }
                    if($_REQUEST["RecordType"]==1 && (!isset($_REQUEST["StepAction"]) || !$_REQUEST["StepAction"])){
                        // coupon
                    ?>
                    <form action="#" method="post" class="form-horizontal" role="form" name="all_copon_payment">
                        <input type="hidden" name="RecordType" value="1">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">รายการใช้คูปองประจำวันที่ : </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control Calendar" id="oilPaidDate" name="oilPaidDate" value="<?php print($_REQUEST['oilPaidDate']); ?>" style="display:inline; width:100px;">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <?php print($TimeRoundOption); ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <b>โดย:</b> <select id="CashierID" name="CashierID" class="form-control inline_input input-sm" style="width:170px;">
                                <?php print($EmpList); ?>
                                </select>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <button type="button" onclick="javascript:document.forms['all_copon_payment'].submit();" class="btn btn-xs btn-primary btn-rounder">GO</button>
                            </div>
                        </div>
                    </form>

                    <form name="EditPaymentForm" action="#" method="post" class="form-horizontal" role="form" autocomplete="off">
                    <table id="couponTable" class="td_center table table-condensed table-striped table-default car_table">
                        <thead>
                            <tr>
                                <th width="10%">เลขคูปอง</th>
                                <th width="10%">มูลค่าคูปอง</th>
                                <th width="35%">ชื่อบริษัท </th>
                                <th width="15%">เติมจริง</th>
                                <th>ส่วนต่าง</th>
                            </tr>
                        </thead>
                        <tbody id="couponForm">
                            <?php
                            $CustOption="";
                            $sqlCust="SELECT CustID, CustName from ".$db_name.".customer where CustID>0 and Deleted=0 and FromService not in (0, 3) order by CustName ASC;";
                            $rsCust=mysql_query($sqlCust);
                            while($CustInfo=mysql_fetch_row($rsCust)){
                                $CustOption.="<option value=\"".$CustInfo[0]."\">".$CustInfo[1]."</option>";
                            }
                            print("<select id=\"ShowCustOption\" style=\"display:none;\">".$CustOption."</select>");
                            $i=0;
                            $NotConfirmed=0;
                            $PaymentIDList=0;
                            $sqlCoupon="select CouponCode, Price, customer.CustName, RealUse, if(payments.Confirmed=0, 1, 0), payments.PaymentID from (((".$db_name.".payments inner join ".$db_name.".coupon on coupon.UseHistoryID=CouponUsedID) inner join ".$db_name.".customer on coupon.CustomerID=customer.CustID) inner join ".$db_name.".employee on employee.EmpID=payments.SendBy) where Type='Coupon' and RealUse>0 and TimeRound=".intval($_REQUEST["TimeRound"])." and SendBy=".intval($_REQUEST["CashierID"])." and payments.Date>=".$startDate." and payments.Date<=".$endDate." group by CouponCode order by Date ASC, TimeRound ASC, CouponCode ASC;";
                            $rsCoupon=mysql_query($sqlCoupon);
                            while($AllCoupon=mysql_fetch_row($rsCoupon)){
                                if(!preg_match("# ".$AllCoupon[5]." #", $PaymentIDList)){
                                    $PaymentIDList.=", ".$AllCoupon[5]." ";
                                }
                                $findDef=round($AllCoupon[1]-$AllCoupon[3], 2);
                                $StatusRec="";
                                if($findDef>0){
                                    $StatusRec=' style="color:blue;"';
                                }
                                else if($findDef<0){
                                    $StatusRec=' style="color:red;"';
                                }
                                $OnlyAdmin=0;
                                if(!$AllCoupon[4] && $PermissionNo<3){
                                    $OnlyAdmin=1;
                                }
                                if(isset($_REQUEST["DoAction"]) && $_REQUEST["DoAction"]==2 && !$OnlyAdmin){
                                    print('<tr id="coupon-'.$i.'">
                                        <td><input type="text" name="useCouponNo['.$i.']" id="useCouponNo-'.$i.'" class="form-control integer" value="'.$AllCoupon[0].'" style="width:200px; text-align:center;" onchange="javascript:checkCouponStatus(document.getElementById(\'useCouponPrice-'.$i.'\').value, this.value, '.$i.');"></td>
                                        <td>
                                            <select name="useCouponPrice['.$i.']" id="useCouponPrice-'.$i.'" class="form-control" onchange="javascript:checkCouponStatus(this.value, document.getElementById(\'useCouponNo-'.$i.'\').value, '.$i.');">
                                            <option value="0">เลือก</option>
                                            <option value="100"');
                                    if($AllCoupon[1]==100){ print(" selected"); }
                                    print('>100</option>
                                            <option value="300"');
                                    if($AllCoupon[1]==300){ print(" selected"); }
                                    print('>300</option>
                                            <option value="500"');
                                    if($AllCoupon[1]==500){ print(" selected"); }
                                    print('>500</option>
                                            <option value="1000"');
                                    if($AllCoupon[1]==1000){ print(" selected"); }
                                    print('>1,000</option>
                                            </select>
                                        </td>
                                        <td style="text-align:left;"><span id="DisplayName-'.$i.'">'.$AllCoupon[2].'</span></td>
                                        <td><input type="text" class="form-control price" name="couponRealUse['.$i.']" id="RealUse-'.$i.'" value="'.number_format($AllCoupon[3], 2).'" style="text-align:center;" onchange="javascript:findDifference('.$i.');"></td>
                                        <td id="DisplayDef-'.$i.'"'.$StatusRec.'>'.number_format($findDef, 2).'</td>
                                    </tr>');
                                }
                                else{
                                    print('<tr id="coupon-'.$i.'">
                                        <td>'.$AllCoupon[0].'<input type="hidden" name="useCouponNo['.$i.']" value="'.$AllCoupon[0].'"></td>
                                        <td>'.number_format($AllCoupon[1], 2).'<input type="hidden" name="useCouponPrice['.$i.']" value="'.round($AllCoupon[1], 2).'"></td>
                                        <td style="text-align:left;">'.$AllCoupon[2].'</td>
                                        <td>'.number_format($AllCoupon[3], 2).'<input type="hidden" name="couponRealUse['.$i.']" value="'.$AllCoupon[3].'"></td>
                                        <td'.$StatusRec.'>'.number_format($findDef, 2).'</td>
                                    </tr>');
                                }
                                $i++;
                                $NotConfirmed+=$AllCoupon[4];
                            }
                            if(!$i){
                                print('<tr>
                                    <td colspan="5"><p style="color:red; margin:20px auto 20px;">ไม่พบรายการตามเงื่อนไขที่กำหนด</p></td>
                                </tr>');
                            }
                            ?>
                        </tbody>
                    </table>
                    <input type="hidden" name="PaymentIDList" value="<?php print($PaymentIDList); ?>">
                    <?php print($AllHidden); ?>
                    </form>


                    <?php
                    }
                    else if($_REQUEST["RecordType"]==2){
                    ?>
                    <form action="#" method="post" class="form-horizontal" role="form" name="all_copon_payment">
                        <input type="hidden" name="RecordType" value="2">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">การขายโดย บัตรเครดิต/เงินสด ประจำวันที่ : </label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control Calendar" id="oilPaidDate" name="oilPaidDate" value="<?php print($_REQUEST['oilPaidDate']); ?>" style="display:inline; width:100px;">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <?php print($TimeRoundOption); ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <b>โดย:</b> <select id="CashierID" name="CashierID" class="form-control inline_input input-sm" style="width:170px;">
                                <?php print($EmpList); ?>
                                </select>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <button type="button" onclick="javascript:document.forms['all_copon_payment'].submit();" class="btn btn-xs btn-primary btn-rounder">GO</button>
                            </div>
                        </div>
                    </form>
                    <form name="EditPaymentForm" action="#" method="post" class="form-horizontal" role="form" autocomplete="off">
                    <table class="td_center table table-condensed table-striped table-default car_table">
                        <thead>
                            <tr>
                                <th width="10%">ลำดับที่</th>
                                <th>ประเภท</th>
                                <th>จำนวนสลิป</th>
                                <th>ยอดเงิน</th>
                            </tr>
                        </thead>
                        <tbody id="couponForm">
                            <?php
                            $i=0;
                            $NotConfirmed=0;
                            $PaymentIDList=0;
                            $TpyeName = array('Cash' => 'เงินสด', 'Card' => 'บัตรเครดิต');
                            $sqlCoupon="select Type, CardSlip, Amount, if(payments.Confirmed=0, 1, 0), PaymentID from (".$db_name.".payments inner join ".$db_name.".employee on employee.EmpID=payments.SendBy) where Type in ('Cash', 'Card') and TimeRound=".intval($_REQUEST["TimeRound"])." and SendBy=".intval($_REQUEST["CashierID"])." and payments.Date>=".$startDate." and payments.Date<=".$endDate." order by Date ASC, TimeRound ASC;";
                            $rsCoupon=mysql_query($sqlCoupon);
                            while($AllCoupon=mysql_fetch_row($rsCoupon)){
                                $i++;
                                $Radio1="";
                                $Radio2="";
                                if($AllCoupon[0]=='Cash'){
                                    $AllCoupon[1]="";
                                    $Radio1=" checked";
                                }
                                else{
                                    $Radio2=" checked";
                                }
                                $OnlyAdmin=0;
                                if(!$AllCoupon[3] && $PermissionNo<3){
                                    $OnlyAdmin=1;
                                }
                                if(isset($_REQUEST["DoAction"]) && $_REQUEST["DoAction"]==2 && !$OnlyAdmin){
                                    print('<tr id="coupon-'.$i.'">
                                        <td>'.$i.'</td>
                                        <td style="width:200px;"><input type="radio" class="form-control inline_input" name="PaymentType['.$AllCoupon[4].']" value="Cash"'.$Radio1.'>เงินสด &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" class="form-control inline_input" name="PaymentType['.$AllCoupon[4].']" value="Card"'.$Radio2.'>บัตรเครดิต</td>
                                        <td><input type="text" class="form-control integer" style="text-align:center;" name="CardSlip['.$AllCoupon[4].']" value="'.$AllCoupon[1].'"></td>
                                        <td><input type="text" class="form-control price" style="text-align:center;" name="Amount['.$AllCoupon[4].']" value="'.number_format($AllCoupon[2], 2).'"></td>
                                    </tr>');
                                }
                                else{
                                    print('<tr id="coupon-'.$i.'">
                                        <td>'.$i.'</td>
                                        <td style="text-align:left;">'.$TpyeName[$AllCoupon[0]].'</td>
                                        <td>'.$AllCoupon[1].'</td>
                                        <td>'.number_format($AllCoupon[2], 2).'</td>
                                    </tr>');
                                }
                                $NotConfirmed+=$AllCoupon[3];
                            }
                            if(!$i){
                                print('<tr>
                                    <td colspan="4"><p style="color:red; margin:20px auto 20px;">ไม่พบรายการตามเงื่อนไขที่กำหนด</p></td>
                                </tr>');
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php print($AllHidden); ?>
                    </form>


                    <?php
                    }
                    else if($_REQUEST["RecordType"]==3){ // customer credit
                    ?>
                    <form action="#" method="post" class="form-horizontal" role="form" name="all_copon_payment">
                        <input type="hidden" name="RecordType" value="3">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">การขายโดย ใบสั่งน้ำมัน ประจำวันที่ : </label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control Calendar" id="oilPaidDate" name="oilPaidDate" value="<?php print($_REQUEST['oilPaidDate']); ?>" style="display:inline; width:100px;">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <?php print($TimeRoundOption); ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <b>โดย:</b> <select id="CashierID" name="CashierID" class="form-control inline_input input-sm" style="width:170px;">
                                <?php print($EmpList); ?>
                                </select>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <button type="button" onclick="javascript:document.forms['all_copon_payment'].submit();" class="btn btn-xs btn-primary btn-rounder">GO</button>
                            </div>
                        </div>
                    </form>
                    <form name="EditPaymentForm" action="#" method="post" class="form-horizontal" role="form" autocomplete="off">
                    <?php
                        print('
                            <table id="creditTable" class="td_center table table-condensed table-striped table-default car_table">
                                <thead>
                                    <tr>
                                        <th width="100px" nowrap>รายการที่</th>
                                        <th width="12%">เล่มที่ใบสั่งน้ำมัน</th>
                                        <th width="12%">เลขที่ใบสั่งน้ำมัน</th>
                                        <th width="24%">ชื่อบริษัท</th>
                                        <th width="17%">ประเภทน้ำมัน</th>
                                        <th width="13%">ยอดเงิน</th>
                                        <th>ทะเบียนรถ</th>
                                    </tr>
                                </thead>');
                        $i=0;
                        $NotConfirmed=0;
                        $PaymentIDList=0;
                        $sqlCoupon="select BookNo, CodeNo, customer.CustName, oil.Name, RealUsed, Note, CarCode, if(payments.Confirmed=0, 1, 0), oil.OilID, payments.PaymentID, payments.Date, credit_billing.Status from (((((".$db_name.".payments inner join ".$db_name.".credit_billing on credit_billing.PaymentID=payments.PaymentID) inner join ".$db_name.".customer on credit_billing.CustID=customer.CustID) inner join ".$db_name.".employee on employee.EmpID=payments.SendBy) inner join ".$db_name.".oil on oil.OilID=credit_billing.OilID) left join ".$db_name.".customer_car on customer_car.CarID=credit_billing.CarID) where Type='Credit' and TimeRound=".intval($_REQUEST["TimeRound"])." and SendBy=".intval($_REQUEST["CashierID"])." and payments.Date>=".$startDate." and payments.Date<=".$endDate." order by payments.Date ASC, TimeRound ASC, customer.CustName, BookNo ASC, CodeNo ASC;";
                        $rsCoupon=mysql_query($sqlCoupon);
                        while($AllCoupon=mysql_fetch_row($rsCoupon)){
                            if(!preg_match("# ".$AllCoupon[9]." #", $PaymentIDList)){
                                $PaymentIDList.=", ".$AllCoupon[9]." ";
                            }
                            $i++;
                            if($AllCoupon[5]){
                                $AllCoupon[6]=$AllCoupon[5];
                            }
                            $NotConfirmed+=$AllCoupon[7];
                            $OnlyAdmin=0;
                            if(!$AllCoupon[7] && $PermissionNo<3){
                                $OnlyAdmin=1;
                            }
                            $sqlPaid="select PaidDate from ".$db_name.".billing_history where billing_history.ID=".intval($AllCoupon[11]).";";
                            $rsPaid=mysql_query($sqlPaid);
                            $Paid=mysql_fetch_row($rsPaid);
                            if(isset($_REQUEST["DoAction"]) && $_REQUEST["DoAction"]==2 && !$OnlyAdmin && !intval($Paid[0])){
                                print('
                                <tr id="credit-'.$i.'">
                                    <td>'.$i.'</td>
                                    <td><input type="text" name="CreditBookNo['.$i.']" id="PaidBookNo-'.$i.'" class="form-control" value="'.$AllCoupon[0].'"></td>
                                    <td><input type="text" name="CreditCodeNo['.$i.']" id="PaidCodeNo-'.$i.'" class="form-control credit_used" value="'.$AllCoupon[1].'" onchange="javascript:getBookNo(this.value, '.$i.');"><input type="hidden" name="OldPaymentID['.$i.']" value="'.$AllCoupon[9].'"><input type="hidden" name="OldDate['.$i.']" value="'.$AllCoupon[10].'"></td>
                                    <td style="text-align:left;"><span id="CompanyName-'.$i.'">'.$AllCoupon[2].'</span></td>
                                    <td>
                                        <select name="CreditOilType['.$i.']" id="CreditOilType-'.$i.'" class="form-control inline_input input-sm">');
                                $sqlOil="select oil.OilID, oil.Name from ".$db_name.".oil where Deleted=0 order by Name ASC;";
                                $rsOil=mysql_query($sqlOil);
                                while($Oil=mysql_fetch_row($rsOil)){
                                    print("<option value=\"".$Oil[0]."\"");
                                    if($AllCoupon[8]==$Oil[0]){
                                        print(" selected");
                                    }
                                    print(">".$Oil[1]."</option>");
                                }
                                print('</select>
                                    </td>
                                    <td><input type="text" name="Amount['.$i.']" id="Amount-'.$i.'" style="text-align:right;" class="form-control price" value="'.number_format($AllCoupon[4], 2).'"></td>
                                    <td><input type="text" name="CreditCar['.$i.']" id="CreditCar-'.$i.'" class="form-control" value="'.$AllCoupon[6].'"></td>
                                </tr>');
                            }
                            else{
                                print('<tr id="coupon-'.$i.'">
                                    <td>'.$i.'</td>
                                    <td style="text-align:left;">'.$AllCoupon[0].'<input type="hidden" name="CreditBookNo['.$i.']" value="'.$AllCoupon[0].'"></td>
                                    <td style="text-align:left;">'.$AllCoupon[1].'<input type="hidden" name="CreditCodeNo['.$i.']" value="'.$AllCoupon[1].'"><input type="hidden" name="OldPaymentID['.$i.']" value="'.$AllCoupon[9].'"><input type="hidden" name="OldDate['.$i.']" value="'.$AllCoupon[10].'"></td>
                                    <td style="text-align:left;">'.$AllCoupon[2].'</td>
                                    <td style="text-align:left;">'.$AllCoupon[3].'<input type="hidden" name="CreditOilType['.$i.']" value="'.$AllCoupon[8].'"></td>
                                    <td style="text-align:right;">'.number_format($AllCoupon[4], 2).'<input type="hidden" name="Amount['.$i.']" value="'.$AllCoupon[4].'"></td>
                                    <td style="text-align:left;">'.$AllCoupon[6].'<input type="hidden" name="CreditCar['.$i.']" value="'.$AllCoupon[6].'"></td>
                                </tr>');
                            }
                        }
                        if(!$i){
                            print('<tr>
                                <td colspan="7"><p style="color:red; margin:20px auto 20px;">ไม่พบรายการตามเงื่อนไขที่กำหนด</p></td>
                            </tr>');
                        }
                    }
                    ?>
                        </tbody>
                    </table>
                    <input type="hidden" name="PaymentIDList" value="<?php print($PaymentIDList); ?>">
                    <?php print($AllHidden); ?>
                    </form>
                    <br>
                    <div class="actionBar right">
                        <form name="PaymentAction" action="#" method="post">
                        <input type="hidden" id="deleteRec" name="deleteRec" value="">
                        <input type="hidden" id="backPage" name="backPage" value="record_info.php">
                        <?php
                        if(intval($i)){
                        ?>
                            <input type="hidden" name="DoAction" id="DoAction" value="">
                            <?php print($AllHidden); ?>
                            <?php
                            if(isset($_REQUEST["DoAction"]) && $_REQUEST["DoAction"]==2){
                                print('<button id="SavePayment" type="button" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp');
                            }
                            else if(($NotConfirmed && $PermissionNo>1) || $PermissionNo==3){
                                print('<button id="EditPayment" type="button" class="btn btn-info btn-rounder">แก้ไขข้อมูล</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                                if($NotConfirmed){
                                    print('<button id="ConfirmPayment" type="button" class="btn btn-success btn-rounder">ยืนยันข้อมูล</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                                }
                            }
                        }
                        if(isset($_REQUEST["DoAction"]) && $_REQUEST["DoAction"]==2){
                            print('<button type="button" class="btn btn-inverse btn-rounder" onclick="javascript:window.history.go(-1);">ย้อนกลับ</button>');
                        }else{
                            print('<button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>');
                        }
                        ?>
                        &nbsp;&nbsp;&nbsp;
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <button data-toggle="modal" data-target="#myModal" id="OpenDialog" style="visibility: hidden;"></button>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" name="byPassCoupon" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel1">ข้อมูลผิดพลาด</h4>
                </div>
                <div class="modal-body">
                    มีรายการคูปองที่ใช้งานไม่ได้อยู่ในรายการบันทึกการขาย<br>
                    ถ้าต้องการบันทึกให้ป้อนรหัสอนุมัติและคลิกยืนยัน<br>
                    หรือคลิกย้อนกลับเพื่อกลับไปแก้ไขรายการ<br><br>
                    รหัสอนุมัติ: <input type="password" class="noEnterSubmit" name="AccessCode" id="AccessCode" value="">
                    <button type="button" class="btn btn-success" id="CheckAccessCode">ยืนยันข้อมูล</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ย้อนกลับ</button>
                </div>
            </form>
        </div>
      </div>
    </div>

    <button data-toggle="modal" data-target="#myModal2" id="OpenDialog2" style="visibility: hidden;"></button>
    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" name="byPassCredit" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel2">ข้อมูลผิดพลาด</h4>
                </div>
                <div class="modal-body">
                    มีรายการเลขที่ใบสั่งน้ำมันที่ใช้งานไม่ได้อยู่ในรายการบันทึกการขาย<br>
                    ถ้าต้องการบันทึกให้ป้อนรหัสอนุมัติและคลิกยืนยัน<br>
                    หรือคลิกย้อนกลับเพื่อกลับไปแก้ไขรายการ<br><br>
                    รหัสอนุมัติ: <input type="password" class="noEnterSubmit" name="AccessCode2" id="AccessCode2" value="">
                    <button type="button" class="btn btn-success" id="CheckAccessCode2">ยืนยันข้อมูล</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ย้อนกลับ</button>
                </div>
            </form>
        </div>
      </div>
    </div>

    <button data-toggle="modal" data-target="#myModal1" id="OpenDialog1" style="visibility: hidden;"></button>
    <div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" name="errorByPass" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel3">ข้อมูลผิดพลาด</h4>
                </div>
                <div class="modal-body" id="warningError">
                    &nbsp;
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ย้อนกลับ</button>
                </div>
            </form>
        </div>
      </div>
    </div>
<?php
include("footer.php");
?>