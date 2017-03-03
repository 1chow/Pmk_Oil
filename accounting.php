<?php
include("dbvars.inc.php");
if(!preg_match('/-7-/', $EmpAccess) && !preg_match('/-11-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

$alertTxt='';
if(isset($_REQUEST["deleteSell"]) && intval($_REQUEST["deleteSell"])){
    $SellID=$_POST["deleteSell"];
    // select old information
    $sqlOldVal="select NoVatQTY, VatQTY, ProductID, QTY, StockID from ".$db_name.".product_history where ServiceID=".intval($SellID)*(-1).";";
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
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>ลบข้อมูลเรียบร้อยแล้ว</div>';
}
else if(isset($_REQUEST["deletePayment"]) && intval($_REQUEST["deletePayment"])){
    $SellID=$_POST["deletePayment"];
    $sqlDelete="delete from ".$db_name.".account_daily where ID=".intval($SellID).";";
    $rsDelete=mysql_query($sqlDelete);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>ลบข้อมูลเรียบร้อยแล้ว</div>';
    $BalanceArr=array(3 => -1, 4 => 1);
    if($_POST["DelOldType"]==3 || $_POST["DelOldType"]==4){
        $SystemBalance=round($SystemBalance-($_POST["DelBalance"]*$BalanceArr[$_POST["DelOldType"]]), 2);
        $queryUpdate="Update system Set ConstantValue=".floatval($SystemBalance)." Where ConstantName='SystemBalance';";
        $rsUpdate=mysql_query($queryUpdate);
    }
}
else if(isset($_POST['accountTotal']) && $_POST['accountTotal']>0 && trim($_POST['accountName'])){
    $_POST["accountTotal"]=preg_replace("/,/", "", $_POST["accountTotal"]);
    if(!isset($_POST['BookCodeNo'])){
        $_POST['BookCodeNo']="";
    }
    if($_POST['accountType']!=2 || !$_POST['OnTaxReport']){
        $_POST['BookCodeNo']="";
        $_POST['OnTaxReport']=0;
    }
    if(!$_POST['AccountID']){
        $sqlInsert="INSERT INTO ".$db_name.".account_daily (Type, Name, Total, PaidTo, PaidDate, Note, BookCodeNo) VALUES ('".intval($_POST['accountType'])."', '".mysql_real_escape_string(trim($_POST['accountName']))."', '".floatval($_POST["accountTotal"])."', '".intval($_POST['PayTo'])."', '".time()."', '".mysql_real_escape_string(trim($_POST['accountNote']))."', '".mysql_real_escape_string(trim($_POST['BookCodeNo']))."');";
    }
    else{
        $sqlInsert="UPDATE ".$db_name.".account_daily SET Type='".intval($_POST['accountType'])."', Name='".mysql_real_escape_string(trim($_POST['accountName']))."', Total='".floatval($_POST["accountTotal"])."', PaidTo='".intval($_POST['PayTo'])."', Note='".mysql_real_escape_string(trim($_POST['accountNote']))."', BookCodeNo='".mysql_real_escape_string(trim($_POST['BookCodeNo']))."' where ID=".intval($_POST['AccountID']).";";
        if(!isset($_REQUEST["ShowDetails"])){
            $_REQUEST['report']=1;
        }
        $_REQUEST['payDate']=date("d/m/Y", $_POST['oldPayDate']);
    }
    $rsInsert=mysql_query($sqlInsert);
    if(intval($_POST['accountType'])==3 || intval($_POST['accountType'])==4){
        $BalanceArr = array(3 => -1, 4 => 1);
        if(intval($_POST['AccountID'])){
            // delete old balance
            $SystemBalance=round($SystemBalance-($_POST["OldBalance"]*$BalanceArr[$_POST["OldType"]]), 2);
            $queryUpdate="Update system Set ConstantValue=".floatval($SystemBalance)." Where ConstantName='SystemBalance';";
            $rsUpdate=mysql_query($queryUpdate);
        }
        // update balance
        $BalanceValue=round($_POST["accountTotal"]*($BalanceArr[$_POST["accountType"]]), 2);
        $queryUpdate="Update system Set ConstantValue=ConstantValue+".$BalanceValue." Where ConstantName='SystemBalance';";
        $rsUpdate=mysql_query($queryUpdate);
    }
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}

$queryConstants="Select ConstantName, ConstantValue From ".$db_name.".system where ConstantName='SystemBalance';";
$rsConstants=mysql_query($queryConstants);
while($Constants=mysql_fetch_row($rsConstants)){
    eval("$".$Constants[0]."=\"".$Constants[1]."\";");
}
if(isset($_REQUEST["completed"]) && intval($_REQUEST["completed"])){
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}

include("header.php");

if(!isset($_REQUEST['back'])){
    $_REQUEST['back']='index';
}
if(isset($_REQUEST["back2page"]) && trim($_REQUEST["back2page"])){ $_REQUEST['back']=$_REQUEST['back2page']; }
if(isset($_REQUEST['report']) && $_REQUEST['report']){
    if(!isset($_REQUEST['payDate'])){
        $_REQUEST['payDate']=date("d/m/Y", time());
    }
    if(!isset($_REQUEST['EmpName'])){
        $_REQUEST['EmpName']=0;
    }
    $SetDate=explode("/", $_REQUEST['payDate']);
    $startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    $endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
    if(isset($_REQUEST['onDate']) && trim($_REQUEST['onDate'])){
        $SetDate=explode("-", $_REQUEST['onDate']);
        $startDate=$SetDate[0];
        $endDate=$SetDate[1];
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>รายงานรายรับ / รายจ่าย</h2>
        </div>

        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <form action="accounting.php" method="post" class="form-horizontal" role="form" name="accountingForm">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="back2page" value="<?php if(isset($_REQUEST['back2page'])){ print($_REQUEST['back2page']); } ?>">
                        <input type="hidden" name="report" value="1">
                        <input type="hidden" name="deletePayment" id="deletePayment" value="0">
                        <input type="hidden" name="deleteSell" id="deleteSell" value="0">
                        <input type="hidden" name="DelBalance" id="DelBalance" value="0">
                        <input type="hidden" name="DelOldType" id="DelOldType" value="0">
                        <h3 class="panel-title" style="margin: 10px 0;">
                        <?php
                            print("รายงานรายรับ / รายจ่ายประจำวันที่:");
                            print("&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="payDate" value="'.$_REQUEST['payDate'].'" style="display:inline; width:100px;">');
                            print("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; พนักงาน: &nbsp;<select name=\"EmpName\" class=\"form-control inline_input input-sm\" style=\"width:170px;\"><option value=\"0\">ทั้งหมด</option>");
                            $sqlEmp="select concat(FirstName, ' ', LastName), EmpID from employee where employee.EmpID!=1 and Deleted=0 order by FirstName ASC, LastName ASC;";
                            $rsEmp=mysql_query($sqlEmp);
                            while($EmpName=mysql_fetch_row($rsEmp)){
                                print("<option value=\"".$EmpName[1]."\"");
                                if($_REQUEST['EmpName']==$EmpName[1]){
                                    print(" selected");
                                }
                                print(">".$EmpName[0]."</option>");
                            }
                            print("</select>");
                            print('&nbsp;&nbsp;&nbsp;<button type="button" onclick="javascript:document.forms[\'accountingForm\'].submit();" class="btn btn-xs btn-primary btn-rounder">GO</button>');
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">
                    <?php print($alertTxt); ?>

                    <table width="100%" border="1" class="coupon_history" style="white-space:normal;">
                        <tr><th colspan="10"><p style="margin:10px;">
                            <?php
                            print('รายงานรายรับ / รายจ่ายประจำวันที่: '.$_REQUEST['payDate']);
                            ?>
                        </p></th></tr>
                        <tr>
                            <th>ลำดับที่</th>
                            <th>วันที่</th>
                            <th>รายการ</th>
                            <th>จ่ายให้ / รับเงินจาก</th>
                            <th>รับเงิน</th>
                            <th>จ่ายเงิน</th>
                            <th width="15%">หมายเหตุ</th>
                            <th class="printhidden">&nbsp;</th>
                        </tr>
                        <?php
                        $count=1;
                        $RealTotalPlus=0;
                        $RealTotalMinus=0;
                        $sqlHistory="SELECT ID, Type, Name, Total, account_daily.PaidDate, Note, CONCAT(FirstName, ' ', LastName), NickName, ForActionID from (".$db_name.".account_daily left join ".$db_name.".employee on employee.EmpID=account_daily.PaidTo) where (account_daily.PaidTo>0 or Name='ยอดนำส่งเจ้าของกิจการ') and account_daily.PaidDate>=".$startDate." and account_daily.PaidDate<=".$endDate;
                        if(intval($_REQUEST['EmpName'])){
                            $sqlHistory.=" and account_daily.PaidTo=".intval($_REQUEST['EmpName']);
                        }
                        $sqlHistory.=" order by account_daily.PaidDate ASC;";
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                $nickName="";
                                if(trim($History[7])){
                                    $nickName=" (".$History[7].")";
                                }
                                $TotalPlus='0.00';
                                $TotalMinus='0.00';
                                if($History[1]==1 || $History[1]==3){ // รายรับ
                                    $RealTotalPlus+=round($History[3], 2);
                                    $TotalPlus=number_format($History[3], 2);
                                }
                                else{
                                    $RealTotalMinus+=round($History[3], 2);
                                    $TotalMinus=number_format($History[3], 2);
                                }
                                $LinkEdit=$History[2];
                                $DeleteLink="";
                                if($PermissionNo>=2 && preg_match('/-7-/', $EmpAccess)){
                                    if($History[2]=='บริการล้างรถ' || $History[2]=='บริการเปลี่ยนน้ำมันเครื่อง' || $History[2]=='ยอดนำส่งเจ้าของกิจการ'){
                                        $History[5]="&nbsp;";
                                        $LinkEdit=$History[2];
                                        $DeleteLink="&nbsp;";
                                    }
                                    else if($History[2]!='ขายสินค้า'){
                                        $LinkEdit='<a href="javascript:gotopage(\'accounting.php?back='.$_REQUEST['back'].'&EditID='.$History[0].'\');">'.$History[2].'</a>';
                                        $DeleteLink='<a href="javascript:void(0);" onclick="javascript:deletePayment('.$History[0].', '.$History[3].', '.$History[1].');" style="color:red;">ลบ</a>';
                                    }
                                    else if($History[8]){ // ขายสินค้า
                                        $sqlOldVal="select ServiceID from ".$db_name.".product_history where ServiceID=".intval($History[8])*(-1).";";
                                        $rsOldVal=mysql_query($sqlOldVal);
                                        $OldVal=mysql_fetch_row($rsOldVal);
                                        if($OldVal[0]){
                                            $LinkEdit='<a href="javascript:gotopage(\'sellproducts.php?EditSell='.$History[8].'&back=accounting&report='.$_REQUEST['report'].'&payDate='.$_REQUEST['payDate'].'\');">'.$History[2].'</a>';
                                            $DeleteLink='<a href="javascript:void(0);" onclick="javascript:deleteSell('.$History[8].');" style="color:red;">ลบ</a>';
                                        }
                                    }
                                }
                                print('<tr>
                                    <td>'.$count.'</td>
                                    <td>'.date('d/m/Y', $History[4]).'</td>
                                    <td style="text-align:left;">&nbsp;'.$LinkEdit.'</td>
                                    <td style="text-align:left;">&nbsp;'.$History[6].$nickName.'</td>
                                    <td style="text-align:right;">'.$TotalPlus.'&nbsp;</td>
                                    <td style="text-align:right;">'.$TotalMinus.'&nbsp;</td>
                                    <td style="text-align:left;">'.$History[5].'</td>
                                    <td class="printhidden">'.$DeleteLink.'</td>
                                    </tr>');
                                $count++;
                            }
                            print('<tr><td colspan="4" style="text-align:right;"><b>รวม:</b>&nbsp;</td><td style="text-align:right;">'.number_format($RealTotalPlus, 2).'</td><td style="text-align:right;">'.number_format($RealTotalMinus, 2).'</td><td>&nbsp;</td><td class="printhidden">&nbsp;</td></tr>');
                        }
                        else{
                            print('<tr><td colspan="11" style="padding:15px;"><span style="color:red;">ไม่มีรายงานรายรับ / รายจ่ายประจำวันที่กำหนด</span></td></tr>');
                        }
                        ?>
                    </table>

                    <?php
                    $sqlDisplay="select concat(FirstName, ' ', LastName), sum(if(Type!=1 && Type!=3, -1, 1)*Total), employee.EmpID as TotalMoney, count(account_daily.ID) from ".$db_name.".account_daily inner join ".$db_name.".employee on employee.EmpID=account_daily.PaidTo where PaidTo>0 and employee.Deleted=0 group by PaidTo order by 1 ASC;";
                    $rsDisplay=mysql_query($sqlDisplay);
                    if(mysql_num_rows($rsDisplay)){
                        print('<br>
                            <table width="50%" border="1" class="coupon_history">
                            <tr><th colspan="3">สรุปยอดกระแสเงินสดของพนักงาน</th></tr>
                            <tr><th>พนักงาน</th><th>คงเหลือ</th><th>ค้างจ่าย</th></tr>');
                        while($Display = mysql_fetch_row($rsDisplay)){
                            print('<tr><td style="text-align:left;" width="33%">&nbsp;'.$Display[0].'</td>');
                            $DetailLink=number_format($Display[1], 2);
                            if($Display[3] <= 150){
                                $DetailLink='<a href="javascript:gotopage(\'accounting.php?ShowDetails='.$Display[2].'&back='.$_REQUEST['back'].'\');">'.number_format($Display[1], 2)."</a>";
                            }
                            if($Display[1]>0){
                                print('<td width="33%">'.$DetailLink.'</td><td>&nbsp;</td>');
                            }
                            else{
                                print('<td width="33%">&nbsp;</td><td>'.$DetailLink.'</td>');
                            }
                            print('</tr>');
                        }
                        print('<tr><th colspan="3" style="text-align:left;">&nbsp;&nbsp;&nbsp; เงินกองกลางคงเหลือ: <span id="SystemBalance">'.number_format($SystemBalance, 2).'</span> บาท</th></tr>');
                        print('</table>');
                    }
                    ?>
                    <br>
                    <div id="actionBar" class="actionBar right">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <!-- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button> -->
                        &nbsp;&nbsp;&nbsp;
                    </div>

                </div>
            </div>
        </div>
    </section>
<?php
}
else if(isset($_REQUEST['ShowDetails']) && intval($_REQUEST['ShowDetails'])){
    $sqlEmployee="SELECT CONCAT(FirstName, ' ', LastName) from ".$db_name.".employee where employee.EmpID=".intval($_REQUEST['ShowDetails']).";";
    $rsEmployee=mysql_query($sqlEmployee);
    $Employee=mysql_fetch_row($rsEmployee);
    $Condition="";
    if(isset($_REQUEST['onDate']) && trim($_REQUEST['onDate'])){
        $SetDate=explode("-", $_REQUEST['onDate']);
        $startDate=$SetDate[0];
        $endDate=$SetDate[1];
        $Condition=" and account_daily.PaidDate>=".$startDate." and account_daily.PaidDate<=".$endDate;
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>รายละเอียดรายรับ / รายจ่ายของ <?php print($Employee[0]); ?></h2>
        </div>

        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div class="panel-body">
                    <?php print($alertTxt); ?>

                    <table width="100%" border="1" class="coupon_history" style="white-space:normal;">
                        <tr><th colspan="10"><p style="margin:10px;">
                            <?php
                            print('รายละเอียดรายรับ / รายจ่ายของ '.$Employee[0]);
                            ?>
                        </p></th></tr>
                        <tr>
                            <th>ลำดับที่</th>
                            <th>วันที่</th>
                            <th>รายการ</th>
                            <th>จ่ายให้ / รับเงินจาก</th>
                            <th>รับเงิน</th>
                            <th>จ่ายเงิน</th>
                            <th width="15%">หมายเหตุ</th>
                        </tr>
                        <?php
                        $count=1;
                        $RealTotalPlus=0;
                        $RealTotalMinus=0;
                        $sqlHistory="SELECT ID, Type, Name, Total, account_daily.PaidDate, Note, CONCAT(FirstName, ' ', LastName), NickName, ForActionID from (".$db_name.".account_daily inner join ".$db_name.".employee on employee.EmpID=account_daily.PaidTo) where account_daily.PaidTo=".intval($_REQUEST['ShowDetails']).$Condition." order by account_daily.PaidDate ASC;";
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                $nickName="";
                                if(trim($History[7])){
                                    $nickName=" (".$History[7].")";
                                }
                                $TotalPlus='-';
                                $TotalMinus='-';
                                if($History[1]==1 || $History[1]==3){ // รายรับ
                                    $RealTotalPlus+=round($History[3], 2);
                                    $TotalPlus=number_format($History[3], 2);
                                }
                                else{
                                    $RealTotalMinus+=round($History[3], 2);
                                    $TotalMinus=number_format($History[3], 2);
                                }
                                $LinkEdit=$History[2];
                                if($PermissionNo>=2 && preg_match('/-7-/', $EmpAccess)){
                                    $LinkEdit='<a href="javascript:gotopage(\'accounting.php?fromShow='.$_REQUEST['ShowDetails'];
                                    if(isset($_REQUEST['onDate']) && trim($_REQUEST['onDate'])){
                                        $LinkEdit.="&onDate=".$_REQUEST['onDate'];
                                    }
                                    $LinkEdit.='&back='.$_REQUEST['back'].'&EditID='.$History[0].'\');">'.$History[2].'</a>';
                                }
                                if($History[2]=='บริการล้างรถ' || $History[2]=='บริการเปลี่ยนน้ำมันเครื่อง'){
                                    $History[5]="&nbsp;";
                                    $LinkEdit=$History[2];
                                }
                                if($History[2]=='ขายสินค้า'){
                                    $LinkEdit=$History[2];
                                    if($History[8]){
                                        $sqlOldVal="select ServiceID from ".$db_name.".product_history where ServiceID=".intval($History[8])*(-1).";";
                                        $rsOldVal=mysql_query($sqlOldVal);
                                        $OldVal=mysql_fetch_row($rsOldVal);
                                        if($OldVal[0]){
                                            $LinkEdit='<a href="javascript:gotopage(\'sellproducts.php?EditSell='.$History[8].'&back=accounting&ShowDetails='.$_REQUEST['ShowDetails'].'\');">'.$History[2].'</a>';
                                        }
                                    }
                                }
                                print('<tr>
                                    <td>'.$count.'</td>
                                    <td>'.date('d/m/Y', $History[4]).'</td>
                                    <td class="text-left">'.$LinkEdit.'</td>
                                    <td>'.$History[6].$nickName.'</td>
                                    <td class="text-right">'.$TotalPlus.'</td>
                                    <td class="text-right">'.$TotalMinus.'</td>
                                    <td class="text-left">'.$History[5].'</td>
                                    </tr>');
                                $count++;
                            }
                            print('<tr><td colspan="4" style="text-align:right;"><b>รวม:</b>&nbsp;</td><td class="text-right">'.number_format($RealTotalPlus, 2).'</td><td class="text-right">'.number_format($RealTotalMinus, 2).'</td><td class="text-right">'.number_format($RealTotalPlus-$RealTotalMinus, 2).'</td></tr>');
                        }
                        else{
                            print('<tr><td colspan="10" style="padding:15px;"><span style="color:red;">ไม่มีรายงานรายรับ / รายจ่ายประจำวันที่กำหนด</span></td></tr>');
                        }
                        ?>
                    </table>
                    <br>
                    <div id="actionBar" class="actionBar right">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php
                        if(isset($_REQUEST["back2page"]) && trim($_REQUEST["back2page"])){
                            print('<input type="hidden" id="backPage" value="'.$_REQUEST["back2page"].'.php">');
                        }
                        else{
                            print('<input type="hidden" id="backPage" value="accounting.php?report=1&back='.$_REQUEST["back"].'">');
                        }
                        ?>
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
    if(!isset($_REQUEST['EditID'])){
        $_REQUEST['EditID']=0;
        $FixBack='';
    }
    $sqlHistory="SELECT Type, Name, Total, PaidDate, Note, PaidTo, BookCodeNo from ".$db_name.".account_daily where ID=".intval($_REQUEST['EditID']).";";
    $rsHistory=mysql_query($sqlHistory);
    $History=mysql_fetch_row($rsHistory);
    if(isset($_REQUEST['fromShow'])){
        $FixBack='accounting.php?ShowDetails='.$_REQUEST['fromShow'].'&back='.$_REQUEST['back'];
    }
    else if($_REQUEST['EditID']){
        $FixBack='accounting.php?report=1&back='.$_REQUEST['back'].'&payDate='.date('j/m/Y', $History[3]);
    }
?>
    <section id="pageContent" class="pageContent">
        <div id="PageHeader" class="title-body">
            <h2>บันทึกรายรับ / รายจ่ายประจำวัน</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <p class="text-right">เงินกองกลาง: <span id="SystemBalance"><?php print(number_format($SystemBalance, 2)); ?></span> บาท</p>
                    <form action="accounting.php" onsubmit="javascript:return AccountingCHK();" id="AccountForm" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="back2page" value="<?php if(isset($_REQUEST['back2page'])){ print($_REQUEST['back2page']); } ?>">
                        <input type="hidden" name="AccountID" value="<?php print($_REQUEST['EditID']); ?>">
                        <input type="hidden" name="oldPayDate" value="<?php if($History[3]){ print($History[3]); }else{ print(time()); } ?>">
                        <input type="hidden" name="OldBalance" value="<?php print($History[2]); ?>">
                        <input type="hidden" name="OldType" value="<?php print($History[0]); ?>">
                        <?php
                        if(isset($_REQUEST['fromShow'])){
                            print('<input type="hidden" name="ShowDetails" value="'.$_REQUEST["fromShow"].'">');
                        }
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">วันที่:</label>
                            <div class="col-sm-3" style="margin-top:7px;">
                                <?php if($History[3]){ print(date("d-m-Y", $History[3])); }else{ print(date("d-m-Y", time())); } ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">ประเภท:</label>
                            <div class="col-sm-7" style="margin-top:5px;">
                                <input type="radio" class="form-control inline_input" name="accountType" id="accountType1" value="1"<?php if(!$_REQUEST['EditID'] || $History[0]==1){ print(' checked'); } ?>> รายรับ
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" class="form-control inline_input" name="accountType" id="accountType2" value="2"<?php if($History[0]==2){ print(' checked'); } ?>> รายจ่าย
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" class="form-control inline_input" name="accountType" id="accountType3" value="3"<?php if($History[0]==3){ print(' checked'); } ?>> เบิกเงินจากกองกลาง
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" class="form-control inline_input" name="accountType" id="accountType4" value="4"<?php if($History[0]==4){ print(' checked'); } ?>> คืนเงินเข้ากองกลาง
                            </div>
                        </div>

                        <div id="WantTax"<?php if($History[0]!=2){ print(" style=\"display:none;\""); } ?>>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">แสดงในรายการภาษีซื้อ:</label>
                                <div class="col-sm-3" style="margin-top:5px;">
                                    <input type="radio" class="form-control inline_input" name="OnTaxReport" id="OnTaxReport1" value="1"<?php if(trim($History[6])){ print(' checked'); } ?>> แสดง
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="radio" class="form-control inline_input" name="OnTaxReport" id="OnTaxReport2" value="0"<?php if(!trim($History[6])){ print(' checked'); } ?>> ไม่แสดง
                                </div>
                            </div>

                            <div class="form-group" id="BookCodeNo"<?php if(!trim($History[6])){ print(" style=\"display:none;\""); } ?>>
                                <label class="col-sm-2 control-label">เล่มที่/เลขที่:</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control" name="BookCodeNo" value="<?php print($History[6]); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" id="ForTitle">ผู้รับเงิน:</label>
                            <div class="col-sm-4">
                                <select name="PayTo" class="form-control input-sm" style="display:inline; width:200px;">
                                <?php
                                $sqlEmp="select EmpID, CONCAT(FirstName, ' ', LastName), NickName from ".$db_name.".employee where employee.EmpID!=1 and Deleted=0 order by FirstName ASC;";
                                $rsEmp=mysql_query($sqlEmp);
                                while($Employee=mysql_fetch_row($rsEmp)){
                                    $nickName="";
                                    if(trim($Employee[2])){
                                        $nickName=" (".$Employee[2].")";
                                    }
                                    print('<option value="'.$Employee[0].'"');
                                    if($Employee[0]==$History[5]){
                                        print(" selected");
                                    }
                                    print('>'.$Employee[1].$nickName.'</option>');
                                }
                                ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">จำนวนเงิน:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control inline_input price" name="accountTotal" value="<?php print(number_format($History[2], 2)); ?>" style="width:120px;"> บาท
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">รายการ:</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="accountName" value="<?php print($History[1]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">หมายเหตุ:</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="accountNote" value="<?php print($History[4]); ?>">
                            </div>
                        </div>

                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" value="<?php
                            if($FixBack){
                                print($FixBack);
                            }
                            else{
                                print($_REQUEST['back'].'.php');
                            }
                            ?>">
                            <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="reset" class="btn btn-danger btn-rounder">ล้างข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php
}
include("footer.php");
?>