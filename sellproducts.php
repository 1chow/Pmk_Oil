<?php
include("dbvars.inc.php");
if(!preg_match('/-7-/', $EmpAccess) && !preg_match('/-13-/', $EmpAccess)&& !preg_match('/-11-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

if(isset($_POST["ProductIDSell"]) && trim($_POST["ProductIDSell"]) && floatval($_POST["SellQTY"]) && floatval($_POST["unitPrice"])){
    // insert to orderitem
    $SetDate=explode("/", $_POST['sellDate']);
    $setSellDate=mktime(date("H", time()), date("i", time()), 0, $SetDate[1], $SetDate[0], $SetDate[2]);
    //$setSellDate=time();
    $ProductInfo=explode("**", $_POST["ProductIDSell"]);
    $_POST["SellQTY"]=preg_replace("/,/", "", $_POST["SellQTY"]);
    $_POST["unitPrice"]=preg_replace("/,/", "", $_POST["unitPrice"]);
    $TotalSell=round($_POST["SellQTY"]*$_POST["unitPrice"], 2);
    if(!intval($_POST["EditSell"])){
        $sqlProdName="SELECT products.Name, NoVatQTY, UnitUsed from ".$db_name.".products where ProductID=".intval($ProductInfo[0]).";";
        $rsProdName=mysql_query($sqlProdName);
        $ProdName=mysql_fetch_row($rsProdName);

        //$QTYset=round($_POST["SellQTY"] * $ProdName[2], 2);
        $QTYset=round($_POST["SellQTY"], 2);
        $sqlInsert="INSERT INTO ".$db_name.".orderitems (ProductID, QTY, UnitPrice, PaidBy, PaidDate, Note, SellBy, FromStock) VALUES (".intval($ProductInfo[0]).", '".floatval($_POST["SellQTY"])."', '".floatval($_POST["unitPrice"])."', '".mysql_real_escape_string(trim($_POST['PaidType']))."', ".intval($setSellDate).", '".mysql_real_escape_string(trim($_POST["SellNote"]))."', ".intval($_POST['SellBy']).", ".intval($_POST["FromStock"]).");";
        $rsInsert=mysql_query($sqlInsert);
        $SellID=mysql_insert_id($Conn);
        if($_POST['PaidType']=='เงินสด'){
            // insert to account_daily
            $sqlInsert="INSERT INTO ".$db_name.".account_daily (Type, Name, Total, PaidTo, PaidDate, Note, BookCodeNo, ForActionID) VALUES (1, 'ขายสินค้า', '".floatval($TotalSell)."', '".intval($_POST['SellBy'])."', '".$setSellDate."', 'ขายสินค้า (".$ProdName[0].")', '', ".$SellID.");";
            $rsInsert=mysql_query($sqlInsert);
        }

        if($ProdName[1] >= $QTYset){
            $updateVatQTY="update products set NoVatQTY=NoVatQTY-".floatval($QTYset)." where ProductID=".intval($ProductInfo[0]).";";
            $rsUpdateVat=mysql_query($updateVatQTY);
            $SetVatQTY=0;
            $SetNoVatQTY=$QTYset;
        }
        else{
            $updateVatQTY="update products set NoVatQTY=0 where ProductID=".intval($ProductInfo[0]).";";
            $rsUpdateVat=mysql_query($updateVatQTY);
            $SetNoVatQTY=round($ProdName[1] * $ProdName[2], 2); // vat QTY = สินค้าทั้งหมดทีต้องคำนวณ vat
            $SetVatQTY=($QTYset-$SetNoVatQTY);
        }
        $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID, ServiceID, VatQTY, NoVatQTY) VALUES (".$_POST['FromStock'].", ".intval($ProductInfo[0]).", ".$setSellDate.", ".$QTYset*(-1).", 'ขายสินค้า', ".intval($UserID).", ".($SellID*(-1)).", ".floatval($SetVatQTY).", ".floatval($SetNoVatQTY).");";
        $rsStockHistory=mysql_query($sqlStockHistory);

        $sqlStock="UPDATE ".$db_name.".product_stock set QTY=QTY-".floatval($QTYset)." where ProductID=".intval($ProductInfo[0])." and StockID=".$_POST['FromStock'].";";
        $rsStock=mysql_query($sqlStock);
        reInvoiceNo($SetDate[1], $SetDate[2]);
        header('location: sellproducts.php?completed=1');
    }
    else{
        $SellID=$_POST["EditSell"];
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

        $sqlProdName="SELECT products.Name, NoVatQTY, UnitUsed from ".$db_name.".products where ProductID=".intval($ProductInfo[0]).";";
        $rsProdName=mysql_query($sqlProdName);
        $ProdName=mysql_fetch_row($rsProdName);

        $sqlInsert="UPDATE ".$db_name.".orderitems set ProductID=".intval($ProductInfo[0]).", QTY='".floatval($_POST["SellQTY"])."', UnitPrice='".floatval($_POST["unitPrice"])."', PaidBy='".mysql_real_escape_string(trim($_POST['PaidType']))."', Note='".mysql_real_escape_string(trim($_POST["SellNote"]))."', SellBy=".intval($_POST['SellBy']).", FromStock=".intval($_POST["FromStock"])." where ID=".intval($_POST["EditSell"]).";";
        $rsInsert=mysql_query($sqlInsert);

        if($_POST['PaidType']=='เงินสด'){
            // update to account_daily
            $sqlInsert="UPDATE ".$db_name.".account_daily set Total='".floatval($TotalSell)."', PaidTo='".intval($_POST['SellBy'])."', Note='ขายสินค้า (".$ProdName[0].")' where account_daily.ForActionID=".intval($SellID).";";
            $rsInsert=mysql_query($sqlInsert);
        }
        else{
            $sqlDel="Delete from ".$db_name.".account_daily where account_daily.ForActionID=".intval($SellID).";";
            $rsDel=mysql_query($sqlDel);
        }

        //$QTYset=round($_POST["SellQTY"] * $ProdName[2], 2);
        $QTYset=round($_POST["SellQTY"], 2);
        if($ProdName[1] >= $QTYset){
            $updateVatQTY="update products set NoVatQTY=NoVatQTY-".floatval($QTYset)." where ProductID=".intval($ProductInfo[0]).";";
            $rsUpdateVat=mysql_query($updateVatQTY);
            $SetVatQTY=0;
            $SetNoVatQTY=$QTYset;
        }
        else{
            $updateVatQTY="update products set NoVatQTY=0 where ProductID=".intval($ProductInfo[0]).";";
            $rsUpdateVat=mysql_query($updateVatQTY);
            $SetNoVatQTY=round($ProdName[1] * $ProdName[2], 2); // vat QTY = สินค้าทั้งหมดทีต้องคำนวณ vat
            $SetVatQTY=($QTYset-$SetNoVatQTY);
        }

        $sqlStockHistory="UPDATE ".$db_name.".product_history set StockID= ".$_POST['FromStock'].", ProductID=".intval($ProductInfo[0]).", QTY=".$QTYset*(-1).", VatQTY=".floatval($SetVatQTY).", NoVatQTY=".floatval($SetNoVatQTY)." where ServiceID=".intval($SellID)*(-1).";";
        $rsStockHistory=mysql_query($sqlStockHistory);
        reInvoiceNo($SetDate[1], $SetDate[2]);

        // update new product stock
        $sqlStock="UPDATE ".$db_name.".product_stock set QTY=QTY-".floatval($QTYset)." where ProductID=".intval($ProductInfo[0])." and StockID=".$_POST['FromStock'].";";
        $rsStock=mysql_query($sqlStock);
        $_REQUEST["completed"]=1;
        if(isset($_REQUEST["back"]) && $_REQUEST["back"]){
            $BackURL=$_REQUEST["back"].'.php?completed=1';
            if(isset($_REQUEST["back2Page"]) && $_REQUEST["back2Page"]){
                $BackURL.="&back=".$_REQUEST["back2Page"];
            }
            if(isset($_REQUEST["report"]) && $_REQUEST["report"]){
                $BackURL.="&report=".$_REQUEST["report"];
            }
            else if(isset($_REQUEST["ShowDetails"]) && $_REQUEST["ShowDetails"]){
                $BackURL.="&ShowDetails=".$_REQUEST["ShowDetails"];
            }
            if(isset($_REQUEST["payDate"]) && $_REQUEST["payDate"]){
                $BackURL.="&payDate=".$_REQUEST["payDate"];
            }
            header('location: '.$BackURL);
        }
    }
}
$alertTxt='';
if(isset($_REQUEST["completed"]) && intval($_REQUEST["completed"])){
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลเรียบร้อยแล้ว</div>';
}
include("header.php");
if(!isset($_REQUEST["EditSell"])){
    $_REQUEST["EditSell"]=0;
}
$sqlInfo="select ProductID, QTY, UnitPrice, PaidBy, PaidDate, Note, SellBy, FromStock from ".$db_name.".orderitems where ID=".intval($_REQUEST["EditSell"]).";";
$rsInfo=mysql_query($sqlInfo);
$SellInfo=mysql_fetch_row($rsInfo);
if(!isset($_REQUEST["payDate"])){ $_REQUEST["payDate"]=0; }
?>
<section class="pageContent">
    <form action="sellproducts.php" onsubmit="javascript:return sellFoemCheck();" method="post" class="form-horizontal" role="form" autocomplete="off">
        <input type="hidden" name="EditSell" value="<?php print($_REQUEST["EditSell"]); ?>">
        <input type="hidden" name="payDate" value="<?php print($_REQUEST["payDate"]); ?>">
        <div class="title-body">
            <h2>ขายสินค้า <?php if(intval($_REQUEST["EditSell"])){ print(" <span style=\"color:red;\">(แก้ไขรายการ)</span>"); } ?></h2>
        </div>

        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <table width="100%" style="border-collapse:separate; border-spacing:10px;">
                        <tr><td align="right">
                            <strong>วันที่:</strong>&nbsp;&nbsp;</td><td><input type="text" class="form-control inline_input Calendar" name="sellDate" id="sellDate" value="<?php print(date("d/m/Y", time())); ?>" style="width:100px;">
                        </td></tr>
                        <tr><td width="30%" align="right"><strong>ขายโดย:</strong>&nbsp;&nbsp;</td><td><select name="SellBy" class="form-control inline_input" style="width:270px;">
                        <?php
                            $sqlEmployee="SELECT CONCAT(FirstName, ' ', LastName), EmpID from ".$db_name.".employee where Deleted=0 and EmpID!=1 order by 1 ASC;";
                            $rsEmployee=mysql_query($sqlEmployee);
                            while($Employee=mysql_fetch_row($rsEmployee)){
                                print("<option value=\"".$Employee[1]."\"");
                                if(isset($SellInfo[6]) && $SellInfo[6]==$Employee[1]){
                                    print(" selected");
                                }
                                print(">".$Employee[0]."</option>");
                            }
                        ?></select></td></tr>

                        <tr><td align="right">
                            <strong>ขายจาก:</strong>&nbsp;&nbsp;</td>
                            <td><select name="FromStock" class="form-control" style="width:140px;" onchange="javascript:setProductIDSell(this.value);">
                            <?php
                                $sqlStock="select stock.ID, StockName, sum(product_stock.QTY) as StockQTY from (".$db_name.".stock inner join ".$db_name.".product_stock on product_stock.StockID=stock.ID) inner join ".$db_name.".products on product_stock.ProductID=products.ProductID where stock.Deleted=0 and products.Deleted=0 group by stock.ID having StockQTY>0 order by stock.ID ASC;";
                                $rsStock=mysql_query($sqlStock);
                                while($Stock=mysql_fetch_row($rsStock)){
                                    $ProductOpt[$Stock[0]]="";
                                    print('<option value="'.$Stock[0].'"');
                                    if(!isset($SellInfo[7])){
                                        $SellInfo[7]=$Stock[0];
                                    }
                                    if($SellInfo[7]==$Stock[0]){
                                        print(" selected");
                                    }
                                    print('>'.$Stock[1].'</option>');
                                }
                            ?>
                            </select></td>
                        </tr>

                        <?php
                            $specialCond="";
                            $PrintQTY="";
                            if(!preg_match('/-14-/', $EmpAccess) && $UserID!=1){
                                $specialCond=" and Special=0";
                            }
                            $sqlCust="SELECT products.ProductID, Code, products.Name, SellPrice, product_unit.Name, sum(product_stock.QTY) as ProductQTY, product_stock.StockID from (".$db_name.".products inner join ".$db_name.".product_unit on product_unit.ID=products.UnitNameID) inner join ".$db_name.".product_stock on product_stock.ProductID=products.ProductID where products.Deleted=0".$specialCond." and Type='สินค้า' group by products.ProductID, product_stock.StockID having ProductQTY>0 order by products.Name ASC;";
                            $rsCust=mysql_query($sqlCust);
                            while($CustInfo=mysql_fetch_row($rsCust)){
                                if(!isset($ProductOpt[$CustInfo[6]])){ $ProductOpt[$CustInfo[6]]=""; }
                                $ProductOpt[$CustInfo[6]].="<option value=\"".$CustInfo[0]."**".$CustInfo[3]."**".$CustInfo[4]."**".$CustInfo[5]."\"";
                                if(isset($SellInfo[0]) && $SellInfo[0]==$CustInfo[0]){
                                    $ProductOpt[$CustInfo[6]].=" selected";
                                    $PrintQTY="มีสินค้าในสต็อก ".$CustInfo[5]." ".$CustInfo[4];
                                }
                                $ProductOpt[$CustInfo[6]].=">".$CustInfo[2]."</option>";
                            }
                            foreach($ProductOpt as $key => $value) {
                                print("<select id=\"Stock-".$key."\" style=\"display:none;\"><option value=\"\">กรุณาเลือกสินค้า</option>".$value."</select>");
                            }
                        ?>

                        <tr><td width="30%" align="right" valign="top" style="padding-top:5px;"><strong>ชื่อสินค้า:</strong>&nbsp;&nbsp;</td><td><select name="ProductIDSell" id="ProductIDSell" class="form-control inline_input" style="width:270px;" onchange="javascript:selectProductSell(this.value);"><option value="">กรุณาเลือกสินค้า</option><?php print($ProductOpt[$SellInfo[7]]); ?></select><p id="PrintQTY" style="margin:5px 0 0;"><?php print($PrintQTY); ?></p></td></tr>

                        <tr><td align="right">
                            <strong>จำนวนสินค้า:</strong>&nbsp;&nbsp;</td><td><input type="text" class="form-control inline_input number" name="SellQTY" id="SellQTY" value="<?php if(isset($SellInfo[1])){ print($SellInfo[1]); } ?>" style="width:100px;" onchange="javascript:findTotalsell();">&nbsp;<span id="UnitNameSelected"></span>
                        </td></tr>

                        <tr><td align="right">
                            <strong>ราคาต่อหน่วย:</strong>&nbsp;&nbsp;</td><td><input type="text" class="form-control inline_input price" name="unitPrice" id="unitPrice" value="<?php if(isset($SellInfo[2])){ print($SellInfo[2]); } ?>" onchange="javascript:findTotalsell();" style="width:100px;"> บาท
                        </td></tr>

                        <tr><td align="right">
                            <strong>ราคารวม:</strong>&nbsp;&nbsp;</td><td>
                            <span id="TotalPrice"><?php if(isset($SellInfo[1])){ print(number_format(($SellInfo[1]*$SellInfo[2]), 2)); }else{ print("0.00"); } ?></span> บาท
                        </td></tr>

                        <tr><td align="right">
                                <strong>วิธีการชำระเงิน:</strong>&nbsp;&nbsp;
                            </td>
                            <td>
                                <input type="radio" name="PaidType" value="เงินสด"<?php if(!isset($SellInfo[3]) || ($SellInfo[3] && $SellInfo[3]=='เงินสด')){ print(" checked"); } ?>>เงินสด &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" name="PaidType" value="บัตรเครดิต"<?php if(isset($SellInfo[3]) && $SellInfo[3]=='บัตรเครดิต'){ print(" checked"); } ?>>บัตรเครดิต &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </td></tr>

                        <tr><td align="right">
                            <strong>หมายเหตุ:</strong>&nbsp;&nbsp;</td><td><input type="text" class="form-control" name="SellNote" value="<?php if(isset($SellInfo[5])){ print($SellInfo[5]); } ?>">
                    </td></tr>
                    </table>
                    <br>
                    <div class="actionBar right">
                        <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="hidden" name="back" value="<?php if(isset($_REQUEST["back"])){ print($_REQUEST["back"]); } ?>">
                        <input type="hidden" name="report" value="<?php if(isset($_REQUEST["report"])){ print($_REQUEST["report"]); } ?>">
                        <input type="hidden" name="ShowDetails" value="<?php if(isset($_REQUEST["ShowDetails"])){ print($_REQUEST["ShowDetails"]); } ?>">

                        <input type="hidden" id="backPage" value="<?php
                        if(isset($_REQUEST["back"])){
                            print($_REQUEST["back"].".php");
                        }else{
                            print('index.php');
                        }
                        if(isset($_REQUEST["report"]) && $_REQUEST["report"]){
                            print("?report=".$_REQUEST["report"]);
                            if(intval($_REQUEST["payDate"])){
                                print("&payDate=".$_REQUEST["payDate"]);
                            }
                        }
                        else if(isset($_REQUEST["ShowDetails"]) && $_REQUEST["ShowDetails"]){
                            print("?ShowDetails=".$_REQUEST["ShowDetails"]);
                        }
                        ?>">
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="reset" class="btn btn-danger btn-rounder">ล้างข้อมูล</button>
                        &nbsp;&nbsp;&nbsp;
                        <?php
                        if(isset($_REQUEST["back2Page"]) && $_REQUEST["back2Page"]){
                            print('<input type="hidden" name="back2Page" value="'.$_REQUEST["back2Page"].'">');
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<?php
include("footer.php");
?>