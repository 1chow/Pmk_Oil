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


$alertTxt="";
if(isset($_POST['updateID']) && intval($_POST["updateID"])){ // แก้ไขวันวางบิล/วันเรียกชำระเงิน
    $DateCut = explode("/", trim($_POST["Date2Set"]));
    $DateSelected = mktime(0, 0, 0, $DateCut[1], $DateCut[0], $DateCut[2]);
    if($_POST['updateSchedule']=='billing'){
        $sqlUpdate="UPDATE ".$db_name.".billing_history set BillingDate=".intval($DateSelected)." where ID=".intval($_POST['updateID']).";";
    }
    else{
        $sqlUpdate="UPDATE ".$db_name.".billing_history set CollectSchedule=".intval($DateSelected)." where ID=".intval($_POST['updateID']).";";
    }
    $rsUpdate=mysql_query($sqlUpdate);
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}
else if(isset($_POST["LockCust"]) && trim($_POST["LockCust"])){
    $sqlDelete="Update ".$db_name.".customer SET customer.CreditLock=".intval($_POST["Locked"])." Where CustID=".intval($_POST["LockCust"]).";";
    $rsDelete=mysql_query($sqlDelete);

    $sqlCust="SELECT CustName from ".$db_name.".customer Where CustID=".intval($_POST["LockCust"]).";";
    $rsCust=mysql_query($sqlCust);
    $CustInfo=mysql_fetch_row($rsCust);
    $alertTxt='<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
    if(intval($_POST["Locked"])){ $alertTxt.='ล็อค'; }
    else{ $alertTxt.='ปลดล็อค'; }
    $alertTxt.='ข้อมูล '.$CustInfo[0].' เรียบร้อยแล้ว.</div>';
    unset($_POST["UpdateItem"]);
}
else if(isset($_POST["DeleteItem"]) && intval($_POST["DeleteItem"])){ // delete employer
    $sqlDelete="Update ".$db_name.".customer SET customer.Deleted=1 Where CustID=".intval($_POST["DeleteItem"]).";";
    $rsDelete=mysql_query($sqlDelete);

    $sqlDelete="Update ".$db_name.".customer_car SET customer_car.Deleted=1 Where CustomerID=".intval($_POST["DeleteItem"]).";";
    $rsDelete=mysql_query($sqlDelete);

    // delete credit billing
    // $sqlDelete="DELETE FROM ".$db_name.".credit_billing WHERE credit_billing.CustID=".intval($_POST["DeleteItem"])." and RealUsed=0;";
    // $rsDelete=mysql_query($sqlDelete);
    exit();
}
else if(isset($_POST["CustName"]) && trim($_POST["CustName"])){
    if($_POST["BranchCode"]=='สำนักงานใหญ่'){
        $BranchCode=$_POST["BranchCode"];
    }
    else if($_POST["BranchCode"]=='ไม่ระบุ'){
        $BranchCode="ไม่ระบุ";
    }
    else{
        $BranchCode=$_POST["BranchCodeNo"];
    }
    $_POST["UnofficialBalance"]='0.00';
    if(isset($_POST["CreditLimit"])){
        $_POST["CreditLimit"]=preg_replace("/,/", "", $_POST["CreditLimit"]);
        //$_POST["UnofficialBalance"]=preg_replace("/,/", "", $_POST["UnofficialBalance"]);
        if(isset($_POST['PayCondition'])){
            $_POST["CreditTerm"]=$_POST['PayCondition'];
            if(intval($_POST['PayCondition'])==1){ // ทุกๆวันที่
                $_POST["SpecialTerm"]=$_POST["WarningDate"];
            }
            else if(intval($_POST['PayCondition'])==2){ // วันในสัปดาห์
                $_POST["SpecialTerm"]="";
                foreach ($_POST["BillingDay"] as $key => $value) {
                    if(trim($_POST["SpecialTerm"])){
                        $_POST["SpecialTerm"].=",";
                    }
                    $_POST["SpecialTerm"].=$value;
                }
            }
            else  if(intval($_POST['PayCondition'])==4){ // ไม่ระบุ
                $_POST["SpecialTerm"]="";
            }
            else{ // เมื่อเครดิตคงเหลือน้อยกว่า
                $_POST["SpecialTerm"]=preg_replace("/,/", "", $_POST["WarningCredit"]);
            }
        }
        else{
            $_POST["CreditTerm"]=0;
            $_POST["SpecialTerm"]='';
        }
    }
    if(intval($_POST["UpdateCust"])>0){
        $sqlUpdate="UPDATE ".$db_name.".customer SET CustName='".mysql_real_escape_string(trim($_POST["CustName"]))."', Address1='".mysql_real_escape_string(trim($_POST["Address1"]))."', Address2='".mysql_real_escape_string(trim($_POST["Address2"]))."', Address3='".mysql_real_escape_string(trim($_POST["Address3"]))."', Address4='".mysql_real_escape_string(trim($_POST["Address4"]))."', Tel='".mysql_real_escape_string(trim($_POST["Tel"]))."', TaxCode='".mysql_real_escape_string(trim($_POST["TaxCode"]))."', BranchCode='".mysql_real_escape_string(trim($BranchCode))."', CheckCarCode=".intval($_POST["CheckCarCode"]);
        if(isset($_POST["CreditLimit"])){
            $sqlUpdate.=", CreditLimit='".floatval($_POST["CreditLimit"])."', CreditTerm='".mysql_real_escape_string(trim($_POST["CreditTerm"]))."', SpecialTerm='".mysql_real_escape_string(trim($_POST["SpecialTerm"]))."', DayBeforePay='".mysql_real_escape_string(trim($_POST["DayBeforePay"]))."', UnofficialBalance='".floatval($_POST["UnofficialBalance"])."'";
        }
        if(isset($_POST["CouponBalance"])){
            $sqlUpdate.=", CouponBalance='".round($_POST["CouponBalance"], 2)."'";
        }
        $sqlUpdate.=" where CustID=".intval($_POST["UpdateCust"]).";";
        $rsUpdate=mysql_query($sqlUpdate);
        $ShowMSG=0;
    }
    else{
        // check cust name
        $sqlCheck="select CustID from ".$db_name.".customer where CustName='".mysql_real_escape_string(trim($_POST["CustName"]))."';";
        $rsCheck=mysql_query($sqlCheck);
        $Check=mysql_fetch_row($rsCheck);
        if($Check[0]){
            $_POST["CustName"]=$_POST["CustName"].' (2)';
        }

        $sqlInsert="INSERT INTO ".$db_name.".customer (CustName, Address1, Address2, Address3, Address4, Tel, TaxCode, BranchCode, CheckCarCode, CreditLock, CreditLimit, CreditTerm, SpecialTerm, DayBeforePay, UnofficialBalance, FromService) VALUES ('".mysql_real_escape_string(trim($_POST["CustName"]))."', '".mysql_real_escape_string(trim($_POST["Address1"]))."', '".mysql_real_escape_string(trim($_POST["Address2"]))."', '".mysql_real_escape_string(trim($_POST["Address3"]))."', '".mysql_real_escape_string(trim($_POST["Address4"]))."', '".mysql_real_escape_string(trim($_POST["Tel"]))."', '".mysql_real_escape_string(trim($_POST["TaxCode"]))."', '".mysql_real_escape_string(trim($BranchCode))."', ".intval($_POST["CheckCarCode"]).", 0, '".floatval($_POST["CreditLimit"])."', '".mysql_real_escape_string(trim($_POST["CreditTerm"]))."', '".mysql_real_escape_string(trim($_POST["SpecialTerm"]))."', '".mysql_real_escape_string(trim($_POST["DayBeforePay"]))."', '".floatval($_POST["UnofficialBalance"])."', 0);";
        $rsInsert=mysql_query($sqlInsert);
        $ShowMSG=1;
    }
    if(isset($_POST['backPage']) && trim($_POST['backPage'])){
        if(preg_match('#\?#', $_POST['backPage'])){
            $MoreVar="&ShowMSG=".$ShowMSG;
        }
        else{
            $MoreVar="?ShowMSG=".$ShowMSG;
        }
        header("location:".$_POST['backPage'].$MoreVar);
    }
}
if(isset($_REQUEST['ShowMSG'])){
    $AlertArr = array('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>แก้ไขข้อมูลลูกค้าเรียบร้อยแล้ว.</div>', '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>เพิ่มข้อมูลลูกค้าเรียบร้อยแล้ว.</div>');
    $alertTxt=$AlertArr[$_REQUEST['ShowMSG']];
}


include("header.php");
if(isset($_POST["UpdateItem"]) && intval($_POST["UpdateItem"])){
    $DayArr = array(1 => 'วันจันทร์', 2 => 'วันอังคาร', 3 => 'วันพุธ', 4 => 'วันพฤหัสบดี', 5 => 'วันศุกร์', 6 => 'วันเสาร์', 7 => 'วันอาทิตย์');
    if(intval($_POST["UpdateItem"])<0){
        $CustCond="CustID=0";
    }
    else{
        $CustCond="CustID=".intval($_POST["UpdateItem"]);
    }
    $sqlCust="SELECT CustName, Address1, Address2, Tel, TaxCode, BranchCode, CreditLimit, CreditTerm, SpecialTerm, DayBeforePay, UnofficialBalance, CheckCarCode, Address3, Address4, FromService, CouponBalance from ".$db_name.".customer where ".$CustCond." order by CustName ASC;";
    $rsCust=mysql_query($sqlCust);
    $CustInfo=mysql_fetch_row($rsCust);
    if($CustInfo[0] && $CustInfo[5]=='ไม่ระบุ'){
        $HeadOffice="";
        $BranchCodeNo="";
        $BranchCodeNoTxt="";
        $unknown=" checked";
    }
    else if(trim($CustInfo[5]) && $CustInfo[5]!='สำนักงานใหญ่'){
        $HeadOffice="";
        $BranchCodeNo=" checked";
        $BranchCodeNoTxt=$CustInfo[5];
        $unknown="";
    }
    else{
        $HeadOffice=" checked";
        $BranchCodeNo="";
        $BranchCodeNoTxt="";
        $unknown="";
    }
    if(!isset($_REQUEST["CouponPage"])){
        $_REQUEST["CouponPage"]=0;
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ทะเบียนลูกค้า</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;"><?php if($_POST["UpdateItem"]<0){ print("เพิ่มข้อมูลลูกค้าเครดิต"); }else{ print("แก้ไขข้อมูลลูกค้า"); } ?></h3>
                </div>

                <div class="panel-body">
                    <form action="customer.php" name="customerForm" method="post" class="form-horizontal" role="form" autocomplete="off" onsubmit="javascript:return checkCustCredit();">
                        <input type="hidden" name="UpdateCust" value="<?php print($_REQUEST["UpdateItem"]); ?>">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">ชื่อบริษัท / ชื่อลูกค้า:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="CustName" placeholder="ชื่อบริษัท/ชื่อลูกค้า" value="<?php print($CustInfo[0]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">ที่อยู่:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="Address1" value="<?php print($CustInfo[1]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="Address2" value="<?php print($CustInfo[2]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="Address3" value="<?php print($CustInfo[12]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="Address4" value="<?php print($CustInfo[13]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">เบอร์โทร:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="Tel" value="<?php print($CustInfo[3]); ?>">
                            </div>
                        </div>

                        <?php
                        if(intval($_REQUEST["CouponPage"]) && $PermissionNo>=2){
                            print('
                            <div class="form-group">
                                <label class="col-sm-3 control-label">คูปองเกินยอดน้ำมันที่เติม:</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control inline_input" name="CouponBalance" value="'.$CustInfo[15].'" style="width:150px;"> บาท
                                </div>
                            </div>');
                        }
                        ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Tax ID:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="TaxCode" value="<?php print($CustInfo[4]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3 control-label"></div>
                            <div class="col-sm-3">
                                <input type="radio" class="form-control" name="BranchCode" value="สำนักงานใหญ่"<?php print($HeadOffice); ?>> สำนักงานใหญ่
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3 control-label"></div>
                            <div class="col-sm-5">
                                <input type="radio" class="form-control" name="BranchCode" value="0"<?php print($BranchCodeNo); ?>> สาขาลำดับที่ &nbsp;<input type="text" class="form-control inline_input" name="BranchCodeNo" value="<?php print($BranchCodeNoTxt); ?>" style="width:70px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3 control-label"></div>
                            <div class="col-sm-3">
                                <input type="radio" class="form-control" name="BranchCode" value="ไม่ระบุ"<?php print($unknown); ?>> ไม่ระบุ
                            </div>
                        </div>
                        <?php
                        if($_POST["UpdateItem"]<0 || ($CustInfo[14]==0)){
                        ?>
                        <p>&nbsp;</p>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">วงเงิน:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control price" name="CreditLimit" value="<?php print(number_format($CustInfo[6], 2)); ?>">
                            </div>
                        </div>

                        <!-- <div class="form-group">
                            <label class="col-sm-3 control-label">วงเงินคงเหลือ:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control price" name="UnofficialBalance" value="<?php print(number_format($CustInfo[10], 2)); ?>">
                            </div>
                        </div> -->

                        <div class="form-group">
                            <label class="col-sm-3 control-label">เงื่อนไขการวางบิล:</label>
                            <div class="col-sm-6" style="padding-top:2px;">
                                <input type="radio" class="form-control" name="PayCondition" value="1"<?php if($CustInfo[7]==1 || !($CustInfo[7])){print(' checked');} ?>> ทุกๆวันที่ <input type="text" class="form-control inline_input" style="width:50%;" name="WarningDate" value="<?php if($CustInfo[7]==1){ print($CustInfo[8]); } ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9" style="padding-top:2px;">
                                <p><input type="radio" class="form-control" name="PayCondition" value="2"<?php if($CustInfo[7]==2){print(' checked');} ?>> วันในสัปดาห์ <?php
                                foreach ($DayArr as $key => $value) {
                                    print("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" name=\"BillingDay[]\" value=\"".$key."\"");
                                    if($CustInfo[7]==2 && preg_match("/".$key."/", $CustInfo[8])){
                                        print(" checked");
                                    }
                                    print(">".$value);
                                }
                                ?></p>
                                <p><input type="radio" class="form-control" name="PayCondition" value="3"<?php if($CustInfo[7]==3){print(' checked');} ?>> เมื่อเครดิตคงเหลือน้อยกว่า <input type="text" class="form-control inline_input price" name="WarningCredit" style="width:100px;" value="<?php if($CustInfo[7]==3){print(number_format($CustInfo[8], 2));} ?>"> บาท</p>
                                <p><input type="radio" class="form-control" name="PayCondition" value="4"<?php if($CustInfo[7]==4){print(' checked');} ?>> ไม่ระบุ</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">กำหนดการชำระเงิน:</label>
                            <div class="col-sm-9" style="padding-top:2px;">
                                <input type="radio" class="form-control" name="DayBeforePayType" value="1"<?php if($CustInfo[9]){print(' checked');} ?>> หลังจากวันออกบิล <input type="text" class="form-control inline_input middle integer" name="DayBeforePay" value="<?php print($CustInfo[9]); ?>"> วัน
                                <p style="margin:5px 0 0;">
                                <input type="radio" class="form-control" name="DayBeforePayType" value="0"<?php if(!$CustInfo[9]){print(' checked');} ?>> ไม่ระบุ
                                </p>
                            </div>
                        </div>
                        <?php
                        }
                        ?>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">ทะเบียนรถ:</label>
                            <div class="col-sm-3" style="padding-top:7px;">
                                <input type="radio" class="form-control inline_input middle" name="CheckCarCode" value="1"<?php if($CustInfo[11] || (!$_REQUEST['UpdateItem'])){ print(" checked"); } ?>> ระบุทะเบียนรถทุกครั้งที่ใช้
                            </div>
                            <div class="col-sm-4" style="padding-top:7px;">
                                <input type="radio" class="form-control inline_input middle" name="CheckCarCode" value="0"<?php if(!$CustInfo[11]){ print(" checked"); } ?>> ใช้แบบไม่ระบุทะเบียนรถได้
                            </div>
                        </div>

                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" name="backPage" value="<?php if(isset($_REQUEST["backPage"]) && trim($_REQUEST["backPage"])){ print($_REQUEST["backPage"]); }else{ print("customer.php"); } if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                            <button type="submit" class="btn btn-success btn-rounder"><?php if(intval($_REQUEST['UpdateItem'])<0){ print('เพิ่มข้อมูล'); }else{ print('แก้ไขข้อมูล'); } ?></button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="reset" class="btn btn-danger btn-rounder"><?php if($_POST["UpdateItem"]<0){ print('ล้างข้อมูล'); }else{ print('รีเซ็ตข้อมูล'); } ?></button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php
}
else{
    $ItemPerPage=30;
    if(!isset($_REQUEST['page']) || !$_REQUEST['page']){
        $_REQUEST['page']=1;
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ทะเบียนลูกค้า</h2>
        </div>

        <div class="content-center">
            <div class="tab-content">
                <div class="tab-pane fade in active" id="tab1">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- TABLE OPTION -->
                            <div role="toolbar" class="btn-toolbar padding-bootom">
                                <div id="-1" class="btn-group">
                                    <button class="btn btn-success editItem" type="button"><i class="fa fa-plus"></i> &nbsp;เพิ่มลูกค้าเครดิต</button>
                                </div>
                                <div style="margin:0 0 0 20px;" class="btn-group">
                                    <button class="btn btn-info" type="button" onclick="location.href='credit-billing.php';"><i class="fa fa-money"></i> &nbsp;จ่ายใบสั่งน้ำมัน</button>
                                </div>
                                <div style="margin:0 20px;" class="btn-group">
                                    <button class="btn btn-primary" type="button" onclick="location.href='manage-billing.php?from=customer';"><i class="fa fa-money"></i> &nbsp;บันทึกการวางบิล/ชำระเงิน</button>
                                </div>
                                <div class="btn-group">
                                    <button class="btn" type="button" onclick="location.href='manage-billing.php?report=1';"><i class="fa fa-money"></i> &nbsp;ประวัติการชำระเงิน</button>
                                </div>
                                <div class="btn-group pull-right">
                                    <form method="post" id="submitForm" role="form">
                                        <input type="hidden" id="backPage" name="backPage" value="customer.php">
                                        <input type="hidden" id="UpdateItem" name="UpdateItem" value="0">
                                        <input type="hidden" id="CouponPage" name="CouponPage" value="0">
                                        <input type="hidden" id="LockCust" name="LockCust" value="0">
                                        <input type="hidden" id="Locked" name="Locked" value="0">
                                        <input type="hidden" id="PageNo" name="PageNo" value="<?php print($_REQUEST['page']); ?>">
                                        <input type="text" name="searchCust" value="<?php if(isset($_POST['searchCust'])){ print(trim($_POST['searchCust'])); } ?>" placeholder="Search..." class="form-control">
                                    </form>
                                </div>
                            </div>

                            <br>
                            <?php print($alertTxt); ?>
                            <br>
                            <!-- TABLE OPTION -->
                            <div class="table-responsive">
                                <input type="hidden" id="submitTo" value="customer.php">
                                <table class="td_center table table-condensed table-striped table-default table_border">
                                    <thead>
                                        <tr>
                                            <th style="min-width:150px;">ชื่อ</th>
                                            <?php if($showAllCust){ print('<th>คูปอง</th>'); } ?>
                                            <th>วงเงิน</th>
                                            <th>ใช้ไป</th>
                                            <th>คงเหลือ</th>
                                            <th>วางบิล</th>
                                            <th>วันวางบิล</th>
                                            <th>วันนัดชำระเงิน</th>
                                            <th style="width:170px;">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $DeleteCust="";
                                    $LockCust="";
                                    $sqlCust="SELECT CustID, CustName, Tel, DayBeforePay, UnofficialBalance, CreditLimit, CreditLock, CreditTerm, SpecialTerm from ".$db_name.".customer left join ".$db_name.".customer_car on CustomerID=CustID where customer.Deleted=0 and CustID>0 and FromService=0 and CreditLimit>0";
                                    if(isset($_POST['searchCust']) && trim($_POST['searchCust'])){
                                        $sqlCust.=" and ((CustName like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%') or (Tel like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%') or (customer_car.CarCode like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%' and CarID!=(-1) and customer_car.Deleted=0))";
                                    }
                                    $rsCust=mysql_query($sqlCust." group by CustID;");
                                    //echo $sqlCust;
                                    $CustNum=mysql_num_rows($rsCust);
                                    $sqlCust.=" group by CustID order by CustName ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                                    $rsCust=mysql_query($sqlCust);
                                    while($CustInfo=mysql_fetch_row($rsCust)){
                                        $setColor="";
                                        $setColor1=' class="text-left"';
                                        $setColor2=' class="text-right"';
                                        $CollectText="";
                                        $BillingDateTxt="";
                                        $CollectDateTxt="";
                                        $sqlUse="SELECT sum(RealUsed) from ".$db_name.".credit_billing where Status=0 and CustID=".$CustInfo[0]." and Confirmed=1;";
                                        $rsUse=mysql_query($sqlUse);
                                        $UseTotal=mysql_fetch_row($rsUse);
                                        // get total สำหรับวางบิลในแต่ละรอบ
                                        $sql4Collect="SELECT Total, billing_history.ID, BillingDate, CollectSchedule from (".$db_name.".customer inner join ".$db_name.".billing_history on billing_history.CustID=customer.CustID) where customer.CustID=".$CustInfo[0]." and billing_history.PaidDate=0 order by BillingDate ASC;";
                                        $rs4Collect=mysql_query($sql4Collect);
                                        while($Total4colect=mysql_fetch_row($rs4Collect)){
                                            $CollectText.='<p style="margin:0 0;"><a href="payment_history.php?CustID='.$CustInfo[0].'&History='.$Total4colect[1].'&page='.$_REQUEST['page'].'" style="color:#694c96;" title="ดูรายละเอียด">'.number_format($Total4colect[0], 2).'</a></p>';
                                            $BillingDateTxt.='<p style="margin:0 0;"><a href="void(0);" onclick="setValue(\'billing\', '.$Total4colect[1].', \''.date('d/m/Y', $Total4colect[2]).'\');" data-toggle="modal" data-target="#myModal">'.date('d-m-Y', $Total4colect[2]).'</a></p>';
                                            $CollectDateTxt.='<p style="margin:0 0;"><a href="void(0);" onclick="setValue(\'collect\', '.$Total4colect[1].', \''.date('d/m/Y', $Total4colect[3]).'\');" data-toggle="modal" data-target="#myModal">'.date('d-m-Y', $Total4colect[3]).'</a></p>';

                                            if($Total4colect[2] <= time()){ // เลยวันวางบิลมาแล้ว
                                                $setColor=' class="billing_now"';
                                                $setColor1=' class="text-left billing_now"';
                                                $setColor2=' class="text-right billing_now"';
                                            }
                                            if($Total4colect[3] <= time()){ // เลยวันชำระเงินมาแล้ว
                                                $setColor=' class="collect_now"';
                                                $setColor1=' class="text-left billing_now"';
                                                $setColor2=' class="text-right billing_now"';
                                            }
                                            //$UseTotal[0]=0;
                                        }


                                        if($PermissionNo==3){
                                            $DeleteCust='&nbsp;&nbsp;&nbsp;&nbsp;<button class="btn btn-danger btn-xs removeItem" id="'.$CustInfo[1].'" title="ลบ"><i class="fa fa-ban"></i></button>&nbsp;&nbsp;&nbsp;&nbsp;';
                                            if($CustInfo[6]){
                                                $LockCust='<button class="btn btn-primary btn-xs UnlockCust" onclick="javascript:LockCust('.$CustInfo[0].', 0, \''.$CustInfo[1].'\');" title="ปลดล็อคเครดิต">&nbsp;<i class="fa fa-unlock"></i></button>';
                                            }
                                            else{
                                                $LockCust='<button class="btn btn-inverse btn-xs LockCust" onclick="javascript:LockCust('.$CustInfo[0].', 1, \''.$CustInfo[1].'\');" title="ล็อคเครดิต">&nbsp;<i class="fa fa-lock"></i>&nbsp;</button>';
                                            }
                                        }
                                        $CouponText='';
                                        if($showAllCust){
                                            $sqlCoupon="SELECT sum(if(Status=1, Price, 0)) as TotalActive from ".$db_name.".coupon where CustomerID=".intval($CustInfo[0])." group by CustomerID;";
                                            $rsCoupon=mysql_query($sqlCoupon);
                                            $Coupon=mysql_fetch_row($rsCoupon);
                                            $CouponText='<td'.$setColor2.'>'.number_format($Coupon[0], 2).'</td>';
                                        }
                                        if($UseTotal[0]>0){
                                            $UsedOil='<a href="credit_history.php?CustID='.$CustInfo[0].'&page='.$_REQUEST['page'].'">'.number_format($UseTotal[0], 2).'</a>';
                                        }else{
                                            $UsedOil=number_format($UseTotal[0], 2);
                                        }
                                        print('
                                    <tr id="item-'.$CustInfo[0].'">
                                        <td'.$setColor1.'>&nbsp;
                                            <span class="cust_name-link editCust" title="แก้ไขข้อมูลของ '.$CustInfo[1].'">'.$CustInfo[1].'</span>
                                        </td>
                                        '.$CouponText.'
                                        <td'.$setColor2.'>'.number_format($CustInfo[5], 2).'</td>
                                        <td'.$setColor2.'>'.$UsedOil.'</td>
                                        <td'.$setColor2.'>'.number_format(($CustInfo[5]-$UseTotal[0]), 2).'</td>
                                        <td'.$setColor2.'>'.$CollectText.'</td>
                                        <td'.$setColor.'>'.$BillingDateTxt.'</td>
                                        <td'.$setColor.'>'.$CollectDateTxt.'</td>
                                        <td'.$setColor.' class="text-left">
                                            <div id="'.$CustInfo[0].'" class="text-left">&nbsp;
                                                <button class="btn btn-success btn-xs editCust" title="แก้ไขข้อมูลลูกค้า"><i class="fa fa-edit"></i></button>
                                                &nbsp;&nbsp;
                                                <button class="btn btn-info btn-xs CustomerCar" title="แก้ไขข้อมูลรถ"><i class="fa fa-truck"></i></button>'.$DeleteCust.$LockCust.'&nbsp;&nbsp;
                                            </div>
                                        </td>
                                    </tr>');
                                    }
                                    if(!$CustNum){ // ค้นหาไม่เจอ
                                        if(!$showAllCust){ $col=8; }else{ $col=9; }
                                        $moreText='ในระบบ';
                                        if(isset($_POST['searchCust']) && trim($_POST['searchCust'])){
                                            $moreText='ของ '.$_POST['searchCust'];
                                        }
                                        print('<tr><td style="background-color:#fff; padding:20px; color:red;" colspan="'.$col.'">ไม่พบข้อมูล'.$moreText.'</td></tr>');
                                    }
                                    ?>
                                </table>
                            </div>

                            <?php
                            if($CustNum > $ItemPerPage){
                                $AllPage=ceil($CustNum/$ItemPerPage);
                                print("<br>");
                                if($_REQUEST['page']!=1){
                                    print('<a href="customer.php?page='.($_REQUEST['page']-1).'" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                                }
                                print("<select onchange=\"javascript:location.href='customer.php?page='+this.value;\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
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
                                    print('<a href="customer.php?page='.($_REQUEST['page']+1).'" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                                }
                            }
                            ?>

                            <!-- FULL FUNCTION TABLE -->

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form action="customer.php" method="post" class="form-horizontal" onsubmit="return checkBillingDate();">
                <input type="hidden" name="updateSchedule" id="updateSchedule" value="">
                <input type="hidden" name="updateID" id="updateID" value="">
                <input type="hidden" name="oldDate" id="oldDate" value="">
                <input type="hidden" name="oldDateTxt" id="oldDateTxt" value="<?php print(date('d/m/Y', time())); ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">เปลี่ยนวันวางบิล</h4>
                </div>
                <div class="modal-body text-center">
                    <b>วันที่:</b> <input type="text" class="form-control inline_input Calendar" name="Date2Set" id="Date2Set" value="<?php print(date('d/m/Y', time())); ?>" style="width:120px;">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">ตกลง</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
                </div>
            </form>
        </div>
      </div>
    </div>
<?php
}
include("footer.php");
?>