<?php
include("dbvars.inc.php");
if(!preg_match('/-1-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

function findMax(){
    global $db_name, $InvoiceBegin;
    $sqlMaxInv="select MAX(InvCode) from ".$db_name.".invoices;";
    $rsMaxInv=mysql_query($sqlMaxInv);
    $MaxInv=mysql_fetch_row($rsMaxInv);
    $MaxCode=($MaxInv[0]+1);
    if($MaxCode < $InvoiceBegin){
        $MaxCode = $InvoiceBegin;
    }
    return $MaxCode;
}

function getCustInfo($getCustInfo, $CustPhone, $CarCodeInv, $InvoiceNo){
    global $db_name;
    if(trim($getCustInfo)){
        $sqlCust="SELECT CustID, Address1, Address2, TaxCode, BranchCode, Tel, CustName, Address3, Address4 from ".$db_name.".customer where CustName='".mysql_real_escape_string(trim($getCustInfo))."' and Deleted=0 order by CustName ASC;";
    }
    else if($CustPhone){
        $sqlCust="SELECT CustID, Address1, Address2, TaxCode, BranchCode, Tel, CustName, Address3, Address4 from ".$db_name.".customer where Tel='".mysql_real_escape_string(trim($CustPhone))."' and Deleted=0 order by CustName ASC;";
    }
    else if(trim($CarCodeInv)){
        $sqlCar4Cust="SELECT CustomerID from ".$db_name.".customer_car where CarCode='".mysql_real_escape_string(trim($CarCodeInv))."';";
        $rsCar4Cust=mysql_query($sqlCar4Cust);
        $Car4Cust=mysql_fetch_row($rsCar4Cust);
        if($Car4Cust[0]){
            $sqlCust="SELECT CustID, Address1, Address2, TaxCode, BranchCode, Tel, CustName, Address3, Address4 from ".$db_name.".customer where CustID=".$Car4Cust[0]." and Deleted=0 order by CustName ASC;";
        }
    }
    if(isset($sqlCust)){
        $rsCust=mysql_query($sqlCust);
        $CustInfo=mysql_fetch_row($rsCust);
        if(!$CustInfo[0]){
            $CustInfo[4]='';
        }
        else if($CustInfo[4] == 'ไม่ระบุ'){
            $CustInfo[4]='';
        }
        $DetailList="";
        if(isset($CustInfo[0]) && trim($CustInfo[0])){
            print('##CustID##'.$CustInfo[0]);
            print('##CustName##'.$CustInfo[6]);
            print('##Address1##'.$CustInfo[1]);
            print('##Address2##'.$CustInfo[2]);
            print('##TaxCode##'.$CustInfo[3]);
            print('##BranchCode##'.$CustInfo[4]);
            print('##Address3##'.$CustInfo[7]);
            print('##Address4##'.$CustInfo[8]);
        }
    }
    else{
        print('Fail');
    }
}

if(isset($_REQUEST['getCustInfo'])){
    getCustInfo($_REQUEST['getCustInfo'], $_REQUEST['CustPhone'], $_REQUEST['CarCodeInv'], $_REQUEST['InvoiceNo']);
    exit();
}
else if(isset($_POST['OilSelected'][1]) && (isset($_POST['OilTotal'][1])) && ($_POST['OilTotal'][1]>0)){
    // check cust name
    $sqlCheck="select CustID from ".$db_name.".customer where CustName='".mysql_real_escape_string(trim($_POST["CustomerName"]))."';";
    $rsCheck=mysql_query($sqlCheck);
    $Check=mysql_fetch_row($rsCheck);
    if(intval($Check[0])){
        $_POST["CustID"]=$Check[0];
    }

    if($_POST['BranchType'] == 'สำนักงานใหญ่'){
        $_POST['BranchCodeNo']='สำนักงานใหญ่';
    }
    if(!intval($_POST["CustID"]) && trim($_POST["CustomerName"])){
        $sqlInsert="INSERT INTO ".$db_name.".customer (CustName, Address1, Address2, Address3, Address4, Tel, TaxCode, BranchCode, CreditLock, CreditLimit, CreditTerm, SpecialTerm, DayBeforePay, UnofficialBalance, CheckCarCode, FromService) VALUES ('".mysql_real_escape_string(trim($_POST["CustomerName"]))."', '".mysql_real_escape_string(trim($_POST["Address1"]))."', '".mysql_real_escape_string(trim($_POST["Address2"]))."', '".mysql_real_escape_string(trim($_POST["Address3"]))."', '".mysql_real_escape_string(trim($_POST["Address4"]))."', '".mysql_real_escape_string(trim($_POST['CustTel']))."', '".mysql_real_escape_string(trim($_POST["TaxCode"]))."', '".mysql_real_escape_string(trim($_POST['BranchCodeNo']))."', 0, '0.00', 0, 0, 0, '0.00', 0, 4);";
        $rsInsert=mysql_query($sqlInsert);
        $_POST["CustID"]=mysql_insert_id($Conn);
    }
    else if(intval($_POST["CustID"])){
        $sqlUpdate="UPDATE ".$db_name.".customer SET CustName='".mysql_real_escape_string(trim($_POST["CustomerName"]))."', Address1='".mysql_real_escape_string(trim($_POST["Address1"]))."', Address2='".mysql_real_escape_string(trim($_POST["Address2"]))."', Address3='".mysql_real_escape_string(trim($_POST["Address3"]))."', Address4='".mysql_real_escape_string(trim($_POST["Address4"]))."', Tel='".mysql_real_escape_string(trim($_POST['CustTel']))."', TaxCode='".mysql_real_escape_string(trim($_POST["TaxCode"]))."', BranchCode='".mysql_real_escape_string(trim($_POST['BranchCodeNo']))."', CheckCarCode=0 where CustID=".intval($_POST["CustID"]).";";
        $rsUpdate=mysql_query($sqlUpdate);
    }

    if(isset($_POST["CarCode"]) && trim($_POST["CarCode"])){
        $sqlCustID="select CarID from ".$db_name.".customer_car where CarCode='".mysql_real_escape_string(trim($_POST["CarCode"]))."' and CustomerID=".intval($_POST["CustID"]).";";
        $rsCustID=mysql_query($sqlCustID);
        $getCustomerID=mysql_fetch_row($rsCustID);
        if(!$getCustomerID[0] && !preg_match("#ใส่ถัง#", $_POST["CarCode"])){
            $sqlCustomerCar="INSERT INTO ".$db_name.".customer_car (CustomerID, CarCode) VALUES (".intval($_POST["CustID"]).", '".mysql_real_escape_string(trim($_POST["CarCode"]))."');";
            $rsCustomerCar=mysql_query($sqlCustomerCar);
        }
    }


    $MaxCode=findMax();
    $ownCompany='<strong class="company-name">'.$CompanyName.'</strong><br>
    '.nl2br($CompanyAddress).'<br>โทร '.$CompanyPhone.'&nbsp;&nbsp; แฟกซ์ '.$CompanyFax.'<br>
    เลขประจำตัวผู้เสียภาษี &nbsp;'.$CompanyCode;

    if($_POST['BranchType'] != 'สำนักงานใหญ่'){
        $_POST['BranchCodeNo']='เลขที่สาขา '.$_POST['BranchCodeNo'];
    }
    if(!$_POST['EditInv']){
        $sqlInsert="INSERT INTO ".$db_name.".invoices (InvDate, InvCode, CompanyDetail, CustID, CustName, CustAddress1, CustAddress2, CustAddress3, CustAddress4, CustTaxCode, CustBranchCode, CustCarCode, CustPhone, InvNote) VALUES (".time().", '".$MaxCode."', '".mysql_real_escape_string(trim($ownCompany))."', ".intval($_POST['CustID']).", '".mysql_real_escape_string(trim($_POST['CustomerName']))."', '".mysql_real_escape_string(trim($_POST['Address1']))."', '".mysql_real_escape_string(trim($_POST['Address2']))."', '".mysql_real_escape_string(trim($_POST['Address3']))."', '".mysql_real_escape_string(trim($_POST['Address4']))."', '".mysql_real_escape_string(trim($_POST['TaxCode']))."', '".mysql_real_escape_string(trim($_POST['BranchCodeNo']))."', '".mysql_real_escape_string(trim($_POST['CarCodeInv']))."', '".mysql_real_escape_string(trim($_POST['CustTel']))."', '".mysql_real_escape_string(trim($_POST["InvNote"]))."');";
        $rsInsert=mysql_query($sqlInsert);
        $InvID=mysql_insert_id($Conn);
    }
    else{
        $sqlUpdate="UPDATE ".$db_name.".invoices SET CustID=".intval($_POST["CustID"]).", CustName='".mysql_real_escape_string(trim($_POST['CustomerName']))."', CustAddress1='".mysql_real_escape_string(trim($_POST['Address1']))."', CustAddress2='".mysql_real_escape_string(trim($_POST['Address2']))."', CustAddress3='".mysql_real_escape_string(trim($_POST['Address3']))."', CustAddress4='".mysql_real_escape_string(trim($_POST['Address4']))."', CustTaxCode='".mysql_real_escape_string(trim($_POST['TaxCode']))."', CustBranchCode='".mysql_real_escape_string(trim($_POST['BranchCodeNo']))."', CustCarCode='".mysql_real_escape_string(trim($_POST['CarCodeInv']))."', PrintNum=".intval($_POST['PrintNum']).", CustPhone='".mysql_real_escape_string(trim($_POST['CustTel']))."', InvNote='".mysql_real_escape_string(trim($_POST["InvNote"]))."' WHERE invoices.InvID=".intval($_POST['EditInv']).";";
        $rsUpdate=mysql_query($sqlUpdate);
        $InvID=intval($_POST['EditInv']);

        $sqlDelete="DELETE FROM ".$db_name.".invoice_detail WHERE invoice_detail.InvID=".intval($_POST['EditInv']).";";
        $rsDelete=mysql_query($sqlDelete);
    }

    for($i=1; $i<=10; $i++){
        if(($_POST['OilSelected'][$i]!=(-1)) && ($_POST['OilTotal'][$i]>0)){
            $DateTimeArr=explode("***", $_POST['DateTime']);
            $ProductInfo=explode("~", $_POST['OilSelected'][$i]);
            if($ProductInfo[0]=="O"){
                $sqlOil="SELECT Name from ".$db_name.".oil where OilID=".intval($ProductInfo[1]).";";
                $rsOil=mysql_query($sqlOil);
                $Oil=mysql_fetch_row($rsOil);
                $ProductName=$Oil[0];

                $sqlOilPrice="SELECT Prices from ".$db_name.".oil_price where OilID=".intval($ProductInfo[1])." and RecordDate='".$DateTimeArr[0]."' and RecordTime='".$DateTimeArr[1]."';";
                $rsOilPrice=mysql_query($sqlOilPrice);
                $OilPrice=mysql_fetch_row($rsOilPrice);
                $OilNo=$ProductInfo[1];
                $ProductNo=0;
                $ProductQTY=0;
            }
            else{
                $sqlOilPrice="SELECT SellPrice, Name from ".$db_name.".products where ProductID=".intval($ProductInfo[1]).";";
                $rsOilPrice=mysql_query($sqlOilPrice);
                $OilPrice=mysql_fetch_row($rsOilPrice);
                $OilNo=0;
                $ProductNo=$ProductInfo[1];
                $ProductQTY=$_REQUEST["inputQTY"][$i];
                $ProductName=$OilPrice[1];
            }

            $_POST["OilTotal"][$i]=preg_replace("/,/", "", $_POST["OilTotal"][$i]);
            $sqlInsert="INSERT INTO ".$db_name.".invoice_detail (InvID, Name, UnitPrice, Total, OrderNo, OilID, ProductID, ProductQTY) VALUES (".intval($InvID).", '".mysql_real_escape_string(trim($ProductName))."', '".$OilPrice[0]."', '".$_POST['OilTotal'][$i]."', ".$i.", ".intval($OilNo).", ".intval($ProductNo).", ".floatval($ProductQTY).");";
            $rsInsert=mysql_query($sqlInsert);
        }
    }
    header('location: invoice.php?print='.$InvID);
}


$Tel4Invoice='';
$sqlCust="SELECT Tel from ".$db_name.".customer where Deleted=0 and CustID>0 order by CustName ASC;";
$rsCust=mysql_query($sqlCust);
while($CustInfo=mysql_fetch_row($rsCust)){
    $Tel4Invoice.="*".$CustInfo[0];
}
$Tel4Invoice = substr($Tel4Invoice, 1);

include("header.php");
$ErrorTxt='';
if(isset($_REQUEST["InvoiceCode"]) && intval($_REQUEST["InvoiceCode"])){
    $_POST['printInvoiceCode']=$_REQUEST["InvoiceCode"];
}
if(isset($_POST['printInvoiceCode'])){
    $sqlInvID="SELECT invoices.InvID from ".$db_name.".invoices where InvCode='".mysql_real_escape_string(trim($_POST['printInvoiceCode']))."';";
    $rsInvID=mysql_query($sqlInvID);
    $InvID=mysql_fetch_row($rsInvID);
    if(!$InvID[0]){
        $ErrorTxt='<div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        ไม่พบข้อมูลใบกำกับภาษีเลขที่ <strong>'.$_POST['printInvoiceCode'].'</strong> ในระบบ
                   </div>';
    }
    else{
        $_REQUEST['print']=$InvID[0];
    }
}
if(isset($_REQUEST['back']) && $_REQUEST['back']=='index'){
    $backToPage='index.php';
}
else{
    $backToPage='invoice.php';
    $_REQUEST['back']='invoice';
}


if(trim($ErrorTxt)){
    print(' <section class="pageContent">
                <div class="title-body">
                    <h2>ใบกำกับภาษี</h2>
                </div>
                <div id="pageContent" class="content-center invoice">
                    <div class="panel panel-default invoice_report">
                        <div class="panel-body"><br>'.$ErrorTxt.'
                            <form action="invoice.php" method="post" class="form-horizontal" autocomplete="off">
                                <p>กรุณาระบุข้อมูลใบกำกับภาษีที่ต้องการ</p>
                                <table class="coupon_history">
                                    <tr>
                                        <td style="text-align:right;"><b>เลขที่ใบกำกับภาษี:</b></td>
                                        <td style="text-align:left;"><input type="text" class="form-control invoice_form" name="printInvoiceCode" value="" style="width:300px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:right;"><b>ชื่อบริษัท:</b></td>
                                        <td style="text-align:left;"><input type="text" class="form-control invoice_form" name="printInvoiceByName" value="" style="width:300px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:right;"><b>เบอร์โทร:</b></td>
                                        <td style="text-align:left;"><input type="text" class="form-control invoice_form" name="printInvoiceByTel" value="" style="width:300px;"></td>
                                    </tr>
                                </table>
                                <p style="margin:15px 0 0 350px;"><button type="submit" class="btn btn-success btn-rounder">ตกลง</button></p>
                            </form>
                        </div>
                    </div>
                </div>
            </section>');
}
else if(isset($_REQUEST['print']) && intval($_REQUEST['print'])){
    $InvDetail="";
    $Total=0;
    $countDetail=0;
    $sqlDetail="select Name, UnitPrice, Total from ".$db_name.".invoice_detail where InvID=".intval($_REQUEST['print'])." order by OrderNo ASC;";
    $rsDetail=mysql_query($sqlDetail);
    while($Detail=mysql_fetch_row($rsDetail)){
        $QTY=round($Detail[2]/$Detail[1], 2);
        $InvDetail.='<tr>
                <td width="100"><div class="invoice-quantity text-center">'.number_format($QTY, 2).'</div></td>
                <td width="60%" class="invoice-product">
                    '.$Detail[0].'
                </td>
                <td width="15%"><div class="invoice-cost text-center">'.number_format($Detail[1], 2).'</div></td>
                <td width="15%"><div class="invoice-total text-right">'.number_format($Detail[2], 2).'</div></td>
            </tr>';
        $Total+=round($Detail[2], 2);
        $countDetail++;
    }
    if($countDetail<=10){
        for($j=$countDetail; $j<10; $j++){
            $InvDetail.='<tr>
                <td width="100">&nbsp;</td>
                <td width="60%" class="invoice-product">&nbsp;</td>
                <td width="15%">&nbsp;</td>
                <td width="15%">&nbsp;</td>
            </tr>';
        }
    }
    $vat=round(($Total*7)/107, 2);
    $subTotal=round(($Total-$vat), 2);
    $textSize="font-size:11px;";

    $sqlInv="SELECT InvDate, InvCode, CompanyDetail, CustName, CustAddress1, CustAddress2, CustPhone, CustTaxCode, CustBranchCode, CustCarCode, PrintNum, CustAddress3, CustAddress4, CustPhone, InvNote from ".$db_name.".invoices where InvID=".intval($_REQUEST['print']).";";
    $rsInv=mysql_query($sqlInv);
    $Invoice=mysql_fetch_row($rsInv);

    $PrintNumTxt='';
    if(intval($Invoice[10])>1){
        $PrintNumTxt=' / พิมพ์ครั้งที่ '.$Invoice[10];
    }
    $AddTxt='<br>';
    $AddPrintButton="";
    $AddBackButton="";
    if(!isset($_POST['printInvoiceCode'])){
        $AddTxt='
        <div class="text-center"><br>
            <p>จัดเก็บข้อมูลใบกำกับภาษีเรียบร้อยแล้ว</p>
            <p><a href="invoice.php?AddNew=1">สร้างใบกำกับภาษีใหม่</a></p>
        </div>';
    }
    else{
        $AddPrintButton='&nbsp;&nbsp;<button type="button" class="btn btn-info btn-rounder" onclick="javascript:printInvoice();">พิมพ์ใบกำกับภาษี</button>';
    }
    if(isset($_REQUEST["page"])){
        $AddBackButton='<input type="hidden" id="backPage" value="invoice-report.php?selectedDate='.$_REQUEST['DateBetween'].'"><button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    if(isset($_REQUEST["fromsearch"])){
        $AddBackButton='<button type="button" class="btn btn-inverse btn-rounder" onclick="javascript:history.back(-1);">ย้อนกลับ</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    print('
    <section class="pageContent">
        <div class="title-body">
            <h2>ใบกำกับภาษี</h2>
        </div>
        '.$AddTxt.'
        <div id="pageContent" class="content-center invoice">

            <div class="panel panel-default invoice_report">
                <div class="panel-body"><br>
                    <table border="0" width="100%" style="'.$textSize.'">
                        <tr>
                            <td colspan="2">
                                <table border="0" style="'.$textSize.'"><tr>
                                <td valign="top"><img src="images/new_logo.jpg"></td>
                                <td>&nbsp;&nbsp;&nbsp;</td>
                                <td valign="top">'.$Invoice[2].'</td>
                                </tr></table>
                            </td>
                            <td valign="top" width="30%" nowrap>
                                <p><strong class="invoice-head">ต้นฉบับใบเสร็จรับเงิน/ใบกำกับภาษี</strong></p>
                                <p>เลขที่เอกสาร '.$Invoice[1].$PrintNumTxt.'</p>
                                <p>วันที่ '.date('j/m/Y', $Invoice[0]).'</p>
                            </td>
                        </tr>
                        <tr><td colspan="2">&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td colspan="3" class="customer_info">
                            <table width="100%">
                            <tr>
                                <td><p><strong class="company-name">นามผู้ซื้อ &nbsp;</strong></p></td>
                                <td><p><strong class="company-name">'.trim($Invoice[3]).'</strong>');
                                print('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;เลขประจำตัวผู้เสียภาษี &nbsp;'.$Invoice[7]);
                                if($Invoice[8]){
                                    print(' &nbsp;/ '.$Invoice[8]);
                                }
                                print('</p></td>
                                <td rowspan="7" valign="bottom">');
                                if($Invoice[9]){
                                    print('<p>ทะเบียนรถ '.$Invoice[9].'</p></td>');
                                }
                                else{
                                    print('&nbsp;');
                                }
                                print('</tr>
                                <tr>
                                    <td width="10%"><p>ที่อยู่ &nbsp;</p></td>
                                    <td width="70%"><p>'.$Invoice[4].'</p></td>
                                </tr>');
                                if($Invoice[5]){
                                    print('<tr>
                                        <td>&nbsp;</td>
                                        <td><p>'.$Invoice[5].'</p></td>
                                    </tr>');
                                }
                                if($Invoice[11]){
                                    print('<tr>
                                        <td>&nbsp;</td>
                                        <td><p>'.$Invoice[11].'</p></td>
                                    </tr>');
                                }
                                if($Invoice[12]){
                                    print('<tr>
                                        <td>&nbsp;</td>
                                        <td><p>'.$Invoice[12].'</p></td>
                                    </tr>');
                                }
                                if($Invoice[14]){
                                    print('<tr>
                                        <td><p>หมายเหตุ &nbsp;</p></td>
                                        <td><p>'.$Invoice[14].'</p></td>
                                    </tr>');
                                }
                            print('</table></td></tr>');
                            print('</table><br>

                    <!-- TABLE INVOICE -->
                    <div class="table-responsive" style="clear:both;">
                        <table class="table table-bordered table-default" style="'.$textSize.'">
                            <thead>
                                <tr>
                                    <th><div class="text-center">ปริมาณ</div></th>
                                    <th><div class="text-center">รายการ</div></th>
                                    <th><div class="text-center">หน่วยละ (บาท)</div></th>
                                    <th><div class="text-center">จำนวนเงิน (บาท)</div></th>
                                </tr>
                            </thead>'.$InvDetail.'
                            <tr>
                                <td colspan="3"><div class="in-total">รวมเงิน</div></td>
                                <td><div class="in-total" id="SubTotal">'.number_format($subTotal, 2).'</div></td>
                            </tr>
                            <tr>
                                <td colspan="3"><div class="in-total">ภาษีมูลค่าเพิ่ม (7%)</div></td>
                                <td><div class="in-total" id="Tax">'.number_format($vat, 2).'</div></td>
                            </tr>
                            <tr>
                                <td colspan="3"><div class="in-total">ยอดเงินสุทธิ</div></td>
                                <td><div class="in-total" id="Total">'.number_format($Total, 2).'</div></td>
                            </tr>
                        </table>
                    </div>

                    <br><br>
                    <table style="width:100%;'.$textSize.'">
                        <tr>
                            <td align="center">
                                <p>..................................................................</p>
                                ผู้รับของ
                            </td>

                            <td align="center">
                                <p>..................................................................</p>
                                ผู้รับเงิน
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="actionBar" style="float:right; clear:both;"><br><br>
                    <form action="invoice.php" method="post" name="invoice_print-page">
                    '.$AddBackButton.'
                    <button type="button" class="btn btn-success btn-rounder" onclick="javascript:location.href=\'invoice.php?AddNew=1\';">สร้างใบกำกับภาษีใหม่</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-warning btn-rounder" onclick="javascript:openEditInvForm('.$Invoice[1].');">แก้ไขใบกำกับภาษี</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    '.$AddPrintButton.'
                    </form>
                    <br><br><br>
                </div>
            </div>

        </div>
    </section>');
    if(!isset($_POST['printInvoiceCode'])){
        $PrintInvNow=1;
    }
}
//else if(isset($_REQUEST['AddNew']) && intval($_REQUEST['AddNew'])){
else{
    $SetDateTime=0;
    $sqlOil="SELECT OilID, Name from ".$db_name.".oil where Deleted=0 order by Name ASC;";
    $rsOil=mysql_query($sqlOil);
    while($Oil=mysql_fetch_row($rsOil)){
        $sqlGetOilPrice="SELECT Prices, RecordDate, RecordTime from ".$db_name.".oil_price where OilID=".$Oil[0]." order by RecordDate DESC, RecordTime DESC;";
        $rsGetOilPrice=mysql_query($sqlGetOilPrice);
        $GetOilPrice=mysql_fetch_row($rsGetOilPrice);

        $OilType[$Oil[0]]=$Oil[1];
        $OilPrice[$Oil[0]]=$GetOilPrice[0];
        if(!isset($FirstPrice)){
            $FirstPrice=$GetOilPrice[0];
            $SetDateTime=$GetOilPrice[1]."***".$GetOilPrice[2];;
        }
    }
    $sqlProduct="SELECT ProductID, Name, SellPrice from ".$db_name.".products where Deleted=0 and CanInvoice=1 order by Name ASC;";
    $rsProduct=mysql_query($sqlProduct);
    while($Products=mysql_fetch_row($rsProduct)){
        $ProductNameArr[$Products[0]]=$Products[1];
        $ProductsPrice[$Products[0]]=$Products[2];
    }
    $MaxCode = findMax();
    $CustomerName = '';
    $CarCodeInv = '';
    $Address1 = '';
    $Address2 = '';
    $Address3 = '';
    $Address4 = '';
    $PhoneInv = '';
    $TaxCode = '';
    $InvNote = '';
    $BranchCode = 'สำนักงานใหญ่';
    $invDate = time();
    $OilID = array('', '', '', '');
    $UnitPrice = array('', '', '', '');
    $ProductID = array('', '', '', '');
    $Total = array(0, 0, 0, 0);
    $QTY = array('', '', '', '');
    $subTotalInv = 0;
    $TotalInv = 0;
    $vat = 0;
    $PrintNum = 1;
    $alertText = '';
    if(isset($_POST['editInvoiceCode'])){
        $sqlInvInfo="SELECT CustID, CustAddress1, CustAddress2, CustTaxCode, CustBranchCode, CustPhone, CustName, InvDate, InvCode, CompanyDetail, PrintNum, InvID, CustCarCode, CustAddress3, CustAddress4, InvNote from ".$db_name.".invoices where InvCode='".intval($_POST['editInvoiceCode'])."';";
        $rsInvInfo=mysql_query($sqlInvInfo);
        if(mysql_num_rows($rsInvInfo)){
            $MaxCode=$_POST['editInvoiceCode'];
            $InvInfo=mysql_fetch_row($rsInvInfo);
            $EditCustID=$InvInfo[0];
            $Address1=$InvInfo[1];
            $Address2=$InvInfo[2];
            $TaxCode=$InvInfo[3];
            $BranchCode=preg_replace("/เลขที่สาขา/", "", $InvInfo[4]);
            $PhoneInv=$InvInfo[5];
            $CustomerName=$InvInfo[6];
            $invDate=$InvInfo[7];
            if(isset($_POST['AddPrintNum']) && intval($_POST['AddPrintNum'])){
                $PrintNum=($InvInfo[10]+1);
            }
            else{
                $PrintNum=$InvInfo[10];
            }
            $originInvID=$InvInfo[11];
            $CarCodeInv=$InvInfo[12];
            $Address3=$InvInfo[13];
            $Address4=$InvInfo[14];
            $InvNote=$InvInfo[15];

            $count=1;
            $sqlDetail="select Name, UnitPrice, Total, OilID, ProductID from ".$db_name.".invoice_detail where InvID=".intval($InvInfo[11])." order by OrderNo ASC;";
            $rsDetail=mysql_query($sqlDetail);
            while($Detail=mysql_fetch_row($rsDetail)){
                if($Detail[3]){
                    $OilID[$count]=$Detail[3];
                }
                else{
                    $ProductID[$count]=$Detail[4];
                }
                $UnitPrice[$count]=$Detail[1];
                $Total[$count]=$Detail[2];
                $QTY[$count]=round($Detail[2]/$Detail[1], 1);
                $TotalInv+=round($Total[$count], 2);
                $count++;
            }
            $vat=round(($TotalInv*7)/100, 2);
            $subTotalInv=($TotalInv-$vat);
        }
        else{
            $alertText='<p class="passcode_send-error">ไม่พบใบรับบริการเลขที่ '.$_REQUEST['editInvoiceCode'].'</p>';
        }
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ใบกำกับภาษี</h2>
        </div>

        <div class="content-center invoice">

            <div class="panel panel-default">
                <div class="panel-body">
                    <form onsubmit="javascript:return checkValue();" action="invoice.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                    <input type="hidden" name="CustID" id="CustID" value="<?php if(isset($EditCustID)){ print($EditCustID); } ?>">
                    <input type="hidden" name="EditInv" id="EditInv" value="<?php if(isset($originInvID)){ print($originInvID); } ?>">
                    <input type="hidden" name="PrintNum" value="<?php if(isset($PrintNum)){ print($PrintNum); } ?>">
                    <input type="hidden" name="DateTime" id="DateTime" value="<?php print($SetDateTime); ?>">
                    <?php print($alertText); ?>
                    <div class="col-md-12" style="clear:both;"><br>
                        <table width="100%" border="0">
                            <tr>
                                <td colspan="2" id="companyDetail">
                                    <?php
                                    if(isset($InvInfo[9]) && trim($InvInfo[9])){
                                        print($InvInfo[9]);
                                    }
                                    else{
                                        print('<strong class="company-name">'.$CompanyName.'</strong><br>'.nl2br($CompanyAddress).'<br>โทร '.$CompanyPhone.'&nbsp;&nbsp; แฟกซ์ '.$CompanyFax.'<br><strong>เลขประจำตัวผู้เสียภาษี &nbsp;'.$CompanyCode.'</strong>');
                                    }
                                    ?>
                                </td>
                                <td rowspan="9" width="50">&nbsp;</td>
                                <td valign="top" align="left" width="30%">
                                    <p><strong class="invoice-head">ต้นฉบับใบเสร็จรับเงิน/ใบกำกับภาษี</strong></p>
                                    <p>เลขที่เอกสาร <?php print($MaxCode); ?><span id="PrintNum"><?php if($PrintNum>1){ print(' / พิมพ์ครั้งที่ '.$PrintNum); } ?></span>&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-pencil pointer" data-toggle="modal" data-target="#EditInvoice" title="แก้ไขใบกำกับภาษี"></i></p>
                                    วันที่ <span id="invDate"><?php print(date('j/m/Y', $invDate)); ?></span>
                                </td>
                            </tr>
                            <tr><td colspan="2">&nbsp;</td><td>&nbsp;</td></tr>
                            <tr>
                                <td><p>นามผู้ซื้อ &nbsp;</p></td>
                                <td><p><input type="text" class="form-control invoice_form" name="CustomerName" id="CustomerInv" value="<?php print($CustomerName); ?>"></p></td>
                                <td><p>เบอร์โทร <input type="text" class="form-control invoice_form" id="CustTel" name="CustTel" value="<?php print($PhoneInv); ?>" style="width:150px;"></p></td>
                            </tr>
                            <tr>
                                <td><p>ที่อยู่ &nbsp;</p></td>
                                <td><p><input type="text" class="form-control invoice_form" name="Address1" id="Address1" value="<?php print($Address1); ?>"></p></td>
                                <td rowspan="6" valign="bottom">
                                <p>ทะเบียนรถ <input type="text" class="form-control invoice_form" name="CarCodeInv" id="CarCodeInv" value="<?php print($CarCodeInv); ?>" style="width:100px;"></p></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td><p><input type="text" class="form-control invoice_form" name="Address2" id="Address2" value="<?php print($Address2); ?>"></p></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td><p><input type="text" class="form-control invoice_form" name="Address3" id="Address3" value="<?php print($Address3); ?>"></p></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td><p><input type="text" class="form-control invoice_form" name="Address4" id="Address4" value="<?php print($Address4); ?>"></p></td>
                            </tr>
                            <tr>
                                <td><p>สาขา &nbsp;</p></td>
                                <td><p><input type="radio" class="form-control" id="BranchType1" name="BranchType" value="สำนักงานใหญ่"<?php if($BranchCode=='สำนักงานใหญ่'){ print(" checked"); }?>> สำนักงานใหญ่ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="BranchType2" class="form-control" name="BranchType" value="0"<?php if($BranchCode!='สำนักงานใหญ่'){ print(" checked"); }?>> สาขาลำดับที่ &nbsp;<input type="text" class="form-control inline_input" id="BranchCodeNo" name="BranchCodeNo" value="<?php if($BranchCode!='สำนักงานใหญ่'){ print($BranchCode); } ?>" style="width:70px;"><input type="hidden" id="BranchType4No" value="<?php if($BranchCode=='สำนักงานใหญ่'){ print("0"); }else{ print("1"); }?>"></p></td>
                            </tr>
                            <tr>
                                <td nowrap><p>เลขประจำตัวผู้เสียภาษี &nbsp;</p></td>
                                <td><p><input type="text" class="form-control invoice_form" name="TaxCode" id="TaxCode" value="<?php print($TaxCode); ?>" style="width:150px;"></p></td>
                            </tr>
                            <tr>
                                <td nowrap><p>หมายเหตุ &nbsp;</p></td>
                                <td colspan="3"><p><input type="text" class="form-control invoice_form" name="InvNote" id="InvNote" value="<?php print($InvNote); ?>"></p></td>
                            </tr>
                        </table>
                    </div>

                    <!-- TABLE INVOICE -->
                    <div class="table-responsive" style="clear:both;"><br>
                        <table class="table table-bordered table-default">
                            <thead>
                                <tr>
                                    <th><div class="text-center">ปริมาณ</div></th>
                                    <th><div class="text-center">รายการ</div></th>
                                    <th><div class="text-center">หน่วยละ (บาท) &nbsp;&nbsp; <a href="javascript:selectOtherTime();"><i class="fa fa-history"></i></a></div></th>
                                    <th><div class="text-center">จำนวนเงิน (บาท)</div></th>
                                </tr>
                            </thead>
                            <?php
                            for($i=1; $i<=10; $i++){
                                if(!isset($QTY[$i])){ $QTY[$i]=""; }
                                if(!isset($UnitPrice[$i])){ $UnitPrice[$i]=""; }
                                if(!isset($Total[$i])){ $Total[$i]=0; }
                                print('<tr>
                                    <td width="100"><div id="QTY-'.$i.'" class="invoice-quantity text-center">'.$QTY[$i].'</div></td>
                                    <td width="60%" class="invoice-product">
                                    <select name="OilSelected['.$i.']" id="OilSelected-'.$i.'" class="form-control" style="width:250px;" onchange="javascript:invoiceSet('.$i.', this.value);">');
                                if($i!=1){
                                    print('<option value="O~-1">กรุณาเลือก</option>');
                                }
                                foreach ($OilType as $key => $value) {
                                    if(!isset($OilID[$i])){
                                        $OilID[$i]='';
                                    }
                                    print('<option value="O~'.$key.'"');
                                    if($OilID[$i]==$key){
                                        print(" selected");
                                    }
                                    print('>'.$value.'</option>');
                                }

                                if(isset($ProductNameArr) && count($ProductNameArr)){
                                    foreach($ProductNameArr as $key => $value) {
                                        if(!isset($ProductID[$i])){
                                            $ProductID[$i]='';
                                        }
                                        print('<option value="P~'.$key.'"');
                                        if($ProductID[$i]==$key){
                                            print(" selected");
                                        }
                                        print('>'.$value.'</option>');
                                    }
                                }
                                print('</select>
                                    </td>
                                    <td width="15%"><div id="cost-'.$i.'" class="invoice-cost text-center">');
                                if($UnitPrice[$i]){
                                    print(number_format($UnitPrice[$i], 2));
                                }
                                else if($i==1){
                                    print($FirstPrice);
                                }
                                print('</div></td>
                                    <td width="15%"><div class="invoice-total text-right"><input type="text" class="form-control invoice_form price text-right" name="OilTotal['.$i.']" id="total-'.$i.'" value="'.number_format($Total[$i], 2).'" style="width:100px;" onchange="javascript:invoiceSet('.$i.', 0)"></div></td>
                                </tr>');
                            }
                            ?>
                            <tr>
                                <td colspan="3"><div class="in-total">รวมเงิน</div></td>
                                <td><div class="in-total" id="SubTotal"><?php print(number_format($subTotalInv, 2)); ?></div></td>
                            </tr>
                            <tr>
                                <td colspan="3"><div class="in-total">ภาษีมูลค่าเพิ่ม (7%)</div></td>
                                <td><div class="in-total" id="Tax"><?php print(number_format($vat, 2)); ?></div></td>
                            </tr>
                            <tr>
                                <td colspan="3"><div class="in-total">ยอดเงินสุทธิ</div></td>
                                <td><div class="in-total" id="Total"><?php print(number_format($TotalInv, 2)); ?></div></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-12"><br><br>
                        <div class="col-md-4 text-center">
                        <p>..................................................................</p>
                        ผู้รับของ
                        </div>

                        <div class="col-md-4 text-center">
                        <p>..................................................................</p>
                        ผู้รับเงิน
                        </div>

                        <div class="col-md-4">
                        &nbsp;
                        </div>
                    </div>


                    <div style="float:right; clear:both;"><br><br>
                    <button type="submit" id="PrintInv" class="btn btn-success btn-rounder">พิมพ์ใบกำกับภาษี</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="reset" class="btn btn-danger btn-rounder" onclick="javascript:resetInvoice();">รีเซ็ตข้อมูล</button>
                    </div>
                    </form>
                </div>
            </div>

        </div>
    </section>

    <form action="customer.php" method="post" role="form" id="submitForm" autocomplete="off">
        <input type="hidden" id="UpdateItem" name="UpdateItem" value="0">
        <input type="hidden" id="backPage" name="backPage" value="coupons.php">
    </form>

<?php
    print('
    <input type="hidden" id="WarningNow" value="'.$WarningOilTrigger.'">
    <input type="hidden" id="Date2Warning" value="'.$Date2Check.'">
    <button data-toggle="modal" data-target="#WarningOil" id="OpenWarningOil" style="visibility: hidden;"></button>
    <div class="modal fade" id="WarningOil" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form action="invoice.php" method="post" class="form-horizontal" autocomplete="off">
                <div class="modal-body text-center" id="updateDetails">
                    <br><p><b>ราคาน้ำมันของวันนี้ยังไม่ถูกอัพเดท</b></p><br>
                    <button type="button" class="btn btn-success" onclick="javascript:updatePrice();">อัพเดทราคาน้ำมัน</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="closeThisBox" onclick="javascript:setPriceWarning();">ยังไม่อัพเดทตอนนี้</button>
                    <br>&nbsp;
                </div>
            </form>
        </div>
      </div>
    </div>');

    $DateList="";
    $sqlOilPriceList="select RecordDate, RecordTime from ".$db_name.".oil_price group by RecordDate, RecordTime order by RecordDate DESC, RecordTime DESC LIMIT 5;";
    $rsOilPriceList=mysql_query($sqlOilPriceList);
    $count=1;
    while($OilPriceList=mysql_fetch_row($rsOilPriceList)){
        $List[$count]="";
        $DateArr=explode("-", $OilPriceList[0]);
        $ShowDate=$DateArr[2]." ".$shortMonthList[intval($DateArr[1])]." ".$DateArr[0];
        $DateList.="<div style=\"margin-bottom:10px;\"><a href=\"javascript:setDateNow(".$count.", '".$OilPriceList[0]."***".$OilPriceList[1]."');\">&nbsp;".$ShowDate." &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ตั้งแต่เวลา ".$OilPriceList[1]."</a></div>";
        $sqlGetList="SELECT Prices, OilID from ".$db_name.".oil_price where RecordDate='".$OilPriceList[0]."' and RecordTime='".$OilPriceList[1]."' order by RecordDate DESC, RecordTime DESC;";
        $rsGetList=mysql_query($sqlGetList);
        while($GetList=mysql_fetch_row($rsGetList)){
            $List[$count].=",".$GetList[1]."=".$GetList[0];
        }
        $count++;
    }
    foreach ($List as $key => $value) {
        print("\r\n<input type=\"hidden\" id=\"PriceByDate-".$key."\" value=\"".substr($value, 1)."\">");
    }
    print('
    <button data-toggle="modal" data-target="#selectOilPrice" id="OilPriceButton" style="visibility: hidden;"></button>
    <div class="modal fade" id="selectOilPrice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form action="invoice.php" method="post" class="form-horizontal" autocomplete="off">
                <div class="modal-body text-center" id="updateDetails">
                    <br><p><b>เลือกราคาใหม่</b></p>
                    '.$DateList.'<br>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="closeThisBox2">ยกเลิก</button>
                    <br>&nbsp;
                </div>
            </form>
        </div>
      </div>
    </div>');
}
include("footer.php");
?>