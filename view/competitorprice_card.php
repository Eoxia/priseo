<?php
/* Copyright (C) 2022      Florian HENRY <floria.henry@scopen.fr>
 * Copyright (C) 2022-2023 EOXIA         <dev@eoxia.fr>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *   	\file       view/competitorprice/competitorprice_card.php
 *		\ingroup    priseo
 *		\brief      Page to create/edit/view competitorprice
 */

// Load Priseo environment
if (file_exists('../priseo.main.inc.php')) {
    require_once __DIR__ . '/../priseo.main.inc.php';
} elseif (file_exists('../../priseo.main.inc.php')) {
    require_once __DIR__ . '/../../priseo.main.inc.php';
} else {
    die('Include of priseo main fails');
}

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';

require_once __DIR__ . '/../class/competitorprice.class.php';
require_once __DIR__ . '/../core/modules/priseo/competitorprice/mod_competitorprice_standard.php';

// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user;

// Load translation files required by the page
$langs->loadLangs(['priseo@priseo', 'other']);

// Get parameters
$id                  = GETPOST('id', 'int');
$rowid               = GETPOST('rowid', 'int');
$ref                 = GETPOST('ref', 'alpha');
$action              = GETPOST('action', 'aZ09');
$confirm             = GETPOST('confirm', 'alpha');
$cancel              = GETPOST('cancel', 'aZ09');
$contextpage         = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'competitorpricecard'; // To manage different context of search
$backtopage          = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid              = GETPOST('lineid', 'int');

// parameters for pagination
$limit     = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page      = (GETPOST('page', 'int') ? GETPOST('page', 'int') : 0);
if (empty($page) || $page == -1) {
	$page = 0;
}     // If $page is not defined, or '' or -1
$offset   = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Initialize technical objects
$competitorPrice       = new CompetitorPrice($db);
$object                = new Product($db);
$extrafields           = new ExtraFields($db);
$refCompetitorPriceMod = new $conf->global->PRISEO_COMPETITORPRICE_ADDON($db);

$hookmanager->initHooks(['competitorpricecard', 'globalcard']); // Note that conf->hooks_modules contains array

if (!empty($rowid)) {
	$resultFetch = $competitorPrice->fetch($rowid);
	if ($resultFetch < 0) {
		setEventMessages($competitorPrice->error, $competitorPrice->errors, 'errors');
	}
}

// Default sort order (if not yet defined by previous GETPOST)
if (!$sortfield) {
    $sortfield = 't.competitor_date';
}
if (!$sortorder) {
    $sortorder = 'DESC';
}

// Definition of array of fields for columns
$arrayfields = [];
foreach ($competitorPrice->fields as $key => $val) {
	// If $val['visible']==0, then we never show the field
	if (!empty($val['visible'])) {
		$visible = (int)dol_eval($val['visible'], 1);
		$arrayfields[$key] = [
			'label'    => $val['label'],
			'checked'  => (($visible < 0) ? 0 : 1),
			'enabled'  => ($visible != 3 && dol_eval($val['enabled'], 1)),
			'position' => $val['position'],
			'help'     => isset($val['help']) ? $val['help'] : ''
        ];
	}
}

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST('search_all', 'alpha');
$search = [];
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_' . $key, 'alpha')) {
		$search[$key] = GETPOST('search_' . $key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be included, not include_once.

// There is several ways to check permission.
$permissiontoread   = $user->rights->priseo->competitorprice->read;
$permissiontoadd    = $user->rights->priseo->competitorprice->write; // Used by the included of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->priseo->competitorprice->delete;

$upload_dir = $conf->priseo->multidir_output[isset($object->entity) ? $object->entity : 1] . '/competitorprice';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->priseo->enabled) || !$permissiontoread) {
    accessforbidden();
}

/*
 * Actions
 */

$parameters = [];
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/priseo/view/competitorprice_card.php', 1).'?id='.$id;

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/priseo/view/competitorprice_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'PRISEO_COMPETITORPRICE_MODIFY'; // Name of trigger action code to execute when we modify record

	//Tricks to use common template
	$product = $object;
	$object = $competitorPrice;
	if (empty($object->id)) {
	$object->fk_product = $product->id;
	}

    // Action to add record
    if ($action == 'add' && $permissiontoadd) {
        $object->ref      = $refCompetitorPriceMod->getNextValue($object);
        $competitorPrices = $object->fetchAll('', '', 0, 0, ['customsql' => 't.fk_soc = ' . GETPOST('fk_soc') . ' AND t.fk_product = ' . $object->fk_product]);
        if (is_array($competitorPrices) && !empty($competitorPrices)) {
            foreach ($competitorPrices as $competitorPrice) {
                $competitorPrice->setValueFrom('status', 0, '', '', 'int', '', $user);
            }
        }
    }

    $noback = 1;
	require_once DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

    if ($action == 'confirm_clone' && $permissiontoadd) {
        setEventMessages('', $langs->trans('RecordCreatedSuccessfully'));
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id); // Open record of new object
        exit;
    }

	//Tricks to use common template
	$object = $product;
}


/*
 * View
 */

// Initialize view objects

$form = new Form($db);

$title    = $langs->trans('CompetitorPrice');
$help_url = 'FR:Module_Priseo';

llxHeader('', $title, $help_url);

if ($object->id > 0) {
    $titre = $langs->trans('CardProduct' . $object->type);
    $picto = ($object->type == Product::TYPE_SERVICE ? 'service' : 'product');

    $head = product_prepare_head($object);
    print dol_get_fiche_head($head, 'priseo', $titre, -1, $picto);

    $formconfirm = '';
    if ($action == 'deleteProductCompetitorPrice' && $permissiontodelete) { // Always output when not jmobile nor js
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id . '&rowid=' . $competitorPrice->id, $langs->trans('DeleteProductCompetitorPrice'), $langs->trans('ConfirmDeleteProductCompetitorPrice'), 'confirm_delete', 'question', 'yes', 1);
    }

    $parameters = ['formConfirm' => $formconfirm];
    $reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
    if (empty($reshook)) {
        $formconfirm .= $hookmanager->resPrint;
    } elseif ($reshook > 0) {
        $formconfirm = $hookmanager->resPrint;
    }

    // Print form confirm
    print $formconfirm;

    $linkback = '<a href="' . DOL_URL_ROOT . '/product/list.php?restore_lastsearch_values=1">' . $langs->trans('BackToList') . '</a>';
	$object->next_prev_filter = ' fk_product_type = ' . $object->type;

	$shownav = 1;
	if ($user->socid && !in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) {
		$shownav = 0;
	}

	dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');

	print '<div class="underbanner clearboth"></div>';

	print dol_get_fiche_end();

	// Actions buttons
	print '<div class="tabsAction">';
	if ($action != 'add_competitor_price' && $action != 'update_competitor_price') {
		$parameters = [];
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if (empty($reshook)) {
			if ($permissiontoadd) {
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=create_competitor_price&token=' . newToken() . '">';
				print $langs->trans('AddCompetitorPrice') . '</a>';
			}
		}
	}
	print '</div>';

	if ($action == 'create_competitor_price') {
		//Tricks to use common template
		$product = $object;
		$object = $competitorPrice;

		if (empty($permissiontoadd)) {
			accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
			exit;
		}

		print load_fiche_titre($langs->trans('NewCompetitorPrice'), '', 'object_' . $object->picto);

		print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="action" value="add">';
		print '<input type="hidden" name="id" value="' . $product->id . '">';
		if ($backtopage) {
			print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
		}
		if ($backtopageforcancel) {
			print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
		}

		print dol_get_fiche_head();

		// Set some default values
		//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

        $competitor_date = dol_getdate(dol_now());

        $_POST['competitor_dateyear']  = $competitor_date['year'];
        $_POST['competitor_datemonth'] = $competitor_date['mon'];
        $_POST['competitor_dateday']   = $competitor_date['mday'];
        $_POST['competitor_datehour']  = $competitor_date['hours'];
        $_POST['competitor_datemin']   = $competitor_date['minutes'];

		print '<table class="border centpercent tableforfieldcreate">';

		// Common attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

		// Other attributes
		//include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

		print '</table>';

		print dol_get_fiche_end();

		print $form->buttonsSaveCancel('Create');

		print '</form>';

		$object = $product;
	} elseif ($action == 'update_competitor_price') {
		//Tricks to use common template
		$product = $object;
		$object = $competitorPrice;

		if (empty($permissiontoadd)) {
			accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
			exit;
		}

		print load_fiche_titre($langs->trans('ModifyCompetitorPrice'), '', 'object_' . $object->picto);

		print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="' . $product->id . '">';
		print '<input type="hidden" name="rowid" value="' . $object->id . '">';
		if ($backtopage) {
			print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
		}
		if ($backtopageforcancel) {
			print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
		}

		print dol_get_fiche_head();

		// Set some default values
		//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

		print '<table class="border centpercent tableforfieldedit">';

		// Common attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

		// Other attributes
		//include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

		print '</table>';

		print dol_get_fiche_end();

		print $form->buttonsSaveCancel();

		print '</form>';

		$object = $product;
	}

	if ($permissiontoread) {
		$param = '';
		if (!empty($contextpage) && $contextpage != $_SERVER['PHP_SELF']) {
			$param .= '&contextpage=' . urlencode($contextpage);
		}
		if ($limit > 0 && $limit != $conf->liste_limit) {
			$param .= '&limit=' . urlencode($limit);
		}
		$param .= '&ref=' . urlencode($object->ref);


		$comptetitorPrices = $competitorPrice->fetchAll($sortorder, $sortfield, $limit, $page, ['t.fk_product' => $object->id]);
		if (!is_array($comptetitorPrices) && $comptetitorPrices < 0) {
			setEventMessages($competitorPrice->errors, $competitorPrice->error, 'errors');
			$comptetitorPrices = [];
		}
		$comptetitorPricesAll = $competitorPrice->fetchAll('', '', 0, 0, ['t.fk_product' => $object->id]);
		if (!is_array($comptetitorPricesAll) && $comptetitorPricesAll < 0) {
			setEventMessages($competitorPrice->errors, $competitorPrice->error, 'errors');
			$comptetitorPricesAll = [];
		}
		$nbtotalofrecords = count($comptetitorPricesAll);
		$nbtotalofrecords = count($comptetitorPrices);
		$num = count($comptetitorPrices);
		if (($num + ($offset * $limit)) < $nbtotalofrecords) {
			$num++;
		}

		print_barre_liste($langs->trans('CompetitorPrices'), $page, $_SERVER['PHP_SELF'], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'object_'.$competitorPrice->picto, 0, '', '', $limit, 1);

		// Selection of new fields
		include DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

		$varpage = empty($contextpage) ? $_SERVER['PHP_SELF'] : $contextpage;
		$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields

		print '<form action="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '" method="post" name="competitorPrices">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
		print '<input type="hidden" name="action" value="list">';
		print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
		print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';

		// Competitor list title
		print '<div class="div-table-responsive">';
		print '<table class="liste centpercent">';

        $param = '&id=' . $object->id;

		print '<tr class="liste_titre">';
		foreach ($competitorPrice->fields as $key => $val) {
			$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
			if ($key == 'status') {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
			} elseif (in_array($val['type'], ['date', 'datetime', 'timestamp'])) {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
			} elseif (in_array($val['type'], ['timestamp'])) {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
			} elseif (in_array($val['type'], ['double(24,8)', 'double(6,3)', 'integer', 'real',
												   'price']) && $val['label'] != 'TechnicalID' && empty($val['arrayofkeyval'])) {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'right';
			}
			if (!empty($arrayfields[$key]['checked'])) {
				print getTitleFieldOfList($arrayfields[$key]['label'], 0, $_SERVER['PHP_SELF'], 't.' . $key, '', $param, ($cssforfield ? 'class="' . $cssforfield . '"' : ''), $sortfield, $sortorder, ($cssforfield ? $cssforfield . ' ' : '')) . "\n";
			}
		}
        print getTitleFieldOfList($selectedfields, 0, $_SERVER['PHP_SELF'], '', '', $param, ($cssforfield ? 'class="' . $cssforfield . '"' : ''), $sortfield, $sortorder, ($cssforfield ? $cssforfield . ' ' : '')) . "\n";
        print '</tr>';

		if (!empty($comptetitorPrices)) {
			foreach ($comptetitorPrices as $competitorPriceDetail) {
				print '<tr class="oddeven ' . ($competitorPriceDetail->status == 0 ? 'opacitymedium' : '') . '">';
				foreach ($competitorPriceDetail->fields as $key => $val) {
					$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
					if (in_array($val['type'], ['date', 'datetime', 'timestamp'])) {
						$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
					} elseif ($key == 'status') {
						$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
					}

					if (in_array($val['type'], ['timestamp'])) {
						$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
					} elseif ($key == 'ref') {
						$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
					}

					if (in_array($val['type'], ['double(24,8)', 'double(6,3)', 'integer', 'real',
													 'price']) && !in_array($key, ['rowid',
																						'status']) && empty($val['arrayofkeyval'])) {
						$cssforfield .= ($cssforfield ? ' ' : '') . 'right';
					}

					//if (in_array($key, array('fk_soc', 'fk_user', 'fk_warehouse'))) $cssforfield = 'tdoverflowmax100';
					//var_dump($val, $key);
					if (!empty($arrayfields[$key]['checked'])) {
						print '<td' . ($cssforfield ? ' class="' . $cssforfield . '"' : '') . '>';
						if ($key == 'status') {
							print $competitorPriceDetail->getLibStatut(5);
						} elseif ($key == 'rowid') {
							print $competitorPriceDetail->showOutputField($val, $key, $competitorPriceDetail->id, '');
						} else {
							print $competitorPriceDetail->showOutputField($val, $key, $competitorPriceDetail->$key, '');
						}
						print '</td>';

					}


				}
				// Modify-Remove
				print '<td class="center nowraponall">';
				if ($permissiontoadd) {
					print '<a class="editfielda marginrightonly" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&socid=' . $competitorPriceDetail->fk_soc . '&action=update_competitor_price&rowid=' . $competitorPriceDetail->id . '&token=' .  newToken() . '">' . img_edit() . '</a>';
                    print '<a class="marginrightonly wpeo-loader" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&socid=' . $competitorPriceDetail->fk_soc . '&action=confirm_clone&confirm=yes&rowid=' . $competitorPriceDetail->id . '&token=' .  newToken() . '">' . img_picto($langs->trans('Clone'), 'clone') . '</a>';
					print '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&socid=' . $competitorPriceDetail->fk_soc . '&action=deleteProductCompetitorPrice&rowid=' . $competitorPriceDetail->id . '&token=' .  newToken() . '">' . img_picto($langs->trans('Remove'), 'delete') . '</a>';
                }
				print '</td>';
				print '</tr>';
			}
		}

		print '</table>';
		print '</div>';
		print '</form>';
	}

	print '</div>';
}

// End of page
llxFooter();
$db->close();
