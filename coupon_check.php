<?php
include("dbvars.inc.php");
if(!preg_match('/-4-/', $EmpAccess) && $UserID!=1){
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
include("header.php");

$DeleteSuccess=0;
if(isset($_POST["removeCouponNow"]) && (intval($_POST["removeCouponNow"]) || intval($_POST["removeCustCoupon"]))){
    $IncludeOldSearch=1;
    if(intval($_POST["removeCouponNow"])){
        foreach ($_POST["DeleteCoupon"] as $key => $value) {
            $sqlDelete="DELETE FROM ".$db_name.".coupon WHERE coupon.ID=".intval($value).";";
            if($rsDelete=mysql_query($sqlDelete)){
                $DeleteSuccess++;
            }
        }
    }
    else if(intval($_POST["removeCustCoupon"])){
        foreach ($_POST["DeleteCoupon"] as $key => $value) {
            $sqlDelete="UPDATE ".$db_name.".coupon SET CustomerID=0, RealUse=0, Status=3, PaidHistoryID=0, UseHistoryID=0 WHERE coupon.ID=".intval($value).";";
            if($rsDelete=mysql_query($sqlDelete)){
                $DeleteSuccess++;
            }
        }
    }
}
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>เช็คคูปอง</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <form action="coupon_check.php" name="CouponCheck" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <input type="hidden" name="checkNow" value="1">
                        <div class="form-group">
                            <div class="col-sm-2 control-label">&nbsp;</div>
                            <div class="col-sm-10">
                                <span class="inline_input"> &nbsp; 100 บาท &nbsp;</span>
                                <span class="inline_input"> &nbsp; เลขที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="CouponCode[100]" id="CouponCode100" value="" style="width:380px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2 control-label">&nbsp;</div>
                            <div class="col-sm-10">
                                <span class="inline_input"> &nbsp; 300 บาท &nbsp;</span>
                                <span class="inline_input"> &nbsp; เลขที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="CouponCode[300]" id="CouponCode300" value="" style="width:380px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2 control-label">&nbsp;</div>
                            <div class="col-sm-10">
                                <span class="inline_input"> &nbsp; 500 บาท &nbsp;</span>
                                <span class="inline_input"> &nbsp; เลขที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="CouponCode[500]" id="CouponCode500" value="" style="width:380px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2 control-label">&nbsp;</div>
                            <div class="col-sm-10">
                                <span class="inline_input"> &nbsp; 1000 บาท</span>
                                <span class="inline_input"> &nbsp; เลขที่ &nbsp; </span>
                                <input type="text" class="form-control inline_input" name="CouponCode[1000]" id="CouponCode1000" value="" style="width:380px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3 control-label">&nbsp;</div>
                            <div class="col-sm-9">
                                <p>** ใช้คูปองต่อเนื่องด้วยเครื่องหมาย -</p>
                                <p>** แบ่งเลขที่คูปองด้วยเครื่องหมาย ,</p>
                            </div>
                        </div>
                        <?php
                        $condition="";
                        if(isset($_POST["checkNow"]) && intval($_POST["checkNow"])){
                            foreach ($_POST["CouponCode"] as $key => $value) {
                                if(trim($value)){
                                    $AllList[$key]=0;
                                    $couponListArr=explode(',', $value);
                                    foreach ($couponListArr as $key1 => $value1) {
                                        if(preg_match('#-#', $value1)){
                                            $LongList=explode('-', $value1);
                                            for($i=intval($LongList[0]); $i<=intval($LongList[1]); $i++){
                                                $AllList[$key].=",'".$i."'";
                                            }
                                        }
                                        else{
                                            $AllList[$key].=",'".trim($value1)."'";
                                        }
                                    }
                                    if($condition){
                                        $condition.=" or ";
                                    }
                                    $condition.="(Price='".intval($key)."' and CouponCode in (".$AllList[$key]."))";
                                }
                            }
                            if(isset($_POST["oldCouponList"]) && trim($_POST["oldCouponList"]) && isset($IncludeOldSearch)){
                                if($condition){
                                    $condition="(".$condition.") or ";
                                }
                                $condition.="(".trim($_POST["oldCouponList"]).")";
                            }

                            $StatusArr = array('0'=>'ไม่ทราบสถานะ', '1'=>'สามารถใช้งานได้', '2'=>'คูปองถูกใช้ไปแล้ว', '3'=>'คูปองยังไม่ถูกขาย', '4'=>'คูปองถูกล็อค', '5'=>'คูปองยังไม่ได้อนุมัติ');
                            $StatusColorArr = array('0'=>'grey', '1'=>'green', '2'=>'red', '3'=>'blue', '4'=>'red', '5'=>'red');
                            $sqlCoupon="SELECT customer.CustName, Status, Price, CouponCode, concat(BookNo,'',BookCodeNo), coupon.ID from (".$db_name.".coupon left join ".$db_name.".customer on coupon.CustomerID=customer.CustID) where ".$condition." order by BookNo ASC, CouponCode ASC;";
                            $rsCoupon=mysql_query($sqlCoupon);
                            if($DeleteSuccess){
                                print('<div class="alert alert-danger" style="width:97%; margin:0 auto;"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>ลบคูปอง '.$DeleteSuccess.' รายการ');
                                if(intval($_POST["removeCustCoupon"])){
                                    print('ออกจากลูกค้า');
                                }
                                else{
                                    print('ออกจากระบบ');
                                }
                                print('เรียบร้อยแล้ว.</div>');
                            }
                            if(mysql_num_rows($rsCoupon)){
                                print('<table style="width:97%; margin:35px auto;" class="td_center table table-condensed table-striped table-default table_border"><thead><tr>
                                        <th width="20%">
                                            <b>เล่มที่/เลขที่</b>
                                        </th>
                                        <th width="20%">
                                            <b>มูลค่าคูปอง</b>
                                        </th>
                                        <th width="20%">
                                            <b>สถานะ</b>
                                        </th>
                                        <th width="33%">
                                            <b>บริษัท / ชื่อผู้ซื้อ</b>
                                        </th>
                                        <th width="7%">
                                            <b>ลบ</b> <a href="javascript:toggleCheckbox();">(T)</a>
                                        </th>
                                    </tr></thead>
                                    <tbody>');
                                $DeleteBoxNum=0;
                                while($Coupon=mysql_fetch_row($rsCoupon)){
                                    $DeleteBox="&nbsp;";
                                    if($Coupon[1]!=2){
                                        $DeleteBox="<input type=\"checkbox\" id=\"DeleteCoupon-".$DeleteBoxNum."\" name=\"DeleteCoupon[]\" value=\"".$Coupon[5]."\">";
                                        $DeleteBoxNum++;
                                    }
                                    print('
                                    <tr>
                                        <td>
                                            <b>'.trim($Coupon[4]).' / '.$Coupon[3].'</b>
                                        </td>
                                        <td>
                                            '.number_format($Coupon[2], 2).'
                                        </td>
                                        <td class="'.$StatusColorArr[$Coupon[1]].'">
                                            '.$StatusArr[$Coupon[1]].'
                                        </td>
                                        <td>
                                            '.$Coupon[0].'
                                        </td>
                                        <td>
                                            '.$DeleteBox.'
                                        </td>
                                    </tr>');
                                }
                                print('</tbody></table>');
                            }
                            else{
                                print('
                                    <table style="width:97%; margin:35px auto;" class="td_center table table-condensed table-striped table-default table_border">
                                        <tbody><tr><td>
                                            <p style="margin-top:10px;" class="passcode_send-error">
                                                ไม่พบคูปองที่เช็คในระบบ
                                            </p>
                                        </td></tr></tbody>
                                    </table>');
                            }
                        }
                        if(isset($_REQUEST['backPage']) && ($_REQUEST['backPage']=='coupons')){
                            $_REQUEST['backPage']='coupons.php';
                        }
                        ?>
                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="backPage" name="backPage" value="<?php if(isset($_REQUEST['backPage'])){ print($_REQUEST['backPage']); }else{ print('index.php'); } if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                            <button type="submit" class="btn btn-success btn-rounder">เช็คคูปอง</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <?php
                            if($DeleteBoxNum){
                                print('<input type="hidden" id="removeCustCoupon" name="removeCustCoupon" value="0"><button type="button" id="remove4Cust" class="btn btn-warning btn-rounder">ลบคูปองของลูกค้า</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                                print('<input type="hidden" id="removeCouponNow" name="removeCouponNow" value="0"><input type="hidden" id="AllDeleteBox" value="'.$DeleteBoxNum.'"><input type="hidden" name="oldCouponList" value="'.$condition.'"><button type="button" id="removeCoupon" class="btn btn-danger btn-rounder">ลบคูปองออกจากระบบ</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                            }
                            ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php
include("footer.php");
?>