<?php
/* Copyright (C) 2022      Florian HENRY <floria.henry@scopen.fr>
 * Copyright (C) 2022-2025 EVARISK <technique@evarisk.com>
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
 * \brief   Priseo setup page
 */

// Load Priseo environment
if (!file_exists('../priseo.main.inc.php')) {
    die('Include of priseo main fails');
}
require_once __DIR__ . '/../priseo.main.inc.php';

// Load Priseo libraries
require_once __DIR__ . '/../lib/priseo.lib.php';

// Global variables definitions
global $db, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Permissions
$permissionToRead = $user->hasRight('priseo', 'adminpage', 'read');

// Security check
saturne_check_access($permissionToRead);

/*
 * View
 */

$title   = $langs->trans('ModuleSetup', 'Priseo');
$helpUrl = 'FR:Module_Priseo';

saturne_header(0,'', $title, $helpUrl);

// Subheader
$linkBack = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1' . '">' . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($title, $linkBack, 'title_setup');

// Configuration header
$head = priseo_admin_prepare_head();
print dol_get_fiche_head($head, 'settings', $title, -1, 'priseo_color@priseo');

// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
