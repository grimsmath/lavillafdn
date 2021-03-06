<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * File Celltype Class for EE2
 * 
 * @package   Matrix
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Matrix_file_ft {

	var $info = array(
		'name' => 'File'
	);

	var $default_settings = array(
		'content_type' => 'any',
		'directory'    => 'all'
	);

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		// Load the file_field library
		$this->EE->load->library('file_field');

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['matrix']['celltypes']['file']))
		{
			$this->EE->session->cache['matrix']['celltypes']['file'] = array();
		}
		$this->cache =& $this->EE->session->cache['matrix']['celltypes']['file'];
	}

	/**
	 * Prep Settings
	 */
	private function _prep_settings(&$settings)
	{
		$settings = array_merge($this->default_settings, $settings);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Upload Preferences
	 * @param  int $group_id Member group ID specified when returning allowed upload directories only for that member group
	 * @param  int $id       Specific ID of upload destination to return
	 * @return array         Result array of DB object, possibly merged with custom file upload settings (if on EE 2.4+)
	 */
	private function _get_upload_preferences($group_id = NULL, $id = NULL)
	{
		if (version_compare(APP_VER, '2.4', '>='))
		{
			$this->EE->load->model('file_upload_preferences_model');
			return $this->EE->file_upload_preferences_model->get_file_upload_preferences($group_id, $id);
		}

		if (version_compare(APP_VER, '2.1.5', '>='))
		{
			$this->EE->load->model('file_upload_preferences_model');
			$result = $this->EE->file_upload_preferences_model->get_upload_preferences($group_id, $id);
		}
		else
		{
			$this->EE->load->model('tools_model');
			$result = $this->EE->tools_model->get_upload_preferences($group_id, $id);
		}

		// If an $id was passed, just return that directory's preferences
		if ( ! empty($id))
		{
			return $result->row_array();
		}

		// Use upload destination ID as key for row for easy traversing
		$return_array = array();
		foreach ($result->result_array() as $row)
		{
			$return_array[$row['id']] = $row;
		}

		return $return_array;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($data)
	{
		$this->_prep_settings($data);

		if (version_compare(APP_VER, '2.2', '>='))
		{
			$directory_options['all'] = lang('all');

			$filedirs = $this->_get_upload_preferences(1);

			foreach ($filedirs as $filedir)
			{
				$directory_options[$filedir['id']] = $filedir['name'];
			}

			$r[] = array(
				lang('allowed_dirs_file'),
				form_dropdown('directory', $directory_options, $data['directory'])
			);
		}

		$content_type_options = array('all' => lang('all'), 'image' => lang('type_image'));

		$r[] = array(
			str_replace(' ', '&nbsp;', lang('field_content_file')),
			form_dropdown('content_type', $content_type_options, $data['content_type'])
		);

		$r[] = array(
			'<strong>'.lang('file_ft_configure_frontend').'</strong>',
			'<i class="instruction_text">'.lang('file_ft_configure_frontend_subtext').'</i>'
		);

		$show_existing = isset($data['file_show_existing']) ? $data['file_show_existing'] : '';
		$r[] = array(
			lang('file_ft_show_files'),
			'<label>'.form_checkbox('file_show_existing', 'y', ($show_existing == 'y')).' '.lang('yes').' </label> <i class="instruction_text">('.lang('file_ft_show_files_subtext').')</i>'
		);

		$num_existing = isset($data['file_num_existing']) ? $data['file_num_existing'] : '';
		$r[] = array(
			lang('file_ft_limit_left'),
			form_input('file_num_existing', $num_existing, 'class="center" id="num_existing" style="width: 55px;"').
			NBS.' <strong>'.lang('file_ft_limit_right').'</strong> <i class="instruction_text">('.lang('file_ft_limit_files_subtext').')</i>'
		);

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		$this->_prep_settings($this->settings);

		if (! isset($this->cache['displayed']))
		{
			if (isset($this->var_id))
			{
				// Load the file browser (thanks Rob!)
				$this->EE->file_field->browser();
			}

			// include matrix_text.js
			$theme_url = $this->EE->session->cache['matrix']['theme_url'];
			if (REQ == 'CP')
			{
				$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$theme_url.'scripts/matrix_file.js"></script>');
			}
			else
			{
				$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$theme_url.'scripts/matrix_file_frontend.js"></script>');
			}

			$this->EE->lang->loadfile('matrix');

			$this->cache['displayed'] = TRUE;
		}

		$r['class'] = 'matrix-file';

		// -------------------------------------------
		//  Get the upload directories
		// -------------------------------------------

		$upload_dirs = array();

		$upload_prefs = $this->_get_upload_preferences($this->EE->session->userdata('group_id'));

		foreach ($upload_prefs as $row)
		{
			$upload_dirs[$row['id']] = $row['name'];
		}

		// -------------------------------------------
		//  Existing file?
		// -------------------------------------------

		if ($data)
		{
			if (is_array($data) && ! empty($data['filedir']) && ! empty($data['filename']))
			{
				$filedir = $data['filedir'];
				$filename = $data['filename'];
			}
			else if (is_string($data) && preg_match('/^{filedir_([0-9]+)}(.*)/', $data, $matches))
			{
				$filedir  = $matches[1];
				$filename = $matches[2];
			}
		}

		$existing_file = FALSE;

		if (isset($filedir))
		{
			if (version_compare(APP_VER, '2.1.5', '>='))
			{
				$this->EE->load->library('filemanager');
				$thumb_info = $this->EE->filemanager->get_thumb($filename, $filedir);
				$thumb_url = $thumb_info['thumb'];

				if (! isset($thumb_info['thumb_path']))
				{
					$filedir_info = $this->_get_upload_preferences(1, $filedir);
					$thumb_info['thumb_path'] = $filedir_info['server_path'].'_thumb/'.$filename;
				}

				if (file_exists($thumb_info['thumb_path']))
				{
					$thumb_size = getimagesize($thumb_info['thumb_path']);
				}
				else
				{
					$thumb_url = PATH_CP_GBL_IMG.'default.png';
					$thumb_size = array(64, 64);
				}
			}
			else
			{
				$filedir_info = $this->_get_upload_preferences(1, $filedir);
				$thumb_filename = $filedir_info['server_path'].'_thumbs/thumb_'.$filename;

				if (file_exists($thumb_filename))
				{
					$thumb_url = $filedir_info['url'].'_thumbs/thumb_'.$filename;
					$thumb_size = getimagesize($thumb_filename);
				}
				else
				{
					$thumb_url = PATH_CP_GBL_IMG.'default.png';
					$thumb_size = array(64, 64);
				}
			}

			$r['data'] = '<div class="matrix-thumb" style="width: '.$thumb_size[0].'px;">'
			           .   '<a title="'.lang('remove_file').'"></a>'
			           .   '<img src="'.$thumb_url.'" width="'.$thumb_size[0].'" height="'.$thumb_size[1].'" />'
			           . '</div>'
			           . '<div class="matrix-filename">'.$filename.'</div>';

			$add_style = ' style="display: none;"';
			$existing_file = TRUE;
		}
		else
		{
			$filedir = '';
			$filename = '';
			$r['data'] = '';
			$add_style = '';
		}

		$add_line = ($this->settings['content_type'] != 'image') ? 'add_file' : 'add_image';


		$r['data'] .= '<input type="hidden" name="'.$this->cell_name.'[filedir]"  value="'.$filedir .'" class="filedir" />';
		$r['data'] .= '<input type="hidden" name="'.$this->cell_name.'[filename]"  value="'.$filename .'" class="filename" />';

		if (REQ != 'PAGE')
		{
			$r['data'] .= '<a class="matrix-btn matrix-add"'.$add_style.'>'.$this->EE->lang->line($add_line).'</a>';

		}
		else
		{
			if ($existing_file)
			{
				$r['data'] .= '<a href="#" class="undo_remove">' . lang('file_undo_remove') . '</a><br />';

			}

			$r['data'] .= '<div style="display: inline-block; text-align: left;" class="upload_controls">';
			$r['data'] .= '<input type="file" name="'.$this->cell_name.'" class="file-chooser" /><br />';
			$allowed_filedir = $this->settings['directory'];

			// If we are allowing any filedir, then, by design, we won't show a list of existing files but display a dropdown selector for filedir instead.
			if ($allowed_filedir == 'all')
			{
				// Retrieve all directories that are both allowed for this user and
				// for this field
				$upload_dirs = $this->EE->file_upload_preferences_model->get_dropdown_array($this->EE->session->userdata('group_id'));
				$r['data'] .= form_dropdown($this->cell_name.'[filedir]', $upload_dirs);
			}
			else
			{
				if (isset($this->settings['file_show_existing']) && $this->settings['file_show_existing'] == 'y')
				{
					$options = array(
						'order' => array('upload_date' => 'desc')
					);

					if (isset($this->settings['file_num_existing']) && $this->settings['file_num_existing'])
					{
						$options['limit'] = $this->settings['file_num_existing'];
					}

					$this->EE->load->model('file_model');

					// Load files in from database
					$files_from_db = $this->EE->file_model->get_files(
						empty($this->settings['directory']) ? array() : $this->settings['directory'],
						$options
					);

					$files = array(
						'' => lang('file_ft_select_existing')
					);

					// Put database files into list
					if ($files_from_db['results'] !== FALSE)
					{
						foreach ($files_from_db['results']->result() as $file)
						{
							$files[$file->file_name."|".$file->upload_location_id] = $file->file_name;
						}
					}

					$existing_files = form_dropdown($this->cell_name.'[uploaded_existing]', $files);

					$r['data'] .= '<span clas="existing">'.$existing_files.'</span>';
				}
			}
			$r['data'] .= '</div>';
		}

		$r['data'] .= '<input type="hidden" name="' . $this->cell_name . '[existing]" value="' . $filename . '|' . $filedir . '" class="existing_file"/>';


		// pass along the EE version in the settings
		$r['settings']['ee22plus'] = version_compare(APP_VER, '2.2', '>=');

		if (APP_VER == '2.1.5')
		{
			$this->EE->cp->add_js_script(array(
					'plugin' => array('tmpl')
				)
			);
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate Cell
	 */
	function validate_cell($data)
	{
		// is this a required column?
		if ($this->settings['col_required'] == 'y' && (empty($data['filename']) || empty($data['filedir'])))
		{
			return lang('col_required');
		}

		return TRUE;
	}

	/**
	 * Save Cell
	 */
	function save_cell($data)
	{
		$field_name = $this->settings['field_name'];
		$row_name = $this->settings['row_name'];
		$col_name = $this->settings['col_name'];
		$allowed_filedir = empty($this->settings['directory']) ? 'all' : $this->settings['directory'];

		// Some data mapping
		if (!empty($data['uploaded_existing']))
		{
			$data['existing'] = $data['uploaded_existing'];
		}

		$allowed_filedirs = $this->_get_upload_preferences($this->EE->session->userdata('group_id'));
		foreach ($allowed_filedirs as &$filedir)
		{
			$filedir = $filedir['id'];
		}

		// Use existing data from channel form
		if (!empty($data['existing']) && strpos($data['existing'], '|') && strlen($data['existing']) > 1)
		{
			$parts = explode('|', $data['existing']);
			$data['filedir'] = array_pop($parts);
			$data['filename'] = join("|", $parts); // Just in case there *was* a | in the filename as well.

			// Precaution.
			if ($allowed_filedir != 'all' && !in_array($data['filedir'], $allowed_filedirs))
			{
				return '';
			}
		}
		// Upload new data
		elseif (!empty($_FILES[$field_name]['tmp_name'][$row_name][$col_name]))
		{

			// Set the target filedir.
			if ($allowed_filedir == 'all')
			{
				$target_filedir = $_POST[$field_name][$row_name][$col_name]['filedir'];

				// Nope.
				if (!in_array($target_filedir, $allowed_filedirs))
				{
					return '';
				}
			}
			else
			{
				$target_filedir = $allowed_filedir;
			}

			$_files = $_FILES;

			// Shuffle variables around, so Filemanager can find them.
			foreach ($_FILES[$field_name] as $key => $value)
			{
				$_FILES[$field_name][$key] = $value[$row_name][$col_name];
			}
			$this->EE->load->library('Filemanager');
			$data = $this->EE->filemanager->upload_file($target_filedir, $field_name);

			// Revert back to the state before our dirty manipulations.
			$_FILES = $_files;

			if (array_key_exists('error', $data))
			{
				return '';
			}
			else
			{
				return '{filedir_'.$target_filedir.'}'.$data['file_name'];
			}
		}

		// Check for data passed directly
		if (!empty($data['filename']) && !empty($data['filedir']))
		{
			return $this->EE->file_field->format_data($data['filename'], $data['filedir']);
		}

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Pre-processes the field data for replace_tag().
	 * @param string $data The file path in "{filedir_X}filename.ext" format
	 * @return array Info about the file
	 */
	function pre_process($data)
	{
		return $this->EE->file_field->parse_field($data);
	}

	/**
	 * Replaces a File cell tag.
	 *
	 * @param array $file_info Whatever was returned by pre_process()
	 * @param array $params
	 * @param $tagdata
	 * @return string
	 */
	function replace_tag($file_info, $params = array(), $tagdata = FALSE)
	{
		// Ignore if there's no image
		if (! $file_info) return;

		if (isset($params['raw_output']) && $params['raw_output'] == 'yes')
		{
			return $file_info['raw_output'];
		}

		if (!empty($params['manipulation']))
		{
			$file_info['path'] .= '_'.$params['manipulation'].'/';
		}

		// Make sure we have file_info to work with
		if ($tagdata)
		{
			// Parse legacy {filesize} tags
			if (strpos($tagdata, 'filesize') !== FALSE)
			{
				$file_info['filesize'] = $this->_format_filesize($file_info['upload_location_id'], $file_info['file_size'], array('format' => 'no'));
			}

			// Parse conditionals
			$tagdata = $this->EE->functions->prep_conditionals($tagdata, $file_info);

			// Parse date variables
			$this->file_info = $file_info;
			$tagdata = preg_replace_callback('/'.LD.'(upload_date|modified_date)\s+format=([\'"])(.*?)\2'.RD.'/s', array($this, '_replace_date_tag'), $tagdata);
			unset($this->file_info);

			// Parse any remaining tags
			$tagdata = $this->EE->functions->var_swap($tagdata, $file_info);

			// Backspace param
			if (isset($params['backspace']))
			{
				$tagdata = substr($tagdata, 0, -$params['backspace']);
			}

			return $tagdata;
		}
		else if ($file_info['path'] && !empty($file_info['filename']) && $file_info['extension'] !== FALSE)
		{
			$full_path = $file_info['path'].$file_info['filename'].'.'.$file_info['extension'];

			if (isset($params['wrap']))
			{
				if ($params['wrap'] == 'link')
				{
					return '<a href="'.$full_path.'">'.$file_info['filename'].'</a>';
				}
				elseif ($params['wrap'] == 'image')
				{
					return '<img src="'.$full_path.'" alt="'.$file_info['filename'].'" />';
				}
			}

			return $full_path;
		}
	}

	/**
	 * Replaces a date tag with a formatted date.
	 * @access private
	 * @param array $match
	 * @return string
	 */
	private function _replace_date_tag($match)
	{
		$var = $match[1];
		if (! isset($this->file_info[$var])) return;

		$dvars = $this->EE->localize->fetch_date_params($match[3]);
		if (!$dvars) return;

		$return = $match[3];

		foreach ($dvars as $dvar)
		{
			$formatted_dvar = $this->EE->localize->convert_timestamp($dvar, $this->file_info[$var], TRUE);
			$return = str_replace($dvar, $formatted_dvar, $return);
		}
		
		return $return;
	}

	/**
	 * Replace File Name
	 */
	function replace_filename($file_info)
	{
		return $file_info['file_name'];
	}

	/**
	 * Replace Extension
	 */
	function replace_extension($file_info)
	{
		return $file_info['extension'];
	}

	/**
	 * Replaces a file manipulation tag, e.g. {my_file_col:thumbnail}
	 * @param array  $file_info
	 * @param array  $params
	 * @param string $tagdata
	 * @param string $modifier
	 */
	function replace_tag_catchall($file_info, $params, $tagdata, $modifier)
	{
		$params['manipulation'] = $modifier;
		return $this->replace_tag($file_info, $params, $tagdata);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Filesize
	 */
	private function _format_filesize($upload_dir, $filename, $params)
	{
		$this->EE->db->select('server_path');
		$query = $this->EE->db->get_where('upload_prefs', array('id' => $upload_dir));

		if ($query->num_rows())
		{
			$full_path = $query->row('server_path') . $filename;

			if (file_exists($full_path))
			{
				// get the filesize in bytes
				$filesize = filesize($full_path);

				// unit conversions
				if (isset($params['unit']))
				{
					switch (strtolower($params['unit']))
					{
						case 'kb': $filesize /= 1024; break;
						case 'mb': $filesize /= 1048576; break;
						case 'gb': $filesize /= 1073741824; break;
					}
				}

				// commas
				if (! isset($params['format']) || $params['format'] == 'yes')
				{
					$decimals = isset($params['decimals']) ? $params['decimals'] : 0;
					$dec_point = isset($params['dec_point']) ? $params['dec_point'] : '.';
					$thousands_sep = isset($params['thousands_sep']) ? $params['thousands_sep'] : ',';

					$filesize = number_format($filesize, $decimals, $dec_point, $thousands_sep);
				}

				return $filesize;
			}
		}

		return '';
	}

	/**
	 * Replace File Size
	 */
	function replace_filesize($data, $params = array())
	{
		if (preg_match('/^{filedir_(\d+)}(.*)$/', $data, $matches))
		{
			return $this->_get_filesize($matches[1], $matches[2], $params);
		}

		return '';
	}
}
