<?php
require 'inc/ojsettings.php';
require ('inc/checklogin.php');

if(isset($_GET['start_id']))
  $query_id=intval($_GET['start_id']);
else
  $query_id=2100000000;

if(isset($_GET['problem_id'])){
  $cond_prob='and problem_id='.intval($_GET['problem_id']);
  $query_prob=substr($cond_prob, 4);
}else{
  $query_prob=$cond_prob='';
}
require('inc/database.php');
$subquery="select thread_id from message where thread_id<$query_id $cond_prob order by thread_id desc limit 50";
$res=mysqli_query($con,"select min(thread_id) from ($subquery) as tmptab");
if(!$res)
  die('Wrong Argument.');
$row=mysqli_fetch_row($res);
$range=$row[0];

function get_pre_link($top)
{
  require ('inc/database.php');
  global $cond_prob;
  $res=mysqli_query($con,"select max(thread_id) from (select thread_id from message where thread_id>=$top $cond_prob order by thread_id limit 50) as tmptab");
  $row=mysqli_fetch_row($res);
  if($row[0])
    $pre=$row[0]+1;
  else
    $pre=2100000000;
  return $pre;
}

$inTitle='讨论';
$Title=$inTitle .' - '. $oj_name;
?>
<!DOCTYPE html>
<html>
  <?php require('head.php'); ?>
  <body>
    <script type="text/x-mathjax-config">
    MathJax.Hub.Config({
      skipStartupTypeset:true
    });
    </script>
    <?php require('inc/mathjax_head.php');?>

    <?php require('page_header.php'); ?>
    <div class="replypanel hide" id="replypanel">
      <div class="well well-small margin-0" style="background-color:<?php echo $well_class?>">
        <h4 style="text-align:center;margin-bottom:10px;">新建讨论</h4>
        <form class="form-horizontal" method="post" action="ajax_message.php" id="form_submit">
          <fieldset>
            <input type="text" style="display:none" id="msg_op" name="op" value="msg_create" readonly="true">
            <div class="control-group">
              <label class="control-label" for="msg_input">标题</label>
              <div class="controls">
                <input type="text" class="input-xxlarge" id="msg_input" name="message" placeholder="请输入消息标题...">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="detail_input">内容</label>
              <div class="controls">
                <textarea class="input-xxlarge" id="detail_input" rows="7" name="detail" placeholder="请输入消息内容..."></textarea>
              </div>
            </div>
            <div id="PreviewPopover" class="popover left" >
              <div class="arrow"></div>
              <div class="popover-inner">
                <h3 class="popover-title">预览<a class="close" style="line-height: inherit;">×</a></h3>
                <div class="popover-content">
                  <pre><div id="preview_content"></div></pre>
                </div>
              </div>
            </div>
            <div style="float:left">
              <span id="post_preview" class="btn btn-info">预览</span>
            </div>
            <div style="float:right">
              <button type="submit" id="post_submit" style="margin-left:20px" class="btn btn-primary shortcut-hint" title="Alt+S">发表</button>
              <button id="cancel_input" class="btn">取消</button>
            </div>
            <div class="center text-error"><strong id="post_status"></strong></div>
          </fieldset>
          <input type="hidden" name="message_id" id="msgid_input">
          <?php if(isset($_GET['problem_id'])){
            echo '<input type="hidden" name="problem_id" value="',$_GET['problem_id'],'">';
          }?>
        </form>
        <div class="resize-ico" id="resize"></div>   
      </div>
    </div>
    <div class="alert hide center alert-popup" id="alert_nothing">本条消息内容为空...</div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span12" id="board">
		    <a href="#" title="Alt+N" class="btn <?php echo $button_class?> shortcut-hint" id="new_msg"><i class="fa fa-file-text-o"></i> 新建讨论...</a>
		  <?php
            $top=$query_id;
            if($range){
              $res=mysqli_query($con,"select title,depth,user_id,message_id,in_date,thread_id,problem_id,ASCII(content) from message where thread_id<$query_id and thread_id>=$range $cond_prob order by thread_id desc,orderNum");
              $deep=-1;
              $top=0;
              $cnt=0;
              while($row=mysqli_fetch_row($res)){
                if($row[1]>$deep){
                  if($deep>-1)
                    echo '<ul class="unstyled msg_group">';
                }else{
                  echo '</li>';
                  while($deep>$row[1]){
                    $deep--;
                    echo '</ul></li>';
                  }
                  if($row[1]==0)
                    echo '</ul>';
                }
                $deep=$row[1];
                if($row[5]>$top)
                  $top=$row[5];
                if($deep==0)
                  echo '<hr><ul class="unstyled">';
                echo '<li class="msg_item">';
                if((++$cnt)&1)
                  echo "<div class=\"msg msg_odd\" style=\"background-color:{$nwell_class}\">";
                else
                  echo "<div class=\"msg msg_even\" style=\"background-color:{$nwell_class}\">";
                echo '<div class="msg_container"><strong>',$row[2],'</strong> ',$row[4];
                if($row[3]==$row[5] && $deep>0)
                  echo '&nbsp;<span class="label label-warning">最新消息</span>';
                if($deep==0 && $row[6])
                    echo '&nbsp;&nbsp;<a class="prob_link" href="problempage.php?problem_id=',$row[6],'">题目#',$row[6],'</a>';
               echo ' <button onclick="open_replypanel(',$row[3],')" class="btn btn-mini"><i class="fa fa-fw fa-reply"></i> 回复</button>';
                if($row[2]==$_SESSION['user']) echo ' <button onclick="open_editpanel(',$row[3],')" class="btn btn-mini"><i class="fa fa-fw fa-pencil"></i> 编辑</button>';
                if($row[7])
                  echo '<p class="msg_content msg_detailed">';
                else
                  echo '<p class="msg_content">';
                echo '<a class="msg_link" href="#" id="msg',$row[3],'">',htmlspecialchars($row[0]),'</a>';
                echo '</p></div></div>';
              }
              echo '</li>';
              while($deep>0){
                $deep--;
                echo '</ul></li>';
              }
              echo '</ul>';
              $top++;
            }else
              echo '<h4 style="text-align: center;">这页暂时没有讨论</h4>';
          ?> 
        </div> 
      </div>
      <div class="row">
        <ul class="pager">
          <li>
            <a class="pager-pre-link shortcut-hint" title="Alt+A" href="board.php?<?php echo $query_prob,'&amp;start_id=',get_pre_link($top) ?>" id="btn-pre"> <i class="fa fa-angle-left"></i> 较新的</a>
          </li>
          <li>
            <a class="pager-next-link shortcut-hint" title="Alt+D" href="<?php if($range) echo 'board.php?',$query_prob,'&amp;start_id=',$range; ?>#" id="btn-next">较旧的 <i class="fa fa-angle-right"></i></a>
          </li>
        </ul>
      </div> 
      
      <hr>
      <footer>
        <p>&copy; <?php echo"{$year} {$oj_copy}";?></p>
      </footer>

    </div>
    <script src="/assets/js/bbcode.js"></script>
    <script src="/assets/js/common.js"></script>
    <script type="text/javascript"> 
      function dealwithlinks($jqobj)
        {
          $jqobj.find('a').each(function(){
            var Href = this.getAttribute("href",2);
            Href=Href.replace(/^([ \t\n\r]*javascript:)+/i,'');
            if(!(/(ht|f)tps?:\/\//i.test(Href)))
              Href = "http://"+Href;
            this.href=Href;
          });
        }
      function open_replypanel(msg_id){
          <?php if(isset($_SESSION['user'])){?>
          var title = ((msg_id=='0')?'新建消息':'新建回复: #'+msg_id);
	$('#msg_op').val('msg_create');
	$('#msgid_input').val(msg_id);
	$('#replypanel h4').html(title);
 	$('#post_status').html('');
	$('#PreviewPopover').hide();
	$('#msg_input').val('');
	$('#detail_input').val('');
	$('#replypanel').fadeIn(300);
	$('#msg_input').focus();
          <?php }else{echo 'alert("请先登录！");';}?>
          return false;
        }
      function open_editpanel(msg_id){
          <?php if(isset($_SESSION['user'])){?>
          var title = '编辑消息';
          $('#msg_op').val('msg_edit');
          $('#post_status').html('');
          $('#msg_input').val('');
          $('#detail_input').val('');
          $.ajax({
                  type:"POST",
                  url:"ajax_message.php",
                  data:{"op":'get_message', "message_id":msg_id},
                  success:function(data){
                      $('#msg_input').val($('#msg'+msg_id).html());
                      $('#detail_input').val(data);
                   }
              });
          $('#msgid_input').val(msg_id);
          $('#replypanel h4').html(title);
          $('#replypanel').fadeIn(300);
	$('#PreviewPopover').hide();
          $('#msg_input').focus();
          <?php }else{echo 'alert("请先登录！");';}?>
          return false;
        }
      $(document).ready(function(){
        $('#nav_bbs').parent().addClass('active');
        $('#ret_url').val('board.php?<?php echo $query_prob,"&start_id=",$query_id?>');

        $('#board').click(function(E){
          if(! $(E.target).is("a.msg_link"))
            return;
          var ID=E.target.id+'_detail';
          var node=document.getElementById(ID);
          var p=$(E.target).parent();
          if(node){
            $(node).remove();
            p.removeClass("expanded");
          }else{
            if(p.hasClass("msg_detailed")){
              p.addClass("expanded");
              p.after('<pre id="'+ID+'"><div id="'+ID+'_div"></div></pre>');
              $.ajax({
                  type:"POST",
                  url:"ajax_message.php",
                  data:{"op":'get_message'," message_id":E.target.id.substring(3)},
                  success:function(data){
                      dealwithlinks( $('#'+ID+'_div').html(parseBBCode(data)) );
                MathJax.Hub.Queue(["Typeset",MathJax.Hub,(ID+'_div')]);
                   }
              });
            }else{
              var tmp=$('#alert_nothing').show();
              setTimeout(function(){tmp.fadeOut(400);},1000);
            }
          }
          return false;
        });
        var detail_ele=document.getElementById('detail_input');
        var minW=260,minH=100;
        
        $('#new_msg').click(function(){open_replypanel('0')});
        reg_hotkey(78,function(){$('#new_msg').click()}); //Alt+N

        $('#replypanel form').submit(function(){
          var msg=$.trim($('#msg_input').val());
          if(msg.length==0){
            $('#post_status').html('消息不可为空！');
            return false;
          }
          if(msg.length>150){
            $('#post_status').html('消息太长了！');
            return false;
          }
          post_submit.setAttribute("disabled",true);
          $('#post_status').html('正在发表...');
		  $.ajax({
            type:"POST",
            url:"ajax_message.php",
            data:$('#form_submit').serialize(),
            success:function(msg){
              if(/success/.test(msg))
                location.reload();
              else{
                post_submit.removeAttribute("disabled");
                $('#post_status').html('错误:'+msg);
               }
            }
          });
          return false;
        });
        reg_hotkey(83,function(){$('#replypanel form').submit()}); //Alt+S

        $('#cancel_input').click(function(){
          $('#replypanel').fadeOut(300);
          return false;
        });
        $('#replypanel').keyup(function(E){
          E.which==27 && $('#replypanel').fadeOut(300);
        });
        $('#post_preview').click(function(){
          var data=$('#detail_input').val();
          data=$('<div/>').text(data).html();
          dealwithlinks( $('#preview_content').html(parseBBCode(data)));
          $('#PreviewPopover').fadeIn(300);
          MathJax.Hub.Queue(["Typeset",MathJax.Hub,('preview_content')]);
        });
        $('#PreviewPopover a.close').click(function(){
          $('#PreviewPopover').fadeOut(300);
        });
        function move_handle(E){
          var w=origX-E.clientX+origW;
          var h=E.clientY-origY+origH;
          if(w>=minW){
            $(detail_ele).width(w);
            $('#msg_input').width(w);
          }
          if(h>=minH)
            $(detail_ele).height(h);
        }
        $('body').mouseup(function(){
          $('body').unbind('mousemove');
        });
        $('#resize').mousedown(function(E){
          origX=parseInt(E.clientX);
          origY=parseInt(E.clientY);
          origW=$(detail_ele).width();
          origH=$(detail_ele).height();
          $('body').unbind('mousemove').mousemove(move_handle);
          return false;
        });
        $('body').mouseleave(function(){$('body').unbind('mousemove');});
      }); 
    </script>
  </body>
</html>
