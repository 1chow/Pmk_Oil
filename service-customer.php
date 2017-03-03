<?php
include("dbvars.inc.php");
if(!preg_match('/-9-/', $EmpAccess) && !preg_match('/-2-/', $EmpAccess) && $UserID!=1){
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
if(isset($_POST["DeleteItem"]) && intval($_POST["DeleteItem"])){ // delete employerr
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
    if(intval($_POST["UpdateCust"])>0){
        $sqlUpdate="UPDATE ".$db_name.".customer SET CustName='".mysql_real_escape_string(trim($_POST["CustName"]))."', Address1='".mysql_real_escape_string(trim($_POST["Address1"]))."', Address2='".mysql_real_escape_string(trim($_POST["Address2"]))."', Address3='".mysql_real_escape_string(trim($_POST["Address3"]))."', Address4='".mysql_real_escape_string(trim($_POST["Address4"]))."', Tel='".mysql_real_escape_string(trim($_POST["Tel"]))."', TaxCode='".mysql_real_escape_string(trim($_POST["TaxCode"]))."', BranchCode='".mysql_real_escape_string(trim($BranchCode))."', CheckCarCode=0 where CustID=".intval($_POST["UpdateCust"]).";";
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

        $sqlInsert="INSERT INTO ".$db_name.".customer (CustName, Address1, Address2, Address3, Address4, Tel, TaxCode, BranchCode, CheckCarCode, CreditLock, CreditLimit, CreditTerm, SpecialTerm, DayBeforePay, UnofficialBalance, FromService) VALUES ('".mysql_real_escape_string(trim($_POST["CustName"]))."', '".mysql_real_escape_string(trim($_POST["Address1"]))."', '".mysql_real_escape_string(trim($_POST["Address2"]))."', '".mysql_real_escape_string(trim($_POST["Address3"]))."', '".mysql_real_escape_string(trim($_POST["Address4"]))."', '".mysql_real_escape_string(trim($_POST["Tel"]))."', '".mysql_real_escape_string(trim($_POST["TaxCode"]))."', '".mysql_real_escape_string(trim($BranchCode))."', 0, 0, '0.00', 0, '', 0, '0.00', 1);";
        $rsInsert=mysql_query($sqlInsert);
        $UpdateCustID=mysql_insert_id($Conn);
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
    $AlertArr = array('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>แก้ไขข้อมูลลูกค้าเรียบร้อยแล้ว.</div>', '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>เพิ่มข้อมูลลูกค้าเรียบร้อยแล้ว.</div>');
    $alertTxt=$AlertArr[$_REQUEST['ShowMSG']];
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
    $sqlCust="SELECT CustName, Address1, Address2, Tel, TaxCode, BranchCode, Address3, Address4 from ".$db_name.".customer where ".$CustCond." order by CustName ASC;";
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
            <h2>ทะเบียนลูกค้า</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;"><?php if($_POST["UpdateItem"]<0){ print("เพิ่มข้อมูลลูกค้า"); }else{ print("แก้ไขข้อมูลลูกค้า"); } ?></h3>
                </div>

                <div class="panel-body">
                    <form action="service-customer.php" method="post" class="form-horizontal" role="form" autocomplete="off">
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
                                print("service-customer.php");
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
                                    <button class="btn btn-success editItem" type="button"><i class="fa fa-plus"></i> &nbsp;เพิ่มลูกค้า</button>
                                </div>
                                <div class="btn-group pull-right">
                                    <form method="post" id="submitForm" role="form">
                                        <input type="hidden" id="backPage" name="backPage" value="<?php if(isset($_REQUEST['backPage'])){ print($_REQUEST['backPage']); }else{ print('service-customer.php'); } ?>">
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
                                <input type="hidden" id="submitTo" value="service-customer.php">
                                <table class="td_center table table-condensed table-striped table-default table_border">
                                    <thead>
                                        <tr>
                                            <th>ชื่อ</th>
                                            <th style="width:20%;">เบอร์โทร</th>
                                            <th style="width:30%;">ทะเบียนรถ</th>
                                            <th style="width:190px;">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $DeleteCust="";
                                    $LockCust="";
                                    $sqlCustSearch="";
                                    if(isset($_POST['searchCust']) && trim($_POST['searchCust'])){
                                        $sqlCarSearch="select customer_car.CustomerID from ".$db_name.".customer_car where customer_car.CarCode like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%' and CarID!=(-1) and customer_car.Deleted=0;";
                                        $rsCarSearch=mysql_query($sqlCarSearch);
                                        $CarSearch=mysql_fetch_row($rsCarSearch);
                                        $sqlCustSearch=" and (CustName like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%' or Tel like '%".mysql_real_escape_string(trim($_POST['searchCust']))."%' or CustID=".intval($CarSearch[0]).")";
                                    }
                                    $sqlCust="SELECT CustID, CustName, Tel from ".$db_name.".customer where customer.Deleted=0 and (FromService=1 or FromService=2 or FromService=4)".$sqlCustSearch;
                                    $rsCust=mysql_query($sqlCust.";");
                                    $CustNum=mysql_num_rows($rsCust);
                                    $sqlCust.=" group by CustID order by CustName ASC";
                                    if(!trim($sqlCustSearch)){
                                        $sqlCust.=" Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage;
                                    }
                                    $rsCust=mysql_query($sqlCust.";");
                                    //echo $sqlCust;
                                    while($CustInfo=mysql_fetch_row($rsCust)){
                                        $CarList="";
                                        $sqlCarCode="SELECT CarCode from ".$db_name.".customer_car where customer_car.CustomerID=".intval($CustInfo[0])." and CarID!=(-1) and Deleted=0 order by CarCode ASC;";
                                        $rsCarCode=mysql_query($sqlCarCode);
                                        while($CarCode=mysql_fetch_row($rsCarCode)){
                                            if($CarList){
                                                $CarList.=", ";
                                            }
                                            $CarList.=$CarCode[0];
                                        }

                                        if($Permission=='admin'){
                                            $DeleteCust='&nbsp;&nbsp;&nbsp;&nbsp;<button class="btn btn-danger btn-xs removeItem" id="'.$CustInfo[1].'" title="ลบ"><i class="fa fa-ban"></i></button>&nbsp;&nbsp;&nbsp;&nbsp;';
                                        }
                                        print('
                                    <tr id="item-'.$CustInfo[0].'">
                                        <td class="text-left">&nbsp;
                                            <span class="cust_name-link editCust" title="แก้ไขข้อมูลของ '.$CustInfo[1].'">'.$CustInfo[1].'</span>
                                        </td>
                                        <td>
                                            '.$CustInfo[2].'
                                        </td>
                                        <td>
                                            '.$CarList.'
                                        </td>
                                        <td>
                                            <div id="'.$CustInfo[0].'">&nbsp;
                                                <button class="btn btn-success btn-xs editCust" title="แก้ไขข้อมูลลูกค้า"><i class="fa fa-edit"></i></button>
                                                &nbsp;&nbsp;
                                                <button class="btn btn-primary btn-xs" title="ประวัติ" onclick="location.href=\'service_history.php?CustID='.$CustInfo[0].'&backPageNo='.$_REQUEST['page'].'&mainNo='.$_REQUEST['mainNo'].'\'"><i class="fa fa-clock-o"></i></button>
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
                                    print('<a href="service-customer.php?page='.($_REQUEST['page']-1).$moreLink.'" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                                }
                                print("<select onchange=\"javascript:location.href='service-customer.php?page='+this.value+'".$moreLink."';\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
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
                                    print('<a href="service-customer.php?page='.($_REQUEST['page']+1).$moreLink.'" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                                }
                            }
                            if(!isset($_REQUEST['mainNo']) || !intval($_REQUEST['mainNo'])){
                                // print('<br>
                                // <div class="actionBar right">
                                //     <button type="button" class="btn btn-inverse btn-rounder" onclick="javascript:location.href=\'car_service.php\';">ย้อนกลับ</button>
                                //     &nbsp;&nbsp;&nbsp;
                                // </div>');
                            }
                            ?>

                            <!-- FULL FUNCTION TABLE -->

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>
<?php
}
include("footer.php");
?>