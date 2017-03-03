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
function diff2time($time_a, $time_b, $AddADate, $AddBDate){
    $now_time1=strtotime($time_a.$AddADate);
    $now_time2=strtotime($time_b.$AddBDate);
    //echo $now_time1."****".$now_time2."<br>";
    $time_diff=abs($now_time2-$now_time1);
    return $time_diff;
}

$alertTxt='';
if(isset($_POST["Advance"]) && !intval($_POST['changeDate'])){
    $DateCut = explode("/", trim($_POST["Date2pay"]));
    $PayDate = mktime(0, 0, 0, $DateCut[1], $DateCut[0], $DateCut[2]);
    foreach($_POST["Advance"] as $EmpID => $Total) {
        if($_POST['paymentID'][$EmpID] > 0){
            if(floatval($Total) > 0){
                $sqlInsert="UPDATE ".$db_name.".emppayment SET TotalPaid=".FixFormat($Total, 0).", Note='".mysql_real_escape_string(trim($_POST['AdvanceNote'][$EmpID]))."', PayMonth='".intval($DateCut[1])."-".intval($DateCut[2])."' WHERE emppayment.EmpID = ".intval($EmpID)." and PayID=".intval($_POST['paymentID'][$EmpID]).";";
                $rsInsert=mysql_query($sqlInsert);
            }
            else{
                $sqlDelete="DELETE FROM ".$db_name.".emppayment WHERE PayID=".intval($_POST['paymentID'][$EmpID]).";";
                $rsDelete=mysql_query($sqlDelete);
            }
        }
        else if(floatval($Total) > 0){
            $sqlInsert="INSERT INTO ".$db_name.".emppayment (EmpID, DaysWork, SocialSecurity, Tax, Mistake, LateTime, OT, OTTotal, Bonus, Incentive, Other, TotalPaid, PaidBy, PaidType, Note, PayDate, PayMonth, InAdvance) VALUES (".intval($EmpID).", 0, '0.00', '0.00', '0.00', '0.00', 0, '0.00', '0.00', '0.00', '0.00', ".FixFormat($Total, 0).", ".intval($UserID).", 'cash', '".mysql_real_escape_string(trim($_POST['AdvanceNote'][$EmpID]))."', ".intval($PayDate).", '".intval($DateCut[1])."-".intval($DateCut[2])."', 1);";
            $rsInsert=mysql_query($sqlInsert);
        }
    }
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว.</div>';
}
else if(isset($_POST["total"])){
    $DateCut = explode("/", trim($_POST["Date2pay"]));
    $PayDate = mktime(0, 0, 0, $DateCut[1], $DateCut[0], $DateCut[2]);
    foreach($_POST["total"] as $EmpID => $Total) {
        if($_POST["workDate"][$EmpID]=='-'){
            $_POST["workDate"][$EmpID]=0;
        }
        if($_POST['paymentID'][$EmpID] > 0){
            $sql4Note="select Note from ".$db_name.".emppayment WHERE emppayment.EmpID = ".intval($EmpID)." and PayID=".intval($_POST["paymentID"]).";";
            $rs4Note=mysql_query($sql4Note);
            $Note=mysql_fetch_row($rs4Note);
            $EditNote=$Note[0].'<br>แก้ไขโดย '.$UserName." วันที่ ".date("j-m-Y", time());
            $sqlInsert="UPDATE ".$db_name.".emppayment SET SocialSecurity=".FixFormat($_POST["SS"][$EmpID], 0).", Tax=".FixFormat($_POST["tax"][$EmpID], 0).", Mistake=".FixFormat($_POST["lost"][$EmpID], 0).", LateTime=".FixFormat($_POST["late"][$EmpID], 0).", OT=".FixFormat($_POST["ot"][$EmpID], 0).", OTTotal=".FixFormat($_POST["otTotal"][$EmpID], 0).", Bonus=".FixFormat($_POST["bonus"][$EmpID], 0).", Incentive=".FixFormat($_POST["incentive"][$EmpID], 0).", Other=".FixFormat($_POST["other"][$EmpID], 0).", TotalPaid=".FixFormat($Total, 0).", PaidBy=".intval($UserID).", Note='".$EditNote."', PayDate=".intval($PayDate).", PayMonth='".intval($_POST['paymentMonth'])."-".intval($_POST['paymentYear'])."', LoanRate=".FixFormat($_POST["LoanRate"][$EmpID], 0).", InAdvancePaid=".FixFormat($_POST["paid"][$EmpID], 0).", HourWork=".FixFormat($_POST["HourWork"][$EmpID], 2)." WHERE emppayment.EmpID = ".intval($EmpID)." and PayID=".intval($_POST['paymentID'][$EmpID]).";";
            //echo $_POST["total"][$EmpID]."<br><br><br>";
        }
        else{
            $EditNote='บันทึกโดย '.$UserName." วันที่ ".date("j-m-Y", time());
            $sqlInsert="INSERT INTO ".$db_name.".emppayment (EmpID, DaysWork, SocialSecurity, Tax, Mistake, LateTime, OT, OTTotal, Bonus, Incentive, Other, TotalPaid, PaidBy, PaidType, Note, PayDate, PayMonth, LoanRate, InAdvancePaid, HourWork) VALUES (".intval($EmpID).", ".intval($_POST["workDate"][$EmpID]).", ".FixFormat($_POST["SS"][$EmpID], 0).", ".FixFormat($_POST["tax"][$EmpID], 0).", ".FixFormat($_POST["lost"][$EmpID], 0).", ".FixFormat($_POST["late"][$EmpID], 0).", ".FixFormat($_POST["ot"][$EmpID], 0).", ".FixFormat($_POST["otTotal"][$EmpID], 0).", ".FixFormat($_POST["bonus"][$EmpID], 0).", ".FixFormat($_POST["incentive"][$EmpID], 0).", ".FixFormat($_POST["other"][$EmpID], 0).", ".FixFormat($Total, 0).", ".intval($UserID).", 'cash', '".$EditNote."', ".intval($PayDate).", '".intval($_POST['paymentMonth'])."-".intval($_POST['paymentYear'])."', ".FixFormat($_POST["LoanRate"][$EmpID], 0).", ".FixFormat($_POST["paid"][$EmpID], 0).", ".FixFormat($_POST["HourWork"][$EmpID], 2).");";
        }
        $rsInsert=mysql_query($sqlInsert);
    }
    $_REQUEST['TimeSheet']=$_POST['paymentYear']."-".$_POST['paymentMonth'];
    unset($_REQUEST['action']);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว.</div>';
}

if(isset($_REQUEST['advance']) && intval($_REQUEST['advance'])){
    if(isset($_POST["Date2pay"])){
        $DateCut = explode("/", trim($_POST["Date2pay"]));
        $PayDate = mktime(0, 0, 0, $DateCut[1], $DateCut[0], $DateCut[2]);
    }
    else{
        $PayDate = mktime(0, 0, 0, date('n', time()), date('j', time()), date('Y', time()));
    }
?>

    <section class="pageContent">
        <div class="title-body">
            <h2>พนักงาน</h2>
        </div>
        <div class="content-center" id="EmployeeContent">
            <form id="AdvanceForm" action="employees_payment.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                <input type="hidden" name="advance" value="1">
                <input type="hidden" name="changeDate" id="changeDate" value="0">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title" style="margin: 10px 0;">วันที่เบิกล่วงหน้า &nbsp; <input type="text" class="form-control inline_input Calendar" name="Date2pay" id="dateSelected" value="<?php print(date('d/m/Y', $PayDate)); ?>" style="width:100px;" onchange="javascript:document.getElementById('changeDate').value=1; document.forms['AdvanceForm'].submit();"></h3>
                    </div>
                    <div class="panel-body">
                        <?php print($alertTxt); ?>
                        <table class="table table-condensed table-striped table-default">
                            <tr>
                                <td style="width:45px;">&nbsp;</td>
                                <td style="width:15%;">ชื่อ</td>
                                <td style="width:15%;">ตำแหน่ง</td>
                                <td class="text-center" style="width:15%;">จำนวนเงิน</td>
                                <td class="text-center">หมายเหตุ</td>
                            </tr>
                            <?php
                            $count=1;
                            $sqlPosition = "select concat(FirstName, ' ', LastName), PositionName, employee.EmpID from (employee inner join empposition on empposition.PositionID=employee.PositionID) where employee.EmpID!=1 and employee.Deleted=0 order by FirstName ASC, LastName ASC;";
                            $rsPosition = mysql_query($sqlPosition);
                            while($Position = mysql_fetch_row($rsPosition)){
                                $sqlPayment="SELECT TotalPaid, Note, PayID from ".$db_name.".emppayment where EmpID=".intval($Position[2])." and PayDate='".intval($PayDate)."' order by PayDate DESC;";
                                $rsPayment=mysql_query($sqlPayment);
                                $Payment=mysql_fetch_row($rsPayment);
                                if($Payment[2]){
                                    $Advance=number_format($Payment[0], 2);
                                    $AdvanceNote=$Payment[1];
                                    $PayID=$Payment[2];
                                }else{
                                    $Advance='';
                                    $AdvanceNote='';
                                    $PayID=0;
                                }
                                print('
                                <tr>
                                    <td>'.$count.'.</td>
                                    <td>'.$Position[0].'</td>
                                    <td>'.$Position[1].'</td>
                                    <td><input type="text" name="Advance['.$Position[2].']" class="form-control price text-right" value="'.$Advance.'"></td>
                                    <td>
                                        <input type="text" name="AdvanceNote['.$Position[2].']" class="form-control" value="'.$AdvanceNote.'">
                                        <input type="hidden" name="paymentID['.$Position[2].']" value="'.$PayID.'">
                                    </td>
                                </tr>');
                                $count++;
                            }
                            ?>
                        </table>
                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" value="employees.php">
                            <button type="submit" class="btn btn-success btn-rounder">บันทึก</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="reset" class="btn btn-danger btn-rounder">ล้างข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
<?php
}
else if(isset($_REQUEST['action']) && $_REQUEST['action']=='Advance'){
    $SetYear=date("Y", time());
    $SetMonth=date("n", time());
    if(isset($_POST['AdvanceMonth'])){
        $SetYear=$_POST['AdvanceYear'];
        $SetMonth=$_POST['AdvanceMonth'];
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>รายงานสรุปการเบิกเงินล่วงหน้า</h2>
        </div>
        <div class="content-center" id="EmployeeContent">
            <form name="AdvanceForm" action="employees_payment.php" method="post">
                <input type="hidden" name="action" value="Advance">
                <p>&nbsp;</p>
                <?php
                print("<div class=\"form-group\" style=\"text-align:center;\">เดือน: <select name=\"AdvanceMonth\" class=\"form-control input-sm\" style=\"display:inline; width:90px;\" onchange=\"document.forms['AdvanceForm'].submit();\">");
                for($i=0; $i<count($monthList); $i++){
                    print("<option value=\"".($i+1)."\"");
                    if(($i+1) == $SetMonth){
                        print(" selected");
                    }
                    print(">".$monthList[$i]."</option>");
                }
                print("</select>");
                print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
                print("ปี: <select name=\"AdvanceYear\" class=\"form-control input-sm\" style=\"display:inline; width:100px;\" onchange=\"document.forms['AdvanceForm'].submit();\">");
                for($i=(date("Y", time())-1); $i<=(date("Y", time())+1); $i++){
                    print("<option value=\"".$i."\"");
                    if($i == $SetYear){
                        print(" selected");
                    }
                    print(">".($i+543)."</option>");
                }
                print("</select></div>");

                print('
                <table width="100%" border="1" class="coupon_history" style="margin:0 auto;">
                    <tr>
                        <th width="10%">ลำดับที่</th>
                        <th width="25%">ชื่อ</th>
                        <th width="20%">วันที่เบิก</th>
                        <th width="20%">มูลค่าที่เบิก</th>
                        <th width="25%">หมายเหตุ</th>
                    </tr>');
                $count=1;
                $sqlAdvance="SELECT concat(FirstName, ' ', LastName), TotalPaid, Note, PayDate from ".$db_name.".emppayment inner join employee on emppayment.EmpID=employee.EmpID where PayMonth='".intval($SetMonth)."-".intval($SetYear)."' and InAdvance=1 order by PayDate ASC;";
                $rsAdvance=mysql_query($sqlAdvance);
                if(mysql_num_rows($rsAdvance)){
                    while($Advance=mysql_fetch_row($rsAdvance)){
                        print('
                        <tr>
                            <td>'.$count.'</td>
                            <td class="text-left">'.$Advance[0].'</td>
                            <td>'.date('j-m', $Advance[3]).'-'.(date('Y', $Advance[3])+543).'</td>
                            <td class="text-right">'.number_format($Advance[1], 2).'&nbsp;&nbsp;</td>
                            <td>'.$Advance[2].'</td>
                        </tr>');
                        $count++;
                    }
                }
                else{
                    print('<tr><td colspan="5" class="passcode_send-error" style="padding:10px;">ไม่มีการเบิกล่วงหน้าในเดือนที่กำหนด</td></tr>');
                }
                print('</table>');
                ?>
                <br>
                <div class="actionBar right">
                    <input type="hidden" id="backPage" value="reports.php">
                    <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                </div>
            </form>
        </div>
    </section>
<?php
}
else{
    if(isset($_REQUEST['action']) && $_REQUEST['action']=='view'){
        $headerText="รายงานสรุปเวลาทำงาน/ค่าแรงประจำเดือน";
        $comeFromReport=1;
        print("<input type=\"hidden\" name=\"comeFromReport\" id=\"comeFromReport\" value=\"1\">");
        $PaymentDateArr=explode(",", $lastDay4Payment);
        if(count($PaymentDateArr)>1){
            $DDayOption=" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <select name=\"DDay\" id=\"DDay\" class=\"form-control input-sm\" style=\"display:inline; width:140px;\">";
            if(!isset($_REQUEST["DDay"])){
                $_REQUEST["DDay"]=$PaymentDateArr[0];
            }
            if($PaymentDateArr[0]){
                $DDayOption.="<option value=\"".$PaymentDateArr[0]."\"";
                if($_REQUEST["DDay"]==$PaymentDateArr[0]){
                     $DDayOption.=" selected";
                }
                $DDayOption.="> รอบ";
                if($PaymentDateArr[0]==(-1)){
                    $DDayOption.="วันสิ้นเดือน";
                }
                else{
                    $DDayOption.="วันที่ ".$PaymentDateArr[0];
                }
                $DDayOption.="</option>";
            }
            if($PaymentDateArr[1]){
                $DDayOption.="<option value=\"".$PaymentDateArr[1]."\"";
                if($_REQUEST["DDay"]==$PaymentDateArr[1]){
                     $DDayOption.=" selected";
                }
                $DDayOption.="> รอบ";
                if($PaymentDateArr[1]==(-1)){
                    $DDayOption.="วันสิ้นเดือน";
                }
                else{
                    $DDayOption.="วันที่ ".$PaymentDateArr[1];
                }
                $DDayOption.="</option>";
            }
            $DDayOption.="</select>";
        }
        else{
            $DDayOption="<input type=\"hidden\" name=\"DDay\" id=\"DDay\" value=\"".$PaymentDateArr[0]."\">";
            $_REQUEST["DDay"]=$PaymentDateArr[0];
        }
    }
    else{
        $headerText="พนักงาน";
        $comeFromReport=0;
        $DDayOption='<input type="hidden" name="DDay" id="DDay" value="'.$_REQUEST['DDay'].'">';
    }
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

        $StartIncentive=mktime(0, 0, 0, $SetMonth, 1, $SetYear);
        $EndIncentive=mktime(23, 59, 59, $SetMonth, $DayPerMonth, $SetYear);
    }
    else{
        $Day4Start = $SetStartYear."-".$SetStartMonth."-".($lastDay4Payment+1);
        $Day4End = $SetYear."-".$SetMonth."-".$lastDay4Payment;
        $Day4StartText = ($lastDay4Payment+1)." ".$monthList[($SetStartMonth-1)]." ".($SetStartYear+543);
        $Day4EndText = $lastDay4Payment." ".$monthList[($SetMonth-1)]." ".($SetYear+543);
        $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $SetStartMonth, $SetStartYear));

        $StartIncentive=mktime(0, 0, 0, $SetStartMonth, ($lastDay4Payment+1), $SetStartYear);
        $EndIncentive=mktime(23, 59, 59, $SetMonth, $lastDay4Payment, $SetYear);
    }

    $count=0;
    $HolidayAr = array();
    $sqlHoliday="SELECT Date, Month from ".$db_name.".Holiday order by Month ASC, Date ASC;";
    $rsHoliDay=mysql_query($sqlHoliday);
    while($Holiday=mysql_fetch_row($rsHoliDay)){
        $HolidayArr[$count]=$SetYear.'-'.sprintf("%02d", $Holiday[1]).'-'.sprintf("%02d", $Holiday[0]);
        if(($Holiday[1]==12) && (($SetMonth-1) == 0)){
            $HolidayArr[$count]=($SetYear-1).'-12-'.sprintf("%02d", $Holiday[0]);
        }
        $count++;
    }
    // incentive calculate start
    $AllIncentive=0;
    $IncentiveList='0';
    $sqlEmpIncebtive="SELECT EmpID from ".$db_name.".employee where employee.EmpID!=1 and Deleted=0 and Incentive=1;";
    $rsEmpIncentive=mysql_query($sqlEmpIncebtive);
    while($EmpIncentive=mysql_fetch_row($rsEmpIncentive)){
        $IncentiveList.=",".$EmpIncentive[0];
    }
    for($i=1; $i<=$DayPerMonth; $i++){
        $DayCheck = $SetYear."-".$SetMonth."-".$i;
        if($lastDay4Payment==-1){
            $DayCheck = $SetYear."-".$SetMonth."-".$i;
        }
        else if($i>$lastDay4Payment){
            $DayCheck = $SetStartYear."-".$SetStartMonth."-".$i;
        }
        $sqlIncentiveDay="SELECT count(DISTINCT EmpID) from ".$db_name.".emptime WHERE  Date='".$DayCheck."' and EmpID in(".$IncentiveList.") and TakeOff!=1;";
        $rsIncentiveDay=mysql_query($sqlIncentiveDay);
        $IncentiveCount=mysql_fetch_row($rsIncentiveDay);
        $AllIncentive+=$IncentiveCount[0];
    }
    $sqlWashService="SELECT sum(QTY*UnitPrice)-DiscountVal from (".$db_name.".car_service inner join ".$db_name.".car_service_detail on car_service_detail.ServiceID=car_service.ID) where ServiceDate>=".intval($StartIncentive)." and ServiceDate<=".intval($EndIncentive)." and ServiceType=1 and car_service.Deleted=0 order by ServiceDate ASC;";
    $rsWashService=mysql_query($sqlWashService);
    $WashService=mysql_fetch_row($rsWashService);
    $WashIncome=$WashService[0];

?>
<form action="employees_payment.php" method="post" class="form-horizontal" role="form" autocomplete="off" onsubmit="javascript:if(!document.getElementById('dateSelected').value){ alert('กรุณาใส่วันที่จ่ายเงินค่าแรง'); return false; }else{ return true; }">
    <section class="pageContent">
        <div class="title-body">
            <h2><?php print($headerText); ?></h2>
        </div>

        <div class="content-center" id="EmployeeContent">
            <input type="hidden" name="action" id="action" value="<?php if(isset($_REQUEST['action'])){ print($_REQUEST['action']); } ?>">
            <p>&nbsp;</p>
            <?php
            if(isset($_REQUEST['action']) && $_REQUEST['action']=='Edit'){
                print('<input type="hidden" name="editPayment" value="1">');
            }
            print("<div id=\"paymentOption\" class=\"form-group\" style=\"text-align:center;\">เดือน: <select id=\"payment4Month\" name=\"paymentMonth\" class=\"form-control input-sm\" style=\"display:inline; width:90px;\">");
            for($i=0; $i<count($monthList); $i++){
                print("<option value=\"".($i+1)."\"");
                if(($i+1) == $SetMonth){
                    print(" selected");
                }
                print(">".$monthList[$i]."</option>");
            }
            print("</select>");
            print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
            print("ปี: <select id=\"payment4Year\" name=\"paymentYear\" class=\"form-control input-sm\" style=\"display:inline; width:100px;\">");
            for($i=(date("Y", time())-1); $i<=(date("Y", time())+1); $i++){
                print("<option value=\"".$i."\"");
                if($i == $SetYear){
                    print(" selected");
                }
                print(">".($i+543)."</option>");
            }
            print("</select>".$DDayOption."</div>");
            print("<p style=\"text-align:center; margin:0 0 20px;\"><span class=\"payment_page_text\">".$Day4StartText." - ".$Day4EndText."</span></p>");
            print($alertTxt);
            ?>
            <table border="1" class="payment_page_text">
                <tr>
                    <td width="100" rowspan="2">ชื่อ</td>
                    <td width="100" rowspan="2">แผนก</td>
                    <td width="100" rowspan="2">อัตรา</td>
                    <td width="100" rowspan="2">แรง</td>
                    <td width="100" rowspan="2">ชั่วโมง</td>
                    <td width="100" rowspan="2">รวม</td>
                    <td width="100" rowspan="2">ประกันสังคม</td>
                    <td width="100" rowspan="2">ภาษี</td>
                    <td width="100">น้ำมันผิด</td>
                    <td width="100">มาสาย</td>
                    <td width="100" rowspan="2">ล่วงหน้า</td>
                    <td width="100" rowspan="2">เงินกู้</td>
                    <td width="100" rowspan="2">OT</td>
                    <td width="100" rowspan="2">เป็นเงิน</td>
                    <td width="100" rowspan="2">เบี้ยขยัน</td>
                    <td width="100" rowspan="2">INCENTIVE</td>
                    <td width="100" rowspan="2">อื่นๆ</td>
                    <td width="100" rowspan="2">คงเหลือ</td>
                </tr>
                <tr>
                    <td width="100">ของหาย</td>
                    <td width="100">ขาดงาน</td>
                </tr>
                <?php
                $PaymentNum=0;
                $sqlPosition = "select concat(FirstName, ' ', LastName), PositionName, EmpID, CalTax, WorkDays, Incentive from (".$db_name.".employee inner join ".$db_name.".empposition on empposition.PositionID=employee.PositionID) where employee.EmpID!=1 and employee.Deleted=0 and employee.PaidDate=".intval($_REQUEST["DDay"])." order by empposition.OrderNo ASC, FirstName ASC, LastName ASC;";
                $rsPosition = mysql_query($sqlPosition);
                while($Position = mysql_fetch_row($rsPosition)){
                    $Paid=0;
                    $WorkDays=$Position[4];
                    $sqlPayment="SELECT DaysWork, SocialSecurity, Tax, Mistake, LateTime, OT, OTTotal, Bonus, Incentive, Other, TotalPaid, PayID, PayDate, LoanRate, InAdvancePaid, HourWork, SalaryRate, HourRate from ".$db_name.".emppayment where EmpID=".intval($Position[2])." and PayMonth='".intval($SetMonth)."-".intval($SetYear)."' and InAdvance=0 order by PayDate DESC;";
                    $rsPayment=mysql_query($sqlPayment);
                    $PaymentNum+=mysql_num_rows($rsPayment);
                    $sqlSalary="SELECT Salary, SalaryType, OTRate, TaxRate from ".$db_name.".empsalary where EmpID=".intval($Position[2])." order by StartDate DESC, ID DESC;";
                    $rsSalary=mysql_query($sqlSalary);
                    $Salary=mysql_fetch_array($rsSalary);
                    $FindHourRate=round($Salary[2]/$OTHourRate, 3);

                    $sqlAdvance="SELECT sum(TotalPaid) from ".$db_name.".emppayment where EmpID=".intval($Position[2])." and PayMonth='".intval($SetMonth)."-".intval($SetYear)."' and InAdvance=1;";
                    $rsAdvance=mysql_query($sqlAdvance);
                    $Advance=mysql_fetch_row($rsAdvance);
                    $Paid=$Advance[0];
                    $rateTotal=0;
                    if($comeFromReport){
                        $PaymentNum=1;
                    }
                    if(!$PaymentNum || (isset($_REQUEST['action']) && $_REQUEST['action']=='Edit')){
                        if($PaymentNum){
                            $Payment=mysql_fetch_array($rsPayment);
                            if(!intval($Payment["SalaryRate"])){
                                $Payment["SalaryRate"]=$Salary[0];
                                $Payment["HourRate"]=$FindHourRate;
                            }
                            $PayDate=$Payment[12];
                            $rateTotal=$Payment["SalaryRate"];
                            $HourWork = $Payment['HourWork'];
                            if($Salary[1]=='วัน'){
                                $rateHour=$HourWork*$Payment["HourRate"];
                                $rateTotal=FixFormat(($Payment['DaysWork']*$Payment["SalaryRate"])+$rateHour, 0);
                            }
                            $count = $Payment['DaysWork'];
                            $CalSS = FixFormat($Payment['SocialSecurity'], 0);
                            $Tax = FixFormat($Payment['Tax'], 0);
                            $CalOT = FixFormat($Payment['OTTotal'], 0);
                            $OTTime = $Payment['OT'];
                            $Bonus = FixFormat($Payment['Bonus'], 0);
                            $Other = FixFormat($Payment['Other'], 0);
                            $Lost = FixFormat($Payment['Mistake'], 0);
                            $Late = FixFormat($Payment['LateTime'], 0);
                            $Paid = FixFormat($Payment['InAdvancePaid'], 0);
                            $Incentive = FixFormat($Payment['Incentive'], 0);
                            $total = FixFormat($Payment['TotalPaid'], 0);
                            $PayID = $Payment['PayID'];
                            $LoanRate = $Payment['LoanRate'];
                        }
                        else{
                            $count='-';
                            $CalSS=0;
                            $Tax=$Salary[3];
                            $CalOT=0;
                            $OTTime=0;
                            $Bonus=0;
                            $Other=0;
                            $Lost=0;
                            $Late=0;
                            $noBonus=0;
                            $Incentive=0;
                            $hourLate=0;
                            $dayLate=0;
                            $Missing=0;
                            $HoliDayCount=0;
                            $StartDate=0;
                            $PayID=0;
                            $LoanRate=0;
                            $Advance[0];
                            $IncentiveDay=0;
                            $HourWork=0;
                            $sqlTimeAdd="SELECT ClockIn, ClockOut, emptime.Date, TakeOff, RoundNo FROM ".$db_name.".emptime WHERE EmpID=".intval($Position[2])." and Date BETWEEN '".$Day4Start."' AND '".$Day4End."' ORDER BY Date ASC;";
                            $rsTimeAdd=mysql_query($sqlTimeAdd);
                            if(mysql_num_rows($rsTimeAdd)){
                                //if($Salary[1]=='วัน'){
                                    $count=0;
                                    $EarlierH=0;
                                    // 10.5, 7, 3.5
                                    $Time4In = array(1 => 7.30, 2 => 18.00, 3 => 4.00);
                                    $Time4Out = array(1 => 18.00, 2 => 1.00, 3 => 7.30);
                                    $Time4InTxt = array(1 => "7.30:00", 2 => "18:00:00", 3 => "4:00:00");
                                    for($i=1; $i<=$DayPerMonth; $i++){
                                        $checkRecord=0;
                                        $Hour2Round2=0;
                                        $DayCheck = $SetYear."-".$SetMonth."-".$i;
                                        $StartDate=mktime(23, 59, 59, $SetMonth, $i, $SetYear);
                                        if($lastDay4Payment==-1){
                                            $DayCheck = $SetYear."-".$SetMonth."-".$i;
                                        }
                                        else if($i>$lastDay4Payment){
                                            $DayCheck = $SetStartYear."-".$SetStartMonth."-".$i;
                                            $StartDate=mktime(23, 59, 59, $SetStartMonth, $i, $SetStartYear);
                                        }
                                        $sqlTime="SELECT ClockIn, ClockOut, emptime.Date, TakeOff, RoundNo FROM ".$db_name.".emptime WHERE EmpID=".intval($Position[2])." and Date='".$DayCheck."' ORDER BY Date ASC, RoundNo ASC;";
                                        $rsTime=mysql_query($sqlTime);
                                        $TimeNum=mysql_num_rows($rsTime);
                                        $checkDay4Work=date('N', $StartDate);
                                        if((!mysql_num_rows($rsTime)) && preg_match('/'.$checkDay4Work.'/', $WorkDays) && ($StartDate && time()>=$StartDate)){
                                            // ขาดงานไม่ได้แจ้งล่วงหน้า
                                            $Missing++;
                                            $noBonus=1;
                                        }
                                        else if(time() <= $StartDate){
                                            $noBonus=1;
                                        }
                                        else if(intval($TimeNum)){
                                            if(intval($Position[5])){
                                                $IncentiveDay++;
                                            }
                                            while($TimeRec=mysql_fetch_array($rsTime)){
                                                $HolidayMark=0;
                                                $AddH=0;
                                                $Round=0;
                                                $calHour=0;
                                                $ClockInText=preg_replace("#:#", ".", substr($TimeRec[0], 0, -3));
                                                $ClockOutText=preg_replace("#:#", ".", substr($TimeRec[1], 0, -3));
                                                $TakeOff[$TimeRec[2]] = $TimeRec[3];
                                                $checkRecord++;

                                                if(intval($TimeRec[4])){
                                                    $Round=$TimeRec[4];
                                                }
                                                else if($ClockInText >= 17.30){ // กะ 2
                                                    $Round=2;
                                                }
                                                else if($ClockInText >= 7){ // กะ 1
                                                    $Round=1;
                                                }
                                                else if($ClockInText){ // กะ 3
                                                    $Round=3;
                                                }
                                                if($Round==1 || $Round==2){
                                                    $count++;
                                                    // วันหยุดพิเศษ คิด 2 แรง
                                                    if(in_array($TimeRec[2], $HolidayArr)){
                                                        $beforeHoliday=date('Y-m-d', strtotime($DayCheck.' -'.$NoDayOff.' day'));
                                                        $afterHoliday=date('Y-m-d', strtotime($DayCheck.' +'.$NoDayOff.' day'));
                                                        $sqlCheckDayOff="select Date FROM ".$db_name.".emptime WHERE EmpID=".intval($Position[2])." and Date>='".$beforeHoliday."' and Date<='".$afterHoliday."' and TakeOff!=1;";
                                                        $rsCheckDayOff=mysql_query($sqlCheckDayOff);
                                                        $CheckDayOff=mysql_num_rows($rsCheckDayOff);
                                                        $ConditionCheck=(($NoDayOff*2)+1);
                                                        if($CheckDayOff == $ConditionCheck){
                                                            $HoliDayCount++;
                                                            $HolidayMark=1;
                                                        }
                                                    }
                                                }
                                                if($Salary[1]=='วัน'){
                                                    $calEarlier=0;
                                                    if($Round!=2 && $ClockOutText<$Time4Out[$Round]){
                                                        $calEarlier=1;
                                                        $KeepEarlier2=0;
                                                    }
                                                    else if($Round==2 && ($ClockOutText<1 || $ClockOutText>18)){
                                                        $calEarlier=1;
                                                        $KeepEarlier2=0;
                                                    }
                                                    else if($Round==2){ // กะ 2 ไม่ได้ออกก่อน เก็บชั่วโมงไว้เผื่อ กะ 3 ออกก่อน
                                                        $KeepEarlier2=1;
                                                    }
                                                    else if(!$KeepEarlier2 && $Round==3 && $ClockOutText=$Time4Out[$Round]){
                                                        $calEarlier=1;
                                                        $KeepEarlier2=0;
                                                    }
                                                    if(intval($calEarlier) || $KeepEarlier2){ // คำนวณออกก่อนเวลา
                                                        if($count < 0){
                                                            $count=0;
                                                        }
                                                        $Day2Check=$DayCheck;
                                                        $time_a=$Day2Check." ".$Time4InTxt[$Round];
                                                        $time_b=$Day2Check." ".$TimeRec[1];
                                                        $AddMoreDate=0;
                                                        $AddADate="";
                                                        $AddBDate="";
                                                        if(intval($Round) == 3){
                                                            $AddADate=" +1 Day";
                                                            $AddBDate=" +1 Day";
                                                        }
                                                        else if(intval($Round)==2 && $ClockOutText<=1){ // เกินเที่ยงคืน แต่ไม่เกินตีหนึ่ง
                                                            $AddBDate=" +1 Day";
                                                        }
                                                        if(!$KeepEarlier2 || ($Round==2 && $checkRecord==$TimeNum)){
                                                            $AddH=diff2time($time_a, $time_b, $AddADate, $AddBDate);
                                                            $EarlierH+=$AddH;
                                                        }
                                                        else if($Round==2){
                                                            $Hour2Round2=diff2time($time_a, $time_b, $AddADate, $AddBDate);
                                                        }
                                                        if($Round==3 && !$KeepEarlier2){
                                                            $EarlierH+=$Hour2Round2;
                                                        }
                                                    }
                                                    if(($Round==1 && $AddH) || ($Round==2 && $AddH) || ($Round==3 && $AddH && $Hour2Round2)){
                                                        $count--;
                                                        if($HolidayMark){
                                                            $HoliDayCount--;
                                                        }
                                                        //echo $Round."===".$AddH."<br>";
                                                    }
                                                }
                                                if($Salary[1]=='วัน' && !$TakeOff[$TimeRec[2]]){ // ไม่ได้แจ้งสาย เช็คเวลาว่าสายหรือไม่
                                                    // check late time
                                                    $checkLate=($ClockInText-$Time4In[$Round]);
                                                    if($checkLate > 0.05){ // สายเกิน 5 นาที
                                                        $dayLate++;
                                                        $noBonus=1;
                                                    }
                                                    else if($checkLate > 0){ // สายไม่เกิน 5 นาที
                                                        $hourLate++;
                                                        $noBonus=1;
                                                    }
                                                }
                                            }
                                        }
                                        else{ // หยุดงาน
                                            $noBonus=1;
                                        }
                                    }
                                    if(!$noBonus){
                                        $Bonus=$BonusRate;
                                    }
                                    else if($Salary[1]=='วัน'){ // ขาดงานโดยไม่แจ้งลา หัก 1 แรง
                                        $dayLate+=($Missing*3);
                                    }
                                    $rateHour=0;
                                    if($Salary[1]=='วัน' && $EarlierH){
                                        $time_diff_h=floor($EarlierH/3600); // จำนวนชั่วโมงที่ต่างกัน
                                        $time_diff_m=floor(($EarlierH%3600)/60); // จำวนวนนาทีที่ต่างกัน
                                        $HourWork=$time_diff_h.".".$time_diff_m;
                                        $rateHour=($HourWork*$FindHourRate);
                                    }
                                    $rateTotal=($count*$Salary[0])+($HoliDayCount*$Salary[0])+($rateHour);
                                    $CalOT=($OTTime*$Salary[2]);
                                    $calHourLate=floor($hourLate/3)*$Salary[2];
                                    $calDayLate=floor($dayLate/3)*$Salary[0];
                                    $Late=(round($calHourLate, 2)+round($calDayLate, 2));
                                // }
                                // else{
                                //     $count="-";
                                // }
                                if($Salary[1]=='เดือน'){
                                    $rateTotal=$Salary[0];
                                    if($HoliDayCount){
                                        $Other=round(round($Salary[0]/$DayPerMonth, 2)*$HoliDayCount, 2);
                                    }
                                }
                                $count+=$HoliDayCount;
                            }
                            if($Position[3] && $rateTotal>=$MinSalarySS){
                                $CalSS = round(($rateTotal*$SSRate)/100, 0);
                                if($CalSS > $MaxSSRate){
                                    $CalSS=$MaxSSRate;
                                }
                            }
                            if($AllIncentive){
                                $Incentive=($WashIncome/$AllIncentive)*$IncentiveDay;
                            }
                            //echo $WashIncome."|||||".$AllIncentive."|||||".$IncentiveDay;
                            $Minus=(round($CalSS,0)+round($Tax,2)+round($Lost,2)+round($Late,2)+round($Paid,2));
                            $Plus=(round($CalOT,2)+round($Bonus,2)+round($Incentive,2)+round($Other,2));
                            $total=$rateTotal-$Minus+$Plus;
                        }
                    ?>

                    <tr id="<?php print($Position[2]); ?>">
                        <td class="text-left">
                            <?php print($Position[0]); ?>
                        </td>
                        <td class="text-left">
                            <?php print($Position[1]); ?>
                        </td>
                        <td class="text-right">&nbsp;<?php print(FixFormat($Salary[0], 1)); ?>&nbsp;</td>

                        <td>&nbsp;<input type="hidden" id="OTRate-<?php print($Position[2]); ?>" value="<?php print($Salary[2]); ?>"><input type="hidden" name="workDate[<?php print($Position[2]); ?>]" value="<?php print($count); ?>"><?php print($count); ?>&nbsp;</td>

                        <td><input type="hidden" name="HourWork[<?php print($Position[2]); ?>]" value="<?php print($HourWork); ?>"><?php print($HourWork); ?>&nbsp;</td>

                        <td>&nbsp;<?php print('<span id="rateTotal-'.$Position[2].'">'.FixFormat($rateTotal, 1).'</span>'); ?>&nbsp;</td>

                        <td><input type="text" id="SS-<?php print($Position[2]); ?>" name="SS[<?php print($Position[2]); ?>]" class="payment_form middle price" value="<?php print(FixFormat($CalSS, 0)); ?>"></td>

                        <td><input type="text" id="tax-<?php print($Position[2]); ?>" name="tax[<?php print($Position[2]); ?>]" class="payment_form middle price" value="<?php print(FixFormat($Tax, 1)); ?>"></td>

                        <td><input type="text" id="lost-<?php print($Position[2]); ?>" name="lost[<?php print($Position[2]); ?>]" class="payment_form middle price" value="<?php print(FixFormat($Lost, 1)); ?>"></td>

                        <td><input type="text" id="late-<?php print($Position[2]); ?>" name="late[<?php print($Position[2]); ?>]" class="payment_form middle price" value="<?php print(FixFormat($Late, 1)); ?>"></td>

                        <td><input type="text" id="paid-<?php print($Position[2]); ?>" name="paid[<?php print($Position[2]); ?>]" class="payment_form price" value="<?php print(FixFormat($Paid, 1)); ?>"></td>

                        <td><input type="text" id="LoanRate-<?php print($Position[2]); ?>" name="LoanRate[<?php print($Position[2]); ?>]" class="payment_form price" value="<?php print(FixFormat($LoanRate, 1)); ?>"></td>

                        <td><input type="text" id="ot-<?php print($Position[2]); ?>" name="ot[<?php print($Position[2]); ?>]" class="payment_form mini" value="<?php print($OTTime); ?>"></td>

                        <td><input type="hidden" id="otTotal-<?php print($Position[2]); ?>" name="otTotal[<?php print($Position[2]); ?>]" value="<?php print(FixFormat($CalOT, 1)); ?>"><span id="otTotal2-<?php print($Position[2]); ?>"><?php print(FixFormat($CalOT, 1)); ?></span></td>

                        <td><input type="text" id="bonus-<?php print($Position[2]); ?>" name="bonus[<?php print($Position[2]); ?>]" class="payment_form price" value="<?php print(FixFormat($Bonus, 1)); ?>"></td>

                        <td><input type="text" id="incentive-<?php print($Position[2]); ?>" name="incentive[<?php print($Position[2]); ?>]" class="payment_form price" value="<?php print(FixFormat($Incentive, 1)); ?>"></td>

                        <td><input type="text" id="other-<?php print($Position[2]); ?>" name="other[<?php print($Position[2]); ?>]" class="payment_form price" value="<?php print(FixFormat($Other, 1)); ?>"></td>

                        <td class="text-right">&nbsp;
                            <input type="hidden" id="total-<?php print($Position[2]); ?>" name="total[<?php print($Position[2]); ?>]" value="<?php print($total); ?>">
                            <input type="hidden" name="paymentID[<?php print($Position[2]); ?>]" value="<?php print($PayID); ?>">
                            <span id="showTotal-<?php print($Position[2]); ?>"><?php print(FixFormat($total, 1)); ?></span>
                            &nbsp;
                        </td>
                    </tr>
                    <?php
                    }
                    else{
                        $Payment=mysql_fetch_array($rsPayment);
                        $Paid=0;
                        if(!intval($Payment["SalaryRate"])){
                            $Payment["SalaryRate"]=$Salary[0];
                            $Payment["HourRate"]=$FindHourRate;
                        }
                        $RateTotal=FixFormat($Payment["SalaryRate"], 1);
                        if($Salary[1]=='วัน'){
                            $RateTotal=FixFormat($Payment['DaysWork']*$Payment["SalaryRate"], 1);
                        }
                        $RateTotal=FixFormat($RateTotal+($Payment['HourWork']*$Payment["HourRate"]), 1);
                        // else{
                        //     $Payment['DaysWork']="-";
                        // }
                        print('
                        <tr>
                        <td class="text-left">&nbsp;'.$Position[0].'</td>
                        <td class="text-left">&nbsp;'.$Position[1].'</td>
                        <td class="text-right">&nbsp;'.FixFormat($Salary[0], 1).'&nbsp;</td>
                        <td>&nbsp;'.$Payment['DaysWork'].'&nbsp;</td>
                        <td>&nbsp;'.$Payment['HourWork'].'&nbsp;</td>
                        <td class="text-right">&nbsp;'.$RateTotal.'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['SocialSecurity'], 1).'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['Tax'], 1).'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['Mistake'], 1).'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['LateTime'], 1).'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['InAdvancePaid'], 1).'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['LoanRate'], 1).'&nbsp;</td>
                        <td class="text-right">'.$Payment['OT'].'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['OTTotal'], 1).'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['Bonus'], 1).'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['Incentive'], 1).'&nbsp;</td>
                        <td class="text-right">'.FixFormat($Payment['Other'], 1).'&nbsp;</td>
                        <td class="text-right">&nbsp;'.FixFormat($Payment['TotalPaid'], 1).'&nbsp;</td>
                    </tr>');
                    }
                } ?>
            </table>

            <div id="actionBar" class="actionBar right"><br>
                <?php
                if(!$PaymentNum){
                    print('<button type="button" class="btn btn-success btn-rounder" id="SavePayment" data-toggle="modal" data-target="#myModal">บันทึกค่าแรง</button>');
                }
                else{
                    $EditButton='';
                    $PrintReport='';
                    if(!isset($_REQUEST['action']) || $_REQUEST['action']!='view'){
                        if(isset($_REQUEST['action']) && $_REQUEST['action']=='Edit'){
                            $EditButton='<input type="hidden" name="TimeSheet" value="'.$_REQUEST["TimeSheet"].'"><button type="button" class="btn btn-success btn-rounder" data-toggle="modal" data-target="#myModal">บันทึก</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                        else{
                            $EditButton='<button type="button" class="btn btn-success btn-rounder" onclick="javascript:location.href=\'employees_payment.php?TimeSheet='.$_REQUEST["TimeSheet"].'&action=Edit&DDay='.$_REQUEST["DDay"].'\';">แก้ไข</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                    }
                    if(!isset($_REQUEST['action']) || (isset($_REQUEST['action']) && $_REQUEST['action']!='Edit')){
                        $PrintReport='<button type="button" class="btn btn-info btn-rounder" id="PrintPayment">พิมพ์รายงาน</button>';
                        $PrintReport.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-primary btn-rounder" id="PrintPaymentSlip">พิมพ์สลิปเงินเดือน</button>';
                    }
                    print($EditButton.$PrintReport);
                }
                ?><input type="hidden" id="backPage" value="<?php if($comeFromReport){ print("reports.php"); }else{ print("employees.php"); } ?>">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                <br><br>&nbsp;
            </div>
        </div>
    </section>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">บันทึกค่าแรง</h4>
            </div>
            <div class="modal-body text-center">
                <b>วันที่จ่ายค่าแรง:</b> <input type="text" class="form-control inline_input Calendar" name="Date2pay" id="dateSelected" value="<?php if(isset($PayDate)){ print(date('d/m/Y', $PayDate)); }else{ print(date('d/m/Y', time())); } ?>" style="width:150px;">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success setPayDate">ตกลง</button>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
            </div>
        </div>
      </div>
    </div>
</form>
<?php
}
include("footer.php");
?>