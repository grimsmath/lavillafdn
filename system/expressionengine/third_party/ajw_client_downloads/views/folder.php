<?php echo form_open( $form_action ); ?>

<?php 

if( isset( $settings["id"] ) ) {
	echo form_hidden("id", $settings["id"] );
}

$this->table->set_template($cp_table_template);
$this->table->set_heading(lang("settings"), lang("value"));

$this->table->add_row(
	'<em class="required">*</em> ' .
		form_label(lang("ajw_client_downloads_title"), 'title') . 
		form_error("title"), 
	form_input("title",  
		isset($settings["title"]) ? $settings["title"] : '' )
);

$this->table->add_row(
	form_label(lang("ajw_client_downloads_description"), 'description'), 
	form_textarea("description",  
		isset($settings["description"]) ? $settings["description"] : '' )
);

$available = '<div class="dnd_list_container">' . form_label(lang("ajw_client_downloads_available_assets")) . '<ul id="all" class="dnd_list">';
$added = '<div class="dnd_list_container">' . form_label(lang("ajw_client_downloads_selected_assets")) . '<ul id="added" class="dnd_list">';
foreach( $all_assets as $id => $asset) {
	if( in_array( $id, $settings["selected_assets"] ) ) {
		$added .= '  <li class="asset" id="asset-'. $id . '">' . $asset . '</li>';
	} else {
		$available .= '  <li class="asset" id="asset-'. $id . '">' . $asset . '</li>';
	}
}
$available .= '</ul></div>';
$added .= '</ul></div>';

$this->table->add_row(
	form_label(lang("ajw_client_downloads_assets"), 'assets'), 
	form_multiselect(
		"assets[]", 
		$all_assets,
		isset( $settings["selected_assets"] ) ? $settings["selected_assets"] : '',
		'id="assets"'
	) . $available . $added
);

echo $this->table->generate();
echo $this->table->clear();

?>

<input type="submit" value="Submit" class="submit" />

<?php echo form_close(); ?>