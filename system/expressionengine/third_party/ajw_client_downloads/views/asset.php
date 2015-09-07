<?php echo form_open_multipart( $form_action ); ?>

<?php 

if( isset( $settings["id"] ) ) {
	echo form_hidden("id", $settings["id"] );
}

$this->table->set_template($cp_table_template);
$this->table->set_heading( lang("settings"), lang("value") );

$this->table->add_row(
	'<em class="required">*</em> ' .
		form_label( lang("ajw_client_downloads_title"), 'title') . 
		form_error("title"), 
	form_input("title",  
		isset($settings["title"]) ? $settings["title"] : '' )
);

$this->table->add_row(
	form_label(lang("ajw_client_downloads_description"), 'description'), 
	form_textarea("description",  
		isset($settings["description"]) ? $settings["description"] : '' )
);

$this->table->add_row(
	form_label(lang("ajw_client_downloads_keywords"), 'keywords'), 
	form_input("keywords",  
		isset($settings["keywords"]) ? $settings["keywords"] : '' )
);

if( !isset( $settings["id"] ) ) {

	$this->table->add_row(
		form_label(lang("ajw_client_downloads_file_to_upload"), 'file_upload'), 
		'<input type="file" name="file_upload" size="20" />' . 
		( count($files) ? '<br/><br/>or:<br/><br/>' . form_multiselect("file", $files) : '' )
	);

}

$available = '<div class="dnd_list_container">' . form_label(lang("ajw_client_downloads_available_assets")) . '<ul id="all" class="dnd_list">';
$added = '<div class="dnd_list_container">' . form_label(lang("ajw_client_downloads_selected_assets")) . '<ul id="added" class="dnd_list">';
foreach( $folders as $id => $folder) {
	if( in_array( $id, $settings["folders"] ) ) {
		$added .= '  <li class="folder" id="id-'. $id . '">' . $folder . '</li>';
	} else {
		$available .= '  <li class="folder" id="id-'. $id . '">' . $folder . '</li>';
	}
}
$available .= '</ul></div>';
$added .= '</ul></div>';

$this->table->add_row(
	form_label(lang("ajw_client_downloads_add_to_folders"), 'folders'), 
	form_multiselect("folders[]", $folders,
		isset($settings["folders"]) ? $settings["folders"] : '',
		'id="folders"' ) . 
		$available . $added
);

if( !isset( $settings["id"] ) ) {

	$this->table->add_row(
		form_label(lang("ajw_client_downloads_notify"), 'notify_upload'), 
		form_checkbox("notify_upload", "y" )
	);

} else {
	
	$this->table->add_row(
		form_label("Uploaded"),
		$settings["created"]
	);
	
}

/*
if( isset( $settings["id"] ) ) {

	$this->table->add_row(
		form_label('Replace asset with new file', 'file_upload'), 
		'<input type="file" name="file_upload" size="20" />'
	);

}
*/

echo $this->table->generate();
echo $this->table->clear();

?>

<input type="submit" value="Submit" class="submit" />

<?php echo form_close(); ?>