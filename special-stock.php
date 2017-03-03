<?php
include("dbvars.inc.php");
if(!preg_match('/-14-/', $EmpAccess) && $UserID!=1){
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
    $sqlDelete="Update ".$db_name.".products SET products.Deleted=1 Where ProductID=".intval($_POST["DeleteItem"]).";";
    $rsDelete=mysql_query($sqlDelete);
    exit();
}
else if(isset($_POST["ProductName"]) && trim($_POST["ProductName"])){
    if(!isset($_POST["UnitName"])){
        $_POST["UnitName"]=0;
    }
    $_POST["AvgCost"]=preg_replace("/,/", "", $_POST["AvgCost"]);
    $_POST["SellPrice"]=preg_replace("/,/", "", $_POST["SellPrice"]);
    $_POST["UnitUsed"]=preg_replace("/,/", "", $_POST["UnitUsed"]);
    if($_POST["UnitUsed"] < 0){
        $_POST["UnitUsed"]=1;
    }

    $sqlStock="select ID, StockName from ".$db_name.".stock where Deleted=0 order by ID ASC;";
    $rsStock=mysql_query($sqlStock);
    while($Stock=mysql_fetch_row($rsStock)){
        $StockName[$Stock[0]]=$Stock[1];
    }

    // check code
    $sqlCheck="select ProductID from ".$db_name.".products where ProductID!=".intval($_POST["UpdateProd"])." and Code='".mysql_real_escape_string(trim($_POST["Code"]))."' and Deleted=0 and Special=1;";
    $rsCheck=mysql_query($sqlCheck);
    $Check=mysql_fetch_row($rsCheck);
    if($Check[0]){
        $_POST["Code"]=$_POST["Code"].' (2)';
    }
    // check name
    $sqlCheck="select ProductID from ".$db_name.".products where ProductID!=".intval($_POST["UpdateProd"])." and Name='".mysql_real_escape_string(trim($_POST["ProductName"]))."' and Deleted=0 and Special=1;";
    $rsCheck=mysql_query($sqlCheck);
    $Check=mysql_fetch_row($rsCheck);
    if($Check[0]){
        $_POST["ProductName"]=$_POST["ProductName"].' (2)';
    }
    if(intval($_POST["UpdateProd"])>0){
        $sqlUpdate="UPDATE ".$db_name.".products SET Code='".mysql_real_escape_string(trim($_POST["Code"]))."', Name='".mysql_real_escape_string(trim($_POST["ProductName"]))."', Type='".$_POST["Type"]."', Vat=0, AvgCost='".floatval($_POST["AvgCost"])."', SellPrice='".floatval($_POST["SellPrice"])."', UseFor='".$_POST["UseFor"]."', CarType='".$_POST["CarType"]."', Note='".mysql_real_escape_string(trim($_POST["ProductNote"]))."', UnitNameID=".intval($_POST["UnitName"]).", UnitUsed=".floatval($_POST["UnitUsed"]).", CanInvoice=".intval($_POST["CanInvoice"])." where ProductID=".intval($_POST["UpdateProd"]).";";
        $rsUpdate=mysql_query($sqlUpdate);
        $NewProID=$_POST["UpdateProd"];
        $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>แก้ไขข้อมูลสินค้าเรียบร้อยแล้ว.</div>';
        if($_POST["Type"]=='สินค้า'){
            foreach ($_POST["stock"] as $key => $value) {
                $sqlQTYChk="select QTY from ".$db_name.".product_stock where ProductID=".intval($_POST["UpdateProd"])." and StockID=".$key.";";
                $rsQTYChk=mysql_query($sqlQTYChk);
                $hasRecord=mysql_num_rows($rsQTYChk);
                $QTYChk=mysql_fetch_row($rsQTYChk);
                $value=preg_replace("/,/", "", $value);
                if(!$hasRecord){
                    $sqlStock="INSERT INTO ".$db_name.".product_stock (ProductID, StockID, QTY) VALUES (".intval($_POST["UpdateProd"]).", ".$key.", ".floatval($value).");";
                }
                else{
                    $sqlStock="UPDATE ".$db_name.".product_stock set QTY=".floatval($value)." where ProductID=".intval($_POST["UpdateProd"])." and StockID=".$key.";";
                }
                $rsStock=mysql_query($sqlStock);
                if(floatval($value)!=$QTYChk[0]){
                    $sqlStock="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID, RequestBy) VALUES (".$key.", ".$NewProID.", ".time().", ".floatval($value).", 'แก้ไขจำนวนสินค้าจาก ".floatval($QTYChk[0])." เป็น ".floatval($value)."', ".intval($UserID).", ".intval($UserID).");";
                    $rsStock=mysql_query($sqlStock);
                }
            }
        }
    }
    else{
        $sqlInsert="INSERT INTO ".$db_name.".products (Code, Name, Type, Vat, AvgCost, SellPrice, UseFor, CarType, Note, UnitNameID, UnitUsed, Special, CanInvoice) VALUES ('".mysql_real_escape_string(trim($_POST["Code"]))."', '".mysql_real_escape_string(trim($_POST["ProductName"]))."', '".$_POST["Type"]."', 0, '".floatval($_POST["AvgCost"])."', '".floatval($_POST["SellPrice"])."', '".$_POST["UseFor"]."', '".$_POST["CarType"]."', '".mysql_real_escape_string(trim($_POST["ProductNote"]))."', ".intval($_POST["UnitName"]).", ".floatval($_POST["UnitUsed"]).", 1, ".intval($_REQUEST["CanInvoice"]).");";
        $rsInsert=mysql_query($sqlInsert);
        $NewProID=mysql_insert_id($Conn);
        $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>เพิ่มข้อมูลสินค้าเรียบร้อยแล้ว.</div>';
        if($_POST["Type"]=='สินค้า'){
            foreach ($_POST["stock"] as $key => $value) {
                $value=preg_replace("/,/", "", $value);
                $sqlStock="INSERT INTO ".$db_name.".product_stock (ProductID, StockID, QTY) VALUES (".$NewProID.", ".$key.", ".floatval($value).");";
                $rsStock=mysql_query($sqlStock);
                $sqlStock="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID) VALUES (".$key.", ".$NewProID.", ".time().", ".floatval($value).", 'ตั้งค่าสต็อกเริ่มต้น', ".intval($UserID).");";
                $rsStock=mysql_query($sqlStock);
            }
        }
    }
    if($_FILES["ProductImg"]["tmp_name"]){
        move_uploaded_file($_FILES["ProductImg"]["tmp_name"], "images/product-img/product-".$NewProID.".jpg");
        chmod("images/product-img/product-".$NewProID.".jpg", 0644);
    }
}


include("header.php");
if(!isset($_REQUEST['page']) || !$_REQUEST['page']){
    $_REQUEST['page']=1;
}
if(isset($_REQUEST["UpdateItem"]) && intval($_REQUEST["UpdateItem"])){
    $sqlProduct="SELECT ProductID, Code, Name, AvgCost, SellPrice, Type, UseFor, Note, CarType, UnitNameID, UnitUsed, NoVatQTY, CanInvoice from ".$db_name.".products where Deleted=0 and ProductID=".intval($_REQUEST["UpdateItem"])." and Special=1 order by Code ASC";
    $rsProduct=mysql_query($sqlProduct);
    $Product=mysql_fetch_row($rsProduct);
    $CarTypeArr = array('ทุกประเภท', 'เก๋ง', 'กระบะ', 'รถใหญ่', 'แท็กซี่', 'มอเตอร์ไซด์', 'อื่นๆ');
?>
    <section class="pageContent product_page">
        <div class="title-body">
            <h2>ทะเบียนสินค้าพิเศษ</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;"><?php if($_REQUEST["UpdateItem"]<0){ print("เพิ่มสินค้า/บริการ"); }else{ print("แก้ไขข้อมูลสินค้า/บริการ"); } ?></h3>
                </div>
                <div class="panel-body">
                    <form id="productInfo" action="special-stock.php" method="post" class="form-horizontal" role="form" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="UpdateProd" value="<?php print($_REQUEST["UpdateItem"]); ?>">
                        <input type="hidden" id="page" name="page" value="<?php print($_REQUEST['page']); ?>">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">รูปสินค้า:</label>
                            <div class="col-sm-3">
                                <?php
                                if(is_file("images/product-img/product-".$Product[0].".jpg")){
                                    print("<img src=\"images/product-img/product-".$Product[0].".jpg\" width=\"120\">");
                                }
                                ?>
                                <input type="file" name="ProductImg" class="form-control">
                            </div>
                        </div>

                        <div class="col-sm-6 form-group">
                            <label class="col-sm-4 control-label">รหัสสินค้า:</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="Code" placeholder="รหัสสินค้า" value="<?php print($Product[1]); ?>">
                            </div>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label class="col-sm-3 control-label">ชื่อสินค้า:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="ProductName" placeholder="ชื่อสินค้า" value="<?php print($Product[2]); ?>">
                            </div>
                        </div>

                        <div class="form-group" style="clear:both;">
                            <label class="col-lg-2 control-label">แสดงในใบกำกับภาษี:</label>
                            <div class="radio col-lg-2">
                                <label>
                                    <input type="radio" name="CanInvoice" value="1"<?php if($Product[12]){ print(" checked"); } ?>>
                                    แสดง
                                </label>
                            </div>
                            <div class="radio col-lg-2">
                                <label>
                                    <input type="radio" name="CanInvoice" value="0"<?php if(!$Product[12]){ print(" checked"); } ?>>
                                    ไม่แสดง
                                </label>
                            </div>
                        </div>

                        <input type="hidden" name="Type" value="สินค้า">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">ราคาขาย:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control price" name="SellPrice" placeholder="ราคาขาย" value="<?php if($Product[1]){ print(number_format($Product[4], 2)); } ?>" style="width:100px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">ราคาทุน:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control price" name="AvgCost" placeholder="ราคาทุน" value="<?php if($Product[1]){print(number_format($Product[3], 2)); } ?>" style="width:100px;">
                            </div>
                        </div>
                        <div class="form-group forProductType">
                            <label class="col-sm-2 control-label">หน่วยนับ:</label>
                            <div class="col-sm-2">
                                <select name="UnitName" id="UnitName" class="form-control input-sm" onchange="javascript:var e=document.getElementById('UnitName'); document.getElementById('UnitUsedName').innerHTML=e.options[e.selectedIndex].text;">
                                <?php
                                    $UnitText="";
                                    $sql="select ID, Name from ".$db_name.".product_unit where Deleted=0 order by Name ASC;";
                                    $rs=mysql_query($sql);
                                    while ($unit=mysql_fetch_row($rs)) {
                                        print('<option value="'.$unit[0].'"');
                                        if(!$unit[0]){
                                            $unit[0] = $Product[9];
                                        }
                                        if($unit[0] == $Product[9]){
                                            print(' selected');
                                            $UnitText=$unit[1];
                                        }
                                        print('>'.$unit[1].'</option>');
                                    }
                                ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group forProductType"<?php if($Product[5]=='บริการ'){ print(' style="display:none;"'); } ?>>
                            <label class="col-sm-2 control-label">ใช้สำหรับงานบริการครั้งละ:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control inline_input number" name="UnitUsed" value="<?php if($Product[10]>0){ print(number_format($Product[10], 2)); }else{ print('1.00'); } ?>" style="width:70px;"> &nbsp; <span id="UnitUsedName"><?php print($UnitText); ?></span>
                            </div>
                        </div>

                        <div class="form-group forProductType"<?php if($Product[5]=='บริการ'){ print(' style="display:none;"'); } ?>>
                            <label class="col-sm-2 control-label">จำนวนสินค้าคงเหลือ:</label>
                            <?php
                                $sqlStock="select stock.ID, StockName, QTY from (".$db_name.".stock left join ".$db_name.".product_stock on StockID=stock.ID and ProductID=".intval($_REQUEST["UpdateItem"]).") where stock.Deleted=0 order by stock.ID ASC;";
                                $rsStock=mysql_query($sqlStock);
                                while($Stock=mysql_fetch_row($rsStock)){
                                    print('<div class="col-sm-2" style="margin-top:7px;">');
                                    if($PermissionNo>=2){ // admin or supervisor
                                        print($Stock[1].'<br><input type="text" class="form-control number" name="stock['.$Stock[0].']" value="'.number_format($Stock[2], 2).'" style="display:inline; width:100px;">');
                                    }
                                    else{
                                        print($Stock[1]." ".number_format($Stock[2], 2)." ".$UnitText);
                                    }
                                    print('</div>');
                                }
                            ?>
                        </div>
                        <?php
                        if($Product[11]>0){
                            print('
                            <div class="form-group" style="color:orange;">
                                <label class="col-sm-2 control-label">จำนวนสินค้าไม่คำนวณภาษี:</label>
                                <div class="col-sm-5" style="padding-top:7px;">
                                    '.number_format($Product[11]).' '.$UnitText.'
                                </div>
                            </div>');
                        }
                        ?>
                        <input type="hidden" name="UseFor" value="สินค้าพิเศษ">
                        <input type="hidden" name="CarType" value="อื่นๆ">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">หมายเหตุ:</label>
                            <div class="col-sm-4">
                                <textarea name="ProductNote" class="form-control" style="height:80px;"><?php print($Product[7]); ?></textarea>
                            </div>
                        </div>
                        <br>
                        <div class="actionBar right">
                            <button type="submit" class="btn btn-success btn-rounder"><?php if(intval($_REQUEST['UpdateItem'])<0){ print('เพิ่มข้อมูล'); }else{ print('แก้ไขข้อมูล'); } ?></button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="hidden" id="backPage" value="special-stock.php<?php if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="reset" class="btn btn-danger btn-rounder"><?php if($_REQUEST["UpdateItem"]<0){ print('ล้างข้อมูล'); }else{ print('รีเซ็ตข้อมูล'); } ?></button>
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php
}
else{
    $ItemPerPage=50;
    if(!isset($_REQUEST['page'])){
        $_REQUEST['page']=1;
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ทะเบียนสินค้าพิเศษ</h2>
        </div>

        <div class="content-center">

            <div class="tab-content">
                <div class="tab-pane fade in active">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- TABLE OPTION -->
                            <div role="toolbar" class="btn-toolbar padding-bootom">
                                <div id="-1" class="btn-group">
                                    <button class="btn btn-success editItem" type="button"><i class="fa fa-plus"></i> เพิ่มสินค้าพิเศษ</button>
                                    <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-info" type="button" onclick="location.href='stock-import.php?page=<?php print($_REQUEST['page']); ?>&special=1';"><i class="fa fa-mail-forward"></i> ซื้อสินค้าเข้าสต็อก</button>
                                    <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                </div>
                                <!-- <div class="btn-group">
                                    <button class="btn btn-warning" type="button" onclick="location.href='unitofsales.php?page=<?php print($_REQUEST['page']); ?>';"><i class="fa fa-gears"></i> จัดการหน่วยนับ</button>
                                    <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                </div> -->
                                <div class="btn-group">
                                    <button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-book"></i> รายงาน <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="stock-report.php?page=<?php print($_REQUEST['page']); ?>&special=1">รายงานการเคลื่อนไหว</a></li>
                                        <li><a href="stock-report.php?reportType=3&page=<?php print($_REQUEST['page']); ?>&special=1">รายงานสินค้าคงคลัง</a></li>
                                        <li><a href="stock-report.php?reportType=2&page=<?php print($_REQUEST['page']); ?>&special=1">รายงานรายการซื้อสินค้า</a></li>
                                        <li><a href="stock-report.php?reportType=4&page=<?php print($_REQUEST['page']); ?>&special=1">รายงานการใช้สินค้า</a></li>
                                        <li><a href="saletax-report.php?page=<?php print($_REQUEST['page']); ?>&special=1">รายงานภาษีซื้อ</a></li>
                                        <li><a href="stock-income.php?back=special-stock&special=1">รายงานกำไร/ขาดทุนประจำวัน</a></li>
                                    </ul>
                                </div>
                                <div class="btn-group pull-right">
                                    <form method="post" id="submitForm" role="form">
                                        <input type="hidden" id="page" name="page" value="<?php print($_REQUEST['page']); ?>">
                                        <input type="hidden" id="UpdateItem" name="UpdateItem" value="0">
                                        <input type="text" name="ProductSearch" value="<?php if(isset($_POST['ProductSearch'])){ print(trim($_POST['ProductSearch'])); } ?>" placeholder="Search..." class="form-control">
                                    </form>
                                </div>
                            </div>

                            <br>
                            <?php print($alertTxt); ?>
                            <br>
                            <!-- TABLE OPTION -->
                            <div class="table-responsive">
                                <input type="hidden" id="submitTo" value="special-stock.php">
                                <input type="hidden" id="backPage" name="backPage" value="special-stock.php">
                                <table class="td_center table table-condensed table-striped table-default table_border">
                                    <thead>
                                        <tr>
                                            <th>&nbsp;</th>
                                            <th>รหัสสินค้า</th>
                                            <th>ชื่อสินค้า</th>
                                            <th>ราคาทุน (฿)</th>
                                            <th>ราคาขาย (฿)</th>
                                            <th>สินค้าคงคลัง</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $sqlProduct="SELECT ProductID, Code, Name, AvgCost, SellPrice, Type from ".$db_name.".products where Deleted=0 and Special=1";
                                    if(isset($_POST["ProductSearch"]) && trim($_POST["ProductSearch"])){
                                        $sqlProduct.=" and ((Code like '%".mysql_real_escape_string(trim($_POST['ProductSearch']))."%') or (Name like '%".mysql_real_escape_string(trim($_POST['ProductSearch']))."%'))";
                                    }
                                    $rsProduct=mysql_query($sqlProduct.";");
                                    $ProductNum=mysql_num_rows($rsProduct);
                                    $sqlProduct.=" order by Type ASC, Code ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                                    $rsProduct=mysql_query($sqlProduct);
                                    while($Product=mysql_fetch_row($rsProduct)){
                                        $ProductPic='<img src="images/clrpix.gif" alt="" class="table-avatar">';
                                        if(is_file('images/product-img/product-'.$Product[0].'.jpg')){
                                            $ProductPic='<img src="images/product-img/product-'.$Product[0].'.jpg" alt="" class="table-avatar">';
                                        }
                                        $sqlStock="select sum(QTY) from ".$db_name.".product_stock where ProductID=".intval($Product[0]).";";
                                        $rsStock=mysql_query($sqlStock);
                                        $Stock=mysql_fetch_row($rsStock);
                                        $inStock=$Stock[0];
                                        $AvgCost=number_format($Product[3], 2);
                                        $removeLink='';
                                        if($PermissionNo==3){
                                            $removeLink='<button class="btn btn-danger btn-xs removeItem" id="'.$Product[1].'"><i class="fa fa-ban"></i> ลบ &nbsp;</button>';
                                        }
                                        print('
                                        <tr id="item-'.$Product[0].'">
                                            <td>
                                                '.$ProductPic.'
                                            </td>
                                            <td>
                                                <a href="special-stock.php?UpdateItem='.$Product[0].'&page='.$_REQUEST['page'].'">'.$Product[1].'</a>
                                            </td>
                                            <td class="text-left">&nbsp;'.$Product[2].'</td>
                                            <td>'.$AvgCost.'</td>
                                            <td>'.number_format($Product[4], 2).'</td>
                                            <td>'.$inStock.'</td>
                                            <td>
                                                <div id="'.$Product[0].'">
                                                    <button class="btn btn-success btn-xs editItem"><i class="fa fa-edit"></i> แก้ไข &nbsp;</button>
                                                    &nbsp;&nbsp;
                                                    '.$removeLink.'
                                                </div>
                                            </td>
                                        </tr>');
                                    }
                                    if(!$ProductNum){ // ค้นหาไม่เจอ
                                        $moreText='ในระบบ';
                                        if(isset($_POST['ProductSearch']) && trim($_POST['ProductSearch'])){
                                            $moreText='ของ '.$_POST['ProductSearch'];
                                        }
                                        print('<tr><td style="background-color:#fff; padding:20px; color:red;" colspan="8">ไม่พบข้อมูล'.$moreText.'</td></tr>');
                                    }
                                    ?>
                                </table>
                            </div>
                            <?php
                            if($ProductNum > $ItemPerPage){
                                $AllPage=ceil($ProductNum/$ItemPerPage);
                                print("<br>");
                                if($_REQUEST['page']!=1){
                                    print('<a href="special-stock.php?page='.($_REQUEST['page']-1).'" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                                }
                                print("<select onchange=\"javascript:location.href='special-stock.php?page='+this.value;\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
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
                                    print('<a href="special-stock.php?page='.($_REQUEST['page']+1).'" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
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

<?php
}
include("footer.php");
?>