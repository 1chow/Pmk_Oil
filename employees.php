<?php
include("dbvars.inc.php");
if(!preg_match('/-8-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

$alertTxt="";
if(isset($_POST["DeleteItem"]) && intval($_POST["DeleteItem"])){ // delete employerr
    $sqlDelete="Update ".$db_name.".employee SET employee.Deleted=1 Where EmpID=".intval($_POST["DeleteItem"]).";";
    $rsDelete=mysql_query($sqlDelete);
    exit();
}
// else if(isset($_POST["DeleteWorkDate"])){
//     $TimeSplit=explode('-', $_POST["DeleteWorkDate"]);
//     $SetWorkTime=$TimeSplit[2]."-".$TimeSplit[1]."-".$TimeSplit[0];
//     $sqlDelete="delete from ".$db_name.".emptime where EmpID=".intval($_POST["EmpID2Delete"])." and Date='".$SetWorkTime."';";
//     $rsDelete=mysql_query($sqlDelete);
//     $_REQUEST["manageTime"]=1;
//     $_REQUEST["EmpID"]=$_POST["EmpID2Delete"];
//     $_REQUEST["TimeSheet"]=$TimeSplit[2]."-".$TimeSplit[1];
//     exit();
// }
// else if(isset($_POST["TakeoffDate"])){
//     $TimeSplit=explode('/', $_POST["TakeoffDate"]);
//     $SetWorkTime=$TimeSplit[2]."-".$TimeSplit[1]."-".$TimeSplit[0];
//     $sqlUpdate="Update ".$db_name.".emptime set TakeOff=".intval($_POST["setTakeOff"])." where EmpID=".intval($_POST["EmpIDTimeoff"])." and Date='".$SetWorkTime."';";
//     $rsUpdate=mysql_query($sqlUpdate); print($rsUpdate);
//     exit();
// }
else if(isset($_POST["FirstName"]) && trim($_POST["FirstName"])){
    // add or update employee
    $BirthDate=0;
    if(trim($_POST["Education"])==''){
        $_POST["Education"]=$_POST["EducationInfo"];
    }
    if((is_numeric($_POST["BirthMonth"]))&&(is_numeric($_POST["BirthDate"]))&&(is_numeric($_POST["BirthYear"]))){
        $BirthDate=mktime(0, 0, 0, $_POST["BirthMonth"], $_POST["BirthDate"], ($_POST["BirthYear"]-543));
    }
    $dateList='';
    for($k=1; $k<8; $k++){
        if(isset($_POST["WorkDate"][$k])){
            if($dateList){
                $dateList.=",";
            }
            $dateList.=$k;
        }
    }
    if(!isset($_POST["AutoInForm"])){
        $_POST["AutoInForm"]=0;
    }
    if(!isset($_POST["Cashier"])){
        $_POST["Cashier"]=0;
    }
    if(!isset($_POST["HoldingMoney"])){
        $_POST["HoldingMoney"]=0;
    }
    if(intval($_REQUEST["UpdateEmp"])){
        $sqlUpdate="UPDATE ".$db_name.".employee SET UserName='".mysql_real_escape_string(trim($_POST["EmpUserName"]))."', FirstName='".mysql_real_escape_string(trim($_POST["FirstName"]))."', LastName='".mysql_real_escape_string(trim($_POST["LastName"]))."', NickName='".mysql_real_escape_string(trim($_POST["NickName"]))."', SSN='".mysql_real_escape_string(trim($_POST["SSN"]))."', BirthDate=".intval($BirthDate).", Education='".mysql_real_escape_string(trim($_POST["Education"]))."', Address1='".mysql_real_escape_string(trim($_POST["Address1"]))."', Address2='".mysql_real_escape_string(trim($_POST["Address2"]))."', Soi='".mysql_real_escape_string(trim($_POST["Soi"]))."', Street='".mysql_real_escape_string(trim($_POST["Street"]))."', SubDistrict='".mysql_real_escape_string(trim($_POST["SubDistrict"]))."', District='".mysql_real_escape_string(trim($_POST["District"]))."', Province='".mysql_real_escape_string(trim($_POST["Province"]))."', ZipCode='".mysql_real_escape_string(trim($_POST["ZipCode"]))."', Tel='".mysql_real_escape_string(trim($_POST["Tel"]))."', Experience1='".mysql_real_escape_string(trim($_POST["Experience1"]))."', Experience2='".mysql_real_escape_string(trim($_POST["Experience2"]))."', ContactName1='".mysql_real_escape_string(trim($_POST["ContactName1"]))."', ContactTel1='".mysql_real_escape_string(trim($_POST["ContactTel1"]))."', ContactName2='".mysql_real_escape_string(trim($_POST["ContactName2"]))."', ContactTel2='".mysql_real_escape_string(trim($_POST["ContactTel2"]))."', PositionID=".intval($_POST["PositionID"]).", Permission=".intval($_POST["Permission"]).", CalTax=".intval($_POST["CalTax"]).", WorkDays='".mysql_real_escape_string(trim($dateList))."', Incentive=".intval($_POST['Incentive']).", AutoInForm=".intval($_POST["AutoInForm"]).", Cashier=".intval($_POST["Cashier"]).", HoldingMoney=".intval($_POST["HoldingMoney"]).", CashierInfo='".mysql_real_escape_string(trim($_POST["CashierInfo"]))."', PaidDate=".intval($_POST["PaidDate"])." WHERE employee.EmpID=".intval($_POST["UpdateEmp"]).";";
        $rsUpdate=mysql_query($sqlUpdate);
        $NewEmpID=$_POST["UpdateEmp"];
        $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>แก้ไขข้อมูลของ <strong>'.$_POST["FirstName"].' '.$_POST["LastName"].'</strong> เรียบร้อยแล้ว.</div>';
    }
    else{ // new employee
        $sqlInsert="INSERT INTO ".$db_name.".employee (UserName, Password, FirstName, LastName, NickName, SSN, BirthDate, Education, Address1, Address2, Soi, Street, SubDistrict, District, Province, ZipCode, Tel, Experience1, Experience2, ContactName1, ContactTel1, ContactName2, ContactTel2, PositionID, Permission, CalTax, WorkDays, Incentive, AutoInForm, Cashier, HoldingMoney, CashierInfo, PaidDate) VALUES ('".mysql_real_escape_string(trim($_POST["EmpUserName"]))."', '', '".mysql_real_escape_string(trim($_POST["FirstName"]))."', '".mysql_real_escape_string(trim($_POST["LastName"]))."', '".mysql_real_escape_string(trim($_POST["NickName"]))."', '".mysql_real_escape_string(trim($_POST["SSN"]))."', ".intval($BirthDate).", '".mysql_real_escape_string(trim($_POST["Education"]))."', '".mysql_real_escape_string(trim($_POST["Address1"]))."', '".mysql_real_escape_string(trim($_POST["Address2"]))."', '".mysql_real_escape_string(trim($_POST["Soi"]))."', '".mysql_real_escape_string(trim($_POST["Street"]))."', '".mysql_real_escape_string(trim($_POST["SubDistrict"]))."', '".mysql_real_escape_string(trim($_POST["District"]))."', '".mysql_real_escape_string(trim($_POST["Province"]))."', '".mysql_real_escape_string(trim($_POST["ZipCode"]))."', '".mysql_real_escape_string(trim($_POST["Tel"]))."', '".mysql_real_escape_string(trim($_POST["Experience1"]))."', '".mysql_real_escape_string(trim($_POST["Experience2"]))."', '".mysql_real_escape_string(trim($_POST["ContactName1"]))."', '".mysql_real_escape_string(trim($_POST["ContactTel1"]))."', '".mysql_real_escape_string(trim($_POST["ContactName2"]))."', '".mysql_real_escape_string(trim($_POST["ContactTel2"]))."', ".intval($_POST["PositionID"]).", ".intval($_POST["Permission"]).", ".intval($_POST["CalTax"]).", '".mysql_real_escape_string(trim($dateList))."', ".intval($_POST['Incentive']).", ".intval($_POST["AutoInForm"]).", ".intval($_POST["Cashier"]).", ".intval($_POST["HoldingMoney"]).", '".mysql_real_escape_string(trim($_POST["CashierInfo"]))."', ".intval($_POST["PaidDate"]).");";
        $rsInsert=mysql_query($sqlInsert);
        $NewEmpID=mysql_insert_id($Conn);
        $_REQUEST["UpdateEmp"]=$NewEmpID;
        $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>เพิ่มข้อมูลของ <strong>'.$_POST["FirstName"].' '.$_POST["LastName"].'</strong> เรียบร้อยแล้ว.</div>';
    }

    if(trim($_REQUEST["EmpUserName"]) && trim($_REQUEST["EmpPassword"])){
        $Pass2Encrypt=PassEncryption(trim($_REQUEST["EmpPassword"]), $NewEmpID, 0); // encrypt password
        $sqlUpdatePass="Update ".$db_name.".employee SET Password='".mysql_real_escape_string(trim($Pass2Encrypt))."' Where EmpID=".$NewEmpID.";";
        $rsUpdatePass=mysql_query($sqlUpdatePass);
    }

    // check for keep salary history
    $_POST["Salary"]=preg_replace("/,/", "", $_POST["Salary"]);
    $sqlSalary="select PositionID, SalaryType, Salary, ID, StartDate, OTRate, TaxRate from ".$db_name.".empsalary where EmpID=".intval($NewEmpID)." order by StartDate DESC, ID DESC;";
    $rsSalary=mysql_query($sqlSalary);
    $Salary=mysql_fetch_array($rsSalary);
    $DateCut = explode("/", trim($_POST["StartWorkDate"]));
    $StartDate = mktime(0, 0, 0, $DateCut[1], $DateCut[0], $DateCut[2]);
    if(!intval($_POST["OTRate"])){
        $_POST["OTRate"]=0;
    }
    if(!intval($_POST["TaxRate"])){
        $_POST["TaxRate"]=0;
    }
    if(($Salary[0]!=$_POST["PositionID"])||($Salary[1]!=$_POST["SalaryType"])||($Salary[2]!=$_POST["Salary"])||($Salary[4]!=$StartDate)||($Salary[5]!=$_POST["OTRate"]) || ($Salary[6]!=$_POST["TaxRate"])){
        $sqlInsert="INSERT INTO ".$db_name.".empsalary (EmpID, PositionID, SalaryType, Salary, OTRate, StartDate, EndDate, TaxRate) VALUES (".$NewEmpID.", ".intval($_POST["PositionID"]).", '".$_POST["SalaryType"]."', '".round($_POST["Salary"], 2)."', '".round($_POST["OTRate"], 3)."', ".$StartDate.", 0, ".round($_POST["TaxRate"], 2).");";
        $rsInsert=mysql_query($sqlInsert);

        if($Salary[3]){
            $setDateVal = date("Y-m-d", $StartDate);
            $EndDate = strtotime($setDateVal.' -1 day');
            $sqlInsert="UPDATE ".$db_name.".empsalary SET EndDate=".$EndDate." where ID=".$Salary[3].";";
            $rsInsert=mysql_query($sqlInsert);
        }
    }
    if($_FILES["EmpImage"]["tmp_name"]){
        move_uploaded_file($_FILES["EmpImage"]["tmp_name"], "images/user-img/user-".$NewEmpID.".jpg");
        chmod("images/user-img/user-".$NewEmpID.".jpg", 0644);
    }

    $sqlDel="delete from ".$db_name.".employee_access where EmpID=".intval($_REQUEST["UpdateEmp"]).";";
    $rsDelete=mysql_query($sqlDel);
    if(isset($_POST["AccessTo"]) && count($_POST["AccessTo"])){
        foreach ($_POST["AccessTo"] as $key => $value) {
            $sqlInsert="INSERT INTO ".$db_name.".employee_access (EmpID, SectionID) VALUES (".intval($_REQUEST["UpdateEmp"]).", ".$key.");";
            $rsInsert=mysql_query($sqlInsert);
        }
    }
    unset($_REQUEST["UpdateEmp"]);
}

else if(isset($_POST["TimeForEmp"]) && intval($_POST["TimeForEmp"])){
    $ErrorTime = 0;
    $StartWork = min(array_keys($_POST["clockin"][1]));
    $EndWork = max(array_keys($_POST["clockin"][1]));
    $sqlDelete="delete from ".$db_name.".emptime where EmpID=".$_POST["TimeForEmp"]." and Date>='".$StartWork."' and Date<='".$EndWork."';";
    $rsDelete=mysql_query($sqlDelete);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเวลาของ <strong>'.$_POST["EmpName"].'</strong> เรียบร้อยแล้ว.</div>';

    //for($i=1; $i<=$DayPerMonth; $i++){
    $StartInsert = strtotime($StartWork);
    $EndInsert = strtotime($EndWork);
    while($StartInsert <= $EndInsert){
        $ThisDayAdded=0;
        $setDateVal = date("Y-m-d", $StartInsert);
        for($j=1; $j<4; $j++){
            $clockin=$_POST["clockin"][$j][$setDateVal];
            $clockout=$_POST["clockout"][$j][$setDateVal];
            if($clockin || $clockout){
                $TakeOff=0;
                if(isset($_POST["takeoff"][$setDateVal])){
                    $TakeOff=1;
                }
                $sqlInsert="INSERT INTO ".$db_name.".emptime (EmpID, Date, ClockIn, ClockOut, TakeOff, RoundNo) VALUES (".intval($_POST["TimeForEmp"]).", '".date("Y-m-d", $StartInsert)."', '".$clockin."', '".$clockout."', ".$TakeOff.", ".$j.");";
                $rsInsert=mysql_query($sqlInsert);
                $ThisDayAdded++;
            }
            $Check=$clockout;
            if(($clockin>=18) && ($clockout < 7)){
                $Check=24;
            }
            if($Check < $clockin){
                $ErrorTime = 1;
                $_REQUEST["manageTime"]=1;
                $_REQUEST["EmpID"]=$_REQUEST["TimeForEmp"];
                $_REQUEST["TimeSheet"]=$_POST["TimeYear"]."-".$_POST["TimeMonth"];
                $alertTxt='<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>มีข้อมูลไม่ถูกต้อง กรุณาตรวจสอบ.</div>';
            }
        }
        if(!$ThisDayAdded && isset($_POST["takeoff"][$setDateVal])){
            $sqlInsert="INSERT INTO ".$db_name.".emptime (EmpID, Date, ClockIn, ClockOut, TakeOff, RoundNo) VALUES (".intval($_POST["TimeForEmp"]).", '".date("Y-m-d", $StartInsert)."', NULL, NULL, 1, ".$j.");";
            $rsInsert=mysql_query($sqlInsert);
        }
        $StartInsert = strtotime($setDateVal.' +1 day');
    }
}
else if(isset($_POST["HoliDayDate"])){
    $sqlDelete="DELETE FROM ".$db_name.".Holiday;";
    $rsDelete=mysql_query($sqlDelete);
    foreach ($_POST["HoliDayDate"] as $key => $value) {
        if(trim($_POST["HoliDayNote"][$key]) && intval($_POST["HoliDayDate"][$key]) && intval($_POST["HoliDayMonth"][$key])){
            $sqlUpdate="INSERT INTO ".$db_name.".Holiday (Date, Month, Note) VALUES (".intval($_POST["HoliDayDate"][$key]).", ".intval($_POST["HoliDayMonth"][$key]).", '".mysql_real_escape_string(trim($_POST["HoliDayNote"][$key]))."');";
            $rsUpdate=mysql_query($sqlUpdate);
        }
    }
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลวันหยุดพิเศษเรียบร้อยแล้ว.</div>';
    $_POST["AsAction"]='HolidayMark';
}



include("header.php");
if(isset($_REQUEST["manageTime"])){
    $sqlPosition = "select concat(FirstName, ' ', LastName), PositionName, EmpID, WorkDays, employee.PaidDate from employee inner join empposition on empposition.PositionID=employee.PositionID where employee.EmpID=".intval($_REQUEST["EmpID"])." order by FirstName ASC, LastName ASC;";
    $rsPosition = mysql_query($sqlPosition);
    $Position = mysql_fetch_row($rsPosition);
    $WorkDays=$Position[3];
    $lastDay4Payment=$Position[4];
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>พนักงาน</h2>
        </div>

        <div class="content-center" id="EmployeeContent">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;">บันทึกเวลาทำงาน</h3>
                </div>
                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <form action="employees.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" id="TimeForEmp" name="TimeForEmp" value="<?php print($_REQUEST["EmpID"]); ?>">
                        <input type="hidden" name="EmpName" value="<?php print($Position[0]); ?>">
                        <div class="form-group col-sm-12" style="text-align:center;">
                            <?php
                            $SetSelected[0]=date("Y", time());
                            $SetSelected[1]=date("n", time());
                            if(isset($_REQUEST["TimeSheet"])){
                                $Date=$_REQUEST["TimeSheet"];
                                $SetSelected=explode('-', $Date);
                            }
                            else{
                                $Date=date("Y", time())."-".date("n", time());
                            }
                            if(trim($Position[0])){
                                print("<p><b>ชื่อ: ".$Position[0]."</b>");
                                print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>");
                                print("<p>แผนก: ".$Position[1]);
                                print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
                                print("เดือน: <select id=\"Work4Month\" name=\"TimeMonth\" class=\"form-control input-sm\" style=\"display:inline; width:90px;\"><option value=\"\">เดือน</option>");
                                for($i=0; $i<count($monthList); $i++){
                                    print("<option value=\"".($i+1)."\"");
                                    if(($i+1) == $SetSelected[1]){
                                        print(" selected");
                                    }
                                    print(">".$monthList[$i]."</option>");
                                }
                                print("</select>");
                                print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
                                print("ปี: <select id=\"Work4Year\" name=\"TimeYear\" class=\"form-control input-sm\" style=\"display:inline; width:100px;\">");
                                for($i=2015; $i<=(date("Y", time())+1); $i++){
                                    print("<option value=\"".$i."\"");
                                    if($i == $SetSelected[0]){
                                        print(" selected");
                                    }
                                    print(">".($i+543)."</option>");
                                }
                                print("</select>");
                            }

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

                                $StartCal=mktime(0, 0, 0, $SetMonth, 1, $SetYear);
                                $EndCal=mktime(23, 59, 59, $SetMonth, $DayPerMonth, $SetYear);
                            }
                            else{//echo $lastDay4Payment;
                                $Day4Start = $SetStartYear."-".$SetStartMonth."-".($lastDay4Payment+1);
                                $Day4End = $SetYear."-".$SetMonth."-".$lastDay4Payment;
                                $Day4StartText = ($lastDay4Payment+1)." ".$monthList[($SetStartMonth-1)]." ".($SetStartYear+543);
                                $Day4EndText = $lastDay4Payment." ".$monthList[($SetMonth-1)]." ".($SetYear+543);
                                $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $SetStartMonth, $SetStartYear));

                                $StartCal=mktime(0, 0, 0, $SetStartMonth, ($lastDay4Payment+1), $SetStartYear);
                                $EndCal=mktime(23, 59, 59, $SetMonth, $lastDay4Payment, $SetYear);
                            }
                            print("<p style=\"text-align:center; margin:0 0 20px;\"><span class=\"payment_page_text\">".$Day4StartText." - ".$Day4EndText."</span></p>");
                            ?>

                            <br>
                        </div>
                        <div class="form-group" id="TimeHeader">
                            <div class="col-sm-2" style="text-align:center;"><b>วันที่</b></div>
                            <div class="col-sm-1" style="text-align:center;">เข้า 1</div>
                            <div class="col-sm-1" style="text-align:center;">ออก 1</div>
                            <div class="col-sm-1" style="text-align:center;">&nbsp;</div>
                            <div class="col-sm-1" style="text-align:center;">เข้า 2</div>
                            <div class="col-sm-1" style="text-align:center;">ออก 2</div>
                            <div class="col-sm-1" style="text-align:center;">&nbsp;</div>
                            <div class="col-sm-1" style="text-align:center;">เข้า 3</div>
                            <div class="col-sm-1" style="text-align:center;">ออก 3</div>
                            <div class="col-sm-1" style="text-align:center;">แจ้งลา/สาย</div>
                        </div>
                        <div id="TimeSheet">
                        <?php
                        $HolidayNote='';
                        $Time4In = array(1 => 7.30, 2 => 18.00, 3 => 4.00);
                        $Time4Out = array(1 => 18.00, 2 => 1.00, 3 => 7.30);
                        $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $SetSelected[1], $SetSelected[0]));

                        //for($i=1; $i<=$DayPerMonth; $i++){
                        while($StartCal <= $EndCal){
                            $TakeOff="";
                            $InArr = array(1 => '', 2 => '', 3 => '');
                            $OutArr = array(1 => '', 2 => '', 3 => '');
                            $ErrorArr = array(1 => '', 2 => '', 3 => '');
                            $DateCheck=date('N', $StartCal);
                            $sqlTimeRec="select ClockIn, ClockOut, TakeOff, RoundNo from ".$db_name.".emptime where EmpID=".intval($_REQUEST["EmpID"])." and Date='".date("Y-m-d", $StartCal)."';";
                            $rsTimeRec=mysql_query($sqlTimeRec);
                            while($TimeRec=mysql_fetch_row($rsTimeRec)){
                                $Round=0;
                                $TimeRec[0]=substr($TimeRec[0], 0, -3);
                                $TimeRec[1]=substr($TimeRec[1], 0, -3);

                                $ClockInText=preg_replace("#:#", ".", $TimeRec[0]);
                                $ClockOutText=preg_replace("#:#", ".", $TimeRec[1]);
                                $Check=$ClockOutText;
                                if(intval($TimeRec[3])){
                                    $Round=$TimeRec[3];
                                }
                                else if($ClockInText >= 17.30){
                                    $Round=2;
                                }
                                else if($ClockInText >= 7){
                                    $Round=1;
                                }
                                else if($ClockInText){
                                    $Round=3;
                                }
                                if($Round){
                                    $InArr[$Round] = $TimeRec[0];
                                    $OutArr[$Round] = $TimeRec[1];
                                    if($Round==2 && $ClockOutText<7){
                                        $Check=24;
                                    }
                                }
                                // check if checkout time > checkin time
                                if($Check < $ClockInText){
                                    $ErrorArr[$Round] = ' style="border:1px solid red;"';
                                }
                                // check late time
                                $checkLate=($ClockInText-$Time4In[$Round]);
                                if($checkLate > 0){ // สาย
                                    $ErrorArr[$Round] = ' style="border:1px solid #FF9C9C;"';
                                }
                                if($TimeRec[2]){
                                    $TakeOff=" checked";
                                }
                            }
                            if(!preg_match('/'.$DateCheck.'/', $WorkDays)){
                                $styleSet=' style="border:1px solid #A3A6FF;"';
                                if(!$ErrorArr[1]){ $ErrorArr[1] = $styleSet; }
                                if(!$ErrorArr[2]){ $ErrorArr[2] = $styleSet; }
                                if(!$ErrorArr[3]){ $ErrorArr[3] = $styleSet; }
                            }
                            $HolidayNote="";
                            $sqlHoliday="SELECT Date, Note from ".$db_name.".Holiday where Date=".intval($i)." and Month=".intval($SetSelected[1])." order by Month ASC, Date ASC;";
                            $rsHoliDay=mysql_query($sqlHoliday);
                            $Holiday=mysql_fetch_row($rsHoliDay);
                            if($Holiday[0]){
                                $styleSet=' style="border:1px solid #C265C2;"';
                                if(!$ErrorArr[1]){ $ErrorArr[1] = $styleSet; }
                                if(!$ErrorArr[2]){ $ErrorArr[2] = $styleSet; }
                                if(!$ErrorArr[3]){ $ErrorArr[3] = $styleSet; }
                                $HolidayNote="<p>** ".$i." ".$monthList[($SetSelected[1]-1)]." ".$Holiday[1]."</p>";
                            }
                            $setDateVal = date("Y-m-d", $StartCal);
                        ?>
                        <div class="form-group" id="row<?php print(date("Y-m-d", $StartCal)); ?>">
                            <div class="col-sm-2" style="text-align:center;"><?php print(date("j", $StartCal)." ".$shortMonthList[date("n", $StartCal)-1]." ".date("Y", $StartCal)); ?></div>
                            <div class="col-sm-1">
                                <input type="text" id="in1[<?php print($setDateVal); ?>]" name="clockin[1][<?php print($setDateVal); ?>]" class="form-control time"<?php print(' value="'.$InArr[1].'"'.$ErrorArr[1]); ?>>
                            </div>
                            <div class="col-sm-1">
                                <input type="text" id="in1[<?php print($setDateVal); ?>]" name="clockout[1][<?php print($setDateVal); ?>]" class="form-control time"<?php print(' value="'.$OutArr[1].'"'.$ErrorArr[1]); ?>>
                            </div>
                            <div class="col-sm-1">&nbsp;</div>
                            <div class="col-sm-1">
                                <input type="text" id="in2[<?php print($setDateVal); ?>]" name="clockin[2][<?php print($setDateVal); ?>]" class="form-control time"<?php print(' value="'.$InArr[2].'"'.$ErrorArr[2]); ?>>
                            </div>
                            <div class="col-sm-1">
                                <input type="text" id="out2[<?php print($setDateVal); ?>]" name="clockout[2][<?php print($setDateVal); ?>]" class="form-control time"<?php print(' value="'.$OutArr[2].'"'.$ErrorArr[2]); ?>>
                            </div>
                            <div class="col-sm-1">&nbsp;</div>
                            <div class="col-sm-1">
                                <input type="text" id="in3[<?php print($setDateVal); ?>]" name="clockin[3][<?php print($setDateVal); ?>]" class="form-control time"<?php print(' value="'.$InArr[3].'"'.$ErrorArr[3]); ?>>
                            </div>
                            <div class="col-sm-1">
                                <input type="text" id="out3[<?php print($setDateVal); ?>]" name="clockout[3][<?php print($setDateVal); ?>]" class="form-control time"<?php print(' value="'.$OutArr[3].'"'.$ErrorArr[3]); ?>>
                            </div>
                            <div class="col-sm-1" style="text-align:center;">
                                <p style="margin-top:8px;">&nbsp;<input type="checkbox" name="takeoff[<?php print($setDateVal); ?>]" class="form-control"<?php if(isset($TakeOff) && trim($TakeOff)){ print($TakeOff); } ?>></p>
                            </div>
                            <?php
                            print('<div style="clear:both" class="col-sm-1"></div><div class="col-sm-11">'.$HolidayNote.'</div>');
                            ?>
                        </div>
                        <?php
                        $StartCal = strtotime($setDateVal.' +1 day');
                        }
                        // print('<div class="col-sm-1"></div>');
                        // print('<div class="col-sm-11">'.$HolidayNote.'</div>');
                        ?>
                        </div>
                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" value="employees.php">
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;
                            <button type="submit" class="btn btn-success btn-rounder">บันทึก</button>
                            &nbsp;&nbsp;&nbsp;
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
else if(isset($_REQUEST["UpdateEmp"])){
    $sqlEmp="select UserName, FirstName, LastName, NickName, SSN, BirthDate, Education, Address1, Address2, Soi, Street, SubDistrict, District, Province, ZipCode, Tel, Experience1, Experience2, ContactName1, ContactTel1, ContactName2, ContactTel2, PositionID, Permission, CalTax, WorkDays, Incentive, AutoInForm, Cashier, HoldingMoney, CashierInfo, PaidDate from ".$db_name.".employee where EmpID=".intval($_REQUEST["UpdateEmp"]).";";
    $rsEmp=mysql_query($sqlEmp);
    $Employee=mysql_fetch_array($rsEmp);
    $otherEcu = "";
    if(($Employee['Education'])&&($Employee['Education']!='ป.6')&&($Employee['Education']!='ม.3')&&($Employee['Education']!='ม.6')){
        $otherEcu=$Employee['Education'];
        $Employee['Education']='other';
    }

    $sqlSalary="select SalaryType, Salary, StartDate, OTRate, TaxRate from ".$db_name.".empsalary where EmpID=".intval($_REQUEST["UpdateEmp"])." order by StartDate DESC, ID DESC;";
    $rsSalary=mysql_query($sqlSalary);
    $Salary=mysql_fetch_array($rsSalary);

    $AccessList="0";
    $sqlSectionID="select SectionID from ".$db_name.".employee_access where EmpID=".intval($_REQUEST["UpdateEmp"]).";";
    $rsSectionID=mysql_query($sqlSectionID);
    while($SectionID = mysql_fetch_row($rsSectionID)){
        $AccessList.="-".$SectionID[0]."-";
    }

    if(!$_REQUEST["UpdateEmp"]){
        $Employee['WorkDays']='1,2,3,4,5,6';
        $Employee["Permission"]=0;
        $Employee['CalTax']=0;
        $Employee['Incentive']=0;
        $Employee["AutoInForm"]=0;
        $Employee['Cashier']=0;
        $Employee['HoldingMoney']=0;
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>พนักงาน</h2>
        </div>

        <div class="content-center" id="EmployeeContent">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;"><?php if(!intval($_REQUEST['UpdateEmp'])){ print('เพิ่มพนักงานใหม่'); }else{ print('แก้ไขข้อมูลพนักงาน'); } ?></h3>
                </div>
                <div class="panel-body">
                    <h4 class="head-form">ข้อมูลส่วนตัว</h4>
                    <form action="employees.php" method="post" id="defaultForm2" class="form-horizontal" role="form" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="UpdateEmp" value="<?php print($_REQUEST["UpdateEmp"]); ?>">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">รูปพนักงาน:</label>
                            <div class="col-sm-3">
                                <?php
                                if(is_file("images/user-img/user-".$_REQUEST["UpdateEmp"].".jpg")){
                                    print("<img src=\"images/user-img/user-".$_REQUEST["UpdateEmp"].".jpg\" width=\"120\">");
                                }
                                ?>
                                <input type="file" name="EmpImage" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="SSN">เลขประจำตัวประชาชน:</label>
                            <div class="col-sm-3">
                                <input type="text" name="SSN" id="SSN" class="form-control" value="<?php if(isset($Employee['SSN'])){ print($Employee['SSN']); } ?>">
                            </div>
                        </div>

                        <div class="col-sm-6 form-group">
                            <label class="col-sm-4 control-label">ชื่อ:</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="FirstName" placeholder="ชื่อ" value="<?php if(isset($Employee['FirstName'])){ print($Employee['FirstName']); } ?>">
                            </div>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label class="col-sm-3 control-label">นามสกุล:</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="LastName" placeholder="นามสกุล" value="<?php if(isset($Employee['LastName'])){ print($Employee['LastName']); } ?>">
                            </div>
                        </div>

                        <div class="form-group" style="clear:both;">
                            <label class="col-sm-2 control-label">ชื่อเล่น:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="NickName" placeholder="ชื่อเล่น" value="<?php if(isset($Employee['NickName'])){ print($Employee['NickName']); } ?>">
                            </div>
                            <label class="col-sm-2 control-label">วันเกิด:</label>
                            <div class="col-sm-3">
                                <?php
                                print("\r\n\t\t<select name=\"BirthDate\" class=\"form-control input-sm\" style=\"display:inline; width:100px;\"><option value=\"\">วันที่</option>");
                                for($i=1; $i<32; $i++){
                                    print("<option value=\"".$i."\"");
                                    if(isset($Employee['BirthDate']) && $i==date("j", $Employee['BirthDate'])){
                                        print(" selected");
                                    }
                                    print(">".$i."</option>");
                                }
                                print("</select>&nbsp;");
                                print("\r\n\r\n\t\t<select name=\"BirthMonth\" class=\"form-control input-sm\" style=\"display:inline; width:90px;\"><option value=\"\">เดือน</option>");
                                for($i=0; $i<count($monthList); $i++){
                                    print("<option value=\"".($i+1)."\"");
                                    if(isset($Employee['BirthDate']) && ($i+1)==date("n", $Employee['BirthDate'])){
                                        print(" selected");
                                    }
                                    print(">".$monthList[$i]."</option>");
                                }
                                print("</select>&nbsp;");
                                ?>
                                <input type="text" class="form-control dates" name="BirthYear" placeholder="ปี พ.ศ." value="<?php if(isset($Employee['BirthDate'])){ print(date("Y", $Employee['BirthDate'])+543); } ?>" style="display:inline; width:100px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ที่อยู่ปัจจุบัน เลขที่:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="Address1" placeholder="เลขที่" value="<?php if(isset($Employee['Address1'])){ print($Employee['Address1']); } ?>">
                            </div>
                            <label class="col-sm-2 control-label">หมู่ที่:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="Address2" placeholder="หมู่ที่" value="<?php if(isset($Employee['Address2'])){ print($Employee['Address2']); } ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ซอย:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="Soi" placeholder="ซอย" value="<?php if(isset($Employee['Soi'])){ print($Employee['Soi']); } ?>">
                            </div>
                            <label class="col-sm-2 control-label">ถนน:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="Street" placeholder="ถนน" value="<?php if(isset($Employee['Street'])){ print($Employee['Street']); } ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ตำบล/แขวง:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="SubDistrict" placeholder="ตำบล/แขวง" value="<?php if(isset($Employee['SubDistrict'])){ print($Employee['SubDistrict']); } ?>">
                            </div>
                            <label class="col-sm-2 control-label">อำเภอ/เขต:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="District" placeholder="อำเภอ/เขต" value="<?php if(isset($Employee['District'])){ print($Employee['District']); } ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">จังหวัด:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="Province" placeholder="จังหวัด" value="<?php if(isset($Employee['Province'])){ print($Employee['Province']); } ?>">
                            </div>
                            <label class="col-sm-2 control-label">รหัสไปรษณีย์:</label>
                            <div class="col-sm-3">
                                <input type="text" name="ZipCode" class="form-control zipcode" value="<?php if(isset($Employee['ZipCode'])){ print($Employee['ZipCode']); } ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">โทรศัพท์:</label>
                            <div class="col-sm-3">
                                <input type="text" name="Tel" class="form-control telephone" value="<?php if(isset($Employee['Tel'])){ print($Employee['Tel']); } ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">วุฒิการศึกษาขั้นสูงสุด:</label>
                            <div class="radio col-lg-2">
                                <label>
                                    <input type="radio" value="ป.6" name="Education"<?php if((!isset($Employee['Education'])) || ($Employee['Education']=='ป.6')){ print(" checked"); } ?>>
                                    ประถมศึกษาปีที่ 6
                                </label>
                            </div>
                            <div class="radio col-lg-2">
                                <label>
                                    <input type="radio" value="ม.3" name="Education"<?php if((isset($Employee['Education'])) && $Employee['Education']=='ม.3'){ print(" checked"); } ?>>
                                    มัธยมศึกษาปีที่ 3
                                </label>
                            </div>
                            <div class="radio col-lg-2">
                                <label>
                                    <input type="radio" value="ม.6" name="Education"<?php if((isset($Employee['Education'])) && $Employee['Education']=='ม.6'){ print(" checked"); } ?>>
                                    มัธยมศึกษาปีที่ 6
                                </label>
                            </div>
                            <div class="radio col-lg-1">
                                <label>
                                    <input type="radio" value="" name="Education" id="MoreEducation"<?php if((isset($Employee['Education'])) && $Employee['Education']=='other'){ print(" checked"); } ?>>
                                    อื่นๆ
                                </label>
                            </div>
                            <div class="col-lg-2" id=""> <input type="text" name="EducationInfo" class="form-control" value="<?php print($otherEcu); ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ประสบการณ์ทำงาน:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="Experience1" placeholder="1." value="<?php if(isset($Employee['Experience1'])){ print($Employee['Experience1']); } ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="Experience2" placeholder="2." value="<?php if(isset($Employee['Experience2'])){ print($Employee['Experience2']); } ?>">
                            </div>
                        </div>
                        <br>
                        <label class="col-sm-12">บุคคลที่สามารถติดต่อได้ในกรณีฉุกเฉิน</label>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ชื่อ:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="ContactName1" placeholder="ชื่อ-นามสกุล" value="<?php if(isset($Employee['ContactName1'])){ print($Employee['ContactName1']); } ?>">
                            </div>
                            <label class="col-sm-1 control-label">โทรศัพท์:</label>
                            <div class="col-sm-2">
                                <input type="text" name="ContactTel1" class="form-control telephone" value="<?php if(isset($Employee['ContactTel1'])){ print($Employee['ContactTel1']); } ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ชื่อ:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="ContactName2" placeholder="ชื่อ-นามสกุล" value="<?php if(isset($Employee['ContactName2'])){ print($Employee['ContactName2']); } ?>">
                            </div>
                            <label class="col-sm-1 control-label">โทรศัพท์:</label>
                            <div class="col-sm-2">
                                <input type="text" name="ContactTel2" class="form-control telephone" value="<?php if(isset($Employee['ContactTel2'])){ print($Employee['ContactTel2']); } ?>">
                            </div>
                        </div>

                        <div class="col-sm-12" style="height:50px;">&nbsp;</div>

                        <h4 class="head-form">ตำแหน่งและอัตราเงินเดือน</h4>
                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-4 control-label">ตำแหน่ง:</label>
                            <div class="col-sm-7">
                                <select name="PositionID" class="form-control input-sm">
                                <?php
                                $sqlPosition = "select PositionID, PositionName from empposition where empposition.Deleted=0 order by PositionName ASC";
                                $rsPosition = mysql_query($sqlPosition);
                                while($Position = mysql_fetch_row($rsPosition)){
                                    print("\t\t\t\t\t<option value=\"".$Position[0]."\"");
                                    if(isset($Employee['PositionID']) && $Employee['PositionID']==$Position[0]){
                                        print(" selected");
                                    }
                                    print(">".$Position[1]."</option>\n");
                                }
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-3 control-label">อัตราค่าแรง:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control price" name="Salary" placeholder="อัตราเงินเดือน" value="<?php if(isset($Salary['Salary']) && $Salary['Salary']){ print(number_format($Salary['Salary'], 2)); } ?>" required>
                            </div>
                            <div class="col-sm-3">
                                <select name="SalaryType" class="form-control input-sm" style="width:80px;">
                                <option value="วัน"<?php if(isset($Salary['SalaryType']) && $Salary['SalaryType']=='วัน'){ print(" selected"); } ?>>ต่อวัน</option>
                                <option value="เดือน"<?php if(isset($Salary['SalaryType']) && $Salary['SalaryType']=='เดือน'){ print(" selected"); } ?>>ต่อเดือน</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-4 control-label">ตั้งแต่วันที่:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control Calendar" name="StartWorkDate" value="<?php if(intval($Salary['StartDate'])){ print(date("d/m/Y", $Salary['StartDate'])); }else{ print(date("d/m/Y", time())); } ?>" style="display:inline; width:100px;">
                            </div>
                        </div>
                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-3 control-label">อัตรา OT:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control price inline_input" name="OTRate" id="OTRate" value="<?php if(intval($Salary['OTRate'])){ print(number_format($Salary['OTRate'], 3)); } ?>" style="width:80px;"> บาท/ชั่วโมง
                            </div>
                        </div>

                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-4 control-label">เริ่มรอบเงินเดือนวันที่:</label>
                            <div class="col-sm-8">
                                <?php
                                $PaidEmployee=explode(",", $lastDay4Payment);
                                if(count($PaidEmployee) > 1){
                                    print("<select name=\"PaidDate\" class=\"form-control input-sm\" style=\"width:130px;\">");
                                    for($i=0; $i<count($PaidEmployee); $i++){
                                        print("<option value=\"".$PaidEmployee[$i]."\"");
                                        if(!intval($Employee['PaidDate'])){
                                            $Employee['PaidDate']=$PaidEmployee[$i];
                                        }
                                        if($Employee['PaidDate']==$PaidEmployee[$i]){
                                            print(" selected");
                                        }
                                        if($PaidEmployee[$i]==(-1)){
                                            $PaidEmployee[$i]="วันสิ้นเดือน";
                                        }
                                        print(">".$PaidEmployee[$i]."</option>");
                                    }
                                    print("</select>");
                                }
                                else{
                                    print($lastDay4Payment."<input type=\"hidden\" name=\"PaidDate\" value=\"".$lastDay4Payment."\">");
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-3 control-label">หักภาษีรายเดือน:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control price inline_input" name="TaxRate" id="TaxRate" value="<?php if(intval($Salary['TaxRate'])){ print(number_format($Salary['TaxRate'], 2)); } ?>" style="width:100px;"> บาท/เดือน
                            </div>
                        </div>

                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-4 control-label">Incentive:</label>
                            <div class="col-sm-8" style="margin-top:5px;">
                                &nbsp; <input type="radio" class="form-control" name="Incentive" value="1"<?php if($Employee['Incentive']){ print(" checked"); } ?>> ได้ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" class="form-control" name="Incentive" value="0"<?php if(!$Employee['Incentive']){ print(" checked"); } ?>> ไม่ได้
                            </div>
                        </div>
                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-3 control-label">ประกันสังคม:</label>
                            <div class="col-sm-8" style="margin-top:5px;">
                                &nbsp; <input type="radio" class="form-control" name="CalTax" value="1"<?php if($Employee['CalTax']){ print(" checked"); } ?>> หัก &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" class="form-control" name="CalTax" value="0"<?php if(!$Employee['CalTax']){ print(" checked"); } ?>> ไม่หัก
                            </div>
                        </div>

                        <div class="col-sm-12 form-group" style="padding:0;">
                            <label class="col-sm-2 control-label">วันทำงาน:</label>
                            <div class="col-sm-10" style="margin-top:7px;">
                                <input type="checkbox" class="form-control" name="WorkDate[1]" value="1"<?php if(preg_match('/1/', $Employee['WorkDays'])){ print(" checked"); } ?>> วันจันทร์
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="checkbox" class="form-control" name="WorkDate[2]" value="2"<?php if(preg_match('/2/', $Employee['WorkDays'])){ print(" checked"); } ?>> วันอังคาร
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="checkbox" class="form-control" name="WorkDate[3]" value="3"<?php if(preg_match('/3/', $Employee['WorkDays'])){ print(" checked"); } ?>> วันพุธ
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="checkbox" class="form-control" name="WorkDate[4]" value="4"<?php if(preg_match('/4/', $Employee['WorkDays'])){ print(" checked"); } ?>> วันพฤหัสบดี
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="checkbox" class="form-control" name="WorkDate[5]" value="5"<?php if(preg_match('/5/', $Employee['WorkDays'])){ print(" checked"); } ?>> วันศุกร์
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="checkbox" class="form-control" name="WorkDate[6]" value="6"<?php if(preg_match('/6/', $Employee['WorkDays'])){ print(" checked"); } ?>> วันเสาร์
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="checkbox" class="form-control" name="WorkDate[7]" value="7"<?php if(preg_match('/7/', $Employee['WorkDays'])){ print(" checked"); } ?>> วันอาทิตย์
                            </div>
                        </div>

                        <?php
                        if(intval($WashNameShow)){
                        ?>
                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-4 control-label">กำหนด:</label>
                            <div class="col-sm-8" style="margin-top:7px;">
                                <input type="checkbox" class="form-control" name="AutoInForm" value="1"<?php if($Employee['AutoInForm']){ print(" checked"); } ?>> แสดงในฟอร์มใบรับบริการล้างรถอัตโนมัติ
                            </div>
                        </div>
                        <?php
                        }
                        else{
                            if(!isset($Employee['AutoInForm'])){
                                $Employee['AutoInForm']=0;
                            }
                            print('<input type="hidden" name="AutoInForm" value="'.$Employee['AutoInForm'].'">');
                        }
                        ?>

                        <div class="col-sm-12 form-group" style="clear:both; padding:0;">
                            <label class="col-sm-2 control-label">พนักงานเก็บเงิน:</label>
                            <div class="col-sm-8" style="margin-top:7px;">
                                <input type="checkbox" class="form-control" name="Cashier" value="1"<?php if($Employee['Cashier']){ print(" checked"); } ?>> ใช่ &nbsp;&nbsp;<input type="text" class="form-control inline_input" name="CashierInfo" placeholder="กรุณาระบุรายละเอียด" value="<?php if(isset($Employee['CashierInfo'])){ print($Employee['CashierInfo']); } ?>" style="width:85%;">
                            </div>
                        </div>

                        <div class="col-sm-6 form-group" style="padding:0;">
                            <label class="col-sm-4 control-label">ผู้ถือเงินสดย่อย:</label>
                            <div class="col-sm-8" style="margin-top:7px;">
                                <input type="checkbox" class="form-control" name="HoldingMoney" value="1"<?php if($Employee['HoldingMoney']){ print(" checked"); } ?>> ใช่
                            </div>
                        </div>

                        <div class="col-sm-12" style="height:50px;">&nbsp;</div>

                        <h4 class="col-sm-12 head-form">ข้อมูลการเข้าใช้ระบบ</h4>
                        <?php
                        if((intval($_REQUEST['UpdateEmp']) && ($PermissionNo==3)) || !intval($_REQUEST['UpdateEmp'])){ // only admin can edit
                        ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">User Name:</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control" name="EmpUserName" placeholder="User Name" value="<?php if(isset($Employee['UserName'])){ print($Employee['UserName']); } ?>" autocomplete="off">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Password:</label>
                                <div class="col-sm-3">
                                    <?php
                                    ?>
                                    <input type="password" class="form-control" name="EmpPassword" placeholder="Password" value="" autocomplete="off">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">ระดับการเข้าใช้งาน:</label>
                                <div class="col-sm-5">
                                    <select name="Permission" class="form-control input-sm" style="width:120px;">
                                    <option value="1"<?php if($Employee["Permission"]==1){ print(" selected"); } ?>>Normal</option>
                                    <option value="2"<?php if($Employee["Permission"]==2){ print(" selected"); } ?>>Supervisor</option>
                                    <option value="3"<?php if($Employee["Permission"]==3){ print(" selected"); } ?>>Admin</option>
                                    <option value="0"<?php if($Employee["Permission"]==0){ print(" selected"); } ?>>No Access</option>
                                    </select>
                                </div>
                            </div>
                        <?php
                        }
                        else{
                            print("<input type=\"hidden\" name=\"EmpUserName\" value=\"".$Employee['UserName']."\"><input type=\"hidden\" name=\"EmpPassword\" value=\"\"><input type=\"hidden\" name=\"Permission\" value=\"".$Employee["Permission"]."\">");
                        }
                        ?>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">ส่วนที่เข้าใช้งานได้:</label>
                            <div class="col-sm-3" style="margin-top:7px;">
                                <p><input type="checkbox" class="form-control" name="AccessTo[1]" value="1"<?php if(preg_match('/-1-/', $AccessList)){ print(" checked"); } ?>> ใบกำกับภาษี</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[2]" value="1"<?php if(preg_match('/-2-/', $AccessList)){ print(" checked"); } ?>> ล้างรถ/เปลี่ยนน้ำมันเครื่อง</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[3]" value="1"<?php if(preg_match('/-3-/', $AccessList)){ print(" checked"); } ?>> สินค้าและบริการ</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[14]" value="1"<?php if(preg_match('/-14-/', $AccessList)){ print(" checked"); } ?>> สินค้าพิเศษ</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[4]" value="1"<?php if(preg_match('/-4-/', $AccessList)){ print(" checked"); } ?>> คูปอง</p>
                            </div>
                            <div class="col-sm-3" style="margin-top:7px;">
                                <p><input type="checkbox" class="form-control" name="AccessTo[5]" value="1"<?php if(preg_match('/-5-/', $AccessList)){ print(" checked"); } ?>> ตั้งค่าราคาน้ำมัน</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[6]" value="1"<?php if(preg_match('/-6-/', $AccessList)){ print(" checked"); } ?>> บันทึกการขายน้ำมัน</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[13]" value="1"<?php if(preg_match('/-13-/', $AccessList)){ print(" checked"); } ?>> ตรวจสอบรายการขาย</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[7]" value="1"<?php if(preg_match('/-7-/', $AccessList)){ print(" checked"); } ?>> บันทึกรายรับ/รายจ่าย</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[8]" value="1"<?php if(preg_match('/-8-/', $AccessList)){ print(" checked"); } ?>> พนักงาน</p>
                            </div>
                            <div class="col-sm-3" style="margin-top:7px;">
                                <p><input type="checkbox" class="form-control" name="AccessTo[9]" value="1"<?php if(preg_match('/-9-/', $AccessList)){ print(" checked"); } ?>> ทะเบียนลูกค้า</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[10]" value="1"<?php if(preg_match('/-10-/', $AccessList)){ print(" checked"); } ?>> ทะเบียนลูกค้าเครดิต</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[11]" value="1"<?php if(preg_match('/-11-/', $AccessList)){ print(" checked"); } ?>> รายงานสรุป</p>
                                <p><input type="checkbox" class="form-control" name="AccessTo[12]" value="1"<?php if(preg_match('/-12-/', $AccessList)){ print(" checked"); } ?>> ตั้งค่าระบบ</p>
                            </div>
                        </div>

                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" value="employees.php">
                            <button type="submit" class="btn btn-success btn-rounder"><?php if(!intval($_REQUEST['UpdateEmp'])){ print('เพิ่มพนักงาน'); }else{ print('แก้ไขข้อมูล'); } ?></button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="reset" class="btn btn-danger btn-rounder"><?php if(!intval($_REQUEST['UpdateEmp'])){ print('ล้างข้อมูล'); }else{ print('รีเซ็ตข้อมูล'); } ?></button>
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php
}
else if(isset($_POST["AsAction"]) && $_POST["AsAction"]=='HolidayMark'){
?>

    <section class="pageContent">
        <div class="title-body">
            <h2>พนักงาน</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;">วันหยุดพิเศษ</h3>
                </div>

                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <form action="employees.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <input type="hidden" id="submitTo" value="employees.php">
                        <table class="table table-condensed table-striped table-default">
                        <?php
                            $count=1;
                            $sqlHoliday="SELECT Date, Month, Note from Holiday order by Month ASC, Date ASC;";
                            $rsHoliDay=mysql_query($sqlHoliday);
                            while($Holiday=mysql_fetch_row($rsHoliDay)){
                                HolidayPrint($count, $Holiday[0], $Holiday[1], $Holiday[2]);
                                $count++;
                            }
                            print("<tr><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr>");
                            HolidayPrint(0, 0, 0, 0);
                        ?>
                        </table>
                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" value="employees.php">
                            <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
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
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>พนักงาน</h2>
        </div>

        <div class="content-center" id="EmployeeContent">
            <div class="panel panel-default">
                <?php
                if($PermissionNo>=2){ // admin or supervisor
                    print('
                    <div class="panel-body">
                        <form method="post" id="submitForm" role="form">
                         <div class="btn-group">
                            <button class="btn btn-success" type="button" onclick="javascript:location.href=\'employees.php?UpdateEmp=0\';"><i class="fa fa-plus"></i> เพิ่มพนักงาน</button>
                        </div>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <div class="btn-group">
                            <button class="btn btn-primary" type="button" onclick="javascript:location.href=\'employees_payment.php?advance=1\';"><i class="fa fa-money"></i> เบิกล่วงหน้า</button>
                        </div>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <div class="btn-group">
                            <button class="btn btn-info" type="button" data-toggle="modal" data-target="#employeePayment"><i class="fa fa-money"></i> คำนวณค่าแรง</button>
                        </div>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>');
                    if($PermissionNo==3){ // only admin can config
                        print('
                        <div class="btn-group">
                            <input type="hidden" id="AsAction" name="AsAction" value="">
                            <button id="HolidayMark" class="btn btn-default" type="button"><i class="fa fa-clock-o"></i> ตั้งค่าวันหยุดพิเศษ</button>
                        </div>');
                    }
                    if(!isset($_POST['searchEmp'])){ $_POST['searchEmp']=''; }
                    print('<div class="btn-group right"><input type="text" name="searchEmp" value="'.$_POST['searchEmp'].'" placeholder="Search..." class="form-control"></div>');
                    print('</form></div>');
                }
                ?>
            </div>

            <?php print($alertTxt); ?>
            <div id="empList" class="row">
                <?php
                $sqlPosition = "select concat(FirstName, ' ', LastName), PositionName, EmpID, Tel from employee inner join empposition on empposition.PositionID=employee.PositionID where employee.Deleted=0 and employee.EmpID!=1";
                if(isset($_POST['searchEmp']) && trim($_POST['searchEmp'])){
                    $sqlPosition.=" and (FirstName like '%".mysql_real_escape_string(trim($_POST['searchEmp']))."%') or (LastName like '%".mysql_real_escape_string(trim($_POST['searchEmp']))."%')";
                }
                $sqlPosition.=" order by FirstName ASC, LastName ASC;";
                $rsPosition = mysql_query($sqlPosition);
                $EmpNum = mysql_num_rows($rsPosition);
                while($Position = mysql_fetch_row($rsPosition)){
                    $EmpPic='images/user-img/guest-avatar.png';
                    if(is_file('images/user-img/user-'.$Position[2].'.jpg')){
                        $EmpPic='images/user-img/user-'.$Position[2].'.jpg';
                    }
                    $sqlSalary="select SalaryType, Salary from ".$db_name.".empsalary where EmpID=".intval($Position[2])." order by StartDate DESC, ID DESC;";
                    $rsSalary=mysql_query($sqlSalary);
                    $Salary=mysql_fetch_array($rsSalary) or die(mysql_error());
                    $TimeButton="";
                    //if($Salary['SalaryType']=='วัน'){
                        $TimeButton='<button class="btn btn-info btn-xs empTime"><i class="fa fa-clock-o"></i> ลงเวลา &nbsp;</button>&nbsp;';
                    //}
                    $removeLink='';
                    if($PermissionNo==3 || $UserID==1){
                        $removeLink='&nbsp;&nbsp;<button class="btn btn-danger btn-xs removeItem" id="'.$Position[0].'"><i class="fa fa-ban"></i> ลบ &nbsp;</button>';
                    }
                    $EditButtom='';
                    if($PermissionNo>1){
                        $EditButtom='<button class="btn btn-success btn-xs editEmp"><i class="fa fa-edit"></i> แก้ไข &nbsp;</button>&nbsp;&nbsp;';
                    }
                    print('
                    <div id="item-'.$Position[2].'" class="col-xs-6 col-md-6 listProfile">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="img-circle thumnailImg" src="'.$EmpPic.'" alt="">
                                    </span>
                                    <div class="media-body" id="'.$Position[2].'">
                                        <h4 class="media-heading nameList"><a href="javascript:void(0);" class="editEmp">'.$Position[0].'</a></h4>
                                        <span class="maillist"><i class="fa fa-user"></i> '.$Position[1].'</span>
                                        <span class="maillist"><i class="fa fa-money"></i> '.number_format($Salary['Salary'], 2).' บาท/'.$Salary['SalaryType'].'</span>
                                        <span class="maillist">&nbsp;</span>
                                        <p>'.$EditButtom.$TimeButton.$removeLink.'
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>');
                }
                if(!$EmpNum && isset($_POST['searchEmp']) && trim($_POST['searchEmp'])){ // ค้นหาไม่เจอ
                    print('<div class="no_emp_search">ไม่พบข้อมูลพนักงานของ '.$_POST['searchEmp'].'</div>');
                }
                ?>
            </div>
            <input type="hidden" id="submitTo" value="employees.php">
        </div>
    </section>

    <div class="modal fade" id="employeePayment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
                <br><p><b>คำนวณค่าแรงพนักงาน</b></p>
                <?php
                $PaymentDateArr=explode(",", $lastDay4Payment);
                if($PaymentDateArr[0]){
                    print("<p><a href=\"employees_payment.php?DDay=".$PaymentDateArr[0]."\">คำนวณค่าแรงพนักงาน รอบ");
                    if($PaymentDateArr[0]==(-1)){
                        $PaymentDateArr[0]="วันสิ้นเดือน";
                    }
                    else{
                        $PaymentDateArr[0]="วันที่ ".$PaymentDateArr[0];
                    }
                    print($PaymentDateArr[0]."</a></p>");
                }
                if($PaymentDateArr[1]){
                    print("<p><a href=\"employees_payment.php?DDay=".$PaymentDateArr[1]."\">คำนวณค่าแรงพนักงาน รอบ");
                    if($PaymentDateArr[1]==(-1)){
                        $PaymentDateArr[1]="วันสิ้นเดือน";
                    }
                    else{
                        $PaymentDateArr[1]="วันที่ ".$PaymentDateArr[1];
                    }
                    print($PaymentDateArr[1]."</a></p>");
                }
                ?>
            </div>
        </div>
      </div>
    </div>
<?php
}
include("footer.php");
?>