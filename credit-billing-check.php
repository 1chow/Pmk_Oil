<?php
include("dbvars.inc.php");
if(!preg_match('/-10-/', $EmpAccess) && $UserID!=1){
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
if(isset($_POST["removeBillingNow"]) && intval($_POST["removeBillingNow"])){
    $IncludeOldSearch=1;
    foreach($_POST["CreditBillDel"] as $key => $value) {
        $sqlDelete="DELETE FROM ".$db_name.".credit_billing WHERE credit_billing.CreditBilling=".intval($value)." and RealUsed=0;";
        if($rsDelete=mysql_query($sqlDelete)){
            $DeleteSuccess++;
        }
    }
}

if(!isset($_POST["CreditBillingCode"])){
    $_POST["CreditBillingCode"]="";
}
if(trim($_POST["CreditBillingCode"])){
    $AllList=0;
    $_POST["CreditBillingCode"]=preg_replace("/\s*\n\s*\,*/", ", ", $_POST["CreditBillingCode"]);
    $couponListArr=explode(',', $_POST["CreditBillingCode"]);
    foreach($couponListArr as $key1 => $value1) {
        if(preg_match('#-#', $value1)){
            $LongList=explode('-', $value1);
            for($i=intval($LongList[0]); $i<=intval($LongList[1]); $i++){
                $AllList.=",'".$i."'";
            }
        }
        else{
            $AllList.=",'".intval($value1)."'";
        }
    }
}
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>เช็คใบสั่งน้ำมัน</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <form action="credit-billing-check.php" name="CreditBillingCheck" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <input type="hidden" name="checkNow" value="1">
                        <div class="form-group">
                            <div class="col-sm-2 control-label">เลขที่ใบสั่งน้ำมัน</div>
                            <div class="col-sm-6">
                                <textarea name="CreditBillingCode" class="form-control" style="height:100px; width:95%;"><?php print($_POST["CreditBillingCode"]); ?></textarea>
                            </div>
                            <div class="col-sm-4"><br><br>
                                <button type="submit" class="btn btn-success btn-rounder">เช็คใบสั่งน้ำมัน</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2 control-label">&nbsp;</div>
                            <div class="col-sm-10">
                                <p>** ใช้เลขที่ใบสั่งน้ำมันต่อเนื่องด้วยเครื่องหมาย -</p>
                                <p>** แบ่งเลขที่ใบสั่งน้ำมันด้วยเครื่องหมาย , หรือขึ้นบรรทัดใหม่</p>
                            </div>
                        </div>
                        <?php
                        $sqlBilling="SELECT customer.CustName, RealUsed, BookNo, CreditBilling, CodeNo from (".$db_name.".credit_billing inner join ".$db_name.".customer on credit_billing.CustID=customer.CustID) where CodeNo in (".$AllList.") order by BookNo ASC, CreditBilling ASC;";
                        $rsBilling=mysql_query($sqlBilling);
                        if($DeleteSuccess){
                            print('<div class="alert alert-danger" style="width:97%; margin:0 auto;"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>ลบใบสั่งน้ำมัน '.$DeleteSuccess.' รายการ ออกจากระบบเรียบร้อยแล้ว.</div>');
                        }
                        if(mysql_num_rows($rsBilling)){
                            print('<table style="width:97%; margin:35px auto;" class="td_center table table-condensed table-striped table-default table_border"><thead><tr>
                                    <th width="20%">
                                        <b>เล่มที่/เลขที่</b>
                                    </th>
                                    <th width="33%">
                                        <b>ชื่อลูกค้า</b>
                                    </th>
                                    <th width="20%">
                                        <b>สถานะ</b>
                                    </th>
                                    <th width="7%">
                                        <b>ลบ</b> <a href="javascript:toggleCreditBilling();">(T)</a>
                                    </th>
                                </tr></thead>
                                <tbody>');
                            $DeleteBoxNum=0;
                            while($CreditBilling=mysql_fetch_row($rsBilling)){
                                $DeleteBox="&nbsp;";
                                $billingStatus="<span style=\"color:red;\">ใบสั่งน้ำมันถูกใช้ไปแล้ว</span>";
                                if($CreditBilling[1]==0){
                                    $DeleteBox="<input type=\"checkbox\" id=\"CreditBillDel-".$DeleteBoxNum."\" name=\"CreditBillDel[]\" value=\"".$CreditBilling[3]."\">";
                                    $billingStatus="<span style=\"color:darkgreen;\">สามารถใช้งานได้</span>";
                                    $DeleteBoxNum++;
                                }
                                print('
                                <tr>
                                    <td>
                                        '.trim($CreditBilling[2]).' / '.$CreditBilling[4].'
                                    </td>
                                    <td>
                                        '.$CreditBilling[0].'
                                    </td>
                                    <td>
                                        '.$billingStatus.'
                                    </td>
                                    <td>
                                        '.$DeleteBox.'
                                    </td>
                                </tr>');
                            }
                            print('</tbody></table>');
                        }
                        else if(trim($AllList)){
                            print('
                                <table style="width:97%; margin:35px auto;" class="td_center table table-condensed table-striped table-default table_border">
                                    <tbody><tr><td>
                                        <p style="margin-top:10px;" class="passcode_send-error">
                                            ไม่พบใบสั่งน้ำมันที่เช็ค
                                        </p>
                                    </td></tr></tbody>
                                </table>');
                        }
                        ?>
                        <div class="actionBar right">
                            <?php
                            if($DeleteBoxNum){
                                print('<input type="hidden" id="removeBillingNow" name="removeBillingNow" value="0"><input type="hidden" id="AllDeleteBox" value="'.$DeleteBoxNum.'"><button type="button" id="removeCreditBilling" class="btn btn-danger btn-rounder">ลบใบสั่งน้ำมันออกจากระบบ</button>&nbsp;&nbsp;&nbsp;&nbsp;');
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