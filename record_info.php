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


function getCustInfo($getCustInfo){
    global $db_name;
    $sqlCust="SELECT CustID from ".$db_name.".customer where CustName='".mysql_real_escape_string(trim($getCustInfo))."' and Deleted=0 order by CustName ASC;";
    $rsCust=mysql_query($sqlCust);
    $CustInfo=mysql_fetch_row($rsCust);
    print(intval($CustInfo[0]));
}

$canSubmit=0;
if(isset($_REQUEST["TimeForCheck"]) && (!isset($_COOKIE["TimeCheck"]) || $_COOKIE["TimeCheck"]!=$_REQUEST["TimeForCheck"])){
    setcookie("TimeCheck", $_REQUEST["TimeForCheck"], 0, "/");
    $_COOKIE["TimeCheck"]=$_REQUEST["TimeForCheck"];
    $canSubmit=1;
}

$SuccessText="";
$CouponError="";
if(isset($_POST["getCustInfo"])){
    getCustInfo($_POST["getCustInfo"]);
    exit();
}
else if(isset($_REQUEST["CheckAccessCode"])){
    if($_REQUEST["CheckAccessCode"] == $AccessOilPayment){
        print('true');
    }
    else{
        print('รหัสอนุมัติไม่ถูกต้อง');
    }
    exit();
}
else if(isset($_REQUEST["customer4coupon"])){
    $sqlUpdate="UPDATE ".$db_name.".coupon SET CustomerID=".intval($_REQUEST["customer4coupon"])." where coupon.ID=".intval($_POST["IDUpdate"]).";";
    $rsUpdate=mysql_query($sqlUpdate);
    exit();
}
else if(isset($_REQUEST["SendBookCode"])){
    $sqlUse="SELECT BookNo, CreditLock, CustName, Confirmed, CodeNo, credit_billing.CustID from (".$db_name.".credit_billing left join ".$db_name.".customer on customer.CustID=credit_billing.CustID) where CodeNo=".intval($_REQUEST["SendBookCode"]).";";
    $rsUse=mysql_query($sqlUse);
    $UseTotal=mysql_fetch_row($rsUse);
    print($UseTotal[0]);
    print("-".intval($UseTotal[3]));
    print("-".intval($UseTotal[1])."-".$UseTotal[2]."-".$UseTotal[4]."-".$UseTotal[5]);
    exit();
}
else if(isset($_REQUEST["CheckCoupon"])){
    $ErrorList  = array(0 => '', 2 => '', 3 => '', 4 => '', 5 => '');
    $sqlCoupon="SELECT Status, CustName from ".$db_name.".coupon left join ".$db_name.".customer on coupon.CustomerID=customer.CustID where Price='".intval($_REQUEST["CouponPrice"])."' and CouponCode=".intval($_REQUEST["CheckCoupon"])." order by BookNo ASC, CouponCode ASC;";
    $rsCoupon=mysql_query($sqlCoupon);
    $CouponInfo=mysql_fetch_row($rsCoupon);
    print(intval($CouponInfo[0])."~".$CouponInfo[1]);
    exit();
}
else if(isset($_POST["TotalCash"]) && (intval($_POST["TotalCash"]) || intval($_POST["TotalCredit"]))){
    $SetDate=explode("/", $_REQUEST['oilPaidDate']);
    $setDateSave=mktime($_POST["RecordH"], $_POST["RecordM"], $_POST["RecordS"], $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    if($canSubmit && intval($_POST["TotalCash"])){
        $_POST["TotalCash"]=preg_replace("/,/", "", $_POST["TotalCash"]);
        $sqlInsertPayment="INSERT INTO ".$db_name.".payments (Date, Type, CardSlip, Amount, SaveBy, SendBy, TimeRound) VALUES ('".$setDateSave."', 'Cash', '0', '".floatval($_POST["TotalCash"])."', ".$UserID.", ".intval($_REQUEST['CashierID']).", ".intval($_POST["TimeRound"]).");";
        $rsInsertPayment=mysql_query($sqlInsertPayment);
    }
    if($canSubmit && intval($_POST["TotalCredit"])){
        $_POST["TotalSlip"]=preg_replace("/,/", "", $_POST["TotalSlip"]);
        $_POST["TotalCredit"]=preg_replace("/,/", "", $_POST["TotalCredit"]);
        $sqlInsertPayment="INSERT INTO ".$db_name.".payments (Date, Type, CardSlip, Amount, SaveBy, SendBy, TimeRound) VALUES ('".$setDateSave."', 'Card', ".intval($_POST["TotalSlip"]).", '".floatval($_POST["TotalCredit"])."', ".$UserID.", ".intval($_REQUEST['CashierID']).", ".intval($_POST["TimeRound"]).");";
        $rsInsertPayment=mysql_query($sqlInsertPayment);
    }
    $SuccessText='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลรายการรับจาก เงินสด/บัตรเครดิตเรียบร้อยแล้ว.</div>';
}
else if(isset($_POST["UpdateCredit"]) && $_REQUEST['StepAction']==1){
    $SetDate=explode("/", $_REQUEST['oilPaidDate']);
    $setDateSave=mktime($_POST["RecordH"], $_POST["RecordM"], $_POST["RecordS"], $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    $sqlCreditChk="select count(*) from ".$db_name.".payments where Date=".$setDateSave." and Type='Credit' and SendBy=".intval($_REQUEST["CashierID"])." and SaveBy=".$UserID." and TimeRound=".intval($_POST["TimeRound"]).";";
    $rsCreditChk=mysql_query($sqlCreditChk);
    $CreditChk=mysql_fetch_row($rsCreditChk);
    if($canSubmit && !intval($CreditChk[0])){
        $AllIDList="0";
        $AllCodeID="0";
        $TotalUsed=0;
        $CredetCount=0;
        foreach($_POST['CreditCodeNo'] as $key => $value) {
            if(trim($value) && floatval($_POST['Amount'][$key]) && !preg_match("/".$value."/", $AllCodeID)){
                $AllCodeID.=",".intval($value);
                $sqlAllID="select CreditBilling, CustID from ".$db_name.".credit_billing WHERE BookNo='".mysql_real_escape_string(trim($_POST['CreditBookNo'][$key]))."' and credit_billing.CodeNo=".intval($value).";";
                $rsAllID=mysql_query($sqlAllID);
                $AllIDNum=mysql_num_rows($rsAllID);
                $AllID=mysql_fetch_row($rsAllID);
                if(!$AllIDNum){
                    $sqlInsert="INSERT INTO ".$db_name.".credit_billing (CustID, BookNo, CodeNo, Date) VALUES (".intval($_REQUEST['Cust4Credit'][$key]).", '".mysql_real_escape_string(trim($_POST['CreditBookNo'][$key]))."', ".intval($value).", ".intval($setDateSave).");";
                    $rsInsert=mysql_query($sqlInsert);
                    $GetCreditID=mysql_insert_id($Conn);
                    $AllIDList.=", ".$GetCreditID;
                }
                else{
                    $AllIDList.=", ".$AllID[0];
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
                $sqlUpdate="UPDATE ".$db_name.".credit_billing SET Amount='".floatval($_POST['Amount'][$key])."', RealUsed='".floatval($_POST['Amount'][$key])."', Date=".intval($setDateSave).", SendUserID=".intval($_REQUEST["CashierID"]).", OilID=".intval($_POST["CreditOilType"][$key]).", OilPrice='".$Oil2[0]."', CarID=".intval($GetCarID).", Confirmed=1, Note='".mysql_real_escape_string(trim($SetNote))."' WHERE BookNo='".mysql_real_escape_string(trim($_POST['CreditBookNo'][$key]))."' and credit_billing.CodeNo=".intval($value).";";
                $rsUpdate=mysql_query($sqlUpdate);

                $TotalUsed+=round($_POST['Amount'][$key], 2);
                $CredetCount++;

                // update balance
                $sqlUpdate="update ".$db_name.".customer set UnofficialBalance=(UnofficialBalance-".floatval($_POST['Amount'][$key]).") where CustID=".intval($_REQUEST['Cust4Credit'][$key]).";";
                $rsUpdate=mysql_query($sqlUpdate);
            }
        }
        //$sqlCreditChk="select count(*) from ".$db_name.".payments where Date=".$setDateSave." and Type='Credit' and CardSlip=".intval($CredetCount)." and Amount='".floatval($TotalUsed)."' and SendBy=".intval($_REQUEST["CashierID"])." and TimeRound=".intval($_POST["TimeRound"]).";";
        if($canSubmit && !intval($CreditChk[0])){
            $sqlInsertPayment="INSERT INTO ".$db_name.".payments (Date, Type, CardSlip, Amount, SaveBy, SendBy, TimeRound) VALUES ('".$setDateSave."', 'Credit', ".intval($CredetCount).", '".floatval($TotalUsed)."', ".$UserID.", ".intval($_REQUEST["CashierID"]).", ".intval($_POST["TimeRound"]).");";
            $rsInsertPayment=mysql_query($sqlInsertPayment);
            $PaymentID=mysql_insert_id($Conn);
            $sqlUpdate="UPDATE ".$db_name.".credit_billing SET PaymentID=".intval($PaymentID)." WHERE credit_billing.CreditBilling in (".$AllIDList.") or CodeNo in (".$AllCodeID.");";
            $rsUpdate=mysql_query($sqlUpdate);
        }
        if($TotalUsed>0){
            $SuccessText='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลรายการใบสั่งน้ำมัน ประจำวันที่ '.$_REQUEST['oilPaidDate'].' เรียบร้อยแล้ว.</div>';
        }
    }
    $sqlDup="select payments.PaymentID from ".$db_name.".payments left join ".$db_name.".credit_billing on credit_billing.PaymentID=payments.PaymentID where credit_billing.PaymentID is Null and payments.Type='Credit' and SaveBy=".$UserID." and SendBy=".intval($_REQUEST["CashierID"]).";";
    $rsDup=mysql_query($sqlDup);
    while($Duplicate=mysql_fetch_row($rsDup)){
        $sqlDup2="select credit_billing.PaymentID from ".$db_name.".credit_billing where credit_billing.PaymentID=".$Duplicate[0].";";
        $rsDup2=mysql_query($sqlDup2);
        if(!mysql_num_rows($rsDup2)){
            $sqlDelDup="delete from ".$db_name.".payments where payments.PaymentID=".intval($Duplicate[0])." and payments.Type='Credit';";
            $rsDelDup=mysql_query($sqlDelDup);
        }
    }
    unset($_REQUEST['StepAction']);
}
else if(isset($_POST["useCouponNo"]) && $canSubmit){
    $couponList = array();
    $couponCount = array();
    asort($_POST["useCouponNo"]);
    $couponusedList=""; // price*couponcode-price*couponcode-...
    foreach($_POST["useCouponNo"] as $key => $value) {
        $_POST["couponRealUse"][$key]=preg_replace("/,/", "", $_POST["couponRealUse"][$key]);
        $couponForCheck=$_POST["couponRealUse"][$key]."~".trim($value);
        if(trim($value) && !preg_match("/".$couponForCheck."/", $couponusedList)){
            $couponusedList.=", ".$couponForCheck;
            $FindDiff = $_POST["useCouponPrice"][$key]- $_POST["couponRealUse"][$key];
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
    $setDateSave=mktime($_POST["RecordH"], $_POST["RecordM"], $_POST["RecordS"], $setDateArr[1], intval($setDateArr[0]), $setDateArr[2]);
    $setDateText = $setDateArr[0].' '.$monthList[($setDateArr[1]-1)].' '.$setDateArr[2];
    $DateCheck = $setDateArr[2].'-'.$setDateArr[1].'-'.$setDateArr[0];
    foreach($couponList as $price => $value) {
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
    $SuccessText='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลการใช้คูปองประจำวันที่ '.$setDateText.' เรียบร้อยแล้ว.</div>';
}
if(isset($_POST["useCouponNo"])){
    unset($_REQUEST["StepAction"]);
}


include("header.php");
$AddNote='';
$EditRecrord=0;
if(!isset($_REQUEST["RecordType"])){
    $_REQUEST["RecordType"]=1;
}
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

$OilOption="";
$OliNameArr = array();
$sqlOil="select oil.OilID, oil.Name from ".$db_name.".oil where Deleted=0 order by Name ASC;";
$rsOil=mysql_query($sqlOil);
while($Oil=mysql_fetch_row($rsOil)){
    $OilOption.="<option value=\"".$Oil[0]."\">".$Oil[1]."</option>";
    $OliNameArr[$Oil[0]]=$Oil[1];
}

if($_REQUEST["RecordType"]==3){
    if(!isset($_REQUEST['oilPaidDate'])){
        $_REQUEST['oilPaidDate']=date("d/m/Y", time());
    }
    $SetDate=explode("/", $_REQUEST['oilPaidDate']);
    $BookArr=array();
    $StatusRec=array();
    $CodeArr=array();
    $AmountArr=array();
    $CarArr=array();
    $OilChooseArr=array();
    $startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    $endDate=mktime(23, 59, 59, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    $sqlConfirmed="SELECT Confirmed, BookNo, CodeNo, Amount, credit_billing.Date, CreditLock, OilID, CarCode FROM ((".$db_name.".credit_billing left join ".$db_name.".customer on customer.CustID=credit_billing.CustID) left join ".$db_name.".customer_car on credit_billing.CarID=customer_car.CarID) WHERE Date>=".$startDate." and Date<=".$endDate." and Confirmed=0 and SendUserID=".intval($_REQUEST["CashierID"])." order by CustName ASC, Amount, BookNo ASC, CodeNo ASC;";
    $rsConfirmed=mysql_query($sqlConfirmed);
    $disabledUPD=0;
    $count=0;
    while($Confirmed=mysql_fetch_row($rsConfirmed)){
        $count++;
        $BookArr[$count]=$Confirmed[1];
        $CodeArr[$count]=$Confirmed[2];
        $AmountArr[$count]=$Confirmed[3];
        $OilChooseArr[$count]=$Confirmed[6];
        $CarArr[$count]=$Confirmed[7];
        $OldDate=$Confirmed[4];
        if($Confirmed[0]){
            $StatusRec[$count]=' class="show_defference2"';
        }
        else if($Confirmed[5]){
            $StatusRec[$count]=' class="show_defference3"';
        }
    }
}

$Time4In = array(1 => 7.30, 2 => 18.00, 3 => 4.00);
$Time4Out = array(1 => 18.00, 2 => 1.00, 3 => 7.30);
$TimeRec = date("H.i", time());
if($TimeRec >= 7.3 && $TimeRec<=18){
    $MayBeRound=1;
}
else if($TimeRec >= 4 && $TimeRec<=7.3){
    $MayBeRound=3;
}
else{
    $MayBeRound=2;
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
$TimeRoundOption.='>3</option>
</select>';
?>
     <section class="pageContent">
        <div class="title-body">
            <h2>บันทึกรายการขายน้ำมัน</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;">
                        <form action="record_info.php" method="post" class="form-horizontal" role="form" name="record_info">
                        บันทึกรายการ โดย &nbsp; <select id="RecordType" name="RecordType" class="form-control inline_input input-sm" style="width:170px;" onchange="javascript:document.forms['record_info'].submit();">
                            <option value="1"<?php if($_REQUEST["RecordType"]==1){print(" selected");} ?>>คูปอง</option>
                            <option value="2"<?php if($_REQUEST["RecordType"]==2){print(" selected");} ?>>บัตรเครดิต/เงินสด</option>
                            <option value="3"<?php if($_REQUEST["RecordType"]==3){print(" selected");} ?>>ใบสั่งน้ำมัน</option>
                            </select>
                        <div style="float:right;"><a href="payment_report.php">ตรวจสอบรายการ</a></div>
                        </form>
                    </h3>
                </div>

                <div class="panel-body">
                    <form id="paymentRecord" name="paymentRecord" method="post" class="form-horizontal" role="form" autocomplete="off">
                    <input type="hidden" name="TimeForCheck" value="<?php print(time()); ?>">
                    <input type="hidden" name="RecordH" value="<?php print(date('H', time())); ?>">
                    <input type="hidden" name="RecordM" value="<?php print(date('i', time())); ?>">
                    <input type="hidden" name="RecordS" value="<?php print(date('s', time())); ?>">
                    <?php
                    if($CouponError && !isset($_REQUEST['rec'])){
                        print('
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$CouponError.'
                        </div>');
                    }else if($SuccessText){ print($SuccessText); }

                    if($_REQUEST["RecordType"]==1 && (!isset($_REQUEST["StepAction"]) || !$_REQUEST["StepAction"])){
                        $CouponFirstStep=1;
                        if(!isset($_REQUEST['oilPaidDate'])){
                            $_REQUEST['oilPaidDate']=date("d/m/Y", time());
                        }
                        $CustOption="";
                        $sqlCust="SELECT CustID, CustName from ".$db_name.".customer where CustID>0 and Deleted=0 and FromService not in (0, 3) order by CustName ASC;";
                        $rsCust=mysql_query($sqlCust);
                        while($CustInfo=mysql_fetch_row($rsCust)){
                            $CustOption.="<option value=\"".$CustInfo[0]."\">".$CustInfo[1]."</option>";
                        }
                        print("<select id=\"ShowCustOption\" style=\"display:none;\">".$CustOption."</select>");

                    ?>
                    <input type="hidden" name="RecordType" id="RecordType" value="1">
                    <input type="hidden" name="StepAction" value="1">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">บันทึกรายการใช้คูปองประจำวันที่ : </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control Calendar" id="oilPaidDate" name="oilPaidDate" value="<?php print($_REQUEST['oilPaidDate']); ?>" style="display:inline; width:100px;">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <?php print($TimeRoundOption); ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <b>โดย:</b> <select id="CashierID" name="CashierID" class="form-control inline_input input-sm" style="width:170px;">
                            <?php print($EmpList); ?>
                            </select>
                        </div>
                    </div>

                    <table id="couponTable" class="td_center table table-condensed table-striped table-default car_table">
                        <thead>
                            <tr>
                                <th width="10%">เลขคูปอง &nbsp;&nbsp;<i onclick="javascript:addCouponPaid();" class="pointer fa fa-plus-square"></i></th>
                                <th width="10%">มูลค่าคูปอง</th>
                                <th width="35%">ชื่อบริษัท </th>
                                <th width="15%">เติมจริง</th>
                                <th>ส่วนต่าง</th>
                            </tr>
                        </thead>
                        <tbody id="couponForm">
                            <?php
                            for($i=1; $i<11; $i++){
                                print('<tr id="coupon-'.$i.'">
                                <td><input type="text" name="useCouponNo['.$i.']" id="useCouponNo-'.$i.'" class="form-control integer" value="" style="width:200px;" onchange="javascript:checkCouponStatus(document.getElementById(\'useCouponPrice-'.$i.'\').value, this.value, '.$i.');"></td>
                                <td>
                                    <select name="useCouponPrice['.$i.']" id="useCouponPrice-'.$i.'" class="form-control" onchange="javascript:checkCouponStatus(this.value, document.getElementById(\'useCouponNo-'.$i.'\').value, '.$i.');">
                                    <option value="0">เลือก</option>
                                    <option value="100">100</option>
                                    <option value="300">300</option>
                                    <option value="500">500</option>
                                    <option value="1000">1,000</option>
                                    </select>
                                </td>
                                <td style="text-align:left;"><span id="DisplayName-'.$i.'"></span></td>
                                <td><input type="text" class="form-control price" name="couponRealUse['.$i.']" id="RealUse-'.$i.'" value="" style="text-align:right;" onchange="javascript:findDifference('.$i.');"></td>
                                <td id="DisplayDef-'.$i.'">0.00</td>
                            </tr>');
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php
                    }
                    else if($_REQUEST["RecordType"]==2){
                        if(!isset($_REQUEST['oilPaidDate']) || !trim($_REQUEST['oilPaidDate'])){
                            $_REQUEST['oilPaidDate']=date("d/m/Y", time());
                        }

                        $SetDate=explode("/", $_REQUEST['oilPaidDate']);
                        $startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
                        $endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
                        $sqlCheck="select Amount from ".$db_name.".payments where Type='Cash' and Date>=".$startDate." and Date<=".$endDate.";";
                        $rsCheck=mysql_query($sqlCheck);
                        $CashNum=mysql_num_rows($rsCheck);
                        $Cash=mysql_fetch_row($rsCheck);
                        $CashBox='<input type="text" class="form-control price" name="TotalCash" value="" style="width:150px;">';
                        // if($CashNum){
                        //     $CashBox='<p style="margin-top:8px;">'.number_format($Cash[0], 2)." บาท</p>";
                        // }

                        $sqlCheck="select Amount, CardSlip from ".$db_name.".payments where Type='Card' and Date>=".$startDate." and Date<=".$endDate.";";
                        $rsCheck=mysql_query($sqlCheck);
                        $CreditNum=mysql_num_rows($rsCheck);
                        $Credit=mysql_fetch_row($rsCheck);
                        $CardBox='<input type="text" class="form-control inline_input price" name="TotalCredit" value="" style="width:150px;">';
                        $SlipBox='<input type="text" class="form-control inline_input integer" name="TotalSlip" value="" style="width:70px;">';
                        // if($CreditNum){
                        //     $CardBox='<p style="margin-top:8px;">'.number_format($Credit[0], 2)." บาท</p>";
                        //     $SlipBox='<p style="margin-top:8px;">'.number_format($Credit[1], 2)." ใบ</p>";
                        // }
                        print('
                        <input type="hidden" name="RecordType" id="RecordType" value="2">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">บันทึกรายการรับประจำวันที่: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control Calendar" name="oilPaidDate" value="'.$_REQUEST['oilPaidDate'].'" style="display:inline; width:100px;">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                '.$TimeRoundOption.'
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <b>จาก:</b> <select id="CashierID" name="CashierID" class="form-control inline_input input-sm" style="width:170px;">
                                '.$EmpList.'
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">บันทึกรับจากเงินสด</label>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">ยอดเงิน: </label>
                            <div class="col-sm-4">
                                '.$CashBox.'
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">บันทึกรับจากบัตรเครดิต</label>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">จำนวนสลิป: </label>
                            <div class="col-sm-4">
                                '.$SlipBox.'
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">ยอดเงิน: </label>
                            <div class="col-sm-4">
                                '.$CardBox.'
                            </div>
                        </div>');
                        $_REQUEST["StepAction"]=2;
                    }
                    else if($_REQUEST["RecordType"]==3 && (!isset($_REQUEST["StepAction"]) || !$_REQUEST["StepAction"])){ // customer credit
                        $SaveButton='';
                        $MaxRow=10;
                        $CreditFirstStep=1;
                        $CustOption="";
                        $sqlCust="SELECT CustID, CustName from ".$db_name.".customer where CustID>0 and Deleted=0 and (FromService=0 or FromService=3) order by CustName ASC;";
                        $rsCust=mysql_query($sqlCust);
                        while($CustInfo=mysql_fetch_row($rsCust)){
                            $CustOption.="<option value=\"".$CustInfo[0]."\">".$CustInfo[1]."</option>";
                        }
                        print("<select id=\"ShowCustOption\" style=\"display:none;\">".$CustOption."</select>");
                        if(isset($count) && $count){
                            if($count>10){
                                $MaxRow=$count;
                            }
                            print('<input type="hidden" name="OldDate" value="'.intval($OldDate).'">');
                        }
                        print('
                            <input type="hidden" name="RecordType" id="RecordType" value="3">
                            <input type="hidden" name="StepAction" value="1">
                            <input type="hidden" name="UpdateCredit" value="1">

                            <div class="form-group">
                                <label class="col-sm-3 control-label">บันทึกรายการรับประจำวันที่: </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control Calendar" id="oilPaidDate" name="oilPaidDate" value="'.$_REQUEST['oilPaidDate'].'" style="display:inline; width:100px;">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    '.$TimeRoundOption.'
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <b>จาก:</b> <select id="CashierID" name="CashierID" class="form-control inline_input input-sm" style="width:170px;">
                                    '.$EmpList.'
                                    </select>
                                </div>
                            </div>

                            <table id="creditTable" class="td_center table table-condensed table-striped table-default car_table">
                                <thead>
                                    <tr>
                                        <th width="100px" nowrap>รายการที่ &nbsp;&nbsp;<i id="addCreditPaid" class="pointer fa fa-plus-square"></i></th>
                                        <th width="12%">เล่มที่ใบสั่งน้ำมัน</th>
                                        <th width="12%">เลขที่ใบสั่งน้ำมัน</th>
                                        <th width="24%">ชื่อบริษัท</th>
                                        <th width="17%">ประเภทน้ำมัน</th>
                                        <th width="13%">ยอดเงิน</th>
                                        <th>ทะเบียนรถ</th>
                                    </tr>
                                </thead>');
                        for($i=1; $i<=$MaxRow; $i++){
                            if(!isset($BookArr[$i])){
                                $BookArr[$i]='';
                                $CodeArr[$i]='';
                                $CarArr[$i]='';
                                $AmountArr[$i]='';
                                $StatusRec[$i]='';
                                $OilChooseArr[$i]='';
                            }
                            $PrintOilOption="";
                            foreach ($OliNameArr as $key => $value) {
                                $PrintOilOption.="<option value=\"".$key."\"";
                                if($key==$OilChooseArr[$i]){
                                    $PrintOilOption.=" selected";
                                }
                                $PrintOilOption.=">".$value."</option>";
                            }
                            print('
                            <tr id="credit-'.$i.'"'.$StatusRec[$i].'>
                                <td>'.$i.'</td>
                                <td><input type="text" name="CreditBookNo['.$i.']" id="PaidBookNo-'.$i.'" class="form-control" value="'.$BookArr[$i].'"></td>
                                <td><input type="text" name="CreditCodeNo['.$i.']" id="PaidCodeNo-'.$i.'" class="form-control credit_used" value="'.$CodeArr[$i].'" onchange="javascript:getBookNo(this.value, '.$i.');"></td>
                                <td style="text-align:left;"><span id="CompanyName-'.$i.'"></span></td>
                                <td>
                                    <select name="CreditOilType['.$i.']" id="CreditOilType-'.$i.'" class="form-control inline_input input-sm">
                                    '.$PrintOilOption.'
                                    </select>
                                </td>
                                <td><input type="text" name="Amount['.$i.']" id="Amount-'.$i.'" class="form-control price" value="'.$AmountArr[$i].'"></td>
                                <td><input type="text" name="CreditCar['.$i.']" id="CreditCar-'.$i.'" class="form-control" value="'.$CarArr[$i].'"></td>
                            </tr>');
                            }
                        print('</table>');
                        print("<br>สัญลักษณ์เตือนตามสถานะดังนี้: <span class=\"nocode\">ไม่มีในระบบ</span>
                            <span class=\"used\">ถูกใช้งานไปแล้ว</span>
                            <span class=\"locked\">เครดิตถูกล็อค</span>");
                    }
                    ?>
                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="deleteRec" name="deleteRec" value="">
                            <?php
                            if($EditRecrord){
                                if(!isset($_REQUEST['back'])){
                                    $_REQUEST['back']='index';
                                }
                                print('<input type="hidden" name="EditRec" value="'.$_REQUEST['rec'].'"><input type="hidden" id="backPage" name="backPage" value="oil_record.php?payDate='.$_POST["Date"].'&back='.$_REQUEST['back'].'">');
                                $Delete='<button type="button" class="btn btn-trash-o btn-rounder" onclick="if(confirm(\'คุณต้องการลบรายการนี้ ?\')){ document.getElementById(\'deleteRec\').value='.$_REQUEST['rec'].'; document.forms[\'paymentRecord\'].submit(); }else{return false;}">ลบรายการ</button>&nbsp;&nbsp;&nbsp;';
                            }
                            else{
                                print('<input type="hidden" id="backPage" name="backPage" value="index.php">');
                                $Delete='';
                            }
                            ?>
                            <!-- <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp; -->
                            <?php
                            //print('<button class="btn btn-success btn-rounder" onclick="javascript:history.back(-1);">ย้อนกลับ</button>');
                            if(!isset($disabledUPD) || !$disabledUPD){
                                if(isset($_REQUEST["StepAction"]) && ($_REQUEST["StepAction"]==2)){
                                    print('<button type="submit" class="btn btn-success btn-rounder">ยืนยันข้อมูล</button>');
                                }
                                else{
                                    print('<button type="button" class="btn btn-success btn-rounder" onclick="javascript:if(document.getElementById(\'oilPaidDate\') && document.getElementById(\'oilPaidDate\').value==\'\'){ alert(\'กรุณาระบุวันที่\'); return false; }else if(document.getElementById(\'CashierID\') && document.getElementById(\'CashierID\').value==\'0\'){ alert(\'กรุณาระบุพนักงานเก็บเงิน\'); return false; }');
                                    if(isset($CouponFirstStep)){
                                        print('else{ var checkVal=checkAllError(); return false; }">');
                                    }
                                    else if(isset($CreditFirstStep)){
                                        print('else{ var checkVal=checCreditError(); return false; }">');
                                    }
                                    else{
                                        print('else{ return true; }">');
                                        }
                                    print('บันทึกรายการ</button>');
                                }
                            }
                            ?>
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </form>
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

    <input type="hidden" id="WarningNow" value="<?php print($WarningOilTrigger); ?>">
    <input type="hidden" id="Date2Warning" value="<?php print($Date2Check); ?>">
    <button data-toggle="modal" data-target="#WarningOil" id="OpenWarningOil" style="visibility: hidden;"></button>
    <div class="modal fade" id="WarningOil" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form action="record_info.php" method="post" class="form-horizontal" autocomplete="off">
                <div class="modal-body text-center">
                    <br><p><b>ราคาน้ำมันของวันนี้ยังไม่ถูกอัพเดท</b></p><br>
                    <button type="button" class="btn btn-success" onclick="javascript:updatePrice();">อัพเดทราคาน้ำมัน</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="closeThisBox" onclick="javascript:setPriceWarning();">ยังไม่อัพเดทตอนนี้</button>
                    <br>&nbsp;
                </div>
            </form>
        </div>
      </div>
    </div>

<?php
print('<select id="OilOptionList" style="display:none;">'.$OilOption.'</select>');
include("footer.php");
?>