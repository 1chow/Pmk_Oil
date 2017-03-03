<?php
include("dbvars.inc.php");
if(!preg_match('/-2-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

function findMax($serviceType){
    global $db_name, $ServiceWashBegin, $OilChangeBegin;
    $Today=date("d/m/Y", time());
    $SetDate=explode("/", $Today);
    $startDate=mktime(0, 0, 0, $SetDate[1], $SetDate[0], $SetDate[2]);
    $endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
    $sqlSelect="select MAX(OrderNo) from ".$db_name.".car_service where ServiceDate>=".$startDate." and ServiceDate<=".$endDate." and ServiceType=".intval($serviceType).";";
    $rsSelect=mysql_query($sqlSelect);
    $Info=mysql_fetch_row($rsSelect);
    $MaxOrder=(intval($Info[0])+1);

    $sqlSelect="select MAX(ServiceCode) from ".$db_name.".car_service where ServiceType=".intval($serviceType)." order by ServiceCode DESC;";
    $rsSelect=mysql_query($sqlSelect);
    $Info=mysql_fetch_row($rsSelect);
    $MaxCode=($Info[0]+1);
    if($serviceType==1 && $MaxCode<$ServiceWashBegin){
        $MaxCode = $ServiceWashBegin;
    }
    else if($serviceType==2 && $MaxCode<$OilChangeBegin){
        $MaxCode = $OilChangeBegin;
    }
    return array($MaxCode, $MaxOrder);
}

if(isset($_POST['ServiceCancelID']) && intval($_POST['ServiceCancelID'])){
    $sqlDelete="UPDATE ".$db_name.".car_service SET Deleted=1 WHERE car_service.ID=".intval($_POST['ServiceCancelID']).";";
    $rsDelete=mysql_query($sqlDelete);

    $sqlDelete="select car_service_detail.ProductID, car_service_detail.QTY, products.Type, orderitems.UnitUsed from ((".$db_name.".car_service_detail inner join ".$db_name.".products on products.ProductID=car_service_detail.ProductID) left join ".$db_name.".orderitems on car_service_detail.ServiceID=orderitems.ServiceID and car_service_detail.ProductID=orderitems.ProductID) where car_service_detail.ServiceID=".intval($_POST['ServiceCancelID'])." and products.Type='สินค้า';";
    $rsDelete=mysql_query($sqlDelete);
    while($backToStock=mysql_fetch_row($rsDelete)){
        $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID) VALUES (".$Stock4Service.", ".intval($backToStock[0]).", ".time().", ".round($backToStock[1]*$backToStock[3], 2).", 'เปลี่ยนแปลงข้อมูลจาก ".$backToStock[1]." เป็น 0<br>(ยกเลิกใบรับบริการ".$_POST['ServiceType']." เลขที่ ".$_POST["ServiceNo"].")', ".intval($UserID).");";
        $rsStockHistory=mysql_query($sqlStockHistory);
        $sqlStock="UPDATE ".$db_name.".product_stock set QTY=QTY+".round($backToStock[1]*$backToStock[3], 2)." where ProductID=".intval($backToStock[0])." and StockID=".$Stock4Service.";";
        $rsStock=mysql_query($sqlStock);
    }
    $sqlDelete="delete from ".$db_name.".account_daily where Note='".intval($_POST['ServiceCancelID'])."' and Name like 'บริการ%';";
    $rsDelete=mysql_query($sqlDelete);

    //====================== start delete record for vat qty report ======================//
    $sqlOrderitemID="select orderitems.ID, product_history.NoVatQTY, orderitems.ProductID, product_history.Date from (".$db_name.".orderitems inner join ".$db_name.".product_history on product_history.ServiceID=(orderitems.ID*(-1)) and product_history.ProductID=orderitems.ProductID) where orderitems.ServiceID=".intval($_POST['ServiceCancelID']).";";
    $rsOrderitemID=mysql_query($sqlOrderitemID);
    while($OrderitemID=mysql_fetch_row($rsOrderitemID)){
        $sqlDelete="delete from ".$db_name.".orderitems where orderitems.ID='".intval($OrderitemID[0])."';";
        $rsDelete=mysql_query($sqlDelete);
        $sqlDelete="delete from ".$db_name.".product_history where ServiceID='".intval($OrderitemID[0]*(-1))."' and ChangeNote='ขายสินค้า';";
        $rsDelete=mysql_query($sqlDelete);
        $updateVatQTY="update ".$db_name.".products set NoVatQTY=NoVatQTY+".floatval($OrderitemID[1])." where ProductID=".intval($OrderitemID[2]).";";
        $rsUpdateVat=mysql_query($updateVatQTY);
    }
    if(intval($OrderitemID[3])){
        reInvoiceNo(date("n", $OrderitemID[3]), date("Y", $OrderitemID[3]));
    }
    //====================== end delete record for vat qty report ======================//

    print('ยกเลิก ใบรับบริการ'.$_POST['ServiceType'].'เลขที่ '.$_POST['ServiceNo'].' เรียบร้อยแล้ว');
    exit();
}
else if(isset($_POST['CarFindCustID'])){
    if(trim($_POST['CarFindCustID'])!='999'){
        $sqlCustID="select CustName from (".$db_name.".customer inner join ".$db_name.".customer_car on CustomerID=CustID) where CarCode='".mysql_real_escape_string(trim($_POST["CarFindCustID"]))."';";
        $rsCustID=mysql_query($sqlCustID);
        $getCustomerName=mysql_fetch_row($rsCustID);
        if($getCustomerName[0]){
            print($getCustomerName[0]);
        }
    }
    exit();
}
else if(isset($_POST['ServiceTotal']) && $_POST['ServiceTotal']>0){
    if(trim($_POST["CustomerName"])){
        // check cust name
        $sqlCheck="select CustID from ".$db_name.".customer where CustName='".mysql_real_escape_string(trim($_POST["CustomerName"]))."';";
        $rsCheck=mysql_query($sqlCheck);
        $Check=mysql_fetch_row($rsCheck);
        if(intval($Check[0])){
            $_POST["CustID"]=$Check[0];
        }
    }
    else if(!trim($_POST["CustomerName"])){
        $_POST["CustID"]=(-1);
    }

    if(trim($_POST["CarCode"])){
        $sqlCustID="select CarID, CustomerID from ".$db_name.".customer_car where CarCode='".mysql_real_escape_string(trim($_POST["CarCode"]))."';";
        $rsCustID=mysql_query($sqlCustID);
        $getCustomerID=mysql_fetch_row($rsCustID);
        $SetCarID=$getCustomerID[0];
        if(!$getCustomerID[0]){
            if(!intval($_POST["CustID"]) && trim($_POST["CustomerName"])){
                $sqlInsert="INSERT INTO ".$db_name.".customer (CustName, Address1, Address2, Address3, Address4, Tel, TaxCode, BranchCode, CreditLock, CreditLimit, CreditTerm, SpecialTerm, DayBeforePay, UnofficialBalance, CheckCarCode, FromService) VALUES ('".mysql_real_escape_string(trim($_POST["CustomerName"]))."', '', '', '', '', '', '', '', 0, '0.00', 0, 0, 0, '0.00', 0, 1);";
                $rsInsert=mysql_query($sqlInsert);
                $_POST["CustID"]=mysql_insert_id($Conn);
            }
            if(trim($_POST["CarCode"])!='999' && !preg_match("#ใส่ถัง#", $_POST["CarCode"])){
                $sqlCustomerCar="INSERT INTO ".$db_name.".customer_car (CustomerID, CarCode) VALUES (".intval($_POST["CustID"]).", '".mysql_real_escape_string(trim($_POST["CarCode"]))."');";
                $rsCustomerCar=mysql_query($sqlCustomerCar);
                $SetCarID=mysql_insert_id($Conn);
            }
            else{
                $SetCarID=(-1);
            }
        }
        else if($getCustomerID[1] && !intval($_POST["CustID"])){
            $_POST["CustID"]=$getCustomerID[1];
        }
    }

    if($_POST['serviceType']==1){ $Text='ล้างรถ'; }else{ $Text='เปลี่ยนน้ำมันเครื่อง'; }
    if(!intval($_POST['EditID'])){ // insert
        $setCode = findMax($_POST['serviceType']);
        $_POST["DiscountVal"]=preg_replace("/,/", "", $_POST["DiscountVal"]);
        $_POST["PercentDiscount"]=preg_replace("/,/", "", $_POST["PercentDiscount"]);
        $sqlInsert="INSERT INTO ".$db_name.".car_service (ServiceCode, ServiceDate, CustID, CustCar, ServiceNote, PercentDiscount, DiscountVal, SaveBy, OrderNo, PrintNum, ServiceType) VALUES (".intval($setCode[0]).", ".time().", ".intval($_POST["CustID"]).", ".intval($SetCarID).", '".mysql_real_escape_string(trim($_POST["ServiceNote"]))."', '".mysql_real_escape_string(trim($_POST["PercentDiscount"]))."', '".mysql_real_escape_string(trim($_POST["DiscountVal"]))."', ".intval($UserID).", ".intval($setCode[1]).", 1, ".intval($_POST['serviceType']).");";
        $rsInsert=mysql_query($sqlInsert);
        $ServiceID=mysql_insert_id($Conn);
        $_POST["ServiceCode"]=intval($setCode[0]);

        $count=1;
        $ServiceTotal=0;
        foreach ($_POST['RecCount'] as $key => $value) {
            if($_POST['QTY'][$value]>0){
                $_POST['price'][$value]=preg_replace("/,/", "", $_POST['price'][$value]);
                $sql="INSERT INTO ".$db_name.".car_service_detail (ServiceID, ProductID, QTY, UnitPrice, OrderNo) VALUES (".intval($ServiceID).", ".intval($_POST['ProducID'][$value]).", ".intval($_POST['QTY'][$value]).", '".floatval($_POST['price'][$value])."', ".$count.");";
                $rs=mysql_query($sql);
                $ServiceTotal+=(intval($_POST['QTY'][$value])*($_POST['price'][$value]));
                $count++;

                if($_POST["ProductType"][$value]=='สินค้า'){
                    $sqlVatQTY="select NoVatQTY, UnitUsed from products where ProductID=".intval($_POST['ProducID'][$value]).";";
                    $rsVatQTY=mysql_query($sqlVatQTY);
                    $NoVatQTY=mysql_fetch_row($rsVatQTY);
                    $QTYset=round($_POST['QTY'][$value] * $NoVatQTY[1], 2);

                    //====================== start manage no vat qty ======================//
                    if($NoVatQTY[0] >= $QTYset){
                        $updateVatQTY="update products set NoVatQTY=NoVatQTY-".floatval($QTYset)." where ProductID=".intval($_POST['ProducID'][$value]).";";
                        $rsUpdateVat=mysql_query($updateVatQTY);
                        $SetVatQTY=0;
                        $SetNoVatQTY=$QTYset;
                    }
                    else{
                        $updateVatQTY="update products set NoVatQTY=0 where ProductID=".intval($_POST['ProducID'][$value]).";";
                        $rsUpdateVat=mysql_query($updateVatQTY);
                        $SetNoVatQTY=round($NoVatQTY[0] * $NoVatQTY[1], 2); // vat QTY = สินค้าทั้งหมดทีต้องคำนวณ vat
                        $SetVatQTY=($QTYset-$SetNoVatQTY);
                    }
                    //====================== end manage no vat qty ======================//

                    $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID, ServiceID, VatQTY, NoVatQTY) VALUES (".$Stock4Service.", ".intval($_POST['ProducID'][$value]).", ".time().", ".$QTYset*(-1).", 'หักสินค้าออก (ใบรับบริการ".$Text." เลขที่ ".$_POST["ServiceCode"].")', ".intval($UserID).", ".intval($ServiceID).", ".floatval($SetVatQTY).", ".floatval($SetNoVatQTY).");";
                    $rsStockHistory=mysql_query($sqlStockHistory);

                    $sqlStock="UPDATE ".$db_name.".product_stock set QTY=QTY-".floatval($QTYset)." where ProductID=".intval($_POST['ProducID'][$value])." and StockID=".$Stock4Service.";";
                    $rsStock=mysql_query($sqlStock);

                    //====================== start insert for vat qty report ======================//
                    $sqlInsert="INSERT INTO ".$db_name.".orderitems (ProductID, QTY, UnitPrice, PaidBy, PaidDate, Note, SellBy, FromStock, ServiceID, UnitUsed) VALUES (".intval($_POST['ProducID'][$value]).", '".floatval($SetVatQTY)."', '".floatval($_POST['price'][$value])."', 'เงินสด', ".time().", '', 0, ".intval($Stock4Service).", ".intval($ServiceID).", ".$NoVatQTY[1].");";
                    $rsInsert=mysql_query($sqlInsert);
                    $SellID=mysql_insert_id($Conn);
                    $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID, ServiceID, VatQTY, NoVatQTY) VALUES (".$Stock4Service.", ".intval($_POST['ProducID'][$value]).", ".time().", ".$QTYset.", 'ขายสินค้า', ".intval($UserID).", ".($SellID*(-1)).", ".floatval($SetVatQTY).", ".floatval($SetNoVatQTY).");";
                    $rsStockHistory=mysql_query($sqlStockHistory);
                    //====================== end insert for vat qty report ======================//
                }
            }
        }
        $ServiceTotal=($ServiceTotal-$_POST["DiscountVal"]);
        $sqlInsert="INSERT INTO ".$db_name.".account_daily (Type, Name, Total, PaidTo, PaidDate, Note, BookCodeNo) VALUES (1, 'บริการ".$Text."', '".floatval($ServiceTotal)."', '".intval($UserID)."', '".time()."', '".$ServiceID."', '');";
        $rsInsert=mysql_query($sqlInsert);
    }
    else if(intval($_POST['EditID'])){ // update
        //====================== start delete record for vat qty report ======================//
        $NoVatQTYInfo=array();
        $sqlOrderitemID="select orderitems.ID, product_history.NoVatQTY, orderitems.ProductID from (".$db_name.".orderitems inner join ".$db_name.".product_history on product_history.ServiceID=(orderitems.ID*(-1)) and product_history.ProductID=orderitems.ProductID) where orderitems.ServiceID=".intval($_POST['EditID']).";";
        $rsOrderitemID=mysql_query($sqlOrderitemID);
        while($OrderitemID=mysql_fetch_row($rsOrderitemID)){
            $sqlDelete="delete from ".$db_name.".orderitems where orderitems.ID='".intval($OrderitemID[0])."';";
            $rsDelete=mysql_query($sqlDelete);
            $sqlDelete="delete from ".$db_name.".product_history where ServiceID='".intval($OrderitemID[0]*(-1))."' and ChangeNote='ขายสินค้า';";
            $rsDelete=mysql_query($sqlDelete);
            $NoVatQTYInfo[$OrderitemID[2]]=$OrderitemID[1];
            $updateVatQTY="update products set NoVatQTY=NoVatQTY+".floatval($OrderitemID[1])." where ProductID=".intval($OrderitemID[2]).";";
            $rsUpdateVat=mysql_query($updateVatQTY);
        }
        //====================== end delete record for vat qty report ======================//

        $sqlInsert="UPDATE ".$db_name.".car_service set CustID=".intval($_POST["CustID"]).", CustCar=".intval($SetCarID).", ServiceNote='".mysql_real_escape_string(trim($_POST["ServiceNote"]))."', PercentDiscount='".mysql_real_escape_string(trim($_POST["PercentDiscount"]))."', DiscountVal='".mysql_real_escape_string(trim($_POST["DiscountVal"]))."', SaveBy=".intval($UserID).", PrintNum=".$_POST['PrintNum']." where ID=".intval($_POST['EditID']).";";
        $rsInsert=mysql_query($sqlInsert);
        $ServiceID=$_POST['EditID'];
        $sqlDelete="DELETE FROM ".$db_name.".car_service_emp WHERE car_service_emp.ServiceID=".intval($_POST['EditID']).";";
        $rsDelete=mysql_query($sqlDelete);

        $count=1;
        $ProductList="0";
        $ServiceTotal=0;
        foreach($_POST['RecCount'] as $key => $value) {
            if($_POST['ProducID'][$value]!=(-1)){
                $setProductID=$_POST['ProducID'][$value];
                $sqlUnitUsed="select UnitUsed, NoVatQTY from products where ProductID=".intval($setProductID).";";
                $rsUnitUsed=mysql_query($sqlUnitUsed);
                $UnitUsed=mysql_fetch_row($rsUnitUsed);
                $setQTY=round($_POST['QTY'][$value]*$UnitUsed[0], 2);

                //====================== start manage no vat qty ======================//
                if($UnitUsed[1] >= $setQTY){
                    $updateVatQTY="update products set NoVatQTY=NoVatQTY-".floatval($setQTY)." where ProductID=".intval($setProductID).";";
                    $rsUpdateVat=mysql_query($updateVatQTY);
                    $SetVatQTY=0;
                    $SetNoVatQTY=$setQTY;
                }
                else{
                    $updateVatQTY="update products set NoVatQTY=0 where ProductID=".intval($setProductID).";";
                    $rsUpdateVat=mysql_query($updateVatQTY);
                    $SetNoVatQTY=round($UnitUsed[0] * $UnitUsed[1], 2); // vat QTY = สินค้าทั้งหมดทีต้องคำนวณ vat
                    $SetVatQTY=($setQTY-$SetNoVatQTY);
                }
                //====================== end manage no vat qty ======================//

                if($_POST["ProductType"][$value]=='สินค้า'){
                    $keepRealInput=0;
                    $ChangeQTY[$setProductID]=$setQTY;
                    $ChangeQTYCond="QTY-".$ChangeQTY[$setProductID];
                    $sqlCheck="select QTY, UnitPrice from ".$db_name.".car_service_detail where ServiceID=".intval($ServiceID)." and ProductID=".intval($setProductID).";";
                    $rsCheck=mysql_query($sqlCheck);
                    $ServiceCheck=mysql_fetch_row($rsCheck);
                    if($ServiceCheck[0]){
                        $keepRealInput=$ServiceCheck[0];
                        $ServiceCheck[0]=($ServiceCheck[0]*$UnitUsed[0]);
                        if($ServiceCheck[0]>$setQTY){
                            $ChangeQTY[$setProductID]=($ServiceCheck[0]-$setQTY);
                            $ChangeQTYCond="QTY+".$ChangeQTY[$setProductID];
                        }
                        else if($ServiceCheck[0]<$setQTY){
                            $ChangeQTY[$setProductID]=($setQTY-$ServiceCheck[0]);
                            $ChangeQTYCond="QTY-".$ChangeQTY[$setProductID];
                            $ChangeQTY[$setProductID]=($ChangeQTY[$setProductID]*(-1));
                        }
                        else{
                            $ChangeQTY[$setProductID]=0;
                            $ChangeQTYCond="QTY";
                        }
                    }
                    $sqlDelete="DELETE FROM ".$db_name.".car_service_detail WHERE car_service_detail.ServiceID=".intval($ServiceID)." and ProductID=".intval($setProductID).";";
                    $rsDelete=mysql_query($sqlDelete);

                    if($setQTY > 0){
                        $_POST['price'][$value]=preg_replace("/,/", "", $_POST['price'][$value]);
                        $sql="INSERT INTO ".$db_name.".car_service_detail (ServiceID, ProductID, QTY, UnitPrice, OrderNo) VALUES (".intval($ServiceID).", ".intval($setProductID).", ".intval($_POST['QTY'][$value]).", '".floatval($_POST['price'][$value])."', ".$count.");";
                        $rs=mysql_query($sql);
                        $ProductList.=",".intval($setProductID);
                        $count++;

                        //====================== start insert for vat qty report ======================//
                        $sqlInsert="INSERT INTO ".$db_name.".orderitems (ProductID, QTY, UnitPrice, PaidBy, PaidDate, Note, SellBy, FromStock, ServiceID, UnitUsed) VALUES (".intval($setProductID).", '".floatval($SetVatQTY)."', '".floatval($_POST['price'][$value])."', 'เงินสด', ".time().", '', 0, ".intval($Stock4Service).", ".intval($ServiceID).", ".$UnitUsed[0].");";
                        $rsInsert=mysql_query($sqlInsert);
                        $SellID=mysql_insert_id($Conn);
                        $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID, ServiceID, VatQTY, NoVatQTY) VALUES (".$Stock4Service.", ".intval($setProductID).", ".time().", ".$setQTY.", 'ขายสินค้า', ".intval($UserID).", ".($SellID*(-1)).", ".floatval($SetVatQTY).", ".floatval($SetNoVatQTY).");";
                        $rsStockHistory=mysql_query($sqlStockHistory);
                        //====================== end insert for vat qty report ======================//
                    }
                    if($ChangeQTY[$setProductID]!=0){
                        $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID) VALUES (".$Stock4Service.", ".intval($setProductID).", ".time().", ".($ChangeQTY[$setProductID]).", 'เปลี่ยนแปลงข้อมูลจาก ".$keepRealInput." เป็น ".intval($_POST['QTY'][$value])."<br>(ใบรับบริการ".$Text." เลขที่ ".$_POST["ServiceCode"].")', ".intval($UserID).");";
                        $rsStockHistory=mysql_query($sqlStockHistory);
                        $sqlStock="UPDATE ".$db_name.".product_stock set QTY=".$ChangeQTYCond." where ProductID=".intval($setProductID)." and StockID=".$Stock4Service.";";
                        $rsStock=mysql_query($sqlStock);
                    }
                }
                else{
                    $sqlDelete="DELETE FROM ".$db_name.".car_service_detail WHERE car_service_detail.ServiceID=".intval($ServiceID)." and ProductID=".intval($setProductID).";";
                    $rsDelete=mysql_query($sqlDelete);
                    if($setQTY > 0){
                        $_POST['price'][$value]=preg_replace("/,/", "", $_POST['price'][$value]);
                        $sql="INSERT INTO ".$db_name.".car_service_detail (ServiceID, ProductID, QTY, UnitPrice, OrderNo) VALUES (".intval($ServiceID).", ".intval($setProductID).", ".intval($_POST['QTY'][$value]).", '".floatval($_POST['price'][$value])."', ".$count.");";
                        $rs=mysql_query($sql);
                        $ProductList.=",".intval($setProductID);
                        $count++;
                    }
                }
            }
        }
        $sqlDelete="select car_service_detail.ProductID, car_service_detail.QTY, products.Type, orderitems.UnitUsed from ((".$db_name.".car_service_detail inner join ".$db_name.".products on products.ProductID=car_service_detail.ProductID) left join ".$db_name.".orderitems on car_service_detail.ServiceID=orderitems.ServiceID and car_service_detail.ProductID=orderitems.ProductID) where ServiceID=".intval($ServiceID)." and car_service_detail.ProductID not in (".$ProductList.") and products.Type='สินค้า';";
        $rsDelete=mysql_query($sqlDelete);
        //echo $sqlDelete."<br><br>";
        while($backToStock=mysql_fetch_row($rsDelete)){
            $sqlStockHistory="INSERT INTO ".$db_name.".product_history (StockID, ProductID, Date, QTY, ChangeNote, UserID) VALUES (".$Stock4Service.", ".intval($backToStock[0]).", ".time().", ".round($backToStock[1]*$backToStock[3], 2).", 'เปลี่ยนแปลงข้อมูลจาก ".$backToStock[1]." เป็น 0<br>(ใบรับบริการ".$Text." เลขที่ ".$_POST["ServiceCode"].")', ".intval($UserID).");";
            $rsStockHistory=mysql_query($sqlStockHistory);
            //echo $sqlStockHistory."<br><br>";
            $sqlStock="UPDATE ".$db_name.".product_stock set QTY=QTY+".round($backToStock[1]*$backToStock[3], 2)." where ProductID=".intval($backToStock[0])." and StockID=".$Stock4Service.";";
            $rsStock=mysql_query($sqlStock);
            //echo $sqlStock."<br><br>";
        }
        $sqlDelete="DELETE FROM ".$db_name.".car_service_detail WHERE car_service_detail.ServiceID=".intval($ServiceID)." and ProductID not in (".$ProductList.");";
        $rsDelete=mysql_query($sqlDelete);

        $sqlWashService="SELECT sum(QTY*UnitPrice) from ".$db_name.".car_service_detail where ServiceID=".intval($ServiceID).";";
        $rsWashService=mysql_query($sqlWashService);
        $WashService=mysql_fetch_row($rsWashService);
        $Summary=($WashService[0]-$_POST["DiscountVal"]);
        $sqlInsert="UPDATE ".$db_name.".account_daily set Total='".floatval($Summary)."' where Note='".intval($ServiceID)."';";
        $rsInsert=mysql_query($sqlInsert);
    }

    reInvoiceNo(date("n", time()), date("Y", time()));
    if(isset($_POST['EmpID'])){
        foreach ($_POST['EmpID'] as $key => $value) {
            if($value>0){
                $sql="INSERT INTO ".$db_name.".car_service_emp (ServiceID, EmpID) VALUES (".intval($ServiceID).", ".intval($value).");";
                $rs=mysql_query($sql);
            }
        }
    }
    header('location: carwash.php?print='.$ServiceID.'&serviceType='.intval($_POST['serviceType']));
}

include("header.php");

if(!isset($_REQUEST['back'])){
    $_REQUEST['back']='car_service';
}
if(isset($_REQUEST['print']) && intval($_REQUEST['print'])){
    $PrintNumTxt='';
    $textSize="font-size:10px;";
    $AddTxt='<br>';
    $InvDetail="";
    $count=1;
    $SubTotal=0;
    $EachTotal=0;
    $RealTotal=0;
    $sqlDetail="select car_service_detail.ProductID, QTY, UnitPrice, OrderNo, Code, products.Name from (".$db_name.".car_service_detail inner join ".$db_name.".products on products.ProductID=car_service_detail.ProductID) where ServiceID=".intval($_REQUEST['print'])." order by OrderNo ASC;";
    $rsDetail=mysql_query($sqlDetail);
    while($Detail=mysql_fetch_row($rsDetail)){
        $EachTotal=($Detail[2]*$Detail[1]);
        $InvDetail.='
                    <tr><td class="text-center">
                        '.$count.'
                    </td>
                    <td width="55%">
                        '.$Detail[5].'
                    </td>
                    <td width="10%">
                        <div class="text-center">'.$Detail[1].'</div>
                    </td>
                    <td width="12%">
                        <div class="text-right">'.number_format($Detail[2], 2).'</div>
                    </td>
                    <td width="12%">
                        <div class="text-right">'.number_format($EachTotal, 2).'</div>
                    </td><tr>';
        $SubTotal+=round($EachTotal, 2);
        $count++;
    }


    $sqlService="select ServiceCode, ServiceDate, CustName, CarCode, ServiceNote, PercentDiscount, DiscountVal, OrderNo, PrintNum, Address1, Address2, Color, CarType, Brand, ServiceType from (((".$db_name.".car_service left join ".$db_name.".customer on car_service.CustID=customer.CustID) left join ".$db_name.".customer_car on car_service.CustCar=customer_car.CarID) left join ".$db_name.".car_brand on car_brand.ID=customer_car.CarBrand) where car_service.ID=".intval($_REQUEST['print']).";";
    $rsService=mysql_query($sqlService);
    $Service=mysql_fetch_row($rsService);
    $DiscountText='<div class="in-total">ส่วนลด</div>';
    if($Service[5]>0){
        $DiscountText='<div class="in-total">ส่วนลด '.$Service[5].' %</div>';
    }
    if(intval($Service[8])>1){
        $PrintNumTxt=' / พิมพ์ครั้งที่ '.$Service[8];
    }

    $count=1;
    $EmpList = array('', '', '', '', '');
    $sqlEmp="select concat(FirstName, ' ', LastName) from ".$db_name.".car_service_emp inner join ".$db_name.".employee on car_service_emp.EmpID=employee.EmpID where car_service_emp.ServiceID=".intval($_REQUEST['print'])." and employee.EmpID!=1 order by FirstName ASC, LastName ASC";
    $rsEmp=mysql_query($sqlEmp);
    while($Emp=mysql_fetch_row($rsEmp)){
        $EmpList[$count]=$Emp[0];
        $count++;
    }
    $RealTotal=($SubTotal-$Service[6]);

    $EmployeeList='';
    if($Service[14]==1 && intval($WashNameShow)){
        $EmployeeList='<tr><td colspan="3">
            <table width="100%"><tr>
            <td width="10%" nowrap>พนักงานบริการ</td>
            <td width="20%">1. '.$EmpList[1].'</td>
            <td width="20%">2. '.$EmpList[2].'</td>
            <td width="20%">3. '.$EmpList[3].'</td>
            <td>4. '.$EmpList[4].'</td>
            </tr></table>
        </td></tr>';
    }
    if($Service[14]==1){ $Text='ล้างรถ'; }else{ $Text='เปลี่ยนน้ำมันเครื่อง'; }
    $_REQUEST['serviceType']=$Service[14];
    print('
    <section class="pageContent">
        <div class="title-body">
            <h2>ใบรับบริการ '.$Text.'</h2>
        </div>
        '.$AddTxt.'
        <div id="pageContent" class="content-center invoice">

            <div class="panel panel-default invoice_report">
                <div class="panel-body">
                    <p><strong style="font-size:15px;">ใบรับรถ/ใบรับบริการ</strong></p>
                    <table width="100%" style="border-collapse:separate; border-spacing:10px;'.$textSize.'">
                        <tr>
                            <td width="7%">
                                วันที่:
                            </td>
                            <td colspan="5" width="70%">'.date('j/m/Y', $Service[1]).'</td>
                            <td width="5%">
                                เลขที่:
                            </td>
                            <td nowrap>'.$Service[0].' '.$PrintNumTxt.'</td>
                        </tr>
                        <tr>
                            <td>
                                ชื่อลูกค้า:
                            </td>
                            <td colspan="5">'.$Service[2].'</td>
                            <td width="5%" nowrap>
                                ลำดับที่:
                            </td>
                            <td nowrap>'.$Service[7].'</td>
                        </tr>
                        <tr>
                            <td>
                                ที่อยู่:
                            </td>
                            <td colspan="7">'.$Service[9].' '.$Service[10].'</td>
                        </tr>
                        <tr>
                            <td nowrap>
                                ทะเบียนรถ:
                            </td>
                            <td width="20%">'.$Service[3].'</td>
                            <td nowrap width="8%">
                                เวลารถเข้า:
                            </td>
                            <td colspan="5">'.date('H:i', $Service[1]).'</td>
                        </tr>
                        <tr>
                            <td>
                                รถยี่ห้อ:
                            </td>
                            <td>'.$Service[13].'</td>
                            <td>
                                สี:
                            </td>
                            <td>'.$Service[11].'</td>
                            <td width="6%">
                                ประเภท:
                            </td>
                            <td colspan="3">'.$Service[12].'</td>
                        </tr>
                    </table>

                    <br>
                    <table width="100%" class="table table-bordered table-default" style="'.$textSize.'">
                        <thead>
                            <tr>
                                <th><div class="text-center">ลำดับที่</div></th>
                                <th><div class="text-center">รายการ</div></th>
                                <th><div class="text-center">จำนวน</div></th>
                                <th><div class="text-center">ราคาต่อหน่วย</div></th>
                                <th><div class="text-center">จำนวนเงิน</div></th>
                            </tr>
                        </thead>
                        '.$InvDetail.'
                        <tr>
                            <td colspan="4"><div class="in-total">รวมเงิน</div></td>
                            <td><div class="in-total" style="'.$textSize.'">'.number_format($SubTotal, 2).'</div></td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                '.$DiscountText.'
                            </td>
                            <td align="right">
                                <div class="in-total" style="'.$textSize.'">'.number_format($Service[6], 2).'</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4"><div class="in-total">ยอดเงินสุทธิ</div></td>
                            <td>
                                <div class="in-total" style="'.$textSize.'">'.number_format($RealTotal, 2).'</div>
                            </td>
                        </tr>
                    </table>

                    <br><br>
                    <table style="border-collapse:separate; border-spacing:10px; width:100%;'.$textSize.'">
                        <tr>
                            <td width="33%">
                                <p>ผู้รับรถ .......................................................</p>
                            </td>
                            <td width="33%">
                                <p>ลูกค้ารับรถคืน .......................................................</p>
                            </td>
                            <td>
                                <p>เวลา .......................................................</p>
                            </td>
                        </tr>
                        '.$EmployeeList.'
                        <tr>
                            <td width="5%">หมายเหตุ</td>
                            <td align="left" colspan="2">'.$Service[4].'</td>
                        </tr>
                    </table>
                </div>

                <div id="actionBar" style="float:right; clear:both;"><br><br>
                    <input type="hidden" id="backPage" value="'.$_REQUEST['back'].'.php">
                    <!--<button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับไปหน้าหลัก</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-->
                    <button type="button" class="btn btn-primary btn-rounder" onclick="javascript:location.href=\'carwash.php?serviceType='.$_REQUEST['serviceType'].'&back='.$_REQUEST['back'].'\';">สร้างใบรับบริการใหม่</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-success btn-rounder" onclick="printService();">พิมพ์ใบรับบริการ</button>&nbsp;&nbsp;&nbsp;
                    <br><br><br>
                </div>
            </div>

        </div>
    </section>');
}
else{
    $CustomerCar='';
    $sqlCust="SELECT CarCode from ".$db_name.".customer_car where Deleted=0 and CustomerID>0 and CarID>0 order by CarCode ASC;";
    $rsCust=mysql_query($sqlCust);
    while($CustInfo=mysql_fetch_row($rsCust)){
        $CustomerCar.="*".$CustInfo[0];
    }
    $CustomerCar=substr($CustomerCar, 1);

    $alertText='';
    if(!isset($_REQUEST['serviceType'])){
        $_REQUEST['serviceType']=1;
    }
    if(is_file('results-'.$_REQUEST['serviceType'].'-'.$UserID.'.json')){
        $loadJson=1;
    }

    $setCode = findMax($_REQUEST['serviceType']);
    $EmpName = array();
    $EmpWash = array();
    $countWashEmp=1;
    $sqlPosition = "select concat(FirstName, ' ', LastName), EmpID, NickName, AutoInForm from ".$db_name.".employee inner join ".$db_name.".empposition on empposition.PositionID=employee.PositionID where employee.EmpID!=1 and employee.Deleted=0 order by FirstName ASC, LastName ASC;";
    $rsPosition = mysql_query($sqlPosition);
    while($Position=mysql_fetch_row($rsPosition)){
        $EmpName[$Position[1]]=$Position[0];
        if($Position[2]){
            $EmpName[$Position[1]].=" (".$Position[2].")";
        }
        if($Position[3]){
            $EmpWash[$countWashEmp]=$Position[1];
            $countWashEmp++;
        }
    }

    $PrintForm = 1;
    $ProductArr = array();
    $ProductNameArr = array();
    $ServiceArr = array();
    $ServiceNameArr = array();
    $AllProduct = array();
    if($_REQUEST['serviceType']==1){ // ล้างรถ
        $ProdCondition=" and UseFor!='ถ่ายน้ำมันเครื่อง'";
    }
    else{ // เปลี่ยนถ่ายน้ำมันเครื่อง
        $ProdCondition=" and UseFor!='บริการล้างรถ'";
    }
    $sqlProduct="SELECT ProductID, Code, Name, SellPrice, Type, UseFor from ".$db_name.".products where Deleted=0".$ProdCondition." and Special=0 order by Code ASC";
    $rsProduct=mysql_query($sqlProduct);
    while($Product=mysql_fetch_row($rsProduct)){
        if($Product[4]=='สินค้า'){
            $sqlProductQTY="SELECT sum(product_stock.QTY) as ProductQTY from ".$db_name.".product_stock where product_stock.ProductID=".$Product[0]." and product_stock.StockID=".$Stock4Service.";";
            $rsProductQTY=mysql_query($sqlProductQTY);
            $ProductQTY=mysql_fetch_row($rsProductQTY);
            if(intval($ProductQTY[0]) > 0){
                $ProductArr[$Product[0]]=$Product[1];
                $ProductNameArr[$Product[0]]=$Product[2];
                $ProductQTYArr[$Product[0]]=$ProductQTY[0];
                $AllProduct[$Product[0]]=$Product[2]."*****".$Product[3]."*****".$ProductQTY[0];
            }
        }
        else{
            $ServiceArr[$Product[0]]=$Product[1];
            $ServiceNameArr[$Product[0]]=$Product[2];
            $AllProduct[$Product[0]]=$Product[2]."*****".$Product[3]."*****0";
        }
    }

    $EditID=0;
    $Note='';
    $CarCode='';
    $CustName='';
    $DiscountVal='';
    $ServiceCode=0;
    $PercentDiscount='';
    $SubTotal=0;
    $RealTotal=0;
    $PrintNum=0;
    $IsDeleted=0;
    $delectTextArea="";
    $serviceDate=date('d/m/Y', time());
    $EmpList = array('', '', '', '', '');
    $QTY = array('', '', '', '', '', '');
    $Name = array('', '', '', '', '', '');
    $Code = array('', '', '', '', '', '');
    $Type = array('', '', '', '', '', '');
    $ProdID = array('', '', '', '', '', '');
    $UnitPrice = array(0, 0, 0, 0, 0, 0);
    $EachTotal = array(0, 0, 0, 0, 0, 0);
    if(isset($_POST['editCode']) && trim($_POST['editCode'])){
        $loadJson=0;
        $sqlService="select ServiceCode, ServiceDate, CustName, CarCode, ServiceNote, PercentDiscount, DiscountVal, OrderNo, PrintNum, car_service.ID, car_service.Deleted from ((".$db_name.".car_service left join ".$db_name.".customer on car_service.CustID=customer.CustID) left join ".$db_name.".customer_car on car_service.CustCar=customer_car.CarID) where car_service.ServiceCode like '".intval($_REQUEST['editCode'])."' and ServiceType=".intval($_REQUEST['serviceType']).";";
        $rsService=mysql_query($sqlService);
        $Service=mysql_fetch_row($rsService);
        if($Service[9]>0){
            $setCode[0]=$Service[0];
            $ServiceCode=$Service[0];
            $setCode[1]=$Service[7];
            $IsDeleted=$Service[10];
            $EditID=$Service[9];
            $serviceDate=date('j/m/Y', $Service[1]);
            $CarCode=$Service[3];
            $CustName=$Service[2];
            $Note=$Service[4];
            $PercentDiscount=$Service[5];
            $DiscountVal=$Service[6];
            $PrintNum=$Service[8];
            if(!$IsDeleted){
                $PrintNum=($Service[8]+1);
            }

            $count=1;
            $sqlDetail="select car_service_detail.ProductID, QTY, UnitPrice, Code, products.Name, products.Type from (".$db_name.".car_service_detail inner join ".$db_name.".products on products.ProductID=car_service_detail.ProductID) where ServiceID=".intval($EditID)." order by OrderNo ASC;";
            $rsDetail=mysql_query($sqlDetail);
            while($Detail=mysql_fetch_row($rsDetail)){
                $EachTotal[$count]=($Detail[2]*$Detail[1]);
                $SubTotal+=$EachTotal[$count];
                $ProdID[$count]=$Detail[0];
                $Name[$count]=$Detail[4];
                $Code[$count]=$Detail[3];
                $QTY[$count]=$Detail[1];
                $UnitPrice[$count]=round($Detail[2],2);
                $Type[$count]=$Detail[5];
                $count++;
            }
            $RealTotal=($SubTotal-$DiscountVal);

            $count=1;
            $sqlEmp="select EmpID from ".$db_name.".car_service_emp where car_service_emp.ServiceID=".intval($EditID)." order by EmpID ASC;";
            $rsEmp=mysql_query($sqlEmp);
            while($Emp=mysql_fetch_row($rsEmp)){
                $EmpList[$count]=$Emp[0];
                $count++;
            }
        }
        else{
            $PrintForm = 0;
            if($_REQUEST['serviceType']==1){ $TypeText='ล้างรถ'; }else{ $TypeText='เปลี่ยนน้ำมันเครื่อง'; }
            print('
            <section class="pageContent car_wash">
                <div class="title-body">
                    <h2>ใบรับบริการ '.$TypeText.'</h2>
                </div>

                <div class="content-center invoice">
                    <div class="panel panel-default">
                        <div class="panel-body"><br><br>
                        <p class="passcode_send-error" style="margin-bottom:20px;"><strong>ไม่พบใบรับบริการเลขที่ '.$_REQUEST['editCode'].'</strong></p>
                        </div><br>
                    </div>
                </div>
            </section>');
        }
    }

    if($PrintForm){
        if($_REQUEST['serviceType']==1){
            $ServiceTypeTxt='ล้างรถ';
        }
        else{
            $ServiceTypeTxt='เปลี่ยนน้ำมันเครื่อง';
        }

        $Disableinput="";
        if($IsDeleted){
            $Disableinput=" disabled";
            $delectTextArea='<div class="alert alert-warning">ใบรับบริการนี้อยู่ในสถานะถูกยกเลิก ไม่สามารถเปลี่ยนแปลงข้อมูลได้</div>';
        }
?>
        <section class="pageContent car_wash">
        <div class="title-body">
            <h2>ใบรับบริการ <?php print($ServiceTypeTxt); if(isset($EditID) && intval($EditID)){ print("&nbsp;&nbsp;<span style=\"color:red;\">(แก้ไขรายการ)</span>"); } ?></h2>
        </div>

        <div class="content-center invoice">

            <div class="panel panel-default">
                <div class="panel-body">
                    <?php print($delectTextArea); ?>
                    <form action="carwash.php" id="carwashForm" name="carwashForm" method="post" class="form-horizontal" role="form" onsubmit="if(checkService()){ deleteJson(); document.getElementById('carwashForm').submit(); }else{ return false; }" autocomplete="off">
                        <input type="hidden" name="serviceType" id="serviceType" value="<?php print($_REQUEST['serviceType']); ?>">
                        <input type="hidden" name="PrintNum" id="PrintNumVal" value="<?php if(isset($PrintNum)){ print($PrintNum); } ?>">
                        <input type="hidden" name="EditID" id="EditID" value="<?php print($EditID); ?>">
                        <input type="hidden" name="ServiceCode" id="ServiceCode" value="<?php print($ServiceCode); ?>">
                        <input type="hidden" name="back" id="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" id="EmployeeID" value="<?php print($UserID); ?>">
                        <input type="hidden" name="InvNo2Show" value="<?php print($setCode[0]."-".$serviceDate."-".$setCode[1]); ?>">
                        <?php print($alertText); ?>
                        <div class="col-sm-4 form-group" style="clear:both;">
                            <b class="col-sm-5 text-right">เลขที่เอกสาร:</b>
                            <div class="col-sm-7">
                                <span id="InvTxtNo"><?php print($setCode[0]); ?></span>
                                <span id="PrintNum"><?php if($PrintNum>1){ print(' / พิมพ์ครั้งที่ '.$PrintNum); } ?></span>
                                &nbsp;&nbsp;
                                <?php if($PermissionNo==3){ print('<i class="fa fa-pencil pointer" onclick="javascript:updateService('.$_REQUEST['serviceType'].');" title="แก้ไขใบรับบริการ"></i>'); } ?>
                            </div>
                        </div>
                        <div class="col-sm-4 form-group">
                            <b class="col-sm-6 text-right">วันที่:</b>
                            <div class="col-sm-5" id="InvDateShow">
                                <?php print($serviceDate); ?>
                            </div>
                        </div>
                        <div class="col-sm-4 form-group">
                            <b class="col-sm-6 text-right">ลำดับที่:</b>
                            <div class="col-sm-5" id="OrderShow">
                                <?php print($setCode[1]); ?>
                            </div>
                        </div>

                        <div class="col-sm-4 form-group">
                            <label class="col-sm-5 control-label">ทะเบียนรถ:</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="CarCode" id="CarCode" value="<?php print($CarCode); ?>" onblur="javascript:Cust4Car(this.value); saveTemporary();"<?php print($Disableinput); ?>>
                            </div>
                        </div>
                        <div class="col-sm-8 form-group">
                            <label class="col-sm-3 control-label" style="padding-right:15px;">ชื่อลูกค้า:</label>
                            <div class="col-sm-5" style="padding-left:5px;">
                                <input type="text" class="form-control" name="CustomerName" id="CustomerName" value="<?php print($CustName); ?>" onchange="javascript:saveTemporary();"<?php print($Disableinput); ?>>
                            </div>
                        </div>

                        <div class="col-sm-12 form-group">
                            <label style="width:13.3%;" class="col-sm-2 control-label">หมายเหตุ:</label>
                            <div style="width:86.0%;" class="col-sm-10" style="padding-left:5px;"><input type="text" class="form-control" name="ServiceNote" id="ServiceNote" value="<?php print($Note); ?>" onchange="javascript:saveTemporary();"<?php print($Disableinput); ?>>
                            </div>
                        </div>
                        <?php
                        if($_REQUEST['serviceType']==1 && intval($WashNameShow)){
                        ?>
                        <div class="col-sm-4 form-group">
                            <input type="hidden" id="serviceTypeCheck" value="<?php print($WashNameShow); ?>">
                            <label class="col-sm-5 control-label">พนักงานล้างรถ:</label>
                            <div class="col-sm-7">
                                <select name="EmpID[1]" id="EmpID-1" class="form-control" style="width:170px;" onchange="javascript:saveTemporary();"<?php print($Disableinput); ?>>
                                    <option value="0">ไม่ระบุ</option>
                                    <?php
                                    foreach ($EmpName as $key => $value) {
                                        print('<option value="'.$key.'"');
                                        if($EmpList[1]==$key){
                                            print(' selected');
                                        }
                                        else if(!$EmpList[1] && isset($EmpWash[1]) && $EmpWash[1]==$key){
                                            print(' selected');
                                        }
                                        print('>'.$value.'</option>');
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3 form-group">
                            <div class="col-sm-2">&nbsp;</div>
                            <div class="col-sm-10">
                                <select name="EmpID[2]" id="EmpID-2" class="form-control" style="width:170px;" onchange="javascript:saveTemporary();"<?php print($Disableinput); ?>>
                                    <option value="0">ไม่ระบุ</option>
                                    <?php
                                    foreach ($EmpName as $key => $value) {
                                        print('<option value="'.$key.'"');
                                        if($EmpList[2]==$key){
                                            print(' selected');
                                        }
                                        else if(!$EmpList[2] && isset($EmpWash[2]) && $EmpWash[2]==$key){
                                            print(' selected');
                                        }
                                        print('>'.$value.'</option>');
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3 form-group">
                            <div class="col-sm-2">&nbsp;</div>
                            <div class="col-sm-10">
                                <select name="EmpID[3]" id="EmpID-3" class="form-control" style="width:170px;" onchange="javascript:saveTemporary();"<?php print($Disableinput); ?>>
                                    <option value="0">ไม่ระบุ</option>
                                    <?php
                                    foreach ($EmpName as $key => $value) {
                                        print('<option value="'.$key.'"');
                                        if($EmpList[3]==$key){
                                            print(' selected');
                                        }
                                        else if(!$EmpList[3] && isset($EmpWash[3]) && $EmpWash[3]==$key){
                                            print(' selected');
                                        }
                                        print('>'.$value.'</option>');
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3 form-group">
                            <div class="col-sm-2">&nbsp;</div>
                            <div class="col-sm-10">
                                <select name="EmpID[4]" id="EmpID-4" class="form-control" style="width:170px;" onchange="javascript:saveTemporary();"<?php print($Disableinput); ?>>
                                    <option value="0">ไม่ระบุ</option>
                                    <?php
                                    foreach ($EmpName as $key => $value) {
                                        print('<option value="'.$key.'"');
                                        if($EmpList[4]==$key){
                                            print(' selected');
                                        }
                                        else if(!$EmpList[4] && isset($EmpWash[4]) && $EmpWash[4]==$key){
                                            print(' selected');
                                        }
                                        print('>'.$value.'</option>');
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                        <select id="productOption" style="display:none;"<?php print($Disableinput); ?>>
                            <option value="-1">&nbsp;</option>
                            <?php
                            $ProductArrOption="";
                            foreach($ProductArr as $key => $value) {
                                $ProductArrOption.='<option value="'.$key.'">'.$value.'</option>';
                            }
                            print($ProductArrOption);
                            ?>
                        </select>
                        <select id="serviceOption" style="display:none;"<?php print($Disableinput); ?>>
                            <option value="-1">&nbsp;</option>
                            <?php
                            $ServiceArrOption="";
                            foreach ($ServiceArr as $key => $value) {
                                $ServiceArrOption.='<option value="'.$key.'">'.$value.'</option>';
                            }
                            print($ServiceArrOption);
                            ?>
                        </select>

                        <div class="table-responsive" style="clear:both;"><br>
                            <table class="table table-bordered table-default">
                                <thead>
                                    <tr>
                                        <th><div class="text-center">ชนิด</div></th>
                                        <th><div class="text-center">รหัสสินค้า/บริการ</div></th>
                                        <th><div class="text-center">รายการ</div></th>
                                        <th><div class="text-center">ปริมาณ</div></th>
                                        <th nowrap><div class="text-center">ราคาต่อหน่วย</div></th>
                                        <th><div class="text-center">จำนวนเงิน</div></th>
                                    </tr>
                                </thead>
                                <?php
                                for($i=1; $i<6; $i++){
                                ?>
                                <tr>
                                    <td id="<?php print($i); ?>" nowrap>
                                        <?php
                                        if(count($ProductArr)){
                                            print('<input type="radio" id="ProductType-'.$i.'" name="ProductType['.$i.']" value="สินค้า"');
                                            if(!count($ServiceArr) || !$Type[$i] || $Type[$i]=='สินค้า'){
                                                print(" checked");
                                            }
                                            print($Disableinput.'> สินค้า');
                                            print('&nbsp;&nbsp;&nbsp;');
                                        }
                                        if(count($ServiceArr)){
                                            print('<input type="radio" id="ServiceType-'.$i.'" name="ProductType['.$i.']" value="บริการ"');
                                            if(!count($ProductArr) || $Type[$i]=='บริการ'){
                                                print(" checked");
                                            }
                                            print($Disableinput.'> บริการ');
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <input type="hidden" name="RecCount[]" value="<?php print($i); ?>">
                                        <select name="ProducID[<?php print($i); ?>]" id="ProducID-<?php print($i); ?>" class="form-control" style="width:150px;" onchange="javascript:setProductName(this.value, <?php print($i); ?>, '', 0, 0 , 1);"<?php print($Disableinput); ?>>
                                            <option value="-1">&nbsp;</option>
                                            <?php
                                            if($Type[$i]==''){
                                                if(!count($ProductArr)){
                                                    print($ServiceArrOption);
                                                }
                                                else{
                                                    print($ProductArrOption);
                                                }
                                            }
                                            else{
                                                if($Type[$i]=='สินค้า'){
                                                    foreach ($ProductArr as $key => $value) {
                                                        print('<option value="'.$key.'"');
                                                        if($ProdID[$i]==$key){
                                                            print(" selected");
                                                        }
                                                        print('>'.$value.'</option>');
                                                    }
                                                }
                                                else{
                                                    foreach ($ServiceArr as $key => $value) {
                                                        print('<option value="'.$key.'"');
                                                        if($ProdID[$i]==$key){
                                                            print(" selected");
                                                        }
                                                        print('>'.$value.'</option>');
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td width="55%">
                                        <!-- <input type="text" class="form-control" id="Name4Prod-<?php print($i); ?>" name="ProductName[<?php print($i); ?>]" value="<?php print($Name[$i]); ?>"> -->
                                        <select id="productNameOption" style="display:none;"<?php print($Disableinput); ?>>
                                            <option value="-1">&nbsp;</option>
                                            <?php
                                            $ProductNameOption="";
                                            foreach ($ProductNameArr as $key => $value) {
                                                $ProductNameOption.='<option value="'.$key.'">'.$value.'</option>';
                                            }
                                            print($ProductNameOption);
                                            ?>
                                        </select>
                                        <select id="serviceNameOption" style="display:none;"<?php print($Disableinput); ?>>
                                            <option value="-1">&nbsp;</option>
                                            <?php
                                            $ServiceNameOption="";
                                            foreach ($ServiceNameArr as $key => $value) {
                                                $ServiceNameOption.='<option value="'.$key.'">'.$value.'</option>';
                                            }
                                            print($ServiceNameOption);
                                            ?>
                                        </select>

                                        <select name="ProductName[<?php print($i); ?>]" id="Name4Prod-<?php print($i); ?>" class="form-control" style="width:350px;" onchange="javascript:setProductName(this.value, <?php print($i); ?>, '', 0, 0, 2);"<?php print($Disableinput); ?>>
                                            <option value="-1">&nbsp;</option>
                                            <?php
                                            if($Type[$i]==''){
                                                if(!count($ProductNameArr)){
                                                    print($ServiceNameOption);
                                                }
                                                else{
                                                    print($ProductNameOption);
                                                }
                                            }
                                            else{
                                                if($Type[$i]=='สินค้า'){
                                                    foreach ($ProductNameArr as $key => $value) {
                                                        print('<option value="'.$key.'"');
                                                        if($ProdID[$i]==$key){
                                                            print(" selected");
                                                        }
                                                        print('>'.$value.'</option>');
                                                    }
                                                }
                                                else{
                                                    foreach ($ServiceNameArr as $key => $value) {
                                                        print('<option value="'.$key.'"');
                                                        if($ProdID[$i]==$key){
                                                            print(" selected");
                                                        }
                                                        print('>'.$value.'</option>');
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td width="18%">
                                        <input type="text" class="form-control text-center number" id="QTY-<?php print($i); ?>" name="QTY[<?php print($i); ?>]" value="<?php print($QTY[$i]); ?>" onchange="javascript:if(this.value>0){ setProductName(0, <?php print($i); ?>, this.value, 0, 0, 0); }else{ document.getElementById('QTY-<?php print($i); ?>').value=<?php if($QTY[$i]){ print($QTY[$i]); }else{ print('1'); } ?> }"<?php print($Disableinput); ?>>
                                    </td>
                                    <td width="12%">
                                        <input type="text" class="form-control text-right" id="UnitPrice-<?php print($i); ?>" name="ProductName[<?php print($i); ?>]" value="<?php if($UnitPrice[$i]){ print(number_format($UnitPrice[$i], 2)); } ?>" onchange="javascript:if(this.value>0){ setProductName(0, <?php print($i); ?>, 0, 0, this.value, 0); }else{ alert('กรุณาใส่ราคาต่อหน่วย'); document.getElementById('UnitPrice-<?php print($i); ?>').value=<?php if($UnitPrice[$i]){ print(number_format($UnitPrice[$i], 2)); }else{ print('0'); } ?> }"<?php print($Disableinput); ?>>

                                        <!-- <div class="invoice-total text-right" id="UnitPrice-<?php print($i); ?>">
                                            <?php if($UnitPrice[$i]){ print(number_format($UnitPrice[$i], 2)); } ?>
                                        </div> -->
                                        <input type="hidden" name="price[<?php print($i); ?>]" id="price-<?php print($i); ?>" value="<?php print(number_format($UnitPrice[$i], 2)); ?>">
                                    </td>
                                    <td width="12%">
                                        <div class="invoice-total text-right" id="TotalPrice-<?php print($i); ?>"><?php if($EachTotal[$i]){ print(number_format($EachTotal[$i], 2)); } ?></div>
                                    </td>
                                </tr>
                                <?php
                                }
                                ?>

                                <tr>
                                    <td colspan="5"><div class="in-total">รวมเงิน</div></td>
                                    <td><div class="in-total" id="SubTotal"><?php print(number_format($SubTotal, 2)); ?></div></td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <div class="in-total">ส่วนลด <input type="text" class="form-control inline_input" id="PercentDis" name="PercentDiscount" value="<?php if($PercentDiscount>0){ print(number_format($PercentDiscount, 2)); } ?>" style="width:50px" onchange="javascript:setProductName(0, 0, 0, 1, 0, 0);"<?php print($Disableinput); ?>> %</div>
                                    </td>
                                    <td align="right">
                                        <input type="text" class="form-control text-right price" id="DiscountVal" name="DiscountVal" value="<?php if($DiscountVal>0){ print(number_format($DiscountVal, 2)); } ?>" style="width:90px" onchange="javascript:setProductName(0, 0, 0, -1, 0, 0);"<?php print($Disableinput); ?>>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5"><div class="in-total">ยอดเงินสุทธิ</div></td>
                                    <td>
                                        <div class="in-total" id="Total"><?php print(number_format($RealTotal, 2)); ?></div>
                                        <input type="hidden" name="ServiceTotal" id="ServiceTotal" value="<?php print(number_format($RealTotal, 2)); ?>">
                                    </td>
                                </tr>
                            </table>

                            <?php
                            if(!$IsDeleted){
                            ?>
                                <div style="float:right; clear:both;"><br>
                                <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                                <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <button type="reset" class="btn btn-danger btn-rounder" onclick="javascript:deleteJson();">รีเซ็ตข้อมูล</button>
                                <?php
                                if(isset($EditID) && intval($EditID)){
                                    print("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type=\"button\" onclick=\"javascript:cancelService('".$setCode[0]."', '".$ServiceTypeTxt."', ".$EditID.");\" class=\"btn btn-warning btn-rounder\">ยกเลิกใบบริการ</button>");
                                }
                                print("<br><br>");
                                ?>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>

    <form action="customer.php" method="post" role="form" id="submitForm">
        <input type="hidden" id="UpdateItem" name="UpdateItem" value="0">
        <input type="hidden" id="backPage" name="backPage" value="coupons.php">
    </form>

<?php
    }
}
include("footer.php");
?>