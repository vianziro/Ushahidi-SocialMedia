<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SocialMedia Messages Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
*
*/

class SocialMedia_Controller extends Admin_Controller {

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'messages';

		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("manage"))
		{
			url::redirect(url::site().'admin/dashboard');
		}
	}

	static private function processSubmit() {
		if ($_POST && !empty($_POST["action"])) {
			$post = Validation::factory($_POST);

			//  Add some filters
			$post->pre_filter('trim', TRUE);

			$action_to_status = array(
					'p'		=> SocialMedia_Message_Model::STATUS_POTENTIAL,
					'n'		=> SocialMedia_Message_Model::STATUS_TOREVIEW, //not spam
					's'		=> SocialMedia_Message_Model::STATUS_SPAM,
					'd'		=> SocialMedia_Message_Model::STATUS_DISCARDED
				);

			$messages = array();

			if (! empty($post->message_single)) {
				$messages[] = $post->message_single;
			} elseif (! empty($post->message_id)) {
				$messages = $post->message_id;
			}

			foreach ($messages as $message) {
				$status = $action_to_status[$post->action];

				$message = ORM::factory("SocialMedia_Message")->find($message);
				$message->updateStatus($status);
			}

			$saved = true;
		}

		return array(
			"error" => false,
			"saved" => false
		);
	}

	public function index()
	{

		$result = self::processSubmit();

		$this->template->content = new View('admin/messages/socialmedia/main');
		$this->template->content->title = Kohana::lang('ui_admin.settings');

		$this->template->content->form_error = $result["error"];
		$this->template->content->form_saved = $result["saved"];

		$review_filter = "`message_level` = " . SocialMedia_Message_Model::STATUS_TOREVIEW . " OR ";
		$review_filter .= "`message_level` = " . SocialMedia_Message_Model::STATUS_INREVIEW;


		$filter = isset($_GET["tab"]) && ! empty($_GET["tab"])
											? "`message_level` = " . $_GET["tab"]
											: $review_filter;
		$this->template->content->tab = $filter;

		$order = socialmedia_helper::getSetting('order');
		if (empty($order)) 
		{
			$order = "ASC";
		}

		// Pagination
		$pagination = new Pagination(array(
			'query_string'   => 'page',
			'items_per_page' => $this->items_per_page,
			'total_items'    => ORM::factory('Socialmedia_Message')
				->join("reporter", "reporter.id", "message.reporter_id")
				->like("message_from", "SocialMedia")
				->where($filter)
				->where("`message_from` LIKE 'SocialMedia%'")
				->count_all()
		));

		$this->template->content->pagination = $pagination;

		$this->template->content->show_images = socialmedia_helper::getSetting("show_images_on_listing");

		$this->template->content->total_items = $pagination->total_items;

		$entries = ORM::factory('Socialmedia_Message')
			->join("reporter", "reporter.id", "message.reporter_id")
			->where($filter)
			->where("`message_from` LIKE 'SocialMedia%'")
			->orderby('message_date', $order)			
			->find_all($this->items_per_page, $pagination->sql_offset);

		$this->template->content->entries = $entries;

		// Counts
		$this->template->content->count_to_review = ORM::factory('Socialmedia_Message')
				->where($review_filter)
				->where("`message_from` LIKE 'SocialMedia%'")
				->like("message_from", "SocialMedia")
				->count_all();

		// Counts
		$this->template->content->count_potential = ORM::factory('Socialmedia_Message')
				->where("message_level", SocialMedia_Message_Model::STATUS_POTENTIAL)
				->where("`message_from` LIKE 'SocialMedia%'")
				->like("message_from", "SocialMedia")
				->count_all();

		// Counts
		$this->template->content->count_reported = ORM::factory('Socialmedia_Message')
				->where("message_level", SocialMedia_Message_Model::STATUS_REPORTED)
				->where("`message_from` LIKE 'SocialMedia%'")
				->like("message_from", "SocialMedia")
				->count_all();

		// Counts
		$this->template->content->count_spam = ORM::factory('Socialmedia_Message')
				->where("message_level", SocialMedia_Message_Model::STATUS_SPAM)
				->where("`message_from` LIKE 'SocialMedia%'")
				->like("message_from", "SocialMedia")
				->count_all();

		// Counts
		$this->template->content->count_discarded = ORM::factory('Socialmedia_Message')
				->where("message_level", SocialMedia_Message_Model::STATUS_DISCARDED)
				->where("`message_from` LIKE 'SocialMedia%'")
				->like("message_from", "SocialMedia")
				->count_all();

		$this->themes->js = new View('admin/messages/socialmedia/main_js');

	}

	public function report($message_id)
	{
		$message = ORM::factory("SocialMedia_Message")->find($message_id);

		if (! $message->loaded) {
			url::redirect(url::site() . 'admin/messages/socialmedia');
		}

		$this->template = null;
		$this->auto_render = FALSE;

		$view = new View('admin/messages/socialmedia/report');

/*		$view->set("incident_description", 	html::specialchars($message->message));
		$view->set("latitude", 		$message->latitude);
		$view->set("longitude", 	$message->longitude);
		$view->set("incident_date", 	date('m/d/Y', $message->original_date));
		$view->set("incident_hour", 	date('h', $message->original_date));
		$view->set("incident_minute", 	date('i', $message->original_date));
		$view->set("incident_ampm", 	date('a', $message->original_date));*/

		$view->set("incident_news", $message->getData("url"));
		$view->set("socialmediaid", 	$message->id);

		// reverse_geocode will return false if latitude or longitude are false
		$view->set("location_name", 	map::reverse_geocode($message->latitude, $message->longitude));

		// the only reason I'm doing this here is because I couldn't get custom fields working
		// this is very fragile as it doesn't ensure the report is being created.
		//$message->updateStatus(SocialMedia_Message_Model::STATUS_REPORTED);

		$view->render(true);
	}


	/**
	 * Generate Report Sub Tab Menus
	 * @param string $this_sub_page
	 * @return string $menu
	 */
	public static function subtabs($this_sub_page = FALSE)
	{
		$menu = "<li><a href='" . url::site() . "admin/settings/socialmedia'" . ($this_sub_page != "main" ? null : " class='active'") . ">General</a></li>";
		$menu .= "<li><a href='" . url::site() . "admin/settings/socialmedia/keywords'" . ($this_sub_page != "keywords" ? null : " class='active'") . ">" . Kohana::lang('ui_admin.keywords') . "</a></li>";


		echo $menu;

		// Action::socialmedia.settings_subtabs - Add items to the social media settings page
		Event::run('socialmedia.settings_subtabs', $this_sub_page);
	}

	public function tool()
	{
		self::processSubmit();

		$this->template->content = new View('admin/messages/socialmedia/tool');
		$this->template->content->title = Kohana::lang('ui_admin.settings');

		$filter = "(`message_level` = " . SocialMedia_Message_Model::STATUS_TOREVIEW . " OR ";
		$filter .= "`message_level` = " . SocialMedia_Message_Model::STATUS_INREVIEW . ") AND";
		$filter .= "`message_from` LIKE 'SocialMedia%'";

		$random_message = ORM::factory("SocialMedia_Message")
					->where($filter)
					//->where("id", 147)
					->orderby("message_level", "ASC")
					->orderby("message_date", "DESC")
					->limit(1)
					->find_all();

		$message = $random_message->as_array();

		//this is poor...
		$message = ORM::factory("Socialmedia_Message")->find($message[0]->id);

		$message->updateStatus(SocialMedia_Message_Model::STATUS_INREVIEW);

		$this->template->content->message = $message;
		$this->template->content->messages_left = ORM::factory("Socialmedia_Message")
													->where($filter)
													->count_all();

		$this->themes->js = new View('admin/messages/socialmedia/tool_js');

		$this->themes->map_enabled = TRUE;
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');
	}
}