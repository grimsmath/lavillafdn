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
class Ajw_client_downloads_upd {

	var $version        = '1.1'; 
	var $module_name = "Ajw_client_downloads";

	function Ajw_client_downloads_upd( $switch = TRUE ) { 
		$this->EE =& get_instance();
	} 

	/**
	* Installer for the Ajw_client_downloads module
	*/
	function install() {

		$this->EE->load->dbforge();

		$data = array(
			'module_name' 	 => $this->module_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
			);

		$this->EE->db->insert('modules', $data);

		// Install additional tables

		$fields = array(
			'id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE,
				'auto_increment'=> TRUE
			),
			'site_id' => array(
				'type' => 'INT',
				'constraint' => 4, 
				'unsigned' => TRUE,
				'default' => 1
			),
			'member_id' => array(
				'type' => 'int',
				'constraint' => '10',
				'null' => FALSE
			), 
			'folder_id' => array(
				'type' => 'int',
				'constraint' => '10',
				'null' => FALSE
			), 
			'created' => array(
				'type' => 'int',
				'constraint' => '10',
				'null' => FALSE
			)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('ajw_client_downloads_users');

		$fields = array(
			'id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE,
				'auto_increment'=> TRUE
			),
			'site_id' => array(
				'type' => 'INT',
				'constraint' => 4, 
				'unsigned' => TRUE,
				'default' => 1
			),
			'title' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'keywords' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'description' => array(
				'type' => 'text',
				'null' => FALSE
			), 
			'path' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
				), 
			'created' => array(
				'type' => 'int',
				'constraint' => '10',
				'null' => FALSE
			)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('ajw_client_downloads_asset');

		$fields = array(
			'id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE,
				'auto_increment'=> TRUE
			),
			'site_id' => array(
				'type' => 'INT',
				'constraint' => 4, 
				'unsigned' => TRUE,
				'default' => 1
			),
			'title' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'description' => array(
				'type' => 'text',
				'null' => FALSE
			), 
			'created' => array(
				'type' => 'int',
				'constraint' => '10',
				'null' => FALSE
			)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('ajw_client_downloads_folder');

		$fields = array(
			'id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE,
				'auto_increment'=> TRUE
			),
			'site_id' => array(
				'type' => 'INT',
				'constraint' => 4, 
				'unsigned' => TRUE,
				'default' => 1
			),
			'folder_id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE
			),
			'asset_id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE
			),
			'created' => array(
				'type' => 'int',
				'constraint' => '10',
				'null' => FALSE
			)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('ajw_client_downloads_folder_assets');

		$fields = array(
			'id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE,
				'auto_increment'=> TRUE
			),
			'site_id' => array(
				'type' => 'INT',
				'constraint' => 4, 
				'unsigned' => TRUE,
				'default' => 1
			),
			'asset_id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE
			),
			'member_id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE
			),
			'created' => array(
				'type' => 'int',
				'constraint' => '10',
				'null' => FALSE
			)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('ajw_client_downloads_log');

		$fields = array(
			'id' => array(
				'type' => 'int',
				'constraint' => '10',
				'unsigned' => TRUE,
				'auto_increment'=> TRUE
			),
			'site_id' => array(
				'type' => 'INT',
				'constraint' => 4, 
				'unsigned' => TRUE,
				'default' => 1
			),
			'basepath' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'email_subject' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'email_from' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'email_body' => array(
				'type' => 'text',
				'null' => FALSE
			), 
			'new_asset_email_subject' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'new_asset_email_from' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'new_asset_email_body' => array(
				'type' => 'text',
				'null' => FALSE
			),
			'user_fields' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
			), 
			'temp_path' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'null' => FALSE
				)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('ajw_client_downloads_settings');

		// Insert action
		$data = array(
			'class' 	=> 'Ajw_client_downloads',
			'method' 	=> 'download_asset'
		);
		$this->EE->db->insert('actions', $data);

		return TRUE;
	}

	/**
	* Uninstall the Ajw_client_downloads module
*/
	function uninstall() {

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->module_name));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', $this->module_name);
		$this->EE->db->delete('actions');

		$this->EE->db->where('class', $this->module_name.'_mcp');
		$this->EE->db->delete('actions');

		return TRUE;
	}

	/**
	* Update the Ajw_client_downloads module
	* 
	* @param $current current version number
	* @return boolean indicating whether or not the module was updated 
*/

	function update($current = '') {
		
		if ($current == $this->version) {
			return FALSE;
		}
		
		$this->EE->load->dbforge();

		/*
		if ( $current < "1.1" )  {

			// Add a site_id field for MSM sites
			$fields = array(
				'site_id' => array(
					'type' => 'INT',
					'constraint' => 4, 
					'unsigned' => TRUE,
					'default' => 1
				)
			);
			$this->EE->dbforge->add_column('ajw_datagrab', $fields, 'id');
			
		}
		*/
		
		return TRUE;
	}

}

/* End of file upd.ajw_client_downloads.php */ 
/* Location: ./system/expressionengine/third_party/ajw_client_downloads/upd.ajw_client_downloads.php */ 