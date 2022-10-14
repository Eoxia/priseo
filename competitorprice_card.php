<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file       competitorprice_card.php
 *        \ingroup    priseo
 *        \brief      Page to create/edit/view competitorprice
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT . '/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
dol_include_once('/priseo/class/competitorprice.class.php');
dol_include_once('/priseo/lib/priseo_competitorprice.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("priseo@priseo", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'competitorpricecard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid = GETPOST('lineid', 'int');


$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = (GETPOST("page", 'int') ? GETPOST("page", 'int') : 0);
if (empty($page) || $page == -1) {
	$page = 0;
}     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield) {
	$sortfield = "s.nom";
}
if (!$sortorder) {
	$sortorder = "ASC";
}


// Initialize technical objects
$competitorPrice = new CompetitorPrice($db);
$object = new Product($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->priseo->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('competitorpricecard', 'globalcard')); // Note that conf->hooks_modules contains array

if (!empty($rowid)) {
	$resultFetch = $competitorPrice->fetch($rowid);
	if ($resultFetch < 0) {
		setEventMessages($competitorPrice->error, $competitorPrice->errors, 'errors');
	}
}

// Definition of array of fields for columns
$arrayfields = array();
foreach ($competitorPrice->fields as $key => $val) {
	// If $val['visible']==0, then we never show the field
	if (!empty($val['visible'])) {
		$visible = (int)dol_eval($val['visible'], 1);
		$arrayfields[$key] = array(
			'label'    => $val['label'],
			'checked'  => (($visible < 0) ? 0 : 1),
			'enabled'  => ($visible != 3 && dol_eval($val['enabled'], 1)),
			'position' => $val['position'],
			'help'     => isset($val['help']) ? $val['help'] : ''
		);
	}
}


// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_' . $key, 'alpha')) {
		$search[$key] = GETPOST('search_' . $key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->priseo->competitorprice->read;
	$permissiontoadd = $user->rights->priseo->competitorprice->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->priseo->competitorprice->delete;
	$permissionnote = $user->rights->priseo->competitorprice->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->priseo->competitorprice->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->priseo->multidir_output[isset($object->entity) ? $object->entity : 1] . '/competitorprice';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->priseo->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/priseo/competitorprice_card.php', 1).'?id='.$id;

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/priseo/competitorprice_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
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
	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	//include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	//include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	//include DOL_DOCUMENT_ROOT . '/core/actions_builddoc.inc.php';

	/*if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}*/

	// Actions to send emails
	//$triggersendname = 'PRISEO_COMPETITORPRICE_SENTBYMAIL';
	//$autocopy = 'MAIN_MAIL_AUTOCOPY_COMPETITORPRICE_TO';
	//$trackid = 'competitorprice' . $object->id;
	//include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';

	//Tricks to use common template
	$object = $product;
}


/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
//$formfile = new FormFile($db);
//$formproject = new FormProjets($db);

$form = new Form($db);

$title = $langs->trans('ProductServiceCard') . '-' . $langs->trans("CompetitorPrice");
$helpurl = '';
$shortlabel = dol_trunc($object->label, 16);
if (GETPOST("type") == '0' || ($object->type == Product::TYPE_PRODUCT)) {
	$title = $langs->trans('Product') . " " . $shortlabel . " - " . $langs->trans('BuyingPrices');
	$helpurl = 'EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
}
if (GETPOST("type") == '1' || ($object->type == Product::TYPE_SERVICE)) {
	$title = $langs->trans('Service') . " " . $shortlabel . " - " . $langs->trans('BuyingPrices');
	$helpurl = 'EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
}

llxHeader('', $title, $helpurl, '', 0, 0, '', '', '', 'classforhorizontalscrolloftabs');

if ($object->id > 0) {

	if ($action == 'ask_remove_cp') {
		$form = new Form($db);
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&rowid=' . $competitorPrice->id, $langs->trans('DeleteProductCompetitorPrice'), $langs->trans('ConfirmDeleteProductCompetitorPrice'), 'confirm_delete', '', 0, 1);
		echo $formconfirm;
	}

	$head = product_prepare_head($object);
	$titre = $langs->trans("CardProduct" . $object->type);
	$picto = ($object->type == Product::TYPE_SERVICE ? 'service' : 'product');

	print dol_get_fiche_head($head, 'priseo', $titre, -1, $picto);

	$linkback = '<a href="' . DOL_URL_ROOT . '/product/list.php?restore_lastsearch_values=1">' . $langs->trans("BackToList") . '</a>';
	$object->next_prev_filter = " fk_product_type = " . $object->type;

	$shownav = 1;
	if ($user->socid && !in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) {
		$shownav = 0;
	}

	dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');

	print '<div class="fichecenter">';

	print '<div class="underbanner clearboth"></div>';
	print '<table class="border tableforfield centpercent">';

	// Type
	/*if (!empty($conf->product->enabled) && !empty($conf->service->enabled)) {
		$typeformat = 'select;0:' . $langs->trans("Product") . ',1:' . $langs->trans("Service");
		print '<tr><td class="$permissiontoadd">';
		print (empty($conf->global->PRODUCT_DENY_CHANGE_PRODUCT_TYPE)) ? $form->editfieldkey("Type", 'fk_product_type', $object->type, $object, 0, $typeformat) : $langs->trans('Type');
		print '</td><td>';
		print $form->editfieldval("Type", 'fk_product_type', $object->type, $object, 0, $typeformat);
		print '</td></tr>';
	}*/

	// Cost price. Can be used for margin module for option "calculate margin on explicit cost price

	print '</table>';

	print '</div>';
	print '<div style="clear:both"></div>';

	print dol_get_fiche_end();

	// Actions buttons

	print '<div class="tabsAction">' . "\n";

	if ($action != 'add_competitor_price' && $action != 'update_competitor_price') {
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if (empty($reshook)) {
			if ($permissiontoadd) {
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=create_competitor_price&token=' . newToken() . '">';
				print $langs->trans("AddCompetitorPrice") . '</a>';
			}
		}
	}

	print "</div>\n";

	if ($action == 'create_competitor_price') {

		//Tricks to use common template
		$product = $object;
		$object = $competitorPrice;

		if (empty($permissiontoadd)) {
			accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
			exit;
		}

		print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("CompetitorPrices")), '', 'object_' . $object->picto);

		print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="action" value="add">';
		print '<input type="hidden" name="id" value="' . $product->id . '">';
		if ($backtopage) {
			print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
		}
		if ($backtopageforcancel) {
			print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
		}

		print dol_get_fiche_head(array(), '');

		// Set some default values
		//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

		print '<table class="border centpercent tableforfieldcreate">' . "\n";

		// Common attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

		// Other attributes
		//include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

		print '</table>' . "\n";

		print dol_get_fiche_end();

		print $form->buttonsSaveCancel("Create");

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

		print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("CompetitorPrices")), '', 'object_' . $object->picto);

		print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
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

		print dol_get_fiche_head(array(), '');

		// Set some default values
		//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

		print '<table class="border centpercent tableforfieldedit">' . "\n";

		// Common attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

		// Other attributes
		//include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

		print '</table>' . "\n";

		print dol_get_fiche_end();

		print $form->buttonsSaveCancel("Save");

		print '</form>';

		$object = $product;
	}


	if ($permissiontoread) {
		$param = '';
		if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
			$param .= '&contextpage=' . urlencode($contextpage);
		}
		if ($limit > 0 && $limit != $conf->liste_limit) {
			$param .= '&limit=' . urlencode($limit);
		}
		$param .= '&ref=' . urlencode($object->ref);


		$comptetitorPrices = $competitorPrice->fetchAll('', '', $limit, $page, array('t.fk_product' => $object->id));
		if (!is_array($comptetitorPrices) && $comptetitorPrices < 0) {
			setEventMessages($competitorPrice->errors, $competitorPrice->error, 'errors');
			$comptetitorPrices = array();
		}
		$comptetitorPricesAll = $competitorPrice->fetchAll('', '', 0, 0, array('t.fk_product' => $object->id));
		if (!is_array($comptetitorPricesAll) && $comptetitorPricesAll < 0) {
			setEventMessages($competitorPrice->errors, $competitorPrice->error, 'errors');
			$comptetitorPricesAll = array();
		}
		$nbtotalofrecords = count($comptetitorPricesAll);
		$nbtotalofrecords = count($comptetitorPrices);
		$num = count($comptetitorPrices);
		if (($num + ($offset * $limit)) < $nbtotalofrecords) {
			$num++;
		}

		print_barre_liste($langs->trans('CompetitorPrices'), $page, $_SERVER['PHP_SELF'], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_accountancy.png', 0, '', '', $limit, 1);

		// Selection of new fields
		include DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

		$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
		$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields

		print '<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post" name="competitorPrices">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
		print '<input type="hidden" name="action" value="list">';
		print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
		print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';

		// Competitor list title
		print '<div class="div-table-responsive">';
		print '<table class="liste centpercent">';

		$param = "&id=" . $object->id;

		print '<tr class="liste_titre">';
		foreach ($competitorPrice->fields as $key => $val) {
			$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
			if ($key == 'status') {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
			} elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
			} elseif (in_array($val['type'], array('timestamp'))) {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
			} elseif (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real',
												   'price')) && $val['label'] != 'TechnicalID' && empty($val['arrayofkeyval'])) {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'right';
			}
			if (!empty($arrayfields[$key]['checked'])) {
				print getTitleFieldOfList($arrayfields[$key]['label'], 0, $_SERVER['PHP_SELF'], 't.' . $key, '', $param, ($cssforfield ? 'class="' . $cssforfield . '"' : ''), $sortfield, $sortorder, ($cssforfield ? $cssforfield . ' ' : '')) . "\n";
			}
		}
		print getTitleFieldOfList('', 0, $_SERVER['PHP_SELF'], '', '', $param, ($cssforfield ? 'class="' . $cssforfield . '"' : ''), $sortfield, $sortorder, ($cssforfield ? $cssforfield . ' ' : '')) . "\n";
		print "</tr>\n";
		//var_dump($arrayfields);

		if (!empty($comptetitorPrices)) {
			foreach ($comptetitorPrices as $competitorPriceDetail) {
				print '<tr class="oddeven">';
				foreach ($competitorPriceDetail->fields as $key => $val) {
					$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
					if (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
						$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
					} elseif ($key == 'status') {
						$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
					}

					if (in_array($val['type'], array('timestamp'))) {
						$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
					} elseif ($key == 'ref') {
						$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
					}

					if (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real',
													 'price')) && !in_array($key, array('rowid',
																						'status')) && empty($val['arrayofkeyval'])) {
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
					print '<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $competitorPriceDetail->fk_soc . '&amp;action=update_competitor_price&amp;rowid=' . $competitorPriceDetail->id . '">' . img_edit() . "</a>";
					print ' &nbsp; ';
					print '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $competitorPriceDetail->fk_soc . '&amp;action=ask_remove_cp&amp;rowid=' . $competitorPriceDetail->id . '">' . img_picto($langs->trans("Remove"), 'delete') . '</a>';
				}

				print '</td>';

				print '</tr>';
			}
		}

		print '</table>';
		print '</div>';
		print '</form>';
	}

	print "</div>\n";
}
// End of page
llxFooter();
$db->close();
