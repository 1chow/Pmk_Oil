<?php
include("dbvars.inc.php");
include("header.php");
$sqlCust="SELECT CustName from ".$db_name.".customer where customer.CustID=".intval($_REQUEST["CustID"]).";";
$rsCust=mysql_query($sqlCust);
$CustInfo=mysql_fetch_row($rsCust);

$sqlTotal="SELECT SUM(RealUsed) from ".$db_name.".credit_billing where Status=0 and CustID=".intval($_REQUEST["CustID"])." and Confirmed=1;";
$rsTotal=mysql_query($sqlTotal);
$Total=mysql_fetch_row($rsTotal);
?>
    <section id="pageContent" class="pageContent">
        <div id="PageHeader" class="title-body">
            <h2>ประวัติการใช้</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="margin: 10px 0;"><?php print($CustInfo[0]); ?></h3>
                    <p><i>รวม: <?php print(number_format($Total[0], 2)); ?></i></p>
                </div>

                <div class="panel-body">
                    <table class="table table-condensed table-striped table-default table_border td_center">
                    <tr>
                        <th>ลำดับที่</th>
                        <th>เล่มที่</th>
                        <th>เลขที่</th>
                        <th>วันที่</th>
                        <th>จำนวนเงิน</th>
                    </tr>
                    <?php
                        $count=1;
                        $sqlOil="SELECT BookNo, CodeNo, RealUsed, credit_billing.Date from ".$db_name.".credit_billing where Status=0 and CustID=".intval($_REQUEST["CustID"])." and Confirmed=1 order by credit_billing.Date ASC, BookNo ASC, CodeNo ASC;";
                        $rsOil=mysql_query($sqlOil);
                        while($Oil=mysql_fetch_row($rsOil)){
                            print('
                    <tr>
                        <td>'.$count.'</td>
                        <td>'.$Oil[0].'</td>
                        <td>'.$Oil[1].'</td>
                        <td>'.date('d/m/Y', $Oil[3]).'</td>
                        <td>'.number_format($Oil[2], 2).'</td>
                    </tr>');
                            $count++;
                        }
                    ?>
                    </table>
                    <br>
                    <div id="actionBar" class="actionBar right">
                        <input type="hidden" id="submitTo" value="oil.php">
                        <input type="hidden" id="backPage" value="<?php
                        if(isset($_REQUEST['back']) && trim($_REQUEST['back'])){
                            print($_REQUEST['back'].".php");
                            if(isset($_REQUEST['from'])){
                                print("?from=".$_REQUEST['from']);
                            }
                        }
                        else{
                            if(!isset($_REQUEST['page'])){
                                $_REQUEST['page']=1;
                            }
                            print('customer.php?page='.$_REQUEST['page']);
                        } ?>">
                        <input type="hidden" id="MakeBillingCustID" value="<?php print($_REQUEST["CustID"]); ?>">
                        <button type="button" class="btn btn-info btn-rounder" id="MakeBilling">วางบิล</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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