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
include("header.php");
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
            <h2>รายงานรายได้ - ถ่ายน้ำมันเครื่อง</h2>
        </div>
       <br>
        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <form action="service_report.php" method="post" class="form-horizontal" role="form" name="service_report">
                        <input type="hidden" name="page" id="setPage" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="report" value="1">
                        <h3 class="panel-title" style="margin: 10px 0;">
                        <?php
                            if(isset($_REQUEST["mainsection"])){
                                print('<input type="hidden" name="mainsection" value="'.$_REQUEST["mainsection"].'">');
                            }
                            print("รายงานรายได้ - ถ่ายน้ำมันเครื่องประจำวันที่:");
                            print("&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="serviceDate" value="'.$_REQUEST['serviceDate'].'" style="display:inline; width:100px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print("&nbsp;ถึง&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="serviceDateTo" value="'.$_REQUEST['serviceDateTo'].'" style="display:inline; width:100px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print("&nbsp;&nbsp;&nbsp;");
                            print('<button type="submit" class="btn btn-xs btn-primary btn-rounder">GO</button>');
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">
                    <h5 class="text-center">บริษัท พี.เอ็ม.เค. ออยส์ จำกัด</h5>
                    <p class="text-center">รายงานรายได้ - ถ่ายน้ำมันเครื่อง</p>
                    <p class="text-center">ตั้งแต่วันที่ <?php print($_REQUEST['serviceDate']); ?> ถึงวันที่ <?php print($_REQUEST['serviceDateTo']); ?></p>
                    <table width="90%" align="center" class="coupon_history">
                        <?php
                        $ServiceQTY=0;
                        $ServiceTotal=0;
                        $sqlHistory="select products.Name, sum(QTY), sum(QTY*UnitPrice) from (".$db_name.".car_service inner join ".$db_name.".car_service_detail on car_service.ID=car_service_detail.ServiceID) inner join ".$db_name.".products on products.ProductID=car_service_detail.ProductID  where ServiceDate>=".$startDate." and ServiceDate<=".$endDate." and ServiceType=2 and car_service.Deleted=0 group by products.ProductID";
                        $rsHistory=mysql_query($sqlHistory.";");
                        $HistoryNum=mysql_num_rows($rsHistory);
                        $AllPage=ceil($HistoryNum/$ItemPerPage);

                        $sqlHistory.=" order by ServiceType ASC, ServiceCode ASC, products.Name ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            print('<tr>
                            <td class="service_report_header">รายการรายได้</td>
                            <td class="service_report_header" width="120px" style="text-align:right;">ปริมาณ</td>
                            <td class="service_report_header" width="150px" style="text-align:right;">จำนวนเงิน</td>
                            </tr>');
                            while($History=mysql_fetch_row($rsHistory)){
                                $ServiceTotal+=round($History[2], 2);
                                $ServiceQTY+=round($History[1], 2);
                                print('<tr>
                                    <td style="text-align:left;">'.$History[0].'</td>
                                    <td style="text-align:right;">'.number_format($History[1], 2).'&nbsp;&nbsp;</td>
                                    <td style="text-align:right;">'.number_format($History[2], 2).'&nbsp;&nbsp;</td>
                                    </tr>');
                            }
                            print('<tr>
                            <td class="service_report_header">ยอดรวมทั้งสิ้น</td>
                            <td class="service_report_header" style="text-align:right;">'.number_format($ServiceQTY, 2).'&nbsp;&nbsp;</td>
                            <td class="service_report_header" style="text-align:right;">'.number_format($ServiceTotal, 2).'&nbsp;&nbsp;</td>
                            </tr>');
                            print('</table><br>');
                        }
                        else{
                            print('<tr><td style="padding:15px;"><span style="color:red;">ไม่มีรถเข้ารับบริการในวันที่กำหนด</span></td></tr></table>');
                            $CannotPrint=1;
                        }
                        if($HistoryNum > $ItemPerPage){
                            print("<br>");
                            if($_REQUEST['page']!=1){
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']-1).'; document.forms[\'service_report\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                            }
                            print("<select onchange=\"javascript:document.getElementById('setPage').value=this.value; document.forms['service_report'].submit();\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
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
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']+1).'; document.forms[\'service_report\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                            }
                        }
                        ?>

                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <input type="hidden" name="page" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                        <?php
                        if(!isset($CannotPrint)){
                            print('<button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>');
                        }
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
include("footer.php");
?>