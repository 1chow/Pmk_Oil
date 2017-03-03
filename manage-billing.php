<?php
include("dbvars.inc.php");
if(!preg_match('/-10-/', $EmpAccess) && !preg_match('/-11-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}
else if(isset($_REQUEST["BillingExport"]) && intval($_REQUEST["BillingExport"])){
    header("Content-Type: application/vnd.ms-excel");
    header("content-type:application/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=1.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><style type="text/css"> body { font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; text-align: justify; font-size:12px; } p { font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; } h1{font-size:14px;} div{ font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; } .TBDetails td, .TBDetails th{ border-top:1px solid #000; border-left:1px solid #000;}</style>');
    $sqlCust="SELECT CustName, Total, CollectSchedule, CreditLimit from (".$db_name.".customer inner join ".$db_name.".billing_history on customer.CustID=billing_history.CustID) where Deleted=0 and billing_history.CustID=".intval($_REQUEST["CustID"])." and PaidDate=0 and billing_history.ID=".intval($_REQUEST["History"]).";";
    $rsCust=mysql_query($sqlCust);
    $CustInfo=mysql_fetch_row($rsCust);

    $OliNameArr = array();
    $sqlOil="select oil.OilID, oil.Name from ".$db_name.".oil where Deleted=0 order by Name ASC;";
    $rsOil=mysql_query($sqlOil);
    while($Oil=mysql_fetch_row($rsOil)){
        $OliNameArr[$Oil[0]]=$Oil[1];
    }
    $count=1;
    $Total=0;
    $TotalUse=0;
    $sqlOil="SELECT MIN(Date), MAX(Date) from (".$db_name.".credit_billing left join ".$db_name.".customer_car on customer_car.CarID=credit_billing.CarID) where Status=".intval($_REQUEST["History"])." and CustID=".intval($_REQUEST["CustID"])." and Confirmed=1 order by Date ASC, BookNo ASC, CodeNo ASC;";
    $rsOil=mysql_query($sqlOil);
    $Oil=mysql_fetch_row($rsOil);
    $MinDate=date("d", $Oil[0])." ".$monthList[(date("n", $Oil[0])-1)]." ".date("Y", $Oil[0]);
    $MaxDate=date("d", $Oil[1])." ".$monthList[(date("n", $Oil[1])-1)]." ".date("Y", $Oil[1]);
    //$Billing="<table border=\"1\"><tr><th colspan=\"9\"><strong>ใบสรุปรายการเติมน้ำมันระหว่างวันที่ ".$MinDate." - ".$MaxDate." ของบริษัท ".$CustInfo[0]."</strong></th></tr>";
    $Billing="<table border=\"1\"><tr><td colspan=\"9\"><strong>ชื่อบริษัท: ".$CustInfo[0]."</strong><span style=\"margin-left:50px;\">วงเงินเครดิต: ".number_format($CustInfo[3], 2)."</span></td></tr>";
    $Billing.="<tr><td colspan=\"9\">&nbsp;</td></tr>";
    $Billing.="<tr><th><strong>ลำดับ</strong></th><th><strong>เล่มที่/เลขที่</strong></th><th><strong>วันที่</strong></th><th><strong>น้ำมัน</strong></th><th><strong>ราคาลิตรละ</strong></th><th><strong>ลิตร</strong></th><th><strong>จำนวนเงิน</strong></th><th><strong>ทะเบียนรถ</strong></th><th><strong>หมายเหตุ</strong></th></tr>";
    $sqlOil="SELECT BookNo, CodeNo, RealUsed, Date, OilPrice, RealUsed, OilID, CarCode from (".$db_name.".credit_billing left join ".$db_name.".customer_car on customer_car.CarID=credit_billing.CarID) where Status=".intval($_REQUEST["History"])." and CustID=".intval($_REQUEST["CustID"])." and Confirmed=1 order by Date ASC, BookNo ASC, CodeNo ASC;";
    $rsOil=mysql_query($sqlOil);
    while($Oil=mysql_fetch_row($rsOil)){
        $Howmuch=round($Oil[2]/$Oil[4], 2);
        $UseDate=date('d/m/Y', $Oil[3]);
        $BookCodeNoTxt=$Oil[1];
        if($Oil[0]){
            $BookCodeNoTxt=$Oil[0].'/'.$Oil[1];
        }
        $Billing.='<tr><td>'.$count.'</td><td>'.$BookCodeNoTxt.'</td><td>'.$UseDate.'</td><td>'.$OliNameArr[$Oil[6]].'</td><td>'.number_format($Oil[4], 2).'</td><td>'.number_format($Howmuch, 2).'</td><td>'.number_format($Oil[2], 2).'</td><td>'.$Oil[7].'</td><td>&nbsp;</td></tr>';
        $count++;
        $TotalUse+=round($Howmuch, 2);
        $Total+=round($Oil[2], 2);
    }
    $Billing.="<tr><td colspan=\"5\" align=\"right\">&nbsp;รวม:</td><td>".number_format($TotalUse, 2)."</td><td>".number_format($Total, 2)."</td><td colspan=\"2\">&nbsp;</td>";
    $Billing.="<tr><td colspan=\"9\">&nbsp;</td></tr>";
    $Billing.="<tr><td colspan=\"9\">&nbsp;</td></tr>";
    $Billing.="<tr><td colspan=\"9\"><strong>รับใบเสร็จรับเงินแล้ว</strong></td></tr>";
    $Billing.="<tr><td colspan=\"9\">&nbsp;</td></tr>";
    $Billing.="<tr><td colspan=\"9\"><strong>ผู้รับวางบิล/ใบเสร็จรับเงิน....................................................</strong></td></tr>";
    $CollectDate=date("d", $CustInfo[2])." ".$monthList[(date("n", $CustInfo[2])-1)]." ".date("Y", $CustInfo[2]);
    $Billing.="<tr><td colspan=\"9\"><strong>กำหนดรับเช็คภายในวันที่ ".$CollectDate."</strong></td></tr>";
    $Billing.="</table>";
    print($Billing);
    // print("ลำดับ่\tเล่มที่/เลขที่\tวันที่\tน้ำมัน\tราคาลิตรละ\tลิตร\tจำนวนเงิน\tทะเบียนรถ\tหมายเหตุ \r\n");
    // $sqlOil="SELECT BookNo, CodeNo, RealUsed, Date, OilPrice, RealUsed, OilID, CarCode from (".$db_name.".credit_billing left join ".$db_name.".customer_car on customer_car.CarID=credit_billing.CarID) where Status=".intval($_REQUEST["History"])." and CustID=".intval($_REQUEST["CustID"])." and Confirmed=1 order by Date ASC, BookNo ASC, CodeNo ASC;";
    // $rsOil=mysql_query($sqlOil);
    // while($Oil=mysql_fetch_row($rsOil)){
    //     $Howmuch=round($Oil[2]/$Oil[4], 2);
    //     print($count);
    //     print("\t".$Oil[0].'/'.$Oil[1]);
    //     print("\t".date('d/m/Y', $Oil[3]));
    //     print("\t".$OliNameArr[$Oil[6]]);
    //     print("\t".number_format($Oil[4], 2));
    //     print("\t".number_format($Howmuch, 2));
    //     print("\t".number_format($Oil[2], 2));
    //     print("\t".$Oil[7]);
    //     print("\t\r\n");
    //     $count++;
    // }
    exit();
}

$alertTxt='';
if(isset($_POST['updateID']) && intval($_POST["updateID"])){ // แก้ไขวันวางบิล/วันนัดชำระเงิน
    $DateCut = explode("/", trim($_POST["Date2Set"]));
    $DateSelected = mktime(0, 0, 0, $DateCut[1], $DateCut[0], $DateCut[2]);
    if($_POST['updateSchedule']=='billing'){
        $sqlUpdate="UPDATE ".$db_name.".billing_history set BillingDate=".intval($DateSelected)." where ID=".intval($_POST['updateID']).";";
    }
    else{
        $sqlUpdate="UPDATE ".$db_name.".billing_history set CollectSchedule=".intval($DateSelected)." where ID=".intval($_POST['updateID']).";";
    }
    $rsUpdate=mysql_query($sqlUpdate);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}
else if(isset($_POST['updateBillingID']) && intval($_POST['updateBillingID'])){
    $DateCut = explode("/", trim($_POST["Date2Collect"]));
    $DateSelected = mktime(0, 0, 0, $DateCut[1], $DateCut[0], $DateCut[2]);
    $sqlUpdate="UPDATE ".$db_name.".billing_history set PaidDate=".intval($DateSelected).", PayBy='".mysql_real_escape_string(trim($_POST["PaidBy"]))."', Note='".mysql_real_escape_string(trim($_POST["Note"]))."' where ID=".intval($_POST['updateBillingID']).";";
    $rsUpdate=mysql_query($sqlUpdate);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}

include("header.php");

if(isset($_REQUEST['report']) && intval($_REQUEST['report'])){
    $SetYear=date("Y", time());
    $SetMonth=date("n", time());
    if(isset($_POST['PaidMonth'])){
        $SetYear=$_POST['PaidYear'];
        $SetMonth=$_POST['PaidMonth'];
    }
    $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $SetMonth, $SetYear));
    $StartPayDate = mktime(0, 0, 0, $SetMonth, 1, $SetYear);
    $EndPayDate = mktime(23, 59, 59, $SetMonth, $DayPerMonth, $SetYear);
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ประวัติการชำระเงิน</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form name="CollectHistoryForm" action="manage-billing.php" method="post">
                    <input type="hidden" name="from" value="<?php if(isset($_REQUEST['from']) && trim($_REQUEST['from'])){ print($_REQUEST['from']); } ?>">
                    <input type="hidden" name="report" value="1">
                    <?php
                    print("<div class=\"form-group\" style=\"text-align:center;\">เดือน: <select name=\"PaidMonth\" class=\"form-control input-sm\" style=\"display:inline; width:90px;\" onchange=\"document.forms['CollectHistoryForm'].submit();\">");
                    for($i=0; $i<count($monthList); $i++){
                        print("<option value=\"".($i+1)."\"");
                        if(($i+1) == $SetMonth){
                            print(" selected");
                        }
                        print(">".$monthList[$i]."</option>");
                    }
                    print("</select>");
                    print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
                    print("ปี: <select name=\"PaidYear\" class=\"form-control input-sm\" style=\"display:inline; width:100px;\" onchange=\"document.forms['AdvanceForm'].submit();\">");
                    for($i=(date("Y", time())-1); $i<=(date("Y", time())+1); $i++){
                        print("<option value=\"".$i."\"");
                        if($i == $SetYear){
                            print(" selected");
                        }
                        print(">".($i+543)."</option>");
                    }
                    print("</select></div><br>");

                    $sqlBilling="SELECT CustName, Total, PaidDate, Note, billing_history.ID, PayBy from (".$db_name.".billing_history inner join ".$db_name.".customer on customer.CustID=billing_history.CustID) where PaidDate>=".intval($StartPayDate)." and PaidDate<=".intval($EndPayDate)." order by CustName ASC, BillingDate ASC, CollectSchedule ASC;";
                    $rsBilling=mysql_query($sqlBilling);
                    $BillingWaiting=mysql_num_rows($rsBilling);
                    if($BillingWaiting){
                        print('<table class="td_center table table-condensed table-striped table-default table_border">
                                <tr>
                                    <th>บริษัท</th>
                                    <th>มูลค่า</th>
                                    <th>วันที่ชำระเงิน</th>
                                    <th>วิธีการชำระเงิน</th>
                                    <th>หมายเหตุ</th>
                                </tr>');
                        while($Billing=mysql_fetch_row($rsBilling)){
                            print('<tr>
                                <td class="text-left">&nbsp;'.$Billing[0].'</td>
                                <td class="text-right">'.number_format($Billing[1], 2).'&nbsp;</td>
                                <td>'.date('d-m-Y', $Billing[2]).'</td>
                                <td class="text-left">&nbsp;'.$Billing[5].'</td>
                                <td class="text-left">&nbsp;'.$Billing[3].'</td>
                            </tr>');
                        }
                        print('</table>');
                    }
                    else{
                        print('<br><p class="passcode_send-error">ไม่มีประวัติการชำระเงินในเดือนที่กำหนด</p>');
                    }
                    ?>
                    </form>

                    <br>
                    <div class="actionBar right">
                        <input type="hidden" id="backPage" name="backPage" value="<?php if(isset($_REQUEST['from']) && trim($_REQUEST['from'])){ print($_REQUEST['from'].'.php'); }else{ print('customer.php'); } ?>">
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
}
else{
    $moreCondition="";
    if(!isset($_REQUEST['actionFor'])){
        $_REQUEST['actionFor']="";
    }
    if($_REQUEST['actionFor']=='billing'){
        $moreCondition=" and FROM_UNIXTIME(BillingDate, '%m-%d-%Y')='".date("m-d-Y", time())."'";
    }
    else if($_REQUEST['actionFor']=='collect'){
        $moreCondition=" and FROM_UNIXTIME(CollectSchedule, '%m-%d-%Y')='".date("m-d-Y", time())."'";
    }
    $sqlBilling="SELECT CustName, Total, BillingDate, CollectSchedule, billing_history.ID, customer.CustID, CreditTerm, SpecialTerm from (".$db_name.".billing_history inner join ".$db_name.".customer on customer.CustID=billing_history.CustID) where PaidDate=0".$moreCondition." order by CustName ASC, BillingDate ASC, CollectSchedule ASC;";
    $rsBilling=mysql_query($sqlBilling);
    $BillingWaiting=mysql_num_rows($rsBilling);
    if(!isset($_REQUEST['from'])){
        $setFrom='index';
    }
    else{
        $setFrom=$_REQUEST['from'];
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>บันทึกการวางบิล/ชำระเงิน</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <form action="manage-billing.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <?php
                        if($BillingWaiting>0){
                            print('<table class="td_center table table-condensed table-striped table-default table_border">
                            <tr>
                                <th>บริษัท</th>
                                <th>วางบิล</th>
                                <th>วันวางบิล</th>
                                <th>วันนัดชำระเงิน</th>
                                <th>&nbsp;</th>
                                <th width="15%">&nbsp;</th>
                            </tr>');
                            while($Billing=mysql_fetch_row($rsBilling)){
                                $addClass='';
                                if($Billing[3] <= time()){ // collect now
                                    $addClass=' class="collect_now"';
                                }
                                else if($Billing[2] <= time()){ // billing now
                                    $addClass=' class="billing_now"';
                                }
                                $MoveAction="&nbsp;";
                                if($Billing[6]==2){
                                    $SelectDay=0;
                                    $DayArr=explode(",", trim($Billing[7]));
                                    $DayCheckNo=date('N', time());
                                    foreach($DayArr as $key => $value) {
                                        if(!$SelectDay){
                                            if($DayCheckNo < $value){ // เจอวันมากกว่าวันนี้
                                                $SelectDay=($value-$DayCheckNo);
                                            }
                                        }
                                    }
                                    if(!$SelectDay){
                                        $SelectDay=7;
                                    }
                                    $CollectDate=strtotime(date('Y-m-d', $Billing[2]).' +'.$SelectDay.' day');
                                    $MoveAction='<button class="btn btn-error btn-xs" title="เลื่อนวันวางบิล" onclick="; setValue(\'billing\', '.$Billing[4].', \''.date('d/m/Y', $CollectDate).'\'); return false;" data-toggle="modal" data-target="#myModal"><i class="fa fa-forward"></i></button>';
                                }
                                else if($Billing[6]==1){ // โดยวันที่
                                    $SelectDay=0;
                                    $DayArr=explode(",", trim($Billing[7]));
                                    $MinOfDay=min($DayArr);
                                    $DayCheckNo=date('j', time());
                                    foreach($DayArr as $key => $value) {
                                        if(!$SelectDay){
                                            if($DayCheckNo < $value){ // เจอวันมากกว่าวันนี้
                                                $SelectDay=($value-$DayCheckNo);
                                            }
                                        }
                                    }
                                    $CollectDate=strtotime(date('Y-m-d', $Billing[2]).' +'.$SelectDay.' day');
                                    if(!$SelectDay){
                                        $ThisMonth=date("n", time());
                                        $CollectDate=mktime(0, 0, 0, ($ThisMonth+1), intval($MinOfDay), date("Y", time()));
                                    }
                                    $MoveAction='<button class="btn btn-error btn-xs" title="เลื่อนวันวางบิล" onclick="; setValue(\'billing\', '.$Billing[4].', \''.date('d/m/Y', $CollectDate).'\'); return false;" data-toggle="modal" data-target="#myModal"><i class="fa fa-forward"></i></button>';
                                }
                                print('<tr>
                                    <td'.$addClass.'>'.$Billing[0].'</td>
                                    <td'.$addClass.'><a href="payment_history.php?CustID='.$Billing[5].'&History='.$Billing[4].'&back=manage-billing&from='.$setFrom.'" style="color:#694c96;" title="ดูรายละเอียด">'.number_format($Billing[1], 2).'</a></td>
                                    <td'.$addClass.'><a href="void(0);" onclick="setValue(\'billing\', '.$Billing[4].', \''.date('d/m/Y', $Billing[2]).'\');" data-toggle="modal" data-target="#myModal">'.date('d-m-Y', $Billing[2]).'</a></td>
                                    <td'.$addClass.'><a href="void(0);" onclick="setValue(\'collect\', '.$Billing[4].', \''.date('d/m/Y', $Billing[3]).'\');" data-toggle="modal" data-target="#myModal">'.date('d-m-Y', $Billing[3]).'</a></td>
                                    <td'.$addClass.'><a href="void(0);" data-toggle="modal" data-target="#myModal2" onclick="setBilling(\''.$Billing[0].'\', \''.number_format($Billing[1], 2).'\', '.$Billing[4].');">บันทึกการชำระเงิน</a></td>
                                    <td'.$addClass.' style="text-align:left;">
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <button class="btn btn-success btn-xs" title="พิมพ์ใบวางบิล (excel)" onclick="javascript:location.href=\'manage-billing.php?BillingExport=1&CustID='.$Billing[5].'&History='.$Billing[4].'\'; return false;"><i class="fa fa-file-excel-o"></i></button>
                                        &nbsp;&nbsp;
                                        <button class="btn btn-info btn-xs" title="เรียกดูใบวางบิล" onclick="javascript:location.href=\'payment_history.php?CustID='.$Billing[5].'&History='.$Billing[4].'&back=manage-billing&from='.$setFrom.'\'; return false;"><i class="fa fa-file-text-o"></i></button>
                                        &nbsp;&nbsp;
                                        '.$MoveAction.'
                                    </td>
                                </tr>');
                            }
                            print('</table>');
                        }
                        else{
                            print('<br><br><p class="passcode_send-error">ไม่มีบริษัทที่รอวางบิล / นัดชำระเงินในวันนี้</p>');
                        }
                        ?>
                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" name="backPage" value="<?php if(isset($_REQUEST['from']) && trim($_REQUEST['from'])){ print($_REQUEST['from'].'.php'); }else{ print('customer.php'); } ?>">
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form action="manage-billing.php" method="post" class="form-horizontal" onsubmit="return checkBillingDate();">
                <input type="hidden" name="updateSchedule" id="updateSchedule" value="">
                <input type="hidden" name="updateID" id="updateID" value="">
                <input type="hidden" name="oldDate" id="oldDate" value="">
                <input type="hidden" name="actionFor" id="actionFor" value="<?php print($_REQUEST["actionFor"]); ?>">
                <input type="hidden" name="from" value="<?php if(isset($_REQUEST['from']) && trim($_REQUEST['from'])){ print($_REQUEST['from']); } ?>">
                <input type="hidden" name="oldDateTxt" id="oldDateTxt" value="<?php print(date('d/m/Y', time())); ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">เปลี่ยนวันวางบิล</h4>
                </div>
                <div class="modal-body text-center">
                    <b>วันที่:</b> <input type="text" class="form-control inline_input Calendar" name="Date2Set" id="Date2Set" value="<?php print(date('d/m/Y', time())); ?>" style="width:120px;">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">ตกลง</button>
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
            <form action="manage-billing.php" method="post" class="form-horizontal">
                <input type="hidden" name="updateBillingID" id="updateBillingID" value="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">บันทึกการชำระเงิน</h4>
                </div>
                <div class="modal-body" style="overflow:hidden;">
                    <p class="col-sm-12">
                        <label class="control-label col-sm-3">บริษัท:</label>
                        <span style="margin-top:6px;" class="col-sm-9" id="BillingName"></span>
                    </p>
                    <p class="col-sm-12">
                        <label class="control-label col-sm-3">มูลค่า:</label>
                        <span style="margin-top:6px;" class="col-sm-9" id="BillingTotal"></span></span>
                    </p>
                    <p class="col-sm-12">
                        <label class="control-label col-sm-3">วันที่จ่าย:</label>
                        <span class="col-sm-9"><input type="text" class="form-control inline_input Calendar" name="Date2Collect" id="Date2Collect" value="<?php print(date('d/m/Y', time())); ?>" style="width:120px;"></span>
                    </p>
                    <p class="col-sm-12">
                        <label class="control-label col-sm-3">วิธีการชำระเงิน:</label>
                        <span class="col-sm-9">
                            <select name="PaidBy" class="form-control input-sm inline_input" style="width:160px;">
                            <option value="เงินสด">เงินสด</option>
                            <option value="เช็ค">เช็ค</option>
                            <option value="บัตรเครดิต">บัตรเครดิต</option>
                            <option value="โอนเงินเข้าธนาคาร">โอนเงินเข้าธนาคาร</option>
                            </select>
                        </span>
                    </p>
                    <p class="col-sm-12">
                        <label class="control-label col-sm-3">หมายเหตุ:</label>
                        <span class="col-sm-9"><textarea class="form-control inline_input" name="Note" id="Note"></textarea></span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">บันทึก</button>
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