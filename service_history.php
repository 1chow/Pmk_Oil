<?php
include("dbvars.inc.php");
if(!preg_match('/-9-/', $EmpAccess) && $UserID!=1){
    include("header.php");
    print('<section class="pageContent">
            <div class="content-center">
            <br><br><br><br><p class="noaccess">คุณไม่สามารถเข้าใช้งานส่วนนี้ได้</p>
            </div>
        </section>');
    include("footer.php");
    exit();
}

include("header.php");

$sqlCust="SELECT CustName from ".$db_name.".customer where Deleted=0 and CustID=".intval($_REQUEST['CustID']).";";
$rsCust=mysql_query($sqlCust);
$CustName=mysql_fetch_row($rsCust);
$ItemPerPage=150;
if(!isset($_REQUEST['page']) || !intval($_REQUEST['page'])){
    $_REQUEST['page']=1;
}
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>ประวัติการใช้บริการ</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div id="pageContent" class="panel-body">
                    <table width="100%" border="1" class="coupon_history">
                        <tr><th colspan="7"><p style="margin:10px;">รายงานประวัติการใช้บริการ</p></th></tr>
                        <tr>
                            <th width="9%">วันที่</th>
                            <th width="12%">ประเภท</th>
                            <th width="10%">เลขที่บริการ</th>
                            <th width="10%">ทะเบียนรถ</th>
                            <th width="9%">จำนวนเงิน</th>
                            <th width="9%">ส่วนลด</th>
                            <th width="10%">ยอดสุทธิ</th>
                        </tr>
                    <?php
                    $allSummary=0;
                    $allDiscount=0;
                    $ServiceTotal=0;
                    $sqlHistory="select ServiceCode, ServiceDate, SaveBy, CarCode, DiscountVal, ServiceType, car_service.ID, car_service.Deleted from (".$db_name.".car_service inner join ".$db_name.".customer_car on car_service.CustCar=customer_car.CarID) where car_service.CustID=".intval($_REQUEST['CustID']);
                    $rsHistory=mysql_query($sqlHistory.";");
                    $HistoryNum=mysql_num_rows($rsHistory);
                    $AllPage=ceil($HistoryNum/$ItemPerPage);

                    $sqlHistory.=" order by ServiceType ASC, ServiceCode ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                    $rsHistory=mysql_query($sqlHistory);
                    if(mysql_num_rows($rsHistory)){
                        while($History=mysql_fetch_row($rsHistory)){
                            $sqlWashService="SELECT sum(QTY*UnitPrice) from ".$db_name.".car_service_detail where ServiceID=".intval($History[6]).";";
                            $rsWashService=mysql_query($sqlWashService);
                            $WashService=mysql_fetch_row($rsWashService);
                            if($History[5]==1){
                                $Type='ล้างรถ';
                            }else{
                                $Type='เปลี่ยนน้ำมันเครื่อง';
                            }
                            $CancelTxt="";
                            if($History[7]){
                                $CancelTxt="&nbsp;&nbsp;<span style=\"color:red;\">(ยกเลิก)</span>";
                            }
                            $Summary=($WashService[0]-$History[4]);
                            print('<tr>
                                <td>'.date('j/m/Y', $History[1]).'</td>
                                <td>'.$Type.'</td>
                                <td>'.$History[0].$CancelTxt.'</td>
                                <td>'.$History[3].'</td>
                                <td style="text-align:right;">'.number_format($WashService[0], 2).'&nbsp;&nbsp;</td>
                                <td style="text-align:right;">'.number_format($History[4], 2).'&nbsp;&nbsp;</td>
                                <td style="text-align:right;">'.number_format($Summary, 2).'&nbsp;&nbsp;</td>
                                </tr>');
                            $allSummary+=round($WashService[0], 2);
                            $allDiscount+=round($History[4], 2);
                            $ServiceTotal+=round($Summary, 2);
                        }
                        if($_REQUEST['page']==$AllPage){
                            print('<tr style="height:40px;">
                                <td colspan="4" style="text-align:right; background-color:#E3E3E3;"><strong>รวมทั้งสิ้น </strong>&nbsp;&nbsp;</td>
                                <td style="text-align:right; background-color:#E3E3E3;"><strong>'.number_format($allSummary, 2).'</strong>&nbsp;&nbsp;</td>
                                <td style="text-align:right; background-color:#E3E3E3;"><strong>'.number_format($allDiscount, 2).'</strong>&nbsp;&nbsp;</td>
                                <td style="text-align:right; background-color:#E3E3E3;"><strong>'.number_format($ServiceTotal, 2).'</strong>&nbsp;&nbsp;</td>
                            </tr></table>');
                        }
                        else{
                            print('</table><br>');
                        }
                    }
                    else{
                        print('<tr><td colspan="8" style="padding:15px;"><span style="color:red;">ไม่มีประวัติการเข้ารับบริการ</span></td></tr></table>');
                    }
                    if($HistoryNum > $ItemPerPage){
                        // prev page
                        $moreLink='';
                        if(isset($_REQUEST['mainNo'])){
                            $moreLink='&mainNo='.$_REQUEST['mainNo'];
                        }
                        print("<br>");
                        if($_REQUEST['page']!=1){
                            print('<a href="service_history.php?CustID='.$_REQUEST['CustID'].'&backPageNo='.$_REQUEST['backPageNo'].'&page='.($_REQUEST['page']-1).$moreLink.'" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                        }
                        print("<select onchange=\"javascript:location.href='service_history.php?CustID=".$_REQUEST['CustID']."&backPageNo=".$_REQUEST['backPageNo']."&page='+this.value+'".$moreLink."';\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
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
                            print('<a href="service_history.php?CustID='.$_REQUEST['CustID'].'&backPageNo='.$_REQUEST['backPageNo'].'&page='.($_REQUEST['page']+1).$moreLink.'" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                        }
                    }
                    ?>
                    <br>
                    <div id="actionBar" class="actionBar right">
                        <input type="hidden" id="backPage" name="backPage" value="<?php
                        print('service-customer.php');
                        if(isset($_REQUEST["backPageNo"])){
                            print('?page='.$_REQUEST["backPageNo"]);
                        }
                        if(isset($_REQUEST['mainNo'])){
                            if(isset($_REQUEST['page'])){
                                print("&");
                            }
                            else{
                                print("?");
                            }
                            print('mainNo='.$_REQUEST['mainNo']);
                        }
                        ?>">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                        &nbsp;&nbsp;&nbsp;
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
include("footer.php");
?>