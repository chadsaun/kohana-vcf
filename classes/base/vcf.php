<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This class creates vcard.
 *
 * @author iFrogz Developers <developers@ifrogz.com>
 * @version 2011-07-06
 * @copyright (c) 2011, Reminderband, Inc. dba iFrogz
 * @package Document
 * @category VCF
 *
 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.5.2
 * @see http://www.imc.org/pdi/vcard-21.txt
 * @see http://www.rfc-editor.org/info/rfc2426
 * @see http://www.ietf.org/rfc/rfc2426.txt
 * @see http://en.wikipedia.org/wiki/VCard
 * @see http://www.hotscripts.com/forums/php/47729-solved-how-create-vcard-photo.html
 * @see http://sourceforge.net/projects/ical4j/forums/forum/368290/topic/3687294
 */
class Base_VCF extends Kohana_Object {

    /**
     * This variable stores the file name for the VCF, which will only be used
     * when saving to disk.
     *
     * @access protected
     * @var string
     */
    protected $file_name;

	/**
	 * This variable stores an array of vCards.
	 *
	 * @access protected
	 * @var array
	 */
	protected $vcards = array();

	/**
	 * This variable stores the end of line character that is used by Window's based files.
	 *
	 * @access protected
	 * @var string
	 */
	protected $eol = "\n";

    /**
    * This variable stores the last error message reported.
    *
    * @access protected
    * @var array
    */
    protected $error = NULL;

	/**
     * This constructor creates an instance of this class.
     *
     * @access public
     * @param array $config                         the configuration array
     */
	public function __construct(Array $config = array()) {
	    $this->file_name = (isset($config['file_name']) && is_string($config['file_name'])) ? $config['file_name'] : '';
	}

	/**
	 * This function generates a new vCard ID.
	 *
	 * @access public
	 * @returns integer								a new vCard's ID
	 */
	public function generate_id() {
		static $next_id = 0;
		while (isset($this->vcards[$next_id])) {
			$next_id++;
		}
		return $next_id;
	}

	/**
	 * This function sets the name for organization and department associated with the
	 * vCard.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $organization					the name of the organization
	 * @param string $department					the name of the department
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.6.4
	 */
	public function set_organization($vcard_id, $organization, $department = '') {
        if (!empty($organization) || !empty($department)) {
		    $this->vcards[$vcard_id]['ORG'][0]['DATA'] = self::prepare_data(array($organization, $department));
		    return TRUE;
	    }
	    return FALSE;
	}

	/**
	 * This function sets the position or job of the object the vCard represents.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $title							the object's title
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.6.1
	 */
	public function set_title($vcard_id, $title) {
	    if (!empty($title)) {
		    $this->vcards[$vcard_id]['TITLE'][0]['DATA'] = self::prepare_data(array($title));
		    return TRUE;
	    }
	    return FALSE;
	}

	/**
	 * This function sets the information concerning the role, occupation, or business
	 * category of the object the vCard represents.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $role							the object's role
	 */
	public function set_role($vcard_id, $role) {
	    if (!empty($role)) {
		    $this->vcards[$vcard_id]['ROLE'][0]['DATA'] = self::prepare_data(array($role));
		    return TRUE;
	    }
	    return FALSE;
	}

	/**
	 * This function sets the formatted text corresponding to the name of the object the
	 * vCard represents.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $name							the name of the object that the vCard
	 * 												represents
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.2.1
	 */
	public function set_formatted_name($vcard_id, $name) {
		if (!empty($name)) {
		    $this->vcards[$vcard_id]['FN'][0]['DATA'] = self::prepare_data(array($name));
		    return TRUE;
	    }
	    return FALSE;
	}

	/**
	 * This function sets the components for the name of the object the vCard represents.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $last_name						the object's last name
	 * @param string $first_name					the object's first name
	 * @param string $prefix						the object's honorable prefix
	 * @param string $suffix						the object's honorable suffix
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.2.2
	 */
	public function set_name($vcard_id, $last_name, $first_name, $prefix = '', $suffix = '') {
	    if (!empty($last_name) || !empty($first_name) || !empty($prefix) || !empty($suffix)) {
		    $this->vcards[$vcard_id]['N'][0]['DATA'] = self::prepare_data(array($last_name, $first_name, $prefix, $suffix));
		    return TRUE;
	    }
	    return FALSE;
	}

	/**
	 * This function sets the descriptive name given instead of or in addition to the one belonging
	 * to a person, place, or thing.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $nickname						the object's nickname
	 */
	public function set_nickname($vcard_id, $nickname) {
	    if (!empty($nickname)) {
		    $this->vcards[$vcard_id]['NICKNAME'][0]['DATA'] = self::prepare_data(array($nickname));
		    return TRUE;
	    }
	    return FALSE;
	}

	/**
	 * This function sets the components of the delivery address for the vCard object.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $street						the street address
	 * @param string $city							the city
	 * @param string $state							the state
	 * @param string $postal						the postal code
	 * @param string $country						the country
	 * @param string $type							the type of address
	 * @throws Kohana_InvalidArgumentException		indicates that the specified type token
	 * 												is not a valid token
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.3.1
	 */
	public function add_address($vcard_id, $street, $city, $state, $postal, $country, Array $types = array('POSTAL')) {
		if (!empty($street) || !empty($city) || !empty($state) || !empty($postal) || !empty($country)) {
		    $index = isset($this->vcards[$vcard_id]['ADR']) ? count($this->vcards[$vcard_id]['ADR']) : 0;
		    $this->vcards[$vcard_id]['ADR'][$index]['ATTRIBUTES'] = self::prepare_attributes($types);
		    $this->vcards[$vcard_id]['ADR'][$index]['DATA'] = self::prepare_data(array($street, $city, $state, $postal, $country));
		    return TRUE;
	    }
	    return FALSE;
	}

	/**
	 * This function sets the telephone number for telephony communication with the object the
	 * vCard represents.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $telephone						the telephone number
	 * @param string $type							the type of telephone number
	 * @throws Kohana_InvalidArgumentException		indicates that the specified type token
	 * 												is not a valid token
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.4.1
	 */
	public function add_telephone($vcard_id, $telephone, Array $types = array('VOICE')) {
		if (!empty($telephone)) {
		    $index = isset($this->vcards[$vcard_id]['TEL']) ? count($this->vcards[$vcard_id]['TEL']) : 0;
		    $this->vcards[$vcard_id]['TEL'][$index]['ATTRIBUTES'] = self::prepare_attributes($types);
	        $this->vcards[$vcard_id]['TEL'][$index]['DATA'] = self::prepare_data(array($telephone));
	        return TRUE;
        }
        return FALSE;
	}

	/**
	 * This function sets the email address for communication with the object the vCard represents.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $email							the email address
	 * @param string $type							the type of email address
	 * @param boolean $preferred					whether it is the preferred email address
	 * @throws Kohana_InvalidArgumentException		indicates that the specified type token
	 * 												is not a valid token
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.4.2
	 */
	public function add_email($vcard_id, $email, Array $types = array('INTERNET')) {
		if (!empty($email)) {
    		if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i', trim($email))) {
                throw new Kohana_InvalidArgument_Exception('Message: Pattern mismatch. Reason: String is not an email address.', array(':vcard_id' => $vcard_id, ':email' => $email));
            }
    		$index = isset($this->vcards[$vcard_id]['EMAIL']) ? count($this->vcards[$vcard_id]['EMAIL']) : 0;
    		$this->vcards[$vcard_id]['EMAIL'][$index]['ATTRIBUTES'] = self::prepare_attributes($types);
    	    $this->vcards[$vcard_id]['EMAIL'][$index]['DATA'] = self::prepare_data(array($email));
    	    return TRUE;
        }
        return FALSE;
	}

	/**
	 * This function sets the URL associated with the object that the vCard refers to.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $url							the URL to be associated
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.7.8
	 */
	public function set_url($vcard_id, $url) {
		if (!empty($url)) {
		    $this->vcards[$vcard_id]['URL'][0]['DATA'] = self::prepare_data(array($url));
		    return TRUE;
	    }
	    return FALSE;
	}

	/**
	 * This function sets an image or photograph information that annotates some aspect of the object
	 * the vCard represents.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $uri							the image's URI
	 * @param string $type							the type of image
	 * @param string $value							the storage value
	 * @throws Kohana_InvalidArgument_Exception		indicates that the specified type token
	 * 												is not a valid token
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.2.4
	 */
	public function set_photo($vcard_id, $uri, $kind = 'BINARY', Array $types = array('JPEG')) {
		if (!empty($uri)) {
    		$kind = strtoupper($kind);
    		if (!in_array($kind, array('BINARY', 'URI'))) {
    			throw new Kohana_InvalidArgument_Exception('Message: Invalid value token. Reason: Token is not enumerated in set.', array(':vcard_id' => $vcard_id));
    		}
    		$attributes = self::prepare_attributes($types);
    		if (strlen($attributes) > 0) {
    			$attributes = ';' . $attributes;
    		}
    		switch ($kind) {
    			case 'BINARY':
    				$contents = @file_get_contents($uri);
    				if ($contents === FALSE) {
    		    		throw new Kohana_FileNotFound_Exception('Message: Unable to load source URI. Reason: The specified URI :uri is bad.', array(':vcard_id' => $vcard_id, ':uri' => $uri));
    				}
    				$this->vcards[$vcard_id]['PHOTO'][0]['ATTRIBUTES'] = 'ENCODING=BASE64' . $attributes;
    				$this->vcards[$vcard_id]['PHOTO'][0]['DATA'] = base64_encode($contents);
    			break;
    			case 'URI':
    				$this->vcards[$vcard_id]['PHOTO'][0]['ATTRIBUTES'] = 'VALUE=URI' . $attributes;
    				$this->vcards[$vcard_id]['PHOTO'][0]['DATA'] = $uri; //self::prepare_data(array($uri));
    			break;
    		}
    		return TRUE;
		}
		return FALSE;
	}

	/**
	 * This function sets the birth date of the object the vCard represents. The value for this property
	 * is a calendar date in a complete representation consistent with ISO 8601.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param string $birthday	    				the date of birth
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.2.5
	 */
	public function set_birthday($vcard_id, $birthday) {
		if (!empty($birthday)) {
		    $this->vcards[$vcard_id]['BDAY'][0]['DATA'] = date('Y-m-d', strtotime($birthday));
		    return TRUE;
	    }
	    return FALSE;
	}

	/**
	 * This function sets any supplemental information or comment that is associated with the vCard.
	 *
	 * @access public
	 * @param integer $vcard_id						the vCard's ID
	 * @param $note									a note
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-vcarddav-vcardrev-12#section-6.7.2
	 */
	public function set_note($vcard_id, $note) {
	    if (!empty($note)) {
		    $this->vcards[$vcard_id]['NOTE'][0]['DATA'] = self::prepare_data(array($note));
		    return TRUE;
	    }
	    return FALSE;
	}

    /**
     * This function returns the last error reported.
     *
     * @access public
     * @return array                            	the last error reported
     */
	public function get_error() {
		return $this->error;
	}

	/**
	 * This function removes all data from the data and error buffers.
	 *
	 * @access public
	 */
	public function clear() {
		$this->vcards = array();
		$this->error = NULL;
	}

	/**
	 * This function returns a count of the number of rows in the data set.
	 *
	 * @access public
	 * @return integer                              the number of rows
	 */
	public function count() {
		$count = count($this->vcards);
		return $count;
	}

	/**
	 * This function checks whether the data array is empty.
	 *
	 * @access public
	 * @return boolean                              whether the data array is empty
	 */
	public function is_empty() {
		return ($this->count() == 0);
	}

	/**
	 * This function outputs the VCF file.
	 *
	 * @access public
	 *
	 * @see http://www.hotscripts.com/forums/php/47729-solved-how-create-vcard-photo.html
	 */
	public function output() {
		$output = $this->render();
		if (empty($this->file_name)) {
			$this->file_name  = date('YmdHis') . '.vcf';
		}
		$uri = preg_split('!(\?.*|/)!', $this->file_name, -1, PREG_SPLIT_NO_EMPTY);
		$file_name = $uri[count($uri) - 1];
		header("Content-Disposition: attachment; filename=\"{$file_name}\"");
    	header('Content-Type: text/x-vcard; charset=utf-8');
		header('Cache-Control: no-store, no-cache');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		echo $output;
		exit();
	}

	/**
	 * This function renders the data as a string.
	 *
	 * @access public
	 * @return string                               the string of imploded data
	 */
	public function render() {
		$buffer = '';
		foreach ($this->vcards as $vcard) {
			$buffer .= 'BEGIN:VCARD' . $this->eol;
			$buffer .= 'VERSION:3.0' . $this->eol;
			foreach ($vcard as $field => $entry) {
				for ($index = 0; $index < count($entry); $index++) {
					$buffer .= $field;
					if (isset($entry[$index]['ATTRIBUTES'])) {
						$buffer .= ';' . $entry[$index]['ATTRIBUTES'];
					}
					$buffer .= ':';
					$buffer .= $entry[$index]['DATA'];
					$buffer .= $this->eol;
				}
			}
			$buffer .= 'END:VCARD' . $this->eol;
			$buffer .= $this->eol;
		}
		return $buffer;
	}

	/**
	 * This function saves the VCF file to disk.
	 *
	 * @access public
	 * @param string $file_name                     the URI for where the VCF file will be stored
	 * @return boolean                              whether the VCF file was saved
	 */
	public function save($file_name = NULL) {
		if (!is_null($file_name)) {
			$this->file_name = $file_name;
		}
		$result = @file_put_contents($this->file_name, $this->render());
		if ($result === FALSE) {
			return FALSE;
		}
		return TRUE;
	}

    /**
     * This function is an alias for VCF::render() and will renders the data as a string when
     * the object is treated like a string, e.g. with PHP's echo and print commands.
     *
     * @access public
     * @return string                               the string of imploded data
     */
    public function __toString() {
        return $this->render();
    }

	/**
	 * This function will create an instance of the VCF class.
	 *
	 * @access public
	 * @static
	 * @param array $config                         the configuration array
	 * @return VCF                                  an instance of the VCF class
	 */
	public static function factory(Array $config = array()) {
		return new VCF($config);
	}

    /**
     * This function will load a VCF file.
     *
     * @access public
     * @static
     * @param array $config                         the configuration array
     * @return VCF                                  an instance of the VCF class containing
     *                                              the contents of the file.
     */
    public static function load($config = array()) {
		return new VCF($config);
	}

	/**
	 * This function prepares the type tokens.
	 *
	 * @access protected
	 * @static
	 * @param array $types							the type tokens
	 * @return string								a concatenated string of type attributes
	 * @throws Kohana_InvalidArgumentException		indicates that the specified type token
	 * 												is not a valid token
	 */
	protected static function prepare_attributes(Array $types) {
		$buffer = array();
		foreach ($types as $type) {
			if (!preg_match('/^[-a-z0-9]+$/i', $type)) {
				throw new Kohana_InvalidArgument_Exception('Message: Invalid type token. Reason: Token is not enumerated in set.', array(':type' => $type));
			}
			$buffer[] = 'type=' . $type;
		}
		return implode(';', $buffer);
	}

	/**
	 * This function prepares the data by escaping any semi-colon in a component of a compound
	 * property value with a Backslash character (ASCII 92).
	 *
	 * @access protected
	 * @static
	 * @param array $data							the data components
	 * @param string $delimiter						the delimiter to be used when concatenating
	 * 												components
	 * @return string								a concatenated string of data components
	 * @throws Kohana_InvalidArgumentException		indicates that the specified data component
	 * 												is not a string
	 */
	protected static function prepare_data(Array $data, $delimiter = ';') {
		$buffer = array();
		foreach ($data as $datum) {
			if (!is_string($datum)) {
				throw new Kohana_InvalidArgument_Exception('Message: Invalid data passed. Reason: The specified data component must be a string.', array(':type' => gettype($datum)));
			}
			$datum = trim("{$datum}");
			//$datum = preg_replace('/:/', '\:', $datum); // colon
			$datum = preg_replace('/;/', '\;', $datum); // semi-colon
			$buffer[] = $datum;
		}
		return implode($delimiter, $buffer);
	}

}
?>