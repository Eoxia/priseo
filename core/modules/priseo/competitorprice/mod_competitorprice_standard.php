<?php
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
 * or see https://www.gnu.org/
 */

/**
 * \file    core/modules/priseo/competitorprice/mod_competitorprice_standard.php
 * \ingroup priseo
 * \brief   File of class to manage competitorprice numbering module standard
 */

// Load Saturne libraries
require_once __DIR__ . '/../../../../../saturne/core/modules/saturne/modules_saturne.php';

/**
 * Class to manage competitorprice numbering rules Standard
 */
class mod_competitorprice_standard extends ModeleNumRefSaturne
{
    /**
     * @var string Numbering module ref prefix
     */
    public string $prefix = 'CP';

    /**
     * @var string Name
     */
    public string $name = 'Bestla';
}
