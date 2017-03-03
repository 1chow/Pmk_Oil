<?php
if(!isset($_COOKIE["warningDate"])){
    setcookie("warningDate", 0, time()+(86400*3), "/");
    $_COOKIE["warningDate"]=0;
}
$WarningOilTrigger=0;
$endCheck=strtotime(date("j-m-Y")." ".$WarningTime);
$Date2Check=date("Y-m-d", time());
if(time() < $endCheck){ // ยังไม่ถึงเวลา
    $endCheck = strtotime("yesterday ".$WarningTime);
    $Date2Check = date('Y-m-d', $endCheck);
}
$sqlCheckPrice="select count(*) from ".$db_name.".oil_price where RecordDate='".$Date2Check."';";
$rsCheckPrice=mysql_query($sqlCheckPrice);
$CheckPrice=mysql_fetch_row($rsCheckPrice);
$OilWarningText="";
if(!$CheckPrice[0]){
    if((time()>=$endCheck) && ($_COOKIE["warningDate"]!=$Date2Check)){
        $WarningOilTrigger=1;
    }
    else{
        $OilWarningText="ยังไม่ได้อัพเดทราคาน้ำมัน";
    }
}

$CanShowReport = array();
if(preg_match('/-7-/', $EmpAccess) || preg_match('/-6-/', $EmpAccess) || preg_match('/-11-/', $EmpAccess) || preg_match('/-13-/', $EmpAccess)){
    // รายงานการขายน้ำมัน
    $CanShowReport[]=1;
}
if(preg_match('/-7-/', $EmpAccess) || preg_match('/-11-/', $EmpAccess)){
    // รายงานรายรับ / รายจ่าย
    $CanShowReport[]=2;
}
if(preg_match('/-3-/', $EmpAccess) || preg_match('/-11-/', $EmpAccess) || preg_match('/-14-/', $EmpAccess)){
    // รายงานการขายสินค้า
    $CanShowReport[]=3;
}
if(preg_match('/-2-/', $EmpAccess) || preg_match('/-11-/', $EmpAccess)){
    // รายงานรายได้ - ถ่ายน้ำมันเครื่อง
    $CanShowReport[]=4;
}
if(preg_match('/-11-/', $EmpAccess) || preg_match('/-7-/', $EmpAccess)  || preg_match('/-13-/', $EmpAccess)){
    // สรุปรายการทางบัญชีประจำวัน
    $CanShowReport[]=5;
}
$oilPage=0;
?>
<!doctype html>
<html lang="en">
<head>

    <!-- META -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>P.M.K OIL</title>

    <!-- STYLE THIS PAGE -->
    <link rel="stylesheet" href="libs/plugins/icheck/skins/flat/green.css">
    <link rel="stylesheet" href="libs/css/calendar.css">

    <!-- CSS STYLE -->
    <link rel="stylesheet" href="libs/css/style.css?rand=1.2000">
    <link rel="stylesheet" href="libs/css/style.responsive.css">
    <script language='VBScript'>
    Sub Print()
           OLECMDID_PRINT = 6
           OLECMDEXECOPT_DONTPROMPTUSER = 2
           OLECMDEXECOPT_PROMPTUSER = 1
           call WB.ExecWB(OLECMDID_PRINT, OLECMDEXECOPT_DONTPROMPTUSER,1)
    End Sub
    document.write "<object ID='WB' WIDTH=0 HEIGHT=0 CLASSID='CLSID:8856F961-340A-11D0-A96B-00C04FD705A2'></object>"
    </script>
</head>
<body>

    <!-- HEADER -->
    <header id="header" class="top-navbar navbar navbar-fixed-top">
        <div class="navbar-header">
            <!-- Logo -->
            <a href="index.php" class="navbar-brand">
                <span class="logo-icon">
                </span>
                <span class="logo-text">
                    P.M.K OIL
                </span>
            </a>
            <!--/ Logo -->
        </div>
        <div class="navbar-toolbar clearfix">
            <ul class="nav navbar-nav navbar-left">
                <li>
                    <div class="btn-collapse-sidebar-left">
                        <i class="fa fa-bars icon-dinamic"></i>
                    </div>
                </li>
            </ul>

            <ul class="top-navRight nav navbar-nav navbar-right navbar-collapse collapse" id="main-fixed-nav">
                <li class="dropdown">
                    <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="blockProfile">
                            <span class="red" id="PriceWarning"><?php print($OilWarningText); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                            <span class="iconProfile">
                                <?php
                                if(is_file("images/user-img/user-".$UserID.".jpg")){
                                    print("<img src=\"images/user-img/user-".$UserID.".jpg\" alt=\"\" class=\"imgProfile\">");
                                }
                                else{
                                    print("<img src=\"images/user-img/guest-avatar.png\" alt=\"\" class=\"imgProfile\">");
                                }
                                ?>
                            </span>
                            <span class="name"><?php print($EmpName[0]); ?>&nbsp;</span>
                        </span>
                    </a>
                </li>

            </ul>
        </div>
    </header>
    <!-- HEADER -->


    <!-- MENU SIDE LEFT -->
    <aside class="sideLeft sidebar-nicescroller">
        <div>

            <!-- NAVIGATOR -->
            <h3 class="title-nav">เมนู</h3>
            <ul id="accordion" class="menuSidebar">
                <?php
                if($SectionNum>1 || preg_match('/-6-/', $EmpAccess) || preg_match('/-7-/', $EmpAccess) || $UserID==1){
                    print('<li><a href="index.php"><i class="fa fa-laptop"></i>หน้าหลัก</a></li>');
                }
                if(count($CanShowReport)){
                    print('<li><a href="invoice.php"><i class="fa fa-list"></i>สรุปรายงานทางบัญชี</a>');
                    $Type1=""; $Type2=""; $Type3=""; $Type4=""; $Type5="";
                    if(preg_match("#/oil_record.php#", $_SERVER['REQUEST_URI']) && !isset($_REQUEST["back"])){
                        $Type1=" class=\"subactive active\"";
                    }
                    else if(preg_match("#/accounting.php#", $_SERVER['REQUEST_URI']) && isset($_REQUEST["report"]) && !isset($_REQUEST["back"])){
                        $Type2=" class=\"subactive active\"";
                    }
                    else if(preg_match("#sell-report.php#", $_SERVER['REQUEST_URI']) && !isset($_REQUEST["back"])){
                        $Type3=" class=\"subactive active\"";
                    }
                    else if(preg_match("#service_report.php#", $_SERVER['REQUEST_URI']) && !isset($_REQUEST["back"]) && !isset($_REQUEST["mainsection"])){
                        $Type4=" class=\"subactive active\"";
                    }
                    else if(preg_match("#accounting_daily.php#", $_SERVER['REQUEST_URI']) && !isset($_REQUEST["back"])){
                        $Type5=" class=\"subactive active\"";
                    }
                    print('<ul class="submenu">');
                    if(in_array(1, $CanShowReport)){
                        print('<li><a href="oil_record.php"'.$Type1.'>รายงานการขายน้ำมัน</a></li>');
                    }
                    if(in_array(2, $CanShowReport)){
                        print('<li><a href="accounting.php?report=1"'.$Type2.'>รายงานรายรับ / รายจ่าย</a></li>');
                    }
                    if(in_array(3, $CanShowReport)){
                        print('<li><a href="sell-report.php"'.$Type3.'>รายงานการขายสินค้า</a></li>');
                    }
                    if(in_array(4, $CanShowReport)){
                        print('<li><a href="service_report.php"'.$Type4.'>รายงานรายได้ - ถ่ายน้ำมันเครื่อง</a></li>');
                    }
                    if(in_array(5, $CanShowReport)){
                        print('<li><a href="accounting_daily.php"'.$Type5.'>สรุปรายการทางบัญชีประจำวัน</a></li>');
                    }
                    print('</ul></li>');
                }
                if(preg_match('/-5-/', $EmpAccess) || $UserID==1){
                    print('<li><a href="oil.php"><i class="fa fa-tint"></i>น้ำมัน</a></li>');
                }
                if(preg_match('/-1-/', $EmpAccess) || $UserID==1){
                    $Type1=""; $Type2=""; $Type3=""; $Type4="";
                    if(preg_match("#/invoice.php#", $_SERVER['REQUEST_URI']) && !isset($_REQUEST["fromsearch"]) && !isset($_REQUEST["DateBetween"])){
                        if(isset($_POST['editInvoiceCode']) && intval($_POST['editInvoiceCode'])){
                            $Type2=" class=\"subactive active\"";
                        }
                        else{
                            $Type1=" class=\"subactive active\"";
                        }
                    }
                    else if(preg_match("#/searchinvoice.php#", $_SERVER['REQUEST_URI']) || isset($_REQUEST["fromsearch"])){
                        $Type3=" class=\"subactive active\"";
                    }
                    else if(isset($_REQUEST["DateBetween"]) || (preg_match("#invoice-report.php#", $_SERVER['REQUEST_URI']) && (!isset($_REQUEST["back"]) || !trim($_REQUEST["back"])))){
                        $Type4=" class=\"subactive active\"";
                    }
                    print('<li><a href="invoice.php"><i class="fa fa-list"></i>ใบกำกับภาษี</a>');
                    print('<ul class="submenu">
                        <li><a href="invoice.php?AddNew=1"'.$Type1.'>ออกใบกำกับภาษี</a></li>
                        <li><a href="javascript:void(0);" data-toggle="modal" data-target="#EditInvoice"'.$Type2.'>แก้ไขใบกำกับภาษี</a></li>
                        <li><a href="searchinvoice.php"'.$Type3.'>เรียกดูใบกำกับภาษี</a></li>
                        <li><a href="invoice-report.php"'.$Type4.'>รายการสรุป</a></li>
                        </ul>');
                    print('</li>');
                }


                if(preg_match('/-2-/', $EmpAccess) || $UserID==1){
                    $Type1=""; $Type2=""; $Type3=""; $Type4=""; $Type5="";
                    if(preg_match("#carwash.php#", $_SERVER['REQUEST_URI'])){
                        if(isset($_POST['editCode']) && trim($_POST['editCode'])){
                            if(isset($_REQUEST["serviceType"]) && $_REQUEST["serviceType"]==2){
                                $Type4=" class=\"subactive active\"";
                            }
                            else{
                                $Type3=" class=\"subactive active\"";
                            }
                        }
                        else if(isset($_REQUEST["serviceType"]) && $_REQUEST["serviceType"]==2){
                            $Type2=" class=\"subactive active\"";
                        }
                        else{
                            $Type1=" class=\"subactive active\"";
                        }
                    }
                    else if(preg_match("#car_service.php#", $_SERVER['REQUEST_URI'])){
                        $Type5=" class=\"subactive active\"";
                    }
                    print('<li><a href="javascript:void(0);"><i class="fa fa-car"></i>ล้างรถ/เปลี่ยนน้ำมันเครื่อง</a>');
                    $EditCarWash="";
                    $EditCarWash2="";
                    if($PermissionNo==3){
                        $EditCarWash='<li><a href="javascript:updateService(1);"'.$Type3.'>แก้ไขใบล้างรถ</a></li>';
                        $EditCarWash2='<li><a href="javascript:updateService(2);"'.$Type4.'>แก้ไขใบเปลี่ยนน้ำมันเครื่อง</a></li>';
                    }
                    print('<ul class="submenu">
                            <li><a href="car_service.php"'.$Type5.'>หน้าหลักงานบริการ</a></li>
                            <li><a href="carwash.php?back=car_service"'.$Type1.'>บริการล้างรถ</a></li>
                            '.$EditCarWash.'
                            <li><a href="carwash.php?serviceType=2&back=car_service"'.$Type2.'>เปลี่ยนน้ำมันเครื่อง</a></li>
                            '.$EditCarWash2.'
                            <li><a href="car_service.php?report=1&mainsection=service"');
                    if(preg_match("#car_service.php#", $_SERVER['REQUEST_URI']) && isset($_REQUEST["mainsection"])){
                        print(' class="subactive active"');
                    }
                    print('>รายงานสรุป</a></li>
                            <li><a href="service_report.php?mainsection=service"');
                    if(preg_match("#service_report.php#", $_SERVER['REQUEST_URI']) && isset($_REQUEST["mainsection"])){
                        print(' class="subactive active"');
                    }
                    print('>รายงานรายได้ - ถ่ายน้ำมันเครื่อง</a></li>
                            <li><a href="service-customer.php"');
                    if(preg_match("#service-customer.php#", $_SERVER['REQUEST_URI']) && !isset($_REQUEST["mainNo"])){
                        print(' class="subactive active"');
                    }
                    print('>ลูกค้า</a></li>
                        </ul>');
                    print('</li>');
                }
                if(preg_match('/-3-/', $EmpAccess) || $UserID==1){
                    print('<li><a href="stock.php"><i class="fa fa-circle-o-notch"></i>ทะเบียนสินค้าและบริการ</a></li>');
                }
                if(preg_match('/-14-/', $EmpAccess) || $UserID==1){
                    print('<li><a href="special-stock.php"><i class="fa fa-star"></i>สินค้าพิเศษ</a></li>');
                }
                if(preg_match('/-4-/', $EmpAccess) || $UserID==1){
                    print('<li><a href="coupons.php"><i class="fa fa-list-alt"></i>คูปอง</a></li>');
                }

                if(preg_match('/-9-/', $EmpAccess) || preg_match('/-10-/', $EmpAccess) || $UserID==1){
                    print('<li><a href="javascript:void(0);"><i class="fa fa-edit"></i>ทะเบียนลูกค้า</a>');
                    print('<ul class="submenu">');
                    if(preg_match('/-9-/', $EmpAccess) || $UserID==1){
                        print('<li><a href="service-customer.php?mainNo=1"');
                        if(preg_match("#service-customer.php#", $_SERVER['REQUEST_URI']) && isset($_REQUEST["mainNo"])){
                            print(' class="subactive active"');
                        }
                        print('>ลูกค้าทั่วไป</a></li>');
                    }
                    if(preg_match('/-10-/', $EmpAccess) || $UserID==1){
                        print('<li><a href="cash-customer.php"');
                        if(preg_match("#cash-customer.php#", $_SERVER['REQUEST_URI'])){
                            print(' class="subactive active"');
                        }
                        print('>ลูกค้าเครดิตเงินสด</a></li>');
                        print('<li><a href="customer.php"');
                        if(preg_match("#/customer.php#", $_SERVER['REQUEST_URI']) && (!isset($_REQUEST["CouponPage"])||(!$_REQUEST["CouponPage"])) ){
                            print(' class="subactive active"');
                        }
                        print('>ลูกค้าเครดิต</a></li>');
                        print('<li><a href="credit-billing-check.php"');
                        if(preg_match("#/credit-billing-check.php#", $_SERVER['REQUEST_URI'])){
                            print(' class="subactive active"');
                        }
                        print('>เช็คใบสั่งน้ำมัน</a></li>');
                    }
                    print('</ul>');
                    print('</li>');
                }

                if(preg_match('/-8-/', $EmpAccess) || $UserID==1){
                    print('<li><a href="employees.php"><i class="fa fa-users"></i>พนักงาน</a></li>');
                }
                if(preg_match('/-11-/', $EmpAccess) || $UserID==1){
                    print('<li><a href="reports.php"><i class="fa fa-book"></i>รายงานสรุป</a></li>');
                }
                if(preg_match('/-12-/', $EmpAccess) || $UserID==1){
                    print('<li><a href="system.php"><i class="fa fa-cog"></i>ตั้งค่าระบบ</a></li>');
                }
                ?>
                <li><a href="index.php?logout=1"><i class="fa fa-sign-out"></i>ออกจากระบบ</a></li>
            </ul>

            <!-- SCHEDULE -->
        </div>
    </aside>
    <!-- END MENU SIDE LEFT -->
<?php
$Customer4Invoice='';
$sqlCust="SELECT CustName from ".$db_name.".customer where Deleted=0 and CustID>0 order by CustName ASC;";
$rsCust=mysql_query($sqlCust);
while($CustInfo=mysql_fetch_row($rsCust)){
    $Customer4Invoice.="*".$CustInfo[0];
}
$Customer4Invoice = substr($Customer4Invoice, 1);
?>