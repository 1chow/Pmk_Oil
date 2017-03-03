<?php
include("dbvars.inc.php");

if($SectionNum==1 && $PermissionNo==1 && $onlyAccess==4){ // เข้าใช้ได้แต่ส่วนคูปอง (normal user)
    header('location: coupon_check.php?SF=1');
    exit();
}
if($SectionNum==1){
    $AccessArr = array(1 => 'invoice', 2 => 'car_service', 3 => 'stock', 4 => 'coupons', 5 => 'oil', 6 => 'index', 7 => 'index', 8 => 'employees', 9 => 'service-customer', 10 => 'customer', 11 => 'reports', 12 => 'system', 13 => 'index', 14 => 'special-stock');
    if($onlyAccess!=6 && $onlyAccess!=7){
        header("location: ".$AccessArr[$onlyAccess].".php?SF=1");
        exit();
    }
}
$_REQUEST['SF']=1;

// check for customer credit
$sqlBillingChk="SELECT CustID, DayBeforePay, CreditTerm, SpecialTerm from ".$db_name.".customer where Deleted=0 and CustID>0 and FromService=0 and CreditTerm in (1,2);";
$rsBillingChk=mysql_query($sqlBillingChk);
while($BillingChhk=mysql_fetch_row($rsBillingChk)){
    $DateCheckNo=date('j', time());
    $DayCheckNo=date('N', time());
    $CanBilling=0;
    if($BillingChhk[2]==1){ // โดยวันที่
        $DateArr=explode(",", trim($BillingChhk[3]));
        if(in_array($DateCheckNo, $DateArr)){
            $CanBilling=1;
        }
    }
    else if($BillingChhk[2]==2 && preg_match("/".$DayCheckNo."/", $BillingChhk[3])){ // โดยวันในสัปดาห์
        $CanBilling=1;
    }
    if($CanBilling){
        $BillingDate=time();
        $CollectDate=time();
        if($BillingChhk[1]){
            $CollectDate=strtotime(date('Y-m-d', $BillingDate).' +'.$BillingChhk[1].' day');
        }
        $sqlUse="SELECT sum(RealUsed) from ".$db_name.".credit_billing where Status=0 and CustID=".$BillingChhk[0]." and Confirmed=1;";
        $rsUse=mysql_query($sqlUse);
        $UseTotal=mysql_fetch_row($rsUse);

        if($UseTotal[0]){
            $sqlInsert="INSERT INTO ".$db_name.".billing_history (BillingDate, CollectSchedule, PaidDate, Total, CustID) VALUES (".$BillingDate.", ".$CollectDate.", 0, '".floatval($UseTotal[0])."', ".$BillingChhk[0].");";
            $rsInsert=mysql_query($sqlInsert);
            $HistoryID=mysql_insert_id($Conn);

            $sqlUpdate="UPDATE ".$db_name.".credit_billing SET Status=".$HistoryID." WHERE Status=0 and CustID=".$BillingChhk[0]." and Confirmed=1;";
            $rsUpdate=mysql_query($sqlUpdate);
        }
    }
}
include("header.php");
?>
        <section class="pageContent homepage">
        <div class="title-body">
            <h2>หน้าหลัก</h2>
        </div>
       <br>
       <div class="row" style="padding: 0px 20px;">
            <?php
            if(preg_match('/-1-/', $EmpAccess)){
            ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-print"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="invoice.php?AddNew=1&back=index">ออกใบกำกับภาษี</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            if(preg_match('/-4-/', $EmpAccess)){
            ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-check-square"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="coupon_check.php">เช็คคูปอง</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            if(preg_match('/-6-/', $EmpAccess)){
            ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-edit"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="record_info.php">บันทึกรายการขายน้ำมัน</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            if(preg_match('/-7-/', $EmpAccess)){
            ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-pencil-square"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="accounting.php">รายรับ / รายจ่ายประจำวัน</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            ?>

            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-tags"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="sellproducts.php">ขายสินค้า</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-tags"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="product_invoice.php">รายงานภาษีขาย</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if($UserID==1 || (preg_match('/-12-/', $EmpAccess) && $PermissionNo>=2)){
            ?>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="media stats-box">
                                <a href="#" class="pull-left">
                                    <i class="fa fa-money"></i>
                                </a>
                                <div class="media-body">
                                    <span class="stats-number"><a href="accounting_balance.php">บันทึกรายการเคลื่อนไหวทางบัญชี</a></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            <div style="clear: both;"></div>


            <?php
            $sqlBilling="SELECT count(DISTINCT CustID), sum(if(FROM_UNIXTIME(BillingDate, '%m-%d-%Y')='".date("m-d-Y", time())."', 1, 0)) as BillingNow, sum(if(FROM_UNIXTIME(BillingDate, '%m-%d-%Y')='".date("m-d-Y", time())."', Total, 0)) as BillingTotal, sum(if(FROM_UNIXTIME(CollectSchedule, '%m-%d-%Y')='".date("m-d-Y", time())."', 1, 0)) as CollectNow, sum(if(FROM_UNIXTIME(CollectSchedule, '%m-%d-%Y')='".date("m-d-Y", time())."', Total, 0)) as CollectTotal, sum(Total) from ".$db_name.".billing_history where PaidDate=0;";
            $rsBilling=mysql_query($sqlBilling);
            $BillingWaiting=mysql_fetch_row($rsBilling);
            if($BillingWaiting[1]+$BillingWaiting[3]){
                print('<div class="col-md-6"><br><br>');
                //print('<p><a class="blue" href="manage-billing.php?from=index">บริษัทที่มีกำหนดการวางบิล: '.$BillingWaiting[0].' บริษัท, ยอดเงินรวม '.number_format($BillingWaiting[5], 2).' บาท</a></p>');
                if(intval($BillingWaiting[1])){
                    print('<p><a class="orange" href="manage-billing.php?from=index&actionFor=billing">ลูกค้าเครดิตที่ต้องวางบิลวันนี้: '.$BillingWaiting[1].' ราย, ยอดเงินรวม '.number_format($BillingWaiting[2], 2).' บาท</a></p>');
                }
                if($BillingWaiting[3]){
                    print('<p><a class="red" href="manage-billing.php?from=index&actionFor=collect">ลูกค้าเครดิตที่ถึงกำหนดนัดชำระเงินแล้ว: '.$BillingWaiting[3].' ราย, ยอดเงินรวม '.number_format($BillingWaiting[4], 2).' บาท</a></p>');
                }
                print('</div>');
            }
            $sqlBilling="SELECT sum(if(UnofficialBalance<=SpecialTerm, 1, 0)), CustName, UnofficialBalance from ".$db_name.".customer where FromService=3 and Deleted=0;";
            $rsBilling=mysql_query($sqlBilling);
            $BillingWaiting=mysql_fetch_row($rsBilling);
            $warningList="";
            if($BillingWaiting[0]){
                $warningList.="\r\n\t\t\t\t<div style=\"float:left; width:50%;\">".$BillingWaiting[1]."</div><div style=\"float:left; width:50%;\">".$BillingWaiting[2]." บาท</div>";
                print('<div class="col-md-6"><br><br>');
                print('<p><a class="blue" href="javascript:void(0);" data-toggle="modal" data-target="#cashwarning">ลูกค้าเครดิตเงินสดที่ควรเติมวงเงิน: '.$BillingWaiting[0].' บริษัท</a></p>');
                print('</div>');
            }
            ?>
        </div>
    </div>

    </section>

    <div class="modal fade" id="cashwarning" tabindex="-1" role="dialog" aria-labelledby="mycashwarning" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
            <?php
                if($BillingWaiting[0]){
                    print("<div style=\"float:left; width:50%;\"><strong>ชื่อบริษัท/ลูกค้า</strong></div><div style=\"float:left; width:50%;\"><strong>วงเงินคงเหลือ</strong></div>");
                    print($warningList);
                } ?><br style="clear:both;">
                <br>
                <div style="text-align: right;">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="javascript:setPriceWarning();">ปิดหน้าต่าง</button>
                </div>
            </div>
        </div>
      </div>
    </div>

<?php
include("footer.php");
?>
