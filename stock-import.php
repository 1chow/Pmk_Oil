<?php
include("dbvars.inc.php");
if(!preg_match('/-3-/', $EmpAccess) && $UserID!=1){
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
if(isset($_POST["ImportQTY"]) && intval($_POST["ImportQTY"])){
    $sqlProduct="SELECT Code, Name, AvgCost from ".$db_name.".products where Type='สินค้า' and ProductID=".intval($_POST['ImportProductID']).";";
    $rsProduct=mysql_query($sqlProduct);
    $Product=mysql_fetch_row($rsProduct);

    $sqlStock="select sum(QTY) from ".$db_name.".product_stock where ProductID=".intval($_POST['ImportProductID']).";";
    $rsStock=mysql_query($sqlStock);
    $Stock=mysql_fetch_row($rsStock);
    $TotalCost1=round($Product[2]*$Stock[0], 2);
    $TotalQTY1=$Stock[0];

    $_POST["ImportQTY"]=preg_replace("/,/", "", $_POST["ImportQTY"]);
    $_POST["TotalPrice"]=preg_replace("/,/", "", $_POST["TotalPrice"]);
    $TotalCost2=$_POST["TotalPrice"];
    $TotalQTY2=$_POST["ImportQTY"];
    //echo $TotalCost1.'+'.$TotalCost2."/".$TotalQTY1."+".$TotalQTY2;
    $AvgCost=round(($TotalCost1+$TotalCost2)/($TotalQTY1+$TotalQTY2), 2);

    $setNoVat="";
    if(!intval($_POST['calVat'])){
        // product_stock for NoVatQTY
        $setNoVat=", NoVatQTY=NoVatQTY+".floatval($_POST["ImportQTY"]);
    }
    $sqlStockVat="UPDATE ".$db_name.".products set AvgCost=".$AvgCost.$setNoVat." where ProductID=".intval($_POST['ImportProductID']).";";
    $rsStockVat=mysql_query($sqlStockVat);

    $SetDate=explode("/", $_REQUEST['BuyDate']);
    $ImportDate=mktime(date("H", time()), date("i", time()), 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    // product_stock
    $sqlStock="UPDATE ".$db_name.".product_stock set QTY=QTY+".floatval($_POST["ImportQTY"])." where ProductID=".intval($_POST['ImportProductID'])." and StockID=".intval($_POST['ImportToStock']).";";
    $rsStock=mysql_query($sqlStock);

    // product_history
    $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID, RequestBy) VALUES (".intval($_POST['ImportToStock']).", ".intval($_POST['ImportProductID']).", ".$ImportDate.", ".floatval($_POST["ImportQTY"]).", 'ซื้อสินค้าเข้าสต็อก', ".intval($UserID).", ".intval($_POST["ImportBy"]).");";
    $rsStockHistory=mysql_query($sqlStockHistory);
    $HistoryID=mysql_insert_id($Conn);

    // product_import
    $sqlStockHistory="INSERT INTO ".$db_name.".product_import (HistoryID, SupName, TotalPrice, Note, CalVat) VALUES (".intval($HistoryID).", '".mysql_real_escape_string(trim($_POST['SupName']))."', ".floatval($_POST["TotalPrice"]).", '".mysql_real_escape_string(trim($_POST['ImportNote']))."', ".intval($_POST['calVat']).");";
    $rsStockHistory=mysql_query($sqlStockHistory);

    // account_daily
    $sqlInsert="INSERT INTO ".$db_name.".account_daily (Type, Name, Total, PaidTo, PaidDate, Note, BookCodeNo) VALUES ('2', '".mysql_real_escape_string(trim($_POST['SupName']))."', '".floatval($_POST["TotalPrice"])."', '".intval($_POST['ImportBy'])."', '".$ImportDate."', 'ซื้อสินค้าเข้าสต็อก ".$Product[1]." (".$Product[0].")', '".mysql_real_escape_string(trim($_POST['BookCodeNo']))."');";
    $rsInsert=mysql_query($sqlInsert);

    header('location: stock-import.php?Success=1&page='.$_REQUEST['page']);
}
else if(isset($_POST["getUnitName"]) && intval($_POST["getUnitName"])){
    $sqlUnitName="SELECT product_unit.Name from ".$db_name.".products left join ".$db_name.".product_unit on UnitNameID=product_unit.ID where ProductID=".intval($_POST['getUnitName']).";";
    $rsUnitName=mysql_query($sqlUnitName);
    $UnitName=mysql_fetch_row($rsUnitName);
    print($UnitName[0]);
    exit();
}
$alertTxt="";
if(isset($_REQUEST['Success']) && intval($_REQUEST["Success"])){
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลการซื้อของเรียบร้อยแล้ว.</div>';
}


include("header.php");

$firstStock=0;
$StockOption="";
$sqlStock="select stock.ID, StockName from ".$db_name.".stock where stock.Deleted=0 order by stock.ID ASC;";
$rsStock=mysql_query($sqlStock);
while($Stock=mysql_fetch_row($rsStock)){
    if(!$firstStock){
        $firstStock=$Stock[0];
    }
    $StockOption.='<option value="'.$Stock[0].'">'.$Stock[1].'</option>';
}
if(isset($_REQUEST["special"]) && intval($_REQUEST["special"])){
    $_REQUEST["back"]="special-stock";
    $_REQUEST["special"]=1;
}
else{
    $_REQUEST["back"]="stock";
    $_REQUEST["special"]=0;
}
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ซื้อสินค้าเข้าสต็อก <?php if(intval($_REQUEST["special"])){ print("(สินค้าพิเศษ)"); } ?></h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form onsubmit="javascript:return checkImport();" action="stock-import.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <input type="hidden" name="special" value="<?php print($_REQUEST["special"]); ?>">
                        <?php print($alertTxt); ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">วันที่:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control Calendar" name="BuyDate" value="<?php print(date("d/m/Y", time())); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">ชื่อร้านค้า/ผู้ขาย:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control inline_input" name="SupName" value="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">เล่มที่/เลขที่:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control inline_input" name="BookCodeNo" value="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">ชื่อสินค้า:</label>
                            <div class="col-sm-4">
                                <select name="ImportProductID" id="ImportProductID" class="form-control" onchange="javascript:checkUnitName();">
                                <?php
                                $count=0;
                                $sqlProduct="SELECT ProductID, Code, Name, UnitNameID from ".$db_name.".products where Type='สินค้า' and Deleted=0 and Special=".intval($_REQUEST["special"])." order by Code ASC;";
                                $rsProduct=mysql_query($sqlProduct);
                                while($Product=mysql_fetch_row($rsProduct)){
                                    if(!$count){
                                        $sqlUnitName="SELECT product_unit.Name from ".$db_name.".product_unit where product_unit.ID=".intval($Product[3]).";";
                                        $rsUnitName=mysql_query($sqlUnitName);
                                        $UnitName=mysql_fetch_row($rsUnitName);

                                        $sqlStock="SELECT QTY from ".$db_name.".product_stock where ProductID=".intval($Product[0])." and StockID=".intval($firstStock).";";
                                        $rsStock=mysql_query($sqlStock);
                                        $InStock=mysql_fetch_row($rsStock);
                                    }
                                    print('<option value="'.$Product[0].'">'.$Product[1].' -- '.$Product[2].'</option>');
                                    $count++;
                                }
                                ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">จำนวน:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control number inline_input" name="ImportQTY" id="ImportQTY" value="" style="width:85px;" onchange="javascript:findUnitPrice();"> <span id="UnitInfo"> <?php print($UnitName[0]); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">ราคารวมทั้งสิ้น:</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control price inline_input" name="TotalPrice" id="TotalPrice" value="" style="width:85px;" onchange="javascript:findUnitPrice();"> บาท &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="UnitPrice" class="red"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">คำนวณ Vat:</label>
                            <div class="col-sm-6" style="margin-top:6px;">
                                <input type="radio" name="calVat" value="1" checked> YES
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" name="calVat" value="0"> NO
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">นำเข้าสต็อก:</label>
                            <div class="col-sm-2">
                                <select name="ImportToStock" id="ImportToStock" class="form-control">
                                <?php
                                    print($StockOption);
                                ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">ซื้อโดย:</label>
                            <div class="col-sm-7">
                                <select name="ImportBy" class="form-control" style="width:170px;">
                                    <?php
                                    $sqlName = "select EmpID, concat(FirstName, ' ', LastName) from ".$db_name.".employee where employee.EmpID!=1 and employee.Deleted=0 order by FirstName ASC, LastName ASC;";
                                    $rsName = mysql_query($sqlName);
                                    while($Name=mysql_fetch_row($rsName)){
                                        print('<option value="'.$Name[0].'">'.$Name[1].'</option>');
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">หมายเหตุ:</label>
                            <div class="col-sm-4">
                                <textarea name="ImportNote" class="form-control" style="height:80px;"><?php print($Product[7]); ?></textarea>
                            </div>
                        </div>

                        <div class="actionBar" style="text-align:center;">
                            <input type="hidden" id="MaxInstock" name="MaxInstock" value="<?php print($InStock[0]); ?>">
                            <input type="hidden" id="page" name="page" value="<?php print($_REQUEST['page']); ?>">
                            <input type="hidden" id="submitTo" value="stock-import.php">
                            <input type="hidden" id="backPage" value="<?php print($_REQUEST["back"].".php"); if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                            <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
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