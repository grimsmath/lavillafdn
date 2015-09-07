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
class Ajw_client_downloads {

	var $return_data;
	
	function Ajw_client_downloads() {
		$this->EE =& get_instance();
	}
	
	function folder() {
		
		if ( !$this->_member_id() ) {
			return $this->EE->TMPL->no_results();
		}
		
		$tagdata = $this->EE->TMPL->tagdata;
		
		$this->EE->db->select( "f.id as folder_id, f.title, f.description" );
		$this->EE->db->from( "exp_ajw_client_downloads_folder f" );
		if ( $this->EE->TMPL->fetch_param('override_member') !== "yes" ) {
			$this->EE->db->join( "exp_ajw_client_downloads_users u", "u.folder_id = f.id" );
			$this->EE->db->where( "u.member_id", $this->member_id );
		}
		if( $this->EE->TMPL->fetch_param('folder_id') !== FALSE ) {
			$this->EE->db->where_in( "f.id", $this->EE->TMPL->fetch_param('folder_id') );
		}
		$this->EE->db->where( "f.site_id", $this->EE->config->item('site_id') );
		if ( $this->EE->TMPL->fetch_param('orderby') != FALSE ) {
			$sort = "asc";
			if ( $this->EE->TMPL->fetch_param('sort') !== FALSE ) {
				$sort = $this->EE->TMPL->fetch_param('sort');
			}
			$this->EE->db->order_by( "f." . $this->EE->TMPL->fetch_param('orderby'), $sort );
		}
		if ( is_numeric( $this->EE->TMPL->fetch_param('limit') ) ) {
			$this->EE->db->limit( $this->EE->TMPL->fetch_param('limit') );
		}
		$outer = $this->EE->db->get();

		if ( $outer->num_rows() == 0 ) {
			return $this->EE->TMPL->no_results;
		}

		$download_url = $this->EE->functions->fetch_site_index() . "?ACT=" . $this->EE->functions->fetch_action_id('Ajw_client_downloads', 'download_asset');

		// Outer loop over all folders
		$out ="";
		
		$count = 0;
		foreach ( $outer->result_array() as $row ) {
			$block = $tagdata;

			$block = str_replace( LD."folder_id".RD, $row["folder_id"], $block );
			$block = str_replace( LD."folder_title".RD, $row["title"], $block );
			$block = str_replace( LD."folder_description".RD, $row["description"], $block );

			// Inner loop over assets
			if ( preg_match( "/".LD."assets(.*?)".RD."(.*?)".LD."\/".'assets'.RD."/s", $block, $matches ) ) {

			$params = $this->EE->functions->assign_parameters( $matches['1'] );
			$assets = "";
			$sql = "SELECT a.id, a.title, a.path, a.description, a.keywords, fa.created as assigned, a.created as created, s.basepath FROM exp_ajw_client_downloads_asset a, exp_ajw_client_downloads_folder_assets fa, exp_ajw_client_downloads_settings s WHERE fa.asset_id = a.id AND fa.folder_id =" . $row["folder_id"];
			if ( $this->EE->TMPL->fetch_param('query') != "" && $this->EE->input->get($this->EE->TMPL->fetch_param('query')) != "" )
			{
				$sql .= ' AND CONCAT(a.title, a.description, a.keywords) LIKE "%' . $this->EE->input->get($this->EE->TMPL->fetch_param('query')) . '%"';
			}
			
			// Only show if status is ok 
			// $sql .= " AND fa.status = 'T' ";
			
			if ( isset( $params['orderby'] ) ) {
				$sql .= " ORDER BY " . $params['orderby'];
			} else {
				$sql .= " ORDER BY id";
			}
			if ( isset( $params['sort'] ) ) {
				$sql .= " " . $params['sort'];
			}

			$inner = $this->EE->db->query($sql);
			$total_assets = $inner->num_rows();
			$block = str_replace( LD."total_assets".RD, $total_assets, $block );

			if ( isset( $params['limit'] ) ) {
				$sql .= " LIMIT " . $params['limit'];
			} 
			if ( isset( $params['offset'] ) && $params["offset"] != "") {
				$sql .= " OFFSET " . $params['offset'];
			} 

			$inner = $this->EE->db->query($sql);
			
			$cond = array();
			$cond['no_assets'] = ( $inner->num_rows() > 0) ? 'FALSE' : 'TRUE';
			$block = $this->EE->functions->prep_conditionals( $block, $cond );
			
			$count = 0;
			foreach ( $inner->result_array() as $inner_row ) {
				
				$asset = $matches[2];

				$cond = array();
				$cond['total_results'] = $inner->num_rows();
				$cond['count'] = ++$count;
				$asset = $this->EE->functions->prep_conditionals( $asset, $cond );

				$file = $inner_row["basepath"] . $inner_row["path"];

				// Handle dates
				if (preg_match("/".LD."date\s+format=[\"'](.*?)[\"']".RD."/s", $asset, $match)) {
					$str	= $match['1'];
					$codes	= $this->EE->localize->fetch_date_params( $str );
					foreach ( $codes as $code ) {
						$str	= str_replace( $code, $this->EE->localize->convert_timestamp( $code, $inner_row["created"], TRUE ), $str );
					}
					$asset	= str_replace( $match['0'], $str, $asset );
				}

				// Parse {switch} tag
				if (preg_match("/".LD."switch=[\"'](.*?)[\"']".RD."/", $asset, $match)) {
					$sopt = explode("|", $match[1]);
					$sw = $sopt[($count-1 + count($sopt)) % count($sopt)];
					$asset = preg_replace("/".LD."switch=[\"'](.*?)[\"']".RD."/", $sw, $asset);
				}
				
				$asset = str_replace( LD."count".RD, $count, $asset );
				$asset = str_replace( LD."id".RD, $inner_row["id"], $asset );
				$asset = str_replace( LD."title".RD, $inner_row["title"], $asset );
				$asset = str_replace( LD."description".RD, $inner_row["description"], $asset );
				$asset = str_replace( LD."keywords".RD, $inner_row["keywords"], $asset );
				$asset = str_replace( LD."url".RD, $download_url . "&id=" . $inner_row["id"], $asset );
				$asset = str_replace( LD."prettydate".RD, $this->_date( $inner_row["created"] ), $asset );
				$asset = str_replace( LD."date".RD, $this->EE->localize->set_human_time( $inner_row["created"] ), $asset );
				$asset = str_replace( LD."assigned".RD, $this->EE->localize->set_human_time( $inner_row["assigned"] ), $asset );
				$asset = str_replace( LD."size".RD, $this->_size( filesize( $file ) ), $asset );
				$extension = "none";
				preg_match("/\.([a-zA-Z0-9]{2,4})$/", $file, $extensions);
				if( isset( $extensions[1] ) ) {
					$extension = $extensions[1];
				}
				$asset = str_replace( LD."extension".RD, strtolower($extension), $asset );
				
				$assets .= $asset;
			}	
			
			$block = preg_replace("/".LD."assets(.*?)".RD."(.*?)".LD."\/".'assets'.RD."/s", $assets, $block);		
			}
			
			$out .= $block;
		}
		
		return $out;
	}

	function download_asset() {
	
		// Check the currently logged in user can download this file
		$sql = "SELECT CONCAT( s.basepath, a.path) as file, a.path as title FROM exp_ajw_client_downloads_folder_assets fa LEFT JOIN exp_ajw_client_downloads_asset a ON a.id = fa.asset_id LEFT JOIN exp_ajw_client_downloads_folder f ON f.id = fa.folder_id INNER JOIN exp_ajw_client_downloads_users u ON u.folder_id = f.id INNER JOIN exp_ajw_client_downloads_settings s WHERE u.member_id = " . $this->EE->session->userdata["member_id"]. " AND fa.asset_id = " . $this->EE->db->escape_str($this->EE->input->get("id"));
		$query = $this->EE->db->query($sql);
		
		if ( $query->num_rows() > 0 ) {

			// Log download
			$data = array(
				'asset_id' => $this->EE->input->get("id"),
				'member_id' => $this->EE->session->userdata["member_id"],
				'created' => $this->EE->localize->now
			);
			$this->EE->db->query( $this->EE->db->insert_string('exp_ajw_client_downloads_log', $data) );
			$row = $query->result_array();

			$title = $row[0]["title"];
			$title = substr( $title, strpos( $title, "_" )+1 );

			// Download file
			$this->_send_file( $row[0]["file"], $title );

			//exit;
						
		} else {
			
			// Permission denied
			return $this->EE->output->show_user_error('general', array($this->EE->lang->line('not_authorized')));
			
		}
		
	}
	
	
	// -------------------------------------
	//  Private Functions
	// -------------------------------------	
	
	function _member_id() {
		
		// Check parameters
		if ( is_numeric( $this->EE->TMPL->fetch_param('member_id') ) ) {
			$this->member_id	= $this->EE->TMPL->fetch_param('member_id');
			return TRUE;
		}
		
		// Use current member
		if ( $this->EE->session->userdata('member_id') != '0' ) {
			$this->member_id	= $this->EE->session->userdata('member_id');
			return TRUE;
		}
		
		return FALSE;
	}
	
	function _date( $timestamp ) {
		
		if(date("Ymd", $timestamp) < date("Ymd", strtotime("+1 week")) && date("Ymd", $timestamp) > date("Ymd", strtotime("tomorrow"))) {
			return "Next ".date("l",$timestamp)." ".date("h:i a",$timestamp);
		} else if(date("Ymd", $timestamp) == date("Ymd", strtotime("tomorrow"))) {
			return "Tomorrow ".date("h:i a",$timestamp);
		} else if(date("Ymd", $timestamp) == date("Ymd")) {
			return "Today ".date("h:i a",$timestamp);
		} else if(date("Ymd", $timestamp) == date("Ymd", strtotime("yesterday"))) {
			return "Yesterday ".date("h:i a",$timestamp);
		} else if(date("Ymd", $timestamp) > date("Ymd", strtotime("-1 week")) && date("Ymd", $timestamp) < date("Ymd", strtotime("tomorrow"))) {
			return "Last ".date("l",$timestamp)." ".date("h:i a",$timestamp);
		} else {
			return date("M d, Y h:i a", $timestamp); 
		}
	}
	
	function _size( $size ) {
		if($size / 1048576 > 1) {
			return round($size / 1048576, 2)." MB";
		}
		if($size / 1024 > 1) {
			return round($size / 1024, 2)." KB";
		}
		return round($size, 2)." B";
	}

	function _send_file($path, $filename) {

		$name = basename($path);
		$data = file_get_contents($path);
		
		// todo: discover mime type
		$mime = 'application/octet-stream';

		// Generate the server headers
		if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE)
		{
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Transfer-Encoding: binary");
			header('Pragma: public');
			header("Content-Length: ".strlen($data));
		}
		else
		{
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			header("Content-Length: ".strlen($data));
		}

		exit($data);
	}
	
}

/* End of file mod.ajw_client_downloads.php */ 
/* Location: ./system/expressionengine/third_party/ajw_client_downloads/mod.ajw_client_downloads.php */ 