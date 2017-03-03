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

include("header.php");

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
    if(!preg_match('/-14-/', $EmpAccess)){
        $specialCond=" and Special=0";
    }
}
if(!isset($_REQUEST['page']) || !$_REQUEST['page']){
    $_REQUEST['page']=1;
}

$StockOption="";
$detailOption="";
$StockArr = array();
$sqlStock="select stock.ID, StockName from ".$db_name.".stock where stock.Deleted=0 order by stock.ID ASC;";
$rsStock=mysql_query($sqlStock);
while($Stock=mysql_fetch_row($rsStock)){
    $StockOption.="<th colspan=\"4\">".$Stock[1]."</th>";
    $detailOption.="<th>เบิก</th><th>ขาย</th><th>ซื้อ</th><th>คงเหลือ</th>";
    $StockArr[]=$Stock[0];
}
if(!isset($_REQUEST["SetProfitDate"])){
    $_REQUEST["SetProfitDate"]=date('d/m/Y', time());
    $_REQUEST["EndProfitDate"]=date('d/m/Y', time());
}
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>รายงานกำไร/ขาดทุนประจำวัน</h2>
        </div>
       <br>
        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <?php
                    print('<h3 class="panel-title" style="margin: 10px 0;">รายงานกำไร/ขาดทุน วันที่ <input type="text" class="form-control Calendar inline_input" name="StartDate" id="StartDate" value="'.$_REQUEST['SetProfitDate'].'" style="width:100px;"> ถึงวันที่ <input type="text" class="form-control Calendar inline_input" name="EndDate" id="EndDate" value="'.$_REQUEST['EndProfitDate'].'" style="width:100px;"> &nbsp;&nbsp;&nbsp;<input type="button" value="GO" class="btn btn-xs btn-primary btn-rounder" onclick="javascript:document.getElementById(\'SetProfitDate\').value=document.getElementById(\'StartDate\').value; document.getElementById(\'EndProfitDate\').value=document.getElementById(\'EndDate\').value; document.forms[\'ProfitReport\'].submit();"></h3>');
                    ?>
                </div>
                <div class="panel-body">
                    <div style="width:100%; display:inline-block;">
                        <table width="100%" border="1" class="coupon_history">
                            <?php
                                print('<tr><th colspan="23" style="padding:10px;">รายงานกำไร/ขาดทุน วันที่ '.$_REQUEST["SetProfitDate"].' ถึงวันที่ '.$_REQUEST["EndProfitDate"].'</th></tr>');
                            ?>
                            <tr>
                                <th rowspan="2">รายการ</th>
                                <?php print($StockOption); ?>
                                <th colspan="4">รวมสต็อก</th>
                                <th rowspan="2">บริการ</th>
                                <th colspan="5" style="background-color:#85CC9E;">สรุปกำไร/ขาดทุน</th>
                            </tr>

                            <tr>
                                <?php print($detailOption); ?>
                                <th>เบิก</th>
                                <th>ขาย</th>
                                <th>ซื้อ</th>
                                <th>คงเหลือ</th>
                                <th style="background-color:#85CC9E;">ราคาขาย</th>
                                <th style="background-color:#85CC9E;">ราคาทุน</th>
                                <th style="background-color:#85CC9E;">กำไร</th>
                                <th style="background-color:#85CC9E;">ขาย</th>
                                <th style="background-color:#85CC9E;">ยอดกำไร</th>
                            </tr>
                            <?php
                            $AllSumValue=0;
                            $IncomeDetails="";
                            $TotalProfit=0;
                            $sqlHistory="select ProductID, Code, Name, AvgCost, SellPrice, Type from ".$db_name.".products where products.Deleted=0".$specialCond." and UseFor!='บริการล้างรถ' order by Type ASC, Code ASC, Name ASC;";
                            //echo $sqlHistory;
                            $rsHistory=mysql_query($sqlHistory);
                            if(mysql_num_rows($rsHistory)){
                                $SetDate=explode("/", $_REQUEST['SetProfitDate']);
                                $StartDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
                                $SetEndDate=explode("/", $_REQUEST['EndProfitDate']);
                                $EndDate=mktime(23, 59, 59, $SetEndDate[1], intval($SetEndDate[0]), $SetEndDate[2]);
                                while($History=mysql_fetch_row($rsHistory)){
                                    if($History[5]=='สินค้า'){
                                        print('<tr>
                                            <td style="text-align:left;">&nbsp;'.$History[2].'</td>');
                                        $Total1=0;
                                        $Total2=0;
                                        $Total3=0;
                                        $Total4=0;
                                        for($i=0; $i<count($StockArr); $i++){
                                            $sqlRoundNo1="select SUM(if(ChangeNote='ซื้อสินค้าเข้าสต็อก', QTY, 0)) as buyQTY, SUM(if(ChangeNote='รับของเข้า' or ChangeNote='เบิกของออก', QTY, 0)) as transferQTY, SUM(if(ChangeNote='ขายสินค้า', ABS(QTY), 0)) as sellQTY from ".$db_name.".product_history where ProductID=".$History[0]." and ChangeNote in ('รับของเข้า', 'ขายสินค้า', 'ซื้อสินค้าเข้าสต็อก', 'เบิกของออก') and StockID=".$StockArr[$i]." and product_history.Date>=".$StartDate." and product_history.Date<=".$EndDate.";";
                                            $rsRoundNo1=mysql_query($sqlRoundNo1);
                                            $RoundNoInfo=mysql_fetch_row($rsRoundNo1);
                                            if(intval($RoundNoInfo[0])){ $RoundNoInfo[0]=number_format(round($RoundNoInfo[0], 1), 1); }
                                            else{ $RoundNoInfo[0]=""; }

                                            if(intval($RoundNoInfo[1])){ $RoundNoInfo[1]=number_format(round($RoundNoInfo[1], 1), 1); }
                                            else{ $RoundNoInfo[1]=""; }

                                            if(intval($RoundNoInfo[2])){ $RoundNoInfo[2]=number_format(round($RoundNoInfo[2], 1), 1); }
                                            else{ $RoundNoInfo[2]=""; }

                                            $InStock[0]="";
                                            $sqlStock="SELECT QTY from ".$db_name.".product_stock where ProductID=".intval($History[0])." and product_stock.StockID=".$StockArr[$i].";";
                                            $rsStock=mysql_query($sqlStock);
                                            $InStock=mysql_fetch_row($rsStock);
                                            print(' <td class="text-right">'.$RoundNoInfo[1].'</td>
                                                    <td class="text-right">'.$RoundNoInfo[2].'</td>
                                                    <td class="text-right">'.$RoundNoInfo[0].'</td>
                                                    <td class="text-right" style="background-color:#F2F2F2;">'.number_format(round($InStock[0], 1), 1).'</td>');
                                            $Total1+=round($RoundNoInfo[1], 1);
                                            $Total2+=round($RoundNoInfo[2], 1);
                                            $Total3+=round($RoundNoInfo[0], 1);
                                            $Total4+=round($InStock[0], 1);
                                        }
                                        $ServiceTotal="&nbsp;";
                                        $Total1=number_format($Total1, 1);
                                        $Total2=number_format($Total2, 1);
                                        $Total3=number_format($Total3, 1);
                                        $Total4=number_format($Total4, 1);
                                        $TotalSell=$Total2;
                                    }
                                    else{
                                        print('<tr>
                                            <td style="text-align:left;">&nbsp;'.$History[2].'</td>');
                                        for($i=0; $i<count($StockArr); $i++){
                                            print(' <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td style="background-color:#F2F2F2;">&nbsp;</td>');
                                        }
                                        $sqlSumHistory="select sum(round((QTY*UnitPrice)*100)/100), sum(round((DiscountVal)*100)/100), sum(QTY) from (".$db_name.".car_service inner join ".$db_name.".car_service_detail on car_service.ID=car_service_detail.ServiceID) where ServiceDate>=".$StartDate." and ServiceDate<=".$EndDate." and car_service_detail.ProductID=".$History[0]." and car_service.Deleted=0 and ServiceType!=1;";
                                        $rsSumHistory=mysql_query($sqlSumHistory);
                                        $SumHistory=mysql_fetch_row($rsSumHistory);
                                        $ServiceTotal=number_format($SumHistory[2], 1);
                                        $Total1="&nbsp;";
                                        $Total2="&nbsp;";
                                        $Total3="&nbsp;";
                                        $Total4="&nbsp;";
                                        $TotalSell=$SumHistory[2];
                                    }
                                    if($Total1=='0.00'){ $Total1="&nbsp;"; }
                                    if($Total2=='0.00'){ $Total2="&nbsp;"; }
                                    if($Total3=='0.00'){ $Total3="&nbsp;"; }
                                    print(' <td class="text-right">'.$Total1.'</td>
                                                <td class="text-right">'.$Total2.'</td>
                                                <td class="text-right">'.$Total3.'</td>
                                                <td class="text-right" style="background-color:#F2F2F2;">'.$Total4.'</td>');
                                    print("<td class=\"text-right\">".$ServiceTotal."</td>");
                                    $EachProfit=round(($History[4]*0.93)-$History[3], 2);
                                    $Profit=round($TotalSell*$EachProfit, 2);
                                    $TotalProfit+=round($Profit, 2);
                                    print(" <td class=\"text-right\" style=\"background-color:#EDFFF3;\">".number_format($History[4]*0.93, 2)."</td>
                                            <td class=\"text-right\" style=\"background-color:#EDFFF3;\">".number_format($History[3], 2)."</td>
                                            <td class=\"text-right\" style=\"background-color:#EDFFF3;\">".number_format($EachProfit, 2)."</td>
                                            <td class=\"text-right\" style=\"background-color:#EDFFF3;\">".number_format($TotalSell, 1)."</td>
                                            <td class=\"text-right\" style=\"background-color:#EDFFF3;\">".number_format($Profit, 2)."</td>");
                                    print('<tr>');
                                }
                                print('<tr><th colspan="22" style="text-align:right; padding:5px;">ยอดรวมกำไร:</th><th style="text-align:right;">'.number_format($TotalProfit, 2).'</th></tr>');
                                print('</table><br>');
                            }
                            else{
                                print('<tr><td colspan="23" style="padding:15px;"><span style="color:red;">ไม่มีการสินค้าในระบบ</span></td></tr></table>');
                            }
                            ?>
                    </div>

                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <form name="ProfitReport" action="#" method="post">
                            <input type="hidden" name="SetProfitDate" id="SetProfitDate" value="<?php print($_REQUEST['SetProfitDate']); ?>">
                            <input type="hidden" name="EndProfitDate" id="EndProfitDate" value="<?php print($_REQUEST['EndProfitDate']); ?>">
                            <input type="hidden" name="back" id="setBack" value="<?php print($_REQUEST['back']); ?>">
                            <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                            <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>

<?php
include("footer.php");
?>