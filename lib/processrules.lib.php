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
    $head[$h][1] = $langs->trans("ExtraFields");
    $head[$h][2] = 'extrafields';
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
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	processRules	$object		Object company shown
 * @return 	array				Array of tabs
 */
function processrules_prepare_head(processRules $object)
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

	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@processrules:/processrules/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@processrules:/processrules/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'processrules');

	return $head;
}

/**
 * @param Form      $form       Form object
 * @param processRules  $object     processRules object
 * @param string    $action     Triggered action
 * @return string
 */
function getFormConfirmprocessRules($form, $object, $action)
{
    global $langs, $user;

    $formconfirm = '';

    if ($action === 'valid' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmValidateprocessRulesBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmValidateprocessRulesTitle'), $body, 'confirm_validate', '', 0, 1);
    }
    elseif ($action === 'accept' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmAcceptprocessRulesBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmAcceptprocessRulesTitle'), $body, 'confirm_accept', '', 0, 1);
    }
    elseif ($action === 'refuse' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmRefuseprocessRulesBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmRefuseprocessRulesTitle'), $body, 'confirm_refuse', '', 0, 1);
    }
    elseif ($action === 'reopen' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmReopenprocessRulesBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmReopenprocessRulesTitle'), $body, 'confirm_refuse', '', 0, 1);
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
    elseif ($action === 'cancel' && !empty($user->rights->processrules->write))
    {
        $body = $langs->trans('ConfirmCancelprocessRulesBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCancelprocessRulesTitle'), $body, 'confirm_cancel', '', 0, 1);
    }

    return $formconfirm;
}
