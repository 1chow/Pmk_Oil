    <button data-toggle="modal" data-target="#EditInvoice" id="OpenEditInvoice" style="visibility:hidden;"></button>
    <div class="modal fade" id="EditInvoice" tabindex="-1" role="dialog" aria-labelledby="EditInvoiceLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form action="invoice.php" method="post" class="form-horizontal" autocomplete="off">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="EditInvoiceLabel">แก้ไขใบกำกับภาษี</h4>
                </div>
                <div class="modal-body">
                    <p><b>เลขที่ใบกำกับภาษีที่ต้องการแก้ไข:</b> <input type="text" class="form-control invoice_form" name="editInvoiceCode" id="editInvoiceCode" value="" style="width:200px;"></p>
                    <p><input type="radio" name="AddPrintNum" id="NextPrint" value="1" checked> <label for="NextPrint" style="font-weight:400; cursor:pointer;">ใช้พิมพ์ครั้งถัดไป</label></p>
                    <p><input type="radio" name="AddPrintNum" id="CurrentPrint" value="0"> <label for="CurrentPrint" style="font-weight:400; cursor:pointer;">ใช้พิมพ์ครั้งปัจจุบัน</label></p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="AddNew" value="1">
                    <input type="hidden" name="back" value="<?php if(isset($_REQUEST['back'])){print($_REQUEST['back']);} ?>">
                    <button type="submit" class="btn btn-success">ตกลง</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
                </div>
            </form>
        </div>
      </div>
    </div>

    <button data-toggle="modal" data-target="#UpdateCarWash" id="OpenUpdateCarWash" style="visibility: hidden;"></button>
    <div class="modal fade" id="UpdateCarWash" tabindex="-1" role="dialog" aria-labelledby="UpdateCarWashLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form action="carwash.php" method="post" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="UpdateCarWashLabel">แก้ไขใบรับบริการ</h4>
                </div>
                <div class="modal-body">
                    <b>เลขที่ใบรับบริการที่ต้องการแก้ไข:</b> <input type="text" class="form-control invoice_form" name="editCode" value="" style="width:200px;">
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="serviceType" id="editServiceType" value="1">
                    <button type="submit" class="btn btn-success">ตกลง</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
                </div>
            </form>
        </div>
      </div>
    </div>

    <button data-toggle="modal" data-target="#updatePriceForm" id="OpenUpdatePriceForm" style="visibility: hidden;"></button>
    <div class="modal fade" id="updatePriceForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div id="UpdatePriceFormDetail"></div>
                <form id="FormFirstStep" name="UpdatePriceForm" action="saveTemporary.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                    <input type="hidden" name="ConfirmNow" id="ConfirmNow" value="0">
                    <input type="hidden" name="oilPage" id="oilPage" value="<?php print($oilPage); ?>">
                    <table class="table table-condensed table-striped table-default">
                    <tr>
                        <th>&nbsp;</th>
                        <th>ชนิดน้ำมัน</th>
                        <th>ราคาน้ำมัน</th>
                        <th>ราคาใหม่</th>
                    </tr>
                    <?php
                        $sqlOil="select oil.OilID, oil.Name from ".$db_name.".oil where Deleted=0 order by Name ASC;";
                        $rsOil=mysql_query($sqlOil);
                        while($Oil=mysql_fetch_row($rsOil)){
                            $sqlOil2="select oil_price.RecordDate, oil_price.Prices, RecordTime from ".$db_name.".oil_price where OilID=".$Oil[0]." order by RecordDate DESC, RecordTime DESC;";
                            $rsOil2=mysql_query($sqlOil2);
                            $Oil2=mysql_fetch_row($rsOil2);
                            $TimeArr=explode("-", $Oil2[0]);
                            $SetDateFormat="";
                            if($Oil2[0]){
                                $SetDateFormat=$TimeArr[2]."/".$TimeArr[1]."/".$TimeArr[0];
                            }
                            $GetTimeArr=explode(":", $Oil2[2]);
                            $ThisTime=$GetTimeArr[0].":".$GetTimeArr[1];
                            OilPrice($Oil[0], $Oil[1], $Oil2[1], $SetDateFormat, 1, $ThisTime);
                        }
                    ?>
                    </table>
                    <br>
                    <div>
                        <button type="button" class="btn btn-success" onclick="javascript:confirmPage(1);">อัพเดทราคา</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="javascript:setPriceWarning();">ปิดหน้าต่าง</button>
                    </div>
                </form>
            </div>
        </div>
      </div>
    </div>
<!--
    <footer class="footer">
        My Checkbook System.
    </footer>
-->
    <!--
    ===========================================================
    Placed at the end of the document so the pages load faster
    ===========================================================
    -->
    <!-- MAIN JAVASRCIPT (REQUIRED ALL PAGE)-->
    <script type="text/javascript" src="libs/plugins/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="libs/js/jquery-datepicker.js"></script>
    <script type="text/javascript" src="libs/js/serialize.js"></script>

    <script type="text/javascript" src="libs/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="libs/plugins/jquery.cookie.js"></script>
    <script type="text/javascript" src="libs/plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <script type="text/javascript" src="libs/plugins/jquery.nicescroll/jquery.nicescroll.min.js"></script>
    <script type="text/javascript" src="libs/plugins/accordion-menu/jquery.hoverIntent.minified.js"></script>
    <script type="text/javascript" src="libs/plugins/accordion-menu/jquery.dcjqaccordion.2.9.js"></script>
    <!-- THEME APP -->
    <script type="text/javascript" src="libs/plugins/app.theme.js"></script>

    <script src="libs/plugins/icheck/icheck.js"></script>
    <script src="libs/plugins/masked-input/jquery.maskedinput.js"></script>
    <script type="text/javascript" src="libs/js/config-validation.js?ver=7.555"></script>
    <script type="text/javascript" src="libs/plugins/bootstrapvalidator/js/bootstrapValidator.js"></script>

    <?php
    if(isset($CustomerList) && trim($CustomerList)){
        $Customer4Invoice=$CustomerList;
        $IDName='AddCustomer';
    }
    else if(isset($Customer4Invoice)){
        $IDName='CustomerInv';
    }
    $javascriptText="";
    $javascriptText2="";
    if(isset($_REQUEST['SF']) && intval($_REQUEST['SF'])){
        $javascriptText.="$.cookie('dcjq-accordion', 0, { path: '/' });\r\n";
    }
    if(isset($PrintInvNow) && $PrintInvNow){
        $javascriptText.="printInvoice();\r\n";
    }
    if(isset($Customer4Invoice) && trim($Customer4Invoice)){
        $javascriptText2.='var str_custname = "'.$Customer4Invoice.'";
            var arr_custname = str_custname.split("*");
            $( "#FindByName" ).autocomplete({
              source: arr_custname
            });
            $( "#'.$IDName.'" ).autocomplete({
              source: arr_custname
            });';

        if(isset($OilType)){
            asort($OilType);
            $javascriptText.="\r\n\r\n\tvar OilPrice = new Array();";
            foreach ($OilType as $key => $value) {
                $javascriptText.="\r\n\tOilPrice[".$key."]='".$OilPrice[$key]."';";
            }
        }
        if(isset($ProductsPrice)){
            asort($ProductsPrice);
            $javascriptText.="\r\n\r\n\tvar ProductPrice = new Array();";
            foreach ($ProductsPrice as $key => $value) {
                $javascriptText.="\r\n\tProductPrice[".$key."]='".$value."';";
            }
        }
        $javascriptText.="\r\n";
    }
    if(isset($Tel4Invoice) && trim($Tel4Invoice)){
        $javascriptText2.='var str_custtel = "'.$Tel4Invoice.'";
            var arr_custtel = str_custtel.split("*");
            $( "#CustTel" ).autocomplete({
              source: arr_custtel
            });';
        $javascriptText.="\r\n";
    }
    if(isset($AllProduct)){
        $javascriptText.="\r\n\r\n\tvar ProductName = new Array();\r\n";
        foreach ($AllProduct as $key => $value) {
            $javascriptText.="\r\n\t ProductName[".$key."]='".$AllProduct[$key]."';";
        }
        $javascriptText.="\r\n\t";
        $javascriptText2.='var str_custcar = "'.$CustomerCar.'";
            var arr_custcar = str_custcar.split("*");
            $( "#CarCode" ).autocomplete({
              source: arr_custcar
            });';
        $javascriptText.="\r\n\t";
        if(isset($loadJson) && intval($loadJson)){
            $javascriptText.='loadJson();';
        }
        $javascriptText.="\r\n";
    }
    print("<SCRIPT>\r\n\t".$javascriptText."\r\n");
    print('$(function() {'.$javascriptText2.' });');
    print("\r\n</SCRIPT>");
    ?>

</body>
</html>
