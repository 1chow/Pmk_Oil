<?php
include("dbvars.inc.php");

if(isset($_POST["ArrDelete"])){
	asort($_POST["ArrDelete"]);
	foreach($_POST["ArrDelete"] as $key => $value) {
		if(intval($value)){
			echo $sqlDelete1="delete from coupon_used where UsedID=".intval($value).";";
			$rsDelete=mysql_query($sqlDelete1);

			echo "<br>";
			$sqlDelete1="delete from coupon_history where LockReason=".intval($value).";";
			$rsDelete=mysql_query($sqlDelete1);

			$sqlDelete1="delete from payments where CouponUsedID=".intval($value).";";
			$rsDelete=mysql_query($sqlDelete1);
		}
	}
	print("done");
	exit();
}

include("header.php");
?>
    <section class="pageContent">
        <div class="content-center">
            <div class="panel panel-default">
                <form name="CouponReport" action="#" method="post"><div class="panel-body">

                    <table width="100%" border="1" class="coupon_report">
                        <tr>
                            <th width="2%">ลำดับ</th>
                            <th width="8%">วันที่</th>
                            <th width="8%">โดย</th>
                            <th width="15%">บริษัท</th>
                            <th>รายละเอียด</th>
                            <th width="8%">มูลค่าคูปอง</th>
                            <th width="8%">จำนวนเงิน</th>
                            <th width="8%">คูปองคงเหลือ</th>
                            <th width="8%">ลบ</th>
                        </tr>

<?php
$count=1;
$sqlHistory="SELECT HistoryNote, Total, ProcessDate, FirstName, customer.CustName, ChangeNote, HistoryID, customer.CustID, LockReason from (((".$db_name.".coupon_history inner join ".$db_name.".employee on employee.EmpID=coupon_history.EmpID) inner join ".$db_name.".customer on customer.CustID=coupon_history.CustomerID) inner join ".$db_name.".payments on payments.PaymentID=coupon_history.LockReason) where HistoryNote like 'ใช้คูปอง' order by ProcessDate DESC, HistoryID ASC;";
$rsHistory=mysql_query($sqlHistory);
while($History=mysql_fetch_row($rsHistory)){
	$sqlPrice="select sum(Price), UsedID, UseHistoryID from (".$db_name.".coupon inner join ".$db_name.".coupon_used on UseHistoryID=UsedID) where UseHistoryID=".intval($History[8])." and ProcessDate='".$History[2]."' and coupon.CustomerID=".intval($History[7])." group by UseHistoryID;";
	$rsPrice=mysql_query($sqlPrice);
    $Price=mysql_fetch_row($rsPrice);
    $DateArr=explode("-", $History[2]);
    $setDate=$DateArr[2]."-".$DateArr[1]."-".$DateArr[0];
    $Detail=preg_replace('/#/', '<br>', $History[5]);
    $overCost=0;
    if($Price[0] != $History[1]){
        $overCost=round($Price[0]-$History[1], 2);
        $overCost=number_format($overCost, 2);
    }
    print('<tr>
	    <td>'.$count.'</td>
	    <td>'.$setDate.'</td>
	    <td>'.$History[3].'</td>
	    <td style="text-align:left;">'.$History[4].'</td>
	    <td style="text-align:left;">'.$Detail.'</td>
	    <td>'.number_format($Price[0], 2).'</td>
	    <td>'.$History[1].'</td>
	    <td>'.$overCost.'</td>
	    <td>');
    if(intval($Price[0])==0){
    	print('<input type="checkbox" name="ArrDelete[]" value="'.$History[8].'">');
    }
    print('</td>
	    </tr>');
	$count++;
}
?>
					</table>
					<input type="submit" name="submit" value="GO">
				</form>
			</div>
		</div>
	</section>
<?php
include("footer.php");
?>