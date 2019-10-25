<?php
/* Copyright (C) 2019 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require 'config.php';
dol_include_once('processrules/class/processrules.class.php');
dol_include_once('processrules/class/procedure.class.php');
dol_include_once('processrules/class/dictionaryproceduretype.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

if(empty($user->rights->processrules->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('processrules@processrules');


$massaction = GETPOST('massaction', 'alpha');
$confirmmassaction = GETPOST('confirmmassaction', 'alpha');
$toselect = GETPOST('toselect', 'array');

$object = new Procedure($db);

$hookmanager->initHooks(array('procedurelist'));

if ($object->isextrafieldmanaged)
{
    $extrafields = new ExtraFields($db);
    $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
}

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend')
{
    $massaction = '';
}

if (GETPOST('button_removefilter_x') == 'x')
{
	$search_product = '';
}

if (empty($reshook))
{
	// do action from GETPOST ...
}


/*
 * View
 */

llxHeader('', $langs->trans('procedureList'), '', '');

//$type = GETPOST('type');
//if (empty($user->rights->processrules->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$keys = array_keys($object->fields);
$fieldList = 't.'.implode(', t.', $keys);
if (!empty($object->isextrafieldmanaged))
{
    $keys = array_keys($extralabels);
	if(!empty($keys)) {
		$fieldList .= ', et.' . implode(', et.', $keys);
	}
}

$sql = 'SELECT '.$fieldList;

// Add fields from hooks
$parameters=array('sql' => $sql);
$reshook=$hookmanager->executeHooks('printFieldListSelect', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;

$sql.= ' FROM '.MAIN_DB_PREFIX.$object->table_element.' t ';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'processrules as p ON p.rowid = t.fk_processrules';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_procedure_type as ptype ON ptype.rowid = t.fk_procedure_type';

if (!empty($object->isextrafieldmanaged))
{
    $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.$object->table_element.'_extrafields et ON (et.fk_object = t.rowid)';
}

$sql.= ' WHERE 1=1';
$sql.= ' AND t.entity IN ('.getEntity('processRules', 1).')';
//if ($type == 'mine') $sql.= ' AND t.fk_user = '.$user->id;

// Add where from hooks
$parameters=array('sql' => $sql);
$reshook=$hookmanager->executeHooks('printFieldListWhere', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;

$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_procedure', 'GET');

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new Listview($db, 'procedure');
echo $r->render($sql, array(
	'view_type' => 'list' // default = [list], [raw], [chart]
    ,'allow-fields-select' => true
	,'limit'=>array(
		'nbLine' => $nbLine
	)
    ,'list' => array(
        'title' => $langs->trans('procedureList')
        ,'image' => 'title_generic.png'
        ,'picto_precedent' => '<'
        ,'picto_suivant' => '>'
        ,'noheader' => 0
        ,'messageNothing' => $langs->trans('NoprocessRules')
        ,'picto_search' => img_picto('', 'search.png', '', 0)
        ,'morehtmlrighttitle' => dolGetButtonTitle($langs->trans('Newprocedure'), '', 'fa fa-plus-circle', dol_buildpath('/processrules/procedure_card.php?action=create', 2))
        ,'massactions'=>array(
            //'yourmassactioncode'  => $langs->trans('YourMassActionLabel')
        )
    )
	,'subQuery' => array()
	,'link' => array()
	,'type' => array(
		'date_creation' => 'date' // [datetime], [hour], [money], [number], [integer]
		,'tms' => 'date'
	)
	,'search' => array(
		'date_creation' => array('search_type' => 'calendars', 'allow_is_null' => true)
		,'tms' => array('search_type' => 'calendars', 'allow_is_null' => false)
		,'ref' => array('search_type' => true, 'table' => 't', 'field' => 'ref')
		,'fk_processrules' => array('search_type' => true, 'table' => array('p'), 'field' => array('ref'))
		,'fk_procedure_type' => array('search_type' => true, 'table' => array('ptype', 'ptype'), 'field' => array('code', 'label'))
		,'label' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('label')) // input text de recherche sur plusieurs champs
		,'status' => array('search_type' => Procedure::$TStatus, 'to_translate' => true) // select html, la clé = le status de l'objet, 'to_translate' à true si nécessaire
	)
	,'translate' => array()
	,'hide' => array(
		'rowid' // important : rowid doit exister dans la query sql pour les checkbox de massaction
	)
	,'title'=>array(
		'ref' => $langs->trans('Ref.')
		,'label' => $langs->trans('Label')
		,'fk_processrules'=> $langs->trans('processRules')
		,'fk_procedure_type'=> $langs->trans('Type')
		,'date_creation' => $langs->trans('DateCre')
		,'status'	=> $langs->trans('Status')
		//,'tms' => $langs->trans('DateMaj')

	)
	,'eval'=>array(
		'ref' => '_getObjectNomUrl(\'@rowid@\', \'@val@\')'
		,'fk_processrules' => 'ProcessRules::getStaticNomUrl(\'@val@\')'
		,'fk_procedure_type' => 'DictionaryProcedureType::getStaticNomUrl(\'@val@\')'
		,'status' => 'Procedure::LibStatut(\'@val@\', 2)'
//		,'fk_user' => '_getUserNomUrl(@val@)' // Si on a un fk_user dans notre requête
	)
));

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

$formcore->end_form();

llxFooter('');
$db->close();

/**
 * TODO remove if unused
 */
function _getObjectNomUrl($id, $ref)
{
	global $db;

	$o = new Procedure($db);
	$res = $o->fetch($id, false, $ref);
	if ($res > 0)
	{
		return $o->getNomUrl(1);
	}

	return '';
}

/**
 * TODO remove if unused
 */
function _getUserNomUrl($fk_user)
{
	global $db;

	$u = new User($db);
	if ($u->fetch($fk_user) > 0)
	{
		return $u->getNomUrl(1);
	}

	return '';
}

