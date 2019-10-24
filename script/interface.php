<?php
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");


dol_include_once('/core/lib/functions.lib.php');

global $db;

$put = GETPOST('put');
$get = GETPOST('get');

switch ($put)
{
	case 'reorderProcedures':
		print json_encode(_reorderProcedures(GETPOST('items')));
		break;
}

function _reorderProcedures($items = array())
{
	global $db;

	$data['msg'] = '';
	$data['success'] = true;

	if(empty($items))
	{
		$data['msg'] = 'Nothing to reorder';
		return $data;
	}

	foreach ($items as $item)
	{

	}

	return $data;
}
