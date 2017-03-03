<?php
include("dbvars.inc.php");

if($SectionNum==1 && $PermissionNo==1 && $onlyAccess==4){ // เข้าใช้ได้แต่ส่วนคูปอง (normal user)
    header('location: coupon_check.php');
    exit();
}

if(!preg_match('/-4-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

function getCustInfo($getCustInfo, $FromAjax, $TotalSell){
    global $db_name, $Info;
    $sqlCust="SELECT Address1, Address2, Tel, TaxCode, CustID, CheckCarCode, CouponBalance, Address3, Address4 from ".$db_name.".customer where CustName='".mysql_real_escape_string(trim($getCustInfo))."' and Deleted=0 order by CustName ASC;";
    $rsCust=mysql_query($sqlCust);
    $CustNum=mysql_num_rows($rsCust);
    $CustInfo=mysql_fetch_row($rsCust);
    $NetTotal=($TotalSell-$CustInfo[6]);
    if($CustInfo[6]>0){
        $NetTotalStr="มียอดคงเหลือการจากใช้งานครั้งก่อน";
    }
    else if($CustInfo[6]<0){
        $NetTotalStr="มูลค่าที่ต้องจ่ายเพิ่มจากยอดการใช้งานครั้งก่อน";
    }
    if($NetTotalStr){
        print('
    <div class="form-group">
        <div class="col-sm-2">&nbsp;</div>
        <div class="col-sm-5" style="color:red;">
            <input type="hidden" name="UseOldBalance" id="UseOldBalance" value="'.$CustInfo[6].'">
            '.$NetTotalStr." ".number_format(abs($CustInfo[6]), 2).' บาท
        </div>
    </div>
    <div id="NetTotal" class="form-group">
        <label class="col-sm-2 control-label">ยอดชำระรวม:</label>
        <div class="col-sm-5" style="margin-top:7px; color:blue;text-decoration:underline;">'.number_format($NetTotal, 2).' บาท</div>
    </div>');
    }
    $Fix4Code=0;
    if(!$FromAjax && isset($Info[4]) && $Info[4]){
        $Fix4Code=1;
    }
    ?>
    <div class="form-group">
        <label class="col-sm-2 control-label">ที่อยู่:</label>
        <div class="col-sm-5">
            <input type="hidden" name="CustID" value="<?php if($CustInfo[4]){ print(intval($CustInfo[4])); } ?>">
            <input type="text" class="form-control" name="Address1" value="<?php if(!$FromAjax && isset($Info[0])){ print($Info[0]); }else{ print($CustInfo[0]); } ?>">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-2"></div>
        <div class="col-sm-5">
            <input type="text" class="form-control" name="Address2" value="<?php if(!$FromAjax && isset($Info[1])){ print($Info[1]); }else{ print($CustInfo[1]); } ?>">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-2"></div>
        <div class="col-sm-5">
            <input type="text" class="form-control" name="Address3" value="<?php if(!$FromAjax && isset($Info[5])){ print($Info[5]); }else{ print($CustInfo[7]); } ?>">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-2"></div>
        <div class="col-sm-5">
            <input type="text" class="form-control" name="Address4" value="<?php if(!$FromAjax && isset($Info[6])){ print($Info[6]); }else{ print($CustInfo[8]); } ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">เบอร์โทร:</label>
        <div class="col-sm-2">
            <input type="text" class="form-control" name="Tel" value="<?php if(!$FromAjax && isset($Info[2])){ print($Info[2]); }else{ print($CustInfo[2]); } ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">Tax ID:</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" name="TaxCode" value="<?php if(!$FromAjax && isset($Info[3])){ print($Info[3]); }else{ print($CustInfo[3]); } ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">ทะเบียนรถ:</label>
        <div class="col-sm-3">
            <select class="form-control" name="CheckCarCode">
            <option value="1"<?php if($Fix4Code || $CustInfo[5] || !$CustNum){ print(" selected"); } ?>>ระบุทะเบียนรถทุกครั้งที่ใช้</option>
            <option value="0"<?php if(!$Fix4Code || (!$CustInfo[5] && $CustNum)){ print(" selected"); } ?>>ใช้แบบไม่ระบุทะเบียนรถได้</option>
            </select>
        </div>
    </div>
<?php
}
function getCouponCode($CustID, $BookNo){
    global $db_name;
    $allCouponCode="";
    $sqlCouponCode="SELECT CouponCode from ".$db_name.".coupon where CustomerID=".intval($CustID)." and Status=1 and BookNo='".mysql_real_escape_string(trim($BookNo))."' order by CouponCode ASC;";
    $rsCouponCode=mysql_query($sqlCouponCode);
    while($CouponCode=mysql_fetch_row($rsCouponCode)){
        $allCouponCode.='<option value="'.$CouponCode[0].'">'.$CouponCode[0].'</option>';
    }
    return $allCouponCode;
}
function LockCouponForm($CustID){
    global $db_name;
    $allBookNo="";
    $sqlBookNo="SELECT Price from ".$db_name.".coupon where CustomerID=".intval($CustID)." and Status=1 group by Price order by Price ASC;";
    $rsBookNo=mysql_query($sqlBookNo);
    while($BookNo=mysql_fetch_row($rsBookNo)){
        if(!isset($FirstBookNo)){
            $FirstBookNo=$BookNo[0];
        }
        $allBookNo.='<option value="'.$BookNo[0].'">'.$BookNo[0].'</option>';
    }
    if(!isset($FirstBookNo)){
        print('<p class="passcode_send-error">ไม่มีคูปองที่สามารถล็อคได้แล้ว</p>');
    }
    else{
        $allCouponCode=getCouponCode($CustID, $FirstBookNo);
        print('
        <div class="form-group">
            <label class="col-sm-2 control-label">มูลค่าคูปอง:</label>
            <div class="col-sm-3">
                <select name="LockPrice" class="form-control input-sm" style="display:inline;">
                '.$allBookNo.'
                </select>
            </div>
            <div class="col-sm-7">
                <input type="text" class="form-control inline_input noEnterSubmit" name="LockCouponCode" value="" placeholder="เลขที่" style="width:85%;">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2 control-label"></div>
            <div class="col-sm-7">
                <p>&nbsp;&nbsp;ใช้คูปองต่อเนื่องด้วยเครื่องหมาย -</p>
                <p>&nbsp;&nbsp;แบ่งเลขที่คูปองด้วยเครื่องหมาย ,</p>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">เหตุผล:</label>
            <div class="col-sm-4">
                <input type="hidden" name="Lock4Cust" value="'.$CustID.'">
                <input type="text" class="form-control noEnterSubmit" name="Reason" value="" style="width:350px;">
            </div>
        </div>');
    }
}

function editItemForm($CustID){
        global $db_name;
        $sqlCouponBalance="SELECT CustName,CouponBalance FROM customer WHERE CustID=".intval($CustID).";";
        $rsCouponBalance=mysql_query($sqlCouponBalance);
        while($CouponBalance=mysql_fetch_row($rsCouponBalance)){
            $changetype = number_format($CouponBalance[1],2);
            print('
        <div class="form-group">
            <label class="col-sm-2 control-label">ชื่อลูกค้า:</label>
            <div class="col-sm-4">
                '.$CouponBalance[0].'
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">ยอดเงินเดิม:</label>
            <div class="col-sm-4">
                '.$CouponBalance[1].'
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">ยอดเงินใหม่:</label>
            <div class="col-sm-4">
                 <input type="hidden" name="CustIDCT" value="'.intval($CustID).'">
                 <input type="text"  name="changetypes" class="price" value="'.$changetype.'" style="width:100px;">
            </div>
        </div>');
        }
}

$alertTxt='';
$DeleteSuccess=0;
if(isset($_POST["getCustInfo"])){
    getCustInfo($_POST["getCustInfo"], 1, $_POST["TotalSell"]);
    exit();
}
else if(isset($_POST["lockCoupon"])){
    LockCouponForm($_POST["lockCoupon"]);
    exit();
}
else if(isset($_POST["show_edit_coupons"]) && intval($_POST["show_edit_coupons"])){
    editItemForm($_POST["show_edit_coupons"]);
    exit();
}
else if(isset($_POST["changetypes"]) && isset($_POST["CustIDCT"])){
    $sqlUpdatecustmoney="UPDATE ".$db_name.".customer SET CouponBalance = ".$_POST["changetypes"]."
    WHERE CustID =".intval($_POST["CustIDCT"]).";";
    $rsUpdatemoney=mysql_query($sqlUpdatecustmoney);
}
else if(isset($_POST["removeCustCoupon"]) && intval($_POST["removeCustCoupon"]) && isset($_POST["DeleteCoupon"])){
    foreach ($_POST["DeleteCoupon"] as $key => $value) {
        $sqlDelete="UPDATE ".$db_name.".coupon SET CustomerID=0, RealUse=0, Status=3, PaidHistoryID=0, UseHistoryID=0 WHERE coupon.ID=".intval($value).";";
        if($rsDelete=mysql_query($sqlDelete)){
            $DeleteSuccess++;
        }
    }
    $alertTxt='<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>ลบคูปอง '.$DeleteSuccess.' รายการจากลูกค้าเรียบร้อยแล้ว.</div>';
}
else if(isset($_POST["unlockAll"]) && intval($_POST["unlockAll"])){
    $CouponSummary=0;
    if($_POST['ApproveNow']){
        $ActionNote='อนุมัติคูปองแบบหลายรายการ';
        $ActionAlert='อนุมัติคูปองเรียบร้อยแล้ว';
        $sqlSummary="select Price, coupon.ID from ".$db_name.".coupon where CustomerID=".intval($_POST["CustomerID"])." and Status=5;";
    }
    else{
        $ActionNote='ปลดล็อคคูปองแบบหลายรายการ';
        $ActionAlert='ปลดล็อคคูปองเรียบร้อยแล้ว';
        $sqlSummary="select Price, coupon.ID from (".$db_name.".coupon_locked inner join ".$db_name.".coupon on coupon_locked.CouponID=coupon.ID) where CustomerID=".intval($_POST["CustomerID"])." and Status=4 group by coupon.ID;";
    }
    $rsSummary=mysql_query($sqlSummary);
    while($Summary=mysql_fetch_row($rsSummary)){
        $sqlUpdate="UPDATE ".$db_name.".coupon SET Status=1 where coupon.ID=".intval($Summary[1]).";";
        $rsUpdate=mysql_query($sqlUpdate);
        $CouponSummary+=round($Summary[0], 2);

        $sqlUpdate="delete from ".$db_name.".coupon_locked where coupon_locked.CouponID=".intval($Summary[1]).";";
        $rsUpdate=mysql_query($sqlUpdate);
    }

    $sqlHistory="INSERT INTO ".$db_name.".coupon_history (EmpID, Total, CustomerID, ProcessDate, HistoryNote, ChangeNote) VALUES (".intval($UserID).", '".floatval($CouponSummary)."', ".intval($_POST["CustomerID"]).", '".date("Y-n-j", time())."', '".$ActionNote."', '');";
    $rsHistory=mysql_query($sqlHistory);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$ActionAlert.'</div>';
}
else if(isset($_POST["unlock"]) && intval($_POST["unlock"])){
    $count=0;
    $CouponList="0";
    $CouponListTxt="";
    if($_POST['ApproveNow']){
        $ActionNote='อนุมัติคูปอง';
        $ActionAlert='อนุมัติคูปองเรียบร้อยแล้ว';
        $sqlSummary="select sum(Price) from ".$db_name.".coupon where Status=5 and PaidHistoryID=".intval($key).";";
        $rsSummary=mysql_query($sqlSummary);
        $Summary=mysql_fetch_row($rsSummary);
        foreach($_POST["unlock"] as $key => $value) {
            $sqlUpdate="UPDATE ".$db_name.".coupon SET Status=1 where Status=5 and PaidHistoryID=".intval($key).";";
            $rsUpdate=mysql_query($sqlUpdate);
            $sqlUpdate="delete from ".$db_name.".coupon_locked where coupon_locked.CouponID=".intval($key).";";
            $rsUpdate=mysql_query($sqlUpdate);
            $CouponList.=",".intval($key);
            $CouponListTxt.="<br>".trim($value);
            $count++;
        }
    }
    else{
        $ActionNote='ปลดล็อคคูปอง';
        $ActionAlert='ปลดล็อคคูปองเรียบร้อยแล้ว';
        foreach($_POST["unlock"] as $key => $value) {
            $sqlUpdate="UPDATE ".$db_name.".coupon SET Status=1 where coupon.ID=".intval($key)." and Status=4;";
            $rsUpdate=mysql_query($sqlUpdate);
            $sqlUpdate="delete from ".$db_name.".coupon_locked where coupon_locked.CouponID=".intval($key).";";
            $rsUpdate=mysql_query($sqlUpdate);
            $CouponList.=",".intval($key);
            $CouponListTxt.="<br>".$value;
            $count++;
        }
        $sqlSummary="select sum(Price) from ".$db_name.".coupon where coupon.ID in (".$CouponList.");";
        $rsSummary=mysql_query($sqlSummary);
        $Summary=mysql_fetch_row($rsSummary);
    }
    if($count>0){
        $sqlHistory="INSERT INTO ".$db_name.".coupon_history (EmpID, Total, CustomerID, ProcessDate, HistoryNote, ChangeNote) VALUES (".intval($UserID).", '".floatval($Summary[0])."', ".intval($_POST["CustomerID"]).", '".date("Y-n-j", time())."', '".$ActionNote."', '".substr($CouponListTxt, 4)."');";
        $rsHistory=mysql_query($sqlHistory);
        $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.$ActionAlert.'</div>';

        $digits = 6;
        $Passcode = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
        $ExpireTime=(86400*1); // 1 day
        $sqlDelete="delete from ".$db_name.".coupon_passcode where CustomerID=".intval($_POST['CustomerID']).";";
        $rsDelete=mysql_query($sqlDelete);
        $sqlHistory="INSERT INTO ".$db_name.".coupon_passcode (CustomerID, Passcode, PasscodeType, ExpireDate) VALUES (".intval($_POST['CustomerID']).", '".$Passcode."', ".intval($_POST['ApproveNow']).", ".(time()+$ExpireTime).");";
        $rsHistory=mysql_query($sqlHistory);
    }
}
else if(isset($_POST["LockCouponCode"]) && trim($_POST["LockCouponCode"])){
    // $startNo=$_POST["LockCouponCode1"];
    // $endNo=$_POST["LockCouponCode1"];
    // $note=" เลขที่ ".$startNo;
    // if(intval($_POST["LockCouponCode2"]) > intval($_POST["LockCouponCode1"])){
    //     $endNo=$_POST["LockCouponCode2"];
    //     $note.=" ถึงเลขที่ ".$endNo;
    // }

    $AllList=0;
    $couponListArr=explode(',', $_POST['LockCouponCode']);
    foreach ($couponListArr as $key1 => $value1) {
        if(preg_match('#-#', $value1)){
            $LongList=explode('-', $value1);
            for($i=intval($LongList[0]); $i<=intval($LongList[1]); $i++){
                $AllList.=",".$i;
            }
        }
        else{
            $AllList.=",".trim($value1);
        }
    }
    $note="มูลค่า ".trim($_POST["LockPrice"])." เลขที่ ".$_POST['LockCouponCode'];
    $couponList=0;
    $CustomerID=$_POST["Lock4Cust"];
    $sqlCoupon="select ID, Price from ".$db_name.".coupon where Price='".mysql_real_escape_string(trim($_POST["LockPrice"]))."' and CouponCode in (".$AllList.") and CustomerID=".intval($CustomerID)." and Status=1 order by CouponCode;";
    $rsCoupon=mysql_query($sqlCoupon);
    if(mysql_num_rows($rsCoupon)){
        $Summary=0;
        while($Coupon=mysql_fetch_row($rsCoupon)){
            $sqlUpdate="UPDATE ".$db_name.".coupon SET Status=4 where coupon.ID=".$Coupon[0].";";
            $rsUpdate=mysql_query($sqlUpdate);
            $Summary+=round($Coupon[1], 2);

            $sqlLock="INSERT INTO ".$db_name.".coupon_locked (CouponID, HistoryID) VALUES (".$Coupon[0].", 0);";
            $rsLock=mysql_query($sqlLock);
            $couponList.=",".$Coupon[0];
        }
        $sqlHistory="INSERT INTO ".$db_name.".coupon_history (EmpID, Total, CustomerID, ProcessDate, HistoryNote, ChangeNote, LockReason) VALUES (".intval($UserID).", '".floatval($Summary)."', ".intval($CustomerID).", '".date("Y-n-j", time())."', 'ล็อคคูปอง', '".$note."', '".mysql_real_escape_string(trim($_POST["Reason"]))."');";
        $rsHistory=mysql_query($sqlHistory);
        $HistoryID=mysql_insert_id($Conn);
        $sqlUpdate="UPDATE ".$db_name.".coupon_locked SET HistoryID=".intval($HistoryID)." where CouponID in (".$couponList.");";
        $rsUpdate=mysql_query($sqlUpdate);
    }
    else{
        $alertTxt='<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>ไม่สามารถล็อคคูปองหมายเลข '.$_POST['LockCouponCode'].' ได้ กรุณาตรวจสอบหมายเลขหรือสถานะของคูปอง</div>';
    }
}
else if(isset($_POST["sellCoupon"]) && isset($_POST["CouponCode"])){
    // check coupon first
    // $startNo=$_POST["CouponCode1"];
    // $endNo=$_POST["CouponCode1"];
    // if(intval($_POST["CouponCode2"]) && $_POST["CouponCode1"]!=$_POST["CouponCode2"]){
    //     $endNo=$_POST["CouponCode2"];
    // }

    $moreDetail="";
    foreach ($_POST['CouponCode'] as $key => $value) {
        if(trim($value)){
            $moreDetail.="คูปองมูลค่า ".$key." เลขที่ ".$value."<br>";
        }
    }
    $condition="";
    foreach ($_POST["CouponCode"] as $key => $value) {
        if(trim($value)){
            $AllList[$key]=0;
            $couponListArr=explode(',', $value);
            foreach ($couponListArr as $key1 => $value1) {
                if(preg_match('#-#', $value1)){
                    $LongList=explode('-', $value1);
                    for($i=intval($LongList[0]); $i<=intval($LongList[1]); $i++){
                        $AllList[$key].=",'".$i."'";
                    }
                }
                else{
                    $AllList[$key].=",'".trim($value1)."'";
                }
            }
            if($condition){
                $condition.=" or ";
            }
            $condition.="(Price='".intval($key)."' and CouponCode in (".$AllList[$key]."))";
        }
    }

    $sqlCheck="select sum(if(Status!=3, 1, 0)), sum(if(Status=3, 1, 0)) from ".$db_name.".coupon where ".$condition.";";
    $rsCheck=mysql_query($sqlCheck);
    $CheckNum=mysql_fetch_row($rsCheck);
    if(!$CheckNum[0] && intval($CheckNum[1])){
        // check cust name
        $sqlCheck="select CustID from ".$db_name.".customer where CustName='".mysql_real_escape_string(trim($_POST["CustName"]))."';";
        $rsCheck=mysql_query($sqlCheck);
        $Check=mysql_fetch_row($rsCheck);
        if(intval($Check[0])){
            $_POST["CustID"]=$Check[0];
        }

        $sqlCoupon="select ID, Price from ".$db_name.".coupon where ".$condition." and Status=3;";
        $rsCoupon=mysql_query($sqlCoupon);
        if(mysql_num_rows($rsCoupon)){
            if(isset($_POST["Sell4CustID"]) && intval($_POST["Sell4CustID"])){
                $_POST["CustID"]=$_POST["Sell4CustID"];
            }
            else if(!intval($_POST["CustID"]) && trim($_POST["CustName"])){
                $sqlInsert="INSERT INTO ".$db_name.".customer (CustName, Address1, Address2, Address3, Address4, Tel, TaxCode, BranchCode, CreditLock, CreditLimit, CreditTerm, SpecialTerm, DayBeforePay, UnofficialBalance, CheckCarCode, FromService) VALUES ('".mysql_real_escape_string(trim($_POST["CustName"]))."', '".mysql_real_escape_string(trim($_POST["Address1"]))."', '".mysql_real_escape_string(trim($_POST["Address2"]))."', '".mysql_real_escape_string(trim($_POST["Address3"]))."', '".mysql_real_escape_string(trim($_POST["Address4"]))."', '".mysql_real_escape_string(trim($_POST["Tel"]))."', '".mysql_real_escape_string(trim($_POST["TaxCode"]))."', 'ไม่ระบุ', 0, '0.00', 0, 0, 0, '0.00', ".intval($_POST["CheckCarCode"]).", 2);";
                $rsInsert=mysql_query($sqlInsert);
                $_POST["CustID"]=mysql_insert_id($Conn);
            }
            else if(intval($_POST["CustID"])){
                $sqlUpdate="UPDATE ".$db_name.".customer SET CustName='".mysql_real_escape_string(trim($_POST["CustName"]))."', Address1='".mysql_real_escape_string(trim($_POST["Address1"]))."', Address2='".mysql_real_escape_string(trim($_POST["Address2"]))."', Address3='".mysql_real_escape_string(trim($_POST["Address3"]))."', Address4='".mysql_real_escape_string(trim($_POST["Address4"]))."', Tel='".mysql_real_escape_string(trim($_POST["Tel"]))."', TaxCode='".mysql_real_escape_string(trim($_POST["TaxCode"]))."', CheckCarCode=".intval($_POST["CheckCarCode"])." where CustID=".intval($_POST["CustID"]).";";
                $rsUpdate=mysql_query($sqlUpdate);
            }
            $Summary=0;
            $DateArr=explode("/", $_POST["payDate"]);
            $setDate=$DateArr[2]."-".$DateArr[1]."-".$DateArr[0];
            if($PermissionNo != 3){ // not approve
                $setActive=5;
            }
            else{
                $setActive=1;
            }

            if(isset($_POST['UseOldBalance']) && intval($_POST['UseOldBalance'])){
                $sqlUpdate="UPDATE ".$db_name.".customer SET CouponBalance='0.00' where CustID=".intval($_POST["CustID"]).";";
                $rsUpdate=mysql_query($sqlUpdate);
                if($_POST['UseOldBalance']>0){
                    $moreDetail.='<br> ** ใช้ยอดคงเหลือการจากใช้งานครั้งก่อน '.number_format($_POST['UseOldBalance'], 2).' บาท';
                }
                else{
                    $moreDetail.='<br> ** จ่ายเพิ่มจากยอดการใช้งานครั้งก่อน '.number_format(abs($_POST['UseOldBalance']), 2).' บาท';
                }
            }
            $LockReason=$_POST["payType"];

            $sqlHistory="INSERT INTO ".$db_name.".coupon_history (EmpID, Total, CustomerID, ProcessDate, HistoryNote, ChangeNote, LockReason) VALUES (".intval($_REQUEST['EmpSellCoupon']).", '0', ".intval($_POST["CustID"]).", '".$setDate."', 'ซื้อคูปอง', '".$_POST["payType"]." ## ".$moreDetail."', '".$LockReason."');";
            $rsHistory=mysql_query($sqlHistory);
            $HistoryID=mysql_insert_id($Conn);
            while($Coupon=mysql_fetch_row($rsCoupon)){
                $sqlUpdate="UPDATE ".$db_name.".coupon SET Status=".$setActive.", PaidHistoryID=".$HistoryID.", CustomerID=".intval($_POST["CustID"])." where coupon.ID=".$Coupon[0].";";
                $rsUpdate=mysql_query($sqlUpdate);
                if($rsUpdate){
                    $Summary+=round($Coupon[1], 2);
                }
            }
            $sqlUpdate="UPDATE ".$db_name.".coupon_history SET Total=".$Summary." where coupon_history.HistoryID=".$HistoryID.";";
            $rsUpdate=mysql_query($sqlUpdate);

            if($_POST['payType']=='เงินสด' || $_POST['payType']=='เช็ค'){
                // insert to account_daily
                $SetDate=explode("/", $_POST['payDate']);
                $setSellDate=mktime(date("H", time()), date("i", time()), 0, $SetDate[1], $SetDate[0], $SetDate[2]);
                $sqlInsert="INSERT INTO ".$db_name.".account_daily (Type, Name, Total, PaidTo, PaidDate, Note, BookCodeNo, ForActionID) VALUES (1, 'ขายคูปอง', '".floatval($Summary)."', '".intval($_REQUEST['EmpSellCoupon'])."', '".$setSellDate."', '".$_POST['payType']." - ".mysql_real_escape_string(trim($_POST["CustName"]))."', '', ".$HistoryID.");";
                $rsInsert=mysql_query($sqlInsert);
            }

            // send email if sell by supervisor
            if($PermissionNo != 3){
                $sqlCust="SELECT CustName from ".$db_name.".customer where CustID=".intval($_POST["CustID"]).";";
                $rsCust=mysql_query($sqlCust);
                $CustInfo=mysql_fetch_row($rsCust);
                $buffer="\n\nขายคูปองสำหรับบริษัท ".$CustInfo[0]." โดย ".$UserName;
                $buffer.="\n\nรายละเอียดคูปอง:\n\t\tเล่มที่ ".$_POST["CouponCode"]."\tเลขที่ ".$startNo;
                if($endNo!=$startNo){
                    $buffer.=" ถึงเลขที่ ".$endNo;
                }
                $sendTo=$AdminEmail;
                $Addheaders="From: P.M.K. OIL\n";
                $Addheaders.="MIME-Version: 1.0\n";
                $Addheaders.="Content-Type: text/html; charset=UTF-8\n";
                $Addheaders.="X-Mailer: PHP 5.x";
                mail($sendTo, 'ขายคูปอง', $buffer, $Addheaders);
            }
        }
    }
    else{
        $alertTxt='<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>มีการระบุเลขที่คูปองที่ไม่สามารถนำมาขายได้ กรุณาตรวจสอบ.</div>';
        $_REQUEST["sellcoupon"]=1;
    }
}
else if(isset($_POST["CouponCode"]) && trim($_POST["CouponCode"])){
    $startNo=$_POST["CouponCode1"];
    $endNo=$_POST["CouponCode1"];
    if(intval($_POST["CouponCode2"])){
        $endNo=$_POST["CouponCode2"];
    }
    $BookCodeNo1=$_POST["BookCodeNo1"];
    $BookCodeNo2=$_POST["BookCodeNo2"];
    $_POST["SellPrice"]=preg_replace("/,/", "", $_POST["SellPrice"]);

    $couponNoStart=$startNo;
    for($i=$BookCodeNo1; $i<=$BookCodeNo2; $i++){
        for($j=1; $j<=$_POST["CouponPerBook"]; $j++){
            $sqlInsert="INSERT INTO ".$db_name.".coupon (BookNo, BookCodeNo, CouponCode, Price, Status) VALUES ('".mysql_real_escape_string(trim($_POST["CouponCode"]))."', '".intval($i)."', '".intval($couponNoStart)."', '".floatval($_POST["SellPrice"])."', 3);";
            $rsInsert=mysql_query($sqlInsert);
            $couponNoStart++;
        }
    }
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>เพิ่มข้อมูลคูปองใหม่เรียบร้อยแล้ว.</div>';
}

if(isset($_REQUEST['ShowMSG'])){
    $AlertArr = array('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>แก้ไขข้อมูลลูกค้าเรียบร้อยแล้ว.</div>', '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>เพิ่มข้อมูลลูกค้าเรียบร้อยแล้ว.</div>');
    $alertTxt=$AlertArr[$_REQUEST['ShowMSG']];
}




include("header.php");
if(isset($_REQUEST["sellcoupon"]) && intval($_REQUEST["sellcoupon"])){
    if(!isset($_REQUEST['page'])){
        $_REQUEST['page']=1;
    }
    $Info = array();
    $CustomerList="";
    $sqlCust="SELECT CustName from ".$db_name.".customer where Deleted=0 and CustID>0 order by CustName ASC;";
    $rsCust=mysql_query($sqlCust);
    while($CustInfo=mysql_fetch_row($rsCust)){
        $CustomerList.="*".$CustInfo[0];
    }
    $CustomerList=substr($CustomerList, 1);
    if(isset($_POST['payType'])){
        $setPayType=$_POST['payType'];
    }else{
        $setPayType='เงินสด';
    }
    $PriceList="0";
    $sqlPrice="SELECT Price from ".$db_name.".coupon where Status=3 group by Price;";
    $rsPrice=mysql_query($sqlPrice);
    while($Price=mysql_fetch_row($rsPrice)){
        $PriceList.="-".$Price[0]."-";
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>คูปอง</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;">ขายคูปอง</h3>
                </div>
                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <form action="coupons.php" method="post" class="form-horizontal" role="form" onsubmit="javascript:return checkCoupon();" autocomplete="off">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">วันที่ขาย:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control Calendar" name="payDate" value="<?php if(!isset($_POST['payDate'])){ print(date("d/m/Y", time())); }else{ print($_POST['payDate']); } ?>">
                            </div>
                        </div>
                        <?php
                        if(preg_match("/-100.00-/", $PriceList)){
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">รายละเอียดคูปอง:</label>
                            <div class="col-sm-10">
                                <span class="inline_input">มูลค่า 100 บาท &nbsp;&nbsp;&nbsp;&nbsp; </span>
                                <!-- <span class="inline_input"> &nbsp; เล่มที่ &nbsp; </span>
                                <select name="CouponBook[100]" class="form-control input-sm" style="display:inline; width:85px;">
                                    <?php
                                    $sqlBook="SELECT concat(BookNo,'',BookCodeNo), BookNo, BookCodeNo from ".$db_name.".coupon where Status=3 and Price='100' group by BookNo order by BookNo ASC;";
                                    $rsBook=mysql_query($sqlBook);
                                    while($Book=mysql_fetch_row($rsBook)){
                                        print("<option value=\"".$Book[1]."-".$Book[2]."\">".$Book[0]."</option>");
                                    }
                                    ?>
                                </select> -->
                                <span class="inline_input"> &nbsp; เลขที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="CouponCode[100]" id="CouponCode100" value="<?php if(isset($_POST["CouponCode"][100])){ print($_POST["CouponCode"][100]); } ?>" style="width:320px;" onchange="getCouponTotal(100, this.value);">
                                <span class="inline_input" id="showtotal-100"></span>
                            </div>
                        </div>
                        <?php
                        }
                        if(preg_match("/-300.00-/", $PriceList)){
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">รายละเอียดคูปอง:</label>
                            <div class="col-sm-10">
                                <span class="inline_input">มูลค่า 300 บาท &nbsp;&nbsp;&nbsp;&nbsp; </span>
                                <!-- <span class="inline_input"> &nbsp; เล่มที่ &nbsp; </span>
                                <select name="CouponBook[300]" class="form-control input-sm" style="display:inline; width:85px;">
                                    <?php
                                    $sqlBook="SELECT concat(BookNo,'',BookCodeNo) from ".$db_name.".coupon where Status=3 and Price='300' group by BookNo order by BookNo ASC;";
                                    $rsBook=mysql_query($sqlBook);
                                    while($Book=mysql_fetch_row($rsBook)){
                                        print("<option value=\"".$Book[0]."\">".$Book[0]."</option>");
                                    }
                                    ?>
                                </select> -->
                                <span class="inline_input"> &nbsp; เลขที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="CouponCode[300]" id="CouponCode300" value="<?php if(isset($_POST["CouponCode"][300])){ print($_POST["CouponCode"][100]); } ?>" style="width:320px;" onchange="getCouponTotal(300, this.value);">
                                <span class="inline_input" id="showtotal-300"></span>
                            </div>
                        </div>
                        <?php
                        }
                        if(preg_match("/-500.00-/", $PriceList)){
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">รายละเอียดคูปอง:</label>
                            <div class="col-sm-10">
                                <span class="inline_input">มูลค่า 500 บาท &nbsp;&nbsp;&nbsp;&nbsp; </span>
                                <!-- <span class="inline_input"> &nbsp; เล่มที่ &nbsp; </span>
                                <select name="CouponBook[500]" class="form-control input-sm" style="display:inline; width:85px;">
                                    <?php
                                    $sqlBook="SELECT concat(BookNo,'',BookCodeNo) from ".$db_name.".coupon where Status=3 and Price='500' group by BookNo order by BookNo ASC;";
                                    $rsBook=mysql_query($sqlBook);
                                    while($Book=mysql_fetch_row($rsBook)){
                                        print("<option value=\"".$Book[0]."\">".$Book[0]."</option>");
                                    }
                                    ?>
                                </select> -->
                                <span class="inline_input"> &nbsp; เลขที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="CouponCode[500]" id="CouponCode500" value="<?php if(isset($_POST["CouponCode"][500])){ print($_POST["CouponCode"][100]); } ?>" style="width:320px;" onchange="getCouponTotal(500, this.value);">
                                <span class="inline_input" id="showtotal-500"></span>
                            </div>
                        </div>
                        <?php
                        }
                        if(preg_match("/-1000.00-/", $PriceList)){
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">รายละเอียดคูปอง:</label>
                            <div class="col-sm-10">
                                <span class="inline_input">มูลค่า 1,000 บาท &nbsp; </span>
                                <!-- <span class="inline_input"> &nbsp; เล่มที่ &nbsp; </span>
                                <select name="CouponBook[1000]" class="form-control input-sm" style="display:inline; width:85px;">
                                    <?php
                                    $sqlBook="SELECT concat(BookNo,'',BookCodeNo) from ".$db_name.".coupon where Status=3 and Price='1000' group by BookNo order by BookNo ASC;";
                                    $rsBook=mysql_query($sqlBook);
                                    while($Book=mysql_fetch_row($rsBook)){
                                        print("<option value=\"".$Book[0]."\">".$Book[0]."</option>");
                                    }
                                    ?>
                                </select> -->
                                <span class="inline_input"> &nbsp; เลขที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="CouponCode[1000]" id="CouponCode1000" value="<?php if(isset($_POST["CouponCode"][1000])){ print($_POST["CouponCode"][100]); } ?>" style="width:320px;" onchange="getCouponTotal(1000, this.value);">
                                <span class="inline_input" id="showtotal-1000"></span>
                            </div>
                        </div>
                        <?php
                        }
                        ?>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">รวมเป็นเงิน:</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control inline_input price" id="SellPrice" name="SellPrice" value="<?php if(isset($_POST['SellPrice'])){ print($_POST['SellPrice']); } ?>" style="width:85px;">
                                <span class="inline_input"> &nbsp; บาท</span>
                                <span id="couponBalance"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ชำระโดย:</label>
                            <div class="col-sm-4">
                                <select id="payType" name="payType" class="form-control input-sm" style="display:inline; width:130px;">
                                <option value="เงินสด"<?php if($setPayType=='เงินสด'){ print(' selected'); } ?>>เงินสด</option>
                                <option value="เช็ค"<?php if($setPayType=='เช็ค'){ print(' selected'); } ?>>เช็ค</option>
                                <option value="บัตรเครดิต"<?php if($setPayType=='บัตรเครดิต'){ print(' selected'); } ?>>บัตรเครดิต</option>
                                <option value="โอนเงินเข้าธนาคาร"<?php if($setPayType=='โอนเงินเข้าธนาคาร'){ print(' selected'); } ?>>โอนเงินเข้าธนาคาร</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ผู้รับเงิน:</label>
                            <div class="col-sm-4">
                                <select id="payType" name="EmpSellCoupon" class="form-control input-sm">
                                    <?php
                                    $sqlEmp="select concat(FirstName, ' ', LastName), EmpID from employee where employee.EmpID!=1 and Deleted=0 order by FirstName ASC, LastName ASC;";
                                    $rsEmp=mysql_query($sqlEmp);
                                    while($EmpName=mysql_fetch_row($rsEmp)){
                                        print("<option value=\"".$EmpName[1]."\"");
                                        if(!isset($_REQUEST['EmpSellCoupon'])){
                                            $_REQUEST['EmpSellCoupon']=$EmpName[1];
                                        }
                                        if($_REQUEST['EmpSellCoupon']==$EmpName[1]){
                                            print(" selected");
                                        }
                                        print(">".$EmpName[0]."</option>");
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php
                        if(isset($_REQUEST["CustIDSell"]) && intval($_REQUEST["CustIDSell"])){
                            $sqlCust="SELECT CustName, CouponBalance from ".$db_name.".customer where CustID='".intval($_REQUEST["CustIDSell"])."' and Deleted=0 order by CustName ASC;";
                            $rsCust=mysql_query($sqlCust);
                            $CustInfo=mysql_fetch_row($rsCust);
                            print('
                            <input type="hidden" name="Sell4CustID" id="Sell4CustID" value="'.$_REQUEST["CustIDSell"].'">
                            <input type="hidden" name="oldUseTotal" id="oldUseTotal" value="'.($CustInfo[1]).'">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">ชื่อบริษัท / ชื่อลูกค้า:</label>
                                <div class="col-sm-4" style="margin-top:7px;">
                                    '.$CustInfo[0].'
                                </div>
                            </div>');
                            if($CustInfo[1]>0){
                                print('
                            <div class="form-group">
                                <div class="col-sm-2">&nbsp;</div>
                                <div class="col-sm-5" style="color:red;">
                                    <input type="hidden" name="UseOldBalance" value="'.$CustInfo[1].'">
                                    มียอดคงเหลือการจากใช้งานครั้งก่อน '.number_format($CustInfo[1], 2).' บาท
                                </div>
                            </div>');
                            }
                            else if($CustInfo[1]<0){
                                print('
                            <div class="form-group">
                                <div class="col-sm-2">&nbsp;</div>
                                <div class="col-sm-5" style="color:red;">
                                    <input type="hidden" name="UseOldBalance" value="'.$CustInfo[1].'">
                                    มูลค่าที่ต้องจ่ายเพิ่มจากยอดการใช้งานครั้งก่อน '.number_format(abs($CustInfo[1]), 2).' บาท
                                </div>
                            </div>');
                            }
                            print('<div class="form-group" id="NetTotal"></div>');
                        }
                        else{
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ชื่อบริษัท / ชื่อลูกค้า:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="CustName" id="AddCustomer" value="<?php if(isset($_POST['CustName'])){ print($_POST['CustName']); } ?>">
                            </div>
                        </div>
                        <div id="CustomerInfo">
                            <?php
                            if(isset($_POST['CustName'])){
                                $Info[0]=$_POST['Address1'];
                                $Info[1]=$_POST['Address2'];
                                $Info[2]=$_POST['Tel'];
                                $Info[3]=$_POST['TaxCode'];
                                $Info[4]=$_POST['CheckCarCode'];
                                $Info[5]=$_POST['Address3'];
                                $Info[6]=$_POST['Address4'];
                                getCustInfo(trim($_POST['CustName']), 0, 0);
                            }
                            else{
                                getCustInfo('', 0, 0);
                            }
                            ?>
                        </div>
                        <?php
                        }
                        ?>


                        <br>
                        <div class="actionBar right">
                            <input type="hidden" name="sellCoupon" id="sellCoupon" value="1">
                            <input type="hidden" name="page" value="<?php if(isset($_REQUEST['page'])){ print($_REQUEST['page']); } ?>">
                            <input type="hidden" id="backPage" value="coupons.php<?php if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                            <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="reset" class="btn btn-danger btn-rounder">ล้างข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php
}
else if(isset($_GET["AddCoupon"]) && intval($_GET["AddCoupon"])){
    $CarTypeArr = array('ทุกประเภท', 'เก๋ง', 'กระบะ', 'รถใหญ่', 'แท็กซี่', 'มอเตอร์ไซด์', 'อื่นๆ');
    if(!isset($_REQUEST['page'])){
        $_REQUEST['page']=1;
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>คูปอง</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;">เพิ่มคูปองใหม่</h3>
                </div>
                <div class="panel-body">
                    <form action="coupons.php" method="post" class="form-horizontal" role="form" onsubmit="javascript:return checkAddCoupon();" autocomplete="off">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">มูลค่าคูปอง:</label>
                            <div class="col-sm-4">
                                <select name="SellPrice" id="SellPrice" class="form-control input-sm" style="display:inline; width:100px;">
                                <option value="100">100</option>
                                <option value="300">300</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                                </select>
                                <span class="inline_input"> &nbsp; บาท</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">เล่มที่:</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control inline_input" name="CouponCode" id="SellBookNo" placeholder="เช่น ac" value="" style="width:80px;">
                                <span class="inline_input"> &nbsp; หมายเลขเล่มที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="BookCodeNo1" id="BookCodeNo1" value="" style="width:80px;">
                                <span class="inline_input"> &nbsp; ถึงหมายเลขเล่มที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="BookCodeNo2" id="BookCodeNo2" value="" style="width:80px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">จำนวนคูปองต่อเล่ม:</label>
                            <div class="col-sm-4">
                                <select name="CouponPerBook" id="CouponPerBook" class="form-control input-sm" style="display:inline; width:65px;">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="150">150</option>
                                <option value="200">200</option>
                                </select>
                                <span class="inline_input"> &nbsp; ใบ</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">เลขที่:</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control inline_input" name="CouponCode1" id="CouponCode1" value="" style="width:80px;">
                                <span class="inline_input"> &nbsp; ถึงเลขที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="CouponCode2" id="CouponCode2" value="" style="width:80px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12" id="AddCouponAlert"><br>
                            </div>
                        </div>
                        <div class="actionBar right">
                            <input type="hidden" name="page" value="<?php if(isset($_REQUEST['page'])){ print($_REQUEST['page']); } ?>">
                            <input type="hidden" id="backPage" value="coupons.php<?php if(isset($_REQUEST['page']) && intval($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                            <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="reset" class="btn btn-danger btn-rounder">ล้างข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php
}
else{
    $ItemPerPage=30;
    if(!isset($_REQUEST['page'])){
        $_REQUEST['page']=1;
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>คูปอง</h2>
        </div>

        <div class="content-center">

            <div class="tab-content">
                <div class="tab-pane fade in active">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- TABLE OPTION -->
                            <div role="toolbar" class="btn-toolbar padding-bootom">
                                <div id="-1" class="btn-group">
                                    <button class="btn btn-success" id="addCoupon" type="button"><i class="fa fa-plus"></i> เพิ่มคูปองใหม่</button>
                                </div>
                                <div class="btn-group" style="padding-left: 30px;">
                                    <button class="btn btn-info" type="button" onclick="javascript:location.href='coupon_check.php?backPage=coupons<?php print("&page=".$_REQUEST['page']); ?>';"><i class="fa fa-check-square"></i> เช็คคูปอง</button>
                                </div>
                                <?php
                                if($PermissionNo > 1){ // admin and supervisor
                                ?>
                                <div class="btn-group" style="padding-left: 30px;">
                                    <button class="btn btn-primary" type="button" onclick="javascript:location.href='coupons.php?sellcoupon=1<?php print("&page=".$_REQUEST['page']); ?>';"><i class="fa fa-money"></i> ขายคูปอง</button>
                                </div>
                                <?php
                                }
                                ?>
                                <!-- <div class="btn-group pull-right">
                                    <form method="post" role="form">
                                        <input type="hidden" id="submitTo" value="coupons.php">
                                        <input type="text" placeholder="Search..." class="form-control">
                                    </form>
                                </div> -->
                                <div class="pull-right btn-group">
                                    <form method="post" role="form">
                                    <input type="text" name="searchCust" value="<?php if(isset($_POST['searchCust'])){ print(trim($_POST['searchCust'])); } ?>" placeholder="Search..." class="form-control">
                                    </form>
                                </div>
                            </div><br><br>

                            <?php print($alertTxt); ?>
                            <!-- TABLE OPTION -->
                            <div class="table-responsive">
                                <?php
                                $sqlCoupon="SELECT count(ID), sum(Price) as TotalInActive from ".$db_name.".coupon where coupon.Status=3;";
                                $rsCoupon=mysql_query($sqlCoupon);
                                $Coupon=mysql_fetch_row($rsCoupon);
                                if($Coupon[0]>0){
                                    print("<p>จำนวนคูปองในระบบที่ยังไม่ได้ขาย มี ".number_format($Coupon[0])." ใบ รวมเป็นเงิน ".number_format($Coupon[1], 2)." บาท</p>");
                                }

                                $sqlCoupon="SELECT customer.CustName, sum(if(Status=1, Price, 0)) as TotalActive, sum(if(Status=2, Price, 0)) as Used, sum(if(Status=4, Price, 0)) as Locked, customer.CustID, sum(if(Status=5, Price, 0)) as NotApprove, CouponBalance from (".$db_name.".coupon inner join ".$db_name.".customer on coupon.CustomerID=customer.CustID) where customer.Deleted=0";
                                if(isset($_POST['searchCust']) && trim($_POST['searchCust'])){
                                    $sqlCoupon.=" and customer.CustName like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%'";
                                }
                                $sqlCoupon.=" group by coupon.CustomerID";
                                $rsCoupon=mysql_query($sqlCoupon.";");
                                $CouponNum=mysql_num_rows($rsCoupon);

                                $BeginItem=(($_REQUEST['page']-1)*$ItemPerPage);
                                if($BeginItem < 0){
                                    $BeginItem=0;
                                }
                                $sqlCoupon.=" order by CustName ASC Limit ".$BeginItem.", ".$ItemPerPage.";";
                                $rsCoupon=mysql_query($sqlCoupon);
                                //echo $sqlCoupon;
                                if($CouponNum){
                                ?>
                                <table style="width:100%;" class="td_center table table-condensed table-striped table-default table_border">
                                    <thead>
                                        <tr>
                                            <th>ชื่อลูกค้า</th>
                                            <!-- <th>จำนวนที่ใช้</th> -->
                                            <th>ยอดที่ใช้ได้</th>
                                            <th>จำนวนที่ล็อค</th>
                                            <th>ไม่ได้อนุมัติ</th>
                                            <th>มูลค่าทั้งหมด</th>
                                            <th>เกินยอดน้ำมัน</th>
                                            <th style="width:25%;">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $lockCoupon='';
                                    $EditCoupon='';
                                    $moreLink='';
                                    if(isset($_REQUEST['page']) && intval($_REQUEST['page'])){
                                        $moreLink='&page='.$_REQUEST['page'];
                                    }
                                    while($Coupon=mysql_fetch_row($rsCoupon)){
                                        $CouponUnlock="";
                                        $CouponLock=number_format($Coupon[3], 2);
                                        if($Coupon[3]>0){
                                            $CouponLock='<a href="lock-history.php?CustHistory='.$Coupon[4].$moreLink.'" class="coupon_locked" title="ดูรายละเอียด/ปลดล็อค">'.number_format($Coupon[3], 2).'</a>';
                                        }
                                        if($PermissionNo > 1){ // admin and supervisor
                                            $lockCoupon='&nbsp;&nbsp;&nbsp;<button title="ล็อคคูปอง" class="btn btn-warning btn-xs lockCoupon" data-toggle="modal" data-target="#myModal">&nbsp;&nbsp;<i class="fa fa-lock"></i>&nbsp;&nbsp;</button>';
                                        }
                                        if($PermissionNo > 2){ // admin
                                            $EditCoupon='&nbsp;&nbsp;&nbsp;<button title="แก้ไขคูปอง" class="btn btn-success btn-xs show_edit_coupons" data-toggle="modal" data-target="#myModal2">&nbsp;&nbsp;<i class="fa fa-edit"></i>&nbsp;&nbsp;</button>';
                                        }

                                        $setColor=' style="background-color:white;"';
                                        $moreField='<td'.$setColor.'>0.00</td>';
                                        if(intval($Coupon[5])){ // admin
                                            $setColor=' style="background-color:#FFFEC9;"';
                                            $moreField='<td'.$setColor.'><a href="lock-history.php?CustHistory='.$Coupon[4].$moreLink.'&Approve=1" class="coupon_locked" title="ดูรายละเอียด/อนุมัติ">'.number_format($Coupon[5], 2).'</a></td>';
                                        }
                                        $CouponTotal=($Coupon[1]+$Coupon[5]+$Coupon[3]);

                                        // <td'.$setColor.'>'.number_format($Coupon[2], 2).'</td>
                                        print('
                                        <tr id="item-'.$Coupon[4].'">
                                            <td'.$setColor.' class="text-left">&nbsp;
                                                <span class="cust_name-link editCust" title="แก้ไขข้อมูลของ '.$Coupon[0].'">'.$Coupon[0].'</span>
                                            </td>
                                            <td'.$setColor.'><a href="lock-history.php?CustHistory='.$Coupon[4].$moreLink.'&active=1">'.number_format($Coupon[1], 2).'</a></td>
                                            <td'.$setColor.'>'.$CouponLock.'</td>
                                            '.$moreField.'
                                            <td'.$setColor.'>'.number_format($CouponTotal, 2).'</td>
                                            <td'.$setColor.'>'.number_format($Coupon[6], 2).'</td>
                                            <td'.$setColor.'>
                                                <div id="'.$Coupon[4].'">
                                                    <button title="ขายคูปอง" class="btn btn-primary btn-xs" onclick="javascript:location.href=\'coupons.php?sellcoupon=1&page='.$_REQUEST['page'].'&CustIDSell='.$Coupon[4].';\'">&nbsp;<i class="fa fa-money"></i>&nbsp;</button>
                                                    &nbsp;&nbsp;
                                                    <button title="เรียกดูประวัติ" class="btn btn-success btn-xs viewHistory">&nbsp;<i class="fa fa-file-text-o"></i>&nbsp;</button>
                                                    &nbsp;&nbsp;
                                                    <button title="แก้ไขข้อมูลรถ" class="btn btn-info btn-xs CustomerCar">&nbsp;<i class="fa fa-car"></i>&nbsp;</button>'.$lockCoupon.$EditCoupon.'
                                                </div>
                                            </td>
                                        </tr>');
                                    }
                                    ?>
                                </table>
                                <?php
                                }
                                else if(isset($_POST['searchCust']) && trim($_POST['searchCust'])){
                                    print('<div class="text-center passcode_send-error"><br><strong>ไม่พบข้อมูลของ '.$_POST['searchCust'].'</strong><br>&nbsp;</div>');
                                }
                                ?>
                            </div>
                            <?php
                            if($CouponNum > $ItemPerPage){
                                $AllPage=ceil($CouponNum/$ItemPerPage);
                                print("<br>");
                                if($_REQUEST['page']!=1){
                                    print('<a href="coupons.php?page='.($_REQUEST['page']-1).'" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                                }
                                print("<select onchange=\"javascript:location.href='coupons.php?page='+this.value;\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
                                // all order page
                                for($i=1; $i<=$AllPage; $i++){
                                    print('<option value="'.$i.'"');
                                    if($_REQUEST['page']==$i){
                                        print(' selected');
                                    }
                                    print('>หน้า '.$i.'</option>');
                                }
                                print("</select>&nbsp;&nbsp;&nbsp;&nbsp;");
                                // next page
                                if($_REQUEST['page']!=$AllPage){
                                    print('<a href="coupons.php?page='.($_REQUEST['page']+1).'" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                                }
                            }
                            ?>
                            <!-- FULL FUNCTION TABLE -->

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <form action="customer.php" method="post" role="form" id="submitForm">
        <input type="hidden" id="UpdateItem" name="UpdateItem" value="0">
        <input type="hidden" id="CouponPage" name="CouponPage" value="1">
        <input type="hidden" id="backPage" name="backPage" value="coupons.php">
        <input type="hidden" id="PageNo" name="page" value="<?php print($_REQUEST['page']); ?>">
        <input type="hidden" id="submitTo" value="coupons.php">
    </form>


    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" name="lockCouponForm" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">ล็อคคูปอง</h4>
                </div>
                <div class="modal-body">
                    <div id="lockCouponForm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="javascript:document.forms['lockCouponForm'].submit();" class="btn btn-success">ล็อคคูปอง</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
                </div>
            </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" name="editItemForm" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">แก้ไขยอดน้ำมัน</h4>
                </div>
                <div class="modal-body">
                    <div id="editItemForm"> 
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="javascript:document.forms['editItemForm'].submit();" class="btn btn-success">แก้ไขยอดน้ำมัน</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
                </div>
            </form>
        </div>
      </div>
    </div>

<?php
}
include("footer.php");
?>