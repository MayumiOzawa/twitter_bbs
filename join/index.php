<?php
	// $error = "";	
	// if ($_SERVER['REQUEST_METHOD'] === 'POST')
	// {
	// 	$name = $_POST['name'];
	// 	$email = $_POST['email'];
	// 	$password = $_POST['password'];
	// 	if ($name == "" || $email == "" || $password == "")
	// 	{
	// 		$error = "データが入力されていません。";
	// 	}
	// 	else
	// 	{
	// 		header('Location: check.html');
	// 	}
	// }

	//セッション変数を使う時は必ず記述
	session_start();

	//ボタンが押されてPOST送信されたら
	if (!empty($_POST)){
		//エラー項目の確認
		if ($_POST['name'] == '') {
			$error['name'] = 'blank';
		}
		if ($_POST['email'] == '') {
			$error['email'] = 'blank';
		}
		if (strlen($_POST['password']) < 4) {
			$error['password'] = 'length';
		}
		if ($_POST['password'] == '') {
			$error['password'] = 'blank';
		}

		//正常に入力されていたら
		if (empty($error)) {
			$_SESSION['join'] = $_POST;
			//画面遷移
			header('Location: check.php');
			exit();
		}
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../style.css" />
<title>会員登録</title>
</head>

<body>
<div id="wrap">
<div id="head">
<h1>会員登録</h1>
</div>

<div id="content">
<p>次のフォームに必要事項をご記入ください。</p>
	<!-- <form action="check.html" method="post" enctype="multipart/form-data"> -->
	<form action="" method="post" enctype="multipart/form-data">
	<dl>
		<dt>ニックネーム<span class="required">必須</span></dt>
		<dd>
			<input type="text" name="name" size="35" maxlength="255" />
			<?php if (isset($error['name']) && ($error['name'] == 'blank')): ?> 
			<p class ="error">* ニックネームを入力してください</p>
		<?php endif; ?>
		</dd>
		<dt>メールアドレス<span class="required">必須</span></dt>
		<dd>
			<input type="text" name="email" size="35" maxlength="255" />
		</dd>
		<dt>パスワード<span class="required">必須</span></dt>
		<dd>
			<input type="password" name="password" size="10" maxlength="20" />
		</dd>
		<dt>写真など</dt>
		<dd>
			<input type="file" name="image" size="35" />
		</dd>
	</dl>
<!-- 	<div><input type="submit" value="入力内容を確認する" /><?php echo $error;?></div> -->
	<div><input type="submit" value="入力内容を確認する" /></div>
</form>

</div>

<div id="foot">
<p><img src="../images/txt_copyright.png" width="136" height="15" alt="(C) H2O SPACE, Mynavi" /></p>
</div>

</div>
</body>
</html>
