<?php
/* Copyright (C) 2025 EVARISK <technique@evarisk.com>
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
 * \brief   List page for competitor price
 */

// Load Priseo environment
if (!file_exists('../../priseo.main.inc.php')) {
    die('Include of priseo main fails');
}
require_once __DIR__ . '/../../priseo.main.inc.php';

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/product.lib.php';

// Load Priseo libraries
require_once __DIR__ . '/../../class/competitorprice.class.php';

// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Get parameters
$action     = GETPOSTISSET('action') ? GETPOST('action', 'aZ09') : 'view'; // The action 'add', 'create', 'edit', 'update', 'view', ...
$massaction = GETPOST('massaction', 'alpha');                                          // The bulk action (combo box choice into lists)
$fromId     = GETPOSTINT('fromid');                                                            // Element id
$fromType   = GETPOST('fromtype', 'alpha');                                            // Element type

// Get list parameters
$toselect                                   = [];
[$confirm, $contextpage, $optioncss, $mode] = ['', '', '', ''];
$listParameters                             = saturne_load_list_parameters(basename(dirname(__FILE__)));
foreach ($listParameters as $listParameterKey => $listParameter) {
    $$listParameterKey = $listParameter;
}

// Get pagination parameters
[$limit, $page, $offset] = [0, 0, 0];
[$sortfield, $sortorder] = ['', ''];
$paginationParameters    = saturne_load_pagination_parameters();
foreach ($paginationParameters as $paginationParameterKey => $paginationParameter) {
    $$paginationParameterKey = $paginationParameter;
}

// Initialize technical objects
$object      = new CompetitorPrice($db);
$product     = new Product($db);
$extrafields = new ExtraFields($db);

// Initialize view objects
$form = new Form($db);

$hookmanager->initHooks([$contextpage]); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Default sort order (if not yet defined by previous GETPOST)
if (!$sortfield) {
    reset($object->fields);  // Reset is required to avoid key() to return null
    $sortfield = 't.competitor_date'; // Set here default search field. By default, date_creation
}
if (!$sortorder) {
    $sortorder = 'DESC';
}

// Definition of array of fields for columns
foreach ($object->fields as $key => $val) {
    if (!empty($val['visible'])) {
        $visible = (int) dol_eval($val['visible']);
        $arrayfields['t.' . $key] = [
            'label'    => $val['label'],
            'checked'  => (($visible < 0 || (!isset($val['showinpwa']) && $mode == 'pwa')) ? 0 : 1),
            'enabled'  => ($visible != 3 && dol_eval($val['enabled'])),
            'position' => $val['position'],
            'help'     => $val['help'] ?? '',
        ];
    }
}

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$searchAll = trim(GETPOST('search_all'));
$search    = [];
foreach ($object->fields as $key => $val) {
    if (GETPOST('search_' . $key, 'alpha') !== '') {
        $search[$key] = GETPOST('search_' . $key, 'alpha');
    }
    if (isset($val['type']) && in_array($val['type'], ['date', 'datetime', 'timestamp'])) {
        $search[$key . '_dtstart'] = dol_mktime(0, 0, 0, GETPOSTINT('search_' . $key . '_dtstartmonth'), GETPOSTINT('search_' . $key . '_dtstartday'), GETPOSTINT('search_' . $key . '_dtstartyear'));
        $search[$key . '_dtend']   = dol_mktime(23, 59, 59, GETPOSTINT('search_' . $key . '_dtendmonth'), GETPOSTINT('search_' . $key . '_dtendday'), GETPOSTINT('search_' . $key . '_dtendyear'));
    }
}

// List of fields to search into when doing a "search in all"
$fieldsToSearchAll = [];
foreach ($object->fields as $key => $val) {
    if (!empty($val['searchall'])) {
        $fieldsToSearchAll['t.' . $key] = $val['label'];
    }
}

// Extra fields
require_once DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_array_fields.tpl.php';

$object->fields = dol_sort_array($object->fields, 'position');
$arrayfields    = dol_sort_array($arrayfields, 'position');

// Permissions
$permissiontoread   = $user->hasRight($object->module, $object->element, 'read');
$permissiontoadd    = $user->hasRight($object->module, $object->element, 'write');
$permissiontodelete = $user->hasRight($object->module, $object->element, 'delete');

// Security check
saturne_check_access($permissiontoread, $object);

/*
 * Actions
 */

$parameters = ['arrayfields' => &$arrayfields];
$resHook    = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($resHook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($resHook)) {
    // Selection of new fields
    require_once DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

    // Purge search criteria
    if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
        foreach ($object->fields as $key => $val) {
            $search[$key] = '';
            if (isset($val['type']) && in_array($val['type'], ['date', 'datetime', 'timestamp'])) {
                $search[$key.'_dtstart'] = '';
                $search[$key.'_dtend']   = '';
            }
        }
        $searchAll            = '';
        $toselect             = [];
        $search_array_options = [];
    }
    if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')
        || GETPOST('button_search_x', 'alpha') || GETPOST('button_search.x', 'alpha') || GETPOST('button_search', 'alpha')) {
        $massaction = ''; // Protection to avoid mass action if we force a new search during a mass action confirmation
    }

    if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') {
        $massaction = '';
    }

    // Mass actions
    $objectclass = 'CompetitorPrice';
    $objectlabel = 'CompetitorPrice';
    $uploaddir   = $conf->priseo->dir_output;

    require_once DOL_DOCUMENT_ROOT . '/core/actions_massactions.inc.php';

    // Mass actions archive
    require_once __DIR__ . '/../../../saturne/core/tpl/actions/list_massactions.tpl.php';
}

/*
 * View
 */

if ($mode == 'pwa') {
    $conf->dol_hide_topmenu  = 1;
    $conf->dol_hide_leftmenu = 1;
}

$title = $langs->transnoentities(ucfirst($object->element) . 'List');
saturne_header(0,'', $title, $helpUrl ?? '', '', 0, 0, [], [], '', 'mod-' . $object->module . '-' . $object->element . ' page-list bodyforlist');

if (!empty($fromType)) {
    $product->fetch($fromId);
    $titleHead = ($product->type == Product::TYPE_SERVICE ? 'service' : 'product');
    saturne_get_fiche_head($product, $object->element, $langs->trans(ucfirst($titleHead)));
    $linkBack = '<a href="' . dol_buildpath($fromType . '/list.php?restore_lastsearch_values=1', 1) . '">' . $langs->trans('BackToList') . '</a>';
    saturne_banner_tab($product, 'fromtype=' . $fromType . '&fromid', $linkBack, 1, 'rowid');

    $moreUrlParameters = '&fromtype=' . $fromType . '&fromid=' . $fromId . '&mode=' . $mode;
}

if ($fromId > 0) {
//    $titre = $langs->trans('CardProduct' . $object->type);
//    $linkback = '<a href="' . DOL_URL_ROOT . '/product/list.php?restore_lastsearch_values=1">' . $langs->trans('BackToList') . '</a>';
//    $object->next_prev_filter = ' fk_product_type = ' . $object->type;
//    $shownav = 1;
//    if ($user->socid && !in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) {
//        $shownav = 0;
//    }
//    dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');
//    print dol_get_fiche_end();

        //$param = '&id=' . $object->id;
        //$param .= '&ref=' . urlencode($object->ref);

//    print '<td class="center nowraponall">';
//    if ($permissiontoadd) {
//        print '<a class="editfielda marginrightonly" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&socid=' . $competitorPriceDetail->fk_soc . '&action=update_competitor_price&rowid=' . $competitorPriceDetail->id . '&token=' .  newToken() . '">' . img_edit() . '</a>';
//        print '<a class="marginrightonly wpeo-loader" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&socid=' . $competitorPriceDetail->fk_soc . '&action=confirm_clone&confirm=yes&rowid=' . $competitorPriceDetail->id . '&token=' .  newToken() . '">' . img_picto($langs->trans('Clone'), 'clone') . '</a>';
//        print '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&socid=' . $competitorPriceDetail->fk_soc . '&action=deleteProductCompetitorPrice&rowid=' . $competitorPriceDetail->id . '&token=' .  newToken() . '">' . img_picto($langs->trans('Remove'), 'delete') . '</a>';
//    }
//    print '</td>';

    require_once __DIR__ . '/../../../saturne/core/tpl/list/objectfields_list_build_sql_select.tpl.php';
    require_once __DIR__ . '/../../../saturne/core/tpl/list/objectfields_list_header.tpl.php';
    require_once __DIR__ . '/../../../saturne/core/tpl/list/objectfields_list_search_input.tpl.php';
    require_once __DIR__ . '/../../../saturne/core/tpl/list/objectfields_list_search_title.tpl.php';
    require_once __DIR__ . '/../../../saturne/core/tpl/list/objectfields_list_loop_object.tpl.php';
    require_once __DIR__ . '/../../../saturne/core/tpl/list/objectfields_list_footer.tpl.php';
}

// End of page
llxFooter();
$db->close();
