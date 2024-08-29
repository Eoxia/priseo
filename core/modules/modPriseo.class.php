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
 * 	\defgroup   priseo     Module Priseo
 *  \brief      Priseo module descriptor.
 *
 *  \file       core/modules/modPriseo.class.php
 *  \ingroup    priseo
 *  \brief      Description and activation file for module Priseo
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module Priseo
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
		$this->db = $db;

        if (file_exists(__DIR__ . '/../../../saturne/lib/saturne_functions.lib.php')) {
            require_once __DIR__ . '/../../../saturne/lib/saturne_functions.lib.php';
            saturne_load_langs(['priseo@priseo']);
        } else {
            $this->error++;
            $this->errors[] = $langs->trans('activateModuleDependNotSatisfied', 'Priseo', 'Saturne');
        }

		// ID for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used module id).
		$this->numero = 436350;

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'priseo';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = '';

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		$this->familyinfo = ['Eoxia' => ['position' => '01', 'label' => $langs->trans('Eoxia')]];
		// Module label (no space allowed), used if translation string 'ModulePriseoName' not found (Priseo is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// Module description, used if translation string 'ModulePriseoDesc' not found (Priseo is name of module).
		$this->description = $langs->trans('PriseoDescription');
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = $langs->trans('PriseoDescriptionLong');

		// Author
		$this->editor_name = 'Eoxia';
		$this->editor_url = 'https://eoxia.com';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.1.0';

		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where PRISEO is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'priseo_color@priseo';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = [
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 1,
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
			'models' => 1,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => ['/priseo/css/priseo_all.css'],
			// Set this to relative path of js file if module must load a js on all pages
			'js' => [],
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => [
                'main',
                'productpricecard'
            ],
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
        ];

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/priseo/temp","/priseo/subdir");
		$this->dirs = ['/priseo/temp'];

		// Config pages. Put here list of php page, stored into priseo/admin directory, to use to set up module.
		$this->config_page_url = ['setup.php@priseo'];

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = ['modProduct'];
		$this->requiredby = []; // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = []; // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)

		// The language file dedicated to your module
		$this->langfiles = ['priseo@priseo'];

		// Prerequisites
		$this->phpmin = [7, 0]; // Minimum version of PHP required by module
		$this->need_dolibarr_version = [15, 0]; // Minimum version of Dolibarr required by module

		// Messages at activation
		$this->warnings_activation = []; // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = []; // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		//$this->automatic_activation = array('FR'=>'PriseoWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('PRISEO_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('PRISEO_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = [
            // CONST COMPETITOR PRICE
            1 => ['PRISEO_COMPETITORPRICE_ADDON', 'chaine', 'mod_competitorprice_standard', '', 0, 'current'],
        ];

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->priseo) || !isset($conf->priseo->enabled)) {
			$conf->priseo = new stdClass();
			$conf->priseo->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs   = [];
        $pictopath    = dol_buildpath('/custom/priseo/img/priseo_color.png', 1);
        $pictoPriseo  = img_picto('', $pictopath, '', 1, 0, 0, '', 'pictoPriseo');
        $this->tabs[] = ['data'=>'product:+priseo:' . $pictoPriseo . $langs->trans('CompetitorPrice') . ':priseo@priseo:$user->rights->priseo->competitorprice->read:/custom/priseo/view/competitorprice_card.php?id=__ID__'];  					// To add a new tab identified by code tabname1
        // Example:
        //$this->tabs[] = array('data'=>'product:+priseo:CompetotorPrice:priseo@priseo:$user->rights->priseo->read:/priseo/competitorprice_card.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@priseo:$user->rights->othermodule->read:/priseo/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname

		// Dictionaries
		$this->dictionaries = [];
		/* Example:
		$this->dictionaries=array(
			'langs'=>'priseo@priseo',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(MAIN_DB_PREFIX."table1", MAIN_DB_PREFIX."table2", MAIN_DB_PREFIX."table3"),
			// Label of tables
			'tablib'=>array("Table1", "Table2", "Table3"),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),
			// Sort order
			'tabsqlsort'=>array("label ASC", "label ASC", "label ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("code,label", "code,label", "code,label"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("code,label", "code,label", "code,label"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("code,label", "code,label", "code,label"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid", "rowid", "rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->priseo->enabled, $conf->priseo->enabled, $conf->priseo->enabled),
			// Tooltip for every fields of dictionaries: DO NOT PUT AN EMPTY ARRAY
			'tabhelp'=>array(array('field1' => 'field1tooltip', 'field2' => 'field2tooltip'), array('field1' => 'field1tooltip', 'field2' => 'field2tooltip'), ...),

		);
		*/

		// Boxes/Widgets
		// Add here list of php file(s) stored in priseo/core/boxes that contains a class to show a widget.
		$this->boxes = [
			//  0 => array(
			//      'file' => 'priseowidget1.php@priseo',
			//      'note' => 'Widget provided by Priseo',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
        ];

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = [
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/priseo/class/competitorprice.class.php',
			//      'objectname' => 'CompetitorPrice',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => '$conf->priseo->enabled',
			//      'priority' => 50,
			//  ),
        ];
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->priseo->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->priseo->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = [];
		$r = 0;

        /* PRISEO PERMISSIONS */
        $this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
        $this->rights[$r][1] = $langs->trans('LirePriseo');
        $this->rights[$r][4] = 'lire';
        $r++;
        $this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
        $this->rights[$r][1] = $langs->trans('ReadPriseo');
        $this->rights[$r][4] = 'read';
        $r++;

        /* COMPETITOR PRICE PERMISSIONS */
		$this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = $langs->trans('ReadCompetitorPrice'); // Permission label
		$this->rights[$r][4] = 'competitorprice';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->priseo->competitorprice->read)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = $langs->trans('CreateCompetitorPrice'); // Permission label
		$this->rights[$r][4] = 'competitorprice';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->priseo->competitorprice->write)
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = $langs->trans('DeleteCompetitorPrice'); // Permission label
		$this->rights[$r][4] = 'competitorprice';
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->priseo->competitorprice->delete)
		$r++;

        /* ADMINPAGE PANEL ACCESS PERMISSIONS */
        $this->rights[$r][0] = $this->numero . sprintf('%02d', $r + 1);
        $this->rights[$r][1] = $langs->transnoentities('ReadAdminPage');
        $this->rights[$r][4] = 'adminpage';
        $this->rights[$r][5] = 'read';

		// Main menu entries to add
		$this->menu = [];

		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT COMPETITORPRICE */
		/*
		$langs->load("priseo@priseo");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='CompetitorPriceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='competitorprice@priseo';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'CompetitorPrice'; $keyforclassfile='/priseo/class/competitorprice.class.php'; $keyforelement='competitorprice@priseo';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'CompetitorPriceLine'; $keyforclassfile='/priseo/class/competitorprice.class.php'; $keyforelement='competitorpriceline@priseo'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='competitorprice'; $keyforaliasextra='extra'; $keyforelement='competitorprice@priseo';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='competitorpriceline'; $keyforaliasextra='extraline'; $keyforelement='competitorpriceline@priseo';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('competitorpriceline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'competitorprice as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'competitorprice_line as tl ON tl.fk_competitorprice = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('competitorprice').')';
		$r++; */
		/* END MODULEBUILDER EXPORT COMPETITORPRICE */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT COMPETITORPRICE */
		/*
		 $langs->load("priseo@priseo");
		 $this->export_code[$r]=$this->rights_class.'_'.$r;
		 $this->export_label[$r]='CompetitorPriceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		 $this->export_icon[$r]='competitorprice@priseo';
		 $keyforclass = 'CompetitorPrice'; $keyforclassfile='/priseo/class/competitorprice.class.php'; $keyforelement='competitorprice@priseo';
		 include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		 $keyforselect='competitorprice'; $keyforaliasextra='extra'; $keyforelement='competitorprice@priseo';
		 include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		 //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		 $this->export_sql_start[$r]='SELECT DISTINCT ';
		 $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'competitorprice as t';
		 $this->export_sql_end[$r] .=' WHERE 1 = 1';
		 $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('competitorprice').')';
		 $r++; */
		/* END MODULEBUILDER IMPORT COMPETITORPRICE */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
        $sql    = [];
        $result = $this->_load_tables('/priseo/sql/');

        if ($result < 0) {
            return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
        }
        // Permissions
        $this->remove($options);

        // Create extrafields during init
        require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

        $extraFields = new ExtraFields($this->db);

        $extraFields->update('product_url', 'ProductPageURL', 'url', '', 'product_fournisseur_price', 0, 0, $this->numero . 10, '', '', '', 1, '', '', '', 0, 'priseo@priseo', "isModEnabled('priseo') && isModEnabled('product')");
        $extraFields->addExtraField('product_url', 'ProductPageURL', 'url', $this->numero . 10, '', 'product_fournisseur_price', 0, 0, '', '', '', '', 1, '', '', 0, 'priseo@priseo', "isModEnabled('priseo') && isModEnabled('product')");

        return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = [];
		return $this->_remove($sql, $options);
	}
}
