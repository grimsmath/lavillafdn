<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dk_server_file_select_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'DK Server File Select',
		'version'	=> '1.0');
	
	function Dk_server_file_select_ft()
	{
		if (version_compare(APP_VER, '2.1.4', '>')) { parent::__construct(); } else { parent::EE_Fieldtype(); } 
	}
	
	// --------------------------------------------------------------------
	
	function display_field($data)
	{
		$files = array();
		
		// open the desired directory
		if (isset($this->settings['server_path']))
		{
			$tmp_files = $this->EE->functions->create_directory_map($this->settings['server_path'], FALSE);
			
			$files[0] = '<-- Select -->';
			
			foreach ($tmp_files as $value)
			{
				$files[$this->settings['server_url'].$value] = $value;
			}
		} 
		else 
		{
			$files[] = 'No files found!';
		}
		
		return form_dropdown($this->field_name, $files, $data);
	}
	
	function display_settings($data)
	{
		$path_data = array(
			'name' => 'server_path',
			'id' => 'server_path',
			'value' => isset($data['server_path']) ? $data['server_path'] : '');

		$url_data = array(
			'name' => 'server_url',
			'id' => 'server_url',
			'value' => isset($data['server_url']) ? $data['server_url'] : '');

		$filter_data = array(
			'name' => 'file_filter',
			'id' => 'file_filter',
			'value' => isset($data['file_filter']) ? $data['file_filter'] : '');			
			
		$this->EE->table->add_row('<strong>Please specify the server path</strong>', form_input($path_data));
		$this->EE->table->add_row('<strong>Please specify the server URL</strong>', form_input($url_data));
		$this->EE->table->add_row('<strong>Please specify any desired filters (REGEX)</strong>', form_input($filter_data));
	}
	
	function save_settings()
	{
		return array(
			'server_path'	=> $this->EE->input->post('server_path'),
			'server_url'	=> $this->EE->input->post('server_url'),
			'file_filter'	=> $this->EE->input->post('file_filter'));
	}
	
	function install() 
	{
		return array(
			'server_path' 	=> '',
			'server_url'	=> '',
			'file_filter'	=> '*');
	}
	
	function uninstall() 
	{
		// nothing to see here
	}	
}
// END Dk_server_file_select_ft class

/* End of file ft.Dk_server_file_select.php */
/* Location: ./system/expressionengine/third_party/Dk_server_file_select_ft/ft.dk_server_file_select.php */