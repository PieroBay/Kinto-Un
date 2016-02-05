<?php 
	define('ROOT', str_replace('app/config/init/index.php', '', $_SERVER['SCRIPT_FILENAME']));

	require_once(ROOT.'vendor/autoload.php');
	$configFile = spyc_load_file(ROOT.'app/config/Config.yml');
	if(!empty($configFile['configuration']['database_name'])){
		header('Location: ../../../');
		exit();
	}

	if(!empty($_POST)){
		$_POST['dev']    = (isset($_POST['dev']))?true:false;
		$_POST['port']   = (empty($_POST['port']))?null:$_POST['port'];
		$_POST['expire'] = (empty($_POST['expire']))?30:$_POST['expire'];
		$_POST['token']  = (empty($_POST['token']))?uniqid():$_POST['token'];
		
		$yml = array(
			"configuration"=>array(
				"database_host"     => $_POST['host'],
				"database_port"     => $_POST['port'],
				"database_name"     => $_POST['name'],
				"database_user"     => $_POST['user'],
				"database_password" => $_POST['password'],
				"local"             => $_POST['local'],
				"template"          => $_POST['template'],
				"development"       => $_POST['dev'],
				"folder"            => $_POST['folder'],
			),
			"security"=>array(
				"token"  			=> $_POST['token'],
				"expire" 			=> $_POST['expire'],
			),
			"login"=>array(
				"login"      		=> "name",
				"password"   		=> "password",
				"activation" 		=> false,
				"remember"   		=> false,
				"session"    		=> "id|name",
			)
		);
        $dump = Spyc::YAMLDump($yml);
        file_put_contents(ROOT.'/app/config/Config.yml', $dump);
	}
?>
<!DOCTYPE html>
<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="css/oolong-form.css" />
  <link rel="stylesheet" href="https://cdn.rawgit.com/PieroBay/Oolong/master/css/oolong-grid.css" />
  <link rel="stylesheet" href="https://cdn.rawgit.com/PieroBay/Oolong/master/css/oolong-tools.css" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css" />
  <title>Kinto|Un Init</title>
 </head>
 <body>
 	<header class="oo-1">
 		<div class="row oo-70 oo-center">
 			<div id="logo"><img src="images/kintoun.png" alt="" /></div>
 			<h1 class="oo-100">Initialisation</h1>
 		</div>
 	</header>

	<section>
		<article class="oo-70 oo-center">
			<div class="row">
			 	<form method="post" class="oo-70 oo-center" action="">
					<span class="field-flat-anim oo-100">
						<input type="text" class="field-flat-1" id="lab_ho" name="host" value="localhost" required/>
						<label class="field-flat-anim-L" for="lab_ho">Database Host</label>
					</span>
					<span class="field-flat-anim oo-100">
						<input type="number" class="field-flat-1" id="lab_po" name="port" />
						<label class="field-flat-anim-L" for="lab_po">Database Port <span>(Leave empty if no port)</span></label>
					</span>
					<span class="field-flat-anim oo-100">
						<input type="text" class="field-flat-1" id="lab_na" name="name" value="kinto" required/>
						<label class="field-flat-anim-L" for="lab_na">Database Name</label>
					</span>
					<span class="field-flat-anim oo-100">
						<input type="text" class="field-flat-1" id="lab_us" name="user" value="root" required/>
						<label class="field-flat-anim-L" for="lab_us">Database User</label>
					</span>
					<span class="field-flat-anim oo-100">
						<input type="text" class="field-flat-1" id="lab_pa" name="password" value="root"/>
						<label class="field-flat-anim-L" for="lab_pa">Database Password</label>
					</span>

					<div class="li_select act oo-100">
						<div class="li_selected">Lang Local</div>
						<input type="hidden" name="local" class="li_field" value="fr" />
						<div class="li_list ac">
							<ul>
								<li data-value="fr">Fr</li>
								<li data-value="en">En</li>
								<li data-value="nl">Nl</li>
								<li data-value="de">De</li>
								<li data-value="es">Es</li>
								<li data-value="it">It</li>
							</ul>
						</div>
					</div>

					<div class="li_select act oo-100">
						<div class="li_selected">Template</div>
						<input type="hidden" name="template" class="li_field" value="Twig" />
						<div class="li_list ac">
							<ul>
								<li data-value="Twig">Twig</li>
								<li data-value="Smarty">Smarty</li>
								<li data-value="none">PHP</li>
							</ul>
						</div>
					</div>

					<label for="switch" class="oo-40" style="margin:15px 0;">Développement: <i class="material-icons oo-s-hidden oo-t-hidden help tooltip-b" title="Si coché, Les erreurs php seront visible">help</i></label>
					<label for="switch" class="switch oo-20">
						<input type="checkbox" name="dev" value="1" id="switch" checked/>
						<span class="round"></span>
						<span class="rectRound"></span>
					</label><div class="clear"></div>

					<span class="field-flat-anim oo-100">
						<input type="text" class="field-flat-1" id="lab_fo" name="folder"/>
						<label class="field-flat-anim-L" for="lab_fo">Folder <span>(If framework not at root of domain else leave empty)</span></label>
					</span>

					<span class="field-flat-anim oo-100">
						<input type="text" class="field-flat-1" id="lab_to" name="token"/>
						<label class="field-flat-anim-L" for="lab_to">Token <span>(Optional. Random string for more secure form)</span></label>
					</span>

					<span class="field-flat-anim oo-100">
						<input type="number" class="field-flat-1" id="lab_ex" name="expire"/>
						<label class="field-flat-anim-L" for="lab_ex">Token expire time <span>(Optional. In minute)</span></label>
					</span>
					
					<div class="oo-20 oo-s-100"><button type="submit" class="submit-flat-3 rippleBtn">Submit</button></div>
			 	</form>
		 	</div>
		</article>
	</section>


  <script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
  <script type="text/javascript" src="https://cdn.rawgit.com/PieroBay/Oolong/master/js/oolong-tools.min.js"></script>
 </body>
</html>