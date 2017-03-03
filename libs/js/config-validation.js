$(function() {
    $.mask.definitions['f'] = "[A-Fa-f0-9]";
    $(".telephone").mask('999-999-9999', {placeholder:'X'});
    $(".zipcode").mask('99999', {placeholder:'X'});
    $(".dates").mask('9999', {placeholder:'X'});
    $(".time").mask('99:99', {placeholder:'X'});
    $( ".Calendar" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: 'dd/mm/yy',yearRange: '-1:+1', dayNamesMin: ['Su','Mo','Tu','We','Th','Fr','Sa'], monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"], monthNamesShort: ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"]});
});

function setCouponCode(){
    var SellPrice = $('#SellPrice').val()
    var SellBookNo = $('#SellBookNo').val();
    var BookStart = $('#BookCodeNo1').val();
    var BookEnd = $('#BookCodeNo2').val();
    if(parseInt(BookEnd) > 0){
        var AllBook = (BookEnd-BookStart)+1;
    }
    else{
        var AllBook = 1;
    }
    var CouponPerBook = $('#CouponPerBook').val();
    var CheckWithBook = (AllBook * CouponPerBook);
    if($('#CouponCode1').val()){
        var CouponStart = $('#CouponCode1').val();
        var CouponEnd = parseInt(CouponStart)+parseInt(CheckWithBook)-1;
        if(CouponStart && CouponEnd){
            $('#CouponCode2').val(CouponEnd);
        }
    }
    else if($('#CouponCode2').val()){
        var CouponEnd = $('#CouponCode2').val();
        var CouponStart = parseInt(CouponEnd)-parseInt(CheckWithBook)+1;
        if(CouponStart && CouponEnd){
            $('#CouponCode1').val(CouponStart);
        }
    }

    var SetBookNoStart = SellBookNo+BookStart;
    var SetBookNoEnd = SellBookNo+BookEnd;
    var InputComplete = 'alert-success';
    var IconComplete = 'fa-check';
    if(!CouponEnd){ CouponEnd='.......'; InputComplete='alert-danger'; IconComplete = 'fa-warning'; }
    if(!BookEnd){ SetBookNoEnd='.......'; InputComplete='alert-danger'; IconComplete = 'fa-warning'; }
    if(!CouponStart){ CouponStart='.......'; InputComplete='alert-danger'; IconComplete = 'fa-warning'; }
    if(!BookStart){ SetBookNoStart='.......'; InputComplete='alert-danger'; IconComplete = 'fa-warning'; }
    if(!BookStart || !BookEnd){ AllBook='.......'; InputComplete='alert-danger'; IconComplete = 'fa-warning'; }
    $('#AddCouponAlert').html('<br><div class="alert '+InputComplete+'">ทำการเพิ่มคูปองมูลค่า '+SellPrice+' บาท จำนวน '+AllBook+' เล่ม (เล่มที่ '+SetBookNoStart+' - '+SetBookNoEnd+') เล่มละ '+CouponPerBook+' ใบ, ตั้งแต่เลขที่ '+CouponStart+' ถึง '+CouponEnd+'<span style="float:right;" class="fa '+IconComplete+'"></span>&nbsp;</div>');
    return false;
}


$(document).ready(function() {
    if($('#WarningNow').val()==1){
        $("#OpenWarningOil").trigger("click");
    }
    $('#accountType1').on('ifChecked', function (event) {
        $('#ForTitle').html('ผู้รับเงิน:');
        $('#WantTax').hide();
    });

    $('#accountType2').on('ifChecked', function (event) {
        $('#ForTitle').html('ผู้จ่ายเงิน:');
        $('#WantTax').show();
    });

    $('#accountType3').on('ifChecked', function (event) {
        $('#ForTitle').html('ผู้เบิกเงิน:');
        $('#WantTax').hide();
    });

    $('#accountType4').on('ifChecked', function (event) {
        $('#ForTitle').html('ผู้คืนเงิน:');
        $('#WantTax').hide();
    });

    $('#OnTaxReport1').on('ifChecked', function (event) {
        $('#BookCodeNo').show();
    });

    $('#OnTaxReport2').on('ifChecked', function (event) {
        $('#BookCodeNo').hide();
    });

    $('.noEnterSubmit').keypress(function(e){
        if ( e.which == 13 ) return false;
        //or...
        if ( e.which == 13 ) e.preventDefault();
    })

    $("#CouponPaid-100").trigger("change");
    $("#CouponPaid-300").trigger("change");
    $("#CouponPaid-500").trigger("change");
    $("#CouponPaid-1000").trigger("change");

    $('#addCreditPaid').on('click', function(){
        var AddRow = $('#creditTable tr').length;
        var AddFiveRow = parseInt(AddRow+5);
        var OilOptionList = $('#OilOptionList').html();
        for(var k=AddRow; k<AddFiveRow; k++){
            $('#creditTable tr:last').after('<tr id="credit-'+k+'"><td>'+k+'</td><td><input type="text" name="CreditBookNo['+k+']" id="PaidBookNo-'+k+'" class="form-control" value=""></td></td><td><input type="text" name="CreditCodeNo['+k+']" class="form-control" value="" onchange="javascript:getBookNo(this.value, '+k+');"></td><td style="text-align:left;"><span id="CompanyName-'+k+'"></span></td><td><select name="CreditOilType['+k+']" class="form-control inline_input input-sm">'+OilOptionList+'</select></td><td><input type="text" name="Amount['+k+']" class="form-control price" value=""></td><td><input type="text" name="CreditCar['+k+']" id="CreditCar-'+k+'" class="form-control" value=""></td></tr>');
        }
    });
    $('#SellPrice').on('change', setCouponCode);
    $('#SellBookNo').on('change', setCouponCode);
    $('#BookCodeNo1').on('change', setCouponCode);
    $('#BookCodeNo2').on('change', setCouponCode);
    $('#CouponCode1').on('change', setCouponCode);
    $('#CouponCode2').on('change', setCouponCode);
    $('#CouponPerBook').on('change', setCouponCode);

    $('.customer4coupon').on('change', function(){
        var ID4Update = $(this).attr('id');
        ID4Update = ID4Update.split('-');
        $.ajax({ // call AJAX to write new value
            url: 'record_info.php',
            type: 'post',
            data: { 'customer4coupon': $(this).val(), 'IDUpdate':ID4Update[1] },
            async: false
        });
    });

    $('.realuse').on('change', function(){
        var ID4Update = $(this).attr('id');
        var RealVal = IntFix($(this).val());
        var CouponVal = IntFix($('#Val-'+ID4Update).val());
        var newResult = number_format(CouponVal-RealVal, 2);
        $('#show-'+ID4Update).html(newResult);
        var RecordType = $('#RecordType').val();

        $.ajax({ // call AJAX to write new value
            url: 'saveTemporary.php',
            type: 'post',
            data: { 'UpdateRealUse': RealVal, 'IDUpdate':ID4Update, 'RecordType':RecordType },
            async: false
        });
        var MinusCoupon=0;
        var PlusCoupon=0;
        var TotalCoupons = IntFix($('#TotalCoupons').html());
        var TotalPerVar = IntFix($('#TotalPerVar-'+CouponVal).html());
        $("span[class="+CouponVal+"]").each(function(index, element) {
            var currentVal=$(this).html();
            if(currentVal > 0){
                MinusCoupon = MinusCoupon+parseInt(currentVal);
            }
            else{
                PlusCoupon = PlusCoupon+parseInt(Math.abs(currentVal));
            }
        });

        var TotalUsed = parseInt(TotalPerVar-MinusCoupon)+parseInt(PlusCoupon);
        $('#Total-'+CouponVal).html(number_format(TotalUsed, 2));
        $('#compare-'+CouponVal).html(TotalPerVar-TotalUsed);
        if($('#RealVal-'+CouponVal)){
            $('#RealVal-'+CouponVal).val(number_format(TotalUsed, 2));
        }

        var TotalSumary=0;
        $("td[class=total_real]").each(function(index, element) {
            TotalSumary+=parseInt(IntFix($(this).html()));
        });
        $('#TotalSumary').html(number_format(TotalSumary, 2));
        $('#TotalCompare').html(TotalCoupons-TotalSumary);
        if($('#CreditRealVal')){
            $('#CreditRealVal').val(number_format(TotalSumary, 2));
        }
    });

    $('.product_page input[name="Type"]').on('ifClicked', function(event){
        var ProductType = $(this).val();
        if(ProductType=='สินค้า'){
            $('.forProductType').show();
        }
        else{
            $('.forProductType').hide();
        }
    });

    $('.invoice input[type="radio"]').on('ifClicked', function(event){
        if($(this).val()==0){
            $('#BranchType4No').val(1);
        }
        else{
            $('#BranchType4No').val(0);
        }
    });

    $('#BranchCodeNo').on('click', function (event) {
        $('#BranchType2').iCheck('check');
    });

    $('.car_wash input[type="radio"]').on('ifClicked', function (event) {
        var ID4Update = $(this).closest('td').attr('id');
        var ProductType = $(this).val();
        switchType(ProductType, ID4Update);
        saveTemporary();
    });

    $('.employee_work input[type="checkbox"]').on('ifClicked', function(event){
        var TimeDate = $(this).closest('td').attr('id');
        var EmpID = $(this).val();
        if(!$(this).is(':checked')){
            addTakeoff(TimeDate, EmpID, 1);
        }
        else{
            addTakeoff(TimeDate, EmpID, 0);
        }
    });

    $('.payment_form').on('change', function(){
        var ID4Update = $(this).closest('tr').attr('id');
        var rateTotal =IntFix($('#rateTotal-'+ID4Update).html());
        var SS = IntFix($('#SS-'+ID4Update).val());
        var tax = IntFix($('#tax-'+ID4Update).val());
        var lost = IntFix($('#lost-'+ID4Update).val());
        var late = IntFix($('#late-'+ID4Update).val());
        var paid = IntFix($('#paid-'+ID4Update).val());
        var LoanRate = IntFix($('#LoanRate-'+ID4Update).val());
        var ot = IntFix($('#ot-'+ID4Update).val());
        var OTRate = IntFix($('#OTRate-'+ID4Update).val());
        if(ot > 0){
            var otTotal = (ot*OTRate);
        }
        else{
            var otTotal = 0;
        }
        var bonus = IntFix($('#bonus-'+ID4Update).val());
        var incentive = IntFix($('#incentive-'+ID4Update).val());
        var other = IntFix($('#other-'+ID4Update).val());

        var Minus = parseInt(toFixed(SS, 2)) + parseInt(toFixed(tax, 2)) + parseInt(toFixed(lost, 2)) + parseInt(toFixed(late, 2)) + parseInt(toFixed(paid, 2)) + parseInt(toFixed(LoanRate,2));
        var Plus = parseInt(toFixed(otTotal, 2)) + parseInt(toFixed(bonus, 2)) + parseInt(toFixed(incentive, 2)) + parseInt(toFixed(other, 2));
        var showTotal = parseInt(rateTotal) - parseInt(Minus) + parseInt(Plus);

        $('#otTotal-'+ID4Update).val(otTotal);
        $('#otTotal2-'+ID4Update).html(number_format(otTotal, 2));
        //alert(showTotal);
        $('#total-'+ID4Update).val(showTotal);
        if(showTotal > 0){
            $('#showTotal-'+ID4Update).html(number_format(showTotal, 2));
        }
        else{
            $('#showTotal-'+ID4Update).html('-'+number_format(showTotal, 2));
        }
    });

    $('#PrintReport').on('click', function(){
        $('.hideValue').show();
        $('.showValue').hide();
        $('.printhidden').hide();
        $('.alert').hide();
        var printReadyEle = document.getElementById("pageContent");
        var shtml='';
        shtml = '<HTML>\n<HEAD>\n';
        if (document.getElementsByTagName != null){
          var sheadTags = document.getElementsByTagName("head");
          if (sheadTags.length > 0)
             shtml += sheadTags[0].innerHTML;
        }
        shtml += '<link href="libs/css/myprintstyle.css" rel="stylesheet" type="text/css"></HEAD>\n<BODY>\n';
        if (printReadyEle != null)
        {
           shtml += '<form name = frmform1>';
           shtml += printReadyEle.innerHTML;
        }
        shtml += '\n</form><SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">if(document.getElementById("PageHeader")){ document.getElementById("PageHeader").style.display=\'none\'; } if(document.getElementById("PageNav")){ document.getElementById("PageNav").style.display=\'none\'; } window.print(); window.close();</SCRIPT>\n</BODY>\n</HTML>';
        var printWin1 = window.open('', 'PrintWindow', 'width=750,height=650,top=50,left=50,toolbars=no,scrollbars=yes,status=no,resizable=yes');
        printWin1.document.write(shtml);
        $('.hideValue').hide();
        $('.showValue').show();
        $('.printhidden').show();
        // if(printWin1.document.getElementById("PageHeader")){
        //     printWin1.document.getElementById("PageHeader").style.display='none';
        // }
        // if(printWin1.document.getElementById("PageNav")){
        //     printWin1.document.getElementById("PageNav").style.display='none';
        // }
        // printWin1.print();
        // printWin1.close();
    });

    $('#PrintPayment').on('click', function(){
        $('.alert').hide();
        var printReadyEle = document.getElementById("EmployeeContent");
        var shtml='';
        shtml = '<HTML>\n<HEAD>\n';
        if (document.getElementsByTagName != null){
          var sheadTags = document.getElementsByTagName("head");
          if (sheadTags.length > 0)
             shtml += sheadTags[0].innerHTML;
        }
        // shtml += '<style type="text/css" media="print">@page { size: landscape; }</style>';
        shtml += '<link href="libs/css/myprintstyle.css" rel="stylesheet" type="text/css"></HEAD>\n<BODY>\n';
        if(printReadyEle != null)
        {
           shtml += '<form name = frmform1>';
           shtml += printReadyEle.innerHTML;
        }
        shtml += '\n</form><SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">if(document.getElementById("paymentOption")){ document.getElementById("paymentOption").style.display=\'none\'; } window.print(); window.close();</SCRIPT>\n</BODY>\n</HTML>';
        var printWin1 = window.open('', 'PrintWindow', 'width=750,height=650,top=50,left=50,toolbars=no,scrollbars=yes,status=no,resizable=yes');
        printWin1.document.write(shtml);
        // printWin1.document.getElementById("paymentOption").style.display='none';
        // printWin1.print();
        // printWin1.close();
    });


    $('.clearPasscode').on('click', function(){
        $('#SendPasscodeTxt').html('');
        $('#UnlockAcess').val('');
    });

    $('#CheckPassCode').on('click', function(){
        checkPassCode();
    });

    $('#MakeBilling').on('click', function(){
        if(confirm('ยืนยันการวางบิล?')){
            $.ajax({ // call AJAX to write new main layout section
                url: 'saveTemporary.php',
                type: 'post',
                data: { 'MakeBillingCustID': $('#MakeBillingCustID').val() },
                async: false,
                success: function(newdata) {
                    alert('บันทึกการวางบิลเรียบร้อยแล้ว');
                    $('#back2Emp').trigger("click");
                }
            });
        }
    });

    $("#SendPasscode").on('click', function(){
        var CustomerID = $('#CustomerID').val();
        var ApproveNow = $('#ApproveNow').val();
        $.ajax({ // call AJAX to write new main layout section
            url: 'lock-history.php',
            type: 'post',
            data: { 'SendPasscode': CustomerID, 'ApproveNow': ApproveNow },
            async: false,
            success: function(newdata) {
                $('#SendPasscodeTxt').html(newdata);
            }
        });
    });

    $('#ConfirmPayment').on('click', function(){
        if(confirm('ยืนยันข้อมูล?')){
            $('#DoAction').val(1);
            document.forms['PaymentAction'].submit();
        }
    });

    $('#EditPayment').on('click', function(){
        $('#DoAction').val(2);
        document.forms['PaymentAction'].submit();
    });

    $('#SavePayment').on('click', function(){
        document.forms['EditPaymentForm'].submit();
    });

    $('.CustomerCar').on('click', function(){
        var ID4Update = $(this).closest('div').attr('id');
        var fromPage = $('#submitTo').val();
        fromPage = fromPage.replace(".php", "");
        var PageNo = '';
        if($('#PageNo').val()>0){
            PageNo = '&page='+$('#PageNo').val();
        }
        if($('#mainNo') && ($('#mainNo').val()>0)){
            PageNo='&mainNo=1';
        }
        $(location).attr('href', 'customer_car.php?CustomerID='+ID4Update+'&from='+fromPage+PageNo);

    });

    $('.viewHistory').on('click', function(){
        var ID4Update = $(this).closest('div').attr('id');
        var PageNo = '';
        if($('#PageNo').val()>0){
            PageNo = '&page='+$('#PageNo').val();
        }
        $(location).attr('href', 'lock-history.php?CustHistory='+ID4Update+'&view=1'+PageNo);

    });

    $('#PrintPaymentSlip').on('click', function(){
        var moreInfo='&DDay='+$('#DDay').val();
        if($('#comeFromReport').length){
            moreInfo+='&comeFromReport=1';
        }
        $(location).attr('href', 'employees_payment_slip.php?TimeSheet='+$('#payment4Year').val()+'-'+$('#payment4Month').val()+moreInfo);
    });

    $('#unlockAllCoupon').on('click', function(){
        if($('#ApproveNow').val()==1){
            var confirmText='คุณต้องการอนุมัติคูปองทั้งหมดใช่หรือไม่?';
        }else{
            var confirmText='คุณต้องการปลดล็อคคูปองทั้งหมดใช่หรือไม่?';
        }
        if(confirm(confirmText)){
            $('#unlockAll').val(1);
            if($('#Permission').val()==3){ // admin then can submit
                $('#submitForm').submit();
            }
            else{ // supervisor then ask for passcode
                $('#ask4Unlock').trigger("click");
                return false;
            }
        }
    });

    $('#deleteBillingID').on('click', function(){
        if(confirm('คุณต้องการลบใบวางบิลนี้ใช่หรือไม่')){
            var BillingID = $('#BillingID').val();
            $('#delBillingID').val(BillingID);
            document.forms['payment_billing'].submit();
            return false;
        }
        else{
            return false;
        }
    });

    $('#removeCreditBilling').on('click', function(){
        if(confirm('คุณต้องการลบใบสั่งน้ำมันทั้งหมดที่เลือกออกจากระบบใช่หรือไม่')){
            $('#removeBillingNow').val(1);
            document.forms['CreditBillingCheck'].submit();
            return false;
        }
        else{
            return false;
        }
    });

    $('#removeCoupon').on('click', function(){
        if(confirm('คุณต้องการลบคูปองทั้งหมดที่เลือกออกจากระบบใช่หรือไม่')){
            $('#removeCouponNow').val(1);
            document.forms['CouponCheck'].submit();
            return false;
        }
        else{
            return false;
        }
    });

    $('#remove4Cust').on('click', function(){
        if(confirm('คุณต้องการลบคูปองทั้งหมดที่เลือกออกจากลูกค้าใช่หรือไม่')){
            $('#removeCustCoupon').val(1);
            document.forms['CouponCheck'].submit();
            return false;
        }
        else{
            return false;
        }
    });

    $('.lockCoupon').on('click', function(){
        var ID4Update = $(this).closest('div').attr('id');
        $.ajax({ // call AJAX to write new main layout section
            url: 'coupons.php',
            type: 'post',
            data: { 'lockCoupon': ID4Update },
            async: false,
            success: function(newdata) {
                $('#lockCouponForm').html(newdata);
            }
        });
    });

    $('.show_edit_coupons').on('click', function(){
        var ID4Update = $(this).closest('div').attr('id');
        $.ajax({ // call AJAX to write new main layout section
            url: 'coupons.php',
            type: 'post',
            data: { 'show_edit_coupons': ID4Update },
            async: false,
            success: function(newdata) {
                $('#editItemForm').html(newdata);
            }
        });
    });


    $('#CustomerName').on('blur', function(event){
        event.preventDefault();
        var CustName = $(this).val();
        $.ajax({ // call AJAX to write new main layout section
            url: 'record_info.php',
            type: 'post',
            data: { 'getCustInfo': CustName },
            async: false,
            success: function(newdata) {
                $('#CustID').val(newdata);
            }
        });
    });

    $('#CreditBill').on('change', function(event){
        event.preventDefault();
        var CustID = $(this).val();
        var CustType = $('#CustType').val();
        $.ajax({ // call AJAX to write new main layout section
            url: 'credit-billing.php',
            type: 'post',
            data: { 'getCustInfo': CustID, 'CustType': CustType },
            async: false,
            success: function(newdata) {
                if(CustType>0){
                    var text = "<p>ยอดเงินคงเหลือ: "+number_format(newdata, 2)+" บาท</p>";
                    $('#CustomerInfo').html(text);
                }
                else{
                    var data=newdata.split('**');
                    var text="<p>ยอดเงินที่วางบิลแล้ว (รอชำระเงิน): "+number_format(data[0], 2)+" บาท</p>";
                    text += "<p>ยอดเงินที่ใช้รอบปัจจุบัน: "+number_format(data[1], 2)+" บาท</p>";
                    $('#CustomerInfo').html(text);
                }
            }
        });
    });

    $('#CustomerInv').on('blur', function(event){
        event.preventDefault();
        var CustName = $(this).val();
        getInvoice(CustName, 0, '', 0);
    });

    $('#CustTel').on('blur', function(event){
        event.preventDefault();
        var PhoneInv = $(this).val();
        getInvoice(0, PhoneInv, '', 0);
    });

    $('#CarCodeInv').on('blur', function(event){
        event.preventDefault();
        var CarCodeInv = $(this).val();
        getInvoice('', 0, CarCodeInv, 0);
    });

    // $('#InvoiceNo').on('blur', function(){
    //     event.preventDefault();
    //     var InvoiceNo = $(this).val();
    //     getInvoice('', 0, 0, InvoiceNo);
    // });

    $('#AddCustomer').on('blur', function(event){
        event.preventDefault();
        var CustName = $(this).val();
        $.ajax({ // call AJAX to write new main layout section
            url: 'coupons.php',
            type: 'post',
            data: { 'getCustInfo': CustName, 'TotalSell':$('#SellPrice').val() },
            async: false,
            success: function(newdata) {
                $('#CustomerInfo').html(newdata);
            }
        });
    });

    $('#CheckAccessCode').on('click', function(event){
        CheckAccess($('#AccessCode').val());
    });
    $('#CheckAccessCode2').on('click', function(event){
        CheckAccess($('#AccessCode2').val());
    });

    $('.time').on('blur', function(event){
        event.preventDefault();
        if($(this).val().match('^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$')){
            return true;
        }
        else if($(this).val()){
            alert('รูปแบบข้อมูลไม่ถูกต้อง กรุณาตรวจสอบ');
            $(this).focus();
            return false;
        }
        else{
            return true;
        }
    });

    $('.editItem').on('click', function(event){
        event.preventDefault();
        var ID4Update = $(this).closest('div').attr('id');
        ID4Update = ID4Update.replace("item-", "");
        $('#UpdateItem').val(ID4Update);
        $('#submitForm').submit();
    });

    $('#ClearPaid').on('click', function(event){
        $("#OpenDialog").trigger("click");
    });

    $('.editCust').on('click', function(event){
        event.preventDefault();
        var ID4Update = $(this).closest('tr').attr('id');
        ID4Update = ID4Update.replace("item-", "");
        $('#UpdateItem').val(ID4Update);
        $('#submitForm').submit();
    });

    $('#HolidayMark').on('click', function(event){
        event.preventDefault();
        $('#AsAction').val('HolidayMark');
        $('#submitForm').submit();
    });

    $('.removeItem').on('click', function(){
        $('.alert').hide();
        var canSubmit = false;
        var DeleteItem = $(this).closest('div').attr('id');
        var ItemName = $(this).attr('id');
        if(confirm('คุณต้องการลบ '+ItemName+' ?')){
            var canSubmit = true;
        }
        if(canSubmit){
            $.ajax({ // call AJAX to write new main layout section
                url: $('#submitTo').val(),
                type: 'post',
                data: { 'DeleteItem': DeleteItem },
                async: false,
                success: function(newdata) {
                    $('#item-'+DeleteItem).fadeOut(300);
                }
            });
        }
        return false;
    });

    $('.removeBalance').on('click', function(){
        $('.alert').hide();
        var canSubmit = false;
        var DeleteItem = $(this).closest('div').attr('id');
        var DelBalance = $('#DelBalance-'+DeleteItem).val();
        var DelType = $('#DelType-'+DeleteItem).val();
        if(confirm('คุณต้องการลบ รายการเคลื่อนไหวนี้ ?')){
            canSubmit = true;
        }
        if(canSubmit){
            $.ajax({ // call AJAX to write new main layout section
                url: $('#submitTo2').val(),
                type: 'post',
                data: { 'DeleteItem': DeleteItem, 'DelBalance':DelBalance, 'DelType':DelType },
                async: false,
                success: function(newdata) {
                    //alert(newdata);
                    $('#item-'+DeleteItem).fadeOut(300);
                    $('#SystemBalance').html(newdata);
                }
            });
        }
        return false;
    });

    $('#Work4Month').on('change', NewTimeSheet);
    $('#Work4Year').on('change', NewTimeSheet);
    $('#payment4Month').on('change', NewPaymentSheet);
    $('#payment4Year').on('change', NewPaymentSheet);
    $('#DDay').on('change', NewPaymentSheet);
    $('#HistoryMonth').on('change', couponHistory);
    $('#HistoryYear').on('change', couponHistory);
    $('#importMonth').on('change', NewImport);
    $('#importYear').on('change', NewImport);
    $('#importVAT').on('change', NewImport);
    $('#importProduct').on('change', NewImport);

    $('.number').on('change', function(event) {
        var numCheck = $(this).val();
        var newVal = number_format(numCheck, 2);
        $(this).val(newVal);
    });

    $('.price').on('change', function(event) {
        var numCheck = $(this).val();
        if($(this).attr('id')!='OTRate'){
            var newVal = number_format(numCheck, 2);
        }else{
            var newVal = number_format(numCheck, 3);
        }
        $(this).val(newVal);
    });

    $('.qty').on('change', function(event) {
        var numCheck = $(this).val();
        newVal = number_format(numCheck);
        $(this).val(newVal);
    });

    $('.integer').on('change', function(event) {
        var numCheck = $(this).val();
        newVal = IntFix(number_format(numCheck));
        $(this).val(newVal);
    });

    $('#SystemBalance').on('change', function(event) {
        var SystemBalance=$(this).val();
        var DebitSym="";
        if(SystemBalance.match('-')){
            DebitSym="-";
        }
        var SystemBalanceCHK=Math.abs(IntFix($(this).val()));
        var OldSystemBalance=$('#OldSystemBalance').val();
        if(SystemBalanceCHK === parseInt(SystemBalanceCHK, 10)){
            $(this).val(DebitSym+number_format(SystemBalanceCHK, 2));
            return;
        }
        else{
            alert("รูปแบบข้อมูลไม่ถูกต้อง");
            $(this).val(OldSystemBalance);
        }
    });

    $('#addCoupon').on('click', function(event){
        event.preventDefault();
        var moreLink='';
        if($('#PageNo').val()>1){
            moreLink='&page='+$('#PageNo').val();
        }
        $(location).attr('href', 'coupons.php?AddCoupon=1'+moreLink);
    });

    $('.editEmp').on('click', function(event){
        event.preventDefault();
        var EmpID = $(this).closest('div').attr('id');
        $(location).attr('href', 'employees.php?UpdateEmp='+EmpID);
    });

    $('.empTime').on('click', function(event){
        event.preventDefault();
        var EmpID = $(this).closest('div').attr('id');
        $(location).attr('href', 'employees.php?manageTime=1&EmpID='+EmpID);
    });

    $('.editBalance').on('click', function(event){
        event.preventDefault();
        var BalanceID = $(this).closest('div').attr('id');
        $(location).attr('href', 'accounting_balance.php?UpdateBalance='+BalanceID);
    });

    $('#BalanceDateReport').on('change', function(event){
        event.preventDefault();
        $(location).attr('href', 'accounting_balance.php?BalanceReport='+this.value);
    });

    $('#back2Emp').on('click', function(){
        $(location).attr('href', $('#backPage').val());
    });

    $('#systemForm').bootstrapValidator({
        message: 'ข้อมูลไม่ถูกต้อง',
        // feedbackIcons: {
        //     valid: 'glyphicon glyphicon-ok',
        //     invalid: 'glyphicon glyphicon-remove',
        //     validating: 'glyphicon glyphicon-refresh'
        // },
        fields: {
            AdminEmail: {
                message: 'Admin\'s Email ไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่ Admin\'s Email'
                    },
                    regexp: {
                        regexp: /^\b[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}\b$/,
                        message: 'Admin\'s Email อยู่ในรูปแบบที่ไม่ถูกต้อง'
                    }
                }
            },
            BonusRate: {
                message: '่อัตราโบนัสไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่อัตราโบนัส'
                    },
                    regexp: {
                        regexp: /^[0-9,\.]*$/,
                        message: 'กรุณาใส่เป็นตัวเลขเท่านั้น'
                    }
                }
            },
            SSRate: {
                message: 'อัตราประกันสังคมไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่อัตราประกันสังคม'
                    },
                    regexp: {
                        regexp: /^[0-9]*$/,
                        message: 'กรุณาใส่เป็นตัวเลขเท่านั้น'
                    }
                }
            },
            ApproveLimit: {
                message: 'มูลค่าคูปองไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่มูลค่าคูปอง'
                    },
                    regexp: {
                        regexp: /^[0-9,\.]*$/,
                        message: 'กรุณาใส่เป็นตัวเลขเท่านั้น'
                    }
                }
            },
            MaxSSRate: {
                message: '่อัตราประกันสังคมไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่อัตราสูงสุดของประกันสังคม'
                    },
                    regexp: {
                        regexp: /^[0-9,\.]*$/,
                        message: 'กรุณาใส่เป็นตัวเลขเท่านั้น'
                    }
                }
            },
            MinSalarySS: {
                message: 'ยอดรายได้ขั้นต่ำในการคิดประกันสังคมไม่ถูกต้อง',
                validators: {
                    regexp: {
                        regexp: /^[0-9,\.]*$/,
                        message: 'กรุณาใส่เป็นตัวเลขเท่านั้น'
                    }
                }
            },
            NoDayOff: {
                message: 'ข้อมูลการลาก่อน/หลังวันหยุดพิเศษไม่ถูกต้อง',
                validators: {
                    regexp: {
                        regexp: /^[0-9]*$/,
                        message: 'กรุณาใส่เป็นตัวเลขเท่านั้น'
                    }
                }
            },
            InvoiceBegin: {
                message: '่เลขที่ใบกำกับภาษีเริ่มต้นไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่เลขที่ใบกำกับภาษีเริ่มต้น'
                    },
                    regexp: {
                        regexp: /^[0-9]*$/,
                        message: 'กรุณาใส่เป็นตัวเลขเท่านั้น'
                    }
                }
            },
            CompanyName: {
                message: 'ชื่อบริษัท/ชื่อลูกค้าไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่ชื่อบริษัท/ชื่อลูกค้า'
                    }
                }
            },
            CompanyAddress: {
                message: 'ที่อยู่ไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่ที่อยู่'
                    }
                }
            },
            CompanyPhone: {
                message: 'เบอร์โทรไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่เบอร์โทร'
                    }
                }
            },
            CompanyFax: {
                message: 'แฟกซ์ไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่เบอร์แฟกซ์'
                    }
                }
            },
            CompanyCode: {
                message: 'เลขประจำตัวผู้เสียภาษีไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่เลขประจำตัวผู้เสียภาษี'
                    }
                }
            }
        }
    });

    $('#paymentRecord').bootstrapValidator({
        message: 'ข้อมูลไม่ถูกต้อง',
        // feedbackIcons: {
        //     valid: 'glyphicon glyphicon-ok',
        //     invalid: 'glyphicon glyphicon-remove',
        //     validating: 'glyphicon glyphicon-refresh'
        // },
        fields: {
            PaymentCode: {
                message: 'เลขที่/เล่มที่ไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่เลขที่/เล่มที่'
                    }
                }
            },
            Total: {
                message: 'จำนวนเงินไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่จำนวนเงิน'
                    },
                    regexp: {
                        regexp:  /^[0-9,\.]*$/,
                        message: 'กรุณาใส่จำนวนเงินเป็นตัวเลขเท่านั้น'
                    }
                }
            }
        }
    });

    $('#productInfo').bootstrapValidator({
        message: 'ข้อมูลไม่ถูกต้อง',
        // feedbackIcons: {
        //     valid: 'glyphicon glyphicon-ok',
        //     invalid: 'glyphicon glyphicon-remove',
        //     validating: 'glyphicon glyphicon-refresh'
        // },
        fields: {
            Code: {
                message: 'รหัสสินค้าไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่รหัสสินค้า'
                    }
                }
            },
            ProductName: {
                message: 'ชื่อสินค้าไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่ชื่อสินค้า'
                    }
                }
            },
            SellPrice: {
                message: 'ราคาขายไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่ราคาขาย'
                    },
                    regexp: {
                        regexp: /^[0-9,\.]*$/,
                        message: 'กรุณาใส่ราคาขายเป็นตัวเลขเท่านั้น'
                    }
                }
            },
            AvgCost: {
                message: 'ราคาซื้อไม่ถูกต้อง',
                validators: {
                    // notEmpty: {
                    //     message: 'กรุณาใส่ราคาทุน'
                    // },
                    regexp: {
                        regexp: /^[0-9,\.]*$/,
                        message: 'กรุณาใส่ราคาทุนเป็นตัวเลขเท่านั้น'
                    }
                }
            }
        }
    });

    $('#defaultForm2').bootstrapValidator({
        message: 'ข้อมูลไม่ถูกต้อง',
        // feedbackIcons: {
        //     valid: 'glyphicon glyphicon-ok',
        //     invalid: 'glyphicon glyphicon-remove',
        //     validating: 'glyphicon glyphicon-refresh'
        // },
        fields: {
            FirstName: {
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่ชื่อ'
                    }
                }
            },
            LastName: {
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่นามสกุล'
                    }
                }
            },
            SSN: {
                message: 'เลขประจำตัวประชาชนไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่เลขประจำตัวประชาชน'
                    },
                    stringLength: {
                        min: 13,
                        max: 13,
                        message: 'กรุณาใส่เลขประจำตัวประชาชน 13 หลัก'
                    },
                    regexp: {
                        regexp: /^[0-9]*$/,
                        message: 'กรุณาใส่เลขประจำตัวประชาชนเป็นตัวเลขเท่านั้น'
                    }
                }
            },
            Salary: {
                message: 'อัตราค่าแรงไม่ถูกต้อง',
                validators: {
                    notEmpty: {
                        message: 'กรุณาใส่อัตราค่าแรง'
                    },
                    regexp: {
                        regexp:  /^[0-9,\.]*$/,
                        message: 'กรุณาใส่อัตราค่าแรงเป็นตัวเลขเท่านั้น'
                    }
                }
            },
            OTRate: {
                message: 'อัตรา OT ไม่ถูกต้อง',
                validators: {
                    regexp: {
                        regexp:  /^[0-9,\.]*$/,
                        message: 'กรุณาใส่เป็นตัวเลขเท่านั้น'
                    }
                }
            }
        }
    });
});

function getInvoice(CustName, CustPhone, CarCode, InvoiceNo){
    $.ajax({ // call AJAX to write new main layout section
        url: 'invoice.php',
        type: 'post',
        data: { 'getCustInfo': CustName, 'CustPhone': CustPhone, 'CarCodeInv': CarCode, 'InvoiceNo':InvoiceNo },
        async: false,
        success: function(newdata) {
            if(newdata=='Fail' && !CarCode.trim()){
                $('#CustID').val(0);
                $('#CustomerInv').val('');
                $('#Address1').val('');
                $('#Address2').val('');
                $('#Address3').val('');
                $('#Address4').val('');
                $('#TaxCode').val('');
                $('#BranchType1').iCheck('check');
                $('#BranchType4No').val(0);
                $('#BranchCodeNo').val('');
            }
            else if(newdata && newdata.match('CustID')){
                var data=newdata.split('##CustName##');
                var CustID=data[0];
                $('#CustID').val(CustID);

                var data1=data[1].split('##Address1##');
                var CustName=data1[0];
                $('#CustomerInv').val(CustName);

                var data2=data1[1].split('##Address2##');
                var Address1=data2[0];
                $('#Address1').val(Address1);

                var data3=data2[1].split('##TaxCode##');
                var Address2=data3[0];
                $('#Address2').val(Address2);

                var data4=data3[1].split('##BranchCode##');
                var TaxCode=data4[0];
                $('#TaxCode').val(TaxCode);

                var data5=data4[1].split('##Address3##');
                var BranchCode=data5[0];
                if(BranchCode=='สำนักงานใหญ่' || BranchCode==''){
                    $('#BranchType1').iCheck('check');
                }
                else{
                    $('#BranchCodeNo').val(BranchCode);
                    $('#BranchType2').iCheck('check');
                    $('#BranchType4No').val(1);
                }

                var data6=data5[1].split('##Address4##');
                var Address3=data6[0];
                $('#Address3').val(Address3);

                $('#Address4').val(data6[1]);
            }
        }
    });
}

function checkPassCode(){
    var CustomerID = $('#CustomerID').val();
    var UnlockAcess = $('#UnlockAcess').val();
    var ApproveNow = $('#ApproveNow').val();
    if($('#UnlockAcess').val().trim()==''){
        alert('กรุณาใส่รหัสปลดล็อค');
        return false;
    }
    else{
        $.ajax({ // call AJAX to write new main layout section
            url: 'lock-history.php',
            type: 'post',
            data: { 'CheckPassCode': CustomerID, 'UnlockAcess':UnlockAcess, 'ApproveNow': ApproveNow },
            async: false,
            success: function(newdata) {
                if(newdata=='true'){
                    $('#PasscodeCancel').trigger("click");
                    $('#submitForm').submit();
                }else{
                    $('#SendPasscodeTxt').html(newdata);
                    return false;
                }
            }
        });
    }
    return false;
}

// function checkInStock(){
//     var MoveProductID = $('#MoveProductID').val();
//     var OnStock = $('#MoveFromStock').val();
//     $.ajax({ // call AJAX to write new main layout section
//         url: 'stock-transfer.php',
//         type: 'post',
//         data: { 'getInStock': MoveProductID, 'OnStock':OnStock },
//         async: false,
//         success: function(newdata) {
//             var InfoArr=newdata.split('**');
//             $('#UnitInfo').html(' '+InfoArr[1]);
//             $('#MaxInstock').val(InfoArr[0]);
//         }
//     });
// }
//
function getCouponTotal(CouponPrice, CouponCodeList){
    if(!CouponCodeList.match('^[0-9,\.-\\s]*$')){
        alert('กรุณาใส่เป็นตัวเลขเท่านั้น');
        return false;
    }
    $.ajax({ // call AJAX to write new main layout section
        url: 'saveTemporary.php',
        type: 'post',
        data: { 'GetCouponTotal': CouponPrice, 'CouponCodeList':CouponCodeList },
        async: false,
        success: function(newdata) {
            var result = newdata.split("-");
            if(result[1] > 0){
                alert('มีการระบุเลขที่คูปองที่ไม่สามารถนำมาขายได้ กรุณาตรวจสอบ.');
                return false;
            }
            else{
                $('#showtotal-'+CouponPrice).html('&nbsp;&nbsp; รวม <span id="total-'+CouponPrice+'">'+number_format(result[0], 2)+'</span> บาท');
                var Sum=0;
                if($('#showtotal-100').html()){
                    Sum+=IntFix($('#total-100').html());
                }
                if($('#showtotal-300').html()){
                    Sum+=IntFix($('#total-300').html());
                }
                if($('#showtotal-500').html()){
                    Sum+=IntFix($('#total-500').html());
                }
                if($('#showtotal-1000').html()){
                    Sum+=IntFix($('#total-1000').html());
                }
                if(document.getElementById('Sell4CustID') && Sum>0){
                    Sum = Sum - $('#oldUseTotal').val();
                    $('#NetTotal').html('<label class="col-sm-2 control-label">ยอดชำระรวม:</label><div class="col-sm-5" style="margin-top:7px; color:blue;text-decoration:underline;">'+number_format(Sum, 2)+' บาท</div>');
                }
                else if(document.getElementById('NetTotal')){
                    Sum = Sum - $('#UseOldBalance').val();
                    $('#NetTotal').html('<label class="col-sm-2 control-label">ยอดชำระรวม:</label><div class="col-sm-5" style="margin-top:7px; color:blue;text-decoration:underline;">'+number_format(Sum, 2)+' บาท</div>');
                }
                $('#SellPrice').val(number_format(Sum, 2));
            }
        }
    });
}

function checkUnitName(){
    var ImportProductID = $('#ImportProductID').val();
    $.ajax({ // call AJAX to write new main layout section
        url: 'stock-import.php',
        type: 'post',
        data: { 'getUnitName': ImportProductID },
        async: false,
        success: function(newdata) {
            $('#UnitInfo').html(newdata);
        }
    });
}

function findUnitPrice(){
    var ImportQTY = IntFix($('#ImportQTY').val());
    var TotalPrice = IntFix($('#TotalPrice').val());
    if(TotalPrice>0 && ImportQTY>0){
        var UnitPrice = (TotalPrice/ImportQTY);
        $('#UnitPrice').html('(ราคาต่อหน่วย '+number_format(UnitPrice, 2)+' บาท)');
    }
    else{
        $('#UnitPrice').html('');
    }
}

function Cust4Car(CarFindCustID){
    $.ajax({ // call AJAX to write new main layout section
        url: 'carwash.php',
        type: 'post',
        data: { 'CarFindCustID': CarFindCustID },
        async: false,
        success: function(newdata) {
            if(newdata!=''){
                $('#CustomerName').val(newdata);
            }
        }
    });
}

function LockCust(ID4Update, Locked, CustomerName){
    if(Locked){ var lockAction="ล็อคเครดิต"; }
    else{ var lockAction="ปลดล็อค"; }
    if(confirm('คุณต้องการ'+lockAction+' '+CustomerName+' ใช่หรือไม่?')){
        $('#LockCust').val(ID4Update);
        $('#Locked').val(Locked);
        $('#submitForm').submit();
    }
}

function couponHistory(){
    var submitTo = 'lock-history.php?CustHistory='+$('#CustomerID').val()+'&view=1';
    if($('#submitTo').val() == 'coupon-report.php'){
        submitTo = "coupon-report.php?viewAction="+$('#viewAction').val();
    }
    $(location).attr('href', submitTo+'&Time='+$('#HistoryYear').val()+'-'+$('#HistoryMonth').val());
}

function NewTimeSheet(){
    $(location).attr('href', 'employees.php?manageTime=1&EmpID='+$('#TimeForEmp').val()+'&TimeSheet='+$('#Work4Year').val()+'-'+$('#Work4Month').val());
}

function NewPaymentSheet(){
    var moreInfo='&DDay='+$('#DDay').val();
    if($('#action').val() != ''){
        moreInfo+='&action='+$('#action').val();
    }
    $(location).attr('href', 'employees_payment.php?TimeSheet='+$('#payment4Year').val()+'-'+$('#payment4Month').val()+moreInfo);
}

function NewImport(){
    $('#ImportForm').submit();
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = IntFix(number);
    var s = '';
    var n = !isFinite(+number) ? 0 : +number;
    var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
    var thousands_sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
    var dec_point = (typeof dec_point === 'undefined') ? '.' : dec_point;
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixed(n, prec) : '' + Math.round(n)).split('.');
    if(s[0].length > 3){
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousands_sep);
    }
    if((s[1] || '').length < prec){
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec_point);
}

function toFixed(num, pre){
    num *= Math.pow(10, pre);
    num = (Math.round(num, pre)+(((num-Math.round(num, pre))>=0.5)?1:0))/Math.pow(10, pre);
    return num.toFixed(pre);
}

function IntFix(CheckValue){
    return (CheckValue+'').replace(/,/g, '').replace(/ /g, '').replace(/-/g, '');
}

function checkdate(newDate, oldDate, oilID, oldTxt){
    var res = newDate.split("/");
    var Date4Check = new Date(res[2], (res[1]-1), res[0], 0, 0, 0).getTime()/1000;
    if(Date4Check < oldDate){
        alert('วันที่มีผล ไม่ควรน้อยกว่า วันที่มีการเปลี่ยนแปลงครั้งสุดท้าย');
        $('#StartDate'+oilID).val(oldTxt);
    }
}

function checkBalance(){
    if(IntFix($('#BalanceAmount').val())==0){
        alert('กรุณาใส่ จำนวนเงิน');
        return false;
    }
    return true;
}

function checkCoupon(){
    if($('#AddCustomer').val().trim()==''){
        alert('กรุณาใส่ ชื่อบริษัท/ชื่อผู้ซื้อ');
        return false;
    }
    return true;
}

function checkAddCoupon(){
    if(($('#CouponCode1').val() > $('#CouponCode2').val())){
        alert('ข้อมูลเลขที่ไม่ถูกต้อง กรุณาตรวจสอบ');
        return false;
    }
    else if($('#SellBookNo').val().trim()==''){
        alert('กรุณาใส่ รหัสเล่มที่ของคูปอง');
        return false;
    }
    else if($('#BookCodeNo1').val().trim()==''){
        alert('กรุณาใส่ หมายเลขเล่มที่ของคูปอง');
        return false;
    }
    else if($('#CouponCode1').val().trim()==''){
        alert('กรุณาใส่ เลขที่ของคูปอง');
        return false;
    }
    else if($('#CouponCode2').val().trim()==''){
        alert('กรุณาใส่ เลขที่ของคูปอง');
        return false;
    }
    var BookStart = $('#BookCodeNo1').val();
    var BookEnd = $('#BookCodeNo2').val();
    if(parseInt(BookEnd) > 0){
        var AllBook = (parseInt(BookEnd)-parseInt(BookStart))+1;
    }
    else{
        var AllBook = 1;
    }
    var CouponPerBook = $('#CouponPerBook').val();
    var CheckWithBook = (AllBook * CouponPerBook);

    var CouponStart = $('#CouponCode1').val();
    var CouponEnd = $('#CouponCode2').val();
    var AllCoupon = parseInt(CouponEnd-CouponStart)+parseInt(1);
    //alert(BookStart+'|'+BookEnd+'|'+AllBook+'|'+CouponPerBook+'|'+CheckWithBook+'|'+AllCoupon);
    if(AllCoupon != CheckWithBook){
        alert("ข้อมูลไม่ถูกต้อง... กรุณาตรวจสอบ หมายเลขเล่มที่, จำนวนคูปองต่อเล่ม และ เลขที่คูปอง");
        return false;
    }
    return true;
}

function invoiceSet(EleNo, ProductDetail){
    if(ProductDetail){
        var OilIDArr = ProductDetail.split('~');
        if(OilIDArr[0]!='P'){
            OilID=OilIDArr[1];
            if($('#inputQTY-'+EleNo).length){
                $('#QTY-'+EleNo).html('');
            }
            if(OilID>0){
                $('#cost-'+EleNo).html(OilPrice[OilID]);
            }
            else if(OilID<0){
                $('#cost-'+EleNo).html('');
                $('#total-'+EleNo).val('');
                $('#QTY-'+EleNo).html('');
            }
        }
        else{
            var SetPrice=ProductPrice[OilIDArr[1]];
            $('#cost-'+EleNo).html(number_format(SetPrice, 2));
            $('#total-'+EleNo).val(number_format(SetPrice, 2));
            $('#QTY-'+EleNo).html('<input type="text" id="inputQTY-'+EleNo+'" name="inputQTY['+EleNo+']" class="form-control invoice_form price text-center" value="1" style="width:80px;" onchange="invoiceSet('+EleNo+', 0)">');
        }
    }

    if($('#inputQTY-'+EleNo).length == 0){
        var NewTotal = IntFix($('#total-'+EleNo).val());
        var NewCost =$('#cost-'+EleNo).html();
        if(NewTotal > 0){
            var SetQTY = (NewTotal/NewCost);
            SetQTY=number_format(SetQTY, 2);
            $('#QTY-'+EleNo).html(SetQTY);
        }
    }
    else{
        var ProdQTY = $('#inputQTY-'+EleNo).val();
        var SetPrice = IntFix($('#cost-'+EleNo).html());
        $('#total-'+EleNo).val(number_format(SetPrice*ProdQTY, 2));
    }

    var Total=0;
    for(var i=1; i<=10; i++){
        if(IntFix($('#total-'+i).val())){
            Total+=parseInt(IntFix($('#total-'+i).val()));
        }
    }
    var Tax = ((Total*7)/107);
    var SubTotal=(Total-Tax);
    $('#Total').html(number_format(Total, 2));
    $('#Tax').html(number_format(Tax, 2));
    $('#SubTotal').html(number_format(SubTotal, 2));
}

function resetInvoice(){
    for(var i=1; i<=10; i++){
        $('#QTY-'+i).html('');
    }
}

function printService(){
    $('.alert').hide();
    var printReadyEle = document.getElementById("pageContent");
    var printWin1 = window.open('', 'PrintWindow', 'width=750,height=650,top=50,left=50,toolbars=no,scrollbars=yes,status=no,resizable=yes');
    printWin1.document.open();

    var shtml = '<HTML>\n<HEAD>\n';
    if (document.getElementsByTagName != null){
      var sheadTags = document.getElementsByTagName("head");
      if (sheadTags.length > 0){
         shtml += sheadTags[0].innerHTML;
      }
    }
    shtml += '<link href="libs/css/myprintstyle.css?rand=1.22222" rel="stylesheet" type="text/css">';
    shtml += '</HEAD>\n<BODY>\n';
    if (printReadyEle != null){
       shtml += '<form name = frmform1>';
       var printReadyEle2 = printReadyEle.innerHTML.replace("table table-bordered table-default", "my-table");
       printReadyEle2 = printReadyEle2.replace(/font-size:10px;/g, "");
       printReadyEle2 = printReadyEle2.replace("table-responsive", "my-table2");
       shtml += '<div class="carwashFont" style="border:red; height:500px; padding:30px 0; margin-left: 15px; margin-right:15px;">'+printReadyEle2+'</div>';
       shtml += '<hr style="border: 1px solid #000" />';
       shtml += '<div class="carwashFont" style="border:red; padding:30px 0; margin-left: 15px; margin-right:15px;">'+printReadyEle2+'</div>';
    }
    shtml += '\n</form>\n<script>window.print(); window.close();</script></BODY>\n</HTML>';
    printWin1.document.write(shtml);
}

function printInvoice(){
    $('.alert').hide();
    var printReadyEle = document.getElementById("pageContent");
    var printWin1 = window.open('', 'PrintWindow', 'width=750,height=650,top=50,left=50,toolbars=no,scrollbars=yes,status=no,resizable=yes');
    printWin1.document.open();

    var shtml = '<HTML>\n<HEAD>\n';
    if (document.getElementsByTagName != null){
      var sheadTags = document.getElementsByTagName("head");
      if (sheadTags.length > 0){
         shtml += sheadTags[0].innerHTML;
      }
    }
    shtml += '<link href="libs/css/myprintstyle.css" rel="stylesheet" type="text/css">';
    shtml += '</HEAD>\n<BODY>\n';
    if (printReadyEle != null)
    {
       shtml += '<form name = frmform1>';
       var printReadyEle2 = printReadyEle.innerHTML.replace("table table-bordered table-default", "my-table");
       var printReadyEle2 = printReadyEle2.replace("table-responsive", "my-table2");
       shtml += '<div style="height:140mm;">'+printReadyEle2+'</div>';
       shtml += '<div style="height:5mm;"><hr style="border:1px solid #000000;"></div>';
       shtml += '<div>'+printReadyEle2.replace("ต้นฉบับ", "สำเนา")+'</div>';

    }
    shtml += '\n</form>\n<script>window.print(); window.close();</script></BODY>\n</HTML>';
    printWin1.document.write(shtml);
}

function checkValue(){
    if($('#CustomerInv').val().trim()==''){
        alert('กรุณาใส่ นามผู้ซื้อ');
        return false;
    }
    else if($('#Address1').val().trim()==''){
        alert('กรุณาใส่ ที่อยู่ผู้ซื้อ');
        return false;
    }
    else if($('#BranchType4No').val()==1 && $('#BranchCodeNo').val()==''){
        alert('กรุณาใส่ ข้อมูลเลขที่สาขา');
        return false;
    }
    else if($('#TaxCode').val().trim()==''){
        alert("กรุณาใส่ เลขประจำตัวผู้เสียภาษี\r\n\r\nใส่ 0000000000000 ในกรณีที่ไม่ต้องการระบุเลขประจำตัวผู้เสียภาษี");
        return false;
    }
    else if($('#TaxCode').val().length!=13){
        alert("กรุณาใส่ เลขประจำตัวผู้เสียภาษีให้ครบ 13 หลัก");
        return false;
    }
    else if($('#Total').html()==0){
        alert('กรุณาใส่ รายละเอียดสินค้า');
        return false;
    }

    for(var j=1; j<=10; j++){
        if(parseInt($('#total-'+j).val())==0 && parseInt($('#cost-'+j).html())>0){
            alert('กรุณาใส่ รายละเอียดสินค้าให้ครบถ้วน');
            return false;
        }
    }
}

function checkImport(){
    var ImportQTY = IntFix($('#ImportQTY').val());
    var TotalPrice = IntFix($('#TotalPrice').val());
    if($('#ImportQTY').val()==0 || $('#ImportQTY').val()==''){
        alert('กรุณาใส่จำนวนที่ซื้อ');
        return false;
    }
    else if($('#TotalPrice').val()==0 || $('#TotalPrice').val()==''){
        alert("กรุณาใส่ราคาทั้งหมดที่ซื้อสินค้า");
        return false;
    }
}

// function checkTransfer(){
//     var MoveQTY = IntFix($('#MoveQTY').val());
//     var MaxInstock = IntFix($('#MaxInstock').val());
//     if($('#MoveQTY').val()==0 || $('#MoveQTY').val()==''){
//         alert('กรุณาใส่จำนวนที่ต้องการเบิก');
//         return false;
//     }
//     else if(parseInt(MoveQTY) > parseInt(MaxInstock)){
//         alert("จำนวนสินค้าเหลือไม่เพียงพอในการเบิก\n\nกรุณาตรวจสอบ");
//         return false;
//     }
// }

function cancelService(ServiceNo, ServiceType, ServiceCancelID){
    if(confirm('คุณต้องการยกเลิก ใบรับบริการ'+ServiceType+'เลขที่ '+ServiceNo+' ?\r\nการยกเลิกจะทำให้ไม่สามารถแก้ไขใบรับบริการนี้ได้อีก\r\n\r\nกรุณายืนยันการยกเลิก')){
        canSubmit = true;
    }
    if(canSubmit){
        $.ajax({ // call AJAX to write new main layout section
            url: 'carwash.php',
            type: 'post',
            data: { 'ServiceCancelID': ServiceCancelID, 'ServiceType': ServiceType, 'ServiceNo': ServiceNo },
            async: false,
            success: function(newdata) {
                location.reload();
            }
        });
    }
    return false;
}

function setValue(what2Set, ID2Set, Date2Set){
    $('#updateSchedule').val(what2Set);
    $('#updateID').val(ID2Set);
    $('#Date2Set').val(Date2Set);
    if(what2Set=='billing'){
        $('#myModalLabel').html('เปลี่ยนวันวางบิล');
    }
    else{
        $('#myModalLabel').html('เปลี่ยนวันนัดชำระเงิน');
    }
}


function checkBillingDate(){
    var Date2Set = $('#Date2Set').val();
    var splitInfo = Date2Set.split('/');
    var newDate = Date.UTC(splitInfo[2], splitInfo[1], splitInfo[0]);

    var oldDate = $('#oldDateTxt').val();
    var split2Date = oldDate.split('/');
    var Date2Check = Date.UTC(split2Date[2], split2Date[1], split2Date[0]);

    if(newDate < Date2Check){
        alert('กรุณาเลือกวันที่มากกว่าวันปัจจุบัน');
        return false;
    }
    return true;
}


function setBilling(Name, Total, ID2Set){
    $('#BillingName').html(Name);
    $('#BillingTotal').html(Total+' บาท');
    $('#updateBillingID').val(ID2Set);
}

function switchType(ChooseType, OrderNo){
    if(ChooseType=='สินค้า'){
        var optionList = $('#productOption').html();
        var optionNameList = $('#productNameOption').html();
    }
    else{
        var optionList = $('#serviceOption').html();
        var optionNameList = $('#serviceNameOption').html();
    }
    $('#ProducID-'+OrderNo).html(optionList);
    $('#Name4Prod-'+OrderNo).html(optionNameList);
    if($('#ProducID-'+OrderNo).val()==-1){
        setProductName(-1, OrderNo, '', 0, 0, 1)
    }
}

function deleteJson(){
    var EmployeeID = $('#EmployeeID').val();
    var serviceType = $('#serviceType').val();
    $.ajax({ // call AJAX to write new main layout section
        url: 'saveTemporary.php',
        type: 'post',
        data: { 'DeleteEmpJson': EmployeeID, 'serviceType':serviceType },
        async: false
    });
    for(var i=1; i<6; i++){
        $('#TotalPrice-'+i).html('');
    }
}

function loadJson(){
    var EmployeeID = $('#EmployeeID').val();
    var serviceType = $('#serviceType').val();
    $.getJSON('results-'+serviceType+'-'+EmployeeID+'.json', function(data) {
        $('#PrintNumVal').val(data.PrintNum);
        if(data.PrintNum > 1){InvTxtNo
            $('#PrintNum').html(' / พิมพ์ครั้งที่ '+data.PrintNum);
        }
        var oldInfo=data.InvNo2Show;
        invoiceInfo = oldInfo.split('-');
        $('#InvTxtNo').html(invoiceInfo[0]);
        $('#InvDateShow').html(invoiceInfo[1]);
        $('#OrderShow').html(invoiceInfo[2]);

        $('#EditID').val(data.EditID);
        $('#back').val(data.back);
        $('#CarCode').val(data.CarCode);
        $('#CustomerName').val(data.CustomerName);
        $('#ServiceNote').val(data.ServiceNote);
        $('#DiscountVal').val(data.DiscountVal);
        $('#PercentDis').val(data.PercentDiscount);
        if(data.PercentDiscount==0 && data.DiscountVal>0){
            data.PercentDiscount=data.DiscountVal;
        }
        if(data.serviceType==1 && $('#EmpID-1').length){ // บริการล้างรถ
            for(var i=1; i<=4; i++){
                if(data.EmpID[i] > 0){
                    $('#EmpID-'+i).val(data.EmpID[i]);
                }
            }
        }
        for(var i=1; i<=5; i++){
            if(parseInt(data.ProducID[i])){
                //alert(data.ProductType[i]);
                if(data.ProductType[i]=='สินค้า'){
                    $('#ProductType-'+i).iCheck('check');
                }
                else{
                    $('#ServiceType-'+i).iCheck('check');
                    switchType(data.ProductType[i], i);
                }
                $('#ProducID-'+i).val(data.ProducID[i]);
                setProductName(data.ProducID[i], i, data.QTY[i], data.PercentDiscount, data.price[i], 100);
            }
        }
    });
}

function saveTemporary(){
    var EmployeeID = $('#EmployeeID').val();
    var AllVar = serializeForm(document.forms['carwashForm']);
    //alert(AllVar);
    $.ajax({ // call AJAX to write new main layout section
        url: 'saveTemporary.php',
        type: 'post',
        data: AllVar, // send all input field
        async: false
    });
}

function setProductName(ProductID, OrderNo, QTY, changePercentDis, UnitPrice, CodeOrName){
    UnitPrice=IntFix(UnitPrice);
    if(ProductID==-1){
        if(CodeOrName==1){ // call by product code
            $('#Name4Prod-'+OrderNo).val('-1');
        }
        else{ // call by product name
            $('#ProducID-'+OrderNo).val('-1');
        }
        $('#UnitPrice-'+OrderNo).val('');
        $('#price-'+OrderNo).val('');
        $('#QTY-'+OrderNo).val('');
    }
    else if(QTY===''){
        var getName = ProductName[ProductID];
        getInfo = getName.split('*****');
        if(CodeOrName==1){ // call by product code
            $('#Name4Prod-'+OrderNo).val(ProductID);
        }
        else{ // call by product name
            $('#ProducID-'+OrderNo).val(ProductID);
        }
        $('#UnitPrice-'+OrderNo).val(number_format(getInfo[1], 2));
        $('#price-'+OrderNo).val(getInfo[1]);
        if($('#QTY-'+OrderNo).val()==''){
            $('#QTY-'+OrderNo).val('1');
        }
        else if(getInfo[2]>0 && parseFloat($('#QTY-'+OrderNo).val())> parseFloat(getInfo[2])){
            $('#QTY-'+OrderNo).val(getInfo[2]);
        }
    }

    var checkProductID=$('#ProducID-'+OrderNo).val();
    var getName = ProductName[checkProductID];
    if(getName){
        getInfo = getName.split('*****');
        if(getInfo[2]>0 && parseFloat(QTY)>parseFloat(getInfo[2])){
            //alert(getInfo[2]+'----'+QTY);
            QTY = getInfo[2];
            $('#QTY-'+OrderNo).val(QTY);
        }
    }

    if(CodeOrName == 100){ // from load json
        $('#Name4Prod-'+OrderNo).val(ProductID);
        $('#QTY-'+OrderNo).val(QTY);
    }
    if(UnitPrice!=0){
        $('#UnitPrice-'+OrderNo).val(number_format(UnitPrice, 2));
        $('#price-'+OrderNo).val(UnitPrice);
    }
    if(OrderNo > 0){
        var PricePerUnit = IntFix($('#UnitPrice-'+OrderNo).val());
        var QTY = $('#QTY-'+OrderNo).val();
        var TotalPrice = (PricePerUnit * QTY);
        if(TotalPrice>0){
            $('#TotalPrice-'+OrderNo).html(number_format(TotalPrice, 2));
        }
        else{
            $('#TotalPrice-'+OrderNo).html('');
        }
    }

    var eachPrice=0;
    var SubTotal=0;
    var SetDiscount=0;
    var RecCount = $("input[name='RecCount[]']").length;
    for (var i=1; i<=RecCount; i++) {
        eachPrice = IntFix($('#TotalPrice-'+i).html());
        if(eachPrice > 0){
            SubTotal+=parseInt(eachPrice);
        }
    }

    SetDiscount = $('#DiscountVal').val();
    if(changePercentDis < 0){ // ลดแบบตาตตัว
        $('#PercentDis').val('');
    }
    else{
        var PercentDis = $('#PercentDis').val();
        if(CodeOrName == 100 && $('#PercentDis').val()==0){ // from load json
            $('#DiscountVal').val(number_format(changePercentDis, 2));
        }
        else if(PercentDis > 0){ // ลดแบบ %
            SetDiscount = (SubTotal*PercentDis)/100;
            $('#DiscountVal').val(number_format(SetDiscount, 2));
        }
        else{
            SetDiscount=0;
            $('#PercentDis').val('');
            $('#DiscountVal').val('');
        }
    }

    var RealTotal = (SubTotal-SetDiscount);
    $('#SubTotal').html(number_format(SubTotal, 2));
    $('#Total').html(number_format(RealTotal, 2));
    $('#ServiceTotal').val(RealTotal);
    saveTemporary();
}

function checkService(){
    if($('#serviceType').val()==1 && $('#serviceTypeCheck').val==1){
        var EmpCheck=parseInt($('#EmpID-1').val())+parseInt($('#EmpID-2').val())+parseInt($('#EmpID-3').val())+parseInt($('#EmpID-4').val());
    }
    else{
        var EmpCheck=1;
    }
    if($('#CarCode').val().trim()==''){
        alert('กรุณาใส่ ทะเบียนรถ');
        return false;
    }
    else if(EmpCheck == 0){
        alert('กรุณาใส่ เลือกพนักงานล้างรถ');
        return false;
    }
    else if($('#ServiceTotal').val()==0){
        alert('กรุณาใส่ รายการสินค้าและบริการ');
        return false;
    }
    return true;
}

function check_credit(){
    if($('#CreditBill').val()==0){
        alert('กรุณาเลือกบริษัท');
        return false;
    }
    else{
        var AllVar = serializeForm(document.forms['credit_billing']);
        AllVar+='&CheckCodeNo=1';
        $.ajax({ // call AJAX to write new main layout section
            url: 'credit-billing.php',
            type: 'post',
            data: AllVar,
            success: function(newdata) {
                if(parseInt(newdata) == -1){
                    alert('กรุณาระบุข้อมูลเล่มที่และเลขที่ใบสั่งน้ำมัน');
                    return false;
                }
                else if(parseInt(newdata)){
                    document.forms['credit_billing'].submit();
                }
                else{
                    alert('มีรหัสเลขที่ใบสั่งน้ำมันซ้ำ กรุณาตรวจสอบ');
                    return false;
                }
            }
        });
    }
}

function getBookNo(BookCode, RecNo){
    if(BookCode.trim()!=''){
        $.ajax({ // call AJAX to write new main layout section
            url: 'record_info.php',
            type: 'post',
            data: { 'SendBookCode': BookCode },
            async: false,
            success: function(newdata) {
                $('#credit-'+RecNo).removeClass('show_defference');
                $('#credit-'+RecNo).removeClass('show_defference2');
                $('#credit-'+RecNo).removeClass('show_defference3');
                newdataArr = newdata.split('-');
                $('#PaidBookNo-'+RecNo).val(newdataArr[0]);
                if(newdataArr[3].trim()){
                    $('#CompanyName-'+RecNo).html(newdataArr[3]+"<input type=\"hidden\" name=\"Cust4Credit["+RecNo+"]\" value=\""+newdataArr[5]+"\">");
                }
                else{
                    $('#CompanyName-'+RecNo).html("<select name=\"Cust4Credit["+RecNo+"]\" class=\"form-control\">"+$('#ShowCustOption').html()+"</select>");
                }
                if(BookCode.trim()!=''){
                    if(!newdataArr[4].trim()){
                        alert('ไม่พบเลขที่ใบสั่งน้ำมัน '+BookCode+' ในระบบ');
                        $('#credit-'+RecNo).addClass('show_defference');
                    }
                    else if(newdataArr[1]==1){
                        alert('เลขที่ใบสั่งน้ำมันนี้ถูกใช้งานไปแล้ว');
                        $('#credit-'+RecNo).addClass('show_defference2');
                    }
                    else if(newdataArr[2]==1){
                        alert('เครดิตของ '+newdataArr[3]+' ถูกล็อค!');
                        $('#credit-'+RecNo).addClass('show_defference3');
                    }
                }
            }
        });
    }
    else{
        $('#PaidBookNo-'+RecNo).val('');
        $('#CompanyName-'+RecNo).html('');
        $('#Amount-'+RecNo).val('');
        $('#CreditCar-'+RecNo).val('');
        $('#CreditOilType-'+RecNo).prop('selectedIndex',0);
        $('#credit-'+RecNo).removeClass('show_defference');
        $('#credit-'+RecNo).removeClass('show_defference2');
        $('#credit-'+RecNo).removeClass('show_defference3');
    }
}

function checkCouponStatus(CouponPrice, CheckCoupon, CouponRow){
    if(CouponPrice>0 && CheckCoupon>0){
        var StatusArr = ["ไม่พบคูปองนี้ในระบบ", "สามารถใช้งานได้", "คูปองถูกใช้ไปแล้ว", "คูปองยังไม่ถูกขาย", "คูปองถูกล็อค", "คูปองยังไม่ได้อนุมัติ"];
        $.ajax({ // call AJAX to write new main layout section
            url: 'record_info.php',
            type: 'post',
            data: { 'CheckCoupon': CheckCoupon, 'CouponPrice':CouponPrice },
            async: false,
            success: function(newdata) {
                var DataArr = newdata.split('~');
                if(DataArr[1].trim()){
                    $('#DisplayName-'+CouponRow).html(DataArr[1]);
                }
                if(DataArr[0]==0){
                    $('#DisplayName-'+CouponRow).html("<select name=\"Cust4Coupon["+CouponRow+"]\" class=\"form-control\">"+$('#ShowCustOption').html()+"</select>");
                }

                $('#coupon-'+CouponRow).removeClass();
                if(DataArr[0]!=1){
                    alert(StatusArr[DataArr[0]]);
                    $('#coupon-'+CouponRow).addClass('coupon_warning'+DataArr[0]);
                    if(DataArr[0]==2 || DataArr[0]==3){
                        $('#coupon-'+CouponRow).addClass('couponused');
                        $('#coupon-'+CouponRow).removeClass('couponwarning');
                    }
                    else{
                        $('#coupon-'+CouponRow).removeClass('couponused');
                        $('#coupon-'+CouponRow).addClass('couponwarning');
                    }
                }
                else{
                    $('#coupon-'+CouponRow).removeClass('couponused');
                    $('#coupon-'+CouponRow).removeClass('couponwarning');
                }
                $("#DisplayDef-"+CouponRow).html('0.00');
                $("#RealUse-"+CouponRow).val('');
            }
        });
    }
    else if($('#DisplayName-'+CouponRow).html()!=''){
        $('#useCouponNo-'+CouponRow).val('');
        $('#useCouponPrice-'+CouponRow).prop('selectedIndex', 0);
        $('#DisplayName-'+CouponRow).html('');
        $('#coupon-'+CouponRow).removeAttr('class');
        $('#coupon-'+CouponRow).attr('class', '');
        $('#coupon-'+CouponRow)[0].className = '';
        $("#DisplayDef-"+CouponRow).html('0.00');
        $("#RealUse-"+CouponRow).val('');
    }
}

function CheckAccess(CheckAccessCode){
    event.preventDefault();
    $.ajax({ // call AJAX to write new main layout section
        url: 'record_info.php',
        type: 'post',
        data: { 'CheckAccessCode': CheckAccessCode },
        async: false,
        success: function(newdata) {
            if(newdata!='true'){
                alert(newdata);
                return false;
            }
            else{
                $('#AccessCode').val();
                $('#AccessCode2').val();
                document.forms['paymentRecord'].submit();
                return false;
            }
        }
    });
}

function checkAllError(){
    var numItems = $('.couponused').length;
    if(numItems){
        $('#warningError').html("มีเลขคูปองที่ใช้งานแล้ว/ยังไม่ถูกขาย กรุณาตรวจสอบ");
        $("#OpenDialog1").trigger("click");
        return false;
    }

    var PassRow = 0;
    var AllRow = $('#couponForm tr').length;
    for(var j=1; j<=AllRow; j++){
        if(($('#DisplayName-'+j).html()!='') && (($('#RealUse-'+j).val()=='')||(parseInt($('#RealUse-'+j).val()) <= 0))){
            alert('กรุณาใส่ยอดเงินให้ครบ');
            return false;
        }
        else if((IntFix($('#RealUse-'+j).val()) > 0)){
            PassRow++;
        }
    }
    if(!PassRow){
        alert('กรุณาใส่ข้อมูล');
        return false;
    }

    var ErrorText = $('.couponwarning').length;
    if(parseInt(ErrorText)){
        $("#OpenDialog").trigger("click");
        return false;
    }
    else{
        document.forms['paymentRecord'].submit();
    }
}

function checCreditError(){
    var AllRow = $('.credit_used').length;
    var PassRow = 0;
    for(var j=1; j<=AllRow; j++){
        if(($('#PaidCodeNo-'+j).val() > 0) && (($('#Amount-'+j).val()=='')|| (parseInt($('#Amount-'+j).val()) <= 0))){
            alert('กรุณาใส่ยอดเงินให้ครบ');
            return false;
        }
        else if(($('#PaidCodeNo-'+j).val() > 0)){
            PassRow++;
        }
    }
    if(!PassRow){
        alert('กรุณาใส่ข้อมูล');
        return false;
    }
    var ErrorCount = parseInt($('.show_defference').length) + parseInt($('.show_defference3').length);
    if(parseInt($('.show_defference2').length) > 0){
        $('#warningError').html("มีรายการที่ใช้งานแล้วในรายการบันทึกการขาย");
        $("#OpenDialog1").trigger("click");
        return false;
    }
    else if(parseInt(ErrorCount)>0){
        $("#OpenDialog2").trigger("click");
        return false;
    }
    else{
        document.forms['paymentRecord'].submit();
    }
}

function setingHolding(formNo){
    if(formNo==1){
        var ReceiveIncome = IntFix($('#ReceiveIncome').val());
        var NotClearYet = IntFix($('#NotClearYet').val());
        if(parseInt(ReceiveIncome) > parseInt(NotClearYet)){
            alert('ยอดที่ระบุสูงกว่ายอดรวม');
            $('#ReceiveIncome').val(number_format(NotClearYet, 2));
            $('#ReceiveHolding').val('0.00');
            return false;
        }
        var setForHolding = NotClearYet-ReceiveIncome;
        $('#ReceiveHolding').val(number_format(setForHolding, 2));
    }
    else{
        var ReceiveHolding = IntFix($('#ReceiveHolding').val());
        var NotClearYet = IntFix($('#NotClearYet').val());
        if(parseInt(ReceiveHolding) > parseInt(NotClearYet)){
            alert('ยอดที่ระบุสูงกว่ายอดรวม');
            $('#ReceiveIncome').val(number_format(NotClearYet, 2));
            $('#ReceiveHolding').val('0.00');
            return false;
        }
        var setForHolding = NotClearYet-ReceiveHolding;
        //alert(ReceiveHolding+''+NotClearYet);
        $('#ReceiveIncome').val(number_format(setForHolding, 2));
    }
}

function recordIncome(){
    if($('#ReceiveHolding').length){
        var NotClearYet = IntFix($('#NotClearYet').val()) || 0;
        var ReceiveIncome = IntFix($('#ReceiveIncome').val()) || 0;
        var ReceiveHolding = IntFix($('#ReceiveHolding').val()) || 0;
        var summary = parseFloat(parseFloat(ReceiveIncome)+parseFloat(ReceiveHolding));
        //alert(NotClearYet+'|'+summary+'|'+ReceiveIncome+'|'+parseFloat(ReceiveHolding));
        if(NotClearYet!=summary){
            alert('ยอดรวมไม่ถูกต้อง กรุณาตรวจสอบ');
            return false;
        }
        else{
            document.forms['ReceiveIncome'].submit();
        }
    }
    else{
        document.forms['ReceiveIncome'].submit();
    }
}

function printExcel(CustID, HistoryID){
    $.ajax({ // call AJAX to write new main layout section
        url: 'saveTemporary.php',
        type: 'post',
        data: { 'BillingExport':1, 'CustID':CustID, 'History':HistoryID },
        async: false
    });
}

function setPriceWarning(){
    $('#PriceWarning').html('ยังไม่ได้อัพเดทราคาน้ำมัน&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    $.ajax({ // call AJAX to write new main layout section
        url: 'saveTemporary.php',
        type: 'post',
        data: { 'PriceWarningDone':1, 'Date2Warning':$("#Date2Warning").val() },
        async: false
    });
}

function updatePrice(){
    $("#closeThisBox").trigger("click");
    $("#OpenUpdatePriceForm").trigger("click");
}

function OpenViewInvoice(){
    $("#closeThisBox").trigger("click");
    $("#OpenViewInvoice").trigger("click");
    setCusName();
}

function confirmPage(GotoSecondStep){
    if(GotoSecondStep==1){
        document.getElementById('FormFirstStep').style.display='none';
        var AllVar = serializeForm(document.forms['UpdatePriceForm']);
        $.ajax({ // call AJAX to write new main layout section
            url: 'saveTemporary.php',
            type: 'post',
            data: AllVar, // send all input field
            async: false,
            success: function(newdata) {
                //alert(newdata);
                $('#UpdatePriceFormDetail').html(newdata);
            }
        });
    }
    else if(GotoSecondStep==2){
        $('#ConfirmNow').val(1);
        var AllVar = serializeForm(document.forms['UpdatePriceForm']);
        $.ajax({ // call AJAX to write new main layout section
            url: 'saveTemporary.php',
            type: 'post',
            data: AllVar, // send all input field
            async: false,
            success: function() {
                if($('#oilPage').val()==1){
                    location.reload();
                }
                else{
                    $('#PriceWarning').html('');
                    $("#closeUpdateForm").trigger("click");
                }
            }
        });
    }
    else{
        $('#UpdatePriceFormDetail').html('');
        document.getElementById('FormFirstStep').style.display='';
    }
}

function checkCustCredit(){
    if(document.forms['customerForm'].CustName && !document.forms['customerForm'].CustName.value.trim()){
        alert('กรุณาระบุชื่อบริษัทลูกค้า');
        return false;
    }
    else if(document.forms['customerForm'].CreditLimit && IntFix(document.forms['customerForm'].CreditLimit.value)<=0){
        alert('กรุณาระบุวงเงิน');
        return false;
    }
    else if(document.forms['customerForm'].PayCondition.value==1 && !document.forms['customerForm'].WarningDate.value.trim()){
        alert('กรุณาระบุวันที่ที่ต้องการวางบิล');
        return false;
    }
    else if(document.forms['customerForm'].PayCondition.value==2 && !document.querySelectorAll('input[type="checkbox"]:checked').length){
        alert('กรุณาเลือกวันที่ต้องการวางบิล');
        return false;
    }
    else if(document.forms['customerForm'].PayCondition.value==3 && !parseInt(document.forms['customerForm'].WarningCredit.value)){
        alert('กรุณาระบุเครดิตคงเหลือที่ต้องการวางบิล');
        return false;
    }
    else if(document.forms['customerForm'].DayBeforePayType.value==1 && !parseInt(document.forms['customerForm'].DayBeforePay.value)){
        alert('กรุณาระบุกำหนดการชำระเงิน');
        return false;
    }
    return true;
}

function addcashcredit(){
    if(!document.forms['AddCashForm'].cashPayDate.value.trim()){
        alert('กรุณาระบุวันที่');
        return false;
    }
    else if(!document.forms['AddCashForm'].cashPayAmount.value.trim() || parseInt(document.forms['AddCashForm'].cashPayAmount.value) <=0){
        alert('กรุณาระบุยอดเงิน');
        return false;
    }
    else{
        document.forms['AddCashForm'].submit();
        return true;
    }
}

function AccountingCHK(){
    //alert(document.getElementById('WantTax').style.display+'|'+document.getElementById('BookCodeNo').style.display);
    if(document.getElementById('WantTax').style.display!='none' && document.getElementById('BookCodeNo').style.display!='none' && !document.forms['AccountForm'].BookCodeNo.value.trim()){
        alert('กรุณาใส่เล่มที่/เลขที่');
        return false;
    }
    else if(IntFix(document.forms['AccountForm'].accountTotal.value)==0){
        alert('กรุณาใส่จำนวนเงิน');
        return false;
    }
    else if(!document.forms['AccountForm'].accountName.value.trim()){
        alert('กรุณาใส่ข้อมูลรายการ');
        return false;
    }
    else{
        return true;
    }
}

function addCouponPaid(){
    for(var j=0; j<5; j++){
        var AddRow = $('#couponTable tr').length;
        $('#couponTable tr:last').after('<tr id="coupon-'+AddRow+'"><td><input type="text" name="useCouponNo['+AddRow+']" id="useCouponNo-'+AddRow+'" class="form-control" value="" style="width:200px;" onchange="javascript:checkCouponStatus(document.getElementById(\'useCouponPrice-'+AddRow+'\').value, this.value, '+AddRow+');"></td><td><select name="useCouponPrice['+AddRow+']" id="useCouponPrice-'+AddRow+'" class="form-control" onchange="javascript:checkCouponStatus(this.value, document.getElementById(\'useCouponNo-'+AddRow+'\').value, '+AddRow+');"><option value="0">เลือก</option><option value="100">100</option><option value="300">300</option><option value="500">500</option><option value="1000">1,000</option></select></td><td style="text-align:left;"><span id="DisplayName-'+AddRow+'"></span></td><td><input type="text" class="form-control price" name="couponRealUse['+AddRow+']" id="RealUse-'+AddRow+'" value="" style="text-align:right;" onchange="javascript:findDifference('+AddRow+');"></td><td id="DisplayDef-'+AddRow+'">0.00</td></tr>');
    }
}

function findDifference(couponRow){
    var realuse = IntFix($("#RealUse-"+couponRow).val());
    var couponPrice = IntFix($("#useCouponPrice-"+couponRow).val());
    //alert(realuse+'|'+couponPrice);
    var Difference = parseFloat(couponPrice-realuse);
    if(Difference<0){
        $("#DisplayDef-"+couponRow).html('<span style="color:red;">'+number_format(Difference*(-1), 2)+'</span>');
    }
    else if(Difference>0){
        $("#DisplayDef-"+couponRow).html('<span style="color:blue;">'+number_format(Difference, 2)+'</span>');
    }
    else{
        $("#DisplayDef-"+couponRow).html(number_format(Difference, 2));
    }
}

function selectOtherTime(){
    $("#OilPriceButton").trigger("click");
}

function setDateNow(RoundSelected, DateNTime){
    var PriceValue = $('#PriceByDate-'+RoundSelected).val().split(',');
    for(var i=0; i<PriceValue.length; i++) {
        var PriceValueArr = PriceValue[i].split('=');
        OilPrice[PriceValueArr[0]]=PriceValueArr[1];
    }
    $("#closeThisBox2").trigger("click");
    for(var j=1; j<=10; j++){
        invoiceSet(j, $('#OilSelected-'+j).val());
    }
    $('#DateTime').val(DateNTime);
}

function sellFoemCheck(){
    if($('#ProductIDSell').val()==''){
        alert('กรุณาเลือกสินค้า');
        return false;
    }
    if(IntFix($('#SellQTY').val())<=0){
        alert('กรุณาระบุจำนวนสินค้า');
        return false;
    }
    if(IntFix($('#unitPrice').val())<=0){
        alert('กรุณาระบุราคาต่อหน่วย');
        return false;
    }
}

function findTotalsell(){
    var SetSellQTY = IntFix($('#SellQTY').val());
    if(parseInt(SetSellQTY)){
        ProductInfo = $('#ProductIDSell').val().split('**');
        if(parseInt(SetSellQTY) <= parseInt(ProductInfo[3])){
            var total = IntFix($('#unitPrice').val()) * IntFix($('#SellQTY').val());
            $('#TotalPrice').html(number_format(total,2));
        }
        else{
            alert('สินค้าในสต็อกไม่พอขาย');
            $('#SellQTY').val(0);
            $('#TotalPrice').html('0.00');
        }
    }
}

function selectProductSell(ProductInfo){
    ProductInfo = ProductInfo.split('**');
    $('#unitPrice').val(number_format(ProductInfo[1],2));
    $('#UnitNameSelected').html(ProductInfo[2]);
    if(ProductInfo!=''){
        $('#PrintQTY').html('มีสินค้าในสต็อก '+ProductInfo[3]+" "+ProductInfo[2]);
    }
    else{
        $('#PrintQTY').html('');
    }
    findTotalsell();
}

function addWorkTime(TimeDate, EmpID, ClockIn1, ClockOut1, ClockIn2, ClockOut2){
    $("#WorkTimeButton").trigger("click");
    $("#SetWorkTimeDate").val(TimeDate);
    $("#setClockIn-1").val(ClockIn1);
    $("#setClockOut-1").val(ClockOut1);
    $("#setClockIn-2").val(ClockIn2);
    $("#setClockOut-2").val(ClockOut2);
}

function deleteSellInfo(SellID){
    if(confirm('ยืนยันการลบข้อมูล?')){
        $('#deleteSellInfo').val(SellID);
        document.forms['sellReport'].submit();
        return false;
    }
    else{
        return false;
    }
}

function deletePayment(SellID, DelBalance, DelOldType){
    if(confirm('ยืนยันการลบข้อมูล?')){
        $('#deletePayment').val(SellID);
        $('#DelBalance').val(DelBalance);
        $('#DelOldType').val(DelOldType);
        document.forms['accountingForm'].submit();
        return false;
    }
    else{
        return false;
    }
}

function deleteSell(SellID){
    if(confirm('ยืนยันการลบข้อมูล?')){
        $('#deleteSell').val(SellID);
        document.forms['accountingForm'].submit();
        return false;
    }
    else{
        return false;
    }
}

function removeWorkTime(TimeDate, EmpID){
    $('.alert').hide();
    var canSubmit = false;
    if(confirm('ยืนยันการลบข้อมูล?')){
        canSubmit = true;
    }
    if(canSubmit){
        $.ajax({ // call AJAX to write new main layout section
            url: 'employees.php',
            type: 'post',
            data: { 'DeleteWorkDate':TimeDate, 'EmpID2Delete':EmpID },
            async: false,
            success: function() {
                $('#worktime-'+TimeDate).fadeOut(300);
                return false;
            }
        });
    }
}

function addTakeoff(TimeDate, EmpID, setTakeOff){
    $.ajax({ // call AJAX to write new main layout section
        url: 'employees.php',
        type: 'post',
        data: { 'TakeoffDate':TimeDate, 'EmpIDTimeoff':EmpID, 'setTakeOff':setTakeOff },
        async: false
    });
}

function openEditInvForm(InvoiceCode){
    $('#editInvoiceCode').val(InvoiceCode);
    $("#OpenEditInvoice").trigger("click");
}

function updateService(editServiceType){
    $('#editServiceType').val(editServiceType);
    $("#OpenUpdateCarWash").trigger("click");
}

function toggleCheckbox(){
    var AllDeleteBox = $('#AllDeleteBox').val();
    for(var x=0; x<AllDeleteBox; x++){
        if($('#DeleteCoupon-'+x).is(':checked')){
            $('#DeleteCoupon-'+x).iCheck('uncheck');
        }
        else{
            $('#DeleteCoupon-'+x).iCheck('check');
        }
    }
}

function toggleCreditBilling(){
    var AllDeleteBox = $('#AllDeleteBox').val();
    for(var x=0; x<AllDeleteBox; x++){
        if($('#CreditBillDel-'+x).is(':checked')){
            $('#CreditBillDel-'+x).iCheck('uncheck');
        }
        else{
            $('#CreditBillDel-'+x).iCheck('check');
        }
    }
}

function gotopage(PageName){
    $(location).attr('href', PageName);
}

function setProductIDSell(StockNo){
    $('#ProductIDSell').html($('#Stock-'+StockNo).html());
    $('#PrintQTY').html('');
}



