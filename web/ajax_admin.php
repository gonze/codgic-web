<?php
session_start();
if(!isset($_SESSION['administrator']))
	die('Not administrator');
if(!isset($_SESSION['admin_tfa']) || !$_SESSION['admin_tfa'])
	die('No TFA');
if(!isset($_POST['op']))
	die('error');
$op=$_POST['op'];
require('inc/database.php');
require 'inc/problem_flags.php';
$level_max=(PROB_LEVEL_MASK>>PROB_LEVEL_SHIFT);
if($op=="list_usr"){ 
	$res=mysqli_query($con,"select user_id,accesstime,solved,submit,(accesstime IS NULL) from users where defunct='Y'");
	if(mysqli_num_rows($res)==0)
		die ('<table class="table table-condensed table-striped"><caption>禁用用户</caption></table><div class="row-fluid"><div class="alert alert-danger center">暂没有被禁用的用户...</div></div>');
?>
	<table class="table table-condensed table-striped">
		<caption>被禁用的用户</caption>
		<thead>
			<tr>
				<th>用户</th>
				<th>最近登录</th>
				<th>提交</th>
				<th>AC数量</th>
				<th>启用</th>
				<th>禁用</th>
			</tr>
		</thead>
		<tbody>
			<?php
				while($row=mysqli_fetch_row($res)){
					echo '<tr><td>',$row[0];
					if(is_null($row[1]))
						echo '<span style="color:red">(新用户)</span>';
					echo '</td>';
					echo '<td>',$row[1],'</td>';
					echo '<td>',$row[3],'</td>';
					echo '<td>',$row[2],'</td>';
					echo '<td><a href="#"><i class="fa fa-check"></i></a></td>';
					echo '<td>',($row[4]?'<a href="#"><i class="fa fa-remove"></i></a>':''),'</td></tr>';
				}
			?>
		</tbody>
	</table>
<?php
}else if($op=="list_priv"){ ?>
	<table class="table table-condensed table-striped">
		<caption>用户权限</caption>
		<thead>
			<tr>
				<th>用户</th>
				<th>权限</th>
				<th>删除</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				$res=mysqli_query($con,"select user_id,rightstr from privilege order by user_id");
				while($row=mysqli_fetch_row($res)){
					echo '<tr><td>',$row[0],'</td><td>',$row[1],'</td><td><a href="#"><i class="fa fa-remove"></i></a></td></tr>';
				}
			?>
		</tbody>
	</table>
<?php
}else if($op=="list_news"){
	$res=mysqli_query($con,"select news_id,time,title,importance from news where news_id>0 order by importance desc, news_id desc");
	if(mysqli_num_rows($res)==0)
		die ('<div class="row-fluid"><div class="alert alert-info span4">目前还没有发布过新闻...</div></div>');
?>
	<table class="table table-condensed table-striped">
		<caption>新闻列表</caption>
		<thead>
			<tr>
				<th style="width:6%">ID</th>
				<th style="width:20%">日期</th>
				<th style="width:68%">标题</th>
				<th style="width:6%">编辑</th>
			</tr>
		</thead>
		<tbody>
			<?php
				while($row=mysqli_fetch_row($res)){
					$addt1='';
					$addt2='';
					if($row[3]=='1'){
						$row[2]='[顶置] '.$row[2];
						$addt1='<b>';
						$addt2='</b>';
						}
					echo '<tr><td>',$row[0],'</td><td>',$row[1],'</td><td style="text-align:left">',$addt1.htmlspecialchars($row[2]).$addt2,'</td><td><a href="#"><i class="fa fa-pencil"></i></a></td></tr>';
				}
			?>
		</tbody>
	</table>
<?php
}else if($op=='list_experience_title'){
	$res=mysqli_query($con,"select title,experience from experience_titles order by experience");
?>
	<table class="table table-condensed table-striped">
      <caption>头衔</caption>
      <thead>
        <tr>
          <th>经验&nbsp;&ge;</th>
          <th>头衔</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
		<?php
			while($row=mysqli_fetch_row($res)){
				$t=htmlspecialchars($row[0]);
				echo <<<EOF
        <tr>
          <td>$row[1]</td>
          <td>$t</td>
          <td><a href="#"><i data-id="$row[1]" class="fa fa-remove"></i></a></td>
        </tr>
EOF;
			}
		?>
      </tbody>
    </table>
<?php
}else if($op=='list_level_experience'){
	$res=mysqli_query($con,"select level,experience from level_experience order by level");
	$le = array_fill(0, $level_max+1, 0);
	while($row=mysqli_fetch_row($res)){
		$le[$row[0]]=$row[1];
	}
?>

	<table class="table table-condensed table-striped">
	<caption>题目经验</caption>
	<thead>
	  <th>等级</th>
	  <th>经验</th>
	</thead>
	<tbody>
	<?php
		foreach ($le as $key => $value) {
			echo "<tr><td>$key</td><td><input type=\"text\" name=\"experience[]\" class=\"input-mini input-in-table center\" value=\"$value\"></td></tr>";
		}
	?>
	</tbody>
	</table>
<?php
}else if($op=='del_title'){
	if(!isset($_POST['id']))
		die('');
	$experience=intval($_POST['id']);
	mysqli_query($con,"DELETE FROM experience_titles where experience=$experience");
}else if($op=='add_experience_title'){
	if(!isset($_POST['experience'], $_POST['title']))
		die('');
	$e=intval($_POST['experience']);
	$t=mysqli_real_escape_string($con,$_POST['title']);
	mysqli_query($con,"INSERT INTO experience_titles VALUES ($e,'$t')");
}else if($op=='update_level_experience'){
	if(!isset($_POST['experience']))
		die('');
	$arr=$_POST['experience'];
	if(count($arr)!=$level_max+1)
		die('Wrong array length');
	foreach ($arr as $key => $value) {
		$key=intval($key);
		$value=intval($value);
		mysqli_query($con,"INSERT INTO level_experience VALUES ($key,$value) ON DUPLICATE KEY UPDATE experience=$value");
	}
}else if($op=="add_news"){
if(!isset($_POST['title'])||!isset($_POST['content'])||!isset($_POST['importance']))
		die('error');
	$importance=intval($_POST['importance']);
	$title=mysqli_real_escape_string($con,trim($_POST['title']));
	$content=isset($_POST['content']) ? mysqli_real_escape_string($con,str_replace("\n", "<br>", $_POST['content'])) : '';
	$row=mysqli_fetch_row(mysqli_query($con,"select max(news_id) from news"));
	$id=1;
	if($row[0])
		$id=$row[0]+1;
	if(mysqli_query($con,"insert into news(news_id,time,title,content,importance) values ($id,NOW(),'$title','$content','$importance')"))
		echo 'success';
	else
		echo 'error';
}else if($op=="get_news"){
	if(!isset($_POST['news_id']))
		die('error');
	$newsid=intval($_POST['news_id']);
	$res=mysqli_query($con,"select title,content,time,importance from news where news_id=$newsid");
    $row=mysqli_fetch_row($res);
    $row[1]=($res && ($row)) ? str_replace('<br>', "\n", $row[1]) : '';
    $arr=array('title'=>$row[0],'content'=>$row[1],'time'=>$row[2],'importance'=>$row[3]);
    echo json_encode($arr);
}else if($op=="edit_news"){
if(!isset($_POST['news_id'])||!isset($_POST['title'])||!isset($_POST['importance']))
		die('error');
	$news_id=intval($_POST['news_id']);
	$importance=intval($_POST['importance']);
	$title=mysqli_real_escape_string($con,trim($_POST['title']));
	$content=isset($_POST['content']) ? mysqli_real_escape_string($con,str_replace("\n", "<br>", $_POST['content'])) : '';
	if(mysqli_query($con,"update news set title='$title',content='$content',importance='$importance',time=NOW() where news_id=$news_id"))
		echo 'success';
	else
		echo 'error';
}else if($op=="add_priv"){
	isset($_POST['user_id']) ? $uid=mysqli_real_escape_string($con,trim($_POST['user_id'])) : die('');
	if($uid=='')
		die('');
	isset($_POST['right']) ? $right=$_POST['right'] : die('');
	if($right!='administrator'&&$right!='source_browser'&&$right!='insider')
		die('无效的权限...');
  $row=mysqli_fetch_row(mysqli_query($con,"select exists(select 1 from privilege where user_id='$uid' and rightstr='$right')"));
	if(!$row[0]) mysqli_query($con,"insert into privilege VALUES ('$uid','$right','N')");
}else if($op=="del_usr"){
	isset($_POST['user_id']) ? $uid=mysqli_real_escape_string($con,trim($_POST['user_id'])) : die('');
   if(!strcasecmp($uid,$_SESSION['user'])) die('');
	mysqli_query($con,"delete from users where user_id='$uid' and (accesstime IS NULL)");
}else if($op=="del_priv"){
	isset($_POST['user_id']) ? $uid=mysqli_real_escape_string($con,trim($_POST['user_id'])) : die('');
	isset($_POST['right']) ? $right=mysqli_real_escape_string($con,$_POST['right']) : die('');
	mysqli_query($con,"delete from privilege where user_id='$uid' and rightstr='$right'");
}else if($op=="del_news"){
	isset($_POST['news_id']) ? $news_id=intval($_POST['news_id']) : die('');
	if(mysqli_query($con,"delete from news where $news_id>0 and news_id=$news_id")){
		$row=mysqli_fetch_row(mysqli_query($con,"select max(news_id) from news"));
		$news_id++;
		for($news_id;$news_id<=$row[0];$news_id++){
			$res=mysqli_query($con,"update news set news_id='".($news_id-1)."'where news_id='".$news_id."'");
			if(!$res) die($news_id);
		}
		echo 'success';
    }
	else
		echo 'error';
}else if($op=="en_usr"){
	isset($_POST['user_id']) ? $uid=mysqli_real_escape_string($con,trim($_POST['user_id'])) : die('');
	mysqli_query($con,"update users set defunct='N' where user_id='$uid'");
}else if($op=="disable_usr"){
	isset($_POST['user_id']) ?  $uid=mysqli_real_escape_string($con,trim($_POST['user_id'])) : die('');
   if(!strcasecmp($uid,$_SESSION['user'])) die('');
	mysqli_query($con,"update users set defunct='Y' where user_id='$uid'");
}else if($op=='update_index'){
	$index_text=isset($_POST['text']) ? mysqli_real_escape_string($con,str_replace("\n", "<br>", $_POST['text'])) : '';
	if(mysqli_query($con,"insert into news (news_id,content,time) VALUES (0,'$index_text',NOW()) ON DUPLICATE KEY UPDATE content='$index_text', time=NOW()"))
		echo "success";
	else
		echo "fail";
}else if($op=="update_category"){
	$category=isset($_POST['content']) ? mysqli_real_escape_string($con,trim($_POST['content'])) : '';
	if(mysqli_query($con,"insert into user_notes (id,problem_id,tags,user_id,content,edit_time) VALUES (0,0,'','root','$category',NOW()) ON DUPLICATE KEY UPDATE content='$category', edit_time=NOW()")) 
		echo 'success';
	else
		echo 'fail';
}
?>
