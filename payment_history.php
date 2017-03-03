<?php
include("dbvars.inc.php");
if(isset($_POST['delBillingID']) && intval($_POST['delBillingID'])){
    $sqlUpdate="DELETE FROM ".$db_name.".billing_history where billing_history.ID=".intval($_POST['delBillingID'])." and CustID=".intval($_POST['CustID']).";";
    $rsUpdate=mysql_query($sqlUpdate);

    $sqlUpdate="UPDATE ".$db_name.".credit_billing SET Status=0 WHERE Status=".intval($_POST['delBillingID'])." and CustID=".intval($_POST['CustID']).";";
    $rsUpdate=mysql_query($sqlUpdate);
    header('location: '.$_POST['backPage']);
}

include("header.php");

$sqlCust="SELECT CustName, Total, CollectSchedule, CreditLimit, sum(billing_history.PaidDate) from (".$db_name.".customer inner join ".$db_name.".billing_history on customer.CustID=billing_history.CustID) where Deleted=0 and billing_history.CustID=".intval($_REQUEST["CustID"])." and PaidDate=0 and billing_history.ID=".intval($_REQUEST["History"]).";";
$rsCust=mysql_query($sqlCust);
$CustInfo=mysql_fetch_row($rsCust);

$sqlOil="SELECT MIN(Date), MAX(Date) from (".$db_name.".credit_billing left join ".$db_name.".customer_car on customer_car.CarID=credit_billing.CarID) where Status=".intval($_REQUEST["History"])." and CustID=".intval($_REQUEST["CustID"])." and Confirmed=1 order by Date ASC, BookNo ASC, CodeNo ASC;";
$rsOil=mysql_query($sqlOil);
$Oil=mysql_fetch_row($rsOil);
$MinDate=date("d", $Oil[0])." ".$monthList[(date("n", $Oil[0])-1)]." ".date("Y", $Oil[0]);
$MaxDate=date("d", $Oil[1])." ".$monthList[(date("n", $Oil[1])-1)]." ".date("Y", $Oil[1]);

/*$OliNameArr = array();
$sqlOil="select oil.OilID, oil.Name from ".$db_name.".oil where Deleted=0 order by Name ASC;";
$rsOil=mysql_query($sqlOil);
while($Oil=mysql_fetch_row($rsOil)){
    $OliNameArr[$Oil[0]]=$Oil[1];
}*/
?>
    <section id="pageContent" class="pageContent">
        <div id="PageHeader" class="title-body">
            <h2>ประวัติการใช้</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form action="payment_history.php" name="payment_billing" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <h5 style="margin-bottom:20px; font-size:15px;">
                            <strong>ชื่อบริษัท: <?php print($CustInfo[0]); ?></strong><span style="margin-left:50px;">วงเงินเครดิต: <?php print(number_format($CustInfo[3], 2)); ?></span>
                        </h5>
                        <table class="table table-condensed table-striped table-default table_border td_center">
                        <tr>
                            <th>ลำดับ</th>
                            <th>เล่มที่/เลขที่</th>
                            <th>วันที่</th>
                            <th>น้ำมัน</th>
                            <th>ราคาลิตรละ</th>
                            <th>ลิตร</th>
                            <th>จำนวนเงิน</th>
                            <th>ทะเบียนรถ</th>
                            <th>หมายเหตุ</th>
                        </tr>
                        <?php
                            $count=1;
                            $Total=0;
                            $TotalUse=0;
                            $sqlOil="SELECT BookNo, CodeNo, RealUsed, Date, OilPrice, RealUsed, OilID, CarCode from (".$db_name.".credit_billing left join ".$db_name.".customer_car on customer_car.CarID=credit_billing.CarID) where Status=".intval($_REQUEST["History"])." and CustID=".intval($_REQUEST["CustID"])." and Confirmed=1 order by Date ASC, BookNo ASC, CodeNo ASC;";
                            $rsOil=mysql_query($sqlOil);
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
                                    <td>&nbsp;</td>
                                </tr>');
                                $count++;
                                $TotalUse+=round($Howmuch, 2);
                                $Total+=round($Oil[2], 2);
                            }
                        print("<tr><td colspan=\"5\" style=\"text-align:right;\">&nbsp;<strong>รวม:</strong>&nbsp;</td><td>".number_format($TotalUse, 2)."</td><td>".number_format($Total, 2)."</td><td colspan=\"2\">&nbsp;</td>");
                        $CollectDate=date("d", $CustInfo[2])." ".$monthList[(date("n", $CustInfo[2])-1)]." ".date("Y", $CustInfo[2]);
                        ?>
                        </table>
                        <br>
                        <span style="border: 1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;</span> <strong>รับใบเสร็จรับเงินแล้ว</strong>
                        <br><br><br>
                        <p><strong>ผู้รับวางบิล/ใบเสร็จรับเงิน....................................................</strong></p>
                        <strong>กำหนดรับเช็คภายในวันที่ <?php print($CollectDate); ?></strong>

                        <??>
                        <br><br>
                        <div id="actionBar" class="actionBar right">
                            <input type="hidden" id="submitTo" value="payment_history.php">
                            <input type="hidden" id="delBillingID" name="delBillingID" value="0">
                            <input type="hidden" id="CustID" name="CustID" value="<?php print($_REQUEST["CustID"]); ?>">
                            <input type="hidden" id="BillingID" value="<?php print($_REQUEST["History"]); ?>">
                            <input type="hidden" id="backPage" name="backPage" value="<?php
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
                                print('customer.php?page='.$_REQUEST['page']);
                            } ?>">
                            <?php
                            if(!intval($CustInfo[4])){
                                print('<button id="deleteBillingID" type="button" class="btn btn-danger btn-rounder">ลบใบวางบิล</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                            }
                            ?>
                            <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์ใบวางบิล</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="button" class="btn btn-info btn-rounder" onclick="javascript:location.href='manage-billing.php?BillingExport=1&CustID=<?php print($_REQUEST["CustID"]); ?>&History=<?php print($_REQUEST["History"]); ?>'; return false;">พิมพ์ Excel</button>
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
include("footer.php");
?>