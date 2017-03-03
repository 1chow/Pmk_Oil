<?php
include("dbvars.inc.php");
if(!preg_match('/-7-/', $EmpAccess) && !preg_match('/-6-/', $EmpAccess) && !preg_match('/-13-/', $EmpAccess) && !preg_match('/-11-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

$canSubmit=0;
if(isset($_REQUEST["TimeForCheck"]) && (!isset($_COOKIE["TimeCheck"]) || $_COOKIE["TimeCheck"]!=$_REQUEST["TimeForCheck"])){
    setcookie("TimeCheck", $_REQUEST["TimeForCheck"], 0, "/");
    $_COOKIE["TimeCheck"]=$_REQUEST["TimeForCheck"];
    $canSubmit=1;
}
$alertTxt='';
if($canSubmit && isset($_REQUEST["clearTotal"])){
    $SetDate=explode("/", $_REQUEST['payDate']);
    $startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    $SetToDate=explode("/", $_REQUEST['payToDate']);
    $endDate=mktime(23, 59, 59, $SetToDate[1], $SetToDate[0], $SetToDate[2]);
    $_POST["ReceiveIncome"]=preg_replace("/,/", "", $_POST["ReceiveIncome"]);
    $_POST["ReceiveHolding"]=preg_replace("/,/", "", $_POST["ReceiveHolding"]);
    $sqlInsert="INSERT INTO ".$db_name.".account_daily (Type, Name, Total, PaidTo, PaidDate, Note) VALUES ('1', 'ยอดขายน้ำมัน', '".floatval($_POST['ReceiveIncome']+$_POST['ReceiveHolding'])."', '0', '".time()."', 'บันทึกวันที่ ".date("d/m/Y", time())."');";
    $rsInsert=mysql_query($sqlInsert);
    // insert in account dairy
    if(intval($_POST["ReceiveIncome"])){
        $sqlInsert="INSERT INTO ".$db_name.".account_daily (Type, Name, Total, PaidTo, PaidDate, Note) VALUES ('-1', 'ยอดนำส่งเจ้าของกิจการ', '".floatval($_POST['ReceiveIncome'])."', '0', '".(time()+2)."', '');";
        $rsInsert=mysql_query($sqlInsert);
    }
    // insert in account dairy
    if(intval($_POST["ReceiveHolding"])){
        $sqlCashier="select concat(FirstName, ' ', LastName) from ".$db_name.".employee where EmpID=".intval($_POST['HoldingUser']).";";
        $rsCashier=mysql_query($sqlCashier);
        $row=mysql_fetch_row($rsCashier);
        $sqlInsert="INSERT INTO ".$db_name.".account_daily (Type, Name, Total, PaidTo, PaidDate, Note) VALUES ('-1', 'ผู้ถือเงินสดย่อยหลังเคลียร์ยอดเงิน', '".floatval($_POST['ReceiveHolding'])."', 0, '".(time()+1)."', '".$row[0]."');";
        $rsInsert=mysql_query($sqlInsert);
        $sqlInsert="INSERT INTO ".$db_name.".account_daily (Type, Name, Total, PaidTo, PaidDate, Note) VALUES ('1', 'ผู้ถือเงินสดย่อยหลังเคลียร์ยอดเงิน', '".floatval($_POST['ReceiveHolding'])."', '".intval($_POST['HoldingUser'])."', '".(time()+2)."', '');";
        $rsInsert=mysql_query($sqlInsert);
    }

    $sqlHistory="UPDATE ".$db_name.".payments set ClearTime=".time()." where payments.Date>=".$startDate." and payments.Date<=".$endDate." and ClearTime=0;";
    $rsHistory=mysql_query($sqlHistory);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}

include("header.php");

if(!isset($_REQUEST['payDate'])){
    $_REQUEST['payDate']=date("d/m/Y", time());
    $_REQUEST['payToDate']=date("d/m/Y", time());
}
if(!isset($_POST['payType'])){
    $_POST['payType']='';
}
if(!isset($_REQUEST["TypeDisplay"])){
    $_REQUEST["TypeDisplay"]=0;
}
if(!isset($_REQUEST['back']) || !trim($_REQUEST['back'])){
    $_REQUEST['back']='index';
}
if(!isset($_REQUEST['page'])){
    $_REQUEST['page']=1;
}
$SetDate=explode("/", $_REQUEST['payDate']);
$startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
$endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
$ItemPerPage=200;
if(!isset($_POST['RoundNo'])){
    $_POST['RoundNo']=0;
}

if(isset($_REQUEST['basicstyle'])){
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>รายงานการขายน้ำมันประจำวัน</h2>
        </div>

        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <form action="oil_record.php" method="post" class="form-horizontal" role="form" name="oil_record">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="page" id="page" value="<?php print($_REQUEST['page']); ?>">
                        <h3 class="panel-title" style="margin: 10px 0;">
                        <?php
                            print("รายงานการขายน้ำมันประจำวันที่:");
                            print("&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="payDate" value="'.$_REQUEST['payDate'].'" style="display:inline; width:100px;" onchange="javascript:document.forms[\'oil_record\'].submit();">');
                            print("&nbsp;&nbsp;&nbsp;");

                            $TypeArr = array('Coupon' => 'คูปอง', 'Card' => 'บัตรเครดิต', 'Credit' => 'ใบสั่งน้ำมัน', 'Cash' => 'เงินสด');
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">

                    <table width="100%" border="1" class="coupon_history">
                        <tr><th colspan="3"><p style="margin:10px;">
                            <?php
                            print('รายงานการขายน้ำมันประจำวันที่: '.$_REQUEST['payDate']);
                            if($_POST['RoundNo']){
                                print('&nbsp;(กะ '.$_POST['RoundNo'].")");
                            }
                            if($_POST['payType']){
                                print('&nbsp;&nbsp;&nbsp;ประเภทการชำระเงิน: '.$TypeArr[$_POST['payType']]);
                            }
                            ?>
                        </p></th></tr>
                        <tr>
                            <th>วันที่</th>
                            <!-- <th>บริษัท</th> -->
                            <th>ชำระโดย</th>
                            <!-- <th>เลขที่/เล่มที่</th> -->
                            <th>จำนวนเงิน</th>
                        </tr>
                        <?php
                        $count=1;
                        $OilTotal=0;
                        $TotalCoupon=0;
                        $TotalCard=0;
                        $TotalCash=0;
                        $TotalCredit=0;
                        $sqlHistory="select Amount, payments.Date, Type from ".$db_name.".payments where payments.Date>=".$startDate." and payments.Date<=".$endDate;
                        $rsHistory=mysql_query($sqlHistory.";");
                        $HistoryNum=mysql_num_rows($rsHistory);
                        $AllPage=ceil($HistoryNum/$ItemPerPage);

                        $sqlLastHistory=$sqlHistory;
                        $sqlLastHistory.=" order by payments.Date ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                        $rsHistory=mysql_query($sqlLastHistory);
                        //echo $sqlLastHistory;
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                print('<tr>
                                    <td>'.date('j/m/Y', $History[1]).'</td>
                                    <td>'.$TypeArr[$History[2]].'</td>
                                    <td>'.number_format($History[0], 2).'</td>
                                    </tr>');
                                $OilTotal+=round($History[0], 2);
                                $count++;
                            }
                            print('<tr style="height:40px;">
                                    <td colspan="2" style="text-align:right; background-color:#E3E3E3;"><strong>รวมทั้งสิ้น </strong>&nbsp;&nbsp;</td>
                                    <td style="background-color:#E3E3E3;"><strong>'.number_format($OilTotal, 2).'</strong></td>
                                </tr>');
                        }
                        else{
                            print('<tr><td colspan="3" style="padding:15px;"><span style="color:red;">ไม่พบข้อมูล</span></td></tr>');
                        }
                        ?>
                    </table>
                    <br>
                    <?php
                    if($HistoryNum > $ItemPerPage){
                        // prev page
                        print('<p><ul id="PageNav" class="pagination page-success"><li');
                        if($_REQUEST['page']==1){
                            print(' class="disabled"><a href="javascript:void(0);">');
                        }
                        else{
                            print('><a href="javascript:void(0);" onclick="javascript:document.getElementById(\'page\').value='.($_REQUEST['page']-1).'; document.forms[\'oil_record\'].submit();">');
                        }
                        print('&laquo;</a></li>');
                        // all order page
                        for($i=1; $i<=$AllPage; $i++){
                            print('<li');
                            if($_REQUEST['page']==$i){
                                print(' class="active"');
                            }
                            print('><a href="javascript:void(0);" onclick="javascript:document.getElementById(\'page\').value='.$i.'; document.forms[\'oil_record\'].submit();">'.$i.'</a></li>');
                        }
                        // next page
                        print('<li');
                        if($_REQUEST['page']==$AllPage){
                            print(' class="disabled"><a href="javascript:void(0);">');
                        }
                        else{
                            print('><a href="javascript:void(0);" onclick="javascript:document.getElementById(\'page\').value='.($_REQUEST['page']+1).'; document.forms[\'oil_record\'].submit();">');
                        }
                        print('&raquo;</a></li></ul></p>');
                    }
                    ?>
                    <div id="actionBar" class="actionBar right">
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
?>
<section class="pageContent">
    <form action="oil_record.php" method="post" class="form-horizontal" role="form" name="oil_record">
        <div class="title-body">
            <h2>รายงานการขาย</h2>
        </div>

        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                        <input type="hidden" name="verify" value="1">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="page" id="page" value="<?php print($_REQUEST['page']); ?>">
                        <h3 class="panel-title" style="margin: 10px 0;">
                        <?php
                            print("รายงานการขายประจำวันที่:");
                            print("&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="payDate" value="'.$_REQUEST['payDate'].'" style="display:inline; width:100px;">');
                            print("&nbsp; ถึงวันที่ &nbsp;");
                            print('<input type="text" class="form-control Calendar" name="payToDate" value="'.$_REQUEST['payToDate'].'" style="display:inline; width:100px;">');
                            print("&nbsp;&nbsp;&nbsp; แสดง &nbsp;<select name=\"TypeDisplay\" class=\"form-control inline_input input-sm\" style=\"width:230px;\"><option value=\"0\"");
                            $onlyNoClear="";
                            if(!$_REQUEST["TypeDisplay"]){
                                print(" selected");
                            }
                            print(">ทุกรายการ</option><option value=\"1\"");
                            if($_REQUEST["TypeDisplay"]==1){
                                print(" selected");
                                $onlyNoClear=" and ClearTime=0";
                            }
                            print(">เฉพาะรายการที่ยังไม่ได้ตรวจสอบ</option></select>");
                            print("&nbsp;&nbsp;&nbsp;");
                            print('<button type="button" onclick="javascript:document.forms[\'oil_record\'].submit();" class="btn btn-xs btn-primary btn-rounder">GO</button>');
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">
                    <?php
                        print($alertTxt);
                        $SetDate=explode("/", $_REQUEST['payDate']);
                        $startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
                        $SetToDate=explode("/", $_REQUEST['payToDate']);
                        $endDate=mktime(23, 59, 59, $SetToDate[1], $SetToDate[0], $SetToDate[2]);
                        $sqlHistory="select payments.Date, Type, sum(if(Type='Cash', Amount, 0)), sum(if(Type='Card', Amount, 0)), sum(if(Type='Credit', Amount, 0)), sum(if(Type='Coupon', Amount, 0)), concat(FirstName, ' ', LastName), sum(if(Type='Card', CardSlip, 0)), sum(if(Type='Credit', CardSlip, 0)), sum(if(Type='Coupon' && CouponPrice='100', CouponCount, 0)), sum(if(Type='Coupon' && CouponPrice='300', CouponCount, 0)), sum(if(Type='Coupon' && CouponPrice='500', CouponCount, 0)), sum(if(Type='Coupon' && CouponPrice='1000', CouponCount, 0)), sum(if(Type='Coupon' && CouponPrice='100', Amount, 0)), sum(if(Type='Coupon' && CouponPrice='300', Amount, 0)), sum(if(Type='Coupon' && CouponPrice='500', Amount, 0)), sum(if(Type='Coupon' && CouponPrice='1000', Amount, 0)), sum(if(ClearTime=0 && Type='Cash', Amount, 0)) from ((".$db_name.".payments left join ".$db_name.".employee on employee.EmpID=payments.SendBy) left join ".$db_name.".coupon_used on coupon_used.UsedID=CouponUsedID) where payments.Date>=".$startDate." and payments.Date<=".$endDate.$onlyNoClear." group by SendBy";
                        $rsHistory=mysql_query($sqlHistory.";");
                        $HistoryNum=mysql_num_rows($rsHistory);
                        $AllPage=ceil($HistoryNum/$ItemPerPage);

                        $sqlLastHistory=$sqlHistory;
                        $sqlLastHistory.=" order by 1 ASC, payments.Date ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                        $rsHistory=mysql_query($sqlLastHistory);
                        //echo $sqlLastHistory;
                        $FinalTotal=0;
                        $FinalCash=0;
                        $FinalCredit=0;
                        $FinalCard=0;
                        $CountCredit=0;
                        $CountCard=0;
                        $NotClearYet=0;
                        $SumCouponCount = array('100' => 0, '300' => 0, '500' => 0, '1000' => 0);
                        $SumCouponTotal = array('100' => 0, '300' => 0, '500' => 0, '1000' => 0);
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                $coupon100=($History[13]);
                                $coupon300=($History[14]);
                                $coupon500=($History[15]);
                                $coupon1000=($History[16]);
                                $sumCoupon=($coupon100+$coupon300+$coupon500+$coupon1000);
                                $couponCount=($History[9]+$History[10]+$History[11]+$History[12]);

                                $SumCouponTotal[100]+=$coupon100;
                                $SumCouponTotal[300]+=$coupon300;
                                $SumCouponTotal[500]+=$coupon500;
                                $SumCouponTotal[1000]+=$coupon1000;
                                $SumCouponCount[100]+=$History[9];
                                $SumCouponCount[300]+=$History[10];
                                $SumCouponCount[500]+=$History[11];
                                $SumCouponCount[1000]+=$History[12];
                                $finalsumCoupon+=($coupon100+$coupon300+$coupon500+$coupon1000);
                                $finalcouponCount+=($History[9]+$History[10]+$History[11]+$History[12]);
                                print('<table width="500px" border="1" class="coupon_history">');
                                print('<tr><th colspan="3"><b>'.$History[6].'</b></th></tr>');
                                print('<tr><th>&nbsp;</th><th><b>จำนวน</b></th><th><b>ยอดเงิน</b></th></tr>');
                                print('<tr><td style="text-align:right;">คูปองมูลค่า 100 บาท</td><td>'.$History[9].'</td><td style="text-align:right;">'.number_format($coupon100, 2).'</td></tr>');
                                print('<tr><td style="text-align:right;">คูปองมูลค่า 300 บาท</td><td>'.$History[10].'</td><td style="text-align:right;">'.number_format($coupon300, 2).'</td></tr>');
                                print('<tr><td style="text-align:right;">คูปองมูลค่า 500 บาท</td><td>'.$History[11].'</td><td style="text-align:right;">'.number_format($coupon500, 2).'</td></tr>');
                                print('<tr><td style="text-align:right;">คูปองมูลค่า 1000 บาท</td><td>'.$History[12].'</td><td style="text-align:right;">'.number_format($coupon1000, 2).'</td></tr>');
                                print('<tr><td style="text-align:right;"><b>รวม:</b>&nbsp;</td><td>'.$couponCount.'</td><td style="text-align:right;">'.number_format($sumCoupon, 2).'</td></tr>');

                                print('<tr><td style="text-align:right;">เงินสด</td><td>-</td><td style="text-align:right;">'.number_format($History[2], 2).'</td></tr>');
                                print('<tr><td style="text-align:right;">บัตรเครดิต</td><td>'.$History[7].'</td><td style="text-align:right;">'.number_format($History[3], 2).'</td></tr>');
                                print('<tr><td style="text-align:right;">ใบสั่งน้ำมัน</td><td>'.$History[8].'</td><td style="text-align:right;">'.number_format($History[4], 2).'</td></tr>');

                                $Total=round($sumCoupon+$History[2]+$History[3]+$History[4], 2);
                                $FinalTotal+=$Total;
                                $FinalCash+=$History[2];
                                $FinalCard+=$History[3];
                                $CountCard+=$History[7];
                                $FinalCredit+=$History[4];
                                $CountCredit+=$History[8];
                                $NotClearYet+=round($History[17], 2);
                                print('<tr><td style="text-align:right;" colspan="2"><b>รวมยอดเงิน:</b>&nbsp;</td><td style="text-align:right;">'.number_format($Total, 2).'</td></tr>');
                                if($History[17]){
                                    print('<tr><td style="text-align:right;" colspan="2"><b>รวมยอดเงินสดที่ยังไม่ได้เคลียร์:</b>&nbsp;</td><td style="text-align:right;">'.number_format($History[17], 2).'</td></tr>');
                                }
                                print('</table><br>');
                            }
                            // final total
                            print('<table width="500px" border="1" class="coupon_history paidreport">');
                            print('<tr><th colspan="3"><b>รวมรายการขายประจำวันที่ '.$_REQUEST['payDate']);
                            if($_REQUEST['payDate']!=$_REQUEST['payToDate']){
                                print(' - '.$_REQUEST['payToDate']);
                            }
                            if($_REQUEST["TypeDisplay"]==1){
                                print("<br>(เฉพาะรายการที่ยังไม่ได้ตรวจสอบ)");
                            }
                            print('</b></th></tr>');
                            print('<tr><th>&nbsp;</th><th><b>จำนวน</b></th><th><b>ยอดเงิน</b></th></tr>');
                            print('<tr><td style="text-align:right;">คูปองมูลค่า 100 บาท</td><td>'.$SumCouponCount[100].'</td><td style="text-align:right;">'.number_format($SumCouponTotal[100], 2).'</td></tr>');
                            print('<tr><td style="text-align:right;">คูปองมูลค่า 300 บาท</td><td>'.$SumCouponCount[300].'</td><td style="text-align:right;">'.number_format($SumCouponTotal[300], 2).'</td></tr>');
                            print('<tr><td style="text-align:right;">คูปองมูลค่า 500 บาท</td><td>'.$SumCouponCount[500].'</td><td style="text-align:right;">'.number_format($SumCouponTotal[500], 2).'</td></tr>');
                            print('<tr><td style="text-align:right;">คูปองมูลค่า 1000 บาท</td><td>'.$SumCouponCount[1000].'</td><td style="text-align:right;">'.number_format($SumCouponTotal[1000], 2).'</td></tr>');
                            print('<tr><td style="text-align:right;"><b>รวม:</b>&nbsp;</td><td>'.$finalcouponCount.'</td><td style="text-align:right;">'.number_format($finalsumCoupon, 2).'</td></tr>');

                            print('<tr><td style="text-align:right;">เงินสด</td><td>-</td><td style="text-align:right;">'.number_format($FinalCash, 2).'</td></tr>');
                            print('<tr><td style="text-align:right;">บัตรเครดิต</td><td>'.$CountCard.'</td><td style="text-align:right;">'.number_format($FinalCard, 2).'</td></tr>');
                            print('<tr><td style="text-align:right;">ใบสั่งน้ำมัน</td><td>'.$CountCredit.'</td><td style="text-align:right;">'.number_format($FinalCredit, 2).'</td></tr>');

                            print('<tr><td style="text-align:right;" colspan="2"><b>รวมยอดเงิน:</b>&nbsp;</td><td style="text-align:right;">'.number_format($FinalTotal, 2).'</td></tr>');
                            if($NotClearYet){
                                print('<tr><td style="text-align:right;" colspan="2"><b>รวมยอดเงินสดที่ยังไม่ได้เคลียร์:</b>&nbsp;</td><td style="text-align:right;">'.number_format($NotClearYet, 2).'</td></tr>');
                            }
                            print('</table><br>');

                            $sqlHistory="select Name, sum(Type*Total), Note from ".$db_name.".account_daily where PaidTo=0 and PaidDate>=".$startDate." and PaidDate<=".$endDate." group by Name, Note order by PaidDate ASC;";
                            $rsHistory=mysql_query($sqlHistory);
                            if(mysql_num_rows($rsHistory)){
                                print('<table width="500px" border="1" class="coupon_history">
                                    <tr><th colspan="2">สรุปยอดเงินที่เคลียร์ยอดในวันที่ '.$_REQUEST['payDate']);
                                if($_REQUEST['payDate']!=$_REQUEST['payToDate']){
                                    print(' - '.$_REQUEST['payToDate']);
                                }
                                print('</th></tr>
                                    <tr><th>&nbsp;</th><th>จำนวนเงิน</th></tr>');
                                while($History=mysql_fetch_row($rsHistory)){
                                    if(trim($History[2])){
                                        $History[2]="(".$History[2].")";
                                    }
                                    print('<tr><td style="text-align:right;">'.$History[0].' '.$History[2].'&nbsp;</td><td style="text-align:right;">'.number_format($History[1], 2).'</tr>');
                                }
                                print("</table><br>");
                            }
                            $ApproveAccess='';
                            $sqlWantClearTime="select count(PaymentID) from ".$db_name.".payments where payments.Date>=".$startDate." and payments.Date<=".$endDate.$onlyNoClear." and ClearTime=0 group by SendBy";
                            $rsWantClearTime=mysql_query($sqlWantClearTime.";");
                            $WantClearTime=mysql_num_rows($rsWantClearTime);
                            if($WantClearTime && preg_match('/-13-/', $EmpAccess)){
                                $ApproveAccess='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <button type="button" class="btn btn-primary btn-rounder" id="ClearPaid">เคลียร์รับเงิน</button>';
                            }
                            print('
                        <div style="width:500px;">
                            <div id="actionBar" class="actionBar right">
                                <input type="hidden" id="backPage" value="'.$_REQUEST['back'].'.php">
                                <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                                '.$ApproveAccess);
                            // print('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            //     <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>');
                            print('</div>
                        </div>');
                        }
                        else{
                            print('<p style="color:red; margin:25px 25px 25px 100px;">ไม่มีรายการตามเงื่อนไขที่กำหนด</p>');
                            $sqlHistory="select Name, sum(Type*Total), Note from ".$db_name.".account_daily where PaidTo=0 and PaidDate>=".$startDate." and PaidDate<=".$endDate." group by Name, Note order by PaidDate ASC;";
                            $rsHistory=mysql_query($sqlHistory);
                            if(mysql_num_rows($rsHistory)){
                                print('<table width="500px" border="1" class="coupon_history">
                                    <tr><th colspan="2">สรุปยอดเงินที่เคลียร์ยอดในวันที่ '.$_REQUEST['payDate']);
                                if($_REQUEST['payDate']!=$_REQUEST['payToDate']){
                                    print(' - '.$_REQUEST['payToDate']);
                                }
                                print('</th></tr>
                                    <tr><th>&nbsp;</th><th>จำนวนเงิน</th></tr>');
                                while($History=mysql_fetch_row($rsHistory)){
                                    if(trim($History[2])){
                                        $History[2]="(".$History[2].")";
                                    }
                                    print('<tr><td style="text-align:right;">'.$History[0].' '.$History[2].'&nbsp;</td><td style="text-align:right;">'.number_format($History[1], 2).'</tr>');
                                }
                                print("</table><br>");
                            }
                            // print('
                            // <div id="actionBar" class="actionBar" style="margin-left:430px;">
                            //     <input type="hidden" id="backPage" value="'.$_REQUEST['back'].'.php">
                            //     <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            // </div>');
                        }
                    ?>
                </div>
            </div>
        </div>
    </form>
</section>

<button data-toggle="modal" data-target="#myModal" id="OpenDialog" style="visibility: hidden;"></button>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <form action="oil_record.php" method="post" name="ReceiveIncome" class="form-horizontal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">บันทึกข้อมูลการนำส่ง</h4>
            </div>
            <div class="modal-body">
                <p>ยอดรวมทั้งสิ้น: <?php print(number_format($NotClearYet, 2)); ?> บาท</p>
                <input type="hidden" name="clearTotal" id="clearTotal" value="1">
                <input type="hidden" name="TimeForCheck" id="TimeForCheck" value="<?php print(time()); ?>">
                <input type="hidden" id="NotClearYet" value="<?php print($NotClearYet); ?>">
                <input type="hidden" name="payDate" value="<?php print($_REQUEST["payDate"]); ?>">
                <input type="hidden" name="payToDate" value="<?php print($_REQUEST["payToDate"]); ?>">
                <input type="hidden" name="TypeDisplay" value="<?php print($_REQUEST["TypeDisplay"]); ?>">
                <?php
                $EmpList="";
                $sqlCashier="select concat(FirstName, ' ', LastName), EmpID from employee where Deleted=0 and HoldingMoney=1 order by FirstName ASC, LastName ASC;";
                $rsCashier=mysql_query($sqlCashier);
                while($Cashier=mysql_fetch_row($rsCashier)){
                    $EmpList.="<option value=\"".$Cashier[1]."\">".$Cashier[0]."</option>";
                }
                if($EmpList){
                ?>
                    <p>นำส่งเจ้าของกิจการ: <input type="text" class="form-control inline_input noEnterSubmit price" name="ReceiveIncome" id="ReceiveIncome" value="<?php print(number_format($NotClearYet, 2)); ?>" onchange="javascript:setingHolding(1);" style="width:150px;"> บาท</p>
                    <p>ผู้ถือเงินสดย่อย: <select name="HoldingUser" id="HoldingUser" class="form-control inline_input input-sm" style="width:170px;"><?php print($EmpList); ?></select> &nbsp; จำนวน <input type="text" class="form-control inline_input noEnterSubmit price" name="ReceiveHolding" id="ReceiveHolding" value="" onchange="javascript:setingHolding(2);" style="width:150px;"> บาท</p>
                <?php
                }
                else{
                ?>
                <p>นำส่งเจ้าของกิจการ: <input type="hidden" name="ReceiveIncome" id="ReceiveIncome" value="<?php print(number_format($NotClearYet, 2)); ?>"><?php print(number_format($NotClearYet, 2)); ?> บาท</p>
                <?php
                }
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="IncomeSubmit" onclick="javascript:recordIncome();">บันทึกข้อมูล</button>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="btn btn-danger" data-dismiss="modal">ย้อนกลับ</button>
            </div>
        </form>
    </div>
  </div>
</div>

<?php
}
include("footer.php");
?>