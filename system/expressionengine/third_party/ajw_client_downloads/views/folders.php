<?php 
	if( isset( $folders[0]["screen_name"] ) ) {
		print "<h3>Folders assigned to: " . $folders[0]["screen_name"] . "</h3>";
	} 
?>

<?php

$this->table->set_template( $cp_table_template );

$this->table->set_heading(
	array( 
		'#',
		lang("ajw_client_downloads_title"), 
		lang("ajw_client_downloads_description"), 
		"", "", ""
	)
);

if( count( $folders ) == 0 ) {
	$this->table->add_row(
		array(
			'colspan' => 5,
			'data' => 'No folders have been added'
		)
	);
}


foreach( $folders as $folder ) {

	$this->table->add_row(
		$folder["id"],
		'<a class="folder" href="' . $base . AMP . 'method=folder' . AMP . 'id=' . $folder["id"] . '">' . $folder["title"] . '</a>',
		$folder["description"],
		'<a class="asset" href="' . $base . AMP . 'method=assets' . AMP . 'folder_id=' . $folder["id"] . '">' . lang("ajw_client_downloads_assets") . '</a>',
		'<a class="user" href="' . $base . AMP . 'method=users' . AMP . 'folder_id=' . $folder["id"] . '">' . lang("ajw_client_downloads_users") . '</a>',
		'<a class="delete" href="' . $base . AMP . 'method=delete_folder' . AMP . 'id=' . $folder["id"] . '">' . lang("ajw_client_downloads_delete") . '</a>'
	);
	
}

?>

<?php echo $this->table->generate(); ?>

<p class="cp_button"><a href="<?php echo $new_folder_url; ?>"><?php echo lang("ajw_client_downloads_folder_add"); ?></a></p>
<div class="clear_left">&nbsp;</div>