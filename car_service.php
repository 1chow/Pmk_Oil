<?php
include("dbvars.inc.php");
include("header.php");
if(isset($_REQUEST['report']) && intval($_REQUEST['report'])){
    if(!isset($_REQUEST['serviceDate'])){
        $_REQUEST['serviceDate']=date("d/m/Y", time());
        $_REQUEST['serviceDateTo']=date("d/m/Y", time());
    }
    if(!isset($_REQUEST['serviceType'])){
        $_REQUEST['serviceType']=0;
    }
    if(!isset($_REQUEST['back'])){
        $_REQUEST['back']='';
    }
    $SetDate=explode("/", $_REQUEST['serviceDate']);
    $startDate=mktime(0, 0, 0, $SetDate[1], intval($SetDate[0]), $SetDate[2]);
    $endDate=mktime(23, 59, 59, $SetDate[1], $SetDate[0], $SetDate[2]);
    if($_REQUEST['serviceDateTo']!=$_REQUEST['serviceDate']){
        $SetDateTo=explode("/", $_REQUEST['serviceDateTo']);
        $endDate=mktime(23, 59, 59, $SetDateTo[1], $SetDateTo[0], $SetDateTo[2]);
    }
    $ItemPerPage=150;
    if(!isset($_REQUEST['page']) || !$_REQUEST['page']){
        $_REQUEST['page']=1;
    }
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>รายงานสรุปบริการ ล้างรถ/เปลี่ยนถ่ายน้ำมันเครื่อง</h2>
        </div>
       <br>
        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <form action="car_service.php" method="post" class="form-horizontal" role="form" name="car_service">
                        <input type="hidden" name="page" id="setPage" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="report" value="1">
                        <h3 class="panel-title" style="margin: 10px 0;">
                        <?php
                            if(isset($_REQUEST["mainsection"])){
                                print('<input type="hidden" name="mainsection" value="'.$_REQUEST["mainsection"].'">');
                            }
                            print("รายงานการบริการประจำวันที่:");
                            print("&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="serviceDate" value="'.$_REQUEST['serviceDate'].'" style="display:inline; width:100px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print("&nbsp;ถึง&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="serviceDateTo" value="'.$_REQUEST['serviceDateTo'].'" style="display:inline; width:100px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print("&nbsp;&nbsp;&nbsp;");
                            print('ประเภทการบริการ <select name="serviceType" class="form-control" style="display:inline; width:150px;" onchange="javascript:document.getElementById(\'setPage\').value=1;"><option value="">ทุกประเภท</option>');
                            print('<option value="1"');
                            if(intval($_REQUEST['serviceType'])==1){
                                print(' selected');
                            }
                            print('>ล้างรถ</option>');
                            print('<option value="2"');
                            if(intval($_REQUEST['serviceType'])==2){
                                print(' selected');
                            }
                            print('>เปลี่ยนน้ำมันเครื่อง</option>');
                            print('</select>');
                            print("&nbsp;&nbsp;&nbsp;");
                            print('<button type="submit" class="btn btn-xs btn-primary btn-rounder">GO</button>');
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">

                    <table width="100%" border="1" class="coupon_history">
                        <tr><th colspan="8"><p style="margin:10px;">
                            <?php
                            print('รายงานการบริการประจำวันที่: '.$_REQUEST['serviceDate']);
                            if($_REQUEST['serviceDate']!=$_REQUEST['serviceDateTo']){
                                print(' - '.$_REQUEST['serviceDateTo']);
                            }
                            ?>
                        </p></th></tr>
                        <tr>
                            <th width="9%">วันที่</th>
                            <th width="12%">ประเภท</th>
                            <th width="13%">เลขที่บริการ</th>
                            <th>ชื่อลูกค้า</th>
                            <th width="10%">ทะเบียนรถ</th>
                            <th width="9%">จำนวนเงิน</th>
                            <th width="9%">ส่วนลด</th>
                            <th width="10%">ยอดสุทธิ</th>
                        </tr>
                        <?php
                        $sqlHistory="select ServiceCode, ServiceDate, CustName, CarCode, DiscountVal, ServiceType, car_service.ID, SaveBy, car_service.Deleted from ((".$db_name.".car_service left join ".$db_name.".customer on car_service.CustID=customer.CustID) left join ".$db_name.".customer_car on car_service.CustCar=customer_car.CarID) where ServiceDate>=".$startDate." and ServiceDate<=".$endDate;
                        if(intval($_REQUEST['serviceType'])){
                            $sqlHistory.=" and ServiceType=".intval($_REQUEST['serviceType']);
                        }
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
                                if($History[8]){
                                    $CancelTxt="&nbsp;&nbsp;<span style=\"color:red;\">(ยกเลิก)</span>";
                                }
                                $Summary=($WashService[0]-$History[4]);
                                print('<tr>
                                    <td>'.date('j/m/Y', $History[1]).'</td>
                                    <td class="text-left">&nbsp;'.$Type.'</td>
                                    <td class="text-left">&nbsp;&nbsp;'.$History[0].$CancelTxt.'</td>
                                    <td class="text-left">&nbsp;'.$History[2].'</td>
                                    <td class="text-left">&nbsp;&nbsp;'.$History[3].'</td>
                                    <td style="text-align:right;">'.number_format($WashService[0], 2).'&nbsp;&nbsp;</td>
                                    <td style="text-align:right;">'.number_format($History[4], 2).'&nbsp;&nbsp;</td>
                                    <td style="text-align:right;">'.number_format($Summary, 2).'&nbsp;&nbsp;</td>
                                    </tr>');
                            }
                            if($_REQUEST['page']==$AllPage){
                                $sqlSumHistory="select sum(round((QTY*UnitPrice)*100)/100), sum(round((DiscountVal)*100)/100) from (".$db_name.".car_service inner join ".$db_name.".car_service_detail on car_service.ID=car_service_detail.ServiceID) where ServiceDate>=".$startDate." and ServiceDate<=".$endDate;
                                if(intval($_REQUEST['serviceType'])){
                                    $sqlSumHistory.=" and ServiceType=".intval($_REQUEST['serviceType']);
                                }
                                $rsSumHistory=mysql_query($sqlSumHistory.";");
                                $SumHistory=mysql_fetch_row($rsSumHistory);
                                $allSummary=$SumHistory[0];
                                $allDiscount=$SumHistory[1];
                                $ServiceTotal=round($SumHistory[0]-$SumHistory[1], 2);
                                print('<tr style="height:40px;">
                                    <td colspan="5" style="text-align:right; background-color:#E3E3E3;"><strong>รวมทั้งสิ้น </strong>&nbsp;&nbsp;</td>
                                    <td style="text-align:right; background-color:#E3E3E3;"><strong>'.number_format($allSummary, 2).'</strong>&nbsp;&nbsp;</td>
                                    <td style="text-align:right; background-color:#E3E3E3;"><strong>'.number_format($allDiscount, 2).'</strong>&nbsp;&nbsp;</td>
                                    <td style="text-align:right; background-color:#E3E3E3;"><strong>'.number_format($ServiceTotal, 2).'</strong>&nbsp;&nbsp;</td>
                                </tr></table><br>');
                            }
                            else{
                                print('</table><br>');
                            }
                        }
                        else{
                            print('<tr><td colspan="8" style="padding:15px;"><span style="color:red;">ไม่มีรถเข้ารับบริการในวันที่กำหนด</span></td></tr></table>');
                        }
                        if($HistoryNum > $ItemPerPage){
                            if($_REQUEST['page']!=1){
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']-1).'; document.forms[\'car_service\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                            }
                            print("<select onchange=\"javascript:document.getElementById('setPage').value=this.value; document.forms['car_service'].submit();\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
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
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']+1).'; document.forms[\'car_service\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                            }
                        }
                        ?>

                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <input type="hidden" name="page" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        <?php
                        if(trim($_REQUEST['back'])){
                        ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
                            &nbsp;&nbsp;&nbsp;
                        <?php
                        }
                        ?>
                    </div>

                </div>
            </div>
        </div>
    </section>
<?php
}
else{
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
    print('
    <section class="pageContent">
        <div class="title-body">
            <h2>ล้างรถ/เปลี่ยนถ่ายน้ำมันเครื่อง</h2>
        </div>
       <br><br>
       <div class="row" style="padding: 0px 20px;">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-car"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="carwash.php?back=car_service">ล้างรถ</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-truck"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="carwash.php?serviceType=2&back=car_service">เปลี่ยนน้ำมันเครื่อง</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa fa-file-text-o"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="car_service.php?report=1">รายงานสรุป</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-edit"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="service-customer.php">ลูกค้า</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media stats-box">
                            <a href="#" class="pull-left">
                                <i class="fa fa-circle-o-notch"></i>
                            </a>
                            <div class="media-body">
                                <span class="stats-number"><a href="stock.php">ทะเบียนสินค้าและบริการ</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <section>');
}
include("footer.php");
?>