<?php
include("dbvars.inc.php");
if(!preg_match('/-3-/', $EmpAccess) && $UserID!=1){
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
if(isset($_POST["UnitName"])){
    foreach ($_POST["UnitName"] as $key => $value) {
        if(trim($value)){
            if(intval($key)){
                $sqlUpdate="update ".$db_name.".product_unit set Name='".mysql_real_escape_string(trim($value))."' where ID=".intval($key).";";
                $rsUpdate=mysql_query($sqlUpdate);
            }
            else{
                $sqlInsert="INSERT INTO ".$db_name.".product_unit (Name) VALUES ('".mysql_real_escape_string(trim($value))."');";
                $rsInsrte=mysql_query($sqlInsert);
            }
        }
    }
    $alertTxt='<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>อัพเดตข้อมูลหน่วยนับเรียบร้อยแล้ว.</div>';
}
else if(isset($_POST["DeleteItem"]) && intval($_POST["DeleteItem"])){
    $sqlDelete="Update ".$db_name.".product_unit SET product_unit.Deleted=1 Where ID=".intval($_POST["DeleteItem"]).";";
    $rsDelete=mysql_query($sqlDelete);
    exit();
}

include("header.php");
?>
    <section id="pageContent" class="pageContent">
        <div id="PageHeader" class="title-body">
            <h2>จัดการหน่วยนับ</h2>
        </div>

        <div class="content-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php print($alertTxt); ?>
                    <form action="unitofsales.php" method="post" class="form-horizontal" role="form" autocomplete="off">
                        <table class="table table-condensed table-striped table-default">
                        <tr>
                            <th style="text-align:center;" width="100px">ลำดับที่</th>
                            <th style="text-align:center;" width="200px">ชื่อหน่วยนับ</th>
                            <th>ลบ</th>
                        </tr>
                    <?php
                        $count=1;
                        $sqlOil="SELECT ID, Name from ".$db_name.".product_unit where Deleted=0 order by Name ASC;";
                        $rsUnit=mysql_query($sqlOil);
                        while($Unit=mysql_fetch_row($rsUnit)){
                            print('
                    <tr id="item-'.$Unit[0].'">
                        <td align="center">'.$count.'</td>
                        <td align="center"><input type="text" name="UnitName['.$Unit[0].']" value="'.$Unit[1].'"></td>
                        <td><div id="'.$Unit[0].'">&nbsp;<i class="fa fa-trash-o removeItem" id="'.$Unit[1].'"></i></div></td>
                    </tr>');
                            $count++;
                        }
                    ?>
                    <tr>
                        <td align="center">เพิ่ม</td>
                        <td align="center"><input type="text" name="UnitName[0]" value=""></td>
                        <td>&nbsp;</td>
                    </tr>
                    </table>
                    <br>
                    <div id="actionBar" class="actionBar" style="width:450px; text-align:right;">
                            <input type="hidden" name="page" value="<?php if(isset($_REQUEST['page'])){ print($_REQUEST['page']); } ?>">
                            <input type="hidden" id="backPage" value="stock.php<?php if(isset($_REQUEST['page'])){ print('?page='.$_REQUEST['page']); } ?>">
                            <input type="hidden" id="submitTo" value="unitofsales.php">
                            &nbsp;&nbsp;&nbsp;
                            <button type="submit" class="btn btn-success btn-rounder">บันทึกข้อมูล</button>
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