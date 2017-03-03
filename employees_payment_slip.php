<?php
include("dbvars.inc.php");
if(!preg_match('/-8-/', $EmpAccess) && !preg_match('/-11-/', $EmpAccess) && $UserID!=1){
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
$lastDay4Payment=intval($_REQUEST["DDay"]);
$SetYear=date("Y", time());
$SetMonth=date("n", time());
if(isset($_REQUEST["TimeSheet"])){
    $Date=$_REQUEST["TimeSheet"];
    $SetSelected=explode('-', $Date);
    $SetYear=$SetSelected[0];
    $SetMonth=$SetSelected[1];
}
else{
    $_REQUEST["TimeSheet"]=$SetYear.'-'.$SetMonth;
}

$SetStartYear=$SetYear;
$SetStartMonth=($SetMonth-1);
if(($SetMonth-1) == 0){
    $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, 12, ($SetYear-1)));
    $SetStartYear=($SetYear-1);
    $SetStartMonth=12;
}
if($lastDay4Payment >= 30){
    $lastDay4Payment=-1;
}
if($lastDay4Payment==(-1)){
    $DayPerMonth = intval(cal_days_in_month(CAL_GREGORIAN, $SetMonth, $SetYear));
    $Day4Start = $SetYear."-".$SetMonth."-1";
    $Day4End = $SetYear."-".$SetMonth."-".$DayPerMonth;
    $Day4StartText = "1 ".$monthList[($SetMonth-1)]." ".($SetYear+543);
    $Day4EndText = $DayPerMonth." ".$monthList[($SetMonth-1)]." ".($SetYear+543);
    $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $SetMonth, $SetYear));
}
else{
    $Day4Start = $SetStartYear."-".$SetStartMonth."-".($lastDay4Payment+1);
    $Day4End = $SetYear."-".$SetMonth."-".$lastDay4Payment;
    $Day4StartText = ($lastDay4Payment+1)." ".$monthList[($SetStartMonth-1)]." ".($SetStartYear+543);
    $Day4EndText = $lastDay4Payment." ".$monthList[($SetMonth-1)]." ".($SetYear+543);
    $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $SetStartMonth, $SetStartYear));
}
?>
<form action="employees_payment.php" method="post" class="form-horizontal" role="form" autocomplete="off">
    <section class="pageContent">
        <div class="title-body">
            <h2>พิมพ์สลิปเงินเดือน</h2>
        </div>

        <div class="content-center" id="EmployeeContent">
        <?php
            print("<p style=\"text-align:center; margin:20px 0 20px;\"><span class=\"payment_page_text\">".$Day4StartText." - ".$Day4EndText."</span></p>");
            $sqlPosition = "select concat(FirstName, ' ', LastName), PositionName, EmpID, CalTax, WorkDays, Incentive from (".$db_name.".employee inner join ".$db_name.".empposition on empposition.PositionID=employee.PositionID) where employee.EmpID!=1 and employee.Deleted=0 and employee.PaidDate=".intval($_REQUEST["DDay"])." order by empposition.OrderNo ASC, FirstName ASC, LastName ASC;";
            $rsPosition = mysql_query($sqlPosition);
            while($Position = mysql_fetch_row($rsPosition)){
                $Paid=0;
                $WorkDays=$Position[4];
                $sqlPayment="SELECT DaysWork, SocialSecurity, Tax, Mistake, LateTime, OT, OTTotal, Bonus, Incentive, Other, TotalPaid, PayID, PayDate, LoanRate, InAdvancePaid, HourWork from ".$db_name.".emppayment where EmpID=".intval($Position[2])." and PayMonth='".intval($SetMonth)."-".intval($SetYear)."' and InAdvance=0 order by PayDate DESC;";
                $rsPayment=mysql_query($sqlPayment);
                $PaymentNum=mysql_num_rows($rsPayment);
                $sqlSalary="SELECT Salary, SalaryType, OTRate, TaxRate from ".$db_name.".empsalary where EmpID=".intval($Position[2])." order by StartDate DESC, ID DESC;";
                $rsSalary=mysql_query($sqlSalary);
                $Salary=mysql_fetch_array($rsSalary);

                $sqlAdvance="SELECT sum(TotalPaid) from ".$db_name.".emppayment where EmpID=".intval($Position[2])." and PayMonth='".intval($SetMonth)."-".intval($SetYear)."' and InAdvance=1;";
                $rsAdvance=mysql_query($sqlAdvance);
                $Advance=mysql_fetch_row($rsAdvance);
                $Paid=$Advance[0];
                $rateTotal=0;
                if($_REQUEST["comeFromReport"]){
                    $PaymentNum=1;
                }
                $Payment=mysql_fetch_array($rsPayment);
                $Paid=0;
                $RateTotal=FixFormat($Salary[0], 1);
                if($Salary[1]=='วัน'){
                    $RateTotal=FixFormat($Payment['DaysWork']*$Salary[0], 1);
                }
                $FindHourRate=round($Salary[2]/$OTHourRate, 3);
                if($Payment['HourWork']){
                    $rateHour=($Payment['HourWork']*$FindHourRate);
                    $RateTotal=FixFormat($RateTotal+$rateHour, 1);
                }

                $Minus=(round($Payment['SocialSecurity'],0)+round($Payment['Tax'],2)+round($Payment['Mistake'],2)+round($Payment['LateTime'],2)+round($Payment['InAdvancePaid'],2)+round($Payment['LoanRate'],2));
                $Plus=(FixFormat($RateTotal, 0)+round($Payment['OTTotal'],2)+round($Payment['Bonus'],2)+round($Payment['Incentive'],2)+round($Payment['Other'],2));
                $FinalTotal=$Plus-$Minus;
                print('<table style="page-break-inside:avoid; border:10px solid white;" width="98%" align="center" class="EmpSlip"><tr><td><table width="100%" border="0" class="payment_page_slip set_left">
                <tr><th colspan="2">สลิบเงินเดือน</th><tr>
                <tr><td align="left" width="90">ชื่อ:</td><td align="left">'.$Position[0].'</td><tr>
                <tr><td align="left">ตำแหน่ง:</td><td align="left">'.$Position[1].'</td><tr>
                <tr><td align="left">เงินเดือน:</td><td align="left">'.FixFormat($Salary[0], 1).' / '.$Salary[1].'</td><tr>
                </table>
                <table width="100%" border="1" class="payment_page_slip">
                <tr>
                    <th rowspan="2">รายรับ</th>
                    <th>วัน / ชั่วโมง</th>
                    <th>อัตรา</th>
                    <th>เป็นเงิน</th>
                    <th>OT(ชม.)</th>
                    <th>OT(เงิน)</th>
                    <th>เบี้ยขยัน</th>
                    <th>incentive</th>
                    <th>อื่นๆ</th>
                    <th>รวมรายรับ</th>
                </tr>
                <tr>
                    <td>'.$Payment['DaysWork'].' / '.$Payment['HourWork'].'</td>
                    <td>'.FixFormat($Salary[0], 1).'</td>
                    <td>'.$RateTotal.'</td>
                    <td>'.$Payment['OT'].'</td>
                    <td>'.FixFormat($Payment['OTTotal'], 1).'</td>
                    <td>'.FixFormat($Payment['Bonus'], 1).'</td>
                    <td>'.FixFormat($Payment['Incentive'], 1).'</td>
                    <td>'.FixFormat($Payment['Other'], 1).'</td>
                    <td>'.FixFormat($Plus, 1).'</td>
                </tr>
                <tr>
                    <th rowspan="2">รายจ่าย</th>
                    <th>ประกันสังคม</th>
                    <th>ภาษี</th>
                    <th>น้ำมันผิด/ของหาย/เงินขาด</th>
                    <th>ขาดงาน/มาสาย</th>
                    <th>ล่วงหน้า</th>
                    <th>เงินกู้</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>รวมรายจ่าย</th>
                </tr>
                <tr>
                    <td>'.FixFormat($Payment['SocialSecurity'], 1).'</td>
                    <td>'.FixFormat($Payment['Tax'], 1).'</td>
                    <td>'.FixFormat($Payment['Mistake'], 1).'</td>
                    <td>'.FixFormat($Payment['LateTime'], 1).'</td>
                    <td>'.FixFormat($Payment['InAdvancePaid'], 1).'</td>
                    <td>'.FixFormat($Payment['LoanRate'], 1).'</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>'.FixFormat($Minus, 1).'</td>
                </tr>
                <tr><td colspan="9">&nbsp;</td><td align="right">&nbsp;'.FixFormat($FinalTotal, 1).'&nbsp;</td></tr>
                </table></td></tr></table><br><br>');
            }
            ?>

            <div id="actionBar" class="actionBar right"><br>
                <?php
                $PrintReport='<button type="button" class="btn btn-info btn-rounder" id="PrintPayment">พิมพ์สลิปเงินเดือน</button>';
                print($PrintReport);
                ?><input type="hidden" id="backPage" value="<?php if($_REQUEST["comeFromReport"]){ print("reports.php"); }else{ print("employees.php"); } ?>">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                <br><br>&nbsp;
            </div>
        </div>
    </section>
</form>
<?php
include("footer.php");
?>