<?php
include("dbvars.inc.php");
if(!preg_match('/-4-/', $EmpAccess) && !preg_match('/-11-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

if(isset($_POST["SendPasscode"])){
    $sendTo=$AdminEmail;
    $digits = 6;
    $Passcode = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);

    $sqlCust="SELECT CustName from ".$db_name.".customer where CustID=".intval($_POST["SendPasscode"]).";";
    $rsCust=mysql_query($sqlCust);
    $CustInfo=mysql_fetch_row($rsCust);
    $buffer="\n\nรหัสปลดล็อคคูปองสำหรับบริษัท ".$CustInfo[0].": ".$Passcode;
    $Addheaders="From: P.M.K. OIL\n";
    $Addheaders.="MIME-Version: 1.0\n";
    $Addheaders.="Content-Type: text/html; charset=UTF-8\n";
    $Addheaders.="X-Mailer: PHP 5.x";

    if(intval($_POST['ApproveNow'])){ // ขอรหัสอนุมัติ
        $MailSubject='รหัสอนุมัติคูปอง';
        print('<span>รหัสอนุมัติถูกส่งไปยังอีเมล์ '.$sendTo.' กรุณาใส่รหัส</span>');
    }
    else{
        $MailSubject='รหัสปลดล็อคคูปอง';
        print('<span>รหัสปลดล็อคถูกส่งไปยังอีเมล์ '.$sendTo.' กรุณาใส่รหัส</span>');
    }
    mail($sendTo, $MailSubject, $buffer, $Addheaders);

    $ExpireTime=(86400*1); // 1 day
    $sqlDelete="delete from ".$db_name.".coupon_passcode where CustomerID=".intval($_POST['SendPasscode']).";";
    $rsDelete=mysql_query($sqlDelete);
    $sqlHistory="INSERT INTO ".$db_name.".coupon_passcode (CustomerID, Passcode, PasscodeType, ExpireDate) VALUES (".intval($_POST["SendPasscode"]).", '".$Passcode."', ".intval($_POST['ApproveNow']).", ".(time()+$ExpireTime).");";
    $rsHistory=mysql_query($sqlHistory);
    exit();
}
else if(isset($_POST["CheckPassCode"])){
    if(trim($_POST["UnlockAcess"])){
        $sqlPasscode="select Passcode from ".$db_name.".coupon_passcode where CustomerID=".intval($_POST["CheckPassCode"])." and ExpireDate>=".time()." and PasscodeType=".intval($_POST["ApproveNow"]).";";
        $rsPasscode=mysql_query($sqlPasscode);
        $Passcode=mysql_fetch_row($rsPasscode);
        if($Passcode[0]==$_POST["UnlockAcess"]){
            print("true");
        }
        else{
            print('<span class="passcode_send-error">รหัสไม่ถูกต้อง</span>');
        }
    }
    else{
        print('<span class="passcode_send-error">กรุณาใส่รหัสปลดล็อค</span>');
    }
    exit();
}

include("header.php");
$sqlClearPasscode="delete from ".$db_name.".coupon_passcode where ExpireDate<".time().";";
$rsClearPasscode=mysql_query($sqlClearPasscode);
// if($PermissionNo < 2){
//     print('<section class="pageContent">
//             <div class="title-body">
//                 <h2>คูปอง</h2>
//             </div>

//             <div class="content-center">
//             <p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
//             </div>
//         </section>');
// }
if(isset($_REQUEST["view"]) && intval($_REQUEST["view"])){
    $sqlCust="SELECT CustName from ".$db_name.".customer where CustID=".intval($_REQUEST["CustHistory"])." and Deleted=0 order by CustName ASC;";
    $rsCust=mysql_query($sqlCust);
    $CustInfo=mysql_fetch_row($rsCust);

    $SetYear=date("Y", time());
    $SetMonth=date("n", time());
    if(isset($_REQUEST["Time"])){
        $SetSelected=explode('-', $_REQUEST["Time"]);
        $SetYear=$SetSelected[0];
        $SetMonth=$SetSelected[1];
    }
    $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $SetMonth, $SetYear));

    if(!isset($_REQUEST['StartDate'])){
        $_REQUEST['StartDate']=date("d/m/Y", time());
        $_REQUEST['EndDate']=date("d/m/Y", time());
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>คูปอง</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <form name="CouponReport" action="#" method="post">
                    <h3 class="panel-title" style="margin: 10px 0;">
                    ประวัติการใช้คูปองของ <?php print($CustInfo[0]); ?> ระหว่างวันที่ <input type="text" class="form-control Calendar inline_input" name="StartDate" value="<?php print($_REQUEST['StartDate']); ?>" style="width:100px;"> ถึงวันที่ <input type="text" class="form-control Calendar inline_input" name="EndDate" value="<?php print($_REQUEST['EndDate']); ?>" style="width:100px;">&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-xs btn-primary btn-rounder" onclick="javascript:document.forms['CouponReport'].submit();">GO</button>
                    </h3>
                    </form>
                </div>
                <div class="panel-body">

                    <table width="100%" border="1" class="coupon_history">
                        <tr>
                            <th width="50">ลำดับที่</th>
                            <th width="100">วันที่</th>
                            <th width="100">โดย</th>
                            <th width="20%">การเคลื่อนไหว</th>
                            <th>รายละเอียด</th>
                            <th width="100">มูลค่าคูปอง</th>
                            <th width="100">จำนวนเงิน</th>
                            <!-- <th width="100">มูลค่าคงเหลือ</th> -->
                        </tr>
                        <?php
                        $count=1;
                        $SetDate=explode("/", $_REQUEST['StartDate']);
                        $SetDateTo=explode("/", $_REQUEST['EndDate']);
                        $sqlHistory="SELECT HistoryNote, Total, ProcessDate, FirstName, ChangeNote, HistoryID, LockReason from (".$db_name.".coupon_history inner join ".$db_name.".employee on employee.EmpID=coupon_history.EmpID) where CustomerID=".intval($_REQUEST["CustHistory"])." and ProcessDate>='".$SetDate[2]."-".$SetDate[1]."-".$SetDate[0]."' and ProcessDate<='".$SetDateTo[2]."-".$SetDateTo[1]."-".$SetDateTo[0]."' order by ProcessDate ASC, coupon_history.HistoryID ASC;";
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                $DateArr=explode("-", $History[2]);
                                $setDate=$DateArr[2]."-".$DateArr[1]."-".$DateArr[0];

                                $sqlPrice='';
                                $overCost='-';
                                if($History[0]=='ใช้คูปอง'){
                                    $sqlPrice="select sum(Price) from (".$db_name.".coupon inner join ".$db_name.".coupon_used on UseHistoryID=UsedID) where ";
                                    if(intval($History[6])){
                                        $sqlPrice.="UseHistoryID=".intval($History[6])." and ";
                                    }
                                    $sqlPrice.="ProcessDate='".$History[2]."'";
                                    if(isset($_REQUEST["CustHistory"]) && intval($_REQUEST["CustHistory"])){
                                        $sqlPrice.=" and coupon.CustomerID=".intval($_REQUEST["CustHistory"]);
                                    }
                                }
                                else if(preg_match('/ซื้อคูปอง/', $History[0])){
                                    $sqlPrice="select sum(Price) from ".$db_name.".coupon where PaidHistoryID=".intval($History[5]);
                                    if(isset($_REQUEST["viewAction"]) && $_REQUEST["viewAction"]==2){
                                        $moreDetail=explode('##', $History[5]);
                                        $Detail=$moreDetail[1];
                                        $payBy='<td>'.$moreDetail[0].'</td>';
                                    }
                                    else{
                                        $Detail=$History[0];
                                    }
                                }
                                else if(preg_match('/ล็อคคูปอง/', $History[0])){
                                    $Price[0]=$History[1];
                                    $History[1]='-';
                                }
                                if($sqlPrice){
                                    $overCost='-';
                                    //echo $sqlPrice."<br>";
                                    $rsPrice=mysql_query($sqlPrice.";");
                                    $Price=mysql_fetch_row($rsPrice);
                                    if($Price[0] > $History[1]){
                                        $overCost=number_format(round($Price[0]-$History[1], 2), 2);
                                    }
                                }
                                if($History[1]>0){
                                    $History[1]=number_format($History[1], 2);
                                }
                                // <td>'.$overCost.'</td>
                                print('<tr>
                                    <td>'.$count.'</td>
                                    <td>'.$setDate.'</td>
                                    <td>'.$History[3].'</td>
                                    <td style="text-align:left; padding-left:5px;">'.$History[0].'</td>
                                    <td style="text-align:left; padding-left:5px;">'.preg_replace('/=/', ' เลขที่ ', preg_replace('/#/', '<br>', preg_replace('/##/', '<br>', $History[4]))).'</td>
                                    <td>'.number_format($Price[0], 2).'</td>
                                    <td>'.$History[1].'</td>
                                    </tr>');
                                $count++;
                            }
                        }
                        else{
                            print('<tr><td colspan="7"><span style="color:red;">ไม่มีการเคลื่อนไหวของคูปอง</span></td></tr>');
                        }
                        ?>
                    </table>
                    <br>
                    <div class="actionBar right">
                        <input type="hidden" id="submitTo" value="lock-history.php">
                        <input type="hidden" name="page" value="<?php if(isset($_REQUEST['page'])){ print($_REQUEST['page']); } ?>">
                        <input type="hidden" id="backPage" value="coupons.php<?php if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                        <input type="hidden" id="CustomerID" value="<?php print(intval($_REQUEST["CustHistory"])); ?>">
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                    </div>

                </div>
            </div>
        </div>
    </section>
<?php
}
else if(isset($_REQUEST['active']) && intval($_REQUEST['active'])){
    $sqlName="select CustName from ".$db_name.".customer where customer.CustID=".intval($_REQUEST['CustHistory']).";";
    $rsName=mysql_query($sqlName);
    $Name=mysql_fetch_row($rsName);
    $DeleteSuccess=0;
    if(isset($_POST["DeleteCoupon"])){
        foreach ($_POST["DeleteCoupon"] as $key => $value) {
            $sqlDelete="UPDATE ".$db_name.".coupon SET CustomerID=0, RealUse=0, Status=3, PaidHistoryID=0, UseHistoryID=0 WHERE coupon.ID=".intval($value).";";
            if($rsDelete=mysql_query($sqlDelete)){
                $DeleteSuccess++;
            }
        }
    }
?>
<section class="pageContent">
        <div class="title-body">
            <h2>คูปอง</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0 0;">คูปองที่ใช้งานได้ <?php if($Name[0]){ print('ของ '.$Name[0]); } ?></h3>
                </div>
                <div class="panel-body">
                  <form action="#" name="CouponCheck" method="post">
                    <?php
                    if($DeleteSuccess){
                        print('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>ลบคูปอง '.$DeleteSuccess.' รายการจากลูกค้าเรียบร้อยแล้ว.</div>');
                    }
                    ?>
                    <table width="600px" border="1" class="coupon_history">
                        <tr>
                            <th width="150">มูลค่าคูปอง</th>
                            <th width="150">เล่มที่</th>
                            <th width="150">เลขที่</th>
                            <th width="70">ลบ &nbsp;<a href="javascript:toggleCheckbox();">(T)</a></th>
                        </tr>
                        <?php
                        $count=1;
                        $sqlHistory="SELECT concat(BookNo,'',BookCodeNo), CouponCode, Price, coupon.ID from (".$db_name.".coupon inner join ".$db_name.".customer on coupon.CustomerID=customer.CustID) where customer.CustID=".intval($_REQUEST['CustHistory'])." and Status=1 group by BookNo, CouponCode order by BookNo ASC, CouponCode ASC;";
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                print('<tr>
                                    <td>'.number_format($History[2], 2).'</td>
                                    <td>'.$History[0].'</td>
                                    <td>'.$History[1].'</td>
                                    <td><input type="checkbox" id="DeleteCoupon-'.$count.'" name="DeleteCoupon[]" value="'.$History[3].'"></td>
                                    </tr>');
                                $count++;
                            }
                        }
                        else{
                            print('<tr><td colspan="4"><span style="color:red;">ไม่มีคูปองที่ใช้งานได้</span></td></tr>');
                        }
                        ?>
                    </table>
                    <br>
                    <div style="width:600px; text-align:right;" class="actionBar">
                        <input type="hidden" id="backPage" value="coupons.php<?php if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        <?php
                        if($count>1){
                            print('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="hidden" id="AllDeleteBox" value="'.$count.'">
                            <input type="hidden" id="removeCustCoupon" name="removeCustCoupon" value="0">
                            <button type="button" id="remove4Cust" class="btn btn-danger btn-rounder">ลบคูปอง</button>');
                        }
                        ?>
                    </div>
                  </form>
                </div>
            </div>
        </div>
    </section>
<?php
}
else{
    $CustName[0]='';
    $CustCondition='';
    $MoreField='';
    $MoreField2='';
    $UnlockAction='';
    $ApproveNow=0;
    $PageHeader='<h2>รายงาน</h2>';
    $PageName='รายงานคูปองที่ถูกล็อค';
    $lockHeader='วันที่ล็อค';
    $ReasonHeader='เหตุผล';
    $unlockButtun='ปลดล็อคคูปอง';
    $unlockAll='ปลดล็อคทั้งหมด';
    $backTo="reports.php";
    $NoItem="ไม่มีคูปองที่ถูกล็อค";
    $MoreField2='<th width="10%">ล็อคโดย</th>';
    $ShowBookNo=1;
    $colspan=9;
    if(isset($_REQUEST["CustHistory"]) && intval($_REQUEST["CustHistory"])){
        $sqlCustName="SELECT customer.CustName from ".$db_name.".customer where CustID=".intval($_REQUEST["CustHistory"]).";";
        $rsCustName=mysql_query($sqlCustName);
        $CustName=mysql_fetch_row($rsCustName);
        $CustCondition.=" and coupon.CustomerID=".intval($_REQUEST["CustHistory"]);
        $MoreField='<th width="80">ปลดล็อค</th>';
        $PageHeader='<h2>คูปอง</h2>';
        $PageName='รายละเอียดคูปองที่ถูกล็อคของ '.$CustName[0];
        $backTo="coupons.php";
    }
    $sqlCoupon="SELECT concat(BookNo,'',BookCodeNo), CouponCode, coupon_history.ProcessDate, LockReason, coupon.ID, customer.CustName, FirstName, ChangeNote, Price, coupon.PaidHistoryID from ((((".$db_name.".coupon_locked inner join ".$db_name.".coupon_history on coupon_locked.HistoryID=coupon_history.HistoryID) inner join ".$db_name.".coupon on coupon_locked.CouponID=coupon.ID) inner join ".$db_name.".customer on customer.CustID=coupon.CustomerID) inner join ".$db_name.".employee on coupon_history.EmpID=employee.EmpID) where Status=4".$CustCondition." group by coupon.ID order by BookNo ASC, BookCodeNo ASC, CouponCode ASC, ProcessDate ASC, coupon_locked.HistoryID ASC;";
    if(isset($_REQUEST['Approve']) && intval($_REQUEST['Approve'])){
        $ApproveNow=1;
        $PageName='รายละเอียดคูปองที่ยังไม่อนุมัติ';
        $NoItem="ไม่มีคูปองที่ยังไม่อนุมัติ";
        $lockHeader='วันที่ซื้อ';
        $ReasonHeader='ชำระโดย';
        $unlockButtun='อนุมัติคูปอง';
        $unlockAll='อนุมัติทั้งหมด';
        $MoreField2='<th width="10%">ขายโดย</th>';
        $MoreField='<th width="50">อนุมัติ</th>';
        $colspan=8;
        $sqlCoupon="SELECT concat(BookNo,'',BookCodeNo), CouponCode, coupon_history.ProcessDate, LockReason, coupon.ID, customer.CustName, FirstName, ChangeNote, Price, coupon.PaidHistoryID from (((".$db_name.".coupon_history inner join ".$db_name.".coupon on coupon_history.HistoryID=coupon.PaidHistoryID) inner join ".$db_name.".customer on customer.CustID=coupon.CustomerID) inner join ".$db_name.".employee on coupon_history.EmpID=employee.EmpID) where Status=5 and coupon.CustomerID=".intval($_REQUEST["CustHistory"])." group by coupon.PaidHistoryID order by BookNo ASC, CouponCode ASC, ProcessDate ASC;";
        $ShowBookNo=0;
    }
    if($PermissionNo < 2){
        $MoreField='';
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <?php print($PageHeader); ?>
        </div>

        <div class="content-center">

            <div class="tab-content">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title" style="margin: 10px 0;"><?php print($PageName); ?></h3>
                    </div>
                    <div class="panel-body">
                        <form id="submitForm" name="CouponCheck" action="coupons.php" method="post" class="form-horizontal" role="form">
                            <table width="100%" border="1" class="coupon_history">
                                <thead>
                                    <tr>
                                        <th width="5%">ลำดับที่</th>
                                        <th width="20%">บริษัท</th>
                                        <th width="8%">มูลค่า</th>
                                        <?php if($ShowBookNo){ print('<th width="8%">เล่มที่</th>'); } ?>
                                        <th width="10%">เลขที่</th>
                                        <th width="15%"><?php print($lockHeader); ?></th>
                                        <th width="15%"><?php print($ReasonHeader); ?></th>
                                        <?php print($MoreField2.$MoreField); ?>
                                        <th width="8%">ลบ &nbsp;<a href="javascript:toggleCheckbox();">(T)</a></th>
                                    </tr>
                                </thead>
                                <?php
                                $count=1;
                                $rsCoupon=mysql_query($sqlCoupon);
                                if(mysql_num_rows($rsCoupon)){
                                    while($Coupon=mysql_fetch_row($rsCoupon)){
                                        $CouponNote=explode('##', $Coupon[7]);
                                        $DateArr=explode("-", $Coupon[2]);
                                        $setDate=$DateArr[2]."-".$DateArr[1]."-".$DateArr[0];
                                        if(($PermissionNo >= 2) && isset($_REQUEST["CustHistory"]) && intval($_REQUEST["CustHistory"])){
                                            if(!$ShowBookNo){
                                                $UnlockAction='<td>
                                                    &nbsp;&nbsp;<input type="checkbox" name="unlock['.$Coupon[9].']" value="'.trim($CouponNote[1]).'">&nbsp;
                                                </td>';
                                            }
                                            else{
                                                $UnlockAction='<td>
                                                    &nbsp;&nbsp;<input type="checkbox" name="unlock['.$Coupon[4].']" value="เล่มที่ '.$Coupon[0].' เลขที่ '.$Coupon[1].'">&nbsp;
                                                </td>';
                                            }
                                        }
                                        $BookShow='<td>'.$Coupon[0].'</td>';
                                        if(!$ShowBookNo){
                                            $BookShow='';
                                            $Coupon[1]=$CouponNote[1];
                                        }
                                        print('
                                        <tr>
                                            <td>'.$count.'</td>
                                            <td>'.$Coupon[5].'</td>
                                            <td>'.number_format($Coupon[8], 2).'</td>
                                            '.$BookShow.'
                                            <td>'.$Coupon[1].'</td>
                                            <td>'.$setDate.'</td>
                                            <td>'.$Coupon[3].'&nbsp;</td>
                                            <td>'.$Coupon[6].'</td>
                                            '.$UnlockAction.'
                                            <td><input type="checkbox" id="DeleteCoupon-'.$count.'" name="DeleteCoupon[]" value="'.$Coupon[4].'"></td>
                                        </tr>');
                                        $count++;
                                    }
                                }
                                else{
                                    print('<tr><td colspan="'.$colspan.'" class="passcode_send-error">'.$NoItem.'</td></tr>');
                                }
                                ?>
                            </table>
                            <br>
                            <div id="0" class="actionBar right">
                                <input type="hidden" name="page" value="<?php if(isset($_REQUEST['page'])){ print($_REQUEST['page']); } ?>">
                                <input type="hidden" id="backPage" value="<?php print($backTo); if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                                <input type="hidden" id="unlockAll" name="unlockAll" value="0">
                                <input type="hidden" id="ApproveNow" name="ApproveNow" value="<?php print($ApproveNow); ?>">
                                <input type="hidden" id="Permission" name="Permission" value="<?php print($PermissionNo); ?>">
                                <input type="hidden" id="CustomerID" name="CustomerID" value="<?php if(isset($_REQUEST["CustHistory"])){ print($_REQUEST["CustHistory"]); } ?>">
                                <?php
                                if($UnlockAction){
                                    if($Permission=='admin'){ // admin
                                        print('<button type="button" class="btn btn-success btn-rounder" onclick="javascript:document.forms[\'CouponCheck\'].submit();">'.$unlockButtun.'</button>');
                                        print('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                                    }
                                    else{ // supervisor
                                        print('<button type="button" class="btn btn-success btn-rounder clearPasscode" data-toggle="modal" data-target="#myModal">'.$unlockButtun.'</button><a href="javascript:void(0);" data-toggle="modal" data-target="#myModal" id="ask4Unlock" class="clearPasscode">&nbsp;</a>');
                                        print('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                                    }
                                    print('<button type="button" class="btn btn-primary btn-rounder" id="unlockAllCoupon">'.$unlockAll.'</button>');
                                }
                                ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                                <?php
                                if($count>1){
                                    print('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="hidden" id="AllDeleteBox" value="'.$count.'">
                                    <input type="hidden" id="removeCustCoupon" name="removeCustCoupon" value="0">
                                    <button type="button" id="remove4Cust" class="btn btn-danger btn-rounder">ลบคูปอง</button>');
                                }
                                ?>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <?php
    if($ApproveNow){
        $UnlockHead="อนุมัติคูปอง";
        $UnlockBody="รหัสอนุมัติ:";
    }else{
        $UnlockHead="ปลดล็อคคูปอง";
        $UnlockBody="รหัสปลดล็อค:";
    }
    ?>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form onsubmit="javascript:return checkPassCode();" action="lock-history.php" method="post" name="lockCouponForm" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><?php print($UnlockHead); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-5 control-label"><?php print($UnlockBody); ?></label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="UnlockAcess" id="UnlockAcess" value="">
                        </div>
                    </div>
                    <div id="SendPasscodeTxt" class="passcode_send"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="CheckPassCode"><?php print($UnlockHead); ?></button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-warning" id="SendPasscode">ขอรหัส</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="PasscodeCancel">ยกเลิก</button>
                </div>
            </form>
        </div>
      </div>
    </div>

<?php
}
include("footer.php");
?>