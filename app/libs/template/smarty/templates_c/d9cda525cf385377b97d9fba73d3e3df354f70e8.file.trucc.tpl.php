<?php /* Smarty version 3.1.22-dev/3, created on 2014-12-10 18:03:46
         compiled from "/Applications/MAMP/htdocs/Autres/api/src/project/home/views/public/trucc.tpl" */ ?>
<?php /*%%SmartyHeaderCode:34707988654887cf27a9f06_64482632%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd9cda525cf385377b97d9fba73d3e3df354f70e8' => 
    array (
      0 => '/Applications/MAMP/htdocs/Autres/api/src/project/home/views/public/trucc.tpl',
      1 => 1418231024,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '34707988654887cf27a9f06_64482632',
  'tpl_function' => 
  array (
  ),
  'type' => 'compiled',
  'variables' => 
  array (
    'message' => 0,
    'Info' => 0,
  ),
  'has_nocache_code' => false,
  'version' => '3.1.22-dev/3',
  'unifunc' => 'content_54887cf27faf54_82882417',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54887cf27faf54_82882417')) {function content_54887cf27faf54_82882417 ($_smarty_tpl) {
$_saved_type = $_smarty_tpl->properties['type'];
$_smarty_tpl->properties['type'] = $_smarty_tpl->caching ? 'cache' : 'compiled';?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '34707988654887cf27a9f06_64482632';
?>

 	<?php echo $_smarty_tpl->tpl_vars['message']->value;?>
 du projet <strong><?php echo $_smarty_tpl->tpl_vars['Info']->value['Project'];?>
</strong>, du controller <strong><?php echo $_smarty_tpl->tpl_vars['Info']->value['Controller'];?>
</strong> et de l'action <strong><?php echo $_smarty_tpl->tpl_vars['Info']->value['Action'];?>
</strong>.
 	<?php $_smarty_tpl->properties['type'] = $_saved_type;?>
<?php }
}
?>