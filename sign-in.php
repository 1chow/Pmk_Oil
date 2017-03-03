<!doctype html>
<html lang="en">
<head>
    <!-- META -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In</title>

    <!-- STYLE THIS PAGE -->
    <link rel="stylesheet" href="libs/css/page-sign.css">

    <!-- CSS STYLE -->
    <link rel="stylesheet" href="libs/css/page-sign.css">
    <link rel="stylesheet" href="libs/css/style.css">
    <link rel="stylesheet" href="libs/css/style.responsive.css">

</head>
<body>

    <div class="container">
        <form method="post" action="index.php" class="boxsign form panel" style="overflow:hidden;">
            <div class="logoSign">
                <img src="images/logo-icon.png" alt="" class="logoicon"> P.M.K OIL
            </div>
            <div class="paddingSign">
                <?php
                if(isset($_POST["userName"]) && trim($_POST["userName"]) && isset($_POST["password"]) && trim($_POST["password"])){
                    print('<p style="color:red;">User Name หรือ Password ไม่ถูกต้อง, กรุณาลองใหม่อีกครั้ง</p>');
                }
                ?>
                <div class="headsign">
                    กรุณาใส่ username และ password
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" id="user" name="userName" placeholder="User Name">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                </div>
                <hr>
                <div class="right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-user"></i> เข้าสู้ระบบ &nbsp;</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="reset" class="btn btn-danger"><i class="fa fa-refresh"></i> รีเซ็ต &nbsp;</button>
                </div>
                <br><br>
            </div>
        </form>
    </div>

    <script type="text/javascript" src="libs/plugins/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="libs/plugins/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>