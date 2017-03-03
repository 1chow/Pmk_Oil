<?php
$db_name = "pmkoil_data";
$db_host = "localhost";
$db_username = "root";
$db_password = "";

// $db_name = "nick_pmkoil";
// $db_host = "localhost";
// $db_username = "nick_pmkoil";
// $db_password = "pmkoil1234";

$Conn = mysql_connect($db_host, $db_username, $db_password);
mysql_select_db($db_name, $Conn);
date_default_timezone_set('Asia/Bangkok');
mysql_query("SET character_set_results=utf8");
mysql_query("SET character_set_client=utf8");
mysql_query("SET character_set_connection=utf8");
$monthList = Array("มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
$shortMonthList = Array("ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
$showAllCust=0;

$queryConstants="Select ConstantName, ConstantValue From ".$db_name.".system;";
$rsConstants=mysql_query($queryConstants);
while($Constants=mysql_fetch_row($rsConstants)){
    eval("$".$Constants[0]."=\"".$Constants[1]."\";");
}

function cutStr($str, $maxChars='', $holder=''){
    if(strlen($str) > $maxChars ){
        $str = iconv_substr($str, 0, $maxChars, "UTF-8") . $holder;
    }
    return $str;
}

function PassEncryption($string, $salt, $Type){ // if Type=0 : Encrypt, if Type=1: Decrypt
	// employeee $salt=userID
	$key="MVjxLn5exXYZ2F8A1321";
	$key=$salt.$key;
	if(!$Type){ // Encrypt
		$result=base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
	}
    else{
		if(strlen($string)>20){ // string 2 Decrypt > 20
			$result=rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
		}else{
			$result=$string;
		}
	}
	return $result;
}

function HolidayPrint($HolidayCount, $Date, $Month, $Note){
    global $monthList;
	if(!$Note){
		$Note='';
	}
	print("\r\n\t\t<tr><th>");
    if($HolidayCount){
    	print($HolidayCount.".");
    }
    else{
    	print("เพิ่ม");
    }
    print('</th><td>&nbsp;&nbsp;');
    print("<select name=\"HoliDayDate[".$HolidayCount."]\" class=\"form-control input-sm inline_input\" style=\"width:70px;\"><option value=\"\">วันที่</option>");
    for($i=1; $i<32; $i++){
        print("<option value=\"".$i."\"");
        if($Date == $i){ print(" selected"); }
        print(">".$i."</option>");
    }
    print("</select>");
    print('&nbsp;&nbsp;&nbsp;');
    print("<select name=\"HoliDayMonth[".$HolidayCount."]\" class=\"form-control input-sm inline_input\" style=\"width:90px;\"><option value=\"\">เดือน</option>");
    for($i=0; $i<count($monthList); $i++){
        print("<option value=\"".($i+1)."\"");
        if($Month == ($i+1)){ print(" selected"); }
        print(">".$monthList[$i]."</option>");
    }
    print("</select>");
    print('&nbsp;&nbsp;&nbsp;');
    print('<input type="text" class="form-control inline_input" name="HoliDayNote['.$HolidayCount.']" placeholder="หมายเหตุ" value="'.$Note.'" style="width:400px;">');
    if(!$HolidayCount){
    	print('&nbsp;&nbsp;<i class="fa fa-plus-circle"></i>');
    }
    print('</td></tr>');
}

function OilPrice($OilID, $Name, $Price, $Date, $WarningPage, $StartTime){
    global $monthList;
    if(!$WarningPage){
        print("\r\n\t\t<tr id=\"item-".$OilID."\">");
        if($OilID){
            print('<td style="width:50px;">&nbsp;</td><td><input type="hidden" name="OilName['.$OilID.']" value="'.$Name.'">'.$Name.'</td>');
        }
        else{
            print('<td style="width:50px;">เพิ่ม</th><td style="width:20%; white-space:nowrap;"><input type="text" class="form-control" name="OilName['.$OilID.']" placeholder="ชนิดน้ำมัน" value="'.$Name.'" style="width:200px;"></td>');
        }

        $TimeArr=explode("/", $Date);
        $GetTimeArr=explode(":", $StartTime);
        $UnixTime=mktime($GetTimeArr[0], $GetTimeArr[1], 0, $TimeArr[1], $TimeArr[0], $TimeArr[2]);
        print('<td style="width:140px;"><input type="text" class="form-control price" name="OilPrice['.$OilID.']" placeholder="ราคา" value="'.number_format($Price, 2).'" style="width:80px;"></td>');
        if($OilID){
            print('<td style="width:140px;">'.$Date.'</td><td style="width:140px;">'.$StartTime.'</td><td><div id="'.$OilID.'">&nbsp;<i class="fa fa-trash-o removeItem" id="'.$Name.'"></i></div></td>');
        }
        else{
            print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
        }
        print('</tr>');
    }
    else{
        print("\r\n\t\t<tr id=\"item-".$OilID."\">");
        print("<th>&nbsp;</th>
               <th>".$Name."<input type=\"hidden\" name=\"OilName2[".$OilID."]\" value=\"".$Name."\"></th>
               <th>".number_format($Price, 2)."<input type=\"hidden\" name=\"OldOilPrice[".$OilID."]\" value=\"".$Price."\"></th>
               <th><input type=\"text\" class=\"form-control\" name=\"OilPrice[".$OilID."]\" value=\"\" style=\"width:80px;\"></th>");
        print("</tr>");
    }
}

function FixFormat($Value, $DisplayText){
    if($DisplayText){
        if($Value){
            return number_format($Value, 2);
        }
        else{
            return "-";
        }
    }
    else{
        if($Value=="-"){
            return 0;
        }
        else{
            return floatval(preg_replace("/,/", "", $Value));
        }
    }
}

function reInvoiceNo($TimeMonth, $TimeYear){
    global $db_name;
    $InvoiceDigits=4;
    $count=1;
    $DayPerMonth=intval(cal_days_in_month(CAL_GREGORIAN, $TimeMonth, $TimeYear));
    $startDate=mktime(0, 0, 0, $TimeMonth, 1, $TimeYear);
    $endDate=mktime(23, 59, 59, $TimeMonth, $DayPerMonth, $TimeYear);
    $sqlInvoiceNo="select orderitems.ID, FROM_UNIXTIME(PaidDate, '%d.%m.%Y') as ndate from ".$db_name.".orderitems inner join ".$db_name.".product_history on product_history.ServiceID=(orderitems.ID*(-1)) where PaidDate>=".intval($startDate)." and PaidDate<=".intval($endDate)." and VatQTY>0 GROUP BY ndate;";
    $rsInvoiceNo=mysql_query($sqlInvoiceNo);
    while($InvoiceNo=mysql_fetch_row($rsInvoiceNo)){
        $sqlInsert="UPDATE ".$db_name.".orderitems set InvoiceNo='".substr(($TimeYear+543), -2).sprintf("%02d", $TimeMonth)."-".sprintf("%0".$InvoiceDigits."d", $count)."' where FROM_UNIXTIME(PaidDate, '%d.%m.%Y')=".intval($InvoiceNo[1])." and FROM_UNIXTIME(PaidDate, '%m')=".intval($TimeMonth).";";
        $rsInsert=mysql_query($sqlInsert);
        $count++;
    }
}

if(isset($_GET["logout"]) && intval($_GET["logout"])){
    setcookie("UserLogin", "", 1, "/");
    unset($_COOKIE["UserLogin"]);
}
if(isset($_POST["userName"]) && trim($_POST["userName"]) && isset($_POST["password"]) && trim($_POST["password"])){
    $query="Select EmpID, Password, Permission From ".$db_name.".employee Where UserName='".mysql_real_escape_string(trim($_POST["userName"]))."' and Deleted=0 and Permission!=0;";
    $RecSet=mysql_query($query);
    $row=mysql_fetch_row($RecSet);
    $PermissionUser=$row[2];
    if($row[0]){
        $Pass2Encrypt=PassEncryption(trim($_POST["password"]), $row[0], 0);
    }
    if($Pass2Encrypt == $row[1]){
        setcookie("UserLogin", $row[0], 0, "/");
        $_COOKIE["UserLogin"]=$row[0];

        $sqlSectionID="select SectionID from ".$db_name.".employee_access where EmpID=".intval($_COOKIE["UserLogin"]).";";
        $rsSectionID=mysql_query($sqlSectionID);
        $SectionNum=mysql_num_rows($rsSectionID);
        if($SectionNum!=1){
            header("location: index.php?SF=1");
            exit();
        }
        else{
            $AccessArr = array(1 => 'invoice', 2 => 'car_service', 3 => 'stock', 4 => 'coupons', 5 => 'oil', 6 => 'index', 7 => 'index', 8 => 'employees', 9 => 'service-customer', 10 => 'customer', 11 => 'reports', 12 => 'system', 13 => 'index', 14 => 'special-stock');
            $SectionID = mysql_fetch_row($rsSectionID); //echo $PermissionUser; exit;
            if($SectionID[0]==4 && $PermissionUser<2){
                header("location: coupon_check.php?SF=1");
                exit();
            }
            else{
                header("location: ".$AccessArr[$SectionID[0]].".php?SF=1");
                exit();
            }
        }
    }
}

$UserID=0;
if(!isset($_COOKIE["UserLogin"]) || !intval($_COOKIE["UserLogin"])){
    include("sign-in.php");
    exit();
}
else{
    $UserID=$_COOKIE["UserLogin"];
}
$sqlEmpName="Select concat(FirstName, ' ', LastName), Permission From ".$db_name.".employee Where EmpID=".intval($UserID).";";
$rsEmpName=mysql_query($sqlEmpName);
$EmpName=mysql_fetch_row($rsEmpName);
$UserName=$EmpName[0];
$PermissionArr = array('none', 'normal', 'supervisor', 'admin');
$Permission=$PermissionArr[$EmpName[1]];
$PermissionNo=$EmpName[1];

$EmpAccess="0";
$sqlSectionID="select SectionID from ".$db_name.".employee_access where EmpID=".intval($UserID).";";
$rsSectionID=mysql_query($sqlSectionID);
$SectionNum=mysql_num_rows($rsSectionID);
while($SectionID = mysql_fetch_row($rsSectionID)){
    $EmpAccess.="-".$SectionID[0]."-";
    $onlyAccess=$SectionID[0];
}

?>