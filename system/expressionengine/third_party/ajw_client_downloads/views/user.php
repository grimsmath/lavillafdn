<?php echo form_open( $form_action ); ?>

<?php 

if( isset( $settings["id"] ) ) {
	echo form_hidden("id", $settings["id"] );
}

$this->table->set_template($cp_table_template);
$this->table->set_heading(lang("settings"), lang("value"));

if( !isset( $settings["id"] ) ) {

	$this->table->add_row(
		'<em class="required">*</em> ' .
			form_label( lang("ajw_client_downloads_member"), 'member_id' ), 
		form_dropdown("member_id", $members )
	);

	$this->table->add_row(
		form_label( lang("ajw_client_downloads_default_folder"), 'title') . 
		'<div class="subtext">' . lang("ajw_client_downloads_default_folder_info") . '</div>', 
		form_error("title") . 
		form_input("title",  
			isset($settings["title"]) ? $settings["title"] : '' )
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
	form_label( lang("ajw_client_downloads_add_to_folders"), 'folders'), 
	form_error("folders") . 
	form_multiselect("folders[]", $folders,
		isset($settings["folders"]) ? $settings["folders"] : '',
		'id="folders"' ) . 
		$available . $added
);

echo $this->table->generate();
echo $this->table->clear();

?>

<input type="submit" value="<?php echo lang("submit"); ?>" class="submit" />

<?php echo form_close(); ?>