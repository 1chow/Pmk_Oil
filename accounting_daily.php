<?php
include("dbvars.inc.php");
if(!preg_match('/-7-/', $EmpAccess) && !preg_match('/-13-/', $EmpAccess)&& !preg_match('/-11-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

include("header.php");

if(!isset($_REQUEST['payDate'])){
    $_REQUEST['payDate']=date("d/m/Y", time());
    $_REQUEST['payToDate']=date("d/m/Y", time());
}
else{
    $_REQUEST['payToDate']=$_REQUEST['payDate'];
}
if(!isset($_POST['payType'])){
    $_POST['payType']='';
}
if(!isset($_REQUEST["TypeDisplay"])){
    $_REQUEST["TypeDisplay"]=0;
}
if(!isset($_REQUEST['page'])){
    $_REQUEST['page']=1;
}
$SetDate=explode("/", $_REQUEST['payDate']);
$startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
$endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
$ItemPerPage=200;
if(!isset($_POST['RoundNo'])){
    $_POST['RoundNo']=1;
}
?>
<section class="pageContent">
    <form action="accounting_daily.php" method="post" class="form-horizontal" role="form" name="account_daily">
        <div class="title-body">
            <h2>สรุปรายการทางบัญชีประจำวัน</h2>
        </div>

        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                        <input type="hidden" name="verify" value="1">
                        <input type="hidden" name="page" id="page" value="<?php print($_REQUEST['page']); ?>">
                        <h3 class="panel-title" style="margin: 10px 0;">
                        <?php
                            if(isset($_REQUEST['back']) && $_REQUEST['back']){
                                print('<input type="hidden" name="back" value="'.$_REQUEST['back'].'">');
                            }
                            print("สรุปรายการทางบัญชีประจำวัน:");
                            print("&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="payDate" value="'.$_REQUEST['payDate'].'" style="display:inline; width:100px;">');
                            print("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; กะ: &nbsp;<select name=\"RoundNo\" class=\"form-control inline_input\" style=\"width:60px;\"><option value=\"1\"");
                            if($_POST['RoundNo']==1){
                                print(" selected");
                            }
                            print(">1</option><option value=\"2\"");
                            if($_POST['RoundNo']==2){
                                print(" selected");
                            }
                            print(">2</option><option value=\"3\"");
                            if($_POST['RoundNo']==3){
                                print(" selected");
                            }
                            print(">3</option></select>");
                            print('&nbsp;&nbsp;&nbsp;<button type="button" onclick="javascript:document.forms[\'account_daily\'].submit();" class="btn btn-xs btn-primary btn-rounder">GO</button>');
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    <?php
                        $SetDate=explode("/", $_REQUEST['payDate']);
                        $startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
                        $SetToDate=explode("/", $_REQUEST['payToDate']);
                        $endDate=mktime(23, 59, 59, $SetToDate[1], $SetToDate[0], $SetToDate[2]);
                        $sqlHistory="select payments.Date, Type, sum(if(Type='Cash', Amount, 0)), sum(if(Type='Card', Amount, 0)), sum(if(Type='Credit', Amount, 0)), sum(if(Type='Coupon', Amount, 0)), concat(FirstName, ' ', LastName) as EmpName, sum(if(Type='Card', CardSlip, 0)), sum(if(Type='Credit', CardSlip, 0)), sum(CouponCount), CashierInfo, SendBy from ((".$db_name.".payments left join ".$db_name.".employee on employee.EmpID=payments.SendBy) left join ".$db_name.".coupon_used on coupon_used.UsedID=CouponUsedID) where payments.Date>=".$startDate." and payments.Date<=".$endDate." and TimeRound=".intval($_POST['RoundNo'])." group by SendBy, payments.Date";
                        $rsHistory=mysql_query($sqlHistory.";");
                        $HistoryNum=mysql_num_rows($rsHistory);
                        $AllPage=ceil($HistoryNum/$ItemPerPage);

                        $sqlLastHistory=$sqlHistory;
                        $sqlLastHistory.=" order by EmpName ASC, payments.PaymentID ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                        $rsHistory=mysql_query($sqlLastHistory);
                        $CurrentEmp=0;
                        $count=0;
                        $FinaltotalCash=0;
                        $FinaltotalCard=0;
                        $FinaltotalCredit=0;
                        $FinaltotalCoupon=0;
                        $FinaltotalCardNum=0;
                        $FinaltotalCreditNum=0;
                        $FinaltotalCouponNum=0;

                        $sqlDisplay="select concat(FirstName, ' ', LastName), sum(if((Type=1 || Type=3) && account_daily.PaidDate>=".$startDate." and account_daily.PaidDate<=".$endDate.", 1, 0)*Total) as Income, sum(if((Type=2 || Type=4) && account_daily.PaidDate>=".$startDate." and account_daily.PaidDate<=".$endDate.", -1, 0)*Total) as Payment, employee.EmpID from ".$db_name.".account_daily inner join ".$db_name.".employee on employee.EmpID=account_daily.PaidTo where PaidTo>0 and employee.Deleted=0 group by PaidTo order by 1 ASC;";
                        $rsDisplay=mysql_query($sqlDisplay);
                        if(mysql_num_rows($rsHistory) || mysql_num_rows($rsDisplay)){
                            while($History=mysql_fetch_row($rsHistory)){
                                if($CurrentEmp!=$History[11]){
                                    if($CurrentEmp){
                                        print("<tr><td><b>รวม</b></td><td style=\"text-align:right;\">".number_format($totalCash, 2)."</td><td>".number_format($totalCouponNum)."</td><td style=\"text-align:right;\">".number_format($totalCoupon, 2)."</td><td>".number_format($totalCardNum)."</td><td style=\"text-align:right;\">".number_format($totalCard, 2)."</td><td>".number_format($totalCreditNum)."</td><td style=\"text-align:right;\">".number_format($totalCredit, 2)."</td></tr>");
                                        print('</table><br>');
                                    }
                                    print('<p style="margin:15px 0 5px;"><b>ชื่อผู้เก็บ: '.$History[6].'</b></p>');
                                    if($History[10]){
                                        print('<p><b>'.$History[10].'</b></p>');
                                    }
                                    print('<table width="600px" border="1" class="coupon_history">');
                                    print('<tr><th width="10%" rowspan="2">ลำดับที่</th><th width="20%" rowspan="2"><b>เงินสด</b></th><th colspan="2"><b>คูปอง</b></th><th colspan="2"><b>บัตรเครดิต</b></th><th colspan="2"><b>ลูกค้าเครดิต</b></th></tr>');
                                    print('<tr><th><b>ใบ</b></th><th><b>ยอด</b></th><th><b>ใบ</b></th><th><b>ยอด</b></th><th><b>ใบ</b></th><th><b>ยอด</b></th></tr>');
                                    $CurrentEmp=$History[11];
                                    $count=0;
                                    $totalCash=0;
                                    $totalCard=0;
                                    $totalCredit=0;
                                    $totalCoupon=0;
                                    $totalCardNum=0;
                                    $totalCreditNum=0;
                                    $totalCouponNum=0;
                                }
                                $count++;
                                // cash
                                print('<tr><td>'.$count.'</td><td style="text-align:right;">'.number_format($History[2], 2).'</td>');
                                // coupon
                                print('<td>'.number_format($History[9]).'</td><td style="text-align:right;">'.number_format($History[5], 2).'</td>');
                                // card
                                print('<td>'.number_format($History[7]).'</td><td style="text-align:right;">'.number_format($History[3], 2).'</td>');
                                // customer credit
                                print('<td>'.number_format($History[8]).'</td><td style="text-align:right;">'.number_format($History[4], 2).'</td>');
                                print('</tr>');
                                $totalCash+=$History[2];
                                $totalCard+=$History[3];
                                $totalCredit+=$History[4];
                                $totalCoupon+=$History[5];
                                $totalCardNum+=$History[7];
                                $totalCreditNum+=$History[8];
                                $totalCouponNum+=$History[9];

                                $FinaltotalCash+=$History[2];
                                $FinaltotalCard+=$History[3];
                                $FinaltotalCredit+=$History[4];
                                $FinaltotalCoupon+=$History[5];
                                $FinaltotalCardNum+=$History[7];
                                $FinaltotalCreditNum+=$History[8];
                                $FinaltotalCouponNum+=$History[9];
                            }
                            if(mysql_num_rows($rsHistory)){
                                print("<tr><td><b>รวม</b></td><td style=\"text-align:right;\">".number_format($totalCash, 2)."</td><td>".$totalCouponNum."</td><td style=\"text-align:right;\">".number_format($totalCoupon, 2)."</td><td>".$totalCardNum."</td><td style=\"text-align:right;\">".number_format($totalCard, 2)."</td><td>".$totalCreditNum."</td><td style=\"text-align:right;\">".number_format($totalCredit, 2)."</td></tr>");
                                print('</table><br>');
                                print('<br><br>');

                                print("<table style=\"width:250px;\"><tr><td width=\"33%\">เงินสด</td><td width=\"33%\" align=\"right\">".number_format($FinaltotalCash, 2)."</td><td width=\"33%\">&nbsp;</td></tr>");
                                print("<tr><td>คูปอง</td><td align=\"right\">".number_format($FinaltotalCoupon, 2)."</td><td align=\"right\">".number_format($FinaltotalCouponNum)." ใบ</td></tr>");
                                print("<tr><td>บัตรเครดิต</td><td align=\"right\">".number_format($FinaltotalCard, 2)."</td><td align=\"right\">".number_format($FinaltotalCardNum)." ใบ</td></tr>");
                                print("<tr><td>ลูกค้าเครดิต</td><td align=\"right\">".number_format($FinaltotalCredit, 2)."</td><td align=\"right\">".number_format($FinaltotalCreditNum)." ใบ</td></tr>");
                                $SumTotal=($FinaltotalCash+$FinaltotalCoupon+$FinaltotalCard+$FinaltotalCredit);
                                print("<tr><td>รวม</td><td align=\"right\">".number_format($SumTotal, 2)."</td><td>&nbsp;</td></tr>");
                                print("</table><hr>");
                            }

                            $rsDisplay=mysql_query($sqlDisplay);
                            if(mysql_num_rows($rsDisplay)){
                                print('<br>
                                    <table width="60%" border="1" class="coupon_history">
                                    <tr><th colspan="5">ยอดกระแสเงินสดของพนักงานประจำวันที่ '.$_REQUEST['payDate'].'</th></tr>
                                    <tr><th>พนักงาน</th><th>รายรับ</th><th>รายจ่าย</th><th>ส่วนต่าง</th><th>สรุป คงเหลือ/ค้างจ่าย</th></tr>');
                                while($Display = mysql_fetch_row($rsDisplay)){
                                    $Summary=($Display[1]-$Display[2]);
                                    $sqlDisplay2="select sum(if(Type!=1 && Type!=3, -1, 1)*Total), count(account_daily.ID) from ".$db_name.".account_daily inner join ".$db_name.".employee on employee.EmpID=account_daily.PaidTo where PaidTo>0 and employee.Deleted=0 and employee.EmpID=".intval($Display[3])." group by PaidTo order by 1 ASC;";
                                    $rsDisplay2=mysql_query($sqlDisplay2);
                                    $Display2 = mysql_fetch_row($rsDisplay2);
                                    print('<tr><td width="25%" class="text-left">&nbsp;'.$Display[0].'</td>');
                                    print('<td width="25%" class="text-right">'.number_format($Display[1], 2)."&nbsp;</td>");
                                    print('<td width="25%" class="text-right">'.number_format($Display[2], 2)."&nbsp;</td>");
                                    $findDiff=($Display[1]+$Display[2]);
                                    print('<td width="25%" class="text-right">'.number_format($findDiff, 2)."&nbsp;</td>");
                                    print('<td width="25%" class="text-right">');
                                    if($Display2[1] <= 150){
                                        print('<a href="accounting.php?ShowDetails='.$Display[3]);
                                        if(isset($_REQUEST['back'])){
                                            print('&back2page='.$_REQUEST['back']);
                                        }
                                        print('">'.number_format($Display2[0], 2)."</a>");
                                    }
                                    print('&nbsp;</td>');
                                    print('</tr>');
                                }
                                print('<tr><th colspan="4" style="text-align:right;">เงินกองกลางคงเหลือ:&nbsp;&nbsp;</th><th><span id="SystemBalance">'.number_format($SystemBalance, 2).'</span> บาท</th></tr>');
                                print('</table>');
                            }
                            // if(isset($_REQUEST['back']) && $_REQUEST['back']){
                            //     print('<br>
                            //     <div style="width:75%;">
                            //         <div id="actionBar" class="actionBar right">
                            //             <input type="hidden" id="backPage" value="'.$_REQUEST['back'].'.php">
                            //             <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            //         </div>
                            //     </div>');
                            // }
                        }
                        else{
                            print('<p style="color:red; margin:25px 25px 25px 100px;">ไม่มีรายการตามเงื่อนไขที่กำหนด</p>');
                            // if(isset($_REQUEST['back']) && $_REQUEST['back']){
                            //     print('
                            //     <div style="width:75%;" id="actionBar" class="actionBar right">
                            //         <input type="hidden" id="backPage" value="'.$_REQUEST['back'].'.php">
                            //         <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            //     </div>');
                            // }
                        }
                    ?>
                    <br>
                </div>
            </div>
        </div>
    </form>
</section>

<?php
include("footer.php");
?>