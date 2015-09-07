<?php echo form_open( $form_action ); ?>

<?php 

if( isset( $settings["id"] ) ) {
	echo form_hidden("id", $settings["id"] );
}

$this->table->set_template($cp_table_template);
$this->table->set_heading(lang('settings'), lang('value'));

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => lang('ajw_client_downloads_paths'),
		'class' => 'box'
	)
);

$this->table->add_row(
	'<em class="required">*</em> ' .
	form_label(lang('ajw_client_downloads_basepath'), 'basepath') .
		'<div class="subtext">' . lang('ajw_client_downloads_basepath_info') . '</div>' . 
		form_error("basepath"),
	form_input("basepath",  
		isset($settings["basepath"]) ? $settings["basepath"] : '' )
);

$this->table->add_row(
	form_label(lang('ajw_client_downloads_temppath'), 'temp_path') .
	'<div class="subtext">'. lang('ajw_client_downloads_temppath_info') . '</div>', 
	form_input("temp_path",  
		isset($settings["temp_path"]) ? $settings["temp_path"] : '' )
);

echo $this->table->generate();
echo $this->table->clear();

$this->table->set_template($cp_table_template);
$this->table->set_heading(lang('settings'), lang('value'));

// Email notification for new download area

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => lang('ajw_client_downloads_new_download_info'),
		'class' => 'box'
	)
);

$this->table->add_row(
	form_label(lang('ajw_client_downloads_new_download_subject'), 'email_subject'), 
	form_input("email_subject",  
		isset($settings["email_subject"]) ? $settings["email_subject"] : '' )
);

$this->table->add_row(
	form_label(lang('ajw_client_downloads_new_download_from'), 'email_from'), 
	form_input("email_from",  
		isset($settings["email_from"]) ? $settings["email_from"] : '' )
);

$this->table->add_row(
	form_label(lang('ajw_client_downloads_new_download_message'), 'email_body'), 
	form_textarea("email_body",  
		isset($settings["email_body"]) ? $settings["email_body"] : '' )
);

echo $this->table->generate();
echo $this->table->clear();

// Email notification for new asset uploaded

$this->table->set_template($cp_table_template);
$this->table->set_heading(lang('settings'), lang('value'));

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => lang('ajw_client_downloads_new_asset_info'),
		'class' => 'box'
	)
);

$this->table->add_row(
	form_label(lang('ajw_client_downloads_new_asset_subject'), 'new_asset_email_subject'), 
	form_input("new_asset_email_subject",  
		isset($settings["new_asset_email_subject"]) ? $settings["new_asset_email_subject"] : '' )
);

$this->table->add_row(
	form_label(lang('ajw_client_downloads_new_asset_from'), 'new_asset_email_from'), 
	form_input("new_asset_email_from",  
		isset($settings["new_asset_email_from"]) ? $settings["new_asset_email_from"] : '' )
);

$this->table->add_row(
	form_label(lang('ajw_client_downloads_new_asset_message'), 'new_asset_email_body') .
	'<div class="subtext">' . lang('ajw_client_downloads_new_asset_message_info') . '</div>', 
	form_textarea("new_asset_email_body",  
		isset($settings["new_asset_email_body"]) ? $settings["new_asset_email_body"] : '' )
);

echo $this->table->generate();
echo $this->table->clear();

// Control Panel Display options

$this->table->set_template($cp_table_template);
$this->table->set_heading(lang('settings'), lang('value'));

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => lang('ajw_client_downloads_user_display_info'),
		'class' => 'box'
	)
);

$this->table->add_row(
	form_label(lang('ajw_client_downloads_user_display_fields'), 'user_fields') .
	'<div class="subtext">' . lang('ajw_client_downloads_user_display_fields_info') . '</div>', 
	form_multiselect("user_fields[]", $user_fields,
		isset($settings["user_fields"]) ? $settings["user_fields"] : '',
	 	'style="width:300px"')
);

echo $this->table->generate();
echo $this->table->clear();

/*

// Moderation options

$this->table->set_template($cp_table_template);
$this->table->set_heading(lang('settings'), lang('value'));

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => 'Paths',
		'class' => 'box'
	)
);

echo $this->table->generate();
echo $this->table->clear();

*/

?>

<input type="submit" value="Update" class="submit" />

<?php echo form_close(); ?>