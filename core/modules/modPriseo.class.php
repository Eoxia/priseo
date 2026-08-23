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
 */

/**
 * 	\defgroup priseo Module Priseo
 *  \brief    Priseo module descriptor
 *
 *  \file    core/modules/modPriseo.class.php
 *  \ingroup priseo
 *  \brief   Description and activation file for module Priseo
 */

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

/**
 * Description and activation class for module Priseo
 */
class modPriseo extends DolibarrModules
{
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;

        parent::__construct($db);

        if (file_exists(__DIR__ . '/../../../saturne/lib/saturne_functions.lib.php')) {
            require_once __DIR__ . '/../../../saturne/lib/saturne_functions.lib.php';
            saturne_load_langs(['priseo@priseo']);
        } else {
            $this->error++;
            $this->errors[] = $langs->trans('activateModuleDependNotSatisfied', 'Priseo', 'Saturne');
        }

        // ID for module (must be unique)
        $this->numero = 436350;

        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'priseo';

        // Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other', 'etc.'
        // It is used to group modules by family in module setup page
        $this->family = '';

        // Module position in the family on 2 digits ('01', '10', '20', ...)
        $this->module_position = '';

        // Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
        $this->familyinfo = ['Eoxia' => ['position' => '01', 'label' => 'Eoxia']];
        // Module label (no space allowed), used if translation string 'ModulePriseoName' not found (Priseo is name of module)
        $this->name = preg_replace('/^mod/i', '', get_class($this));

        // DESCRIPTION_FLAG
        // Module description, used if translation string 'ModulePriseoDesc' not found (Priseo is name of module)
        $this->description = $langs->transnoentities('PriseoDescription');
        // Used only if file README.md and README-LL.md not found
        $this->descriptionlong = $langs->transnoentities('PriseoDescription');

        // Author
        $this->editor_name = 'Eoxia';
        $this->editor_url = 'https://eoxia.com';
        //$this->editor_squarred_logo = ''; // Must be image filename into the priseo/img directory followed with @priseo. Example: 'priseo.png@priseo'

        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
        $this->version = '21.0.0';
        // Url to the file with your last numberversion of this module
        //$this->url_last_version = 'http://www.example.com/versionmodule.txt';

        // Key used in llx_const table to save module status enabled/disabled (where PRISEO is value of property name of module in uppercase)
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

        // Name of image file used for this module
        // If file is in theme/yourtheme/img directory under name object_priseo.png, use this->picto='priseo'
        // If file is in priseo/img directory under name object_priseo.png, use this->picto='priseo@priseo'
        // To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
        $this->picto = 'priseo_color@priseo';

        // Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
        $this->module_parts = [
            // Set this to 1 if module has its own trigger directory (core/triggers)
            'triggers' => 0,
            // Set this to 1 if module has its own login method file (core/login)
            'login' => 0,
            // Set this to 1 if module has its own substitution function file (core/substitutions)
            'substitutions' => 0,
            // Set this to 1 if module has its own menus handler directory (core/menus)
            'menus' => 0,
            // Set this to 1 if module overwrite template dir (core/tpl)
            'tpl' => 0,
            // Set this to 1 if module has its own barcode directory (core/modules/barcode)
            'barcode' => 0,
            // Set this to 1 if module has its own models' directory (core/modules/xxx)
            'models' => 0,
            // Set this to 1 if module has its own printing directory (core/modules/printing)
            'printing' => 0,
            // Set this to 1 if module has its own theme directory (theme)
            'theme' => 0,
            // Set this to relative path of css file if module has its own css file
            'css' => [],
            // Set this to relative path of js file if module must load a js on all pages
            'js' => [],
            // Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
            /* BEGIN MODULEBUILDER HOOKSCONTEXTS */
            'hooks' => [
                'main',
                'productpricecard',
                'elementproperties'
            ],
            /* END MODULEBUILDER HOOKSCONTEXTS */
            // Set this to 1 if features of module are opened to external users
            'moduleforexternal' => 0,
            // Set this to 1 if the module provides a website template into doctemplates/websites/website_template-mytemplate
            'websitetemplates' => 0,
            // Set this to 1 if the module provides a captcha driver
            'captcha' => 0
        ];

        // Data directories to create when module is enabled
        $this->dirs = ['/priseo/temp'];

        // Config pages. Put here list of php page, stored into priseo/admin directory, to use to set up module
        $this->config_page_url = ['setup.php@priseo'];

        // Dependencies
        // A condition to hide module
        $this->hidden = getDolGlobalInt('MODULE_PRISEO_DISABLED'); // A condition to disable module
        // List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
        $this->depends = ['modProduct'];
        // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
        $this->requiredby = [];
        // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
        $this->conflictwith = [];

        // The language file dedicated to your module
        $this->langfiles = ['priseo@priseo'];

        // Prerequisites
        $this->phpmin                  = [7, 4];  // Minimum version of PHP required by module
        $this->need_dolibarr_version   = [20, 0]; // Minimum version of Dolibarr required by module
        // $this->max_dolibarr_version = [21, 0]; // Maximum version of Dolibarr required by module
        $this->need_javascript_ajax    = 0;

        // Messages at activation
        $this->warnings_activation     = []; // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
        $this->warnings_activation_ext = []; // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
        //$this->automatic_activation  = array('FR'=>'PriseoWasAutomaticallyActivatedBecauseOfYourCountryChoice');
        //$this->always_enabled        = true; // If true, can't be disabled

        // Constants
        $i = 0;
        $this->const = [
            // CONST COMPETITOR PRICE
            $i++ => ['PRISEO_COMPETITORPRICE_ADDON', 'chaine', 'mod_competitorprice_standard', '', 0, 'current'],

            // CONST MODULE
            $i++ => ['PRISEO_VERSION', 'chaine', $this->version, '', 0, 'current'],
            $i++ => ['PRISEO_DB_VERSION', 'chaine', $this->version, '', 0, 'current'],
            $i   => ['PRISEO_SHOW_PATCH_NOTE', 'integer', 1, '', 0, 'current']
        ];

        if (!isModEnabled('priseo')) {
            $conf->priseo = new stdClass();
            $conf->priseo->enabled = 0;
        }

        // Array to add new pages in new tabs
        /* BEGIN MODULEBUILDER TABS */
        $this->tabs   = [];
        $pictoPath    = dol_buildpath('custom/priseo/img/priseo_color.png', 1);
        $pictoPriseo  = img_picto('', $pictoPath, 'class="imgTabTitle paddingright marginrightonlyshort"', 1, 0, 0, '');
        $this->tabs[] = ['data'=>'product:+competitorprice:' . $pictoPriseo . ' ' . $langs->transnoentities('CompetitorPrice') . ':priseo@priseo:$user->hasRight(\'priseo\', \'competitorprice\', \'read\'):/custom/priseo/view/competitorprice/competitorprice_list.php?fromid=__ID__&fromtype=product'];
        /* END MODULEBUILDER TABS */

        // Dictionaries
        /* BEGIN MODULEBUILDER DICTIONARIES */
        $this->dictionaries = [];
        /* END MODULEBUILDER DICTIONARIES */

        // Boxes/Widgets
        // Add here list of php file(s) stored in priseo/core/boxes that contains a class to show a widget
        /* BEGIN MODULEBUILDER WIDGETS */
        $this->boxes = [];
        /* END MODULEBUILDER WIDGETS */

        // Cronjobs (List of cron jobs entries to add when module is enabled)
        /* BEGIN MODULEBUILDER CRON */
        $this->cronjobs = [];
        /* END MODULEBUILDER CRON */

        // Permissions provided by this module
        $this->rights = [];
        $r = 0;
        /* BEGIN MODULEBUILDER PERMISSIONS */

        /* PRISEO PERMISSIONS */
        $this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
        $this->rights[$r][1] = $langs->transnoentities('ReadModule', $this->name);
        $this->rights[$r][4] = 'read';
        $r++;

        /* COMPETITOR PRICE PERMISSIONS */
        $this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
        $this->rights[$r][1] = $langs->transnoentities('ReadObjects', dol_strtolower($langs->transnoentities('CompetitorPrices')));
        $this->rights[$r][4] = 'competitorprice';
        $this->rights[$r][5] = 'read';
        $r++;
        $this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
        $this->rights[$r][1] = $langs->transnoentities('CreateObjects', dol_strtolower($langs->transnoentities('CompetitorPrices')));
        $this->rights[$r][4] = 'competitorprice';
        $this->rights[$r][5] = 'write';
        $r++;
        $this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
        $this->rights[$r][1] = $langs->transnoentities('DeleteObjects', dol_strtolower($langs->transnoentities('CompetitorPrices')));
        $this->rights[$r][4] = 'competitorprice';
        $this->rights[$r][5] = 'delete';
        $r++;

        /* ADMINPAGE PANEL ACCESS PERMISSIONS */
        $this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
        $this->rights[$r][1] = $langs->transnoentities('ReadAdminPage',  $this->name);
        $this->rights[$r][4] = 'adminpage';
        $this->rights[$r][5] = 'read';

        /* END MODULEBUILDER PERMISSIONS */

        // Main menu entries to add
        $this->menu = [];
    }

    /**
     * Function called when module is enabled
     * The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database
     * It also creates data directories
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int            1 if OK, 0 if KO
     * @throws Exception
     */
    public function init($options = ''): int
    {
        global $conf;

        // Create tables of module at module activation
        $result = $this->_load_tables('/priseo/sql/');
        if ($result < 0) {
            return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
        }

        dolibarr_set_const($this->db, 'PRISEO_VERSION', $this->version, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($this->db, 'PRISEO_DB_VERSION', $this->version, 'chaine', 0, '', $conf->entity);

        // Create extra fields during init
        $extraFieldsArrays = [
            'product_url' => ['Label' => 'ProductPageURL', 'type' => 'url', 'elementtype' => ['product_fournisseur_price'], 'position' => $this->numero . 10, 'list' => 1, 'entity' => 0, 'langfile' => 'priseo@priseo', 'enabled' => "isModEnabled('priseo') && isModEnabled('product')"]
        ];

        saturne_manage_extrafields($extraFieldsArrays);

        // Permissions
        $this->remove($options);

        return $this->_init([], $options);
    }
}
