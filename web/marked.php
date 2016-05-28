<?php
require 'inc/ojsettings.php';
require ('inc/checklogin.php');

if(!isset($_SESSION['user']))
	$info = 'Not logged in.';
else{
	require('inc/database.php');
	$user_id=$_SESSION['user'];
	$result=mysqli_query($con,"SELECT saved_problem.problem_id,title,savetime,problem_flag_to_level(has_tex) from saved_problem inner join problem using (problem_id) where user_id='$user_id' order by savetime desc");
}
$inTitle='收藏';
$Title=$inTitle .' - '. $oj_name;
?>
<!DOCTYPE html>
<html>
	<?php require('head.php'); ?>  

	<body>
		<?php require('page_header.php'); ?>
		<div class="container-fluid">
			<?php 
			if(isset($info)){
				echo '<div class="center">',$info,'</div>';
			}else{
			?>
			<div class="row-fluid">
				<div class="span8 offset2">
					<table class="table table-hover table-condensed table-bordered">
						<thead><tr>
							<th style="width:6%">题号</th>
							<th>题目</th>
							<th style="width:8%">等级</th>
							<th style="width:25%">收藏时间</th>
							<th style="width:10%">删除</th>
						</tr></thead>
						<tbody id="marked_list">
						<?php
						while($row=mysqli_fetch_row($result)){
						?>
							<tr>
								<td><?php echo $row[0] ?></td>
						        <td style="text-align:left"><a href="problempage.php?problem_id=<?php echo $row[0]?>" ><?php echo $row[1] ?></a></td>
								<td><?php echo $row[3] ?></td>
								<td><?php echo $row[2] ?></td>
								<td><i data-pid="<?php echo $row[0] ?>" style="cursor:pointer;" class="text-error fa fa-remove"></i></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
			</div>  
			<?php } ?>
			<hr>
			<footer>
				<p>&copy; <?php echo"{$year} {$oj_copy}";?></p>
			</footer>
		</div><!--/.container-->
		<script src="/assets/js/common.js"></script>
		<script type="text/javascript"> 
			$(document).ready(function(){
				$('#ret_url').val("marked.php");
				$('#marked_list').click(function(E){
					var $target = $(E.target);
					if($target.is('i')){
						var pid = $target.attr('data-pid');
						$.get('ajax_saveproblem.php?prob='+pid+'&op=rm_saved',function(result){
							if(/__ok__/.test(result)){
								$target.parents('tr').remove();
							}
						});
					}
				});
			}); 
		</script>
	</body>
</html>
