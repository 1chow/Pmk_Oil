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

include("header.php");
$alertTxt='';
if(isset($_POST["AdminEmail"]) && trim($_POST["AdminEmail"])){
    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["AdminEmail"]))."' Where ConstantName='AdminEmail';";
    $rsUpdate=mysql_query($queryUpdate);

    $_POST["BonusRate"]=preg_replace("/,/", "", $_POST["BonusRate"]);
    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["BonusRate"]))."' Where ConstantName='BonusRate';";
    $rsUpdate=mysql_query($queryUpdate);

    $_POST["OTHourRate"]=preg_replace("/,/", "", $_POST["OTHourRate"]);
    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["OTHourRate"]))."' Where ConstantName='OTHourRate';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".intval($_POST["SSRate"])."' Where ConstantName='SSRate';";
    $rsUpdate=mysql_query($queryUpdate);

    $_POST["MaxSSRate"]=preg_replace("/,/", "", $_POST["MaxSSRate"]);
    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["MaxSSRate"]))."' Where ConstantName='MaxSSRate';";
    $rsUpdate=mysql_query($queryUpdate);

    $_POST["MinSalarySS"]=preg_replace("/,/", "", $_POST["MinSalarySS"]);
    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["MinSalarySS"]))."' Where ConstantName='MinSalarySS';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".intval($_POST["NoDayOff"])."' Where ConstantName='NoDayOff';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".trim($_POST["PaymentDate"]).",".trim($_POST["PaymentDate2"])."' Where ConstantName='lastDay4Payment';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["CompanyName"]))."' Where ConstantName='CompanyName';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["CompanyAddress"]))."' Where ConstantName='CompanyAddress';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["CompanyPhone"]))."' Where ConstantName='CompanyPhone';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["CompanyFax"]))."' Where ConstantName='CompanyFax';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["CompanyCode"]))."' Where ConstantName='CompanyCode';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["InvoiceBegin"]))."' Where ConstantName='InvoiceBegin';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["ServiceWashBegin"]))."' Where ConstantName='ServiceWashBegin';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["AccessOilPayment"]))."' Where ConstantName='AccessOilPayment';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["OilChangeBegin"]))."' Where ConstantName='OilChangeBegin';";
    $rsUpdate=mysql_query($queryUpdate);

    // $queryUpdate="Update system Set ConstantValue='".intval($_POST["showAllCust"])."' Where ConstantName='showAllCust';";
    // $rsUpdate=mysql_query($queryUpdate);
    $_POST["SystemBalance"]=preg_replace("/,/", "", $_POST["SystemBalance"]);
    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["SystemBalance"]))."' Where ConstantName='SystemBalance';";
    $rsUpdate=mysql_query($queryUpdate);

    $_POST["ApproveLimit"]=preg_replace("/,/", "", $_POST["ApproveLimit"]);
    $queryUpdate="Update system Set ConstantValue='".intval($_POST["ApproveLimit"])."' Where ConstantName='ApproveLimit';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".intval($_POST["Stock4Service"])."' Where ConstantName='Stock4Service';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".mysql_real_escape_string(trim($_POST["WarningTime"]))."' Where ConstantName='WarningTime';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryUpdate="Update system Set ConstantValue='".intval($_POST["WashNameShow"])."' Where ConstantName='WashNameShow';";
    $rsUpdate=mysql_query($queryUpdate);

    $queryConstants="Select ConstantName, ConstantValue From ".$db_name.".system;";
    $rsConstants=mysql_query($queryConstants);
    while($Constants=mysql_fetch_row($rsConstants)){
        eval("$".$Constants[0]."=\"".$Constants[1]."\";");
    }

    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว.</div>';
}
?>
    <section id="pageContent" class="pageContent">
        <div id="PageHeader" class="title-body">
            <h2>ตั้งค่าระบบ</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">

                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <form action="system.php" id="systemForm" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Admin's Email:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="AdminEmail" value="<?php print($AdminEmail); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">เลขประจำตัวผู้เสียภาษี:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="CompanyCode" value="<?php print($CompanyCode); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">ชื่อบริษัท:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="CompanyName" value="<?php print($CompanyName); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">ที่อยู่:</label>
                            <div class="col-sm-5">
                                <textarea name="CompanyAddress" class="form-control" style="height:80px;"><?php print($CompanyAddress); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">เบอร์โทร:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="CompanyPhone" value="<?php print($CompanyPhone); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">แฟกซ์:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="CompanyFax" value="<?php print($CompanyFax); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">เงินกองกลาง:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control inline_input" id="SystemBalance" name="SystemBalance" value="<?php print(number_format($SystemBalance, 2)); ?>" style="width:120px;"> บาท
                                <input type="hidden" id="OldSystemBalance" value="<?php print(number_format($SystemBalance, 2)); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">เลขที่ใบกำกับภาษีเริ่มต้น:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control integer" name="InvoiceBegin" value="<?php print($InvoiceBegin); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">เลขที่ใบรับบริการล้างรถเริ่มต้น:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control integer" name="ServiceWashBegin" value="<?php print($ServiceWashBegin); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">เลขที่ใบรับบริการเปลี่ยนน้ำมันเครื่องเริ่มต้น:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control integer" name="OilChangeBegin" value="<?php print($OilChangeBegin); ?>">
                            </div>
                        </div>


                        <!-- <hr>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">หน้าหลักทะเบียนลูกค้า:</label>
                            <div class="col-sm-3">
                                <select class="form-control inline_input" name="showAllCust" style="width:1ุ0px;">
                                    <option value="0"<?php if(!$showAllCust){ print(" selected"); } ?>>แสดงเฉพาะรายชื่อลูกค้าเครดิต</option>
                                    <option value="1"<?php if($showAllCust){ print(" selected"); } ?>>แสดงรายชื่อลูกค้าทุกประเภท</option>
                                </select>
                            </div>
                        </div> -->

                        <hr>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">เวลาสำหรับเตือนอัพเดตน้ำมัน:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control inline_input time" name="WarningTime" value="<?php if(trim($WarningTime)){ print($WarningTime); }else{ print("00:00"); } ?>" style="width:80px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">รหัสอนุมัติเพื่อบันทึกการขาย:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control inline_input" name="AccessOilPayment" value="<?php print($AccessOilPayment); ?>" style="width:80px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">มูลค่าที่ใช้ได้ของคูปองที่ยังไม่ได้อนุมัติ:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control inline_input price" name="ApproveLimit" value="<?php print(number_format($ApproveLimit, 2)); ?>" style="width:120px;"> บาท
                            </div>
                        </div>

                        <hr>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">เริ่มรอบเงินเดือนพนักงานทุกวันที่:</label>
                            <div class="col-sm-8">
                                <?php
                                $PaymentDateArr=explode(",", $lastDay4Payment);
                                ?>
                                <input type="text" class="form-control inline_input" name="PaymentDate" value="<?php print($PaymentDateArr[0]); ?>" style="width:80px;"> &nbsp;&nbsp;&nbsp; <input type="text" class="form-control inline_input" name="PaymentDate2" value="<?php print($PaymentDateArr[1]); ?>" style="width:80px;"> ** ใส่ -1 ในกรณีที่ต้องการวันสิ้นเดือน
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">อัตราโบนัส:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control inline_input price" name="BonusRate" value="<?php print(number_format($BonusRate, 2)); ?>" style="width:120px;"> บาท
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">อัตราโอที:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control inline_input price" name="OTHourRate" value="<?php print(number_format($OTHourRate, 2)); ?>" style="width:120px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">อัตราประกันสังคม:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control inline_input" name="SSRate" value="<?php print($SSRate); ?>" style="width:80px;"> %
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">อัตราประกันสังคมสูงสุดไม่เกิน:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control inline_input price" name="MaxSSRate" value="<?php print(number_format($MaxSSRate, 2)); ?>" style="width:120px;"> บาท
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">ยอดรายได้ขั้นต่ำในการคิดประกันสังคม:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control inline_input price" name="MinSalarySS" value="<?php print(number_format($MinSalarySS, 2)); ?>" style="width:120px;"> บาท
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">ไม่มีการลาก่อน / หลังวันหยุดพิเศษ:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control inline_input qty" name="NoDayOff" value="<?php print($NoDayOff); ?>" style="width:50px;"> วัน
                            </div>
                        </div>

                        <hr>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">สต็อกสำหรับล้างรถ/เปลี่ยนน้ำมันเครื่อง:</label>
                            <div class="col-sm-3">
                                <select name="Stock4Service" class="form-control">
                                <?php
                                    $sqlStock="select stock.ID, StockName from ".$db_name.".stock where stock.Deleted=0 order by stock.ID ASC;";
                                    $rsStock=mysql_query($sqlStock);
                                    while($Stock=mysql_fetch_row($rsStock)){
                                        print('<option value="'.$Stock[0].'"');
                                        if($Stock4Service==$Stock[0]){
                                            print(" selected");
                                        }
                                        print('>'.$Stock[1].'</option>');
                                    }
                                ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">แสดงชื่อพนักงานในหน้าล้างรถ:</label>
                            <div class="col-sm-3">
                                <select name="WashNameShow" class="form-control" style="width:100px;">
                                <option value="0"<?php if(intval($WashNameShow)==0){ print(" selected"); } ?>>ไม่แสดง</option>
                                <option value="1"<?php if(intval($WashNameShow)==1){ print(" selected"); } ?>>แสดง</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" value="employees.php">
                            <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="reset" class="btn btn-danger btn-rounder">รีเซ็ตข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php
include("footer.php");
?>