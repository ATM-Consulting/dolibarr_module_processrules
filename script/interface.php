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
		print json_encode(_reorderProcedures(GETPOST('items'), GETPOST('id', 'int')));
		break;

	case 'reorderDocumentFiles':
		print json_encode(_reorderDocumentFiles(GETPOST('items')));
		break;
}

function _reorderProcedures($items = array(), $id = 0)
{
	global $db;

	$data['msg'] = '';
	$data['success'] = true;

	if(empty($items))
	{
		$data['msg'] = 'Nothing to reorder';
		return $data;
	}

	$db->begin();

	foreach ($items as $item)
	{
		$item['id'] = str_replace("item_", "", $item['id']);

		$sql = "UPDATE ".MAIN_DB_PREFIX."procedure SET rang=".$item['order']." WHERE rowid=".$item['id']." AND fk_processrules=".$id;
		$resql = $db->query($sql);
		if (!$resql)
		{
			$data['success'] = false;
			$data['msg'] = "Error updating rank of item ".$item['id'];
			break;
		}
	}

	if ($data['success']) $db->commit();
	else $db->rollback();
	return $data;
}
