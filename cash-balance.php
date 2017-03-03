<?php
include("dbvars.inc.php");
include("header.php");
if(!isset($_POST["TimeMonth"])){
    $_POST["TimeMonth"]=date("n", time());
    $_POST["TimeYear"]=date("Y", time());
}


$DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $_REQUEST['TimeMonth'], $_REQUEST['TimeYear']));
$startDate=mktime(0, 0, 0, $_POST["TimeMonth"], 1, $_POST["TimeYear"]);
$endDate=mktime(23, 59, 59, $_POST["TimeMonth"], $DayPerMonth, $_POST["TimeYear"]);
$sqlMinTime="";
$rsMinTime=mysql_query($sqlMinTime);
$MinTime=mysql_fetch_row($rsMinTime);
$sqlCustName="select CustName from ".$db_name.".customer where CustID=".intval($_REQUEST["CustID"]).";";
$rsCustName=mysql_query($sqlCustName);
$CustName=mysql_fetch_row($rsCustName);
?>
	<section class="pageContent">
        <div class="title-body">
            <h2>รายงานสรุปสำหรับลูกค้าเครดิตเงินสด</h2>
        </div>

        <div class="content-center">

            <div class="tab-content">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5><strong>รายงานสรุป <?php
                        print("เดือน: <select id=\"Cash4Month\" name=\"TimeMonth\" class=\"form-control input-sm\" style=\"display:inline; width:90px;\"><option value=\"\">เดือน</option>");
                        $monthName="";
                        for($i=0; $i<count($monthList); $i++){
                            print("<option value=\"".($i+1)."\"");
                            if(($i+1) == $_POST["TimeMonth"]){
                                print(" selected");
                                $monthName=$monthList[$i];
                            }
                            print(">".$monthList[$i]."</option>");
                        }
                        print("</select>&nbsp;&nbsp;");
                        print("ปี: <select id=\"Cash4Year\" name=\"TimeYear\" class=\"form-control input-sm\" style=\"display:inline; width:60px;\">");
                        for($i=2015; $i<=(date("Y", time())+1); $i++){
                            print("<option value=\"".$i."\"");
                            if($i == $_POST["TimeYear"]){
                                print(" selected");
                            }
                            print(">".($i+543)."</option>");
                        }
                        print("</select> ของ ".$CustName[0]);
                        ?></strong></h5>
                    </div>
                    <div class="panel-body" id="pageContent">
                        <form id="submitForm" action="cash-balance.php" method="post" class="form-horizontal" role="form">
                            <table width="100%" border="1" class="coupon_history">
                                <thead>
                                	<tr>
                                		<th style="padding:10px;" colspan="8">รายงานสรุป เดือน <?php print($monthName." ปี ".$_POST["TimeYear"]." ของ ".$CustName[0]); ?></th>
                                	</tr>
                                    <tr>
                                        <th width="70">ลำดับที่</th>
                                        <th>วันที่</th>
                                        <th>รายการ</th>
                                        <th>เพิ่มเงิน</th>
                                        <th>ใช้เงิน</th>
                                        <th>คงเหลือ</th>
                                    </tr>
                                </thead>
                                <?php
                                $count=1;
                                $sqlSellInfo="SELECT products.Name, QTY, UnitPrice, PaidBy, PaidDate, orderitems.Note, SellBy, StockName, product_unit.Name from ((".$db_name.".orderitems inner join ".$db_name.".products on orderitems.ProductID=products.ProductID) inner join ".$db_name.".product_unit on product_unit.ID=products.UnitNameID) inner join ".$db_name.".stock on stock.ID=orderitems.FromStock where PaidDate>=".intval($startDate)." and PaidDate<=".intval($endDate)." and CustID=".intval($_REQUEST["CustID"])." order by PaidDate ASC, products.Name ASC;";
                                $rsSellInfo=mysql_query($sqlSellInfo);
                                if(mysql_num_rows($rsSellInfo)){
                                    while($SellInfo=mysql_fetch_row($rsSellInfo)){
                                        print('
                                        <tr>
                                            <td>'.$count.'</td>
                                            <td>'.date("d/m/Y H:i", $SellInfo[4]).'</td>
                                            <td>'.$SellInfo[0].'</td>
                                            <td>'.$SellInfo[7].'</td>
                                            <td>'.number_format($SellInfo[2], 2).'</td>
                                            <td>'.number_format($SellInfo[1], 2).'</td>
                                            <td>'.number_format($SellInfo[1]*$SellInfo[2], 2).'</td>
                                            <td>'.$SellInfo[5].'</td>
                                        </tr>');
                                        $count++;
                                    }
                                }
                                else{
                                    print('<tr><td colspan="8" class="passcode_send-error">ไม่มีรายการขาย</td></tr>');
                                }
                                ?>
                            </table>
                            <br>
                            <div id="actionBar" class="actionBar right">
		                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
		                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="hidden" id="backPage" value="index.php">
                                <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </section>

<?php
include("footer.php");
?>