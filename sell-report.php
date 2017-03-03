<?php
include("dbvars.inc.php");
if(!preg_match('/-3-/', $EmpAccess) && !preg_match('/-14-/', $EmpAccess) && !preg_match('/-11-/', $EmpAccess) && $UserID!=1){
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
if(isset($_REQUEST["deleteSellInfo"]) && intval($_REQUEST["deleteSellInfo"])){
    $SellID=$_POST["deleteSellInfo"];
    // select old information
    $sqlOldVal="select NoVatQTY, VatQTY, ProductID, QTY, StockID, product_history.Date from ".$db_name.".product_history where ServiceID=".intval($SellID)*(-1).";";
    $rsOldVal=mysql_query($sqlOldVal);
    $OldVal=mysql_fetch_row($rsOldVal);

    // update old product stock
    $sqlStock="UPDATE ".$db_name.".product_stock set QTY=QTY+(".abs($OldVal[3]).") where ProductID=".intval($OldVal[2])." and StockID=".$OldVal[4].";";
    $rsStock=mysql_query($sqlStock);

    // update old product NoVat QTY
    $updateVatQTY="UPDATE products set NoVatQTY=NoVatQTY+".floatval($OldVal[0])." where ProductID=".intval($OldVal[2]).";";
    $rsVatQTY=mysql_query($updateVatQTY);

    $sqlDelete="delete from ".$db_name.".orderitems where ID=".intval($SellID).";";
    $rsDelete=mysql_query($sqlDelete);

    $sqlDelete="delete from ".$db_name.".account_daily where ForActionID=".intval($SellID).";";
    $rsDelete=mysql_query($sqlDelete);

    $sqlDelete="delete from ".$db_name.".product_history where ServiceID=".intval($SellID)*(-1).";";
    $rsDelete=mysql_query($sqlDelete);
    if(intval($OldVal[5])){
        reInvoiceNo(date("n", $OldVal[5]), date("Y", $OldVal[5]));
    }
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>ลบข้อมูลเรียบร้อยแล้ว</div>';
}
include("header.php");

if(!isset($_REQUEST['StartDate'])){
    $_REQUEST['StartDate']=date("01/m/Y", time());
    $_REQUEST['EndDate']=date("d/m/Y", time());
}

$SetDate=explode("/", $_REQUEST['StartDate']);
$startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
$SetDateTo=explode("/", $_REQUEST['EndDate']);
$endDate=mktime(23, 59, 59, $SetDateTo[1], $SetDateTo[0], $SetDateTo[2]);
if(!isset($_REQUEST['back'])){ $_REQUEST['back']=''; }
?>
	<section class="pageContent">
        <div class="title-body">
            <h2>รายงานการขายสินค้า</h2>
        </div>

        <div class="content-center">

            <div class="tab-content">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <form action="sell-report.php" method="post" class="form-horizontal" role="form" name="sellReport">
                            <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                            <h5><strong>รายงานการขายสินค้า ระหว่างวันที่ <input type="text" class="form-control Calendar inline_input" name="StartDate" value="<?php print($_REQUEST['StartDate']); ?>" style="width:100px;"> ถึงวันที่ <input type="text" class="form-control Calendar inline_input" name="EndDate" value="<?php print($_REQUEST['EndDate']); ?>" style="width:100px;"></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="javascript:document.forms['sellReport'].submit();" class="btn btn-xs btn-primary btn-rounder">GO</button></h5>
                            <input type="hidden" name="deleteSellInfo" id="deleteSellInfo" value="0">
                        </form>
                    </div>
                    <div class="panel-body" id="pageContent">
                        <?php print($alertTxt); ?>
                        <table width="100%" border="1" class="coupon_history">
                            <thead>
                            	<tr>
                            		<th style="padding:10px;" colspan="10">รายงานการขายสินค้า วันที่ <?php print($_REQUEST['StartDate']); ?> ถึงวันที่ <?php print($_REQUEST['EndDate']); ?></th>
                            	</tr>
                                <tr>
                                    <th width="70">ลำดับที่</th>
                                    <th width="150">วันที่</th>
                                    <th>สินค้า</th>
                                    <th>สต็อก</th>
                                    <th width="100">ราคาต่อหน่วย</th>
                                    <th width="100">จำนวน</th>
                                    <th width="100">ราคารวม</th>
                                    <th width="200">หมายเหตุ</th>
                                    <th width="80" class="printhidden" colspan="2">&nbsp;</th>
                                </tr>
                            </thead>
                            <?php
                            $count=1;
                            $sqlSellInfo="SELECT products.Name, QTY, UnitPrice, PaidBy, PaidDate, orderitems.Note, SellBy, StockName, product_unit.Name, orderitems.ID from ((".$db_name.".orderitems inner join ".$db_name.".products on orderitems.ProductID=products.ProductID) inner join ".$db_name.".product_unit on product_unit.ID=products.UnitNameID) inner join ".$db_name.".stock on stock.ID=orderitems.FromStock where PaidDate>=".intval($startDate)." and PaidDate<=".intval($endDate)." and ServiceID=0 order by PaidDate ASC, products.Name ASC;";
                            $rsSellInfo=mysql_query($sqlSellInfo);
                            if(mysql_num_rows($rsSellInfo)){
                                while($SellInfo=mysql_fetch_row($rsSellInfo)){
                                    $sqlOldVal="select ServiceID from ".$db_name.".product_history where ServiceID=".intval($SellInfo[9])*(-1).";";
                                    $rsOldVal=mysql_query($sqlOldVal);
                                    $OldVal=mysql_fetch_row($rsOldVal);
                                    $EditLink='';
                                    $DeleteLink='<a href="javascript:void(0);" onclick="javascript:deleteSellInfo('.$SellInfo[9].');" style="color:red;">ลบ</a>';

                                    if($PermissionNo>=2 && $OldVal[0]){
                                        $EditLink='<a href="javascript:gotopage(\'sellproducts.php?EditSell='.$SellInfo[9];
                                        if(isset($_REQUEST['back']) && $_REQUEST['back']){
                                            $EditLink.='&back2Page='.$_REQUEST['back'];
                                        }
                                        $EditLink.='&back=sell-report\');">แก้ไข</a>';
                                        $DeleteLink='<a href="javascript:void(0);" onclick="javascript:deleteSellInfo('.$SellInfo[9].');" style="color:red;">ลบ</a>';
                                    }
                                    print('
                                    <tr>
                                        <td>'.$count.'</td>
                                        <td>'.date("d/m/Y H:i", $SellInfo[4]).'</td>
                                        <td class="text-left">&nbsp;'.$SellInfo[0].'</td>
                                        <td class="text-left">&nbsp;'.$SellInfo[7].'</td>
                                        <td class="text-right">'.number_format($SellInfo[2], 2).'&nbsp;</td>
                                        <td class="text-right">'.number_format($SellInfo[1], 2).'&nbsp;</td>
                                        <td class="text-right">'.number_format($SellInfo[1]*$SellInfo[2], 2).'&nbsp;</td>
                                        <td>'.$SellInfo[5].'</td>
                                        <td class="printhidden">'.$EditLink.'</td>
                                        <td class="printhidden">'.$DeleteLink.'</td>
                                    </tr>');
                                    $count++;
                                }
                            }
                            else{
                                print('<tr><td colspan="10" class="passcode_send-error"><p style="margin:15px auto 15px;">ไม่มีรายการขายในช่วงเวลาที่กำหนด</p></td></tr>');
                            }
                            ?>
                        </table>
                        <br>
                        <div id="actionBar" class="actionBar right">
	                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                            <?php
                            if(isset($_REQUEST['back']) && $_REQUEST['back']){
                                print('<input type="hidden" id="backPage" value="'.$_REQUEST['back'].'.php">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>');
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

<?php
include("footer.php");
?>