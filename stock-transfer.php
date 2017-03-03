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
$canSubmit=0;
if(isset($_REQUEST["TimeForCheck"]) && (!isset($_COOKIE["TimeCheck"]) || $_COOKIE["TimeCheck"]!=$_REQUEST["TimeForCheck"])){
    setcookie("TimeCheck", $_REQUEST["TimeForCheck"], 0, "/");
    $_COOKIE["TimeCheck"]=$_REQUEST["TimeForCheck"];
    $canSubmit=1;
}
if($canSubmit && isset($_POST["MoveQTY"])){
    $SetDate=explode("/", $_REQUEST['BuyDate']);
    $ImportDate=mktime(date("H", time()), date("i", time()), 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    foreach ($_POST["MoveQTY"] as $key => $value) {
        if($value){
            $sqlStock="SELECT QTY from ".$db_name.".product_stock where ProductID=".intval($key)." and StockID=".intval($_POST["MoveFromStock"]).";";
            $rsStock=mysql_query($sqlStock);
            $InStock=mysql_fetch_row($rsStock);
            if($InStock[0]>=$value){
                $sqlStock="UPDATE ".$db_name.".product_stock set QTY=QTY-".floatval($value)." where ProductID=".intval($key)." and StockID=".intval($_POST['MoveFromStock']).";";
                $rsStock=mysql_query($sqlStock);
                $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, RoundNo, QTY, ChangeNote, UserID, RequestBy) VALUES (".intval($_POST['MoveFromStock']).", ".intval($key).", ".$ImportDate.", ".intval($_POST["RoundNo"]).", ".(floatval($value)*(-1)).", 'เบิกของออก', ".intval($UserID).", ".intval($_POST["RequestBy"]).");";
                $rsStockHistory=mysql_query($sqlStockHistory);

                $sqlStock="UPDATE ".$db_name.".product_stock set QTY=QTY+".floatval($value)." where ProductID=".intval($key)." and StockID=".intval($_POST['MoveToStock']).";";
                $rsStock=mysql_query($sqlStock);
                $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, RoundNo, QTY, ChangeNote, UserID, RequestBy) VALUES (".intval($_POST['MoveToStock']).", ".intval($key).", ".($ImportDate+1).", ".intval($_POST["RoundNo"]).", ".floatval($value).", 'รับของเข้า', ".intval($UserID).", ".intval($_POST["RequestBy"]).");";
                $rsStockHistory=mysql_query($sqlStockHistory);
            }
            else{
                $sqlUnitName="SELECT Name from ".$db_name.".products where ProductID=".intval($key).";";
                $rsUnitName=mysql_query($sqlUnitName);
                $UnitName=mysql_fetch_row($rsUnitName);
                $alertTxt.="<p class=\"red text-center\">ไม่สามารถเบิก ".$UnitName[0]." ได้ในจำนวนที่ระบุ</p>";
            }
        }
    }
    if($alertTxt){
        $alertTxt='<div style="margin-bottom:30px;">'.$alertTxt.'</div>';
    }
    else{
        $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>บันทึกข้อมูลการเบิกของเรียบร้อยแล้ว.</div>';
    }
    //header('location: stock.php?transferSuccess=1&page='.$_REQUEST['page']);
}
else if(isset($_POST["getInStock"]) && intval($_POST["getInStock"])){
    $sqlUnitName="SELECT product_unit.Name from ".$db_name.".products left join ".$db_name.".product_unit on UnitNameID=product_unit.ID where ProductID=".intval($_POST['getInStock']).";";
    $rsUnitName=mysql_query($sqlUnitName);
    $UnitName=mysql_fetch_row($rsUnitName);

    $sqlStock="SELECT QTY from ".$db_name.".product_stock where ProductID=".intval($_POST['getInStock'])." and StockID=".intval($_POST["OnStock"]).";";
    $rsStock=mysql_query($sqlStock);
    $InStock=mysql_fetch_row($rsStock);
    print($InStock[0]."**".$UnitName[0]);
    exit();
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
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>เบิกสินค้า</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form action="stock-transfer.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <input type="hidden" name="TimeForCheck" value="<?php print(time()); ?>">
                        <?php print($alertTxt); ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">วันที่:</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control Calendar" name="BuyDate" value="<?php print(date("d/m/Y", time())); ?>">
                            </div>
                            <label class="col-sm-1 control-label">กะ:</label>
                            <div class="col-sm-1">
                                <select name="RoundNo" class="form-control">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">เบิกโดย:</label>
                            <div class="col-sm-7">
                                <select name="RequestBy" class="form-control" style="width:170px;">
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
                            <label class="col-sm-3 control-label">เบิกจาก:</label>
                            <div class="col-sm-2">
                                <select name="MoveFromStock" id="MoveFromStock" class="form-control">
                                <?php
                                    print($StockOption);
                                ?>
                                </select>
                            </div>
                            <label class="col-sm-1 control-label">ไปยัง:</label>
                            <div class="col-sm-2">
                                <select name="MoveToStock" id="MoveToStock" class="form-control">
                                <?php
                                    print($StockOption);
                                ?>
                                </select>
                            </div>
                        </div>

                        <br>
                        <div class="form-group">
                            <div class="col-sm-12 floatright">
                        <?php
                        $count=0;
                        $sqlProduct="SELECT ProductID, Code, Name, UnitNameID from ".$db_name.".products where Type='สินค้า' and Deleted=0 order by Code ASC";
                        $rsProduct=mysql_query($sqlProduct);
                        print('<table style="width:70%; margin:0 0 0 10%;" class="td_center table table-condensed table-striped table-default table_border">
                            <tr><th>ชื่อสินค้า</th><th>จำนวนที่เบิก</th></tr>');
                        while($Product=mysql_fetch_row($rsProduct)){
                            print('<tr><td style="text-align:left;">&nbsp;'.$Product[1].' -- '.$Product[2].'</td>
                                    <td>
                                        <input type="text" class="form-control" name="MoveQTY['.$Product[0].']" id="MoveQTY-'.$Product[0].'" value="" style="margin:0 auto; width:85px; text-align:center;">
                                    </td>
                                   </tr>');
                        }
                        print('</table>');
                        ?>
                        </div></div>

                        <div class="actionBar" style="text-align:center;">
                            <!-- <input type="hidden" id="MaxInstock" name="MaxInstock" value="<?php //print($InStock[0]); ?>"> -->
                            <input type="hidden" id="page" name="page" value="<?php print($_REQUEST['page']); ?>">
                            <input type="hidden" id="submitTo" value="stock-transfer.php">
                            <input type="hidden" id="backPage" value="stock.php<?php if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
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