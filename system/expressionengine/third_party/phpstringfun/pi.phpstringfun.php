<?php
  
$plugin_info = array(
  'pi_name' => 'PHPStringFun',
  'pi_version' => '1.1 (EEv2)',
  'pi_author' => 'Engaging.net',
  'pi_author_url' => 'http://www.engaging.net/products/phpstringfun',
  'pi_description' => 'Fun with strings! A gateway to the PHP string functions without using PHP in a template',
  'pi_usage' => Phpstringfun::usage()
);

/*
* EE Syntax changes v1 -> v2
*
* $TMPL -> $this->EE->TMPL
* $this->return_data
*/

class Phpstringfun
{
	var $return_data = "";

	function Phpstringfun()
	{
		// global $this->EE->TMPL; // EEv1 syntax
		$this->EE =& get_instance(); // EEv2 syntax
		
		$fun = $this->EE->TMPL->fetch_param('function');
		$par1 = $this->EE->TMPL->fetch_param('par1');
		$par2 = $this->EE->TMPL->fetch_param('par2');
		$par3 = $this->EE->TMPL->fetch_param('par3');
		$par4 = $this->EE->TMPL->fetch_param('par4');
		$par5 = $this->EE->TMPL->fetch_param('par5');
		$fun_choices = array('addcslashes', 'bin2hex', 'chop', 'chr', 'chunk_split', 'convert_cyr_string', 'convert_uudecode', 'convert_uuencode', 'count_chars', 'crc32', 'crypt', 'echo', 'explode', 'get_html_translation_table', 'hebrev', 'hebrevc', 'html_entity_decode', 'htmlentities', 'htmlspecialchars_decode', 'htmlspecialchars', 'join', 'levenshtein', 'localeconv', 'ltrim', 'md5_file', 'md5', 'metaphone', 'money_format', 'nl_langinfo', 'nl2br', 'number_format', 'ord', 'parse_str', 'print', 'printf', 'quoted_printable_decode', 'quotemeta', 'rtrim', 'setlocale', 'sha1_file', 'sha1', 'similar_text', 'soundex', 'sprintf', 'sscanf', 'str_ireplace', 'str_pad', 'str_repeat', 'str_replace', 'str_rot13', 'str_shuffle', 'str_split', 'str_word_count', 'strcasecmp', 'strchr', 'strcmp', 'strcoll', 'strcspn', 'strip_tags', 'stripcslashes', 'stripos', 'stripslashes', 'stristr', 'strlen', 'strnatcasecmp', 'strnatcmp', 'strncasecmp', 'strncmp', 'strpbrk', 'strpos', 'strrchr', 'strrev', 'strripos', 'strrpos', 'strspn', 'strstr', 'strtok', 'strtolower', 'strtoupper', 'strtr', 'substr_compare', 'substr_count', 'substr_replace', 'substr', 'trim', 'ucfirst', 'ucwords', 'urlencode', 'vfprintf', 'vprintf', 'vsprintf', 'wordwrap');

		if (in_array($fun, $fun_choices))
		{
			if ($par5) { $this->return_data = $fun($this->EE->TMPL->tagdata, $par1, $par2, $par3, $par4, $par5 ); }
			elseif ($par4) { $this->return_data = $fun($this->EE->TMPL->tagdata, $par1, $par2, $par3, $par4 ); }
			elseif ($par3) { $this->return_data = $fun($this->EE->TMPL->tagdata, $par1, $par2, $par3 ); }
			elseif ($par2) { $this->return_data = $fun($this->EE->TMPL->tagdata, $par1, $par2); }
			elseif ($par1) { $this->return_data = $fun($this->EE->TMPL->tagdata, $par1); }
			else { $this->return_data = $fun($this->EE->TMPL->tagdata); }
		}
		else 
		{
			$this->return_data = "ERROR: PHPStringFun can't have fun with '$fun' as its function.";
		}
	}

	//  Plugin Usage
	// ----------------------------------------

	// This function describes how the plugin is used.
	//  Make sure and use output buffering

	function usage()
	{
		ob_start();
		?>See the documentation at http://www.engaging.net/docs/phpstringfun
		<?php
		$buffer = ob_get_contents();

		ob_end_clean();

		return $buffer;
	}
	// END
}