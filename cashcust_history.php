<?php
include("dbvars.inc.php");
include("header.php");
$sqlCust="SELECT CustName from ".$db_name.".customer where Deleted=0 and customer.CustID=".intval($_REQUEST["CustID"]).";";
$rsCust=mysql_query($sqlCust);
$CustInfo=mysql_fetch_row($rsCust);
/*$OliNameArr = array();
$sqlOil="select oil.OilID, oil.Name from ".$db_name.".oil where Deleted=0 order by Name ASC;";
$rsOil=mysql_query($sqlOil);
while($Oil=mysql_fetch_row($rsOil)){
    $OliNameArr[$Oil[0]]=$Oil[1];
}*/

if(!isset($_REQUEST['StartDate'])){
    $_REQUEST['StartDate']=date("01/m/Y", time());
    $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, date("n", time()), date("Y", time())));
    $_REQUEST['EndDate']=date($DayPerMonth."/m/Y", time());
}
$SetDate=explode("/", $_REQUEST['StartDate']);
$startTime=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);

$SetEndDate=explode("/", $_REQUEST['EndDate']);
$endTime=mktime(23, 59, 59, $SetEndDate[1], $SetEndDate[0], $SetEndDate[2]);

$sqlOil="SELECT BookNo, CodeNo, RealUsed, Date, OilPrice, RealUsed, OilID, CarCode from (".$db_name.".credit_billing left join ".$db_name.".customer_car on customer_car.CarID=credit_billing.CarID) where CustID=".intval($_REQUEST["CustID"])." and Confirmed=1 and Date>=".$startTime." and Date<=".$endTime." order by Date ASC, BookNo ASC, CodeNo ASC;";
$rsOil=mysql_query($sqlOil);
$OilNum=mysql_num_rows($rsOil);


$sqlCashInfo="SELECT PaidType, PaidDate, PaidAmount, Note from ".$db_name.".cash_credit where CustomerID=".intval($_REQUEST["CustID"])." and PaidDate>=".$startTime." and PaidDate<=".$endTime." order by PaidDate ASC;";
$rsCashInfo=mysql_query($sqlCashInfo);
$CashInfo=mysql_num_rows($rsCashInfo);
?>
    <section class="pageContent">
        <div id="PageHeader" class="title-body">
            <h2>ประวัติการใช้</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form action="#" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <h5><strong>ใบสรุปรายการเติมน้ำมันระหว่างวันที่ <input type="text" class="form-control Calendar inline_input" name="StartDate" value="<?php print($_REQUEST['StartDate']); ?>" style="width:100px;"> ถึงวันที่ <input type="text" class="form-control Calendar inline_input" name="EndDate" value="<?php print($_REQUEST['EndDate']); ?>" style="width:100px;"> ของบริษัท <?php print($CustInfo[0]); ?></strong> &nbsp;&nbsp;&nbsp;<input type="submit" value="GO" class="btn btn-xs btn-primary btn-rounder"></h5><br>

                        <div id="pageContent">
                        <?php
                        if($CashInfo){
                        ?>
                        <table style="width:550px;" class="table table-condensed table-striped table-default table_border td_center">
                            <tr>
                                <th colspan="8" style="padding:10px;"><strong>ข้อมูลวางเงิน ระหว่างวันที่ <?php print($_REQUEST['StartDate']); ?> ถึงวันที่ <?php print($_REQUEST['EndDate']); ?></strong></th>
                            </tr>
                            <tr>
                                <th>วันที่</th>
                                <th>ชำระโดย</th>
                                <th>วางเงิน</th>
                                <th width="30%">หมายเหตุ</th>
                            </tr>
                        <?php
                            $TotalCash=0;
                            while($CashInfo=mysql_fetch_row($rsCashInfo)){
                                print('
                                <tr>
                                    <td>'.date('d/m/Y', $CashInfo[1]).'</td>
                                    <td>'.$CashInfo[0].'</td>
                                    <td>'.number_format($CashInfo[2], 2).'</td>
                                    <td>'.$CashInfo[3].'</td>
                                </tr>');
                                $TotalCash+=round($CashInfo[2], 2);
                            }
                            print("<tr><td colspan=\"2\" style=\"text-align:right;\">&nbsp;<strong>รวม:</strong>&nbsp;</td><td>".number_format($TotalCash, 2)."</td><td>&nbsp;</td></tr>");
                            print("</table><br><br>");
                        }
                        if($OilNum){
                        ?>
                            <table class="table table-condensed table-striped table-default table_border td_center">
                            <tr>
                                <th colspan="8" style="padding:10px;"><strong>ใบสรุปรายการเติมน้ำมันระหว่างวันที่ <?php print($_REQUEST['StartDate']); ?> ถึงวันที่ <?php print($_REQUEST['EndDate']); ?> ของบริษัท <?php print($CustInfo[0]); ?></strong></th>
                            </tr>
                            <tr>
                                <th>ลำดับ</th>
                                <th>เล่มที่/เลขที่</th>
                                <th>วันที่</th>
                                <th>น้ำมัน</th>
                                <th>ราคาลิตรละ</th>
                                <th>ลิตร</th>
                                <th>จำนวนเงิน</th>
                                <th>ทะเบียนรถ</th>
                            </tr>
                            <?php
                                $count=1;
                                $Total=0;
                                $TotalUse=0;
                                while($Oil=mysql_fetch_row($rsOil)){
                                    $subsqlOil="select Name from ".$db_name.".oil where OilID=".$Oil[6].";";
                                    $subrsOil=mysql_query($subsqlOil);
                                    $SubOil=mysql_fetch_array($subrsOil);
                                    $Howmuch=round($Oil[2]/$Oil[4], 2);
                                    $BookCodeNoTxt=$Oil[1];
                                    if($Oil[0]){
                                        $BookCodeNoTxt=$Oil[0].'/'.$Oil[1];
                                    }
                                    print('
                                    <tr>
                                        <td>'.$count.'</td>
                                        <td>'.$BookCodeNoTxt.'</td>
                                        <td>'.date('d/m/Y', $Oil[3]).'</td>
                                        <td>'.$SubOil[0].'</td>
                                        <td>'.number_format($Oil[4], 2).'</td>
                                        <td>'.number_format($Howmuch, 2).'</td>
                                        <td>'.number_format($Oil[2], 2).'</td>
                                        <td>'.$Oil[7].'</td>
                                    </tr>');
                                    $count++;
                                    $TotalUse+=round($Howmuch, 2);
                                    $Total+=round($Oil[2], 2);
                                }
                                print("<tr><td colspan=\"5\" style=\"text-align:right;\">&nbsp;<strong>รวม:</strong>&nbsp;</td><td>".number_format($TotalUse, 2)."</td><td>".number_format($Total, 2)."</td><td>&nbsp;</td></tr>");
                                print("</table>");
                            }
                            else{
                                print("<p style=\"color:red; text-align:center;\"><strong>ไม่มีรายการใช้น้ำมันในช่วงเวลาที่กำหนด</strong></p>");
                            }
                            ?>

                            <br><br>
                            <div id="actionBar" class="actionBar right">
                                <input type="hidden" id="submitTo" value="oil.php">
                                <input type="hidden" id="backPage" value="<?php
                                if(isset($_REQUEST['back']) && trim($_REQUEST['back'])){
                                    print($_REQUEST['back'].".php");
                                    if(isset($_REQUEST['from'])){
                                        print("?from=".$_REQUEST['from']);
                                    }
                                }
                                else{
                                    if(!isset($_REQUEST['page'])){
                                        $_REQUEST['page']=1;
                                    }
                                    print('cash-customer.php?page='.$_REQUEST['page']);
                                } ?>">
                                <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <!-- <button type="button" class="btn btn-info btn-rounder" onclick="javascript:location.href='manage-billing.php?BillingExport=1&CustID=<?php //print($_REQUEST["CustID"]); ?>&History=<?php //print($_REQUEST["History"]); ?>'; return false;">พิมพ์ Excel</button>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
                                <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                                &nbsp;&nbsp;&nbsp;
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php
include("footer.php");
?>