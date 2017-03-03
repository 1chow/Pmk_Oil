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
    if(!preg_match('/-14-/', $EmpAccess)){
        $specialCond=" and Special=0";
    }
}

$SetDate=explode("/", $_REQUEST['serviceDate']);
$startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
$endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
if($_REQUEST['serviceDateTo']!=$_REQUEST['serviceDate']){
    $SetDateTo=explode("/", $_REQUEST['serviceDateTo']);
    $endDate=mktime(23, 59, 59, $SetDateTo[1], $SetDateTo[0], $SetDateTo[2]);
}
$ItemPerPage=150;
if(!isset($_REQUEST['page']) || !$_REQUEST['page']){
    $_REQUEST['page']=1;
}
if(!isset($_REQUEST["reportType"])){
    $_REQUEST["reportType"]=1;
}
if($_REQUEST["reportType"]==1){
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>รายงานการเคลื่อนไหว</h2>
        </div>
       <br>
        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <form action="stock-report.php" method="post" class="form-horizontal" role="form" name="stock_movement">
                        <input type="hidden" name="page" id="setPage" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="report" value="1">
                        <h3 class="panel-title" style="margin: 10px 0;">
                        <?php
                            print("รายงานการเคลื่อนไหวประจำวันที่:");
                            print("&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="serviceDate" value="'.$_REQUEST['serviceDate'].'" style="display:inline; width:100px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print("&nbsp;ถึง&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="serviceDateTo" value="'.$_REQUEST['serviceDateTo'].'" style="display:inline; width:100px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print("&nbsp;&nbsp;&nbsp;");
                            print('ประเภท <select name="serviceType" class="form-control" style="display:inline; width:170px;" onchange="javascript:document.getElementById(\'setPage\').value=1;"><option value="">ทุกประเภท</option>');
                            print('<option value="1"');
                            if(intval($_REQUEST['serviceType'])==1){
                                print(' selected');
                            }
                            print('>ย้ายสินค้าจากสต็อก</option>');
                            print('<option value="2"');
                            if(intval($_REQUEST['serviceType'])==2){
                                print(' selected');
                            }
                            print('>ซื้อสินค้าเข้าสต็อก</option>');
                            print('<option value="3"');
                            if(intval($_REQUEST['serviceType'])==3){
                                print(' selected');
                            }
                            print('>ใช้สำหรับบริการล้างรถ/เปลี่ยนน้ำมันเครื่อง</option>');
                            print('</select>');
                            print("&nbsp;&nbsp;&nbsp;");
                            print('<button type="submit" class="btn btn-xs btn-primary btn-rounder">GO</button>');
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">

                    <table width="100%" border="1" class="coupon_history">
                        <tr><th colspan="8"><p style="margin:10px;">
                            <?php
                            print('รายงานการเคลื่อนไหวประจำวันที่: '.$_REQUEST['serviceDate']);
                            if($_REQUEST['serviceDate']!=$_REQUEST['serviceDateTo']){
                                print(' - '.$_REQUEST['serviceDateTo']);
                            }
                            ?>
                        </p></th></tr>
                        <tr>
                            <th width="12%">วันที่</th>
                            <th width="10%">รหัสสินค้า</th>
                            <th width="20%">ชื่อสินค้า</th>
                            <th width="10%">จำนวน</th>
                            <th>การเปลี่ยนแปลง</th>
                            <th width="12%">สต็อก</th>
                            <th width="9%">เปลี่ยนแปลงโดย</th>
                            <th width="9%">บันทึกโดย</th>
                        </tr>
                        <?php
                        $sqlHistory="select Code, Name, Date, QTY, ChangeNote, UserID, StockName, RequestBy from ((".$db_name.".product_history inner join ".$db_name.".products on products.ProductID=product_history.ProductID) inner join ".$db_name.".stock on stock.ID=product_history.StockID) where product_history.Date>=".$startDate." and product_history.Date<=".$endDate.$specialCond;
                        if(intval($_REQUEST['serviceType'])){
                            if(intval($_REQUEST['serviceType'])==2){
                                $sqlHistory.=" and ChangeNote like '%ซื้อ%'";
                            }
                            else if(intval($_REQUEST['serviceType'])==3){
                                $sqlHistory.=" and ChangeNote like '%ใบรับบริการ%'";
                            }
                            else{
                                $sqlHistory.=" and ((ChangeNote like '%รับของ%') or (ChangeNote like '%เบิกของ%'))";
                            }
                        }
                        $rsHistory=mysql_query($sqlHistory.";");
                        $HistoryNum=mysql_num_rows($rsHistory);
                        $AllPage=ceil($HistoryNum/$ItemPerPage);

                        $sqlHistory.=" order by Date ASC, Code ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                        //echo $_REQUEST['serviceType'].'==='.$sqlHistory;
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                if(intval($History[5])){
                                    $sqlUserName = "select concat(FirstName, ' ', LastName) from ".$db_name.".employee where employee.EmpID=".intval($History[5]).";";
                                    $rsUserName = mysql_query($sqlUserName);
                                    $UserName=mysql_fetch_row($rsUserName);
                                }else{
                                    $UserName[0]='ระบบ';
                                }
                                if(intval($History[5])){
                                    $sqlRequestBy = "select concat(FirstName, ' ', LastName) from ".$db_name.".employee where employee.EmpID=".intval($History[7]).";";
                                    $rsRequestBy = mysql_query($sqlRequestBy);
                                    $RequestBy=mysql_fetch_row($rsRequestBy);
                                }else{
                                    $RequestBy[0]='ระบบ';
                                }
                                print('<tr>
                                    <td class="text-left">'.date('d/m/Y H:i', $History[2]).'</td>
                                    <td>'.$History[0].'</td>
                                    <td class="text-left">'.$History[1].'</td>
                                    <td class="text-right">'.number_format($History[3], 2).'</td>
                                    <td class="text-left">'.$History[4].'</td>
                                    <td class="text-left">'.$History[6].'</td>
                                    <td class="text-left">'.$RequestBy[0].'</td>
                                    <td class="text-left">'.$UserName[0].'</td>
                                    </tr>');
                            }
                            print('</table><br>');
                        }
                        else{
                            print('<tr><td colspan="9" style="padding:15px;"><span style="color:red;">ไม่มีการเคลื่อนไหวในวันที่กำหนด</span></td></tr></table>');
                        }
                        if($HistoryNum > $ItemPerPage){
                            // prev page
                            if($_REQUEST['page']!=1){
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']-1).'; document.forms[\'stock_movement\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                            }
                            print("<select onchange=\"javascript:document.getElementById('setPage').value=this.value; document.forms['stock_movement'].submit();\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
                            // all order page
                            for($i=1; $i<=$AllPage; $i++){
                                print('<option value="'.$i.'"');
                                if($_REQUEST['page']==$i){
                                    print(' selected');
                                }
                                print('>หน้า '.$i.'</option>');
                            }
                            print("</select>&nbsp;&nbsp;&nbsp;&nbsp;");
                            // next page
                            if($_REQUEST['page']!=$AllPage){
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']+1).'; document.forms[\'stock_movement\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                            }
                        }
                        ?>
                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        &nbsp;&nbsp;&nbsp;
                    </div>

                </div>
            </div>
        </div>
    </section>
<?php
}
else if($_REQUEST["reportType"]==2){
    if(!isset($_REQUEST["importVAT"])){
        $_REQUEST["importVAT"]=0;
        $_REQUEST['importProduct']=0;
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
            <h2>รายงานรายการซื้อสินค้า</h2>
        </div>
       <br>
        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <form action="stock-report.php" method="post" class="form-horizontal" role="form" id="ImportForm" name="stock_movement">
                        <input type="hidden" name="page" id="setPage" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="reportType" value="2">
                        <h3 class="panel-title" style="margin:0;">
                        <?php
                            print("<div class=\"form-group\" style=\"margin:0; text-align:center;\">");
                            print("สินค้า: <select id=\"importProduct\" name=\"importProduct\" class=\"form-control input-sm\" style=\"display:inline; width:130px;\"><option value=\"0\"");
                            if(!$_REQUEST['importProduct']){
                                print(" selected");
                            }
                            print(">ทั้งหมด</option>");
                            $productSelectes="";
                            $sqlAllProduct="select ProductID, Name from products where Deleted=0 and Type='สินค้า'".$specialCond." order by Name ASC;";
                            $rsAllProduct=mysql_query($sqlAllProduct);
                            while($allProduct=mysql_fetch_row($rsAllProduct)){
                                print("<option value=\"".$allProduct[0]."\"");
                                if($allProduct[0] == $_REQUEST['importProduct']){
                                    print(" selected");
                                    $productSelectes="<br>".$allProduct[1];
                                }
                                print(">".$allProduct[1]."</option>");
                            }
                            print("</select>");
                            print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
                            print("VAT: <select id=\"importVAT\" name=\"importVAT\" class=\"form-control input-sm\" style=\"display:inline; width:90px;\"><option value=\"0\"");
                            if($_REQUEST["importVAT"]==0){
                                print(" selected");
                            }
                            print(">ทังหมด</option><option value=\"1\"");
                            if($_REQUEST["importVAT"]==1){
                                print(" selected");
                            }
                            print(">มี</option><option value=\"2\"");
                            if($_REQUEST["importVAT"]==2){
                                print(" selected");
                            }
                            print(">ไม่มี</option></select>");
                            print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
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
                            print("</select></div>");
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">

                    <table width="100%" border="1" class="coupon_history">
                        <tr><th colspan="11"><p style="margin:10px;">
                            <?php
                            print('รายงานรายการซื้อสินค้าเดือน: '.$monthList[$_REQUEST['importMonth']-1]." ปี: ".$_REQUEST['importYear'].$productSelectes);
                            ?>
                        </p></th></tr>
                        <tr>
                            <th width="8%">วันที่</th>
                            <th>ชื่อร้านค้า</th>
                            <th width="10%">รหัสสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <th width="9%">หมายเหตุ</th>
                            <th width="9%">ซื้อโดย</th>
                            <th width="5%">VAT</th>
                            <th width="6%">จำนวน</th>
                            <th width="9%">ราคาต่อหน่วย</th>
                            <th width="5%">จำนวนภาษี</th>
                            <th width="9%">ราคารวม</th>
                        </tr>
                        <?php
                        $Total1=0;
                        $Total2=0;
                        $Total3=0;
                        $Total4=0;
                        $vatCond="";
                        if($_REQUEST["importVAT"]==1){
                            $vatCond=" and CalVat=1";
                        }
                        else if($_REQUEST["importVAT"]==2){
                            $vatCond=" and CalVat=0";
                        }
                        $sqlHistory="select Code, Name, Date, QTY, ChangeNote, TotalPrice, SupName, RequestBy, SupName, product_import.Note, CalVat from ((".$db_name.".product_history inner join ".$db_name.".product_import on product_import.HistoryID=product_history.ID) inner join ".$db_name.".products on products.ProductID=product_history.ProductID) where product_history.Date>=".$startDate." and product_history.Date<=".$endDate." and ChangeNote like '%ซื้อ%'".$vatCond.$specialCond;
                        if($_REQUEST['importProduct']){
                            $sqlHistory.=" and products.ProductID=".intval($_REQUEST['importProduct']);
                        }
                        $rsHistory=mysql_query($sqlHistory.";");
                        $HistoryNum=mysql_num_rows($rsHistory);
                        $AllPage=ceil($HistoryNum/$ItemPerPage);

                        $sqlHistory.=" order by Date ASC, Code ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                        //echo $sqlHistory;
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                if(intval($History[5])){
                                    $sqlRequestBy = "select concat(FirstName, ' ', LastName) from ".$db_name.".employee where employee.EmpID=".intval($History[7]).";";
                                    $rsRequestBy = mysql_query($sqlRequestBy);
                                    $RequestBy=mysql_fetch_row($rsRequestBy);
                                }else{
                                    $RequestBy[0]='ระบบ';
                                }
                                $vatVal=0;
                                $subTotalVal=$History[5];
                                $ShowVAT="ไม่มี";
                                if($History[10]){
                                    $ShowVAT="มี";
                                    $VatVal="";
                                    $vatVal=round(($History[5]*7)/100, 2);
                                    $subTotalVal=round($History[5]-$vatVal);
                                }
                                print('<tr>
                                    <td>'.date('d/m/Y', $History[2]).'</td>
                                    <td class="text-left">'.$History[6].'</td>
                                    <td>'.$History[0].'</td>
                                    <td class="text-left">'.$History[1].'</td>
                                    <td class="text-left">'.$History[9].'</td>
                                    <td class="text-left">'.$RequestBy[0].'</td>
                                    <td>'.$ShowVAT.'</td>
                                    <td>'.number_format($History[3]).'</td>
                                    <td style="text-align:right;">'.number_format($subTotalVal, 2).'&nbsp;&nbsp;</td>
                                    <td style="text-align:right;">'.number_format($vatVal, 2).'&nbsp;&nbsp;</td>
                                    <td style="text-align:right;">'.number_format($History[5], 2).'&nbsp;&nbsp;</td>
                                    </tr>');
                                $Total1+=$History[3];
                                $Total2+=$subTotalVal;
                                $Total3+=$vatVal;
                                $Total4+=$History[5];
                            }
                            print('<tr><th colspan="7" style="text-align:right;"><strong>รวม:</strong></th>
                                    <th>'.number_format($Total1).'</th>
                                    <th style="text-align:right;">'.number_format($Total2, 2).'&nbsp;&nbsp;</th>
                                    <th style="text-align:right;">'.number_format($Total3, 2).'&nbsp;&nbsp;</th>
                                    <th style="text-align:right;">'.number_format($Total4, 2).'&nbsp;&nbsp;</th>
                                   </tr>');
                            print('</table><br>');
                        }
                        else{
                            print('<tr><td colspan="11" style="padding:15px;"><span style="color:red;">ไม่มีการซื้อสินค้าในเดือนที่กำหนด</span></td></tr></table>');
                        }
                        if($HistoryNum > $ItemPerPage){
                            print("<br>");
                            if($_REQUEST['page']!=1){
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']-1).'; document.forms[\'stock_movement\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                            }
                            print("<select onchange=\"javascript:document.getElementById('setPage').value=this.value; document.forms['stock_movement'].submit();\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
                            // all order page
                            for($i=1; $i<=$AllPage; $i++){
                                print('<option value="'.$i.'"');
                                if($_REQUEST['page']==$i){
                                    print(' selected');
                                }
                                print('>หน้า '.$i.'</option>');
                            }
                            print("</select>&nbsp;&nbsp;&nbsp;&nbsp;");
                            // next page
                            if($_REQUEST['page']!=$AllPage){
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']+1).'; document.forms[\'stock_movement\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                            }
                        }
                        ?>
                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <input type="hidden" name="back" id="setBack" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        &nbsp;&nbsp;&nbsp;
                    </div>

                </div>
            </div>
        </div>
    </section>
<?php
}
else if($_REQUEST["reportType"]==4){
    if(!isset($_REQUEST["importVAT"])){
        $_REQUEST["importVAT"]=1;
        $_REQUEST['importProduct']=0;
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
            <h2>รายงานการใช้สินค้า</h2>
        </div>
       <br>
        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <form action="stock-report.php" method="post" class="form-horizontal" role="form" id="ImportForm" name="stock_movement">
                        <input type="hidden" name="page" id="setPage" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="reportType" value="4">
                        <h3 class="panel-title" style="margin:0;">
                        <?php
                            print("<div class=\"form-group\" style=\"margin:0; text-align:center;\">");
                            print("สินค้า: <select id=\"importProduct\" name=\"importProduct\" class=\"form-control input-sm\" style=\"display:inline; width:130px;\"><option value=\"0\"");
                            if(!$_REQUEST['importProduct']){
                                print(" selected");
                            }
                            print(">ทั้งหมด</option>");
                            $productSelectes="";
                            $sqlAllProduct="select ProductID, Name from products where Deleted=0 and Type='สินค้า'".$specialCond." order by Name ASC;";
                            $rsAllProduct=mysql_query($sqlAllProduct);
                            while($allProduct=mysql_fetch_row($rsAllProduct)){
                                print("<option value=\"".$allProduct[0]."\"");
                                if($allProduct[0] == $_REQUEST['importProduct']){
                                    print(" selected");
                                    $productSelectes="<br>".$allProduct[1];
                                }
                                print(">".$allProduct[1]."</option>");
                            }
                            print("</select>");
                            print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
                            print("VAT: <select id=\"importVAT\" name=\"importVAT\" class=\"form-control input-sm\" style=\"display:inline; width:90px;\"><option value=\"1\"");
                            if($_REQUEST["importVAT"]==1){
                                print(" selected");
                            }
                            print(">มี</option><option value=\"2\"");
                            if($_REQUEST["importVAT"]==2){
                                print(" selected");
                            }
                            print(">ไม่มี</option></select>");
                            print(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ");
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
                            print("</select></div>");
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">

                    <table width="100%" border="1" class="coupon_history">
                        <tr><th colspan="9"><p style="margin:10px;">
                            <?php
                            print('รายงานการใช้สินค้าเดือน: '.$monthList[$_REQUEST['importMonth']-1]." ปี: ".$_REQUEST['importYear'].$productSelectes);
                            ?>
                        </p></th></tr>
                        <tr>
                            <th width="8%">ลำดับที่</th>
                            <th width="8%">วันที่</th>
                            <th width="9%">เลขที่</th>
                            <th>ชื่อผู้ซื้อ</th>
                            <th>ชื่อสินค้า</th>
                            <th>จำนวน</th>
                            <th width="9%">มูลค่าสินค้า</th>
                            <th width="9%">ภาษีมูลค่าเพิ่ม</th>
                            <th width="9%">จำนวนเงินรวม</th>
                        </tr>
                        <?php
                        $Total1=0;
                        $Total2=0;
                        $Total3=0;
                        $QTYField="product_history.VatQTY";
                        $vatCond=" and product_history.VatQTY>0";
                        if($_REQUEST["importVAT"]==2){
                            $QTYField="product_history.NoVatQTY";
                            $vatCond=" and product_history.NoVatQTY>0";
                        }
                        $sqlHistory="select products.Name, ServiceCode, ServiceDate, car_service.ID, ".$QTYField.", car_service.CustID from (((".$db_name.".product_history inner join ".$db_name.".car_service on car_service.ID=product_history.ServiceID) inner join ".$db_name.".car_service_detail on car_service_detail.ProductID=product_history.ProductID and car_service.ID=car_service_detail.ServiceID) inner join ".$db_name.".products on products.ProductID=product_history.ProductID) where product_history.Date>=".$startDate." and product_history.Date<=".$endDate." and car_service.Deleted=0 and product_history.ServiceID>0".$vatCond.$specialCond;
                        if($_REQUEST['importProduct']){
                            $sqlHistory.=" and products.ProductID=".intval($_REQUEST['importProduct']);
                        }
                        $rsHistory=mysql_query($sqlHistory.";");
                        $HistoryNum=mysql_num_rows($rsHistory);
                        $AllPage=ceil($HistoryNum/$ItemPerPage);
                        $sqlHistory.=" order by Date ASC, Code ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                        //echo $sqlHistory;
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            $count=(($_REQUEST['page']-1)*$ItemPerPage)+1;
                            while($History=mysql_fetch_row($rsHistory)){
                                $sqlWashService="SELECT sum(QTY*UnitPrice) from ".$db_name.".car_service_detail where ServiceID=".intval($History[3]).";";
                                $Qty="SELECT QTY from ".$db_name.".car_service_detail where ServiceID=".intval($History[3]).";";
                                $rsQty=mysql_query($Qty);
                                $QtyService=mysql_fetch_row($rsQty);
                                $rsWashService=mysql_query($sqlWashService);
                                $WashService=mysql_fetch_row($rsWashService);
                                $CustName[0]='ไม่ระบุ';
                                if($History[5]){
                                    $sqlCustName="select CustName from customer where CustID=".intval($History[5]).";";
                                    $rsCustName=mysql_query($sqlCustName);
                                    $CustName=mysql_fetch_row($rsCustName);
                                }
                                $AllTotal=$WashService[0];
                                $vatTotal=($AllTotal*7)/100;
                                $subTotal=($AllTotal-$vatTotal);
                                if($_REQUEST["importVAT"]==2){ // ไม่มี vat
                                    $vatTotal=0;
                                    $subTotal=$AllTotal;
                                }
                                print('<tr>
                                    <td>'.$count.'</td>
                                    <td>'.date('d/m/Y', $History[2]).'</td>
                                    <td>'.$History[1].'</td>
                                    <td class="text-left">'.$CustName[0].'</td>
                                    <td class="text-left">'.$History[0].'</td>
                                    <td style="text-align:right;">'.
                                    $QtyService[0].'</td>
                                    <td style="text-align:right;">'.number_format($subTotal, 2).'</td>
                                    <td style="text-align:right;">'.number_format($vatTotal, 2).'&nbsp;&nbsp;</td>
                                    <td style="text-align:right;">'.number_format($AllTotal, 2).'&nbsp;&nbsp;</td>
                                    </tr>');
                                $Total1+=$subTotal;
                                $Total2+=$vatTotal;
                                $Total3+=$AllTotal;
                                $count++;
                            }
                            print('<tr><th colspan="6" style="text-align:right;"><strong>รวม:</strong></th>
                                    <th style="text-align:right;">'.number_format($Total1, 2).'</th>
                                    <th style="text-align:right;">'.number_format($Total2, 2).'&nbsp;&nbsp;</th>
                                    <th style="text-align:right;">'.number_format($Total3, 2).'&nbsp;&nbsp;</th>
                                   </tr>');
                            print('</table><br>');
                        }
                        else{
                            print('<tr><td colspan="9" style="padding:15px;"><span style="color:red;">ไม่มีการซื้อสินค้าในเดือนที่กำหนด</span></td></tr></table>');
                        }
                        if($HistoryNum > $ItemPerPage){
                            print("<br>");
                            if($_REQUEST['page']!=1){
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']-1).'; document.forms[\'stock_movement\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                            }
                            print("<select onchange=\"javascript:document.getElementById('setPage').value=this.value; document.forms['stock_movement'].submit();\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
                            // all order page
                            for($i=1; $i<=$AllPage; $i++){
                                print('<option value="'.$i.'"');
                                if($_REQUEST['page']==$i){
                                    print(' selected');
                                }
                                print('>หน้า '.$i.'</option>');
                            }
                            print("</select>&nbsp;&nbsp;&nbsp;&nbsp;");
                            // next page
                            if($_REQUEST['page']!=$AllPage){
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']+1).'; document.forms[\'stock_movement\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                            }
                        }
                        ?>
                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <input type="hidden" name="back" id="setBack" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        &nbsp;&nbsp;&nbsp;
                    </div>

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
            <h2>รายงานสินค้าคงคลังประจำวัน</h2>
        </div>
       <br>
        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div class="panel-body">
                    <div id="PageHeader" class="panel-heading">
                    <form action="stock-report.php" method="post" class="form-horizontal" role="form" name="stock_movement">
                        <input type="hidden" name="reportType" value="3">
                        <h3 class="panel-title" style="margin: 10px 0;">
                        <?php
                            print("รายงานสินค้าคงคลังประจำวันที่:");
                            print("&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="serviceDate" value="'.$_REQUEST['serviceDate'].'" style="display:inline; width:90px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print('<input type="hidden" class="form-control Calendar" name="serviceDateTo" value="'.$_REQUEST['serviceDateTo'].'" style="display:inline; width:100px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print("&nbsp;&nbsp;&nbsp;");
                            print('<button type="submit" class="btn btn-xs btn-primary btn-rounder">GO</button>');
                        ?></h3>
                    </form>
                </div>
                    <table width="100%" border="1" class="coupon_history">
                        <tr><th colspan="9"><p style="margin:10px;">
                            <?php
                            print('รายงานสินค้าคงคลังประจำวันที่: '.$_REQUEST['serviceDate']);
                            ?>
                        </p></th></tr>
                        <tr>
                            <th width="12%">รหัสสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <?php
                            $Colspan=4;
                            $sqlProduct="select StockName from ".$db_name.".stock where stock.Deleted=0 order by stock.ID ASC;";
                            $rsProduct=mysql_query($sqlProduct);
                            while($Product=mysql_fetch_row($rsProduct)){
                                print('<th width="12%">'.$Product[0].'</th>');
                                $Colspan++;
                            }
                            ?>
                            <th width="12%">รวมสต็อกคงเหลือ</th>
                            <th width="12%">ราคาทุน/หน่วย (ก่อนภาษี)</th>
                            <th width="12%">มูลค่า</th>
                        </tr>
                        <?php
                        $AllSumValue=0;
                        $sqlHistory="select ProductID, Code, Name, AvgCost from ".$db_name.".products where products.Deleted=0 and Type='สินค้า'".$specialCond." order by Code ASC, Name ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                        //echo $sqlHistory;
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                print('<tr>
                                    <td>'.$History[1].'</td>
                                    <td style="text-align:left;">&nbsp;'.$History[2].'</td>');
                                $ProductQTY=0;
                                $numstock=1;
                                $sqlStock="SELECT QTY from ".$db_name.".stock left join ".$db_name.".product_stock on stock.ID=product_stock.StockID where ProductID=".intval($History[0])." and stock.Deleted=0 order by stock.ID ASC;";
                                $rsStock=mysql_query($sqlStock);
                                while($InStock=mysql_fetch_row($rsStock)){
                                    if(!$InStock[0]){ 
                                        $InStock[0]=0;
                                        $numstock=1;
                                     }
                                    if($_REQUEST['serviceDate'] != $_REQUEST['serviceDateTo']){
                                    $sqlStock2="SELECT QTY from ((".$db_name.".product_history inner join ".$db_name.".products on products.ProductID=product_history.ProductID) inner join ".$db_name.".stock on stock.ID=product_history.StockID) where product_history.StockID=".$numstock." and 
                                        product_history.ProductID=".intval($History[0])." and
                                        product_history.Date>=".$startDate." and product_history.Date<=".$endDate.$specialCond;
                                    $rsStock2=mysql_query($sqlStock2);
                                        while($InStock2=mysql_fetch_row($rsStock2)){
                                           $InStock[0]-=$InStock2[0];
                                        }
                                    }
                                    $ProductQTY+=$InStock[0];
                                    $numstock+=1;
                                    print('<td>'.number_format($InStock[0], 2).'</td>');
                                }
                                $vat=round(($History[3]*7)/100, 2);
                                $History[3]=round($History[3]-$vat, 2);
                                $SumValue=round($ProductQTY*$History[3], 2);
                                $AllSumValue+=round($SumValue, 2);
                                print('<td>'.number_format($ProductQTY, 2).'</td>');
                                print('<td style="text-align:right;">'.number_format($History[3], 2).'&nbsp;</td>');
                                print('<td style="text-align:right;">'.number_format($SumValue, 2).'&nbsp;</td>');
                                print('</tr>');
                            }
                            print("<tr><th colspan=\"".$Colspan."\">&nbsp;</th><th style=\"text-align:right;\">".number_format($AllSumValue, 2)."&nbsp;</th></tr>");
                            print('</table><br>');
                        }
                        else{
                            print('<tr><td colspan="'.$Colspan.'" style="padding:15px;"><span style="color:red;">ไม่มีการสินค้าในระบบ</span></td></tr></table>');
                        }
                        ?>
                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <input type="hidden" name="page" id="setPage" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" name="back" id="setBack" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        &nbsp;&nbsp;&nbsp;
                    </div>

                </div>
            </div>
        </div>
    </section>
<?php
}
include("footer.php");
?>