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
			//SQL文では数値をダブルクォーテーションで囲まない（文字は囲む）
			$sql = sprintf('INSERT INTO posts SET member_id=%d, message="%s", reply_post_id=%d, created=NOW(), delete_flag=0',
				mysqli_real_escape_string($db, $member['id']),
				mysqli_real_escape_string($db, $_POST['message']),
				mysqli_real_escape_string($db, $_POST['reply_post_id'])
			);
			mysqli_query($db, $sql) or die(mysqli_error($db));

			header('Location: index.php');
			exit();
		}
	}

	//$_REQUEST['page']にユーザーが見たいページ数が入る
	if (isset($_REQUEST['page'])) {
		$page = $_REQUEST['page'];
	}else{
		$page = 1;
	}

	// if ($page == '') {
	// 	$page = 1;
	// }

	//もし$pageにマイナスが入っていた場合、1に置き換えたい
	$page = max($page, 1);

	//最終ページを取得する
	$sql = 'SELECT COUNT(*) AS cnt FROM posts WHERE delete_flag=0';
	$recordSet = mysqli_query($db, $sql);
	$table = mysqli_fetch_assoc($recordSet);
	$maxPage = ceil($table['cnt'] / 5);
	//最大ページより大きい数を指定されても、最大ページに置き換える
	$page = min($page, $maxPage);

	//開始データの開始番号を割り出す
	//SQL文のLIMIT句の開始番号は0からなので、データの最初を0にしておく
	$start = ($page - 1) * 5;
	$start = max(0, $start);

	//投稿を取得する
	$sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.delete_flag=0 ORDER BY p.created DESC LIMIT %d, 5', $start);
	$posts = mysqli_query($db, $sql) or die(mysqli_error($db));

	//返信の場合
	if (isset($_REQUEST['res'])) {
		//返信用のメッセージを作る為に、元のメッセージと投稿者の名前を取得
		$sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=%d AND p.delete_flag=0 ORDER BY p.created DESC',
			mysqli_real_escape_string($db, $_REQUEST['res'])
		);
		$record = mysqli_query($db, $sql) or die(mysqli_error($db));
		$table = mysqli_fetch_assoc($record);
		
		//返信用メッセージを作成
		$message = '@' . $table['name'] . ' ' . $table['message'];
	}

	//自作関数 htmlspecialcharsのショートカット
	function h($value) {
		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}

	//本文内のURLにリンクを設定します
	function makeLink($value){
		return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", '<a href="\1\2">\1\2</a>', $value);
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
				<textarea name="message" cols="50" rows="5"><?php if(isset($message)){ echo h($message); } ?></textarea>
				<input type="hidden" name="reply_post_id" value="<?php if(isset($_REQUEST['res'])){ echo h($_REQUEST['res']); } ?>" />
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
			<img src="member_picture/<?php echo h($post['picture']); ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>" />

			<p><?php echo makeLink(h($post['message'])); ?><span class="name">(<?php echo h($post['name']); ?>)</span>
				[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>
			<p class="day">
				<a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
			<?php
				if ($post['reply_post_id'] > 0):
			?>
				<a href="view.php?id=<?php echo h($post['reply_post_id']); ?>">返信元のメッセージ</a>
			<?php
				endif;
			?>
			<?php
				if ($_SESSION['id'] == $post['member_id']):
			?>
				[<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color: #F33;">削除</a>]
			<?php
				endif;
			?>
			</p>
		</div>
		<?php
			endwhile;
		?>

		<ul class="paging">
			<?php
				if ($page > 1) {
			?>
					<li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
			<?php
				}else{
			?>
					<li>前のページへ</li>
			<?php
			}
			?>

			<?php
				if ($page < $maxPage) {
			?>
					<li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a></li>
			<?php
				}else{
			?>
					<li>次のページへ</li>
			<?php
			}
			?>
		</ul>
	</div>
	<div id="foot">
		<p><img src="images/txt_copyright.png" width="136" 	height="15" alt="(C) H2O SPACE, Mynavi" /></p>
	</div>
</div>
</body>
</html>
<span></span>