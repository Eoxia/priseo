<?php
/* Copyright (C) 2022      Florian HENRY <floria.henry@scopen.fr>
 * Copyright (C) 2022-2023 EOXIA         <dev@eoxia.fr>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    admin/setup.php
 * \ingroup priseo
 * \brief   Priseo setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
	$res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT']. '/main.inc.php';
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)). '/main.inc.php')) {
	$res = @include substr($tmp, 0, ($i + 1)). '/main.inc.php';
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))). '/main.inc.php')) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))). '/main.inc.php';
}
// Try main.inc.php using relative path
if (!$res && file_exists('../../main.inc.php')) {
	$res = @include '../../main.inc.php';
}
if (!$res && file_exists('../../../main.inc.php')) {
	$res = @include '../../../main.inc.php';
}
if (!$res) {
	die('Include of main fails');
}

// Libraries
require_once DOL_DOCUMENT_ROOT. '/core/lib/admin.lib.php';

require_once __DIR__ . '/../lib/priseo.lib.php';

// Global variables definitions
global $db, $langs, $user;

// Translations
$langs->loadLangs(['admin', 'priseo@priseo']);

// Parameters
$backtopage = GETPOST('backtopage', 'alpha');

// Access control
$permissiontoread = $user->rights->priseo->adminpage->read;
if (empty($conf->priseo->enabled) || !$permissiontoread) {
    accessforbidden();
}

/*
 * View
 */

$help_url = 'FR:Module_Priseo';
$title    = $langs->trans('PriseoSetup');

llxHeader('', $title, $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ?: DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans('BackToModuleList').'</a>';
print load_fiche_titre($title, $linkback, 'priseo_color@priseo');

// Configuration header
$head = priseoAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $title, -1, 'priseo_color@priseo');

// Setup page goes here
echo '<span class="opacitymedium">'.$langs->trans('PriseoSetupPage').'</span><br><br>';

// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
