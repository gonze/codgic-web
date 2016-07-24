<?php
require 'inc/ojsettings.php';
if(!isset($_GET['key']))
	die('Invalid key.');
$key=$_GET['key'];
if(strlen($key)!=32 || preg_match('/\W/',$key))
	die('Invalid key.');
$inTitle='评测窗口';
$Title=$inTitle .' - '. $oj_name;
?>
<!DOCTYPE html>
<html>
  <?php require 'head.php'; ?>

  <body>
    <div class="container text-center" style="margin-top:20px">
    	<div class="row">
        <div class="col-xs-12">
          <h1 class="text-center">评测结果</h1>
		  <hr>
          <p class="help-block">评测机正在评测你提交的代码，请不要手动关闭或是刷新该页面。<br>
          评测结束后，该页面会自动刷新。</p>
          <div class="row">
            <div class="col-xs-12">
              <div id="ele_queue" class="alert alert-info text-center"><strong><i class="fa fa-spinner fa-lg fa-spin"></i> 正在等待，请坐和放宽...</strong></div>
              <div id="ele_judge" class="collapse alert alert-success text-center"><strong><i class="fa fa-spinner fa-lg fa-spin"></i> 正在评测，请坐和放宽...</strong></div>
            </div>
          </div>
          <div class="collapse panel panel-default" id="ele_table">
			<div class="panel-body" id="ele_body">
            <table class="table table-condensed table-bordered result_table">
              <thead>
                <tr><th>节点</th><th>结果</th><th>时间</th><th>内存</th><th>分数</th></tr>
              </thead>
              <tbody style="color:white" id="ele_tbody"></tbody>
            </table>
			</div>
          </div>
          <div class="collapse" id="ele_finish" style="margin-top:15px">
			<p><b><span id="judge_result" style="font-size:16px"></span></b></p>
            <ul class="pager"><li class="previous"><a class="pager-pre-link shortcut-hint" title="Alt+P" id="btn_back" href="#"><i class="fa fa-angle-left"></i> 题目页面</a></li>
            <li class="next"><a class="pager-next-link shortcut-hint" title="Alt+R" href="record.php">提交记录 <i class="fa fa-angle-right"></i></a></li></ul>
          </div>
        </div>
    	</div>
      <hr>
      <footer>
        <p>&copy; <?php echo"{$year} {$oj_copy}";?></p>
      </footer>
    </div>
    <script type="text/javascript"> 
	  $.ajaxSetup({cache:false});
      res_tyep={"0":"Correct","2":"Time Out","3":"MLE","4":"Wrong Answer","5":"Runtime Error","99":"Validator Error"};
      function disp_CE(str){
        $("#ele_judge").hide();
        $('#ele_queue').hide();
		$('#ele_body').html('<h3>Compile Error</h3><div class="problem-sample" style="text-align:left;margin-top:10px">'+htmlEncode(str)+'</div>');
        $('#ele_table').show();
        $("#ele_finish").show();
      }
      function disp_SE(){
        $("#ele_judge").hide();
        $('#ele_queue').hide();
        $('#ele_table').removeClass().html('<div class="alert alert-danger text-center"><p>对不起，评测系统出了些问题，你的代码没有被记录。<br>请联系info@cwoj.tk。</p></div>').show();
        $("#ele_finish").show();
      }
      function htmlEncode(str) {
        var s = "";
        if (str.length == 0) return "";
        s = str.replace(/&/g, "&amp;");
        s = s.replace(/ /g, "&nbsp;");
        s = s.replace(/</g, "&lt;");
        s = s.replace(/>/g, "&gt;");  
        s = s.replace(/\'/g, "&#39;");
        s = s.replace(/\"/g, "&quot;");
        return s;
      }
      last_i=0;
      function load_progress(){
        var url='<?php echo "proxy.php?url=query_$key";?>';
        // alert(url);
        $.getJSON(url,function(obj){
          if(obj.state=="invalid"){
            $("#ele_judge").hide();
            $("#ele_queue").hide();
			$("#judge_result").html('该页面已过期，请到提交记录页查询得分...');
            $("#ele_finish").show();
          }else{
            var timeout=2500;
            if(obj.detail.length>1){
              var content="",record,i;
              if(obj.detail[0][0]==7)
                return disp_CE(obj.detail[0][3]);
              if(obj.detail[0][0]==100)
                return disp_SE(obj.detail[0][3]);
              for(i=0;obj.detail[i].length>0;i++){
                var record=obj.detail[i];
                content+='<tr id="line'+i+'" title="'+htmlEncode(record[3])+'" ';
                switch(record[0]){
                  case 0:
                  content+='class="res-ac">';break;
                  case 2:
                  case 3:
                  content+='class="res-le">';break;
                  case 4:
                  content+='class="res-wa">';break;
                  case 5:
                  content+='class="res-re">';break;
                  case 99:
                  content+='class="res-se">';break;
                  default:
                  content+='>';break;
                }
                content+='<td>'+(i+1)+'</td><td>'+res_tyep[record[0]]+'</td><td>'+record[1]+' ms</td><td>'+record[2]+' KB</td><td>'+record[4]+'</td></tr>';
              }
              //$('body>.tooltip').remove();
              $('#ele_tbody').empty().html(content);
              if(i-1-last_i==0)
                timeout=3600;
              else if(i-1-last_i>1)
                timeout=1000;
              last_i=i;
              for(i;i>=0;--i)
                $('#line'+i).tooltip({});
              $('#ele_queue').hide();
              $('#ele_judge').show();
              $('#ele_table').show();
            }
            if(obj.state=='finish'){
              $('#ele_queue').hide();
              $("#ele_judge").hide();
			  $("#judge_result").html('评测完成！现在你可以返回题目页面或是提交记录页面了...');
              $("#ele_finish").show();
              return;
            }
            window.setTimeout(load_progress,timeout);
          }
        });
      }
      $(document).ready(function(){
		window.setTimeout(load_progress,500);
        $('#btn_back').click(function(){
          history.go(-1);
          return false;
        });
      }).keydown(function(E){
        if(E.altKey && !E.metaKey){
          var key=E.keyCode;
          if(key==80)
            history.go(-1);
          else if(key==82)
            location.href="record.php";
          return false;
        }
      });
    </script>
  </body>
</html>
