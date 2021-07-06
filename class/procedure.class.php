<?php
/* Copyright (C) 2019 ATM Consulting <support@atm-consulting.fr>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!class_exists('SeedObject'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call or for session timeout on our module page
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}

dol_include_once('workstation/class/workstation.class.php');

class Procedure extends SeedObject
{
    /**
     * Canceled status
     */
    const STATUS_CANCELED = -1;
    /**
     * Draft status
     */
    const STATUS_DRAFT = 0;
	/**
	 * Validated status
	 */
	const STATUS_VALIDATED = 1;
	/**
	 * Refused status
	 */
	const STATUS_REFUSED = 3;
	/**
	 * Accepted status
	 */
	const STATUS_ACCEPTED = 4;

	/** @var array $TStatus Array of translate key for each const */
	public static $TStatus = array(
		self::STATUS_DRAFT => 'Disabled'
		,self::STATUS_VALIDATED => 'Enabled'
	);

	/** @var string $table_element Table name in SQL */
	public $table_element = 'procedure';

	/** @var string $element Name of the element (tip for better integration in Dolibarr: this value should be the reflection of the class name with ucfirst() function) */
	public $element = 'procedure';

	/** @var int $isextrafieldmanaged Enable the fictionalises of extrafields */
    public $isextrafieldmanaged = 1;

    /** @var int $ismultientitymanaged 0=No test on entity, 1=Test with field entity, 2=Test with link by societe */
    public $ismultientitymanaged = 1;

    /** @var int @var workstation  */
    public $fk_workstation = 0;

	/** @var string $note_public */
	public $note_public;

	/** @var string $note_private */
	public $note_private;

	/** @var int $fk_user_modif */
	public $fk_user_modif;

	/** @var int $fk_processrules */
	public $fk_processrules;

	/** @var int $fk_procedure_type */
	public $fk_procedure_type;


	/** @var string $picto a picture file in [@...]/img/object_[...@].png  */
	public $picto = 'procedure@processrules';


	/**
     *  'type' is the field format.
     *  'label' the translation key.
     *  'enabled' is a condition when the field must be managed.
     *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). Using a negative value means field is not shown by default on list but can be selected for viewing)
     *  'noteditable' says if field is not editable (1 or 0)
     *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
     *  'default' is a default value for creation (can still be replaced by the global setup of default values)
     *  'index' if we want an index in database.
     *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
     *  'position' is the sort order of field.
     *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
     *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
     *  'css' is the CSS style to use on field. For example: 'maxwidth200'
     *  'help' is a string visible as a tooltip on field
     *  'comment' is not used. You can store here any text of your choice. It is not used by application.
     *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
     *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
     */

    public $fields = array(

        'entity' => array(
            'type' => 'integer',
            'label' => 'Entity',
            'enabled' => 1,
            'visible' => 0,
            'default' => 1,
            'notnull' => 1,
            'index' => 1,
            'position' => 20
        ),

        'status' => array(
            'type' => 'integer',
            'label' => 'Status',
            'enabled' => 1,
            'visible' => 0,
            'notnull' => 1,
            'default' => 0,
            'index' => 1,
            'position' => 30,
            'arrayofkeyval' => array(
                0 => 'Draft',
                1 => 'Active',
//                -1 => 'Canceled'
            )
        ),

		'fk_workstation' => array(
			'type' => 'integer', // workstation use PDODB so isn't compatible with dolibarr std
			'label' => 'Workstation',
			'visible' => 1,
			'enabled' => 1,
			'position' => 30,
			'index' => 1,
			'notnull' => 1,
			'help' => 'ProcedureWorkstationHelp'
		),

		'label' => array(
			'type' => 'varchar(255)',
			'label' => 'Label',
			'enabled' => 1,
			'visible' => 1,
			'position' => 40,
			'searchall' => 1,
			'css' => 'minwidth200',
			'showoncombobox' => 1
		),

        'fk_processrules' => array(
			'type' 		=> 'integer:processrules:processrules/class/processrules.class.php',
			'label'		=> 'processRules',
			'visible' 	=> 1,
			'notnull' 	=> 1,
			'enabled'	=> 1,
			'position'	=> 50,
			'index'		=> 1
        ),


		'fk_procedure_type' => array(
			'type' => 'sellist:c_procedure_type:label:rowid::active=1',
			'label' => 'Type',
			'visible' =>  0, // désactivation du dico "type" au profit de l'utilisation du module workstation. PS: on garde au cas où car "type" c'est générique
			'enabled' => 0, // désactivation du dico "type" au profit de l'utilisation du module workstation. PS: on garde au cas où car "type" c'est générique
			'position' => 30,
			'index' => 1,
			'help' => 'DictionnaryProcedureTypeHelp'
		),

        'description' => array(
            'type' => 'html', // or html for WYSWYG
            'label' => 'Description',
            'enabled' => 1,
            'visible' => -1, //  un bug sur la version 9.0 de Dolibarr necessite de mettre -1 pour ne pas apparaitre sur les listes au lieu de la valeur 3
            'position' => 60
        ),

        'rang' => array(
        	'type' => 'integer',
        	'label' => 'rang',
        	'visible' => 0,
        	'enabled' => 1,
        	'default' => 1,
            'notnull' => 1,
            'comment' => 'the procedure must be sortable in processRules'
        ),

		'note_public' => array(
			'type' => 'text', // or text
			'label' => 'NotePublic',
			'enabled' => 1,
			'visible' => 0,
			'position' => 100
		),

		'note_private' => array(
			'type' => 'text', // or text
			'label' => 'NotePrivate',
			'enabled' => 1,
			'visible' => 0,
			'position' => 100
		),

        'fk_user_modif' =>array(
            'type' => 'integer',
            'label' => 'UserValidation',
            'enabled' => 1,
            'visible' => 0,
            'position' => 512
        ),

        'import_key' => array(
            'type' => 'varchar(14)',
            'label' => 'ImportId',
            'enabled' => 1,
            'visible' => -2,
            'notnull' => -1,
            'index' => 0,
            'position' => 1000
        ),

    );

    /** @var int $entity Object entity */
	public $entity;

	/** @var int $status Object status */
	public $status;

	/** @var string $label Object label */
	public $label;

    /** @var string $description Object description */
    public $description;

    /** @var int $rang */
    public $rang;



    /**
     * processRules constructor.
     * @param DoliDB    $db    Database connector
     */
    public function __construct($db)
    {
		global $conf;

        parent::__construct($db);

		$this->init();

		$this->status = self::STATUS_DRAFT;
		$this->entity = $conf->entity;
    }

	/**
	 *	Get object and children from database
	 *
	 *	@param      int			$id       		Id of object to load
	 * 	@param		bool		$loadChild		used to load children from database
	 *  @param      string      $ref            Ref
	 *	@return     int         				>0 if OK, <0 if KO, 0 if not found
	 */
	public function fetch($id, $loadChild = true, $ref = null)
	{
		$res = parent::fetch($id, $loadChild, $ref);
		$this->statut = $this->status; // Somme test of dolibarr are made on $this->statut instead of $this->status
		return $res;
	}

    /**
     * @param User $user User object
     * @return int
     */
    public function save($user)
    {

        if (empty($this->id)) $this->rang = $this->getMaxRang();

        return $this->create($user);
    }

    public function getMaxRang()
	{
		$sql = "SELECT MAX(rang) as m, count(rang) as nb FROM ".MAIN_DB_PREFIX.$this->table_element." WHERE fk_processrules = ".$this->fk_processrules;
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$obj = $this->db->fetch_object($resql);
			if ($obj->nb > 0)
			{
				return intval($obj->m) + 1;
			}
		}

		return 0;
	}


    /**
     * @param User $user User object
     * @return int
     */
    public function delete(User &$user, $notrigger = false)
	{
        $this->deleteObjectLinked();

		$this->fetch_lines();
		if (!empty($this->lines))
		{
			foreach ($this->lines as $line) $line->delete($user, $notrigger);
		}

        unset($this->fk_element); // avoid conflict with standard Dolibarr comportment
        return parent::delete($user, $notrigger);
    }

	/**
	 * @param User $user object
	 * @return int
	 */
	public function cloneObject($user, $notrigger = false)
	{
		$this->fetch_lines();

		$this->clear();
		$this->status = 0;

		$newID = $this->create($user);

		if ($newID > 0)
		{
			foreach ($this->lines as $line)
			{
				$line->fk_procedure = $newID;

				$line->cloneObject($user, $notrigger);
			}
		}

		return $newID;
	}

	/**
	 * get all ProcessSteps link to the procedure
	 */
	public function fetch_lines()
	{
		$this->lines = array();

		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."processstep WHERE fk_procedure = ".$this->id." ORDER BY rang ASC";
		$resql = $this->db->query($sql);

		if ($resql)
		{
			$num = $this->db->num_rows($resql);

			if ($num)
			{
				dol_include_once('/processrules/class/processstep.class.php');

				while ($obj = $this->db->fetch_object($resql))
				{
					$step = new ProcessStep($this->db);
					$ret = $step->fetch($obj->rowid);
					if ($ret > 0) $this->lines[] = $step;
				}
			}

			return $num;
		}
		else
		{
			$this->error = $this->db->lasterror;
			return -1;
		}
	}




    /**
     * @param User  $user   User object
     * @return int
     */
    public function setDraft($user)
    {
        if ($this->status === self::STATUS_VALIDATED)
        {
            $this->status = self::STATUS_DRAFT;
            $this->withChild = false;

            return $this->update($user);
        }

        return 0;
    }

    /**
     * @param User  $user   User object
     * @return int
     */
    public function setValid($user)
    {
        if ($this->status === self::STATUS_DRAFT)
        {
            $this->status = self::STATUS_VALIDATED;
            $this->withChild = false;

            return $this->update($user);
        }

        return 0;
    }

    /**
     * @param User  $user   User object
     * @return int
     */
    public function setAccepted($user)
    {
        if ($this->status === self::STATUS_VALIDATED)
        {
            $this->status = self::STATUS_ACCEPTED;
            $this->withChild = false;

            return $this->update($user);
        }

        return 0;
    }

    /**
     * @param User  $user   User object
     * @return int
     */
    public function setRefused($user)
    {
        if ($this->status === self::STATUS_VALIDATED)
        {
            $this->status = self::STATUS_REFUSED;
            $this->withChild = false;

            return $this->update($user);
        }

        return 0;
    }

    /**
     * @param User  $user   User object
     * @return int
     */
    public function setReopen($user)
    {
        if ($this->status === self::STATUS_ACCEPTED || $this->status === self::STATUS_REFUSED)
        {
            $this->status = self::STATUS_VALIDATED;
            $this->withChild = false;

            return $this->update($user);
        }

        return 0;
    }


    /**
     * @param int    $withpicto     Add picto into link
     * @param string $moreparams    Add more parameters in the URL
     * @return string
     */
    public function getNomUrl($withpicto = 0, $moreparams = '')
    {
		global $langs;

		$name = $this->getNom();

        $result='';
        $label = '<u>' . $langs->trans("Showprocedure") . '</u>';
        $label.= '<br><b>'.$langs->trans('Workstation').':</b> '.$name;

		$url = dol_buildpath('/processrules/procedure_card.php', 1).'?id='.$this->id.urlencode($moreparams);
        $link = '<a class="classfortooltip clickable" href="'.$url.'" title="'.dol_escape_htmltag($label, 1).'" >';



		$result.= '<a class="classfortooltip clickable" href="'.$url.'" title="'.dol_escape_htmltag($label, 1).'" >';

		if ($withpicto){
			$result.=img_object('', $this->picto, 'class="paddingright valignmiddle clickable " ');
		}
		if ($withpicto && $withpicto != 2) $result.=' ';

		$result.= $name;
        $result.= '</a>';

        return $result;
    }

	/**
	 * @return string
	 */
	public function getNom()
	{
		global $langs;

		$PDOdb = new TPDOdb($this->db);

		$TWorkstation = new TWorkstation();
		$TWorkstation->load($PDOdb, $this->fk_workstation);

		$name =  $TWorkstation->name;
		if(!empty($this->label)){
			$name.= ' - '.$this->label;
		}

		return $name;
	}

    /**
     * @param int       $id             Identifiant
     * @param null      $ref            Ref not used
     * @param int       $withpicto      Add picto into link
     * @param string    $moreparams     Add more parameters in the URL
     * @return string
     */
    public static function getStaticNomUrl($id, $ref = null, $withpicto = 0, $moreparams = '')
    {
		global $db;

		$object = new Procedure($db);
		$object->fetch($id, false, $ref);

		return $object->getNomUrl($withpicto, $moreparams);
    }


    /**
     * @param int $mode     0=Long label, 1=Short label, 2=Picto + Short label, 3=Picto, 4=Picto + Long label, 5=Short label + Picto, 6=Long label + Picto
     * @return string
     */
    public function getLibStatut($mode = 0)
    {
    	return '';
        //return self::LibStatut($this->status, $mode);
    }

    /**
     * @param int       $status   Status
     * @param int       $mode     0=Long label, 1=Short label, 2=Picto + Short label, 3=Picto, 4=Picto + Long label, 5=Short label + Picto, 6=Long label + Picto
     * @return string
     */
    public static function LibStatut($status, $mode)
    {
		global $langs;

		$langs->load('processrules@processrules');
        $res = '';

        if ($status==self::STATUS_DRAFT) { $statusType='status0'; $statusLabel=$langs->trans('Disabled'); $statusLabelShort=$langs->trans('Disabled'); }
        else { $statusType='status1'; $statusLabel=$langs->trans('Enabled'); $statusLabelShort=$langs->trans('Enabled'); }

        if (function_exists('dolGetStatus'))
        {
            $res = dolGetStatus($statusLabel, $statusLabelShort, '', $statusType, $mode);
        }
        else
        {
            if ($mode == 0) $res = $statusLabel;
            elseif ($mode == 1) $res = $statusLabelShort;
            elseif ($mode == 2) $res = img_picto($statusLabel, $statusType).$statusLabelShort;
            elseif ($mode == 3) $res = img_picto($statusLabel, $statusType);
            elseif ($mode == 4) $res = img_picto($statusLabel, $statusType).$statusLabel;
            elseif ($mode == 5) $res = $statusLabelShort.img_picto($statusLabel, $statusType);
            elseif ($mode == 6) $res = $statusLabel.img_picto($statusLabel, $statusType);
        }

        return $res;
    }

	/**
	 * Return HTML string to put an input field into a page
	 * Code very similar with showInputField of extra fields
	 *
	 * @param  array   		$val	       Array of properties for field to show
	 * @param  string  		$key           Key of attribute
	 * @param  string  		$value         Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
	 * @param  string  		$moreparam     To add more parameters on html input tag
	 * @param  string  		$keysuffix     Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string  		$keyprefix     Suffix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string|int	$morecss       Value for css to define style/length of field. May also be a numeric.
	 * @return string
	 */
	public function showInputField($val, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = 0)
	{
		global $conf, $langs, $form, $db;

		if($key == 'fk_workstation'){

			dol_include_once('workstation/class/workstation.class.php');

			$PDOdb = new TPDOdb;

			$TWorkstation = TWorkstation::getWorstations($PDOdb, false, true);
			$out = $form->selectArray('fk_workstation', $TWorkstation, $value, 0, 0, 0, '', 0, 0, 0, '', '', 1);

		}
		else{
			$out = parent::showInputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
		}

		return $out;
	}

	/**
	 * Return HTML string to show a field into a page
	 * Code very similar with showOutputField of extra fields
	 *
	 * @param  array   $val		       Array of properties of field to show
	 * @param  string  $key            Key of attribute
	 * @param  string  $value          Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
	 * @param  string  $moreparam      To add more parametes on html input tag
	 * @param  string  $keysuffix      Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string  $keyprefix      Suffix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  mixed   $morecss        Value for css to define size. May also be a numeric.
	 * @return string
	 */
	public function showOutputField($val, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = '')
	{
		global $conf, $langs, $form;

		// TODO : quite a fixer autant le faire dans le seed object
		// patch for dolibarr 9.0 show PR : https://github.com/Dolibarr/dolibarr/pull/11571
		if(preg_match('/^sellist:(.*):(.*):(.*):(.*)/i', $val['type'], $reg)) {
			$val['param']['options'] = array($reg[1] . ':' . $reg[2] . ':' . $reg[3] . ':' . $reg[4] => 'N');
			$val['type'] = 'sellist';
		}

		if($key == 'fk_workstation'){
			$PDOdb = new TPDOdb;

			$TWorkstation = new TWorkstation();
			$TWorkstation->load($PDOdb, $value);
			$out = '<a class="clickable" href="'.dol_buildpath('/workstation/workstation.php?action=view&id='.$TWorkstation->getId(),1).'" >'.img_picto('', 'object_generic').$TWorkstation->name.'</a>';

		}
		else{
			$out = parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
		}



		return $out;
	}

	/**
	 * Return HTML string to show a field into a page
	 * Code very similar with showOutputField of extra fields
	 *
	 * @param  string  $key            Key of attribute
	 * @param  string  $moreparam      To add more parametes on html input tag
	 * @param  string  $keysuffix      Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string  $keyprefix      Suffix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  mixed   $morecss        Value for css to define size. May also be a numeric.
	 * @return string
	 */
	public function showFieldValue($key, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = '')
	{
		return $this->showOutputField($this->fields[$key], $key, $this->{$key}, $moreparam, $keyprefix, $morecss);
	}

}
