<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       processrules_note.php
 *  \ingroup    processrules
 *  \brief      Card with notes on processRules
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include "../main.inc.php";
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

dol_include_once('/processrules/class/processrules.class.php');
dol_include_once('/processrules/lib/processrules.lib.php');

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Load translation files required by the page
$langs->loadLangs(array("processrules@processrules","companies"));

// Get parameters
$id			= GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');


$massaction = GETPOST('massaction', 'alpha');
$confirmmassaction = GETPOST('confirmmassaction', 'alpha');
$toselect = GETPOST('toselect', 'array');

// Initialize technical objects
$object=new ProcessRules($db);
if (!empty($d) || !empty($ref)) {
	$result = $object->fetch($id, 1, $ref);

	if ($result <= 0 || empty($object->id)) {
		print $langs->trans('NotFound');
		exit;
	}
}



$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->processrules->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('processrulesproducttab','globalcard'));     // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('processrules');

// Security check - Protection if external user
if ($user->societe_id > 0) access_forbidden();
if ($user->societe_id > 0) $socid = $user->societe_id;
$result = restrictedArea($user, 'processrules', $id);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || ! empty($ref)) $upload_dir = $conf->processrules->multidir_output[$object->entity] . "/" . $object->id;

// LOAD PRODUCTS EXTRAFIELDS
$productStatic = new Product($db);
$productExtrafields = new ExtraFields($db);
$productExtrafieldslabels = $productExtrafields->fetch_name_optionals_label($object->table_element);


/*
 * Actions
 */


if($action == 'addProduct'){
	$fk_product = GETPOST('fk_product', 'int');
	if(!empty($fk_product)){
		if($object->add_object_linked('product', intval($fk_product)) > 0)
		{
			setEventMessage($langs->trans('ProcessRulesLinkAdded'));
		}
		else{
			setEventMessage($langs->trans('ProcessRulesLinkAddError'), 'errors');
		}
	}
	else{
		setEventMessage($langs->trans('FormProductMissing'), 'warnings');
	}
}


if($massaction == 'massDelProductLink')
{
	if(!empty($toselect) && is_array($toselect))
	{
		$toselect = array_map('intval', $toselect);
		$countDeleted = 0;
		foreach ($toselect as $fk_product)
		{
			if($object->deleteObjectLinked(intval($fk_product), 'product') > 0)
			{
				$countDeleted++;
			}
			else{
				setEventMessage($langs->trans('ProcessRulesDeletedError', $fk_product), 'errors');
			}
		}

		if($countDeleted>0)
		{
			setEventMessage($langs->trans('ProcessRulesLinksDeleted', $countDeleted));
		}
	}
	else{
		setEventMessage($langs->trans('NoProductSelected'), 'errors');
	}
}




/*
 * View
 */

$form = new Form($db);

//$help_url='EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes';
$help_url='';
$arrayofjs = '';
$arrayofcss = array('processrules/css/module-style.css');
llxHeader('', $langs->trans('processRules'), $help_url, '', 0, 0, $arrayofjs, $arrayofcss);


if ($id > 0 || ! empty($ref))
{
	$object->fetch_thirdparty();

	$head = processrules_prepare_head($object);
	$picto = 'processrules@processrules';
	dol_fiche_head($head, 'products', $langs->trans("processRules"), -1, $picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' .dol_buildpath('/processrules/processrules_list.php', 1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref='<div class="refidno">';

	$morehtmlref.='</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	dol_fiche_end();


	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';

	/*
	 * PRODUCT ADD FORM
	 */
	print '<div class="form-add-contener" >';
	$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_add_product_to_processrule', 'POST');

	print '<h4>'.$langs->trans('LinkProcessRuleToAProduct').'</h4>';

	print '<input type="hidden" name="id" value="'.$object->id.'" />';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print $form->select_produits('', 'fk_product');
	print  '<button type="submit" name="action" value="addProduct" >'.$langs->trans('AddProduct').'</button>';
	print '</form>';
	print '</div>';



	/*
	 * LIST OF PRODUCT
	 */

	$keys = array(
		'rowid',
		'ref',
		'entity',
		'note_public',
		'note',
		'datec',
		'tms',
		'fk_user_author',
		'fk_user_modif',
		'import_key',
		'label',
		'tobuy',
		'tosell'
	);
	$fieldList = 't.'.implode(', t.', $keys);

	// PREPARE PRODUCTS EXTRAFIELS
	if (!empty($productExtrafieldslabels))
	{
		$keys = array_keys($productExtrafieldslabels);
		if(!empty($keys)) {
			$fieldList .= ', et.' . implode(', et.', $keys);
		}
	}


	$sql = 'SELECT ';
	$sql.= $fieldList;


// Add fields from hooks
	$parameters=array('sql' => $sql);
	$reshook=$hookmanager->executeHooks('printFieldListSelect', $parameters, $object);    // Note that $action and $object may have been modified by hook
	$sql.=$hookmanager->resPrint;

	$sql.= ' FROM '.MAIN_DB_PREFIX.'product t ';
	$sql.= ' JOIN '.MAIN_DB_PREFIX.'element_element elel ON (elel.targettype = \''.$db->escape($object->element).'\' AND elel.fk_target = '.intval($object->id).' AND elel.sourcetype = \'product\' AND  elel.fk_source = t.rowid)';

	$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product_extrafields et ON (et.fk_object = t.rowid)';

	$sql.= ' WHERE 1=1';
	$sql.= ' AND t.entity IN ('.getEntity('product', 1).')';


	// Add where from hooks
	$parameters=array('sql' => $sql);
	$reshook=$hookmanager->executeHooks('printFieldListWhere', $parameters, $object);    // Note that $action and $object may have been modified by hook
	$sql.=$hookmanager->resPrint;

	$formcorelist = new TFormCore($_SERVER['PHP_SELF'], 'form_list_processrule_product', 'POST');

	if(!empty($object->id)){
		print  '<input type="hidden" name="id" value="'.$object->id.'" />';
	}

	$listViewRenderName = 'processrule_product';
	$r = new Listview($db, $listViewRenderName);


	/*
	$form = new Form($db);
	$inputKey = 'fk_supplier';
	$selectSupplier = $form->select_company(GETPOST('Listview_'.$listViewRenderName.'_search_'.$inputKey,'int'), 'Listview_'.$listViewRenderName.'_search_'.$inputKey, '', 1, 0);
	$inputKey = 'fk_soc';
	$selectCustomer = $form->select_company(GETPOST('Listview_'.$listViewRenderName.'_search_'.$inputKey,'int'), 'Listview_'.$listViewRenderName.'_search_'.$inputKey, '', 1, 0);
	*/

	//$addNewUrl = dol_buildpath('/processrules/_card.php',1).'?action=create&backtopage='.urlencode($_SERVER["PHP_SELF"]);

	$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

	$productCardUrl = DOL_URL_ROOT.'/product/card.php?id=@rowid@';
	$productCardLink = '<a href="'.$productCardUrl.'" >@val@</a>';

	$param = array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'allow-fields-select' => true
	,'limit'=>array(
			'nbLine' => $nbLine
		)
	,'list' => array(
		'title' => $langs->trans('ProductsListLinkedToThisProcessrules')
		,'image' => 'title_products.png'
		//,'morehtmlrighttitle' => dolGetButtonTitle($langs->trans('AddNew'), '', 'fa fa-plus-circle', $addNewUrl, 'btnaddnew', $user->rights->processrules->write)
		,'massactions'=>array(
				'massDelProductLink'  => $langs->trans('MassDelProductLink')
			)
		)
	,'subQuery' => array()
	,'link' => array(
		'ref' => $productCardLink,
		'label' => $productCardLink
	)
	,'type' => array()
	,'search' => array(
		'ref' => array('search_type' => true, 'table' => 't', 'field' => 'ref')
		,'label' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('label')) // input text de recherche sur plusieurs champs
		//,'status' => array('search_type' => array(), 'to_translate' => true) // select html, la clé = le status de l'objet, 'to_translate' à true si nécessaire
	)
	,'translate' => array()
	,'hide' => array(
			'rowid' // important : rowid doit exister dans la query sql pour les checkbox de massaction
	)
	,'title'=>array(
		'ref' => $langs->trans('Ref.')
		,'label' => $langs->trans('Label')
		//,'status' => $langs->trans('Status')
	)
	,'eval'=>array(
//			'ref' => '_getObjectNomUrl(\'@rowid@\', \'@val@\')',
//			'status' => 'WebInstance::LibStatut(\'@val@\', 2)',
//			'fk_soc' => '_getSocUrl("@val@")',
//			'fk_supplier' => '_getSocUrl("@val@")',
//			'fk_processrules' => '_getProcessrulesNomUrl("@val@")',
//
//			'url' => '_outLink(\'@val@\')',
//			'url_admin' => '_outLink(\'@val@\')',
//			'instanceType' => 'labelPicto(\'@val@\', \'@picto@\')'
			//		,'fk_user' => '_getUserNomUrl(@val@)' // Si on a un fk_user dans notre requête
		)
	);

	if(!empty($object->id)){
		$param['list']['param_url'] = 'id='.$object->id;
	}



	echo $r->render($sql, $param);
	//print $r->sql;

	$parameters=array('sql'=>$sql);
	$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	print '</form>';

	print '</div>';

}

// End of page
llxFooter();
$db->close();
