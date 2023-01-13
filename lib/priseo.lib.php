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
 * \file    lib/priseo.lib.php
 * \ingroup priseo
 * \brief   Library files with common functions for Priseo
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function priseoAdminPrepareHead()
{
    // Global variables definitions
	global $conf, $langs;

    // Load translation files required by the page
	$langs->load('priseo@priseo');

    // Initialize values
	$h = 0;
	$head = [];

	$head[$h][0] = dol_buildpath('/priseo/admin/setup.php', 1);
	$head[$h][1] = '<i class="fas fa-cog pictofixedwidth"></i>' . $langs->trans('Settings');
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dol_buildpath('/priseo/admin/about.php', 1);
	$head[$h][1] = '<i class="fab fa-readme pictofixedwidth"></i>' . $langs->trans('About');
	$head[$h][2] = 'about';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'priseo@priseo');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'priseo@priseo', 'remove');

	return $head;
}
