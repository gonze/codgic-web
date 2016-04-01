<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="renderer" content="webkit">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php header("Content-Type: text/html; charset=utf-8");?>
  <link rel="shortcut icon" href="assets/res/favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="/assets/css/normalize.min.css" type="text/css" />
  <title><?php echo $Title?></title>
  <script src="/assets/js/twemoji.min.js"></script>
  <script src="/assets/js/jquery.min.js"></script> 
  <?php 
  $year=date('Y');
  require 'inc/ojsettings.php';
  require_once 'inc/preferences.php';
  if(isset($_SESSION['pref']))
	  $pref=unserialize($_SESSION['pref']);
  else
	  $pref=new preferences();
  if($pref->hidehotkey=='on')
	  echo "<script>window.hidehotkey=true;</script>";
  if($pref->autonight=='on'){
	$hour = date('H',time());
	if($hour>$daystart && $hour<$nightstart)
		$pref->night='off';
	else
		$pref->night='on';
	};
	if($pref->night=='on') {
		$loginimg='/assets/res/loginbg_dark.png';
		$css1='/assets/css/cerulean_dark.min.css';
		$css2='/assets/css/docs_dark.css?v=12';
		$well_class='#212121';
		$nwell_class='#404040';
		$nav_class='navbar-inverse';
     	$button_class='btn-inverse';}
	else {
		$loginimg='/assets/res/loginbg.png';
		$css1='/assets/css/cerulean.min.css';
		$css2='/assets/css/docs.css?v=12';
		$well_class='#fff';
		$nwell_class='#f5f5f5';
		$nav_class='';
	    $button_class='btn-primary';
		};
  ?>
  <link href="<?php echo $css1?>" rel="stylesheet" type="text/css">
  <link href="<?php echo $css2?>" rel="stylesheet" type="text/css">
  <link href="/assets/css/bootstrap-responsive.min.css" rel="stylesheet">
  <link href="/assets/css/font-awesome.min.css" rel="stylesheet">
  <link href="/assets/js/google-code-prettify/prettify.css" rel="stylesheet">
  <!--[if lt IE 8]>
  <script type="text/javascript">
  window.location = "fuckie.php"; 
  </script>
  <![endif]-->
  <!--[if lt IE 9]>
    <link rel="stylesheet" href="/assets/css/font-awesome-ie7.min.css">
    <script src="/assets/js/html5.js"></script>
	<script src="/assets/js/respond.js"></script>
	<script type="text/javascript" src="/assets/js/jquery.placeholder.js">
      $(function() {
      $('input, textarea').placeholder();
      });
    </script>
  <![endif]-->
  <script type="text/javascript">
  function checkUpdate() {
    window.addEventListener('load', function(e) {
        window.applicationCache.addEventListener('updateready', function(e) {
            if (window.applicationCache.status === window.applicationCache.UPDATEREADY) {
                   window.applicationCache.swapCache();
                    window.location.reload();
            } else {
                // Manifest didn't changed. do Nothing.
            }
        }, false);
    }, false);
}
  checkUpdate();
  </script>
</head>