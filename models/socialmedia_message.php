<?php defined('SYSPATH') or die('No direct script access.');

/**
* Model for Social Media
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @subpackage Models
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Socialmedia_Message_Model extends Message_Model
{
		//protected $has_one = array('author' => 'socialmedia_author');
	protected $belongs_to = array('incident','reporter');
	protected $has_many = array('Socialmedia_Asset', 'Socialmedia_Message_Data');

	var $message_type = "1"; // Inbox, always


	// Database table name
	//protected $table_name = 'socialmedia_message_metadata';

	const STATUS_TOREVIEW 	= 0;
	const STATUS_DISCARDED 	= 1;
	const STATUS_INREVIEW 	= 2;
	const STATUS_POTENTIAL 	= 10;
	const STATUS_REPORTED 	= 11;
	const STATUS_SPAM 		= 100;

	const STATUS_TRUSTED = 1000;

	const CHANNEL_TWITTER = 'twitter';


	public function setMessageId($id) {
		$this->service_messageid = $id;
	}

	public function setMessageFrom($service) {
		$this->message_from = $service;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function setMessageDetail($message) {
		$this->message_detail = $message;
	}

	public function setMessageDate($date) {
		$this->message_date = $date;
	}

	public function setMessageLevel($level) {
		$this->message_level = $level;
	}

	public function setCoordinates($lat, $lon) {
		$this->latitude = $lat;
		$this->longitude = $lon;
	}

	public function getServiceId() {
		return $this->service_id;
	}

	public function getMessageDate() {
		return $this->message_date;
	}

	public function addData($field, $value) {
		if (empty($this->id)) {
			throw new Kohana_Exception("Object needs to be saved before being able to add any data to it");
		}

		$data = ORM::factory("Socialmedia_Message_Data");
		$data->field_name = $field;
		$data->value = $value;
		$data->message_id = $this->id;

		$data->save();
	}


	public function updateStatus($s, $make_spam =true) {
		$this->status = $s;
		$this->save(true);

		if ($make_spam && $s == self::STATUS_SPAM) 
		{
			$this->makeSpam();
		}
	}

	public function save($ignore_auto_spam_check = false) {
		/*if (! $ignore_auto_spam_check) 
		{
			if ($this->author->status == SocialMedia_Author_Model::STATUS_SPAM) 
			{
				$this->status = Socialmedia_Message_Model::STATUS_SPAM;
			}
		}

		if ($this->author->status == SocialMedia_Author_Model::STATUS_TRUSTED) 
		{
			$this->status = Socialmedia_Message_Model::STATUS_POTENTIAL;
		}*/

		//$this->addData("last_updated", time());
		return parent::save();
	}

	public function makeSpam() {
		$this->author->updateStatus(SocialMedia_Author_Model::STATUS_SPAM);

		$messages_from_author = ORM::factory("Socialmedia_Message")
									->where("author_id", $this->author->id)
									->find_all();

		foreach ($messages_from_author as $message)
		{
			$message->updateStatus(self::STATUS_SPAM, false);
		}
	}

	public function addAssets($media) {
		if (empty($this->id)) {
			throw new Kohana_Exception("Object needs to be saved before being able to add any data to it");
		}

		foreach ($media as $type => $objects) 
		{
			foreach ($objects as $url) 
			{
				$media = ORM::factory("Socialmedia_Asset");
				$media->type = $type;
				$media->url = $url;
				$media->message_id = $this->id;
				$media->save();
			}
		}
	}

	public function getData($name) {

		$data = ORM::factory("Socialmedia_Message_Data")
					->where("field_name", $name)
					->where("message_id", $this->id)
					->find();

		return $data->value;
	}




	static function getMessage($message_id, $service_id) {
		return ORM::factory("Socialmedia_Message")
						->join("reporter","reporter.id", "message.reporter_id")
						->where("service_messageid", $message_id)
						->find();
	}


	static function getAuthor($service_id, $id, $first, $last, $email) {
		$reporter = ORM::factory("Reporter")
							->where("service_id", $service_id)
							->where("service_account", $id)
							->find();

		// save author in case they don't exist
		if (! $reporter->loaded) 
		{
			return self::addReporter($service_id, $id, $first, $last, $email);
		}

		return $reporter->id;
	}

	static public function addReporter($service, $id, $first, $last, $email, $level = self::STATUS_TOREVIEW) {
		$reporter = ORM::factory("Reporter");

		$reporter->service_account = $id;
		$reporter->reporter_first = $first;
		$reporter->reporter_last = $last;
		$reporter->reporter_email = $email;
		$reporter->service_id = $service;
		$reporter->level_id = $level;
		$reporter->reporter_date = date("Y-m-d H:i:s");

		$reporter->save();

		return $reporter->id;
	}

}