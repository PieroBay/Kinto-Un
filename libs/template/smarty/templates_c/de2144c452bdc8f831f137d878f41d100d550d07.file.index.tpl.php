<?php /* Smarty version Smarty-3.1.18, created on 2014-10-07 16:58:43
         compiled from "/Applications/MAMP/htdocs/Framework/src/project/home/views/public/index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:917503395433feb4e30319-19909724%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'de2144c452bdc8f831f137d878f41d100d550d07' => 
    array (
      0 => '/Applications/MAMP/htdocs/Framework/src/project/home/views/public/index.tpl',
      1 => 1412693921,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '917503395433feb4e30319-19909724',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_5433feb4e7d745_66001224',
  'variables' => 
  array (
    'nom' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5433feb4e7d745_66001224')) {function content_5433feb4e7d745_66001224($_smarty_tpl) {?>
<a href="">actualiser</a><br/>
	<?php echo $_smarty_tpl->tpl_vars['nom']->value;?>

	<form method="post" action="">
		<input type="text" name="pseudo" placeholder="pseudo"/><br/>
		<input type="text" name="mail" placeholder="mail" /><br/>
		<textarea name="message" placeholder="mail"></textarea><br/>
		<input type="submit" value="ok" />
	</form>

	<hr>
<?php }} ?>
