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

if(!isset($_REQUEST['page'])){
	$_REQUEST['page']=1;
}

$ErrorTxt="";
$ItemPerPage=100;
if(isset($_POST['printInvoiceCode']) && trim($_POST['printInvoiceCode'])){
    $sqlInvID="SELECT invoices.InvID, invoices.CustName, InvCode, InvDate from ".$db_name.".invoices where InvCode='".mysql_real_escape_string(trim($_POST['printInvoiceCode']))."'";
    $rsInvID=mysql_query($sqlInvID.";");
    $InvNum=mysql_num_rows($rsInvID);
    if(!$InvNum){
        $ErrorTxt='<div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        ไม่พบข้อมูลใบกำกับภาษีเลขที่ <strong>'.$_POST['printInvoiceCode'].'</strong> ในระบบ
                   </div>';
    }
    $setLink="&search1=".trim($_POST['printInvoiceCode']);
    $TitleText="<p><strong>ผลการค้นหา ใบกำกับภาษีเลขที่ ".$_POST['printInvoiceCode']." พบ ".$InvNum." รายการ</strong></p>";
    $rsInvID=mysql_query($sqlInvID." order by InvCode DESC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";");
}
else if(isset($_POST['printInvoiceByName']) && trim($_POST['printInvoiceByName'])){
	$sqlInvID="SELECT invoices.InvID, invoices.CustName, InvCode, InvDate from ".$db_name.".invoices where invoices.CustName like '%".mysql_real_escape_string(trim($_POST['printInvoiceByName']))."%'";
    $rsInvID=mysql_query($sqlInvID.";");
    $InvNum=mysql_num_rows($rsInvID);
    if(!$InvNum){
        $ErrorTxt='<div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        ไม่พบข้อมูลใบกำกับภาษีของ <strong>'.$_POST['printInvoiceByName'].'</strong> ในระบบ
                   </div>';
    }
    $setLink="&search2=".trim($_POST['printInvoiceByName']);
    $TitleText="<p><strong>ผลการค้นหาจากชื่อบริษัท ".$_POST['printInvoiceByName']." พบ ".$InvNum." รายการ</strong></p>";
    $rsInvID=mysql_query($sqlInvID." order by InvCode DESC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";");
}
else if(isset($_POST['printInvoiceByTel']) && trim($_POST['printInvoiceByTel'])){
	$sqlInvID="SELECT invoices.InvID, invoices.CustName, InvCode, InvDate from ".$db_name.".invoices inner join ".$db_name.".customer on invoices.CustID=customer.CustID where customer.Tel like '%".mysql_real_escape_string(trim($_POST['printInvoiceByTel']))."%'";
    $rsInvID=mysql_query($sqlInvID.";");
    $InvNum=mysql_num_rows($rsInvID);
    if(!$InvNum){
        $ErrorTxt='<div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        ไม่พบข้อมูลใบกำกับภาษีของเบอร์โทร <strong>'.$_POST['printInvoiceByTel'].'</strong> ในระบบ
                   </div>';
    }
    $setLink="&search3=".trim($_POST['printInvoiceByTel']);
    $TitleText="<p><strong>ผลการค้นหาจากเบอร์โทร ".$_POST['printInvoiceByTel']." พบ ".$InvNum." รายการ</strong></p>";
    $rsInvID=mysql_query($sqlInvID." order by InvCode DESC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";");
}
else if(isset($_REQUEST['printInvoiceByCar']) && trim($_REQUEST['printInvoiceByCar'])){
	$sqlInvID="SELECT invoices.InvID, invoices.CustName, InvCode, InvDate from ".$db_name.".invoices where CustCarCode like '%".mysql_real_escape_string(trim($_POST['printInvoiceByCar']))."%'";
    $rsInvID=mysql_query($sqlInvID.";");
    $InvNum=mysql_num_rows($rsInvID);
    if(!$InvNum){
        $ErrorTxt='<div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        ไม่พบข้อมูลใบกำกับภาษีของเบอร์โทร <strong>'.$_POST['printInvoiceByTel'].'</strong> ในระบบ
                   </div>';
    }
    $setLink="&search4=".trim($_POST['printInvoiceByCar']);
    $TitleText="<p><strong>ผลการค้นหาจากทะเบียนรถ ".$_POST['printInvoiceByCar']." พบ ".$InvNum." รายการ</strong></p>";
    $rsInvID=mysql_query($sqlInvID." order by InvCode DESC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";");
}
else{
	$_REQUEST['printInvoiceCode']="";
	$_REQUEST['printInvoiceByName']="";
	$_REQUEST['printInvoiceByTel']="";
	$_REQUEST['printInvoiceByCar']="";
	$InvNum=0;
    $TitleText="";
}

$Tel4Invoice='';
$sqlCust="SELECT Tel from ".$db_name.".customer where Deleted=0 and CustID>0 order by CustName ASC;";
$rsCust=mysql_query($sqlCust);
while($CustInfo=mysql_fetch_row($rsCust)){
    $Tel4Invoice.="*".$CustInfo[0];
}
$Tel4Invoice = substr($Tel4Invoice, 1);
include("header.php");
print(' <section class="pageContent">
	        <div class="title-body">
	            <h2>เรียกดูใบกำกับภาษี</h2>
	        </div>
	        <div id="pageContent" class="content-center invoice">
	            <div class="panel panel-default invoice_report">
	                <div class="panel-body">
	                    <form action="searchinvoice.php" method="post" class="form-horizontal" autocomplete="off">
	                        <p>กรุณาระบุข้อมูลใบกำกับภาษีที่ต้องการ</p><br>
	                        <table class="coupon_history">
	                            <tr>
	                                <td style="text-align:right;"><b>เลขที่ใบกำกับภาษี:</b></td>
	                                <td style="text-align:left;"><input type="text" class="form-control invoice_form" name="printInvoiceCode" value="" style="width:300px;"> &nbsp; หรือ</td>
	                            </tr>
	                            <tr>
	                                <td style="text-align:right;"><b>ชื่อบริษัท:</b></td>
	                                <td style="text-align:left;"><input type="text" class="form-control invoice_form" name="printInvoiceByName" id="FindByName" value="" style="width:300px;"> &nbsp; หรือ</td>
	                            </tr>
	                            <tr>
	                                <td style="text-align:right;"><b>ทะเบียนรถ:</b></td>
	                                <td style="text-align:left;"><input type="text" class="form-control invoice_form" name="printInvoiceByCar" id="FindByCar" value="" style="width:300px;"> &nbsp; หรือ</td>
	                            </tr>
	                            <tr>
	                                <td style="text-align:right;"><b>เบอร์โทร:</b></td>
	                                <td style="text-align:left;"><input type="text" class="form-control invoice_form" name="printInvoiceByTel" id="CustTel" value="" style="width:300px;"></td>
	                            </tr>
	                        </table>
	                        <p style="margin:15px 0 0 350px;"><button type="submit" class="btn btn-success btn-rounder">ตกลง</button></p>
	                    </form>
	                    <br>
	                    '.$ErrorTxt);

if($InvNum){
	print($TitleText);
	print('<table width="500px" border="1" class="coupon_history"><tr><th>ลำดับที่</th><th>เลขที่</th><th>วันที่</th><th>นามผู้ซื้อ</th></tr>');
	$count=(($_REQUEST['page']-1)*$ItemPerPage)+1;
	while($InvInfo=mysql_fetch_row($rsInvID)){
		print('<tr><td>'.$count.'</td><td><a href="invoice.php?InvoiceCode='.$InvInfo[2].'&fromsearch=1">'.$InvInfo[2].'</a></td><td>'.date("j/m/Y", $InvInfo[3]).'</td><td>'.$InvInfo[1].'</td></tr>');
		$count++;
	}
	print('</table><br>');

	if($InvNum > $ItemPerPage){
		print('<form action="searchinvoice.php" name="searchForm" method="post">
			<input type="hidden" name="page" id="setPageNo" value="1">
			<input type="hidden" name="printInvoiceCode" value="'.$_REQUEST['printInvoiceCode'].'">
			<input type="hidden" name="printInvoiceByName" value="'.$_REQUEST['printInvoiceByName'].'">
			<input type="hidden" name="printInvoiceByCar" value="'.$_REQUEST['printInvoiceByCar'].'">
			<input type="hidden" name="printInvoiceByTel" value="'.$_REQUEST['printInvoiceByTel'].'">
			</form>');
        $AllPage=ceil($InvNum/$ItemPerPage);
        print("<br>");
        if($_REQUEST['page']!=1){
            print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPageNo\').value=\''.($_REQUEST['page']-1).'\'; document.forms[\'searchForm\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
        }
        print("<select onchange=\"javascript:document.getElementById('setPageNo').value=this.value; document.forms['searchForm'].submit();\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
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
            print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPageNo\').value=\''.($_REQUEST['page']+1).'\'; document.forms[\'searchForm\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
        }
    }
}
print('
	                </div>
	            </div>
	        </div>
	    </section>');
include("footer.php");
?>