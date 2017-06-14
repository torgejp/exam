<?php
  session_start();

  require_once('config.inc.php');
  
$start='2017-05-01';
$end='2017-05-31';

  $db = mysql_pconnect(DB_HOST, DB_USER_NAME, DB_PASSWORD) or die("MySQL connection error");
  mysql_select_db(DB_DATABASE, $db) or die("DB error");
  
  $sqlset = "SET NAMES 'utf8';";
  mysql_query($sqlset) or die (mysql_error());

  $user_no="";
  $nick_name="";
  $geust_name="";
  $guest_cnt=0;
  $ranking=array();
  //ランキングを集計するためのレコードを取得（user_noが7654321のデータは集計から除外する）
  $qry = "SELECT b.user_no user_no,b.nick_name nick_name, b.first_name first_name, b.last_name last_name,a.guest_name guest_name, a.regist_datetime regist_datetime FROM `bs_mobilization` a,users b WHERE b.user_no <> 7654321 and a.user_no=b.user_no and a.regist_datetime between '".$start." 00:00:00' and '".$end." 23:59:59' ORDER by b.user_no, a.guest_name";
  $result = mysql_query($qry) or die("Query failed ".$qry);
  while($row = mysql_fetch_array($result)) {
    //ユーザ番号ごとに結果を集計する
	if($row['user_no'] != $user_no) {
	  if($guest_cnt>0) {
		$ranking[] = array("nick_name"=>$nick_name, "guest_cnt"=>$guest_cnt);
	  }
	  $user_no = $row['user_no'];
	  $guest_name = $row['guest_name'];
	  //ニックネームがない場合は氏名
	  if($row['nick_name']) $nick_name = $row['nick_name'];
	  else                  $nick_name = $row['first_name'] . ' ' . $row['last_name'];
	  $guest_cnt=1;
	} else {
	  //ゲスト名(guest_name)が重複するデータがある場合は1人とみなす
	  if($row['guest_name'] != $guest_name) {
		$guest_cnt++;
		$guest_name=$row['guest_name'];
	  }
	}
  }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="format-detection" content="telephone=no,address=no,email=no">
<meta name="viewport" content="width=1200,initial-scale=1.0">
<title></title>
</head>

<body>
<div class="table">
<h2 class="tit">ランキングの集計結果(<?php echo date("m/d", strtotime($start)); ?>~<?php echo date("m/d", strtotime($end)); ?>)</h2>
</div>
<table>
<tr>
<th>順位</th>
<th>ニックネーム</th>
<th>合計人数</th>
</tr>
<?php
  //紹介数を降順に並べ替え
  function compare($a, $b) {
    if ($a == $b) {
      return 0;
    }
    return ($a['guest_cnt'] > $b['guest_cnt']) ? -1 : 1;
  }
  $compare = 'compare';
  $result = uasort($ranking, 'compare');
  $i=1;
  $guest_cnt=0;
  $rank=0;
  //ランキングを一覧表示
  foreach($ranking as $data) {
	//同じ紹介数の場合は同順とする
	if($data['guest_cnt']!=$guest_cnt) {
	  $guest_cnt = $data['guest_cnt'];
	  $rank=$i;
	}
	echo '<tr>';
	echo '<td align="right">'.$rank.'</td>';
	echo '<td>'.$data['nick_name'].'</td>';
	echo '<td align="right">'.$data['guest_cnt'].'</td>';
	echo '</tr>';
	$i++;
  }
?>
</table>
</body>
</html>