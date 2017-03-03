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

if(!isset($_REQUEST["TimeMonth"])){
    $_REQUEST["TimeMonth"]=date("n", time());
    $_REQUEST["TimeYear"]=date("Y", time());
}
$alertTxt='';
$DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $_REQUEST['TimeMonth'], $_REQUEST['TimeYear']));
$startDate=mktime(0, 0, 0, $_REQUEST["TimeMonth"], 1, $_REQUEST["TimeYear"]);
$endDate=mktime(23, 59, 59, $_REQUEST["TimeMonth"], $DayPerMonth, $_REQUEST["TimeYear"]);
if(isset($_REQUEST["GenerateInv"]) && $_REQUEST["GenerateInv"]){
    reInvoiceNo($_REQUEST["TimeMonth"], $_REQUEST["TimeYear"]);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}

include("header.php");
if(!isset($_REQUEST['back'])){
    $_REQUEST['back']="index";
}
?>
<section class="pageContent">
    <form action="product_invoice.php" method="post" class="form-horizontal" role="form" autocomplete="off" name="product_invoice">
        <div class="title-body">
            <h2>รายงานภาษีขาย</h2>
        </div>

        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div class="panel-heading printhidden">
                    <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                    <h5><strong>รายงานภาษีขาย ระหว่างวันที่ <?php
                        print("เดือน: <select name=\"TimeMonth\" class=\"form-control input-sm\" style=\"display:inline; width:120px;\" onchange=\"javascript:document.forms['product_invoice'].submit();\">");
                        for($i=0; $i<count($monthList); $i++){
                            print("<option value=\"".($i+1)."\"");
                            if(($i+1) == $_REQUEST["TimeMonth"]){
                                print(" selected");
                                $monthDisplay=$monthList[$i];
                            }
                            print(">".$monthList[$i]."</option>");
                        }
                        print("</select>");
                        print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
                        print("ปี: <select name=\"TimeYear\" class=\"form-control input-sm\" style=\"display:inline; width:60px;\" onchange=\"javascript:document.forms['product_invoice'].submit();\">");
                        for($i=2015; $i<=(date("Y", time())+1); $i++){
                            print("<option value=\"".$i."\"");
                            if($i == $_REQUEST["TimeYear"]){
                                print(" selected");
                                $yearDisplay=($i+543);
                            }
                            print(">".($i+543)."</option>");
                        }
                        print("</select>");
                        // print('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-xs btn-primary btn-rounder">GO</button>');
                    ?></strong></h5>
                </div>
                <div class="panel-body">
                <?php
                print($alertTxt);
                $sqlInvoiceNo="select InvoiceNo, PaidDate, sum((UnitPrice/orderitems.QTY)*VatQTY), FROM_UNIXTIME(PaidDate, '%d.%m.%Y') as ndate from ".$db_name.".orderitems inner join ".$db_name.".product_history on product_history.ServiceID=(orderitems.ID*(-1)) where PaidDate>=".intval($startDate)." and PaidDate<=".intval($endDate)." and VatQTY>0 GROUP BY ndate;";
                $rsInvoiceNo=mysql_query($sqlInvoiceNo);
                if(!mysql_num_rows($rsInvoiceNo)){
                    print("<br><p class=\"passcode_send-error\">ไม่มีรายการขายสินค้าในเดือนนี้</p><br>");
                }
                else{
                ?>
                <p class="text-center">รายงานภาษีขาย ประจำเดือน <?php print($monthDisplay." ".$yearDisplay); ?></p>
                    <table width="100%" border="1" class="coupon_history">
                        <tr>
                            <td width="5%"><strong>ลำดับที่</strong></td>
                            <td width="15%"><strong>เลขที่ใบกำกับภาษี</strong></td>
                            <td width="15%"><strong>วันที่</strong></td>
                            <td width="15%"><strong>มูลค่า</strong></td>
                            <td width="15%"><strong>ภาษี</strong></td>
                            <td width="15%"><strong>จำนวนเงิน</strong></td>
                        </tr>
                        <?php
                        $count=1;
                        $ProductValue=0;
                        $TaxVal=0;
                        $AllValue=0;
                        while($InvoiceNo=mysql_fetch_row($rsInvoiceNo)){
                            $vatVal=round(($InvoiceNo[2]*7)/100, 2);
                            $subTotalVal=round($InvoiceNo[2]-$vatVal, 2);
                            $ShowDate=date("d", $InvoiceNo[1])." ".$shortMonthList[($_REQUEST["TimeMonth"]-1)]." ".$yearDisplay;
                            print('<tr>
                                <td>'.$count.'</td>
                                <td>'.$InvoiceNo[0].'</td>
                                <td>'.$ShowDate.'</td>
                                <td style="text-align:right;">'.number_format($subTotalVal, 2).'</td>
                                <td style="text-align:right;">'.number_format($vatVal, 2).'</td>
                                <td style="text-align:right;">'.number_format($InvoiceNo[2], 2).'</td>
                                </tr>');
                            $count++;
                            $ProductValue+=round($subTotalVal, 2);
                            $TaxVal+=round($vatVal, 2);
                            $AllValue+=round($InvoiceNo[2], 2);
                        }
                        print("<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td style=\"text-align:right;\">".number_format($ProductValue, 2)."</td><td style=\"text-align:right;\">".number_format($TaxVal, 2)."</td><td style=\"text-align:right;\">".number_format($AllValue, 2)."</td></tr>");
                        print('</table><br>');
                        ?>
                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <input type="hidden" name="page" id="setPage" value="<?php if(isset($_REQUEST['page'])){ print($_REQUEST['page']); } ?>">
                        <input type="hidden" name="back" id="setBack" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <input type="hidden" name="GenerateInv" id="GenerateInv" value="0">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        <!--
                        <button type="button" class="btn btn-info btn-rounder" onclick="javascript:location.href='product_invoice.php?ExcelExport=1&startDate=<?php print($startDate); ?>&endDate=<?php print($endDate); ?>'; return false;">พิมพ์ Excel</button> -->
                        <!-- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="javascript:document.getElementById('GenerateInv').value=1; document.forms['product_invoice'].submit();" class="btn btn-primary btn-rounder">สร้างใบกำกับภาษี</button> -->
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        &nbsp;&nbsp;&nbsp;
                    </div>
                <?php
                }
                ?>
                </div>
            </div>
        </div>
    </form>
</section>

<?php
include("footer.php");
?>