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
    mail($sendTo, 'รหัสปลดล็อคคูปอง', $buffer, $Addheaders);

    $ExpireTime=(86400*1);
    $sqlDelete="delete from ".$db_name.".coupon_passcode where CustomerID=".intval($_POST["SendPasscode"]).";";
    $rsDelete=mysql_query($sqlDelete);
    $sqlHistory="INSERT INTO ".$db_name.".coupon_passcode (CustomerID, Passcode, ExpireDate) VALUES (".intval($_POST["SendPasscode"]).", '".$Passcode."', ".(time()+$ExpireTime).");";
    $rsHistory=mysql_query($sqlHistory);
    print('<span>รหัสปลดล็อคถูกส่งไปยังอีเมล์ '.$sendTo.' กรุณาใส่รหัสปลดล็อค</span>');
    exit();
}
else if(isset($_POST["CheckPassCode"])){
    $sqlPasscode="select Passcode from ".$db_name.".coupon_passcode where CustomerID=".intval($_POST["CheckPassCode"]).";";
    $rsPasscode=mysql_query($sqlPasscode);
    $Passcode=mysql_fetch_row($rsPasscode);
    if($Passcode[0]==$_POST["UnlockAcess"]){
        print("true");
    }
    else{
        print('<span class="passcode_send-error">รหัสปลดล็อคไม่ถูกต้อง</span>');
    }
    exit();
}

include("header.php");

if(!isset($_REQUEST['StartDate'])){
    $_REQUEST['StartDate']=date("d/m/Y", time());
    $_REQUEST['EndDate']=date("d/m/Y", time());
}
if(!isset($_REQUEST["CustCouponID"])){
    $_REQUEST["CustCouponID"]=0;
}
$sqlClearPasscode="delete from ".$db_name.".coupon_passcode where ExpireDate<".time().";";
$rsClearPasscode=mysql_query($sqlClearPasscode);
if(isset($_REQUEST["viewAction"]) && intval($_REQUEST["viewAction"])){
    $Setcolspan=7;
    $CustCouponName="";
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>รายงาน</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                <form name="CouponReport" action="#" method="post">
                    <h5 class="panel-title" style="margin: 10px 0;"><?php
                        $usedReport=0;
                        $MoreOption="";
                        if($_REQUEST["viewAction"] == 1){
                            print("รายงานการเคลื่อนไหวของคูปอง");
                            $moreCondition="";
                        }
                        else if($_REQUEST["viewAction"] == 3){
                            print("รายงานการใช้คูปอง");
                            $usedReport=1;
                            $moreCondition=" and HistoryNote like 'ใช้คูปอง'";
                            $MoreOption="&nbsp; กะ <select name=\"UsedRound\" class=\"form-control inline_input\" style=\"width:60px;\"><option value=\"1\"";
                            if(!isset($_REQUEST["UsedRound"])){
                                $Time4In = array(1 => 7.30, 2 => 18.00, 3 => 4.00);
                                $Time4Out = array(1 => 18.00, 2 => 1.00, 3 => 7.30);
                                $TimeRec = date("H.i", time());
                                if($TimeRec >= 7.3 && $TimeRec<=18){
                                    $_REQUEST["UsedRound"]=1;
                                }
                                else if($TimeRec >= 4 && $TimeRec<=7.3){
                                    $_REQUEST["UsedRound"]=3;
                                }
                                else{
                                    $_REQUEST["UsedRound"]=2;
                                }
                            }
                            if($_REQUEST["UsedRound"]==1){ $MoreOption.=" selected"; }
                            $MoreOption.=">1</option><option value=\"2\"";
                            if($_REQUEST["UsedRound"]==2){ $MoreOption.=" selected"; }
                            $MoreOption.=">2</option><option value=\"3\"";
                            if($_REQUEST["UsedRound"]==3){ $MoreOption.=" selected"; }
                            $MoreOption.=">3</option></select>";
                        }
                        else{
                            print("รายงานการซื้อคูปอง");
                            $moreCondition=" and HistoryNote like '%ซื้อคูปอง%'";
                        }
                        print("&nbsp;");
                        ?>
                        ระหว่างวันที่ <input type="text" class="form-control Calendar inline_input" name="StartDate" value="<?php print($_REQUEST['StartDate']); ?>" style="width:90px;">&nbsp; ถึงวันที่ <input type="text" class="form-control Calendar inline_input" name="EndDate" value="<?php print($_REQUEST['EndDate']); ?>" style="width:90px;"><?php print($MoreOption); ?>&nbsp;
                        <?php
                        print(" ของ <select name=\"CustCouponID\" class=\"form-control inline_input\" style=\"width:130px;\"><option value=\"0\">ทุกบริษัท</option>");
                        $sqlCouponCode="SELECT coupon.CustomerID, customer.CustName from (".$db_name.".coupon inner join ".$db_name.".customer on coupon.CustomerID=customer.CustID) where customer.Deleted=0 group by coupon.CustomerID order by customer.CustName ASC;";
                        $rsCouponCode=mysql_query($sqlCouponCode);
                        while($CouponCode=mysql_fetch_row($rsCouponCode)){
                            print('<option value="'.$CouponCode[0].'"');
                            if($CouponCode[0] == intval($_REQUEST["CustCouponID"])){
                                print(" selected");
                                $CustCouponName=" ของ ".$CouponCode[1];
                            }
                            print('>'.$CouponCode[1].'</option>');
                        }
                        print("</select>");
                        ?>&nbsp;&nbsp;
                        <button type="submit" class="btn btn-xs btn-primary btn-rounder" onclick="javascript:document.forms['CouponReport'].submit();">GO</button>
                    </h5>
                </form>
                </div>
                <div class="panel-body">

                    <table width="100%" border="1" class="coupon_report">
                        <tr>
                            <th width="2%">ลำดับ</th>
                            <th width="8%">วันที่</th>
                            <th width="8%">โดย</th>
                            <th width="15%">บริษัท</th>
                            <?php
                            if($_REQUEST["viewAction"]==2){
                                $Setcolspan++;
                                print('<th width="8%">ชำระโดย</th>');
                            }
                            else if($_REQUEST["viewAction"]==1){
                                $Setcolspan++;
                                print('<th width="8%">การเคลื่อนไหว</th>');
                            }
                            ?>
                            <th>รายละเอียด</th>
                            <th width="8%">มูลค่าคูปอง</th>
                            <th width="8%">จำนวนเงิน</th>
                            <?php
                            if($_REQUEST["viewAction"]!=2){
                                $Setcolspan++;
                                print('<th width="8%">คูปองคงเหลือ</th>');
                            }
                            ?>
                        </tr>
                        <?php
                        $count=1;
                        $SetDate=explode("/", $_REQUEST['StartDate']);
                        $SetDateTo=explode("/", $_REQUEST['EndDate']);
                        $startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
                        $endDate=mktime(23, 59, 59, $SetDateTo[1], $SetDateTo[0], $SetDateTo[2]);
                        if(intval($_REQUEST["CustCouponID"])){
                            $moreCondition.=" and customer.CustID=".intval($_REQUEST["CustCouponID"]);
                        }
                        $sqlHistory="SELECT HistoryNote, Total, ProcessDate, FirstName, customer.CustName, ChangeNote, HistoryID, customer.CustID, LockReason from ((".$db_name.".coupon_history inner join ".$db_name.".employee on employee.EmpID=coupon_history.EmpID) inner join ".$db_name.".customer on customer.CustID=coupon_history.CustomerID) where ProcessDate>='".$SetDate[2]."-".$SetDate[1]."-".$SetDate[0]."' and ProcessDate<='".$SetDateTo[2]."-".$SetDateTo[1]."-".$SetDateTo[0]."'".$moreCondition." order by customer.CustName ASC, ProcessDate ASC, HistoryID ASC;";
                        if($_REQUEST["viewAction"] == 3){
                            $sqlHistory="SELECT HistoryNote, Total, ProcessDate, FirstName, customer.CustName, ChangeNote, HistoryID, customer.CustID, LockReason from (((".$db_name.".payments inner join ".$db_name.".coupon_history on payments.CouponUsedID=coupon_history.LockReason) inner join ".$db_name.".employee on employee.EmpID=coupon_history.EmpID) inner join ".$db_name.".customer on customer.CustID=coupon_history.CustomerID) where (payments.Date>=".intval($startDate)." and (payments.Date<=".intval($endDate)." and TimeRound<=".intval($_REQUEST["UsedRound"])."))".$moreCondition." order by customer.CustName ASC, ProcessDate ASC, HistoryID ASC;";
                        }
                        //echo $sqlHistory;
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            $TotalUsed1=0;
                            $TotalUsed2=0;
                            $TotalUsed3=0;
                            while($History=mysql_fetch_row($rsHistory)){
                                $Detail=$History[0];
                                if($usedReport){
                                    $Detail=preg_replace('/#/', '<br>', $History[5]);
                                    $Detail=preg_replace('/=/', ' เลขที่ ', $Detail);
                                }
                                $payBy='';
                                $sqlPrice='';
                                $overCost='-';
                                if($History[0]=='ใช้คูปอง'){
                                    $sqlPrice="select sum(Price), count(DISTINCT coupon.CustomerID) from (".$db_name.".coupon inner join ".$db_name.".coupon_used on UseHistoryID=UsedID) where UseHistoryID=".intval($History[8])." and ProcessDate='".$History[2]."' and coupon.CustomerID=".intval($History[7])." group by UseHistoryID;";
                                    if($_REQUEST["viewAction"] == 3){
                                        $sqlPrice="select sum(Price), 1 from (".$db_name.".coupon inner join ".$db_name.".coupon_used on UseHistoryID=UsedID) where UseHistoryID=".intval($History[8])." and ProcessDate='".$History[2]."' and coupon.CustomerID=".intval($History[7])." group by UseHistoryID;";
                                    }
                                }
                                else if(preg_match('/ซื้อคูปอง/', $History[0])){
                                    $sqlPrice="select sum(Price), 1 from ".$db_name.".coupon where PaidHistoryID=".intval($History[6]).";";
                                    if($_REQUEST["viewAction"]==2){
                                        $moreDetail=explode('##', $History[5]);
                                        if(isset($moreDetail[1])){
                                            $Detail=$moreDetail[1];
                                        }
                                        $payBy='<td style="text-align:left;">'.$moreDetail[0].'</td>';
                                    }
                                    else{
                                        $Detail=$History[0];
                                    }
                                }
                                else if(preg_match('/ล็อคคูปอง/', $History[0])){
                                    $Price[0]=$History[1];
                                    $Price[1]=1;
                                    $History[1]='-';
                                }
                                if($sqlPrice){
                                    //echo $sqlPrice;
                                    $overCost='-';
                                    $rsPrice=mysql_query($sqlPrice);
                                    $Price=mysql_fetch_row($rsPrice);
                                    if($Price[0]>0 && $Price[0]!=$History[1]){
                                        $overCost=round($Price[0]-$History[1], 2);
                                        $TotalUsed3+=$overCost;
                                        $overCost=number_format($overCost, 2);
                                    }
                                }
                                if($Price[0]>0){
                                    $DateArr=explode("-", $History[2]);
                                    $setDate=$DateArr[2]."-".$DateArr[1]."-".$DateArr[0];
                                    $addDetails='';
                                    $TotalUsed2+=$History[1];
                                    if($_REQUEST["viewAction"]!=2){
                                        $addDetails='<td style="text-align:right;">'.$overCost.'&nbsp;</td>';
                                    }
                                    if($History[1]>0){
                                        $History[1]=number_format($History[1], 2);
                                    }
                                    if($_REQUEST["viewAction"]==1){
                                        $payBy='<td style="text-align:left;">'.$History[0].'</td>';
                                        $Detail=preg_replace('/=/', ' เลขที่ ', preg_replace('/#/', '<br>', preg_replace('/##/', '<br>', $History[5])));
                                    }
                                	print('<tr>
                                    <td>'.$count.'</td>
                                    <td>'.$setDate.'</td>
                                    <td>'.$History[3].'</td>
                                    <td style="text-align:left;">'.$History[4].'</td>
                                    '.$payBy.'
                                    <td style="text-align:left;">'.$Detail.'</td>
                                    <td style="text-align:right;">'.number_format($Price[0], 2).'&nbsp;</td>
                                    <td style="text-align:right;">'.$History[1].'&nbsp;</td>
                                    '.$addDetails.'
                                    </tr>');
                                	$count++;
                                    $TotalUsed1+=$Price[0];
                                }
                            }
                            if($_REQUEST["viewAction"]==3){
                                print("<tr><td colspan=\"5\" style=\"text-align:right;\"><strong>รวม:</strong></td><td style=\"text-align:right;\">".number_format($TotalUsed1, 2)."&nbsp;</td><td style=\"text-align:right;\">".number_format($TotalUsed2, 2)."&nbsp;</td><td style=\"text-align:right;\">".number_format($TotalUsed3, 2)."&nbsp;</td></tr>");
                            }
                        }
                        else{
                            print('<tr><td colspan="'.$Setcolspan.'"><span style="color:red;">ไม่มีการเคลื่อนไหวของคูปอง'.$CustCouponName.'</span></td></tr>');
                        }
                        ?>
                    </table>
                    <br>
                    <div class="actionBar right">
                        <input type="hidden" id="submitTo" value="coupon-report.php">
                        <input type="hidden" id="backPage" value="reports.php">
                        <input type="hidden" id="viewAction" value="<?php print(intval($_REQUEST["viewAction"])); ?>">
                        <input type="hidden" id="CustomerID" value="<?php if(isset($_REQUEST["CustHistory"])){ print(intval($_REQUEST["CustHistory"])); } ?>">
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        &nbsp;&nbsp;&nbsp;
                    </div>

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
    $UnlockAction='';
    $PageHeader='<h2>รายงาน</h2>';
    $PageName='รายงานคูปองยังไม่ได้อนุมัติ';
    if(isset($_REQUEST["CustHistory"]) && intval($_REQUEST["CustHistory"])){
        $sqlCustName="SELECT customer.CustName from ".$db_name.".customer where CustID=".intval($_REQUEST["CustHistory"]).";";
        $rsCustName=mysql_query($sqlCustName);
        $CustName=mysql_fetch_row($rsCustName);
        $CustCondition=" and coupon.CustomerID=".intval($_REQUEST["CustHistory"]);
        $PageHeader='<h2>คูปอง</h2>';
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
                        <form id="submitForm" action="coupons.php" method="post" class="form-horizontal" role="form">
                            <table width="100%" border="1" class="coupon_history">
                                <thead>
                                    <tr>
                                        <th width="100">ลำดับที่</th>
                                        <th width="25%">บริษัท</th>
                                        <th>รายละเอียด</th>
                                        <th width="15%">ขายโดย</th>
                                        <th width="15%">วันที่</th>
                                    </tr>
                                </thead>
                                <?php
                                $count=1;
                                $sqlCoupon="SELECT ChangeNote, coupon_history.ProcessDate, coupon.ID, customer.CustName, FirstName from (((".$db_name.".coupon_history inner join ".$db_name.".coupon on coupon_history.HistoryID=coupon.PaidHistoryID) inner join ".$db_name.".customer on customer.CustID=coupon_history.CustomerID) inner join ".$db_name.".employee on coupon_history.EmpID=employee.EmpID) where Status=5".$CustCondition." group by coupon.PaidHistoryID order by BookNo ASC, CouponCode ASC, ProcessDate ASC, HistoryID ASC;";
                                $rsCoupon=mysql_query($sqlCoupon);
                                if(mysql_num_rows($rsCoupon)){
                                    while($Coupon=mysql_fetch_row($rsCoupon)){
                                        $DateArr=explode("-", $Coupon[1]);
                                        $setDate=$DateArr[2]."-".$DateArr[1]."-".$DateArr[0];
                                        $ChangeInfo=explode("##", $Coupon[0]);
                                        print('
                                        <tr>
                                            <td>'.$count.'</td>
                                            <td class="text-left">'.$Coupon[3].'</td>
                                            <td class="text-left">'.$ChangeInfo[1].'</td>
                                            <td>'.$Coupon[4].'</td>
                                            <td>'.$setDate.'</td>
                                        </tr>');
                                        $count++;
                                    }
                                }
                                else{
                                    print('<tr><td colspan="5" class="passcode_send-error">ไม่มีคูปองยังไม่ได้อนุมัติ</td></tr>');
                                }
                                ?>
                            </table>
                            <br>
                            <div id="0" class="actionBar right">
                                <input type="hidden" id="backPage" value="reports.php">
                                <input type="hidden" id="unlockAll" name="unlockAll" value="0">
                                <input type="hidden" id="Permission" name="Permission" value="<?php print($PermissionNo); ?>">
                                <input type="hidden" id="CustomerID" name="CustomerID" value="<?php if(isset($_REQUEST["CustHistory"])){ print($_REQUEST["CustHistory"]); } ?>">
                                <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                                &nbsp;&nbsp;&nbsp;
                                <?php
                                if($UnlockAction){
                                    if($PermissionNo==3){ // admin
                                        print('<button type="submit" class="btn btn-success btn-rounder">ปลดล็อคคูปอง</button>&nbsp;&nbsp;');
                                    }
                                    else{ // supervisor
                                        print('<button type="button" class="btn btn-success btn-rounder clearPasscode" data-toggle="modal" data-target="#myModal">ปลดล็อคคูปอง</button><a href="javascript:void(0);" data-toggle="modal" data-target="#myModal" id="ask4Unlock" class="clearPasscode">&nbsp;</a>&nbsp;');
                                    }
                                    print('<button type="button" class="btn btn-primary btn-rounder" id="unlockAllCoupon">ปลดล็อคทั้งหมด</button>');
                                }
                                ?>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form action="coupon-report.php" method="post" name="lockCouponForm" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">ปลดล็อคคูปอง</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-5 control-label">รหัสปลดล็อค:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="UnlockAcess" id="UnlockAcess" value="">
                        </div>
                    </div>
                    <div id="SendPasscodeTxt" class="passcode_send"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="CheckPassCode">ปลดล็อคคูปอง</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-warning" id="SendPasscode">ขอรหัส</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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