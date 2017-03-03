<?php
include("dbvars.inc.php");
if(isset($_POST["PriceWarningDone"]) && intval($_POST["PriceWarningDone"])){
    setcookie("warningDate", $_POST["Date2Warning"], time()+(86400*3), "/");
    $_COOKIE["warningDate"]=$_POST["Date2Warning"];
}
else if(isset($_POST['ConfirmNow']) && intval($_POST['ConfirmNow'])){
    foreach($_POST["OilPrice"] as $key => $value) {
        $setDatePrice=date("Y-m-j", time());
        if(isset($_POST["OilPrice"][$key]) && intval($_POST["OilPrice"][$key])){
            $PriceSet=$_POST["OilPrice"][$key];
        }
        else{
            $PriceSet=$_POST["OldOilPrice"][$key];
        }
        $PriceSet=preg_replace("/,/", "", $PriceSet);
        $sqlInsert="INSERT INTO ".$db_name.".oil_price(OilID, RecordDate, Prices, RecordTime) VALUES (".intval($key).", '".$setDatePrice."', '".floatval($PriceSet)."', '".date("H:i:s", time())."');";
        $rsInsrte=mysql_query($sqlInsert);
    }
    exit();
}
else if(isset($_POST['OldOilPrice'])){
    print('<form name="ConfirmPriceForm" method="post" class="form-horizontal" role="form" autocomplete="off">
            <table class="table table-condensed table-striped table-default">
            <tr>
                <th>&nbsp;</th>
                <th>ชนิดน้ำมัน</th>
                <th>ราคาน้ำมัน</th>
            </tr>');
    asort($_POST["OilName2"]);
    foreach($_POST["OilName2"] as $key => $value) {
        if(isset($_POST["OilPrice"][$key]) && intval($_POST["OilPrice"][$key])){
            $PriceSet='<span style="color:blue;">'.number_format($_POST["OilPrice"][$key], 2).'</span>';
        }
        else{
            $PriceSet=number_format($_POST["OldOilPrice"][$key], 2);
        }
        $HiddenPriceSet=round($_POST["OldOilPrice"][$key], 2);
        print('<tr>
                <td>&nbsp;</td>
                <td align="left">'.$_POST['OilName2'][$key].'</td>
                <td align="left">'.$PriceSet.'<input type="hidden" name="SaveOilPrice['.$key.']" value="'.$HiddenPriceSet.'"></td>
            </tr>');
    }
    print('</table><br>
            <div>
                <button type="button" class="btn btn-success" onclick="javascript:confirmPage(2);">ยืนยันราคา</button>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="btn btn-danger" onclick="javascript:confirmPage(0);">ย้อนกลับ</button>
                <button id="closeUpdateForm" data-dismiss="modal" style="visibility: hidden;"></button>
            </div>
        </form>');
    exit();
}
else if(isset($_POST["IDUpdate"]) && intval($_POST["IDUpdate"])){
    if($_POST["RecordType"]==1){
        $sqlUpdate="UPDATE ".$db_name.".coupon SET RealUse='".floatval(intval($_POST["UpdateRealUse"]))."' WHERE coupon.ID=".intval($_POST["IDUpdate"]).";";
    }else{
        $sqlUpdate="UPDATE ".$db_name.".credit_billing SET RealUsed='".floatval(intval($_POST["UpdateRealUse"]))."' WHERE credit_billing.CreditBilling=".intval($_POST["IDUpdate"]).";";
    }
    $rsUpdate=mysql_query($sqlUpdate);
}
else if(isset($_POST["GetCouponTotal"]) && intval($_POST["GetCouponTotal"])){
	$condition="";
	$AllList=0;
    $couponListArr=explode(',', $_POST["CouponCodeList"]);
    foreach($couponListArr as $key1 => $value1) {
        if(preg_match('#-#', $value1)){
            $LongList=explode('-', $value1);
            for($i=intval($LongList[0]); $i<=intval($LongList[1]); $i++){
                $AllList.=",'".$i."'";
            }
        }
        else{
            $AllList.=",'".trim($value1)."'";
        }
    }
    $condition.="(Price='".intval($_POST["GetCouponTotal"])."' and CouponCode in (".$AllList."))";
    $sqlCoupon="SELECT sum(if(Status=3, Price, 0)) as canSell, sum(if(Status!=3, Price, 0)) as noForSell from ".$db_name.".coupon where ".$condition.";";
    $rsCoupon=mysql_query($sqlCoupon);
    $CouponTotal=mysql_fetch_row($rsCoupon);
    print(intval($CouponTotal[0])."-".intval($CouponTotal[1]));
    exit();
}
else if(isset($_POST['DeleteEmpJson'])){
	unlink('results-'.$_POST['serviceType'].'-'.$UserID.'.json');
}
else if(isset($_POST["MakeBillingCustID"])){
    $BillingDate=time();
    $CollectDate=time();
    $CanBilling=0;
    $sqlBillingChk="SELECT DayBeforePay from ".$db_name.".customer where Deleted=0 and CustID=".intval($_REQUEST["MakeBillingCustID"]).";";
    $rsBillingChk=mysql_query($sqlBillingChk);
    $BillingChhk=mysql_fetch_row($rsBillingChk);
    if($BillingChhk[0]){
        $CollectDate=strtotime(date('Y-m-d', $BillingDate).' +'.$BillingChhk[0].' day');
    }
    $sqlUse="SELECT sum(RealUsed) from ".$db_name.".credit_billing where Status=0 and CustID=".intval($_REQUEST["MakeBillingCustID"])." and Confirmed=1;";
    $rsUse=mysql_query($sqlUse);
    $UseTotal=mysql_fetch_row($rsUse);
    $sqlInsert="INSERT INTO ".$db_name.".billing_history (BillingDate, CollectSchedule, PaidDate, Total, CustID) VALUES (".$BillingDate.", ".$CollectDate.", 0, '".floatval($UseTotal[0])."', ".intval($_REQUEST["MakeBillingCustID"]).");";
    $rsInsert=mysql_query($sqlInsert);
    $HistoryID=mysql_insert_id($Conn);

    $sqlUpdate="UPDATE ".$db_name.".credit_billing SET Status=".$HistoryID." WHERE Status=0 and CustID=".intval($_REQUEST["MakeBillingCustID"])." and Confirmed>0;";
    $rsUpdate=mysql_query($sqlUpdate);
    exit();
}
else{
	$return = $_POST;
	$fp = fopen('results-'.$_POST['serviceType'].'-'.$UserID.'.json', 'w');
	fwrite($fp, json_encode($return));
	fclose($fp);
}
?>