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
?>
        <section class="pageContent">
        <div class="title-body">
            <h2>รายงานสรุป</h2>
        </div>
        <br>
        <div class="content-center" style="padding: 0px 5px;">
            <div class="col-xs-6 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media">
                            <div class="media-body">
                                <h4 class="media-heading nameList">บัญชี</h4>
                                <p><a href="accounting_daily.php?back=reports">สรุปรายการทางบัญชีประจำวัน</a></p>
                                <p><a href="accounting.php?report=1&back=reports">รายงานรายรับ-รายจ่ายประจำวัน</a></p>
                                <p><a href="invoice-report.php?back=reports">สรุปรายการใบกำกับภาษีการขายน้ำมัน</a></p>
                                <p><a href="oil_record.php?back=reports">รายงานการขายน้ำมันประจำวัน</a></p>
                                <p><a href="sell-report.php?back=reports">รายงานการขายสินค้า</a></p>
                                <p><a href="service_report.php?back=reports">รายงานรายได้ - ถ่ายน้ำมันเครื่อง</a></p>
                                <p><a href="car_service.php?report=1&back=reports">รายงานสรุปบริการ ล้างรถ/เปลี่ยนถ่ายน้ำมันเครื่อง</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-6 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media">
                            <div class="media-body">
                                <h4 class="media-heading nameList" style="padding-top:6px;">สต็อก</h4>
                                <p><a href="stock-report.php?reportType=3&back=reports">รายงานสินค้าคงคลัง</a></p>
                                <p><a href="stock-report.php?back=reports">รายงานการเคลื่อนไหว</a></p>
                                <p><a href="stock-report.php?reportType=2&back=reports">รายงานรายการซื้อสินค้า</a></p>
                                <p><a href="stock-report.php?reportType=4&back=reports">รายงานการใช้สินค้า</a></p>
                                <p><a href="sell-report.php?back=reports">รายงานการขายสินค้า</a></p>
                                <p><a href="saletax-report.php?back=reports">รายงานภาษีซื้อ</a></p>
                                <p><a href="product_invoice.php?back=reports">รายงานภาษีขาย</a></p>
                                <p><a href="stock-income.php?back=reports">รายงานกำไร/ขาดทุนประจำวัน</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br style="clear:both;">

            <div class="col-xs-6 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media">
                            <div class="media-body">
                                <h4 class="media-heading nameList" style="overflow:visible;">คูปอง</h4>
                                <p><a href="coupon-report.php?viewAction=2">รายงานการซื้อ</a></p>
                                <p><a href="coupon-report.php?viewAction=3">รายงานการใช้</a></p>
                                <p><a href="coupon-report.php">รายงานคูปองที่ยังไม่ได้อนุมัติ</a></p>
                                <p><a href="lock-history.php">รายงานคูปองที่ถูกล็อค</a></p>
                                <p><a href="coupon-report.php?viewAction=1">รายงานการเคลื่อนไหวของคูปอง</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-6 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media">
                            <div class="media-body">
                                <h4 class="media-heading nameList" style="overflow:visible;">ลูกค้า</h4>
                                <p><a href="manage-billing.php?report=1&from=reports">รายงานประวัติการชำระเงิน</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-6 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media">
                            <div class="media-body">
                                <h4 class="media-heading nameList">พนักงาน</h4>
                                <p><a href="employees_payment.php?TimeSheet=<?php print(date("Y-n", time())); ?>&action=view">รายงานสรุปเวลาทำงาน/ค่าแรงประจำเดือน</a></p>
                                <p><a href="employees_payment.php?action=Advance&TimeSheet=<?php print(date("Y-n", time())); ?>">รายงานสรุปการเบิกเงินล่วงหน้า</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>


<?php
include("footer.php");
?>