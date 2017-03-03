<?php
include("dbvars.inc.php");
$alertTxt='';
if(isset($_POST["CustomerCar"]) && intval($_POST["CustomerCar"])){
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
            $Update=1;
        }
    }
    if(isset($_POST["NewCar"]) && trim($_POST["NewCar"])){
        $sql="select CarID from ".$db_name.".customer_car where CarCode='".mysql_real_escape_string(trim($_POST["NewCar"]))."' and Deleted=0;";
        $rsCar=mysql_query($sql);
        if(!mysql_num_rows($rsCar) && !preg_match("#ใส่ถัง#", $_POST["NewCar"])){
            $sqlCustomerCar="INSERT INTO ".$db_name.".customer_car (CustomerID, CarCode, CarType, CarBrand, Model, Color) VALUES (".intval($_POST["CustomerCar"]).", '".mysql_real_escape_string(trim($_POST["NewCar"]))."', '".mysql_real_escape_string(trim($_POST["NewCarType"]))."', '".mysql_real_escape_string(trim($_POST["NewCarBrand"]))."', '".mysql_real_escape_string(trim($_POST["NewCarModel"]))."', '".mysql_real_escape_string(trim($_POST["NewCarColor"]))."');";
            $rsCustomerCar=mysql_query($sqlCustomerCar);
            $Update=1;
        }
    }
    if(isset($Update)){
        $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว.</div>';
    }
}

include("header.php");
$sqlCustName="SELECT customer.CustName from ".$db_name.".customer where CustID=".intval($_REQUEST["CustomerID"]).";";
$rsCustName=mysql_query($sqlCustName);
$CustName=mysql_fetch_row($rsCustName);

$BrandList = array();
$sqlCarBrand="select ID, Brand from ".$db_name.".car_brand where Deleted=0;";
$rsCarBrand=mysql_query($sqlCarBrand);
while($CarBrand=mysql_fetch_row($rsCarBrand)){
    $BrandList[$CarBrand[0]] = $CarBrand[1];
}
$BrandList[-1]='อื่นๆ';
asort($BrandList);

$CarTypeArr = array('เก๋ง', 'กระบะ', 'รถใหญ่', 'แท็กซี่', 'มอเตอร์ไซค์', 'อื่นๆ');

if(!isset($_REQUEST['mainNo'])){
    $_REQUEST['mainNo']=0;
}
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ข้อมูลรถ</h2>
        </div>

        <div class="content-center">

            <div class="tab-content">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title" style="margin: 10px 0;">ข้อมูลรถทั้งหมดของ <?php print($CustName[0]); ?></h3>
                    </div>
                    <div class="panel-body center">
                        <?php print($alertTxt); ?>
                        <form id="submitForm" action="customer_car.php" method="post" class="form-horizontal" role="form">
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
                                $SaveButton='';
                                $sqlCarCode="SELECT CarCode, CarID, CarType, CarBrand, Model, Color from ".$db_name.".customer_car where customer_car.CustomerID=".intval($_REQUEST["CustomerID"])." and Deleted=0 order by CarCode ASC;";
                                $rsCarCode=mysql_query($sqlCarCode);
                                if($PermissionNo >= 2){
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

                                    $BrandSelect='<select name="NewCarBrand" class="form-control" style="width:180px">';
                                    foreach($BrandList as $key => $value) {
                                        $BrandSelect.='<option value="'.$key.'">'.strtoupper($value).'</option>';
                                    }
                                    $BrandSelect.='</select>';
                                    $CarType='<select name="NewCarType" class="form-control" style="width:120px">';
                                    foreach($CarTypeArr as $key => $value) {
                                        $CarType.='<option value="'.$value.'">'.$value.'</option>';
                                    }
                                    $CarType.='</select>';
                                    print('
                                    <tr>
                                        <td>เพิ่ม</td>
                                        <td><input type="text" name="NewCar" class="form-control" value=""></td>
                                        <td>'.$CarType.'</td>
                                        <td>'.$BrandSelect.'</td>
                                        <td><input type="text" name="NewCarModel" class="form-control" value=""></td>
                                        <td><input type="text" name="NewCarColor" class="form-control" value=""></td>
                                        <td>&nbsp;</td>
                                    </tr>');
                                    $SaveButton='<button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>';
                                }
                                else{
                                    while($CarCode=mysql_fetch_row($rsCarCode)){
                                        print('
                                        <tr>
                                            <td>'.$count.'</td>
                                            <td>'.$CarCode[0].'</td>
                                            <td>'.$CarCode[2].'</td>
                                            <td>'.$CarCode[3].'</td>
                                            <td>'.$CarCode[4].'</td>
                                            <td>'.$CarCode[5].'</td>
                                        </tr>');
                                        $count++;
                                    }
                                }
                                ?>
                            </table>
                            <br>
                            <div id="0" class="actionBar right">
                                <input type="hidden" name="from" value="<?php print($_REQUEST["from"]); ?>">
                                <input type="hidden" name="page" value="<?php if(isset($_REQUEST['page'])){ print($_REQUEST['page']); } ?>">
                                <input type="hidden" id="backPage" value="<?php
                                print($_REQUEST["from"].'.php');
                                if(isset($_REQUEST['page'])){
                                    print('?page='.$_REQUEST['page']);
                                }
                                if(isset($_REQUEST['mainNo'])){
                                    if(isset($_REQUEST['page'])){
                                        print("&");
                                    }
                                    else{
                                        print("?");
                                    }
                                    print('mainNo='.$_REQUEST['mainNo']);
                                }
                                ?>">
                                <input type="hidden" name="mainNo" id="mainNo" value="<?php print($_REQUEST["mainNo"]); ?>">
                                <input type="hidden" name="CustomerCar" value="<?php print($_REQUEST["CustomerID"]); ?>">
                                <input type="hidden" id="CustomerID" name="CustomerID" value="<?php print($_REQUEST["CustomerID"]); ?>">
                                <?php print($SaveButton); ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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