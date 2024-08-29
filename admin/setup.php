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

// Load Priseo environment
if (file_exists('../priseo.main.inc.php')) {
    require_once __DIR__ . '/../priseo.main.inc.php';
} elseif (file_exists('../../priseo.main.inc.php')) {
    require_once __DIR__ . '/../../priseo.main.inc.php';
} else {
    die('Include of priseo main fails');
}

// Libraries
require_once DOL_DOCUMENT_ROOT. '/core/lib/admin.lib.php';

//Load Priseo libraries
require_once __DIR__ . '/../lib/priseo.lib.php';

// Global variables definitions
global $db, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Parameters
$backtopage = GETPOST('backtopage', 'alpha');

// Security check - Protection if external user
$permissionToRead = $user->rights->priseo->adminpage->read;
saturne_check_access($permissionToRead);


/*
 * View
 */

$title    = $langs->trans('ModuleSetup', 'Priseo');
$helpUrl = 'FR:Module_Priseo';

saturne_header(0,'', $title, $helpUrl);

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
