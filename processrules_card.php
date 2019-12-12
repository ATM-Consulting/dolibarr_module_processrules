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
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('processrules/class/processrules.class.php');
dol_include_once('processrules/lib/processrules.lib.php');

if(empty($user->rights->processrules->read)) accessforbidden();
$permissiondellink = $user->rights->webhost->write;	// Used by the include of actions_dellink.inc.php

$langs->load('processrules@processrules');

// Get parameters
$optioncss = GETPOST("optioncss");
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'processrulescard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');



$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'processrulescard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');



$object = new ProcessRules($db);


// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.


$permissiontoread = $user->rights->processrules->processrules->read;
$permissiontoadd = $user->rights->processrules->processrules->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->processrules->processrules->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->processrules->processrules->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->processrules->processrules->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->processrules->multidir_output[isset($object->entity) ? $object->entity : 1];

$backurlforlist = dol_buildpath('/processrules/processrules_list.php', 1);

if (empty($backtopage) || ($cancel && empty($id))) {
	if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
		if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
		else $backtopage = dol_buildpath('/processrules/processrules_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
	}
}
$triggermodname = 'PROCESSRULES_PROCESSRULES_MODIFY'; // Name of trigger action code to execute when we modify record


$thisUrl = dol_buildpath('/processrules/processrules_card.php', 1).'?id='.$object->id;

$hookmanager->initHooks(array('processrulescard', 'globalcard'));


if ($object->isextrafieldmanaged)
{
    $extrafields = new ExtraFields($db);

    $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
    $search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');
}

// Initialize array of search criterias
//$search_all=trim(GETPOST("search_all",'alpha'));
//$search=array();
//foreach($object->fields as $key => $val)
//{
//    if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
//}

/*
 * Actions
 */

$parameters = array('id' => $id, 'ref' => $ref);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{

    if ($cancel)
    {
        if (! empty($backtopage))
        {
            header("Location: ".$backtopage);
            exit;
        }
        $action='';
    }

    // For object linked
    include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once

	// Action to build doc
	// $action must be defined
	// $id must be defined
	// $object must be defined and must have a method generateDocument().
	// $permissiontoadd must be defined
	// $upload_dir must be defined (example $conf->projet->dir_output . "/";)
	// $hidedetails, $hidedesc, $hideref and $moreparams may have been set or not.
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';


	$error = 0;
	switch ($action) {
		case 'add':
		case 'update':
			$object->setValues($_REQUEST); // Set standard attributes

            if ($object->isextrafieldmanaged)
            {
                $ret = $extrafields->setOptionalsFromPost($extralabels, $object);
                if ($ret < 0) $error++;
            }

			if ($error > 0)
			{
				$action = 'edit';
				break;
			}

			$res = $object->save($user);
            if ($res < 0)
            {
                setEventMessage($object->errors, 'errors');
                if (empty($object->id)) $action = 'create';
                else $action = 'edit';
            }
            else
            {
                header('Location: '.dol_buildpath('/processrules/processrules_card.php', 1).'?id='.$object->id);
                exit;
            }
        case 'update_extras':

            $object->oldcopy = dol_clone($object);

            // Fill array 'array_options' with data from update form
            $ret = $extrafields->setOptionalsFromPost($extralabels, $object, GETPOST('attribute', 'none'));
            if ($ret < 0) $error++;

            if (! $error)
            {
                $result = $object->insertExtraFields('PROCESSRULES_MODIFY');
                if ($result < 0)
                {
                    setEventMessages($object->error, $object->errors, 'errors');
                    $error++;
                }
            }

            if ($error) $action = 'edit_extras';
            else
            {
                header('Location: '.dol_buildpath('/processrules/processrules_card.php', 1).'?id='.$object->id);
                exit;
            }
            break;
		case 'confirm_clone':
			$object->cloneObject($user);

			header('Location: '.dol_buildpath('/processrules/processrules_card.php', 1).'?id='.$object->id);
			exit;

		case 'confirm_delete':
			if (!empty($user->rights->processrules->delete)) $object->delete($user);

			header('Location: '.dol_buildpath('/processrules/processrules_list.php', 1));
			exit;

		// link from llx_element_element
		case 'dellink':
			$object->deleteObjectLinked(null, '', null, '', GETPOST('dellinkid'));
			header('Location: '.dol_buildpath('/processrules/processrules_card.php', 1).'?id='.$object->id);
			exit;

		case 'confirm_enable':
			$object->setValid($user);
			header('Location: '.dol_buildpath('/processrules/processrules_card.php', 1).'?id='.$object->id);
			exit;

		case 'confirm_disable':
			$ret = $object->setDraft($user);
			header('Location: '.dol_buildpath('/processrules/processrules_card.php', 1).'?id='.$object->id);
			exit;
	}


	// Actions to send emails
	$triggersendname = 'PROCESSRULES_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_PROCESSRULES_TO';
	$trackid = 'processrules'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


/**
 * View
 */
$form = new Form($db);
$formfile = new FormFile($db);

$title=$langs->trans('processRules');
llxHeader('', $title);

if ($action == 'create')
{
    print load_fiche_titre($langs->trans('NewprocessRules'), '', 'processrules@processrules');

    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

    dol_fiche_head(array(), '');

    print '<table class="border centpercent">'."\n";

    // Common attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

    print '</table>'."\n";

    dol_fiche_end();

    print '<div class="center">';
    print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans('Create')).'">';
    print '&nbsp; ';
    print '<input type="'.($backtopage?"submit":"button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans('Cancel')).'"'.($backtopage?'':' onclick="javascript:history.go(-1)"').'>';	// Cancel for create does not post form if we don't know the backtopage
    print '</div>';

    print '</form>';
}
else
{
    if (empty($object->id))
    {
        $langs->load('errors');
        print $langs->trans('ErrorRecordNotFound');
    }
    else
    {
        if (!empty($object->id) && $action === 'edit')
        {
            print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            print '<input type="hidden" name="action" value="update">';
            print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
            print '<input type="hidden" name="id" value="'.$object->id.'">';

            $head = processrules_prepare_head($object);
            $picto = 'processrules@processrules';
            dol_fiche_head($head, 'card', $langs->trans('processRules'), 0, $picto);

            print '<table class="border centpercent">'."\n";

            // Common attributes
            include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

            // Other attributes
            include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

            print '</table>';

            dol_fiche_end();

            print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans('Save').'">';
            print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
            print '</div>';

            print '</form>';
        }
        elseif ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
        {
        	if (empty($optioncss))
			{
				$head = processrules_prepare_head($object);
				$picto = 'processrules@processrules';
				dol_fiche_head($head, 'card', $langs->trans('processRules'), -1, $picto);

				$formconfirm = getFormConfirmprocessRules($form, $object, $action);
				if (!empty($formconfirm)) print $formconfirm;


				$linkback = '<a href="' .dol_buildpath('/processrules/processrules_list.php', 1) . '?restore_lastsearch_values=1">' . $langs->trans('BackToList') . '</a>';

				$morehtmlref='<div class="refidno">';
				/*
				// Ref bis
				$morehtmlref.=$form->editfieldkey("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->processrules->write, 'string', '', 0, 1);
				$morehtmlref.=$form->editfieldval("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->processrules->write, 'string', '', null, null, '', 1);
				// Thirdparty
				$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $soc->getNomUrl(1);
				*/
				$morehtmlref.='</div>';


				$morehtmlstatus.=''; //$object->getLibStatut(2); // pas besoin fait doublon
				dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, '', 0, '', $morehtmlstatus);

				print '<div class="fichecenter">';

				print '<div class="fichehalfleft">'; // Auto close by commonfields_view.tpl.php
				print '<div class="underbanner clearboth"></div>';
				print '<table class="border tableforfield" width="100%">'."\n";

				// Common attributes
				//$keyforbreak='fieldkeytoswithonsecondcolumn';
				include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

				// Other attributes
				include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

				print '</table>';

				print '</div></div>'; // Fin fichehalfright & ficheaddleft
				print '</div>'; // Fin fichecenter

				print '<div class="clearboth"></div><br />';

			}

            print '<div class="fichecenter">';
            $object->fetch_lines();

			if (empty($optioncss))
			{
				$titleBtn = '';
				if ($object->status < 1 )
					$titleBtn = dolGetButtonTitle($langs->trans('Newprocedure'), '', 'fa fa-plus-circle', dol_buildpath('/processrules/procedure_card.php', 2).'?action=create&fk_processrules='.$object->id.'&backtopage='.urlencode($thisUrl));
				print load_fiche_titre($langs->trans('Procedures'), $titleBtn, 'title_generic.png');
			}
			elseif ($optioncss == 'print')
			{
				print "<div style='text-align: center'><h1>".$langs->trans("processRulesCard")." ".$object->label."</h1></div>";
			}

			print '<div id="ajaxResults" ></div>';
            print _displaySortableProcedures($object->lines, 'sortableLists', false, $object->status < 1 && empty($optioncss));

			print '<script src="'.dol_buildpath('processrules/js/jquery-sortable-lists.min.js',1).'" ></script>';
			print '<link rel="stylesheet" href="'.dol_buildpath('/processrules/css/sortable.css.php', 1).'">';
			print '</div>';// Fin fichecenter

			if ($object->status < 1 && empty($optioncss)) {
				?>

				<script type="text/javascript">
                    $(function () {
                        var options = {
                            insertZone: 10, // This property defines the distance from the left, which determines if item will be inserted outside(before/after) or inside of another item.
                            placeholderClass: 'pr-sortable-list__item--placeholder',
                            hintClass: 'pr-sortable-list__item--hint',
                            onChange: function (cEl) {

                                $("#ajaxResults").html("");

                                $.ajax({
                                    url: "<?php echo dol_buildpath('/processrules/script/interface.php', 1) ?>",
                                    method: "POST",
                                    data: {
                                        put: 'reorderProcedures'
                                        , id: '<?php echo $object->id; ?>'
                                        , items: $('#sortableLists').sortableListsToHierarchy()
                                    },
                                    dataType: "json",

                                    // La fonction à apeller si la requête aboutie
                                    success: function (data) {
                                        // Loading data
                                        console.log(data);
                                        if (data.success == true) {
                                            // ok case
                                            $("#ajaxResults").html('<span class="badge badge-success">' + data.msg + '</span>');
                                        } else {
                                            // error case
                                            $("#ajaxResults").html('<span class="badge badge-danger">' + data.errorMsg + '</span>');
                                        }
                                    },
                                    // La fonction à appeler si la requête n\'a pas abouti
                                    error: function (jqXHR, textStatus) {
                                        console.log("Request failed: " + textStatus);
                                    }
                                });
                            },
                            complete: function (cEl) {
                                // nothing foor now
                            },
                            isAllowed: function (cEl, hint, target) {
                                if (cEl.data('parent') == "meth_<?php echo $object->id; ?>" && target.data('parent') == undefined) return true;
                                else if (cEl.data('parent').substring(0, 4) == "proc"
                                    && target.data('parent') != undefined
                                    && target.data('parent').substring(0, 4) == "meth"
                                    && target.data('id') == cEl.data("parent").substring(5)) return true;
                                else {
                                    console.log(cEl.data('parent'), target.data('parent'));
                                    target.find('#sortableListsHintWrapper').hide();
                                    target.find('#sortableListsHint').hide();
                                    return false;
                                }
                            },
                            opener: {
                                active: true,
                                as: 'html',  // if as is not set plugin uses background image
                                close: '<i class="fa fa-minus c3"></i>',  // or \'fa-minus c3\',  // or \'./imgs/Remove2.png\',
                                open: '<i class="fa fa-plus"></i>',  // or \'fa-plus\',  // or\'./imgs/Add2.png\',
                                openerCss: {
                                    'display': 'inline-block',
                                    'float': 'left',
                                    'margin-left': '-35px',
                                    'margin-right': '5px',
                                    'font-size': '1.1em'
                                }
                            },
                            ignoreClass: 'clickable',

                            insertZonePlus: true,
                        };

                        $('#sortableLists').sortableLists(options);

                    });
				</script>

				<?php
			}

			if (empty($optioncss))
			{

				print '<div class="tabsAction">'."\n";
				$parameters=array();
				$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
				if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

				if (empty($reshook))
				{
					// Modify
					if (!empty($user->rights->processrules->write))
					{
						if ($object->status < 1)
						{
							// Modify
							print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("processRulesModify").'</a></div>'."\n";
						}

						// Clone
						print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=clone">'.$langs->trans("processRulesClone").'</a></div>'."\n";

						if ($object->status == ProcessRules::STATUS_DRAFT)
						{
							print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=enable">'.$langs->trans("Activate").'</a></div>';
						}
						else
						{
							print '<div class="inline-block divButAction"><a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?action=disable&amp;id='.$object->id.'">'.$langs->trans("Disable").'</a></div>';
						}
					}
					else
					{
						// Modify
						print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("processRulesModify").'</a></div>'."\n";
						// Clone
						print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("processRulesClone").'</a></div>'."\n";

						if ($object->status == ProcessRules::STATUS_DRAFT)
						{
							print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("Activate").'</a></div>';
						}
						else
						{
							print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("Disable").'</a></div>';
						}
					}

					if (!empty($user->rights->processrules->delete))
					{
						print '<div class="inline-block divButAction"><a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete">'.$langs->trans("processRulesDelete").'</a></div>'."\n";
					}
					else
					{
						print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("processRulesDelete").'</a></div>'."\n";
					}
				}
				print '</div>'."\n";

				print '<div class="fichecenter"><div class="fichehalfleft">';
				print '<a name="builddoc"></a>'; // ancre

				// Documents
				$objref = dol_sanitizeFileName($object->ref);
				$relativepath = $objref . '/' . $objref . '.pdf';
				$filedir = $conf->processrules->dir_output . '/' . $objref;
				$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
				$genallowed = 1;$user->rights->processrules->read;	// If you can read, you can build the PDF to read content
				$delallowed = $user->rights->processrules->create;	// If you can create/edit, you can remove a file on card
				print $formfile->showdocuments('processrules:processrules', $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);


				$linktoelem = $form->showLinkToObjectBlock($object, null, array($object->element));
				$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

				print '</div><div class="fichehalfright"><div class="ficheaddleft">';

				// List of actions on element
				include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
				$formactions = new FormActions($db);
				$somethingshown = $formactions->showactions($object, $object->element, $socid, 1);

				print '</div></div></div>';
			}

            dol_fiche_end(-1);
        }
    }
}


llxFooter();
$db->close();
