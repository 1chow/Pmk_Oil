<?php
include("dbvars.inc.php");
if(!preg_match('/-5-/', $EmpAccess)){
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
if(isset($_POST["OilName"])){
    foreach ($_POST["OilName"] as $key => $value) {
        if(trim($value)){
            $StartDateCut = explode("/", trim($_POST["SetStartDate"]));
            $setDatePrice=$StartDateCut[2]."-".$StartDateCut[1]."-".$StartDateCut[0];
            $sqlCheck="select OilID from ".$db_name.".oil where OilID=".$key.";";
            $rsCheck=mysql_query($sqlCheck);
            if(!mysql_num_rows($rsCheck)){
                $sqlInsert="INSERT INTO ".$db_name.".oil (Name) VALUES ('".mysql_real_escape_string(trim($value))."');";
                $rsInsrte=mysql_query($sqlInsert);
                $NewOilID=mysql_insert_id($Conn);
                $sqlInsert="INSERT INTO ".$db_name.".oil_price(OilID, RecordDate, RecordTime, Prices) VALUES (".intval($NewOilID).", '".$setDatePrice."', '".trim($_POST["SetStartTime"]).":00', '".floatval($_POST["OilPrice"][$key])."');";
                $rsInsrte=mysql_query($sqlInsert);
            }
            else{
                $sqlInsert="INSERT INTO ".$db_name.".oil_price(OilID, RecordDate, RecordTime, Prices) VALUES (".intval($key).", '".$setDatePrice."', '".trim($_POST["SetStartTime"]).":00', '".floatval($_POST["OilPrice"][$key])."');";
                $rsInsrte=mysql_query($sqlInsert);
            }
        }
    }
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>อัพเดตข้อมูลน้ำมันเรียบร้อยแล้ว.</div>';
}
else if(isset($_POST["DeleteItem"]) && intval($_POST["DeleteItem"])){
    $sqlDelete="Update ".$db_name.".oil SET oil.Deleted=1 Where OilID=".intval($_POST["DeleteItem"]).";";
    $rsDelete=mysql_query($sqlDelete);
    exit();
}


include("header.php");
$sqlDateTime="select RecordDate, RecordTime from ".$db_name.".oil_price order by RecordDate DESC, RecordTime DESC;";
$rsDateTime=mysql_query($sqlDateTime);
$DateTime=mysql_fetch_row($rsDateTime);
$TimeArr=explode("-", $DateTime[0]);
$SetDateFormat=$TimeArr[2]."/".$TimeArr[1]."/".$TimeArr[0];
$GetTimeArr=explode(":", $DateTime[1]);
$SetTime=$GetTimeArr[0].":".$GetTimeArr[1];
?>
    <section class="pageContent">
        <div class="title-body">
            <h2>น้ำมัน</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <form action="oil.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                    <div class="panel-heading">
                        <h3 class="panel-title" style="margin: 10px 0;">ราคาน้ำมัน มีผลตั้งแต่ <input type="text" name="SetStartDate" id="StartDate" class="form-control inline_input Calendar" value="<?php print($SetDateFormat); ?>" style="width:100px;"> เวลา <input type="text" name="SetStartTime" id="StartTime" class="form-control inline_input time" value="<?php print($SetTime); ?>" style="width:60px;"></h3>
                    </div>

                    <div class="panel-body">
                        <?php print($alertTxt); ?>
                        <table width="100%" class="table table-condensed table-striped table-default">
                        <tr>
                            <th>&nbsp;</th>
                            <th>ชนิดน้ำมัน</th>
                            <th>ราคาน้ำมัน</th>
                            <th>มีผลตั้งแต่</th>
                            <th>เวลา</th>
                            <th>ลบ</th>
                        </tr>
                        <?php
                            $sqlOil="select oil.OilID, oil.Name from ".$db_name.".oil where Deleted=0 order by Name ASC;";
                            $rsOil=mysql_query($sqlOil);
                            while($Oil=mysql_fetch_row($rsOil)){
                                $sqlOil2="select oil_price.RecordDate, oil_price.Prices, oil_price.RecordTime from ".$db_name.".oil_price where OilID=".$Oil[0]." order by RecordDate DESC, RecordTime DESC;";
                                $rsOil2=mysql_query($sqlOil2);
                                $Oil2=mysql_fetch_row($rsOil2);
                                $TimeArr=explode("-", $Oil2[0]);
                                $SetDateFormat="";
                                if($Oil2[0]){
                                    $SetDateFormat=$TimeArr[2]."/".$TimeArr[1]."/".$TimeArr[0];
                                }
                                $GetTimeArr=explode(":", $Oil2[2]);
                                $ThisTime=$GetTimeArr[0].":".$GetTimeArr[1];
                                OilPrice($Oil[0], $Oil[1], $Oil2[1], $SetDateFormat, 0, $ThisTime);
                            }
                            print("<tr><td colspan=\"6\"><p>&nbsp;</p></td></tr>");
                            OilPrice(0, '', 0, date("j/m/Y", time()), 0, date("H:i", time()));
                        ?>
                        </table>
                        <br>
                        <div class="actionBar right">
                            <input type="hidden" id="submitTo" value="oil.php">
                            &nbsp;&nbsp;&nbsp;
                            <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <input type="hidden" id="WarningNow" value="<?php print($WarningOilTrigger); ?>">
    <input type="hidden" id="Date2Warning" value="<?php print($Date2Check); ?>">
    <button data-toggle="modal" data-target="#WarningOil" id="OpenWarningOil" style="visibility: hidden;"></button>
    <div class="modal fade" id="WarningOil" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <form action="record_info.php" method="post" class="form-horizontal" autocomplete="off">
                <div class="modal-body text-center">
                    <br><p><b>ราคาน้ำมันของวันนี้ยังไม่ถูกอัพเดท</b></p><br>
                    <button type="button" class="btn btn-success" onclick="javascript:updatePrice();">อัพเดทราคาน้ำมัน</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="closeThisBox" onclick="javascript:setPriceWarning();">ยังไม่อัพเดทตอนนี้</button>
                    <br>&nbsp;
                </div>
            </form>
        </div>
      </div>
    </div>
<?php
$oilPage=1;
include("footer.php");
?>