<?php
include("dbvars.inc.php");
if(!preg_match('/-3-/', $EmpAccess) && !preg_match('/-11-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}
else if(isset($_REQUEST["ExcelExport"]) && intval($_REQUEST["ExcelExport"])){
    $getMonth=date("n", $_REQUEST["startDate"]);
    $getYear=date("Y", $_REQUEST["startDate"]);
    header("Content-Type: application/vnd.ms-excel");
    header("content-type:application/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=รายงานภาษีซื้อ ".$monthList[$getMonth-1]." ".($getYear+543).".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><style type="text/css"> body { font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; text-align: justify; font-size:12px; } p { font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; } h1{font-size:14px;} div{ font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; } .TBDetails td, .TBDetails th{ border-top:1px solid #000; border-left:1px solid #000;}</style>');
    print("<table width=\"100%\"><tr><td colspan=\"7\">รายงานภาษีซื้อประจำเดือน".$monthList[$getMonth-1]." ".($getYear+543)."</td></tr>");
    print("<tr><td colspan=\"7\">ผู้ประกอบการ: ".$CompanyName."</td></tr>");
    print("<tr><td colspan=\"7\">".preg_replace("\r\n", " ", $CompanyAddress)."</td></tr>");
    print("<tr><td colspan=\"7\">เลขประจำตัวผู้เสียภาษี ".$CompanyCode."</td></tr>");
    print("<tr><td width=\"5%\">ลำดับที่</td><td width=\"15%\">เล่มที่/เลขที่</td><td width=\"15%\">วัน/เดือน/ปี</td><td width=\"15%\">รายการ</td><td width=\"15%\">จำนวนเงิน</td><td width=\"15%\">จำนวนภาษี</td><td width=\"15%\">รวมเป็นเงิน</td></tr>");
    $sqlHistory="select Name, sum(Total), BookCodeNo, PaidDate from ".$db_name.".account_daily where PaidDate>=".intval($_REQUEST["startDate"])." and PaidDate<=".intval($_REQUEST["endDate"])." and BookCodeNo!='' group by BookCodeNo order by PaidDate ASC;";
    $rsHistory=mysql_query($sqlHistory);
    $count=1;
    while($History=mysql_fetch_row($rsHistory)){
        $vatVal=round(($History[1]*7)/100, 2);
        $subTotalVal=round($History[1]-$vatVal);
        print("<tr><td style=\"text-align:right;\">".$count."</td><td>".$History[2]."</td><td>".date("d/m/Y", $History[3])."</td><td>".$History[0]."</td><td style=\"text-align:right;\">".number_format($subTotalVal, 2)."</td><td style=\"text-align:right;\">".number_format($vatVal, 2)."</td><td style=\"text-align:right;\">".number_format($History[1], 2)."</td></tr>");
        $count++;
    }
    print("</table>");
    exit();
}

include("header.php");

if(!isset($_REQUEST['serviceDate'])){
    $_REQUEST['serviceDate']=date("d/m/Y", time());
    $_REQUEST['serviceDateTo']=date("d/m/Y", time());
}
if(!isset($_REQUEST['serviceType'])){
    $_REQUEST['serviceType']=0;
}
if(!isset($_REQUEST['back'])){
    $_REQUEST['back']='stock';
}
if(isset($_REQUEST["special"]) && intval($_REQUEST["special"])){
    $_REQUEST["back"]="special-stock";
    $_REQUEST["special"]=1;
    $specialCond=" and Special=1";
}
else if($_REQUEST["back"]!='reports'){
    $_REQUEST["back"]="stock";
    $_REQUEST["special"]=0;
    $specialCond=" and Special=0";
}
else{
    $_REQUEST["back"]="reports";
    $specialCond="";
}

$SetDate=explode("/", $_REQUEST['serviceDate']);
$startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
$endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
if($_REQUEST['serviceDateTo']!=$_REQUEST['serviceDate']){
    $SetDateTo=explode("/", $_REQUEST['serviceDateTo']);
    $endDate=mktime(23, 59, 59, $SetDateTo[1], $SetDateTo[0], $SetDateTo[2]);
}

if(!isset($_REQUEST['page']) || !$_REQUEST['page']){
    $_REQUEST['page']=1;
}
if(!isset($_REQUEST['importYear'])){
    $_REQUEST['importYear']=date("Y", time());
    $_REQUEST['importMonth']=date("n", time());
}
$DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $_REQUEST['importMonth'], $_REQUEST['importYear']));
$startDate=mktime(0, 0, 0, $_REQUEST['importMonth'], 1, $_REQUEST['importYear']);
$endDate=mktime(23, 59, 59, $_REQUEST['importMonth'], $DayPerMonth, $_REQUEST['importYear']);
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>รายงานภาษีซื้อ</h2>
        </div>
       <br>
        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <form action="saletax-report.php" method="post" class="form-horizontal" role="form" id="ImportForm">
                        <input type="hidden" name="page" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="reportType" value="2">
                        <h3 class="panel-title" style="margin:0;">
                        <?php
                            print("<div class=\"form-group\" style=\"margin:0; text-align:center;\">");
                            print("เดือน: <select id=\"importMonth\" name=\"importMonth\" class=\"form-control input-sm\" style=\"display:inline; width:90px;\">");
                            for($i=0; $i<count($monthList); $i++){
                                print("<option value=\"".($i+1)."\"");
                                if(($i+1) == $_REQUEST['importMonth']){
                                    print(" selected");
                                }
                                print(">".$monthList[$i]."</option>");
                            }
                            print("</select>");
                            print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
                            print("ปี: <select id=\"importYear\" name=\"importYear\" class=\"form-control input-sm\" style=\"display:inline; width:100px;\">");
                            for($i=(date("Y", time())-2); $i<=(date("Y", time())+2); $i++){
                                print("<option value=\"".$i."\"");
                                if($i == $_REQUEST['importYear']){
                                    print(" selected");
                                }
                                print(">".$i."</option>");
                            }
                            print("</select>");
                            print("</div>");
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">
                    <p>รายงานภาษีซื้อประจำเดือน<?php print($monthList[$_REQUEST['importMonth']-1]." ".($_REQUEST['importYear']+543)); ?></p>
                    <p>ผู้ประกอบการ: <?php print($CompanyName); ?></p>
                    <p><?php print(($CompanyAddress)); ?></p>
                    <p>เลขประจำตัวผู้เสียภาษี <?php print($CompanyCode); ?></p>
                    <table width="100%" border="1" class="coupon_history">
                        <tr>
                            <td width="5%">ลำดับที่</td>
                            <td width="15%">เล่มที่/เลขที่</td>
                            <td width="15%">วัน/เดือน/ปี</td>
                            <td width="15%">รายการ</td>
                            <td width="15%">จำนวนเงิน</td>
                            <td width="15%">จำนวนภาษี</td>
                            <td width="15%">รวมเป็นเงิน</td>
                        </tr>
                        <?php
                        $sqlHistory="select Name, sum(Total), BookCodeNo, PaidDate from ".$db_name.".account_daily where PaidDate>=".$startDate." and PaidDate<=".$endDate." and BookCodeNo!='' group by BookCodeNo order by PaidDate ASC;";
                        //echo $sqlHistory;
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            $count=1;
                            while($History=mysql_fetch_row($rsHistory)){
                                $vatVal=round(($History[1]*7)/100, 2);
                                $subTotalVal=round($History[1]-$vatVal);
                                print('<tr>
                                    <td>'.$count.'</td>
                                    <td class="text-left">&nbsp;'.$History[2].'</td>
                                    <td>'.date("d/m/Y", $History[3]).'</td>
                                    <td class="text-left">&nbsp;'.$History[0].'</td>
                                    <td style="text-align:right;">'.number_format($subTotalVal, 2).'</td>
                                    <td style="text-align:right;">'.number_format($vatVal, 2).'</td>
                                    <td style="text-align:right;">'.number_format($History[1], 2).'</td>
                                    </tr>');
                                $count++;
                            }
                            print('</table><br>');
                        }
                        else{
                            print('<tr><td colspan="7" style="padding:15px;"><span style="color:red;">ไม่พบรายการในเดือนที่กำหนด</span></td></tr></table>');
                        }
                        ?>
                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <input type="hidden" name="page" id="setPage" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" name="back" id="setBack" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="button" class="btn btn-info btn-rounder" onclick="javascript:location.href='saletax-report.php?ExcelExport=1&startDate=<?php print($startDate); ?>&endDate=<?php print($endDate); ?>'; return false;">พิมพ์ Excel</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        &nbsp;&nbsp;&nbsp;
                    </div>

                </div>
            </div>
        </div>
    </section>
<?php
include("footer.php");
?>