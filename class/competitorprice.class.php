<?php
/* Copyright (C) 2022      Florian HENRY <floria.henry@scopen.fr>
 * Copyright (C) 2022-2024 EOXIA         <dev@eoxia.fr>
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
 * \file    class/competitorprice.class.php
 * \ingroup priseo
 * \brief   This file is a CRUD class file for CompetitorPrice (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

//Load saturne libraries
require_once __DIR__ . '/../../saturne/class/saturneobject.class.php';

/**
 * Class for CompetitorPrice
 */
class CompetitorPrice extends SaturneObject
{
    /**
     * @var string Module name
     */
    public $module = 'priseo';

    /**
     * @var string Element type of object.
     */
    public $element = 'competitorprice';

    /**
     * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management (so extrafields know the link to the parent table)
     */
    public $table_element = 'priseo_competitorprice';

    /**
     * @var int Does this object support multicompany module ?
     * 0 = No test on entity, 1 = Test with field entity, field@table = Test with link by field@table
     */
    public $ismultientitymanaged = 0;

    /**
     * @var int Does object support extrafields ? 0 = No, 1 = Yes
     */
    public $isextrafieldmanaged = 1;

    /**
     * @var string Name of icon for competitorprice. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'competitorprice@priseo' if picto is file 'img/object_competitorprice.png'
     */
    public string $picto = 'fontawesome_fa-chart-line_fas_#63ACC9';

    public const STATUS_VALIDATED = 1;

    /**
     * 'type' field format:
     *      'integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]',
     *      'select' (list of values are in 'options'. for integer list of values are in 'arrayofkeyval'),
     *      'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:CategoryIdType[:CategoryIdList[:SortField]]]]]]',
     *      'chkbxlst:...',
     *      'varchar(x)',
     *      'text', 'text:none', 'html',
     *      'double(24,8)', 'real', 'price', 'stock',
     *      'date', 'datetime', 'timestamp', 'duration',
     *      'boolean', 'checkbox', 'radio', 'array',
     *      'mail', 'phone', 'url', 'password', 'ip'
     *      Note: Filter must be a Dolibarr Universal Filter syntax string. Example: "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.status:!=:0) or (t.nature:is:NULL)"
     * 'length' the length of field. Example: 255, '24,8'
     * 'label' the translation key
     * 'langfile' the key of the language file for translation
     * 'alias' the alias used into some old hard coded SQL requests
     * 'picto' is code of a picto to show before value in forms
     * 'enabled' is a condition when the field must be managed (Example: 1 or 'getDolGlobalInt("MY_SETUP_PARAM")' or 'isModEnabled("multicurrency")' ...)
     * 'position' is the sort order of field
     * 'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0)
     * 'visible' says if field is visible in list (Examples: 0 = Not visible, 1 = Visible on list and create/update/view forms, 2 = Visible on list only, 3 = Visible on create/update/view form only (not list), 4 = Visible on list and update/view form only (not create). 5 = Visible on list and view only (not create/not update). 6=visible on list and create/view form (not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
     * 'noteditable' says if field is not editable (1 or 0)
     * 'alwayseditable' says if field can be modified also when status is not draft (1 or 0)
     * 'default' is a default value for creation (can still be overwritten by the setup of default values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created
     * 'index' if we want an index in database
     * 'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...)
     * 'searchall' is 1 if we want to search in this field when making a search from the quick search button
     * 'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
     * 'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
     * 'placeholder' to set the placeholder of a varchar field
     * 'help' and 'helplist' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click
     * 'showoncombobox' if value of the field must be visible into the label of the combobox that list record
     * 'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code like the constructor of the class
     * 'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array(0 => 'Draft', 1 => 'Active', -1 => 'Cancel'). Note that type can be 'integer' or 'varchar'
     * 'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1
     * 'comment' is not used. You can store here any text of your choice. It is not used by application
     * 'validate' is 1 if you need to validate with $this->validateField() Need MAIN_ACTIVATE_VALIDATION_RESULT
     * 'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1 = picto after label, 2 = picto after value)
     *
     * Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor
     */

    // BEGIN MODULEBUILDER PROPERTIES
    /**
     * @var array Array with all fields and their property. Do not use it as a static var. It may be modified by constructor
     */
    public $fields = [
        'rowid'           => ['type' => 'integer',      'label' => 'TechnicalID',        'enabled' => 1, 'position' => 1,   'notnull' => 1,  'visible' => 0, 'noteditable' => 1, 'index' => 1, 'css' => 'left', 'comment' => 'Id'],
        'ref'             => ['type' => 'varchar(128)', 'label' => 'Ref',                'enabled' => 1, 'position' => 10,  'notnull' => 1,  'visible' => 4, 'noteditable' => 1, 'index' => 1, 'searchall' => 1, 'showoncombobox' => 1, 'comment' => 'Reference of object'],
        'entity'          => ['type' => 'integer',      'label' => 'Entity',             'enabled' => 1, 'position' => 20,  'notnull' => 1,  'visible' => -2],
        'date_creation'   => ['type' => 'datetime',     'label' => 'DateCreation',       'enabled' => 1, 'position' => 30,  'notnull' => 1,  'visible' => -2],
        'tms'             => ['type' => 'timestamp',    'label' => 'DateModification',   'enabled' => 1, 'position' => 40,  'notnull' => 0,  'visible' => -2],
        'import_key'      => ['type' => 'varchar(14)',  'label' => 'ImportId',           'enabled' => 1, 'position' => 50,  'notnull' => 0,  'visible' => -2, 'index' => 0],
        'status'          => ['type' => 'integer',      'label' => 'Status',             'enabled' => 1, 'position' => 60,  'notnull' => 1,  'visible' => -2, 'default' => 1, 'index' => 1, 'arrayofkeyval' => [1 => 'Validate']],
        'label'           => ['type' => 'varchar(255)', 'label' => 'Label',              'enabled' => 1, 'position' => 70,  'notnull' => 0,  'visible' => 1, 'alwayseditable' => 1, 'searchall' => 1, 'showoncombobox' => 2],
        'amount_ht'       => ['type' => 'double(24,8)', 'label' => 'CompetitorPriceHT',  'enabled' => 1, 'position' => 80,  'notnull' => 0,  'visible' => 1, 'default' => 'null'],
        'amount_ttc'      => ['type' => 'double(24,8)', 'label' => 'CompetitorPriceTTC', 'enabled' => 1, 'position' => 90,  'notnull' => 0,  'visible' => 1, 'default' => 'null'],
        'vat'             => ['type' => 'varchar(10)',  'label' => 'VAT',                'enabled' => 1, 'position' => 100, 'notnull' => 0,  'visible' => -2],
        'url_competitor'  => ['type' => 'url',          'label' => 'ProductPageURL',     'enabled' => 1, 'position' => 110, 'notnull' => 0,  'visible' => 1, 'cssview' => 'wordbreak'],
        'competitor_date' => ['type' => 'datetime',     'label' => 'CompetitorDate',     'enabled' => 1, 'position' => 120, 'notnull' => 1,  'visible' => -2],
        'fk_product'      => ['type' => 'integer:Product:product/class/product.class.php:1',                                                    'label' => 'Product',    'enabled' => 1, 'position' => 130, 'notnull' => 1,  'visible' => -2, 'index' => 1, 'foreignkey' => 'product.rowid', 'csslist' => 'tdoverflowmax150'],
        'fk_soc'          => ['type' => 'integer:Societe:societe/class/societe.class.php:1:((status:=:1) AND (entity:IN:__SHARED_ENTITIES__))', 'label' => 'Competitor', 'enabled' => 1, 'position' => 140, 'notnull' => 1,  'visible' => 1,  'index' => 1, 'foreignkey' => 'societe.rowid', 'css' => 'maxwidth500 widthcentpercentminusxx', 'csslist' => 'tdoverflowmax150'],
        'fk_user_creat'   => ['type' => 'integer:User:user/class/user.class.php',                                                               'label' => 'UserAuthor', 'enabled' => 1, 'position' => 150, 'notnull' => 1,  'visible' => -2, 'index' => 1, 'foreignkey' => 'user.rowid', 'csslist' => 'tdoverflowmax150'],
        'fk_user_modif'   => ['type' => 'integer:User:user/class/user.class.php',                                                               'label' => 'UserModif',  'enabled' => 1, 'position' => 160, 'notnull' => -1, 'visible' => -2, 'index' => 1, 'foreignkey' => 'user.rowid', 'csslist' => 'tdoverflowmax150'],
    ];

    /**
     * @var int ID
     */
    public int $rowid;

    /**
     * @var string Ref
     */
    public $ref;

    /**
     * @var int Entity
     */
    public $entity;

    /**
     * @var int|string Creation date
     */
    public $date_creation;

    /**
     * @var int|string Timestamp
     */
    public $tms;

    /**
     * @var string Import key
     */
    public $import_key;

    /**
     * @var int Status
     */
    public $status;

    /**
     * @var string|null Label
     */
    public ?string $label;

    /**
     * @var float|null HT amount
     */
    public ?float $amount_ht;

    /**
     * @var float|null TTC amount
     */
    public ?float $amount_ttc;

	public $url_competitor;
	public $competitor_date;
	public $fk_product;
	public $fk_soc;

    /**
     * @var int User ID
     */
    public $fk_user_creat;

    /**
     * @var int|null User ID
     */
    public $fk_user_modif;

    /**
     * Constructor
     *
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        parent::__construct($db, $this->module, $this->element);
    }

    /**
     * Create object into database
     *
     * @param  User      $user      User that creates
     * @param  int       $noTrigger 0 = launch triggers after, 1 = disable triggers
     * @return int                  0 < if KO, ID of created object if OK
     */
    public function create(User $user, int $noTrigger = 0): int
    {
        $this->status = 1;
        return parent::create($user, $noTrigger);
    }

    /**
     * Clone an object into another one
     *
     * @param  User      $user    User that creates
     * @param  int       $fromID  ID of object to clone
     * @return int                New object created, <0 if KO
     * @throws Exception
     */
    public function createFromClone(User $user, int $fromID): int
    {
        dol_syslog(__METHOD__, LOG_DEBUG);

        $object = new self($this->db);
        $this->db->begin();

        // Reset some properties
        unset($object->id);
        unset($object->fk_user_creat);

        // Load source object
        $object->fetchCommon($fromID);

        // Clear fields
        if (property_exists($object, 'date_creation')) {
            $object->date_creation = dol_now();
        }
        if (property_exists($object, 'competitor_date')) {
            $object->competitor_date = dol_now();
        }
        if (property_exists($object, 'ref')) {
            $object->ref = $this->getNextNumRef();
        }

        // Create clone
        $object->context   = 'createfromclone';
        $competitorPriceID = $object->create($user);

        unset($object->context);

        // End
        if ($competitorPriceID > 0) {
            $this->db->commit();
            return $competitorPriceID;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Sets object to supplied categories
     *
     * Deletes object from existing categories not supplied
     * Adds it to non-existing supplied categories
     * Existing categories are left untouched
     *
     * @param  int[]|int $categories Category or categories IDs
     * @return float|int
     */
    public function setCategories($categories, string $typeCateg = '', bool $removeExisting = false): int
    {
        return 1;
    }

    /**
     * Return the status
     *
     * @param  int    $status ID status
     * @param  int    $mode   0 = long label, 1 = short label, 2 = Picto + short label, 3 = Picto, 4 = Picto + long label, 5 = Short label + Picto, 6 = Long label + Picto
     * @return string         Label of status
     */
    public function LibStatut(int $status, int $mode = 0): string
    {
        if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
            global $langs;

            $this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');

            $this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
        }

        $statusType = 'status'.$status;
        if ($status == self::STATUS_VALIDATED) {
            $statusType = 'status4';
        }

        return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
    }

    /**
     * Return average of amount_ht
     *
     * @param  int        $fkProductID ID of product
     * @return float|null $average     Average of amount_ht
     */
    public function getAverage(int $fkProductID = 0): ?float
    {
        $sql  = 'SELECT AVG(amount_ht) AS moyenne_amount_ht';
        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
        $sql .= ' WHERE status = 1 AND fk_product = ' . $fkProductID;

        $average = 0;
        $result  = $this->db->query($sql);
        if ($result) {
            if ($this->db->num_rows($result)) {
                $obj     = $this->db->fetch_object($result);
                $average = $obj->moyenne_amount_ht;
            }
            $this->db->free($result);
        } else {
            dol_print_error($this->db);
        }

        return $average;
    }

    /**
     * Load dashboard info
     *
     * @return array
     * @throws Exception
     */
    public function load_dashboard(): array
    {
        $getCompetitorPriceByAmountHT = $this->getCompetitorPriceByAmountHT();

        $array['graphs'] = [$getCompetitorPriceByAmountHT];

        return $array;
    }

    /**
     * Get competitor price by amount HT
     *
     * @return array     Graph datas (label/color/type/title/data etc..)
     * @throws Exception
     */
    public function getCompetitorPriceByAmountHT(): array
    {
        global $langs;

        // Graph Title parameters
        $array['title'] = $langs->transnoentities('CompetitorPriceEvolutionOverTime');
        $array['picto'] = $this->picto;

        // Graph parameters
        $array['width']      = '100%';
        $array['height']     = 400;
        $array['type']       = 'lines';
        $array['showlegend'] = 1;
        $array['dataset']    = 2;

        $array['labels'] = [
            0 => [
                'label' => $langs->transnoentities('AmountHT'),
            ],
            1 => [
                'label' => $langs->transnoentities('Average'),
            ]
        ];

        $arrayCompetitorPriceByAmountHT = [];
        $competitorPrices               = $this->fetchAll('', '', 0, 0, ['customsql' => 't.status = ' . self::STATUS_VALIDATED . ' AND t.amount_ht IS NOT NULL AND t.fk_product = ' . GETPOST('id')]);
        $averageAmountHT                = $this->getAverage(GETPOST('id'));
        if (is_array($competitorPrices) && !empty($competitorPrices)) {
            foreach ($competitorPrices as $competitorPrice) {
                $arrayCompetitorPriceByAmountHT[] = [dol_print_date($competitorPrice->date_creation, 'dayhour', 'tzuser'), $competitorPrice->amount_ht, $averageAmountHT];
            }
        }

        $array['data'] = $arrayCompetitorPriceByAmountHT;

        return $array;
    }
}
