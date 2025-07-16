<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>__TITLENAME__</title>

    <!-- BootstrapのCSS読み込み -->
    <link
      href="/kotei/include/bootstrap/css/bootstrap.min.css"
      rel="stylesheet"
    />

    <link rel="stylesheet" type="text/css" href="/kotei/css/rnsien.css" />
  </head>

  <body>
    __SHeaderWithoutLogout__

    <div class="content-all">
      <!--content-all-->

      <hr />
      以下の内容をご確認ください。
      <hr />

      <br /><br />

      <font color="red">__ErrorLoop____ErrorString__<br />__ErrorLoop__</font
      ><br /><br />

      <input
        type="button"
        value="もどる"
        class="btn btn-primary"
        onclick="javascript:history.back()"
      />
      <hr />
    </div>
    <!--content-all-->
    <a href="top.php">トップ</a>
    __SFooter__ __SCopyright__
  </body>
</html>
