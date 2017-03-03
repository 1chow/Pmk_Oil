<?php
include("dbvars.inc.php");
if(!preg_match('/-10-/', $EmpAccess) && $UserID!=1){
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
if(isset($_REQUEST["CheckCodeNo"])){
    $CodeNoList="";
    for($i=1; $i<=3; $i++){
        $couponListArr=explode(',', $_POST['CreditCodeNo'][$i]);
        foreach($couponListArr as $key1 => $value1) {
            if(preg_match('#-#', $value1)){
                $LongList=explode('-', $value1);
                for($j=intval($LongList[0]); $j<=intval($LongList[1]); $j++){
                    $CodeNoList.=",'".$j."'";
                }
            }
            else if(trim($value1)){
                $CodeNoList.=",'".$value1."'";
            }
        }
    }

    if($CodeNoList==""){
        print('-1');
    }
    else{
        $sqlCheck="select CreditBilling from ".$db_name.".credit_billing where CodeNo in (".substr($CodeNoList, 1).");";
        $rsCheck=mysql_query($sqlCheck);
        $CheckExist=mysql_num_rows($rsCheck);
        if($CheckExist){
            print('0');
        }else{
            print('1');
        }
    }
    exit();
}
if(isset($_REQUEST["TimeForCheck"]) && (!isset($_COOKIE["TimeCheck"]) || $_COOKIE["TimeCheck"]!=$_REQUEST["TimeForCheck"])){
    setcookie("TimeCheck", $_REQUEST["TimeForCheck"], 0, "/");
    $_COOKIE["TimeCheck"]=$_REQUEST["TimeForCheck"];
    $canSubmit=1;
}
$alertTxt='';
if(isset($_POST["getCustInfo"]) && intval($_POST["getCustInfo"])){
    if(!intval($_POST["CustType"])){
        $sqlBilling="SELECT sum(if(BillingDate<=".time().", Total, 0)) as BillingTotal, sum(if(CollectSchedule<=".time().", Total, 0)) as CollectTotal, sum(Total) from ".$db_name.".billing_history where PaidDate=0 and CustID=".intval($_POST["getCustInfo"]).";";
        $rsBilling=mysql_query($sqlBilling);
        $BillingWaiting=mysql_fetch_row($rsBilling);

        $sqlUse="SELECT sum(RealUsed) from ".$db_name.".credit_billing where Status=0 and CustID=".intval($_POST["getCustInfo"])." and Confirmed=1;";
        $rsUse=mysql_query($sqlUse);
        $UseTotal=mysql_fetch_row($rsUse);
        print(floatval($BillingWaiting[0]).'**'.floatval($UseTotal[0]));
    }
    else{
        $sqlBalance="select UnofficialBalance from ".$db_name.".customer where CustID=".intval($_POST["getCustInfo"]).";";
        $rsBalance=mysql_query($sqlBalance);
        $Balance=mysql_fetch_row($rsBalance);
        print($Balance[0]);
    }
    exit();
}
else if($canSubmit && isset($_POST['CreditBill']) && intval($_POST['CreditBill'])){
    for($i=1; $i<=3; $i++){
        $couponListArr=explode(',', $_POST['CreditCodeNo'][$i]);
        foreach($couponListArr as $key1 => $value1) {
            if(preg_match('#-#', $value1)){
                $LongList=explode('-', $value1);
                for($j=intval($LongList[0]); $j<=intval($LongList[1]); $j++){
                    if(trim($_POST['CreditBooKNo'][$i])){
                        $sqlInsert="INSERT INTO ".$db_name.".credit_billing (CustID, BookNo, CodeNo) VALUES (".intval($_POST['CreditBill']).", '".mysql_real_escape_string(trim($_POST['CreditBooKNo'][$i]))."', '".mysql_real_escape_string(trim($j))."');";
                        $rsInsert=mysql_query($sqlInsert);
                    }
                }
            }
            else if(trim($_POST['CreditBooKNo'][$i])){
                $sqlInsert="INSERT INTO ".$db_name.".credit_billing (CustID, BookNo, CodeNo) VALUES (".intval($_POST['CreditBill']).", '".mysql_real_escape_string(trim($_POST['CreditBooKNo'][$i]))."', '".mysql_real_escape_string(trim($value1))."');";
                $rsInsert=mysql_query($sqlInsert);
            }
        }
    }
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}

include("header.php");
if(!isset($_REQUEST["CustType"])){
    $_REQUEST["CustType"]=0;
}
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>จ่ายใบสั่งน้ำมันให้ลูกค้า<?php if(intval($_REQUEST["CustType"])){ print('เครดิตเงินสด'); }else{ print('เครดิต'); } ?></h2>
        </div>

        <div class="content-center">

            <div class="tab-content">
                <div class="panel panel-default">
                    <div class="panel-body center">
                        <form id="submitForm" name="credit_billing" method="post" class="form-horizontal" role="form">
                            <input type="hidden" name="TimeForCheck" value="<?php print(time()); ?>">
                            <input type="hidden" name="CustType" id="CustType" value="<?php print($_REQUEST["CustType"]); ?>">
                            <?php print($alertTxt); ?>
                            <h3 class="panel-title" style="margin: 10px 0;">ชื่อบริษัท: <select class="form-control" name="CreditBill" id="CreditBill" style="width:300px; display:inline-block;"><option value="0"></option>
                                <?php
                                $sqlCust="SELECT CustID, CustName from ".$db_name.".customer where FromService=0 and Deleted=0 and CreditLimit>0 order by CustName ASC;";
                                if(intval($_REQUEST["CustType"])){
                                    $sqlCust="SELECT CustID, CustName from ".$db_name.".customer where Deleted=0 and FromService=".intval($_REQUEST["CustType"])." and CustID>0 order by CustName ASC;";
                                }
                                $rsCust=mysql_query($sqlCust);
                                while($CustNum=mysql_fetch_row($rsCust)){
                                    print("<option value=\"".$CustNum[0]."\">".$CustNum[1]."</option>");
                                }
                                ?>
                            </select></h3>
                            <div id="CustomerInfo" style="padding:10px 0;"></div>
                            <table class="td_center table table-condensed table-striped table-default car_table">
                                <thead>
                                    <tr>
                                        <th nowrap>รายการที่</th>
                                        <th>เล่มที่</th>
                                        <th>เลขที่</th>
                                    </tr>
                                </thead>
                                <?php
                                $SaveButton='';
                                for($i=1; $i<=3; $i++){
                                    print('
                                    <tr>
                                        <td>'.$i.'</td>
                                        <td><input type="text" name="CreditBooKNo['.$i.']" class="form-control" value=""></td>
                                        <td><input type="text" name="CreditCodeNo['.$i.']" class="form-control" value=""></td>
                                    </tr>');
                                    $SaveButton='<button type="button" class="btn btn-success btn-rounder" onclick="javascript:check_credit();">บันทึกข้อมูล</button>';
                                }
                                ?>
                            </table>
                            <br>
                            <div id="0" class="actionBar right">
                                <input type="hidden" name="from" value="customer">
                                <input type="hidden" id="backPage" value="customer.php">
                                <?php print($SaveButton); ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                                &nbsp;&nbsp;&nbsp;
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