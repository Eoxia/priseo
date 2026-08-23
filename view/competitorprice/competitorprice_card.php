<?php ini_set('display_errors', 1); error_reporting(E_ALL);
/* Copyright (C) 2022      Florian HENRY <floria.henry@scopen.fr>
 * Copyright (C) 2022-2025 EVARISK <technique@evarisk.com>
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
 * \file    view/competitorprice/competitorprice_card.php
 * \ingroup priseo
 * \brief   Page to create/edit/view competitorprice
 */

// Load Priseo environment
if (!file_exists('../../priseo.main.inc.php')) {
    die('Include of priseo main fails');
}
require_once __DIR__ . '/../../priseo.main.inc.php';

// Load Priseo libraries
require_once __DIR__ . '/../../class/competitorprice.class.php';

// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

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
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');
$fromId   = GETPOSTINT('fromid');                                                            // Element id
$fromType = GETPOST('fromtype', 'alpha');                                            // Element type

// Initialize technical objects
$object      = new CompetitorPrice($db);
$extrafields = new ExtraFields($db);

$form = new Form($db);

$hookmanager->initHooks(['competitorpricecard', 'globalcard']);  // Note that conf->hooks_modules contains array


// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be included, not include_once

// Permissions
$permissiontoread   = $user->hasRight($object->module, $object->element, 'read');
$permissiontoadd    = $user->hasRight($object->module, $object->element, 'write');
$permissiontodelete = $user->hasRight($object->module, $object->element, 'delete');

// Security check
saturne_check_access($permissiontoread, $object);

/*
 * Actions
 */

$parameters = [];
$resHook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($resHook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($resHook)) {
    $backurlforlist = dol_buildpath('priseo/view/competitorprice/competitorprice_list.php', 1) . '?fromid=' . $fromId . '&fromtype=' . $fromType;

    if (empty($backtopage) || ($cancel && empty($id))) {
        if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
            if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
                $backtopage = $backurlforlist;
            } else {
                $backtopage = $backurlforlist;
            }
        }
    }

	$triggermodname = 'PRISEO_COMPETITORPRICE_MODIFY'; // Name of trigger action code to execute when we modify record

    // Action to add record
    if ($action == 'add' && $permissiontoadd) {
        // $object is still empty here (nothing fetched on a creation), the values come from the posted form
        $competitorPrices = $object->fetchAll('', '', 0, 0, ['customsql' => 't.fk_soc = ' . GETPOSTINT('fk_soc') . ' AND t.fk_product = ' . GETPOSTINT('fk_product')]);
        if (is_array($competitorPrices) && !empty($competitorPrices)) {
            foreach ($competitorPrices as $competitorPrice) {
                $competitorPrice->setValueFrom('status', 0, '', '', 'int', '', $user);
            }
        }
    }

    require_once DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';
}


/*
 * View
 */

$title    = $langs->trans('CompetitorPrice');
$help_url = 'FR:Module_Priseo';

llxHeader('', $title, $help_url);

// Part to create
if ($action == 'create') {
    if (empty($permissiontoadd)) {
        accessforbidden($langs->trans('NotEnoughPermissions'), 0);
    }

    print load_fiche_titre($langs->trans('NewObject', dol_strtolower($langs->transnoentities(dol_ucfirst($object->element)))), '', $object->picto);

    print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
    print '<input type="hidden" name="token" value="' . newToken() . '">';
    print '<input type="hidden" name="action" value="add">';
    if ($backtopage) {
        print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
    }
    if ($backtopageforcancel) {
        print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
    }
    if (!empty($backtopagejsfields)) {
        print '<input type="hidden" name="backtopagejsfields" value="' . $backtopagejsfields . '">';
    }
    if ($dol_openinpopup) {
        print '<input type="hidden" name="dol_openinpopup" value="' . $dol_openinpopup . '">';
    }

    print dol_get_fiche_head();

    // fk_product and competitor_date are hidden on the creation form (visible = -2), so they are never
    // posted and the record ends up without any product nor date. Post them along with the form.
    $competitorDate = dol_getdate(dol_now());

    print '<input type="hidden" name="fromid" value="' . $fromId . '">';
    print '<input type="hidden" name="fromtype" value="' . $fromType . '">';
    if ($fromType == 'product' && $fromId > 0) {
        print '<input type="hidden" name="fk_product" value="' . $fromId . '">';
    }
    print '<input type="hidden" name="competitor_date" value="' . dol_print_date(dol_now(), 'dayhour') . '">';
    print '<input type="hidden" name="competitor_dateyear" value="' . $competitorDate['year'] . '">';
    print '<input type="hidden" name="competitor_datemonth" value="' . $competitorDate['mon'] . '">';
    print '<input type="hidden" name="competitor_dateday" value="' . $competitorDate['mday'] . '">';
    print '<input type="hidden" name="competitor_datehour" value="' . $competitorDate['hours'] . '">';
    print '<input type="hidden" name="competitor_datemin" value="' . $competitorDate['minutes'] . '">';

    print '<table class="border centpercent tableforfieldcreate">';

    // Common attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

    print '</table>';

    print dol_get_fiche_end();

    print $form->buttonsSaveCancel('Create');

    print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
    print load_fiche_titre($langs->trans('ModifyCompetitorPrice'), '', $object->picto);

    print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
    print '<input type="hidden" name="token" value="' . newToken() . '">';
    print '<input type="hidden" name="action" value="update">';
    print '<input type="hidden" name="id" value="' . $object->id . '">';
    if ($backtopage) {
        print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
    }
    if ($backtopageforcancel) {
        print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
    }

    print dol_get_fiche_head();

    print '<table class="border centpercent tableforfieldedit">';

    // Common attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

    print '</table>';

    print dol_get_fiche_end();

    print $form->buttonsSaveCancel();

    print '</form>';
}

// End of page
llxFooter();
$db->close();
