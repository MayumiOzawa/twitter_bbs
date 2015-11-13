<?php
	session_start();
	require('dbconnect.php');

	//ログインしているかどうかチェック→サーバー
	if (isset($_SESSION['id'])) {
		$id = $_REQUEST['id'];

		//投稿を検査する
		$sql = sprintf('SELECT * FROM posts WHERE id=%d', 
			mysqli_real_escape_string($db, $id)
		);
		$record = mysqli_query($db, $sql) or die(mysqli_error($db));
		$table = mysqli_fetch_assoc($record);
		//いたずらされた時の為にIF文を記載しておく。
		if ($table['member_id'] == $_SESSION['id'])  {
			//削除
			$sql = sprintf('UPDATE posts SET delete_flag=1 WHERE id=%d',
			// $sql = sprintf('DELETE FROM posts WHERE id=%d',
				mysqli_real_escape_string($db, $id)
			);
			mysqli_query($db, $sql) or die(mysqli_error($db));
		}
	}

	header('Location: index.php');
	exit();
?>