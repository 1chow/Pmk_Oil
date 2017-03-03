<?php
include("dbvars.inc.php");
if(!preg_match('/-11-/', $EmpAccess) && $UserID!=1){
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

if(isset($_REQUEST['selectedDate'])){
    $UseThisDate=explode(",", $_REQUEST['selectedDate']);
    $_REQUEST['serviceDate']=date("d/m/Y", $UseThisDate[0]);
    $_REQUEST['serviceDateTo']=date("d/m/Y", $UseThisDate[1]);
}
if(!isset($_REQUEST['serviceDate'])){
    $_REQUEST['serviceDate']=date("d/m/Y", time());
    $_REQUEST['serviceDateTo']=date("d/m/Y", time());
    $_REQUEST['onlyCustID']=0;
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
$DateBetween=$startDate.",".$endDate;
$ItemPerPage=150;
if(!isset($_REQUEST['page']) || !$_REQUEST['page']){
    $_REQUEST['page']=1;
}
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>สรุปรายการใบกำกับภาษีการขายน้ำมัน</h2>
        </div>
       <br>
        <div class="content-center">
            <div id="pageContent" class="panel panel-default">
                <div id="PageHeader" class="panel-heading">
                    <form action="invoice-report.php" method="post" class="form-horizontal" role="form" name="invoice_report">
                        <input type="hidden" name="page" id="setPage" value="<?php print($_REQUEST['page']); ?>">
                        <input type="hidden" name="back" value="<?php print($_REQUEST['back']); ?>">
                        <input type="hidden" name="onlyCustID" id="onlyCustID" value="<?php print($_REQUEST['onlyCustID']); ?>">
                        <input type="hidden" name="report" value="1">
                        <h3 class="panel-title" style="margin: 10px 0;">
                        <?php
                            print("สรุปรายการใบกำกับภาษีประจำวันที่:");
                            print("&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="serviceDate" value="'.$_REQUEST['serviceDate'].'" style="display:inline; width:100px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print("&nbsp;ถึง&nbsp;");
                            print('<input type="text" class="form-control Calendar" name="serviceDateTo" value="'.$_REQUEST['serviceDateTo'].'" style="display:inline; width:100px;" onchange="javascript:document.getElementById(\'setPage\').value=1;">');
                            print("&nbsp;&nbsp;&nbsp;");
                            print('<button type="submit" class="btn btn-xs btn-primary btn-rounder" onclick="javascript:document.getElementById(\'onlyCustID\').value=\'0\';">GO</button>');
                        ?></h3>
                    </form>
                </div>
                <div class="panel-body">
                    <table width="100%" border="1" class="coupon_history">
                        <tr><th colspan="8"><p style="margin:10px;">
                            <?php
                            print('สรุปรายการใบกำกับภาษีประจำวันที่: '.$_REQUEST['serviceDate']);
                            if($_REQUEST['serviceDate']!=$_REQUEST['serviceDateTo']){
                                print(' - '.$_REQUEST['serviceDateTo']);
                            }
                            ?>
                        </p></th></tr>
                        <tr>
                            <th width="12%">วันที่</th>
                            <th width="12%">เลขที่</th>
                            <th>นามผู้ซื้อ</th>
                            <th width="15%">เลขประจำตัวผู้เสียภาษี</th>
                            <th width="10%">สาขา</th>
                            <th width="11%">มูลค่าสินค้า</th>
                            <th width="11%">ภาษีมูลค่าเพิ่ม (7%)</th>
                            <th width="11%">รวม</th>
                        </tr>
                        <?php
                        $Total1=0;
                        $Total2=0;
                        $Total3=0;
                        $sqlHistory="select InvDate, InvCode, CustName, CustTaxCode, CustBranchCode, InvID, CustID from ".$db_name.".invoices where invoices.InvDate>=".$startDate." and invoices.InvDate<=".$endDate;
                        if(isset($_POST['onlyCustID']) && trim($_POST['onlyCustID'])){
                            $sqlHistory.=" and invoices.CustID=".intval($_POST['onlyCustID']);
                        }
                        $rsHistory=mysql_query($sqlHistory.";");
                        $HistoryNum=mysql_num_rows($rsHistory);
                        $AllPage=ceil($HistoryNum/$ItemPerPage);

                        $sqlHistory.=" order by InvDate ASC, InvCode ASC Limit ".(($_REQUEST['page']-1)*$ItemPerPage).", ".$ItemPerPage.";";
                        //echo $sqlHistory;
                        $rsHistory=mysql_query($sqlHistory);
                        if(mysql_num_rows($rsHistory)){
                            while($History=mysql_fetch_row($rsHistory)){
                                $sqlDetail="select sum(Total) from ".$db_name.".invoice_detail where InvID=".intval($History[5]).";";
                                $rsDetail=mysql_query($sqlDetail);
                                $Detail=mysql_fetch_row($rsDetail);
                                $TotalInv=$Detail[0];
                                $vat=round(($TotalInv*7)/100, 2);
                                $subTotalInv=($TotalInv-$vat);
                                $Total1+=round($subTotalInv, 2);
                                $Total2+=round($vat, 2);
                                $Total3+=round($TotalInv, 2);
                                print('<tr>
                                    <td>'.date('d/m/Y', $History[0]).'</td>
                                    <td><a href="javascript:gotopage(\'invoice.php?InvoiceCode='.$History[1].'&page='.$_REQUEST["page"].'&DateBetween='.$DateBetween.'\');">'.$History[1].'</a></td>
                                    <td class="text-left">&nbsp;<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'onlyCustID\').value=\''.$History[6].'\'; document.forms[\'invoice_report\'].submit();">'.$History[2].'</a></td>
                                    <td>'.$History[3].'</td>
                                    <td>'.$History[4].'</td>
                                    <td style="text-align:right;">'.number_format($subTotalInv, 2).'&nbsp;&nbsp;</td>
                                    <td style="text-align:right;">'.number_format($vat, 2).'&nbsp;&nbsp;</td>
                                    <td style="text-align:right;">'.number_format($TotalInv, 2).'&nbsp;&nbsp;</td>
                                    </tr>');
                            }
                            print('<tr><th colspan="5" style="text-align:right;">รวม: </th><th style="text-align:right;">'.number_format($Total1, 2).'&nbsp;&nbsp;</th><th style="text-align:right;">'.number_format($Total2, 2).'&nbsp;&nbsp;</th><th style="text-align:right;">'.number_format($Total3, 2).'&nbsp;&nbsp;</th></tr>');
                            print('</table><br>');
                            $sqlTotal="select sum(Total) from (".$db_name.".invoices inner join ".$db_name.".invoice_detail on invoices.InvID=invoice_detail.InvID) where invoices.InvDate>=".$startDate." and invoices.InvDate<=".$endDate.";";
                            $rsTotal=mysql_query($sqlTotal);
                            $Total=mysql_fetch_row($rsTotal);
                            print("<div class=\"clearboth\">รวมทั้งสิ้น: ".number_format($Total[0], 2)." บาท</div>");
                        }
                        else{
                            print('<tr><td colspan="8" style="padding:15px;"><span style="color:red;">ไม่มีการเคลื่อนไหวในวันที่กำหนด</span></td></tr></table>');
                        }
                        if($HistoryNum > $ItemPerPage){
                            print("<br>");
                            if($_REQUEST['page']!=1){
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']-1).'; document.forms[\'invoice_report\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&laquo;&laquo;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;');
                            }
                            print("<select onchange=\"javascript:document.getElementById('setPage').value=this.value; document.forms['invoice_report'].submit();\" name=\"setcustompage\" class=\"form-control inline_input\" style=\"width:100px;\">");
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
                                print('<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'setPage\').value='.($_REQUEST['page']+1).'; document.forms[\'invoice_report\'].submit();" style="border:1px solid #CCCCCC;">&nbsp;&raquo;&raquo;&nbsp;</a>');
                            }
                        }
                        ?>

                    <div id="actionBar" style="clear:both; margin-top:6px;" class="actionBar right">
                        <input type="hidden" name="page" value="<?php print($_REQUEST['page']); ?>">
                        <button type="button" class="btn btn-success btn-rounder" id="PrintReport">พิมพ์รายงาน</button>
                        <?php
                        if(trim($_REQUEST['back'])){
                        ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="hidden" id="backPage" value="<?php print($_REQUEST['back'].'.php'); ?>">
                            <button id="back2Emp" type="button" class="btn btn-inverse btn-rounder">ย้อนกลับ</button>
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