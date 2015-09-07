<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Secure client downloads area
 *
 * @package		Ajw_client_downloads
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		Andrew Weaver
 * @link		http://brandnewbox.co.uk/
 */
class Ajw_client_downloads_mcp
{
	var $base;
	var $form_base;
	var $module_name = "ajw_client_downloads";

	var $settings;

	/*
	
	TODO:
	
	1-) Check MSM compatible (site_id exists, check functionality, x-site tag parameter?)
	2) Look into extra features (eg, member groups assignments)
	
	*/

	function Ajw_client_downloads_mcp( $switch = TRUE ) {

		$this->EE =& get_instance(); 
		$this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;
		$this->form_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;
		$this->theme_url = $this->EE->config->item('theme_folder_url') . 'third_party/ajw_client_downloads/';
		$this->EE->cp->add_to_head('<link type="text/css" rel="stylesheet" href="'. $this->theme_url .'css/ajw_client_downloads.css" />');

		$this->settings = $this->_fetch_settings();

		if( $this->settings !== FALSE ) {
			
			// Global right hand side navigation
			$this->EE->cp->set_right_nav( 
				array(
					'ajw_client_downloads_users' => $this->base . AMP . 'method=users',
					'ajw_client_downloads_assets' => $this->base . AMP . 'method=assets',
					'ajw_client_downloads_folders' => $this->base . AMP . 'method=folders',
					'ajw_client_downloads_reports' => $this->base . AMP . 'method=reports',
					'ajw_client_downloads_settings' => $this->base . AMP . 'method=settings'
				) 
			);

		} else {
			
			// Global right hand side navigation
			$this->EE->cp->set_right_nav( 
				array(
					'ajw_client_downloads_settings' => $this->base . AMP . 'method=settings'
				) 
			);
			
		}

		$this->EE->cp->set_breadcrumb($this->base, $this->EE->lang->line('ajw_client_downloads_module_name') );

		if( $this->settings === FALSE ) {
			
		}

	}

	function index() {
		$data = array();
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ajw_client_downloads_module_name') );
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set data
		$data["content"] = 'index';

		// Fetch settings
		$settings = $this->_fetch_settings();
		if( $settings === FALSE ) {
			$data["content"] = "setup";
			$data["settings_url"] = $this->base . AMP . 'method=settings';
			return $this->EE->load->view('_wrapper', $data, TRUE);
		}
	
		// Fill user array
		$data["users"] = array();
		$this->EE->db->select( "exp_ajw_client_downloads_users.member_id, exp_ajw_client_downloads_users.created, screen_name, count(*) as num_folders, " . implode(", ", $settings["user_fields"] ) );
		$this->EE->db->from( 'exp_ajw_client_downloads_users' );
		$this->EE->db->join( 'exp_members', "exp_ajw_client_downloads_users.member_id = exp_members.member_id" );
		$this->EE->db->join( 'exp_member_data', "exp_members.member_id = exp_member_data.member_id" );
		$this->EE->db->where( 'site_id', $this->EE->config->item('site_id') );
		if( $this->EE->input->get("folder_id") !== FALSE ) {
			$this->EE->db->where( 'folder_id', $this->EE->input->get("folder_id") );
		}
		$this->EE->db->group_by( "exp_ajw_client_downloads_users.member_id" );
		$this->EE->db->order_by( 'exp_ajw_client_downloads_users.created desc' );
		$this->EE->db->limit( 5 );
		
		$query = $this->EE->db->get();
		if( $query->num_rows() > 0 ) {
			$data["users"] = $query->result_array();
		}
		
		foreach( $data["users"] as $idx => $user ) {
			$data["users"][ $idx ][ "date" ] = $this->_date( $user["created"] );
		}

		// Fetch folder name
		$data["display_folder"] = FALSE;
		if( $this->EE->input->get("folder_id") !== FALSE ) {
			$data["display_folder"] = TRUE;
			$this->EE->db->select( "title" );
			$this->EE->db->from( 'exp_ajw_client_downloads_folder' );
			$this->EE->db->where( "id", $this->EE->input->get("folder_id") );
			$query = $this->EE->db->get();
			$data["folder_title"] = $query->row_array();
		}


		$data["assets"] = array();
		
		$this->EE->db->select("exp_ajw_client_downloads_asset.id,
			exp_ajw_client_downloads_asset.title, 
			exp_ajw_client_downloads_asset.path, 
			exp_ajw_client_downloads_asset.created, 
			COUNT( exp_ajw_client_downloads_log.id ) as downloads");
		$this->EE->db->from("exp_ajw_client_downloads_asset");
		$this->EE->db->join("exp_ajw_client_downloads_log", "exp_ajw_client_downloads_log.asset_id = exp_ajw_client_downloads_asset.id", "left outer");
		$this->EE->db->where( 'exp_ajw_client_downloads_asset.site_id', $this->EE->config->item('site_id') );
		
		if( $this->EE->input->get("folder_id") !== FALSE ) {
			$this->EE->db->select("exp_ajw_client_downloads_folder.title as folder_title");
			$this->EE->db->join("exp_ajw_client_downloads_folder_assets", "exp_ajw_client_downloads_folder_assets.asset_id = exp_ajw_client_downloads_asset.id", "left outer");
			$this->EE->db->join("exp_ajw_client_downloads_folder", "exp_ajw_client_downloads_folder_assets.folder_id = exp_ajw_client_downloads_folder.id", "left outer");
			$this->EE->db->where( 'exp_ajw_client_downloads_folder_assets.folder_id', $this->EE->input->get('folder_id') );
		}
		
		$this->EE->db->group_by( 'exp_ajw_client_downloads_asset.id' );
		$this->EE->db->order_by( 'exp_ajw_client_downloads_asset.created desc' );
		$this->EE->db->limit( 5 );

		$query = $this->EE->db->get();
		if( $query->num_rows() > 0 ) {
			$data["assets"] = $query->result_array();
		}
		
		foreach( $data["assets"] as $idx => $asset ) {
			$extension = "none";
			preg_match("/\.([a-zA-Z0-9]{2,4})$/", $asset["path"], $extensions);
			if( isset( $extensions[1] ) ) {
				$extension = $extensions[1];
			}
			$data["assets"][ $idx ][ "extension" ] = $extension;
			$data["assets"][ $idx ][ "size" ] = $this->_size( filesize( $settings["basepath"] . $asset['path'] ) );
			$data["assets"][ $idx ][ "date" ] = $this->_date( $asset["created"] );
		}
		
		// Form data
		$data['base'] = $this->base;
		$data['form_base'] = $this->form_base;
		$data['new_user_url'] = $this->base . AMP . "method=user";
		$data['new_asset_url'] = $this->base . AMP . "method=asset";
		
		
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function users() {
		$data = array();
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ajw_client_downloads_users') );
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set data
		$data["content"] = 'users';

		// Method data
		
		// Fetch settings
		$settings = $this->_fetch_settings();
		
		// Fill user array
		$data["users"] = array();
		$this->EE->db->select( "exp_ajw_client_downloads_users.id, exp_ajw_client_downloads_users.member_id, screen_name, count(*) as num_folders, " . implode(", ", $settings["user_fields"] ) );
		$this->EE->db->from( 'exp_ajw_client_downloads_users' );
		$this->EE->db->join( 'exp_members', "exp_ajw_client_downloads_users.member_id = exp_members.member_id" );
		$this->EE->db->join( 'exp_member_data', "exp_members.member_id = exp_member_data.member_id" );
		$this->EE->db->where( 'site_id', $this->EE->config->item('site_id') );
		if( $this->EE->input->get("folder_id") !== FALSE ) {
			$this->EE->db->where( 'folder_id', $this->EE->input->get("folder_id") );
		}
		$this->EE->db->group_by( "exp_ajw_client_downloads_users.member_id" );
		$query = $this->EE->db->get();
		if( $query->num_rows() > 0 ) {
			$data["users"] = $query->result_array();
		}

		// Fetch folder name
		$data["display_folder"] = FALSE;
		if( $this->EE->input->get("folder_id") !== FALSE ) {
			$data["display_folder"] = TRUE;
			$this->EE->db->select( "title" );
			$this->EE->db->from( 'exp_ajw_client_downloads_folder' );
			$this->EE->db->where( "id", $this->EE->input->get("folder_id") );
			$query = $this->EE->db->get();
			$data["folder_title"] = $query->row_array();
		}
		
		// Get member custom field titles
		$data["titles"] = array(
			"screen_name" => "Screen Name",
			"username" => "Username",
			"email" => "Email",
			"location" => "Location",
			"occupation" => "Occupation"
		);
		$data["fields"] = $settings["user_fields"];
		$field_id = array();
		foreach( $data["fields"] as $idx => $value ) {
			if ( substr( $value, 0, 11 ) == "m_field_id_" ) {
				$field_id[] = substr( $value, 11 );
			}
		}
		if( count( $field_id ) ) {
			$this->EE->db->select( "m_field_id, m_field_label" );
			$this->EE->db->where_in( "m_field_id", $field_id );
			$query = $this->EE->db->get( "exp_member_fields" );
			foreach( $query->result_array() as $row ) {
				$data["titles"][ "m_field_id_".$row["m_field_id"] ] = $row[ "m_field_label" ];
			}
		}
		
		// Form data
		$data['base'] = $this->base;
		$data['form_base'] = $this->form_base;
		$data['new_user_url'] = $this->base . AMP . "method=user";
		
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}
	
	function user( $user_id = NULL ) {
		$data = array();
		
		if( $user_id == NULL ) {
			$user_id = $this->EE->input->get("id");
		}

		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ajw_client_downloads_user_edit') );
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set data
		$data["content"] = 'user';

		// Method data
		$this->EE->db->select("folder_id");
		$this->EE->db->where("member_id", $user_id );
		$query = $this->EE->db->get("exp_ajw_client_downloads_users");
		
		$data["settings"]["folders"] = array();
		foreach( $query->result_array() as $idx => $row ) {
			$data["settings"]["folders"][ $idx ] = $row["folder_id"];
		}
		if( $user_id !== FALSE ) { 
			$data["settings"]["id"] = $user_id;
		}
		
		$sql = "SELECT  m.member_id, m.screen_name FROM exp_members m LEFT OUTER JOIN exp_ajw_client_downloads_users u ON m.member_id = u.member_id WHERE u.id IS NULL ORDER BY screen_name";
		$query = $this->EE->db->query($sql);

		$data["members"] = array();
		foreach( $query->result_array() as $row ) {
			$data["members"][ $row["member_id"] ] = $row["screen_name"];
		}

		$data["folders"] = array();
		$this->EE->db->select("id, title");
		$this->EE->db->where( 'site_id', $this->EE->config->item('site_id') );
		$query = $this->EE->db->get("exp_ajw_client_downloads_folder");
		foreach( $query->result_array() as $row ) {
			$data["folders"][ $row["id"] ] = $row["title"];
		}

		$this->EE->javascript->output(
			array('
			$(function(){

				$("#all, #added").sortable({
					connectWith: ".dnd_list",
					placeholder: "highlight",
					update: function() {
						var order = $("#added").sortable("toArray");
						var added = new Array();
						for( o in order ) {
							added.push(order[o].substr(3));
						}
						$("select#folders").val(added);
						// console.log($("select#folders").val());
					}
				}).disableSelection();

				$("select#folders").hide();

			});
			')
		); 
		$this->EE->javascript->compile();
		
		// Form data
		$data['base'] = $this->base;
		$data['form_action'] = $this->form_base . AMP . "method=update_user";
		
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function update_user() {

		$this->EE->load->library('form_validation');
		$this->EE->form_validation->set_error_delimiters('<p class="notice">', '</p>');
		$this->EE->form_validation->set_rules('folders', 'Folders', 'callback__folder_selected');
		
		if( $this->EE->input->post("id") !== FALSE ) {
			
			if ($this->EE->form_validation->run() === FALSE) {
				return $this->user( $this->EE->input->post("id") );
			} 

			// Update folders
			
			// Get existing folders
			$this->EE->db->select("folder_id");
			$this->EE->db->where("member_id", $this->EE->input->post("id"));
			$query = $this->EE->db->get("exp_ajw_client_downloads_users");
			$existing = array();
			foreach( $query->result_array() as $row ) {
				$existing[] = $row["folder_id"];
			}

			$delete = array_diff( $existing, $this->EE->input->post('folders') );
			$insert = array_diff( $this->EE->input->post('folders'), $existing );
			$update = array_intersect( $this->EE->input->post('folders'), $existing );

			foreach( $insert as $folder_id ) {
				$data = array(
					"site_id" => $this->EE->config->item('site_id'),
					"member_id" => $this->EE->input->post("id"),
					"folder_id" => $folder_id,
					"created" => $this->EE->localize->now
				);
				$this->EE->db->insert('exp_ajw_client_downloads_users', $data);
			}
			
			// Remove old folders
			if( count( $delete ) ) {
				$this->EE->db->where("site_id", $this->EE->config->item('site_id'));
				$this->EE->db->where("member_id", $this->EE->input->post("id"));
				$this->EE->db->where_in("folder_id", $delete );
				$this->EE->db->delete("exp_ajw_client_downloads_users");
			}
			
			$this->EE->functions->redirect( $this->base . AMP . "method=users");
			
		} else {
			
			// Add new user area

			$folders = $this->EE->input->post('folders');

			if ($this->EE->form_validation->run() === FALSE) {
				return $this->user();
			} 

			// Create default folder
			if( trim( $this->EE->input->post("title") ) != "" ) {
				$data = array(
					"site_id" => $this->EE->config->item('site_id'),
					"title" => trim( $this->EE->input->post("title") ),
					"created" => $this->EE->localize->now
				);
				$this->EE->db->insert( "exp_ajw_client_downloads_folder", $data );
				$folders[] = $this->EE->db->insert_id();
			}
			
			// Add user to folders
			foreach( $folders as $folder_id ) {
				$data = array(
					"site_id" => $this->EE->config->item('site_id'),
					"member_id" => $this->EE->input->post("member_id"),
					"folder_id" => $folder_id,
					"created" => $this->EE->localize->now
				);
				$this->EE->db->insert( "exp_ajw_client_downloads_users", $data );
			}
			
			// Email notifcation to new member
			$this->EE->load->library('email');
			$this->EE->load->helper('text'); 

			$message = $this->settings["email_body"];

			if( $message != "" ) {

				// todo: add variables to email message?
				
				$this->EE->db->select( "screen_name, email" );
				$this->EE->db->where( "member_id", $this->EE->input->post("member_id") );
				$query = $this->EE->db->get( "exp_members" );
				if( $query->num_rows() > 0 ) {
					$member = $query->row_array();
					$recipient = $member["email"];

					$this->EE->email->wordwrap = true;
					$this->EE->email->mailtype = 'text';
					if (preg_match( '/(.*)\<(.*)\>/', $this->settings["email_from"], $match)) {
						$this->EE->email->from( trim($match[2]), trim($match[1]) );
					} else {
						$this->EE->email->from( $this->settings["email_from"] );
					}
					$this->EE->email->to( $recipient ); 
					$this->EE->email->subject( $this->settings["email_subject"] );
					$this->EE->email->message( entities_to_ascii( $message ) );
					$this->EE->email->Send();
				}
			}
			
			$this->EE->functions->redirect( $this->base . AMP . "method=users");
		}
	}

	function _folder_selected( $data ) {
		if ( $this->EE->input->post("folders") == FALSE && trim( $this->EE->input->post("title") ) == "" ) {
			$this->EE->form_validation->set_message('_folder_selected', "No folder selected");
			return FALSE;
		}
		return TRUE;
	}

	function delete_user() {

		$vars = array();

		if( $this->EE->input->post('confirm') != 'confirm' ) {

			$data["content"] = 'delete';

			$data["id"] = $this->EE->input->get('id');
			$data["delete_action"] = $this->form_base . AMP . "method=delete_user";

			$data["confirm_message"] = $this->EE->lang->line('ajw_client_downloads_delete_confirm');

			return $this->EE->load->view('_wrapper', $data, TRUE);

		} else {

			$id = $this->EE->input->post('id');

			$this->EE->db->where( "id", $this->EE->input->post('id') );
			$query = $this->EE->db->delete( "exp_ajw_client_downloads_users" );

			$message = $this->EE->lang->line('ajw_client_downloads_delete_success');
			$this->EE->session->set_flashdata('message_success', $message);
			$this->EE->functions->redirect($this->base . AMP . "method=users");
			
		}

	}

	function assets() {
		$data = array();
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ajw_client_downloads_assets') );
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set data
		$data["content"] = 'assets';
		
		$data["settings"] = $this->_fetch_settings();
		
		$data["assets"] = array();
		
		$this->EE->db->select("exp_ajw_client_downloads_asset.id,
			exp_ajw_client_downloads_asset.title, 
			exp_ajw_client_downloads_asset.path, 
			exp_ajw_client_downloads_asset.created, 
			COUNT( exp_ajw_client_downloads_log.id ) as downloads");
		$this->EE->db->from("exp_ajw_client_downloads_asset");
		$this->EE->db->join("exp_ajw_client_downloads_log", "exp_ajw_client_downloads_log.asset_id = exp_ajw_client_downloads_asset.id", "left outer");
		$this->EE->db->where( 'exp_ajw_client_downloads_asset.site_id', $this->EE->config->item('site_id') );
		
		if( $this->EE->input->get("folder_id") !== FALSE ) {
			$this->EE->db->select("exp_ajw_client_downloads_folder.title as folder_title");
			$this->EE->db->join("exp_ajw_client_downloads_folder_assets", "exp_ajw_client_downloads_folder_assets.asset_id = exp_ajw_client_downloads_asset.id", "left outer");
			$this->EE->db->join("exp_ajw_client_downloads_folder", "exp_ajw_client_downloads_folder_assets.folder_id = exp_ajw_client_downloads_folder.id", "left outer");
			$this->EE->db->where( 'exp_ajw_client_downloads_folder_assets.folder_id', $this->EE->input->get('folder_id') );
		}
		
		$this->EE->db->group_by( 'exp_ajw_client_downloads_asset.id' );

		$query = $this->EE->db->get();
		if( $query->num_rows() > 0 ) {
			$data["assets"] = $query->result_array();
		}
		
		foreach( $data["assets"] as $idx => $asset ) {
			$extension = "none";
			preg_match("/\.([a-zA-Z0-9]{2,4})$/", $asset["path"], $extensions);
			if( isset( $extensions[1] ) ) {
				$extension = $extensions[1];
			}
			$data["assets"][ $idx ][ "extension" ] = $extension;
			$data["assets"][ $idx ][ "size" ] = $this->_size( filesize( $data["settings"]["basepath"] . $asset['path'] ) );
			$data["assets"][ $idx ][ "date" ] = $this->_date( $asset["created"] );
		}
		
		// Form data
		$data['base'] = $this->base;
		$data['new_asset_url'] = $this->base . AMP . "method=asset";
		
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function asset( $asset_id = NULL ) {
		$data = array();

		if( $asset_id == NULL ) {
			$asset_id = $this->EE->input->get("id");
		}
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ajw_client_downloads_asset_upload') );
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set data
		$data["content"] = 'asset';
		
		$data["settings"] = array();
		$data["settings"]["folders"] = array();
		if( $asset_id !== FALSE ) {

			$this->EE->db->where("id", $asset_id );
			$query = $this->EE->db->get("exp_ajw_client_downloads_asset");
			$data["settings"] = $query->row_array();
			$data["settings"]["created"] = $this->EE->localize->set_human_time($data["settings"]["created"]);

			$this->EE->db->select("folder_id");
			$this->EE->db->where("asset_id", $asset_id );
			$query = $this->EE->db->get("exp_ajw_client_downloads_folder_assets");

			foreach( $query->result_array() as $idx => $row ) {
				$data["settings"]["folders"][ $idx ] = $row["folder_id"];
			}
			
		}

		$data["files"] = array();
		if ( $handle = opendir( $this->settings["temp_path"] ) ) {
			while ( FALSE !== ( $file = readdir( $handle ) ) ) {
				if( is_file( $this->settings["temp_path"] . $file ) ) {
					$data["files"][ $file ] = $file;
				}
			}
			closedir($handle);
			natcasesort( $data["files"] );
		}

		$data["folders"] = array();
		$this->EE->db->select("id, title");
		$this->EE->db->where( 'site_id', $this->EE->config->item('site_id') );
		$query = $this->EE->db->get("exp_ajw_client_downloads_folder");
		foreach( $query->result_array() as $row ) {
			$data["folders"][ $row["id"] ] = $row["title"];
		}
		
		$this->EE->javascript->output(
			array('
			$(function(){

				$("#all, #added").sortable({
					connectWith: ".dnd_list",
					placeholder: "highlight",
					update: function() {
						var order = $("#added").sortable("toArray");
						var added = new Array();
						for( o in order ) {
							added.push(order[o].substr(3));
						}
						$("select#folders").val(added);
						// console.log($("select#folders").val());
					}
				}).disableSelection();

				$("select#folders").hide();

			});
			')
		); 
		$this->EE->javascript->compile();
		
		// Form data
		$data['base'] = $this->base;
		$data['form_action'] = $this->form_base . AMP . "method=upload_asset";
		
		return $this->EE->load->view('_wrapper', $data, TRUE);	
	}

	function upload_asset() {

		$settings = $this->_fetch_settings();

		$this->EE->load->library('form_validation');
		$this->EE->form_validation->set_error_delimiters('<p class="notice">', '</p>');
		$this->EE->form_validation->set_rules('title', 'Title', 'required');

		if( $this->EE->input->post("id") !== FALSE ) {

			// Update an existing asset

			if ($this->EE->form_validation->run() === FALSE) {
				return $this->asset( $this->EE->input->post("id") );
			}

			// todo: check whether we are replacing the asset with a new one

			$data = array(
				"title" => $this->EE->input->post("title"),
				"keywords" => $this->EE->input->post("keywords"),
				"description" => $this->EE->input->post("description")
				);
			$this->EE->db->where('id', $this->EE->input->post("id") );
			$this->EE->db->update('exp_ajw_client_downloads_asset', $data);
			$asset_id = $this->EE->input->post("id");

				// Get existing folders
				$this->EE->db->select("folder_id");
				$this->EE->db->where("asset_id", $this->EE->input->post("id"));
				$query = $this->EE->db->get("exp_ajw_client_downloads_folder_assets");
				$existing = array();
				foreach( $query->result_array() as $row ) {
					$existing[] = $row["folder_id"];
				}

				if( $this->EE->input->post('folders') !== FALSE ) {
					$folders = $this->EE->input->post('folders');
				} else {
					$folders = array();
				}

				$delete = array_diff( $existing, $folders );
				$insert = array_diff( $folders, $existing );
				$update = array_intersect( $folders, $existing );

				// Add new folders
				foreach( $insert as $folder_id ) {
					$data = array(
						"site_id" => $this->EE->config->item('site_id'),
						"folder_id" => $folder_id,
						"asset_id" => $asset_id,
						"created" => $this->EE->localize->now
						);
					$this->EE->db->insert('exp_ajw_client_downloads_folder_assets', $data);
				}

				// Remove old folders
				if( count( $delete) ) {
					$this->EE->db->where("site_id", $this->EE->config->item('site_id'));
					$this->EE->db->where("asset_id", $asset_id);
					$this->EE->db->where_in("folder_id", $delete );
					$this->EE->db->delete("exp_ajw_client_downloads_folder_assets");
				}


			$this->EE->functions->redirect( $this->base . AMP . "method=assets");

		} else {

			// Add a new asset

			// $this->EE->form_validation->set_rules('file_upload', 'File', 'callback__file_uploaded');

			if ($this->EE->form_validation->run() === FALSE) {
				return $this->asset( $this->EE->input->post("id") );
			}

			// Fetch the file

			if( isset( $_FILES["file_upload"] ) && $_FILES["file_upload"]["name"] != "" ) {

				// Do file upload

				$config['upload_path'] = $settings["basepath"];
				$config['allowed_types'] = '*';

				$this->EE->load->library( 'upload', $config );

				if ( ! $this->EE->upload->do_upload( "file_upload" ) ) {

					// todo: handle this error properly
					print_r( $this->EE->upload->display_errors() ); 
					exit;

				} else {

					$upload = $this->EE->upload->data();

					$data = array(
						"site_id" => $this->EE->config->item('site_id'),
						"title" => $this->EE->input->post("title"),
						"keywords" => $this->EE->input->post("keywords"),
						"description" => $this->EE->input->post("description"),
						"path" => $upload["file_name"],
						"created" => $this->EE->localize->now
						);

				}

			} elseif( $this->EE->input->post("file") !== FALSE ) {

				// Move file from temporary folder
				$target_path = $this->settings["basepath"] . $this->EE->input->post("file");
				rename( $this->settings["temp_path"] . $this->EE->input->post("file"), $target_path );
				
				$data = array(
					"site_id" => $this->EE->config->item('site_id'),
					"title" => $this->EE->input->post("title"),
					"keywords" => $this->EE->input->post("keywords"),
					"description" => $this->EE->input->post("description"),
					"path" => $this->EE->input->post("file"),
					"created" => $this->EE->localize->now
					);

			}

			$this->EE->db->insert('exp_ajw_client_downloads_asset', $data);
			$asset_id = $this->EE->db->insert_id();

			if( $this->EE->input->post('folders') !== FALSE ) {
				foreach( $this->EE->input->post('folders') as $folder_id ) {
					$data = array(
						"site_id" => $this->EE->config->item('site_id'),
						"folder_id" => $folder_id,
						"asset_id" => $asset_id,
						"created" => $this->EE->localize->now
						);
					$this->EE->db->insert('exp_ajw_client_downloads_folder_assets', $data);
				}

			}

			// Send email notification to all members assigned to this folder

			if( $this->EE->input->post("notify_upload") == "y" && 
				$this->settings["new_asset_email_body"] != "" ) {

				$this->EE->db->distinct();
				$this->EE->db->select( "email" );
				$this->EE->db->from( "exp_ajw_client_downloads_asset" );
				$this->EE->db->join( "exp_ajw_client_downloads_folder_assets", "exp_ajw_client_downloads_asset.id = exp_ajw_client_downloads_folder_assets.asset_id" );
				$this->EE->db->join( "exp_ajw_client_downloads_users", " exp_ajw_client_downloads_folder_assets.folder_id = exp_ajw_client_downloads_users.folder_id" );
				$this->EE->db->join( "exp_members", "exp_ajw_client_downloads_users.member_id = exp_members.member_id" );
				$this->EE->db->where( "exp_ajw_client_downloads_asset.id", $asset_id );
				$query = $this->EE->db->get();

				$this->EE->load->library('email');
				$this->EE->load->helper('text'); 

				$this->EE->email->wordwrap = true;
				$this->EE->email->mailtype = 'text';

				$message = $this->settings["new_asset_email_body"];

				// todo: any more variables?

				$message = str_replace( '{title}', $this->EE->input->post("title"), $message);

				foreach( $query->result_array() as $row ) {

					$recipient = $row["email"];

					$this->EE->email->initialize();
					if (preg_match( '/(.*)\<(.*)\>/', $this->settings["new_asset_email_from"], $match)) {
						$this->EE->email->from( trim($match[2]), trim($match[1]) );
					} else {
						$this->EE->email->from( $this->settings["new_asset_email_from"] );
					}
					$this->EE->email->to( $recipient ); 
					$this->EE->email->subject( $this->settings["new_asset_email_subject"] );
					$this->EE->email->message( entities_to_ascii( $message ) );
					$this->EE->email->Send();


				}
			}

			$this->EE->functions->redirect( $this->base . AMP . "method=assets"); 

		}

	}

	function delete_asset() {

		$vars = array();

		if( $this->EE->input->post('confirm') != 'confirm' ) {

			// Change this depending on required action

			$data["content"] = 'delete';

			$data["id"] = $this->EE->input->get('id');
			$data["delete_action"] = $this->form_base . AMP . "method=delete_asset";

			$data["confirm_message"] = $this->EE->lang->line('ajw_client_downloads_delete_asset_confirm');

			return $this->EE->load->view('_wrapper', $data, TRUE);

		} else {

			$id = $this->EE->input->post('id');

			$this->EE->db->where( "id", $this->EE->input->post('id') );
			$query = $this->EE->db->delete( "exp_ajw_client_downloads_asset" );

			$this->EE->db->where( "asset_id", $this->EE->input->post('id') );
			$query = $this->EE->db->delete( "exp_ajw_client_downloads_folder_assets" );
			
			// todo: delete file too?

			$message = $this->EE->lang->line('ajw_client_downloads_delete_success');
			$this->EE->session->set_flashdata('message_success', $message);
			$this->EE->functions->redirect($this->base . AMP . "method=assets");
			
		}

	}

	function folders() {
		$data = array();
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ajw_client_downloads_folders') );
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Round buttons
		$this->EE->javascript->compile(); 

		// Set data
		$data["title"] = "Client Downloads";
		$data["content"] = 'folders';
		
		$data["folders"] = array();
		$this->EE->db->select("exp_ajw_client_downloads_folder.*");
		$this->EE->db->where( 'exp_ajw_client_downloads_folder.site_id', $this->EE->config->item('site_id') );
		if( $this->EE->input->get("member_id") !== FALSE ) {
			$this->EE->db->select("screen_name");
			$this->EE->db->join("exp_ajw_client_downloads_users", "exp_ajw_client_downloads_folder.id = exp_ajw_client_downloads_users.folder_id");
			$this->EE->db->join("exp_members", "exp_ajw_client_downloads_users.member_id = exp_members.member_id");
			$this->EE->db->where( 'exp_ajw_client_downloads_users.member_id', $this->EE->input->get("member_id") );
		}
		$query = $this->EE->db->get( 'exp_ajw_client_downloads_folder' );
		if( $query->num_rows() > 0 ) {
			$data["folders"] = $query->result_array();
		}
		
		// Form data
		$data['base'] = $this->base;
		$data['new_folder_url'] = $this->base . AMP . "method=folder";
		
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function folder( $folder_id = NULL ) {
		$data = array();
		
		if( $folder_id == NULL ) {
			$folder_id = $this->EE->input->get("id");
		}
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ajw_client_downloads_folder_edit') );
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set data
		$data["content"] = 'folder';
		
		$data["settings"] = array();
		
		if( $folder_id !== FALSE ) {
			// Get title, description etc
			$this->EE->db->where("id", $folder_id );
			$query = $this->EE->db->get("exp_ajw_client_downloads_folder");
			$data["settings"] = $query->row_array();
		}
		
		// Get assets in this folder
		$data["settings"]["selected_assets"] = array();
		$this->EE->db->select("asset_id");
		$this->EE->db->where("folder_id", $folder_id);
		$query = $this->EE->db->get("exp_ajw_client_downloads_folder_assets");
		foreach( $query->result_array() as $row ) {
			$data["settings"]["selected_assets"][] = $row["asset_id"];
		}
		
		// Get list of all available assets
		$data["all_assets"] = array();
		$this->EE->db->select("id, title");
		$query = $this->EE->db->get("exp_ajw_client_downloads_asset");
		foreach( $query->result_array() as $row ) {
			$data["all_assets"][ $row["id"] ] = $row["title"];
		}
		
		// Form data
		$data['base'] = $this->base;
		$data['form_action'] = $this->form_base . AMP . "method=update_folder";
		
		$this->EE->javascript->output(
			array('
			$(function(){

				$("#all, #added").sortable({
					connectWith: ".dnd_list",
					placeholder: "highlight",
					update: function() {
						var order = $("#added").sortable("toArray");
						var added = new Array();
						for( o in order ) {
							added.push(order[o].substr(6));
						}
						$("select#assets").val(added);
						console.log($("select#assets").val());
					}
				}).disableSelection();

				$("select#assets").hide();

			});
			')
		); 
		$this->EE->javascript->compile();
		// $this->EE->cp->load_package_js('ajw_client_downloads');
		
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}
	
	function update_folder() {

		$this->EE->load->library('form_validation');
		$this->EE->form_validation->set_error_delimiters('<p class="notice">', '</p>');
		$this->EE->form_validation->set_rules('title', 'Title', 'required');
		
		$data = array(
			"site_id" => $this->EE->config->item('site_id'),
			"title" => $this->EE->input->post("title"),
			"description" => $this->EE->input->post("description")
		);

		if( $this->EE->input->post("id") !== FALSE ) {
			
			if ($this->EE->form_validation->run() === FALSE) {
				return $this->folder( $this->EE->input->post("id") );
			}
			
			$this->EE->db->where('id', $this->EE->input->post("id") );
			$this->EE->db->update('exp_ajw_client_downloads_folder', $data);

			// Update assets

			// Get existing folders
			$this->EE->db->select("asset_id");
			$this->EE->db->where("folder_id", $this->EE->input->post("id"));
			$query = $this->EE->db->get("exp_ajw_client_downloads_folder_assets");
			$existing = array();
			foreach( $query->result_array() as $row ) {
				$existing[] = $row["asset_id"];
			}

			$new = array();
			if( $this->EE->input->post('assets') ) {
				$new = $this->EE->input->post('assets');
			}

			$delete = array_diff( $existing, $new );
			$insert = array_diff( $new, $existing );
			$update = array_intersect( $new, $existing );

			// Add new assets
			foreach( $insert as $asset_id ) {
				$data = array(
					"site_id" => $this->EE->config->item('site_id'),
					"folder_id" => $this->EE->input->post("id"),
					"asset_id" => $asset_id,
					"created" => $this->EE->localize->now
				);
				$this->EE->db->insert('exp_ajw_client_downloads_folder_assets', $data);
			}
			
			// Remove old folders
			if( count( $delete ) ) {
				$this->EE->db->where("site_id", $this->EE->config->item('site_id'));
				$this->EE->db->where("folder_id", $this->EE->input->post("id"));
				$this->EE->db->where_in("asset_id", $delete );
				$this->EE->db->delete("exp_ajw_client_downloads_folder_assets");
			}

		} else {

			if ($this->EE->form_validation->run() === FALSE) {
				return $this->folder();
			}


			$data["created"] = $this->EE->localize->now;
			$this->EE->db->insert('exp_ajw_client_downloads_folder', $data);
			$folder_id = $this->EE->db->insert_id();

			// Add assets
			if( $this->EE->input->post('assets') ) {
				foreach( $this->EE->input->post('assets') as $asset_id ) {
					$data = array(
						"site_id" => $this->EE->config->item('site_id'),
						"folder_id" => $folder_id,
						"asset_id" => $asset_id,
						"created" => $this->EE->localize->now
					);
					$this->EE->db->insert('exp_ajw_client_downloads_folder_assets', $data);
				}
			}
		}

		$this->EE->functions->redirect( $this->base . AMP . "method=folders");

	}

	function delete_folder() {

		$vars = array();

		if( $this->EE->input->post('confirm') != 'confirm' ) {

			$data["title"] = "Delete folder";
			$data["content"] = 'delete';

			$data["id"] = $this->EE->input->get('id');
			$data["delete_action"] = $this->form_base . AMP . "method=delete_folder";

			$data["confirm_message"] = $this->EE->lang->line('ajw_client_downloads_delete_folder_confirm');

			return $this->EE->load->view('_wrapper', $data, TRUE);

		} else {

			$id = $this->EE->input->post('id');

			$this->EE->db->where( "id", $this->EE->input->post('id') );
			$query = $this->EE->db->delete( "exp_ajw_client_downloads_folder" );

			$this->EE->db->where( "folder_id", $this->EE->input->post('id') );
			$query = $this->EE->db->delete( "exp_ajw_client_downloads_folder_assets" );

			$this->EE->db->where( "folder_id", $this->EE->input->post('id') );
			$query = $this->EE->db->delete( "exp_ajw_client_downloads_users" );
			
			$message = $this->EE->lang->line('ajw_client_downloads_delete_success');
			$this->EE->session->set_flashdata('message_success', $message);
			$this->EE->functions->redirect($this->base . AMP . "method=folders");
			
		}

	}

	function reports() {
		$data = array();
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ajw_client_downloads_reports') );
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set data
		$data["content"] = 'reports';
		
		$this->EE->db->select("exp_ajw_client_downloads_log.asset_id, exp_ajw_client_downloads_log.member_id, title, screen_name, exp_ajw_client_downloads_log.created");
		$this->EE->db->from("exp_ajw_client_downloads_log");
		if( $this->EE->input->get("member_id") !== FALSE ) {
			$data["display_member"] = TRUE;
			$this->EE->db->where( 'exp_ajw_client_downloads_log.member_id', $this->EE->input->get("member_id") );
		}
		if( $this->EE->input->get("asset_id") !== FALSE ) {
			$data["display_asset"] = TRUE;
			$this->EE->db->where( 'exp_ajw_client_downloads_log.asset_id', $this->EE->input->get("asset_id") );
		}
		$this->EE->db->join("exp_members", "exp_members.member_id = exp_ajw_client_downloads_log.member_id");
		$this->EE->db->join("exp_ajw_client_downloads_asset", "exp_ajw_client_downloads_asset.id = exp_ajw_client_downloads_log.asset_id");
		$this->EE->db->order_by("exp_ajw_client_downloads_log.created", "desc");
		$query = $this->EE->db->get();
		$data["downloads"] = $query->result_array();
		foreach( $data["downloads"] as $idx => $download ) {
			$data["downloads"][$idx] = $download;
			$data["downloads"][$idx]["time"] = $this->EE->localize->set_human_time($data["downloads"][$idx]["created"]);
		}

		// todo: pagination
		// todo: export

		// Form data
		$data['base'] = $this->base;
		$data['form_base'] = $this->form_base;
		
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function settings() {
		$data = array();
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ajw_client_downloads_settings') );
		
		// Load helpers
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set data
		$data["content"] = 'settings';
		
		// Load current settings
		$data["settings"] = $this->_fetch_settings();

		// Fetch custom member fields
		$data["user_fields"] = array();
		$data["user_fields"]["username"] = "Username";
		$data["user_fields"]["email"] = "Email";
		$data["user_fields"]["url"] = "URL";
		$data["user_fields"]["location"] = "Location";
		$data["user_fields"]["occupation"] = "Occupation";
		
		$this->EE->db->select( 'm_field_id, m_field_label' );
		$this->EE->db->order_by( 'm_field_id' );
		$query = $this->EE->db->get( 'exp_member_fields' );
		
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$data["user_fields"][ "m_field_id_" . $row["m_field_id"] ] = $row["m_field_label"];
			}
		}
		
		// Form data
		$data['base'] = $this->base;
		$data['form_action'] = $this->form_base . AMP . "method=update_settings";
		
		return $this->EE->load->view('_wrapper', $data, TRUE);
	}

	function update_settings() {
		
		$this->EE->load->library('form_validation');
		$this->EE->form_validation->set_error_delimiters('<p class="notice">', '</p>');
		$this->EE->form_validation->set_rules('basepath', 'Basepath', 'required');
		
		if ($this->EE->form_validation->run() === FALSE) {
			return $this->settings();
		}
		

		$data = array(
			"site_id" => $this->EE->config->item('site_id'),
			"basepath" => $this->EE->input->post("basepath"),
			"email_subject" => $this->EE->input->post("email_subject"),
			"email_from" => $this->EE->input->post("email_from"),
			"email_body" => $this->EE->input->post("email_body"),
			"new_asset_email_subject" => $this->EE->input->post("new_asset_email_subject"),
			"new_asset_email_from" => $this->EE->input->post("new_asset_email_from"),
			"new_asset_email_body" => $this->EE->input->post("new_asset_email_body"),
			"temp_path" => $this->EE->input->post("temp_path")
		);

		if( $this->EE->input->post("user_fields") !== FALSE ) {
			$data["user_fields"] = implode(", ", $this->EE->input->post("user_fields") );
		}
		
		
		if( $this->EE->input->post("id") !== FALSE ) {
			$this->EE->db->where('id', $this->EE->input->post("id") );
			$this->EE->db->update('exp_ajw_client_downloads_settings', $data);
		} else {
			$this->EE->db->insert('exp_ajw_client_downloads_settings', $data);
		}

		$this->EE->functions->redirect( $this->base . AMP . "method=settings"); 
		
	}

	/**
	 * Fetch settings from the database
	 *
	 * @return array of settings or boolean FALSE if it fails
	 * @author Andrew Weaver
	 */
	function _fetch_settings() {
		
		$settings = array();
		
		$this->EE->db->where( 'site_id', $this->EE->config->item('site_id') );
		$query = $this->EE->db->get( 'exp_ajw_client_downloads_settings' );
		if( $query->num_rows() > 0 ) {
			$settings = $query->row_array();
			$settings["user_fields"] = explode(", ", $settings["user_fields"] );
		} else {
			$settings = FALSE;
		}
		
		return $settings;
	}

	function _date( $timestamp )
	{
		
		if(date("Ymd", $timestamp) < date("Ymd", strtotime("+1 week")) && date("Ymd", $timestamp) > date("Ymd", strtotime("tomorrow")))
		{
			return "Next ".date("l",$timestamp)." ".date("h:i a",$timestamp);
		}
		else if(date("Ymd", $timestamp) == date("Ymd", strtotime("tomorrow")))
		{
			return "Tomorrow ".date("h:i a",$timestamp);
		}
		else if(date("Ymd", $timestamp) == date("Ymd"))
		{
			return "Today ".date("h:i a",$timestamp);
		}
		else if(date("Ymd", $timestamp) == date("Ymd", strtotime("yesterday")))
		{
			return "Yesterday ".date("h:i a",$timestamp);
		}
		else if(date("Ymd", $timestamp) > date("Ymd", strtotime("-1 week")) && date("Ymd", $timestamp) < date("Ymd", strtotime("tomorrow")))
		{
			return "Last ".date("l",$timestamp)." ".date("h:i a",$timestamp);
		}
		else
		{
			return date("M d, Y h:i a", $timestamp); 
		}
	}

	function _size( $size )
	{
		if($size / 1048576 > 1)
		{
			return round($size / 1048576, 2)." MB";
		}
		if($size / 1024 > 1)
		{
			return round($size / 1024, 2)." KB";
		}
		return round($size, 2)." B";
	}

}

/* End of file mcp.ajw_client_downloads.php */ 
/* Location: ./system/expressionengine/third_party/ajw_client_downloads/mcp.ajw_client_downloads.php */ 