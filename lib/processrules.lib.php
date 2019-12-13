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

/**
 *	\file		lib/processrules.lib.php
 *	\ingroup	processrules
 *	\brief		This file is an example module library
 *				Put some comments here
 */

/**
 * @return array
 */
function processrulesAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load('processrules@processrules');

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/processrules/admin/processrules_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;

    $head[$h][0] = dol_buildpath("/processrules/admin/processrules_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ProcessrulesExtraFields");
    $head[$h][2] = 'processrulesExtrafields';
    $h++;

	$head[$h][0] = dol_buildpath("/processrules/admin/procedure_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ProcedureExtraFields");
	$head[$h][2] = 'procedureExtrafields';
	$h++;

	$head[$h][0] = dol_buildpath("/processrules/admin/processstep_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ProcessstepExtraFields");
	$head[$h][2] = 'processstepExtrafields';
	$h++;

    $head[$h][0] = dol_buildpath("/processrules/admin/processrules_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@processrules:/processrules/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@processrules:/processrules/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'processrules');

    return $head;
}

/**
 * Return array of tabs to used on pages for processRules cards.
 *
 * @param 	ProcessRules	$object		Object company shown
 * @return 	array				Array of tabs
 */
function processrules_prepare_head(ProcessRules $object)
{
    global $langs, $conf;
    $h = 0;
    $head = array();
    $head[$h][0] = dol_buildpath('/processrules/processrules_card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("processRulesCard");
    $head[$h][2] = 'card';
    $h++;

	$nbNote = 0;
	if (!empty($object->note_private)) $nbNote++;
	if (!empty($object->note_public)) $nbNote++;
    $head[$h][0] = dol_buildpath('/processrules/processrules_note.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Notes");
	if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
    $head[$h][2] = 'note';
    $h++;

	$head[$h][0] = dol_buildpath('/processrules/processrules_document.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Documents");
	$head[$h][2] = 'document';
	$h++;

	$head[$h][0] = dol_buildpath('/processrules/processrules_product_tab.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Products");
	$head[$h][2] = 'products';
	$h++;

	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@processrules:/processrules/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@processrules:/processrules/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'processrules');

	return $head;
}

/**
 * Return array of tabs to used on pages for procedure cards.
 *
 * @param 	Procedure	$object		Object company shown
 * @return 	array				Array of tabs
 */
function procedure_prepare_head(Procedure $object)
{
	global $langs, $conf;
	$h = 0;
	$head = array();
	$head[$h][0] = dol_buildpath('/processrules/procedure_card.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("procedureCard");
	$head[$h][2] = 'card';
	$h++;

	$nbNote = 0;
	if (!empty($object->note_private)) $nbNote++;
	if (!empty($object->note_public)) $nbNote++;
	$head[$h][0] = dol_buildpath('/processrules/procedure_note.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Notes");
	if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
	$head[$h][2] = 'note';
	$h++;

	$head[$h][0] = dol_buildpath('/processrules/procedure_document.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Documents");
	$head[$h][2] = 'document';
	$h++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'procedure');

	return $head;
}

/**
 * @param ProcessStep $object
 * @return array
 */
function processstep_prepare_head(ProcessStep $object)
{
	global $langs, $conf;
	$h = 0;
	$head = array();
	$head[$h][0] = dol_buildpath('/processrules/processstep_card.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("ProcessStepCard");
	$head[$h][2] = 'card';
	$h++;

	$nbNote = 0;
	if (!empty($object->note_private)) $nbNote++;
	if (!empty($object->note_public)) $nbNote++;
	$head[$h][0] = dol_buildpath('/processrules/processstep_note.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Notes");
	if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
	$head[$h][2] = 'note';
	$h++;

	$head[$h][0] = dol_buildpath('/processrules/processstep_document.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Documents");
	$head[$h][2] = 'document';
	$h++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'procedure');

	return $head;
}



/**
 * @param Form      $form       Form object
 * @param ProcessRules  $object     processRules object
 * @param string    $action     Triggered action
 * @return string
 */
function getFormConfirmprocessRules($form, $object, $action)
{
    global $langs, $user;

    $formconfirm = '';

    if ($action === 'enable' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmEnableprocessRulesBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmEnableprocessRulesTitle'), $body, 'confirm_enable', '', 0, 1);
    }
        elseif ($action === 'disable' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmDisableprocessRulesBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmDisableprocessRulesTitle'), $body, 'confirm_disable', '', 0, 1);
    }
    elseif ($action === 'delete' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmDeleteprocessRulesBody');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmDeleteprocessRulesTitle'), $body, 'confirm_delete', '', 0, 1);
    }
    elseif ($action === 'clone' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmCloneprocessRulesBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCloneprocessRulesTitle'), $body, 'confirm_clone', '', 0, 1);
    }

    return $formconfirm;
}

/**
 * @param Form      $form       Form object
 * @param Procedure  $object    procedure object
 * @param string    $action     Triggered action
 * @return string
 */
function getFormConfirmprocedure($form, $object, $action)
{
    global $langs, $user;

    $formconfirm = '';

    if ($action === 'enable' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmEnableprocedureBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmEnableprocessRulesTitle'), $body, 'confirm_enable', '', 0, 1);
    }
        elseif ($action === 'disable' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmDisableprocedureBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmDeleteprocessRulesTitle'), $body, 'confirm_disable', '', 0, 1);
    }
    elseif ($action === 'delete' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmDeleteprocedureBody');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCloneprocessRulesTitle'), $body, 'confirm_delete', '', 0, 1);
    }
    elseif ($action === 'clone' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmCloneprocedureBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCloneprocedureTitle'), $body, 'confirm_clone', '', 0, 1);
    }

    return $formconfirm;
}

/**
 * @param Form      $form       Form object
 * @param Procedure  $object    procedure object
 * @param string    $action     Triggered action
 * @return string
 */
function getFormConfirmProcessStep($form, $object, $action)
{
    global $langs, $user;

    $formconfirm = '';

    if ($action === 'enable' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmEnableprocessStepBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmEnableprocessRulesTitle'), $body, 'confirm_enable', '', 0, 1);
    }
        elseif ($action === 'disable' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmDisableprocessStepBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmDeleteprocessRulesTitle'), $body, 'confirm_disable', '', 0, 1);
    }
    elseif ($action === 'delete' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmDeleteprocessStepBody');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCloneprocessRulesTitle'), $body, 'confirm_delete', '', 0, 1);
    }
    elseif ($action === 'clone' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmCloneprocessStepBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCloneprocedureTitle'), $body, 'confirm_clone', '', 0, 1);
    }

    return $formconfirm;
}

function _displaySortableProcedures($Tab, $htmlId='', $open = true, $editable = true){
	global $langs;

	if(!empty($Tab) && is_array($Tab))
	{
		$out = '<ul id="'.$htmlId.'" class="pr-sortable-list" >';
		/** @var Procedure $procedure */
		foreach ($Tab as $procedure)
		{
			$class = '';
			if($open){
				$class.= 'sortableListsClosed';
			}
			else $class.= 'sortableListsOpen';

			$out.= '<li id="item_'.$procedure->id.'" class="pr-sortable-list__item '.$class.'" ';
			$out.= ' data-id="'.$procedure->id.'" ';
			$out.= ' data-parent="meth_'.$procedure->fk_processrules.'"';
			$out.= '>';
			$out.= '<div class="pr-sortable-list__item__title  move">';
			$out.= '<div class="pr-sortable-list__item__title__flex">';

			$out.= '<div class="pr-sortable-list__item__title__col -label" >';
			$out.=  $procedure->getNomUrl(2);
			$out.= '</div>';

			$out.= '<div class="pr-sortable-list__item__title__col -action clickable" >';

			$backtopage = dol_buildpath('/processrules/processrules_card.php', 2).'?id='.$procedure->fk_processrules;

			if ($editable)
			{
				$out.= '<a href="'.dol_buildpath('/processrules/procedure_card.php', 1).'?id='.$procedure->id.'&backtopage='.urlencode($backtopage).'" class="classfortooltip pr-sortable-list__item__title__button clickable -view-btn"  title="' . $langs->trans("Showprocedure") . '" data-id="'.$procedure->id.'">';
				$out.= '<i class="fa fa-eye clickable"></i>';
				$out.= '</a>';

				$out.= '<a href="'.dol_buildpath('/processrules/procedure_card.php', 1).'?id='.$procedure->id.'&action=edit&backtopage='.urlencode($backtopage).'" class="classfortooltip pr-sortable-list__item__title__button clickable -edit-btn"  title="' . $langs->trans("Edit") . '" data-id="'.$procedure->id.'">';
				$out.= '<i class="fa fa-pencil clickable"></i>';
				$out.= '</a>';
			}

//			$deleteUrl = $_SERVER ['PHP_SELF'].'?sesslevel_remove=1&amp;id='. $procedure->id.'&amp;action=sessionlevel_update&amp;sesslevel_remove=1';
//
//			$out.= '<a href="'.$deleteUrl.'" class="classfortooltip pr-sortable-list__item__title__button clickable -delete-btn"  title="' . $langs->trans("Delete") . '"  data-id="'.$procedure->id.'">';
//			$out.= '<i class="fa fa-trash clickable"></i>';
//			$out.= '</a>';
			$out.= '</div>';

			$out.= '</div>';



			$out.= '<div class="pr-sortable-list__item__desc" >';
			$out.= $procedure->description;
			$out.= '</div>';

			$out.= '</div>';
			$procedure->fetch_lines();
			$out.= _displaySortableSteps($procedure->lines, 'sortableProcedures', $open, $backtopage, $editable); // pour afficher les étapes des procédures dans la card
			$out.= '</li>';
		}
		$out.= '</ul>';
		return $out;
	}
	else return '';
}

function _displaySortableSteps($Tab, $htmlClass = '', $open = true, $backtopage = '', $editable = true)
{
	global $langs;

	if(!empty($Tab) && is_array($Tab))
	{
		$out = '<ul class="pr-sortable-list steps '.$htmlClass.'" >';
		foreach ($Tab as $step)
		{
			$class = '';
			if($open){
				$class.= 'sortableListsClosed';
			}
			else $class.= 'sortableListsOpen';

			$out.= '<li id="item_'.$step->id.'" class="pr-sortable-list__item '.$class.'" ';
			$out.= ' data-id="'.$step->id.'" ';
			$out.= ' data-ref="'.$step->ref.'"';
			$out.= ' data-title="'.dol_escape_htmltag($step->label).'" ';
			$out.= ' data-parent="proc_'.$step->fk_procedure.'"';
			$out.= '>';
			$out.= '<div class="pr-sortable-list__item__title  move">';
			$out.= '<div class="pr-sortable-list__item__title__flex">';

			$out.= '<div class="pr-sortable-list__item__title__col"  style="flex:1">';
			$out.= dol_htmlentities($step->ref) . ' - ' . dol_htmlentities($step->label);
			$out.= '</div>';

			$out.= '<div class="pr-sortable-list__item__title__col"  style="flex:3">';
			$out.= $step->description;
			$out.= '</div>';

			$out.= '<div class="pr-sortable-list__item__title__col -action clickable"  style="flex:1">';

			if ($editable)
			{
				$out.= '<a href="'.dol_buildpath('/processrules/processstep_card.php', 1).'?id='.$step->id.(!empty($backtopage) ? '&backtopage='.urlencode($backtopage) : '').'" class="classfortooltip pr-sortable-list__item__title__button clickable -view-btn"  title="' . $langs->trans("ShowProcessStep") . '" data-id="'.$step->id.'">';
				$out.= '<i class="fa fa-eye clickable"></i>';
				$out.= '</a>';

				$out.= '<a href="'.dol_buildpath('/processrules/processstep_card.php', 1).'?id='.$step->id.'&action=edit'.(!empty($backtopage) ? '&backtopage='.urlencode($backtopage) : '').'" class="classfortooltip pr-sortable-list__item__title__button clickable -edit-btn"  title="' . $langs->trans("Edit") . '" data-id="'.$step->id.'">';
				$out.= '<i class="fa fa-pencil clickable"></i>';
				$out.= '</a>';
			}

//			$deleteUrl = $_SERVER ['PHP_SELF'].'?sesslevel_remove=1&amp;id='. $step->id.'&amp;action=sessionlevel_update&amp;sesslevel_remove=1';
//
//			$out.= '<a href="'.$deleteUrl.'" class="classfortooltip pr-sortable-list__item__title__button clickable -delete-btn"  title="' . $langs->trans("Delete") . '"  data-id="'.$step->id.'">';
//			$out.= '<i class="fa fa-trash clickable"></i>';
//			$out.= '</a>';
			$out.= '</div>';

			$out.= '</div>';
			$out.= '</div>';
			$TImage = $step->fetch_images();
			$out.= _displaySortableStepsImages($TImage, 'sortableimages', $open); // pour afficher les images des étapes dans la card
			$out.= '</li>';
		}
		$out.= '</ul>';
		return $out;
	}
	else return '';
}


function _displaySortableStepsImages($Tab, $htmlClass = '', $open = true, $backtopage = '')
{
	global $langs, $conf;

	if(!empty($Tab) && is_array($Tab))
	{
		$out = '<div class="'.$htmlClass.' clickable" >';
		$out.= '<div class="pr-sortable-list__item__title  move">';
		$out.= '<div class="pr-sortable-list__item__title__flex">';
		foreach ($Tab as $img)
		{
//			$class = '';
//			if($open){
//				$class.= 'sortableListsClosed';
//			}
//			else $class.= 'sortableListsOpen';

			$out.= '<div id="item_'.$img->id.'" class="pr-sortable-list__item" ';
			$out.= ' data-id="'.$img->id.'" ';
			$out.= ' data-path="'.$img->filepath.'"';
			$out.= ' data-filename="'.dol_escape_htmltag($img->filename).'" ';
			$out.= ' data-parent="step_'.$img->fk_step.'"';
			$out.= '>';
//			$out.= '<div class="pr-sortable-list__item__title  move">';
//			$out.= '<div class="pr-sortable-list__item__title__flex">';

//			$out.= '<div class="pr-sortable-list__item__title__col clickable" >';
//			$out.= dol_htmlentities($img->filepath) . ' - ' . dol_htmlentities($img->filename);

			$file = urlencode('processstep/'.$img->fk_step.'/'.$img->filename);
			$thumb = urlencode('processstep/'.$img->fk_step.'/thumbs/'.substr($img->filename, 0, strrpos($img->filename,'.')).'_mini'.substr($img->filename, strrpos($img->filename,'.')));
			$doclink = dol_buildpath('document.php', 1).'?modulepart=processrules&attachment=0&file='.$file.'&entity='.$conf->entity;
			$viewlink = dol_buildpath('viewimage.php', 1).'?modulepart=processrules&file='.$thumb.'&entity='.$conf->entity;

			$out.= '<a href="'.$doclink.'" class="pr-sortable-list__item__images documentpreview clickable" target="_blank" mime="image/png"><img class="clickable" src="'.(empty($conf->global->PROCESSRULES_DISPLAY_WIDE_IMG) ? $viewlink : $doclink).'" title=""></a>';
//			$out.= '</div>';

//			$out.= '</div>';
//			$out.= '</div>';

			$out.= '</div>';
		}
		$out.= '</div>';
		$out.= '</div>';
		$out.= '</div>';
		return $out;
	}
	else return '';
}
