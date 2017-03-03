<?php
include("dbvars.inc.php");
if(!preg_match('/-12-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

$canSubmit=0;
$alertTxt="";
if(isset($_REQUEST["TimeForCheck"]) && (!isset($_COOKIE["TimeCheck"]) || $_COOKIE["TimeCheck"]!=$_REQUEST["TimeForCheck"])){
    setcookie("TimeCheck", $_REQUEST["TimeForCheck"], 0, "/");
    $_COOKIE["TimeCheck"]=$_REQUEST["TimeForCheck"];
    $canSubmit=1;
}
$BalanceArr = array(1 => 1, 2 => -1, 3 => 1, 4 => -1);
if($canSubmit && isset($_REQUEST["BalanceAmount"]) && $_REQUEST["BalanceAmount"]){
    $_POST["BalanceAmount"]=preg_replace("/,/", "", $_POST["BalanceAmount"]);

    $SetDateArr=explode("/", $_REQUEST['BalanceDate']);
    $SetDate=mktime(9, 00, 0, $SetDateArr[1], $SetDateArr[0], $SetDateArr[2]);
    $BalanceValue=round($_POST["BalanceAmount"]*($BalanceArr[$_POST["BalanceAction"]]), 2);
    if(!intval($_POST["UpdateBalanceID"])){
        $sqlInsert="INSERT INTO pmkoil_data.account_balance (BalanceAction, BalanceAmount, BalanceDate) VALUES (".intval($_POST["BalanceAction"]).", '".floatval($BalanceValue)."', ".intval($SetDate).");";
    }
    else{
        $sqlInsert="UPDATE pmkoil_data.account_balance SET BalanceAction=".intval($_POST["BalanceAction"]).", BalanceAmount='".floatval($BalanceValue)."', BalanceDate=".intval($SetDate)." WHERE account_balance.ID=".intval($_POST["UpdateBalanceID"]).";";
        // delete old balance
        $SystemBalance=round($SystemBalance+($_POST["OldBalance"]*$BalanceArr[$_POST["OldType"]]), 2);
        $queryUpdate="Update system Set ConstantValue=".floatval($SystemBalance)." Where ConstantName='SystemBalance';";
        $rsUpdate=mysql_query($queryUpdate);
    }
    $rsInsert=mysql_query($sqlInsert);
    // update balance
    $queryUpdate="Update system Set ConstantValue=ConstantValue+".($BalanceValue*(-1))." Where ConstantName='SystemBalance';";
    $rsUpdate=mysql_query($queryUpdate);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}
else if(isset($_POST["DeleteItem"]) && intval($_POST["DeleteItem"])){ // delete employer
    $sqlDelete="Delete from ".$db_name.".account_balance where account_balance.ID=".intval($_POST["DeleteItem"]).";";
    $rsDelete=mysql_query($sqlDelete);
    // delete old balance
    $SystemBalance=round($SystemBalance+($_POST["DelBalance"]), 2);
    //echo $SystemBalance."***";
    $queryUpdate="Update system Set ConstantValue=".floatval($SystemBalance)." Where ConstantName='SystemBalance';";
    $rsUpdate=mysql_query($queryUpdate);
    print(number_format($SystemBalance, 2));
    exit();
}

include("header.php");
$UpdateID=0;
$UpdateAction=2;
$UpdateDate=date("d/m/Y", time());
$UpdateAmount="";
if(isset($_REQUEST["UpdateBalance"]) && intval($_REQUEST["UpdateBalance"])){
    $UpdateID=$_REQUEST["UpdateBalance"];
    $sqlBalance="select BalanceAction, BalanceAmount, BalanceDate from account_balance where ID=".intval($_REQUEST["UpdateBalance"])." order by BalanceDate ASC, ID ASC;";
    $rsBalance=mysql_query($sqlBalance);
    $BalanceInfo=mysql_fetch_row($rsBalance);
    $UpdateAction=$BalanceInfo[0];
    $UpdateDate=date("d/m/Y", $BalanceInfo[2]);
    $UpdateAmount=abs($BalanceInfo[1]);
}
if(!isset($_REQUEST["BalanceReport"])){
    $_REQUEST["BalanceReport"]=date("d/m/Y", time());
}

$queryConstants="Select ConstantName, ConstantValue From ".$db_name.".system where ConstantName='SystemBalance';";
$rsConstants=mysql_query($queryConstants);
while($Constants=mysql_fetch_row($rsConstants)){
    eval("$".$Constants[0]."=\"".$Constants[1]."\";");
}
?>
<section class="pageContent">
    <div class="title-body">
        <h2>รายการเคลื่อนไหวทางบัญชี <?php if(isset($_REQUEST["UpdateBalance"])){ print("<span style=\"color:red;\">(แก้ไขรายการ)</span>"); }?></h2>
    </div>

    <div class="content-center">
        <div id="pageContent" class="panel panel-default">
            <div class="panel-body">
                <?php print($alertTxt); ?>
                <form action="accounting_balance.php" method="post" class="form-horizontal" role="form" name="accounting_balance" onsubmit="javascript:return checkBalance();">
                <input type="hidden" name="UpdateBalanceID" value="<?php print($UpdateID); ?>">
                <input type="hidden" name="OldType" id="OldType" value="<?php print($UpdateAction); ?>">
                <input type="hidden" name="OldBalance" id="OldBalance" value="<?php print($UpdateAmount); ?>">
                <input type="hidden" name="TimeForCheck" value="<?php print(time()); ?>">
                <table width="50%" class="coupon_history payment_balance">
                <tr>
                    <td class="text-right"><strong>รายการเคลื่อนไหวทางบัญชี:</strong></td>
                    <td><select name="BalanceAction" class="form-control inline_input input-sm" style="width:200px;">
                        <option value="2"<?php if($UpdateAction==2){ print(" selected"); } ?>>เติมเงินเข้ากองกลาง</option>
                        <option value="1"<?php if($UpdateAction==1){ print(" selected"); } ?>>รับเงินจากกองกลาง</option>
                        <option value="3"<?php if($UpdateAction==3){ print(" selected"); } ?>>นำเงินไปฝากแบงค์</option>
                        <!-- <option value="4"<?php if($UpdateAction==4){ print(" selected"); } ?>>เพิ่มเงินเข้าระบบ</option> -->
                    </select></td>
                </tr>
                <tr>
                    <td class="text-right"><strong>วันที่:</strong></td>
                    <td><input type="text" name="BalanceDate" id="BalanceDate" class="form-control inline_input Calendar" value="<?php print($UpdateDate); ?>" style="width:120px;"></td>
                </tr>
                <tr>
                    <td class="text-right"><strong>จำนวนเงิน:</strong></td>
                    <td><input type="text" class="form-control inline_input price" id="BalanceAmount" name="BalanceAmount" value="<?php if($UpdateAmount){ print(number_format($UpdateAmount, 2)); } ?>" style="width:120px;"> บาท</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right"><br><input type="hidden" id="submitTo" value="accounting_balance.php"><button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                    <input type="hidden" id="backPage" value="index.php">
                    <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder" style="margin:0 0 0 25px;">ย้อนกลับ</button>
                    </td>
                </tr>
                </table>
                </form>

                <br><br><br>
                <h4 class="text-center">รายการเคลื่อนไหวทางบัญชีประจำวันที่ <input type="text" name="BalanceDate" id="BalanceDateReport" class="form-control inline_input Calendar" value="<?php print($_REQUEST["BalanceReport"]); ?>" style="width:120px;"></h4>
                <p class="text-right">เงินกองกลาง: <span id="SystemBalance"><?php print(number_format($SystemBalance, 2)); ?></span> บาท</p>
                <table width="80%" style="font-size:12px;" class="table table-condensed table-striped table-default">
                <tr>
                    <th>&nbsp;</th>
                    <th>วันที่</th>
                    <th width="30%">รายการ</th>
                    <th>ประเภท</th>
                    <th class="text-right">จำนวนเงิน</th>
                    <th width="20%" class="text-center">ลบ</th>
                </tr>
                <?php
                $SetDate=explode("/", $_REQUEST["BalanceReport"]);
                $startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
                $endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
                $sqlBalance="select BalanceAction, BalanceAmount, BalanceDate, ID from account_balance where BalanceDate>=".intval($startDate)." and BalanceDate<=".intval($endDate)." order by BalanceDate ASC, ID ASC;";
                $rsBalance=mysql_query($sqlBalance);
                if(mysql_num_rows($rsBalance)){
                    $count=0;
                    $DetailArr = array(1 => "รับเงินจากกองกลาง", 2 => "เติมเงินเข้ากองกลาง", 3 => "นำเงินไปฝากแบงค์", 4 => "เพิ่มเงินเข้าระบบ");
                    $DetailArr2 = array(1 => "เครดิต", 2 => "เดบิต", 3 => "เครดิต", 4 => "เดบิต");
                    while($BalanceInfo=mysql_fetch_row($rsBalance)){
                        $count++;
                        print("<tr id=\"item-".$BalanceInfo[3]."\">
                        <td>".$count."</td>
                        <td>".date("d/m/Y", $BalanceInfo[2])."</td>
                        <td>".$DetailArr[$BalanceInfo[0]]."</td>
                        <td>".$DetailArr2[$BalanceInfo[0]]."</td>
                        <td class=\"text-right\">".number_format($BalanceInfo[1], 2)."</td>
                        <td class=\"text-center\">
                            <div id=\"".$BalanceInfo[3]."\">
                            <button class=\"btn btn-success btn-xs editBalance\" title=\"แก้ไขข้อมูลลูกค้า\"><i class=\"fa fa-edit\"></i></button>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <button class=\"btn btn-danger btn-xs removeBalance\" title=\"ลบ\"><i class=\"fa fa-ban\"></i></button>
                            <input type=\"hidden\" id=\"DelBalance-".$BalanceInfo[3]."\" value=\"".$BalanceInfo[1]."\">
                            <input type=\"hidden\" id=\"DelType-".$BalanceInfo[3]."\" value=\"".$BalanceInfo[0]."\">
                        </td>
                            </div>
                        </tr>");
                    }
                }
                else{
                    print('<tr><td colspan="6" class="text-center" style="padding:15px;"><span style="color:red;">ไม่มีรายการเคลื่อนไหวทางบัญชีประจำวันที่ '.$_REQUEST["BalanceReport"].'</span></td></tr>');
                }
                ?>
                </table>
                <input type="hidden" id="submitTo2" value="accounting_balance.php">
            </div>
        </div>
    </div>
</section>

<?php
include("footer.php");
?>