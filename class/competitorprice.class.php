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
 * \file        class/competitorprice.class.php
 * \ingroup     priseo
 * \brief       This file is a CRUD class file for CompetitorPrice (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

//Load saturne libraries
require_once __DIR__ . '/../../saturne/class/saturneobject.class.php';

/**
 * Class for CompetitorPrice
 */
class CompetitorPrice extends SaturneObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'priseo';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'competitorprice';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'priseo_competitorprice';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for competitorprice. Must be the part after the 'object_' into object_competitorprice.png
	 */
	public $picto = 'fontawesome_fa-chart-line_fas_#63ACC9';

    /**
     * @var array Label status of const.
     */
    public $labelStatus;

    /**
     * @var array Label status short of const.
     */
    public $labelStatusShort;

    public const STATUS_DRAFT = 0;
    public const STATUS_VALIDATED = 1;

    /**
	 *  'type' field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]', 'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:Sortfield]]]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwroted by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if you need to validate with $this->validateField()
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

    /**
     * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
     */
    public $fields = [
        'rowid'           => ['type' => 'integer', 'label' => 'TechnicalID', 'enabled' => '1', 'position' => 1, 'notnull' => 1, 'visible' => 0, 'noteditable' => '1', 'index' => 1, 'css' => 'left', 'comment' => 'Id'],
        'ref'             => ['type' => 'varchar(128)', 'label' => 'Ref', 'enabled'=>'1', 'position' => 20, 'notnull' => 1, 'visible' => 4, 'noteditable'=>'1', 'index' => 1, 'searchall' => 1, 'showoncombobox' => '1', 'comment' => 'Reference of object'],
        'entity'          => ['type' => 'integer', 'label' => 'Entity', 'enabled' => '1', 'position' => 30, 'notnull' => 1, 'visible' => 0],
        'date_creation'   => ['type' => 'datetime', 'label' => 'DateCreation', 'enabled' => '1', 'position' => 40, 'notnull' => 1, 'visible' => 0],
        'tms'             => ['type' => 'timestamp', 'label' => 'DateModification', 'enabled' => '1', 'position' => 50, 'notnull' => 0, 'visible' => 0],
        'status'          => ['type' => 'integer', 'label' => 'Status', 'enabled' => '1', 'position' => 60, 'notnull' => 1, 'visible' => 0, 'default' => 1, 'index' => 1, 'arrayofkeyval' => ['0' => 'Draft', '1' => 'Validate']],
        'label'           => ['type' => 'varchar(255)', 'label' => 'Label', 'enabled' => '1', 'position' => 110, 'notnull' => 0, 'visible' => 1, 'searchall' => 1, 'css' => 'minwidth200'],
        'amount_ht'       => ['type' => 'price', 'label' => 'CompetitorPriceHT', 'enabled' => '1', 'position' => 80, 'notnull' => 0, 'visible' => 1, 'default' => 'null', 'isameasure' => '1'],
        'amount_ttc'      => ['type' => 'price', 'label' => 'CompetitorPriceTTC', 'enabled' => '1', 'position' => 90, 'notnull' => 0, 'visible' => 1, 'default' => 'null', 'isameasure' => '1'],
        'vat'             => ['type' => 'varchar(10)', 'label' => 'VAT', 'enabled' => '1', 'position' => 120, 'notnull' => 0, 'visible' => 0],
        'url_competitor'  => ['type' => 'url', 'label' => 'ProductPageURL', 'enabled' => '1', 'position' => 100, 'notnull' => 0, 'visible' => 1, 'cssview' => 'wordbreak'],
        'competitor_date' => ['type' => 'datetime', 'label' => 'CompetitorDate', 'enabled' => '1', 'position' => 70, 'notnull' => 1, 'visible' => 1],
        'fk_product'      => ['type' => 'integer:Product:product/class/product.class.php:1', 'label' => 'Product', 'enabled' => '1', 'position' => 130, 'notnull' => 1, 'visible' => 0, 'index' => 1, 'foreignkey' => 'product.rowid'],
        'fk_soc'          => ['type' => 'integer:Societe:societe/class/societe.class.php:1:((status:=:1) AND (entity:IN:__SHARED_ENTITIES__))', 'label' => 'Competitor', 'enabled' => '1', 'position' => 140, 'notnull' => 1, 'visible' => 1, 'index' => 1, 'foreignkey ' =>  'societe.rowid', 'css' => 'maxwidth500 widthcentpercentminusxx'],
        'fk_user_creat'   => ['type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => '1', 'position' => 150, 'notnull' => 1, 'visible' => 0, 'index' => 1, 'foreignkey' => 'user.rowid'],
        'fk_user_modif'   => ['type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'enabled' => '1', 'position' => 160, 'notnull' => -1, 'visible' => 0],
    ];

    public $rowid;
    public $ref;
    public $entity;
	public $date_creation;
	public $tms;
    public $status;
	public $label;
	public $amount_ht;
	public $amount_ttc;
	public $vat;
	public $url_competitor;
	public $competitor_date;
	public $fk_product;
	public $fk_soc;
	public $fk_user_creat;
	public $fk_user_modif;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             0 < if KO, ID of created object if OK
	 */
	public function create(User $user, bool $notrigger = false): int
	{
        $this->status = 1;
		return $this->createCommon($user, $notrigger);
	}

    /**
     * Load object in memory from the database
     *
     * @param  int|string  $id        ID object
     * @param  string|null $ref       Ref
     * @param  string      $morewhere More SQL filters (' AND ...')
     * @return int                    0 < if KO, 0 if not found, > 0 if OK
     */
    public function fetch($id, string $ref = null, string $morewhere = ''): int
    {
        return $this->fetchCommon($id, $ref, $morewhere);
    }


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND/OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
     * @throws Exception
	 */
	public function fetchAll(string $sortorder = '', string $sortfield = '', int $limit = 0, int $offset = 0, array $filter = array(), string $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = [];

		$sql = 'SELECT ';
		$sql .= $this->getFieldList('t');
		$sql .= ' FROM ' .MAIN_DB_PREFIX.$this->table_element. ' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= ' WHERE t.entity IN (' .getEntity($this->element). ')';
		} else {
			$sql .= ' WHERE 1 = 1';
		}
		// Manage filter
		$sqlwhere = [];
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid' || $key == 't.fk_product' || $key == 't.fk_soc') {
					$sqlwhere[] = $key. ' = ' .((int) $value);
				} elseif (in_array($this->fields[$key]['type'], ['date', 'datetime', 'timestamp'])) {
					$sqlwhere[] = $key." = '".$this->db->idate($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key. ' IN (' .$this->db->sanitize($this->db->escape($value)). ')';
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND (' .implode(' ' .$filtermode. ' ', $sqlwhere). ')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);
			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

    /**
     * Update object into database
     *
     * @param  User $user      User that modifies
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             0 < if KO, >0 if OK
     */
    public function update(User $user, bool $notrigger = false): int
    {
        return $this->updateCommon($user, $notrigger);
    }

    /**
     * Delete object in database
     *
     * @param  User $user      User that deletes
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             0 < if KO, >0 if OK
     */
    public function delete(User $user, bool $notrigger = false): int
    {
        return $this->deleteCommon($user, $notrigger);
    }

    /**
     * Clone an object into another one.
     *
     * @param  User      $user    User that creates
     * @param  int       $fromID  ID of object to clone.
     * @return int                New object created, <0 if KO.
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
    public function setCategories($categories)
    {
        return 1;
    }

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl(int $withpicto = 0, string $option = '', int $notooltip = 0, string $morecss = '', int $save_lastsearch_value = -1): string
	{
		global $action, $conf, $hookmanager, $langs;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

        $pictopath    = dol_buildpath('/custom/priseo/img/priseo_color.png', 1);
        $pictoPriseo  = img_picto('', $pictopath, '', 1, 0, 0, '', 'pictoPriseo');
		$label = $pictoPriseo.' <u>'.$langs->trans('CompetitorPrice').'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/priseo/view/competitorprice_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER['PHP_SELF'])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans('ShowCompetitorPrice');
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

        if ($withpicto) $result .= $pictoPriseo . ' ';
        $result .= $linkstart;
        if ($withpicto != 2) {
            $result .= $this->ref;
        }

        $result .= $linkend;

//		if (empty($this->showphoto_on_popup)) {
//			if ($withpicto) {
//				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
//			}
//		} else {
//			if ($withpicto) {
//				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
//
//				list($class, $module) = explode('@', $this->picto);
//				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
//				$filearray = dol_dir_list($upload_dir, 'files');
//				$filename = $filearray[0]['name'];
//				if (!empty($filename)) {
//					$pospoint = strpos($filearray[0]['name'], '.');
//
//					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
//					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
//						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
//					} else {
//						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
//					}
//
//					$result .= '</div>';
//				} else {
//					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
//				}
//			}
//		}

		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		$hookmanager->initHooks(array('competitorpricedao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut(int $mode = 0): string
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut(int $status, int $mode = 0): string
	{
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			$langs->load("priseo@priseo");
			$this->labelStatus[self::STATUS_DRAFT]     = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');

			$this->labelStatusShort[self::STATUS_DRAFT]     = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
		}

		$statusType = 'status'.$status;

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       ID of object
	 *	@return	void
	 */
	public function info(int $id): void
    {
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql .= ' fk_user_creat, fk_user_modif';
		$sql .= ' FROM ' .MAIN_DB_PREFIX.$this->table_element. ' as t';
		$sql .= ' WHERE t.rowid = ' .($id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
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
	 * Initialise object with example values
	 * ID must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen(): void
    {
		$this->initAsSpecimenCommon();
	}
}
