<?php
include("dbvars.inc.php");
if(!preg_match('/-9-/', $EmpAccess) && $UserID!=1){
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
if(isset($_POST["AddCash4Cust"]) && intval($_POST["AddCash4Cust"])){
    $SetDate=explode("/", $_REQUEST['cashPayDate']);
    $setDateAdd=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    $_POST["cashPayAmount"]=preg_replace("/,/", "", $_POST["cashPayAmount"]);
    $sqlInsert="INSERT INTO ".$db_name.".cash_credit (CustomerID, PaidType, PaidDate, PaidAmount, Note) VALUES (".intval($_POST["AddCash4Cust"]).", '".mysql_real_escape_string(trim($_POST["AddType"]))."', '".$setDateAdd."', '".floatval($_POST["cashPayAmount"])."', '".mysql_real_escape_string(trim($_POST["CashAddNote"]))."');";
    $rsInsert=mysql_query($sqlInsert);
    $sqlUpdate="Update ".$db_name.".customer SET customer.UnofficialBalance=(UnofficialBalance+".floatval($_POST["cashPayAmount"]).") Where CustID=".intval($_POST["AddCash4Cust"]).";";
    $rsUpdate=mysql_query($sqlUpdate);
    $sqlUpdate="Update ".$db_name.".cash_credit_warning set HideNotice=0 where CustomerID=".intval($_POST["AddCash4Cust"]).";";
    $rsUpdate=mysql_query($sqlUpdate);
    header("location:cash-customer.php?AddCash=1");
}
else if(isset($_POST["DeleteItem"]) && intval($_POST["DeleteItem"])){ // delete employerr
    $sqlDelete="Update ".$db_name.".customer SET customer.Deleted=1 Where CustID=".intval($_POST["DeleteItem"]).";";
    $rsDelete=mysql_query($sqlDelete);

    $sqlDelete="Update ".$db_name.".customer_car SET customer_car.Deleted=1 Where CustomerID=".intval($_POST["DeleteItem"]).";";
    $rsDelete=mysql_query($sqlDelete);
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
    $_POST["BalanceWarning"]=preg_replace("/,/", "", $_POST["BalanceWarning"]);
    if(intval($_POST["UpdateCust"])>0){
        $sqlUpdate="UPDATE ".$db_name.".customer SET CustName='".mysql_real_escape_string(trim($_POST["CustName"]))."', Address1='".mysql_real_escape_string(trim($_POST["Address1"]))."', Address2='".mysql_real_escape_string(trim($_POST["Address2"]))."', Address3='".mysql_real_escape_string(trim($_POST["Address3"]))."', Address4='".mysql_real_escape_string(trim($_POST["Address4"]))."', Tel='".mysql_real_escape_string(trim($_POST["Tel"]))."', TaxCode='".mysql_real_escape_string(trim($_POST["TaxCode"]))."', BranchCode='".mysql_real_escape_string(trim($BranchCode))."', CheckCarCode=0, SpecialTerm='".mysql_real_escape_string(trim($_POST["BalanceWarning"]))."' where CustID=".intval($_POST["UpdateCust"]).";";
        $rsUpdate=mysql_query($sqlUpdate);
        $UpdateCustID=$_POST["UpdateCust"];
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

        $sqlInsert="INSERT INTO ".$db_name.".customer (CustName, Address1, Address2, Address3, Address4, Tel, TaxCode, BranchCode, CheckCarCode, CreditLock, CreditLimit, CreditTerm, SpecialTerm, DayBeforePay, UnofficialBalance, FromService) VALUES ('".mysql_real_escape_string(trim($_POST["CustName"]))."', '".mysql_real_escape_string(trim($_POST["Address1"]))."', '".mysql_real_escape_string(trim($_POST["Address2"]))."', '".mysql_real_escape_string(trim($_POST["Address3"]))."', '".mysql_real_escape_string(trim($_POST["Address4"]))."', '".mysql_real_escape_string(trim($_POST["Tel"]))."', '".mysql_real_escape_string(trim($_POST["TaxCode"]))."', '".mysql_real_escape_string(trim($BranchCode))."', 0, 0, '0.00', 0, '".mysql_real_escape_string(trim($_POST["BalanceWarning"]))."', 0, '0.00', 3);";
        $rsInsert=mysql_query($sqlInsert);
        $UpdateCustID=mysql_insert_id($Conn);
        $sqlUpdate="INSERT INTO ".$db_name.".cash_credit_warning (CustomerID, HideNotice) VALUES (".intval($UpdateCustID).", 0);";
        $rsUpdate=mysql_query($sqlUpdate);
        $ShowMSG=1;
    }
    if(isset($_POST["UpdateCar"])){
        foreach ($_POST["UpdateCar"] as $key => $value) {
            if(isset($_POST["delete"][$key])){
                $sqlDelete="UPDATE ".$db_name.".customer_car set Deleted=1 where CarID=".intval($key).";";
                $rsDelete=mysql_query($sqlDelete);
            }
            else{
                $sqlDelete="UPDATE ".$db_name.".customer_car set CarCode='".mysql_real_escape_string(trim($value))."', CarType='".mysql_real_escape_string(trim($_POST['CarType'][$key]))."', CarBrand='".mysql_real_escape_string(trim($_POST['CarBrand'][$key]))."', Model='".mysql_real_escape_string(trim($_POST['CarModel'][$key]))."', Color='".mysql_real_escape_string(trim($_POST['CarColor'][$key]))."' where CarID=".intval($key).";";
                $rsDelete=mysql_query($sqlDelete);
            }
        }
    }
    if(isset($_POST["NewCar"])){
        foreach ($_POST["NewCar"] as $key => $value) {
            $sql="select CarID from ".$db_name.".customer_car where CarCode='".mysql_real_escape_string(trim($_POST["NewCar"][$key]))."' and Deleted=0;";
            $rsCar=mysql_query($sql);
            if(trim($_POST["NewCar"][$key]) && !mysql_num_rows($rsCar) && !preg_match("#ใส่ถัง#", $_POST["NewCar"][$key])){
                $sqlCustomerCar="INSERT INTO ".$db_name.".customer_car (CustomerID, CarCode, CarType, CarBrand, Model, Color) VALUES (".intval($UpdateCustID).", '".mysql_real_escape_string(trim($_POST["NewCar"][$key]))."', '".mysql_real_escape_string(trim($_POST["NewCarType"][$key]))."', '".mysql_real_escape_string(trim($_POST["NewCarBrand"][$key]))."', '".mysql_real_escape_string(trim($_POST["NewCarModel"][$key]))."', '".mysql_real_escape_string(trim($_POST["NewCarColor"][$key]))."');";
                $rsCustomerCar=mysql_query($sqlCustomerCar);
            }
        }
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
    $AlertArr = array('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>แก้ไขข้อมูลลูกค้าเรียบร้อยแล้ว.</div>', '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>เพิ่มข้อมูลลูกค้าเครดิตเงินสดเรียบร้อยแล้ว.</div>');
    $alertTxt=$AlertArr[$_REQUEST['ShowMSG']];
}
else if(isset($_POST["AddCash"])){
    $alertTxt = '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>เพิ่มเงินในวงเงินลูกค้าเรียบร้อยแล้ว</div>';
}

include("header.php");
if(!isset($_REQUEST['mainNo'])){
    $_REQUEST['mainNo']=0;
}
if(!isset($_REQUEST['page']) || !intval($_REQUEST['page'])){
    $_REQUEST['page']=1;
}

$BrandList = array();
$sqlCarBrand="select ID, Brand from ".$db_name.".car_brand where Deleted=0;";
$rsCarBrand=mysql_query($sqlCarBrand);
while($CarBrand=mysql_fetch_row($rsCarBrand)){
    $BrandList[$CarBrand[0]] = $CarBrand[1];
}
$BrandList[-1]='อื่นๆ';
asort($BrandList);

$CarTypeArr = array('เก๋ง', 'กระบะ', 'รถใหญ่', 'แท็กซี่', 'มอเตอร์ไซค์', 'อื่นๆ');

if(isset($_POST["UpdateItem"]) && intval($_POST["UpdateItem"])){
    $DayArr = array(1 => 'วันจันทร์', 2 => 'วันอังคาร', 3 => 'วันพุธ', 4 => 'วันพฤหัสบดี', 5 => 'วันศุกร์', 6 => 'วันเสาร์', 7 => 'วันอาทิตย์');
    if(intval($_POST["UpdateItem"])<0){
        $CustCond="CustID=0";
    }
    else{
        $CustCond="CustID=".intval($_POST["UpdateItem"]);
    }
    $sqlCust="SELECT CustName, Address1, Address2, Tel, TaxCode, BranchCode, Address3, Address4, SpecialTerm from ".$db_name.".customer where ".$CustCond." order by CustName ASC;";
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

?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ทะเบียนลูกค้าเครดิตเงินสด</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;"><?php if($_POST["UpdateItem"]<0){ print("เพิ่มข้อมูลลูกค้าเครดิตเงินสด"); }else{ print("แก้ไขข้อมูลลูกค้าเครดิตเงินสด"); } ?></h3>
                </div>

                <div class="panel-body">
                    <form action="cash-customer.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <input type="hidden" name="mainNo" id="mainNo" value="<?php print($_REQUEST['mainNo']); ?>">
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
                                <input type="text" class="form-control" name="Address3" value="<?php print($CustInfo[6]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="Address4" value="<?php print($CustInfo[7]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">เบอร์โทร:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="Tel" value="<?php print($CustInfo[3]); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">เตือนเพิ่มวงเงิน:</label>
                            <div class="col-sm-9">
                                เมื่อจำนวนวงเงินเหลือน้อยกว่า <input type="text" class="form-control price inline_input" style="width:100px;" name="BalanceWarning" value="<?php print(number_format($CustInfo[8], 2)); ?>"> บาท
                            </div>
                        </div>

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
                        <BR>
                        <table class="td_center table table-condensed table-striped table-default car_table">
                            <thead>
                                <tr>
                                    <th nowrap>ลำดับที่</th>
                                    <th>ทะเบียนรถ</th>
                                    <th>ประเภท</th>
                                    <th>ยี่ห้อ</th>
                                    <th>รุ่น</th>
                                    <th>สี</th>
                                    <?php if($PermissionNo >= 2){ print('<th>ลบ</th>'); } ?>
                                </tr>
                            </thead>
                            <?php
                            $count=1;
                            $sqlCarCode="SELECT CarCode, CarID, CarType, CarBrand, Model, Color from ".$db_name.".customer_car where customer_car.CustomerID=".intval($_REQUEST["UpdateItem"])." and CustomerID>0 and CarID!=(-1) and Deleted=0 order by CarCode ASC;";
                            $rsCarCode=mysql_query($sqlCarCode);
                            while($CarCode=mysql_fetch_row($rsCarCode)){
                                $BrandSelect='<select name="CarBrand['.$CarCode[1].']" class="form-control" style="width:180px">';
                                foreach($BrandList as $key => $value) {
                                    $BrandSelect.='<option value="'.$key.'"';
                                    if($key==$CarCode[3]){
                                        $BrandSelect.=' selected';
                                    }
                                    $BrandSelect.='>'.strtoupper($value).'</option>';
                                }
                                $BrandSelect.='</select>';

                                $CarType='<select name="CarType['.$CarCode[1].']" class="form-control" style="width:120px">';
                                foreach($CarTypeArr as $key => $value) {
                                    $CarType.='<option value="'.$value.'"';
                                    if($value==$CarCode[2]){
                                        $CarType.=' selected';
                                    }
                                    $CarType.='>'.$value.'</option>';
                                }
                                $CarType.='</select>';
                                print('
                                <tr>
                                    <td>'.$count.'</td>
                                    <td><input type="text" name="UpdateCar['.$CarCode[1].']" class="form-control" value="'.$CarCode[0].'"></td>
                                    <td>'.$CarType.'</td>
                                    <td>'.$BrandSelect.'</td>
                                    <td><input type="text" name="CarModel['.$CarCode[1].']" class="form-control" value="'.$CarCode[4].'"></td>
                                    <td><input type="text" name="CarColor['.$CarCode[1].']" class="form-control" value="'.$CarCode[5].'"></td>
                                    <td>
                                        &nbsp;<input type="checkbox" name="delete['.$CarCode[1].']" value="'.$CarCode[1].'">
                                    </td>
                                </tr>');
                                $count++;
                            }

                            $BrandSelect='';
                            foreach($BrandList as $key => $value) {
                                $BrandSelect.='<option value="'.$key.'">'.strtoupper($value).'</option>';
                            }
                            $CarType='';
                            foreach($CarTypeArr as $key => $value) {
                                $CarType.='<option value="'.$value.'">'.$value.'</option>';
                            }

                            $AddThree=1;
                            if($count==1){ $AddThree=3; }
                            for($k=1; $k<=$AddThree; $k++){
                                print('
                                <tr>
                                    <td>เพิ่ม</td>
                                    <td><input type="text" name="NewCar['.$k.']" class="form-control" value=""></td>
                                    <td><select name="NewCarType['.$k.']" class="form-control" style="width:120px">'.$CarType.'</select></td>
                                    <td><select name="NewCarBrand['.$k.']" class="form-control" style="width:180px">'.$BrandSelect.'</select></td>
                                    <td><input type="text" name="NewCarModel['.$k.']" class="form-control" value=""></td>
                                    <td><input type="text" name="NewCarColor['.$k.']" class="form-control" value=""></td>
                                    <td>&nbsp;</td>
                                </tr>');
                            }
                            ?>
                        </table>
                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" name="backPage" value="<?php
                            if(isset($_REQUEST["backPage"]) && trim($_REQUEST["backPage"])){
                                print($_REQUEST["backPage"]);
                            }else{
                                print("cash-customer.php");
                            }
                            print('?page='.$_REQUEST['page']);
                            if(isset($_REQUEST['mainNo'])){
                                print('&mainNo='.$_REQUEST['mainNo']);
                            }
                            ?>">
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
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ทะเบียนลูกค้าเครดิตเงินสด</h2>
        </div>

        <div class="content-center">
            <div class="tab-content">
                <div class="tab-pane fade in active" id="tab1">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- TABLE OPTION -->
                            <div role="toolbar" class="btn-toolbar padding-bootom">
                                <div id="-1" class="btn-group">
                                    <button class="btn btn-success editItem" type="button"><i class="fa fa-plus"></i> &nbsp;เพิ่มลูกค้าเครดิตเงินสด</button>
                                </div>
                                <div style="margin:0 0 0 20px;" class="btn-group">
                                    <button class="btn" type="button" data-toggle="modal" data-target="#AddCash"><i class="fa fa-plus"></i> &nbsp;เพิ่มเงินในวงเงิน</button>
                                </div>
                                <div style="margin:0 0 0 20px;" class="btn-group">
                                    <button class="btn btn-info" type="button" onclick="location.href='credit-billing.php?CustType=3';"><i class="fa fa-money"></i> &nbsp;จ่ายใบสั่งน้ำมัน</button>
                                </div>
                                <div class="btn-group pull-right">
                                    <form method="post" id="submitForm" role="form">
                                        <input type="hidden" id="backPage" name="backPage" value="<?php if(isset($_REQUEST['backPage'])){ print($_REQUEST['backPage']); }else{ print('cash-customer.php'); } ?>">
                                        <input type="hidden" id="UpdateItem" name="UpdateItem" value="0">
                                        <input type="hidden" id="CouponPage" name="CouponPage" value="0">
                                        <input type="hidden" name="mainNo" id="mainNo" value="<?php print($_REQUEST['mainNo']); ?>">
                                        <input type="hidden" id="PageNo" name="PageNo" value="<?php print($_REQUEST['page']); ?>">
                                        <input type="text" name="searchCust" value="<?php if(isset($_POST['searchCust'])){ print(trim($_POST['searchCust'])); } ?>" placeholder="Search..." class="form-control">
                                    </form>
                                </div>
                            </div>

                            <br>
                            <?php print($alertTxt); ?>

                            <!-- TABLE OPTION -->
                            <div class="table-responsive">
                                <input type="hidden" id="submitTo" value="cash-customer.php">
                                <table class="td_center table table-condensed table-striped table-default table_border">
                                    <thead>
                                        <tr>
                                            <th>ชื่อ</th>
                                            <th style="width:20%;">เบอร์โทร</th>
                                            <th style="width:30%;">ยอดเงินคงเหลือ</th>
                                            <th style="width:190px;">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $DeleteCust="";
                                    $LockCust="";
                                    $sqlCust="SELECT CustID, CustName, Tel, UnofficialBalance, SpecialTerm from ".$db_name.".customer left join ".$db_name.".customer_car on CustomerID=CustID where customer.Deleted=0 and FromService=3";
                                    if(isset($_POST['searchCust']) && trim($_POST['searchCust'])){
                                        $sqlCust.=" and (CustName like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%' or Tel like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%' or (customer_car.CarCode like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%' and CarID!=(-1) and customer_car.Deleted=0))";
                                    }
                                    $rsCust=mysql_query($sqlCust.";");
                                    $CustNum=mysql_num_rows($rsCust);
                                    $sqlCust.=" group by CustID order by CustName ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                                    $rsCust=mysql_query($sqlCust);
                                    //echo $sqlCust;
                                    while($CustInfo=mysql_fetch_row($rsCust)){
                                        if($Permission=='admin'){
                                            $DeleteCust='&nbsp;&nbsp;&nbsp;&nbsp;<button class="btn btn-danger btn-xs removeItem" id="'.$CustInfo[1].'" title="ลบ"><i class="fa fa-ban"></i></button>&nbsp;&nbsp;&nbsp;&nbsp;';
                                        }
                                        $setColor='';
                                        $setColor1=' class="text-left"';
                                        if($CustInfo[3]<=$CustInfo[4]){
                                            $setColor=' class="billing_now"';
                                            $setColor1=' class="text-left billing_now"';
                                        }
                                        print('
                                    <tr id="item-'.$CustInfo[0].'">
                                        <td'.$setColor1.'>&nbsp;
                                            <span class="cust_name-link editCust" title="แก้ไขข้อมูลของ '.$CustInfo[1].'">'.$CustInfo[1].'</span>
                                        </td>
                                        <td'.$setColor.'>
                                            '.$CustInfo[2].'
                                        </td>
                                        <td'.$setColor.'>
                                            '.number_format($CustInfo[3], 2).'
                                        </td>
                                        <td'.$setColor.'>
                                            <div id="'.$CustInfo[0].'">&nbsp;
                                                <button class="btn btn-success btn-xs editCust" title="แก้ไขข้อมูลลูกค้า"><i class="fa fa-edit"></i></button>
                                                &nbsp;&nbsp;
                                                <button class="btn btn-primary btn-xs" title="ประวัติ" onclick="location.href=\'cashcust_history.php?CustID='.$CustInfo[0].'&page='.$_REQUEST['page'].'&mainNo='.$_REQUEST['mainNo'].'\'"><i class="fa fa-clock-o"></i></button>
                                                &nbsp;&nbsp;
                                                <button class="btn btn-info btn-xs CustomerCar" title="แก้ไขข้อมูลรถ"><i class="fa fa-truck"></i></button>'.$DeleteCust.'&nbsp;&nbsp;
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
                                // prev page
                                $moreLink='';
                                if(isset($_REQUEST['mainNo'])){
                                    $moreLink='&mainNo='.$_REQUEST['mainNo'];
                                }
                                print("<br>");
                                if($_REQUEST['page']!=1){
                                    print('<a href="cash-customer.php?page='.($_REQUEST['page']-1).$moreLink.'" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                                }
                                print("<select onchange=\"javascript:location.href='cash-customer.php?page='+this.value+'".$moreLink."';\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
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
                                    print('<a href="cash-customer.php?page='.($_REQUEST['page']+1).$moreLink.'" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
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

    <div class="modal fade" id="AddCash" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form name="AddCashForm" action="cash-customer.php" method="post" class="form-horizontal" autocomplete="off">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel3">เพิ่มเงินในวงเงิน</h4>
                </div>
                <div class="modal-body">
                    <table width="100%" style="border-collapse:separate; border-spacing:10px;">
                        <tr><td width="30%" align="right"><strong>ชื่อบริษัท / ชื่อลูกค้า:</strong>&nbsp;&nbsp;</td><td><select name="AddCash4Cust" class="form-control inline_input" style="width:170px;">
                        <?php
                            $sqlCust="SELECT CustID, CustName from ".$db_name.".customer where customer.Deleted=0 and FromService=3 order by CustName ASC;";
                            $rsCust=mysql_query($sqlCust);
                            while($CustInfo=mysql_fetch_row($rsCust)){
                                print("<option value=\"".$CustInfo[0]."\">".$CustInfo[1]."</option>");
                            }
                        ?></select></td></tr>
                        <tr><td align="right">
                            <strong>วันที่เติม:</strong>&nbsp;&nbsp;</td><td><input type="text" class="form-control inline_input Calendar" name="cashPayDate" id="cashPayDate" value="" style="width:100px;">
                        </td></tr>
                        <tr><td align="right">
                            <strong>จำนวนเงิน:</strong>&nbsp;&nbsp;</td><td><input type="text" class="form-control inline_input price" name="cashPayAmount" id="cashPayAmount" value="" style="width:100px;">
                        </td></tr>
                        <tr><td align="right">
                                <strong>วิธีการชำระเงิน:</strong>&nbsp;&nbsp;
                            </td>
                            <td>
                                <input type="radio" name="AddType" value="เงินสด" checked>เงินสด &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" name="AddType" value="บัตรเครดิต">บัตรเครดิต &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" name="AddType" value="เช็ค">เช็ค &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </td></tr>
                        <tr><td align="right">
                            <strong>หมายเหตุ:</strong>&nbsp;&nbsp;</td><td><input type="text" class="form-control" name="CashAddNote" value="">
                    </td></tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="javascript:addcashcredit();">ยืนยันข้อมูล</button>
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