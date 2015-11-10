<?php
	session_start();
	require('dbconnect.php');

	//ログイン時に記録しているidが存在しているか+ログインしてから１週間以内か
	//今の時間がログインから１時間以内か（3600が60分を指している）
	if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
		//ログインしている
		$_SESSION['time'] = time();

		//ログインしている人のユーザー情報を取得
		$sql = sprintf('SELECT * FROM members WHERE id=%d',
			mysqli_real_escape_string($db, $_SESSION['id'])
			);

		//mysqli_query...SQL文をDBで実行する関数
		//mysqli_error...DB処理でエラーが発生した場合、エラーメッセージを返す
		//die...メッセージを表示して処理を終了する
		$record = mysqli_query($db, $sql) or die(mysqli_error($db));

		//mysqli_fetch_assoc...実行結果をフェッチする関数
		//フェッチ,,,実行結果から１行分のデータを取り出して、カーソルを下に移動する
		$member = mysqli_fetch_assoc($record);
	}else{
		//ログインしていない
		header('Location: login.php');
		exit();
	}

	//投稿を記録する
	//投稿ボタンを押してPOST送信されたかチェック
	if (!empty($_POST)){
		//メッセージが入力されていた時
		if ($_POST['message'] != '') {
			//%d...整数型のデータを置換する文字
			//%s...文字型のデータを置換する文字
			//SQL文では数位をダブルクォーテーションで囲まない（文字は囲む）
			$sql = sprintf('INSERT INTO posts SET member_id=%d, message="%s", reply_post_id=%d, created=NOW()',
				mysqli_real_escape_string($db, $member['id']),
				mysqli_real_escape_string($db, $_POST['message']),
				mysqli_real_escape_string($db, $_POST['reply_post_id'])
			);
			mysqli_query($db, $sql) or die(mysqli_error($db));

			header('Location: index.php');
			exit();
		}
	}

	//投稿を取得する
	//テーブル結合して
	$sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC');
	$posts = mysqli_query($db, $sql) or die(mysqli_error($db));

	//返信の場合
	if (isset($_REQUEST['res'])) {
		$sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=%d ORDER BY p.created DESC',
			mysqli_real_escape_string($db, $_REQUEST['res'])
		);
		$record = mysqli_query($db, $sql) or die(mysqli_error($db));
		$table = mysqli_fetch_assoc($record);
		$message = '@' . $table['name'] . ' ' . $table['message'];
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<title>ひとこと掲示板</title>
</head>

<body>
<div id="wrap">
	<div id="head">
		<h1>ひとこと掲示板</h1>
	</div>
	<div id="content">
		<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
		<form action="" method="post">
			<dl>
				<dt><?php echo htmlspecialchars($member['name']); ?>さん、メッセージをどうぞ</dt>
				<dd>
				<textarea name="message" cols="50" rows="5"><?php if(isset($message)){ echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); } ?></textarea>
				<input type="hidden" name="reply_post_id" value="<?php if(isset($_REQUEST['res'])){ echo htmlspecialchars($_REQUEST['res'], ENT_QUOTES, 'UTF-8'); } ?>" />
				</dd>
			</dl>
			<div>
			<p>
				<input type="submit" value="投稿する" />
			</p>
			</div>
		</form>

		<?php
			while($post = mysqli_fetch_assoc($posts)):
		?>

		<div class="msg">
			<img src="member_picture/<?php echo htmlspecialchars($post['picture'], ENT_QUOTES, 'UTF-8'); ?>" width="48" height="48" 
				alt="<?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8'); ?>" />

			<p><?php echo htmlspecialchars($post['message'], ENT_QUOTES, 'UTF-8'); ?><span class="name">(<?php echo htmlspecialchars($post['name'], ENT_QUOTES, 
				'UTF-8'); ?>)</span>
				[<a href="index.php?res=<?php echo htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8'); ?>">Re</a>]</p>
			<p class="day">
				<a href="view.php?id=<?php echo htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($post['created'], ENT_QUOTES, 'UTF-8'); ?></a>
			<?php
				if ($post['reply_post_id'] > 0):
			?>
				<a href="view.php?id=<?php echo htmlspecialchars($post['reply_post_id'], ENT_QUOTES, 'UTF-8'); ?>">返信元のメッセージ</a>
			<?php
				endif;
			?>
			</p>
		</div>
		<?php
			endwhile;
		?>

		<div class="msg">

			<img src="member_picture/dummy.png" width="48" height="48" alt="dummy" />

			<p>押忍！<span class="name">（しんや）</span>[<a href="index.html?res=1">Re</a>]</p>
			<p class="day">
				<a href="view.html?id=2">2015-10-10 12:00:00</a>
				[<a href="#" style="color: #F33;">削除</a>]
			</p>


		</div>

		<ul class="paging">
			<li><a href="#">前のページへ</a></li>
			<li><a href="#">次のページへ</a></li>
		</ul>
	</div>
	<div id="foot">
		<p><img src="images/txt_copyright.png" width="136" 	height="15" alt="(C) H2O SPACE, Mynavi" /></p>
	</div>
</div>
</body>
</html>
