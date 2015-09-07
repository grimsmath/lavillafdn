<?php 
	if( isset( $assets[0]["folder_title"] ) ) {
		print "<h3>Assets in: " . $assets[0]["folder_title"] . "</h3>";
	} 
?>

<?php

$this->table->set_template( $cp_table_template );

$this->table->set_heading(
	array( 
		lang("ajw_client_downloads_title"), 
		lang("ajw_client_downloads_filename"), 
		lang("ajw_client_downloads_filetype"), 
		lang("ajw_client_downloads_size"), 
		lang("ajw_client_downloads_upload_date"), 
		lang("ajw_client_downloads_downloads"), 
		""
	)
);

if( count( $assets ) == 0 ) {
	$this->table->add_row(
		array(
			'colspan' => 7,
			'data' => 'No assets have been added'
		)
	);
}

foreach( $assets as $asset ) {
	
	$this->table->add_row(
		'<a class="asset" href="' . $base . AMP . 'method=asset' . AMP . 'id=' . $asset["id"] . '">'. $asset["title"] . '</a>',
		$asset["path"],
		$asset["extension"],
		$asset["size"],
		$asset["date"],
		'<a class="download" href="' . $base . AMP . 'method=reports' . AMP . 'asset_id=' . $asset["id"] . '">' . $asset["downloads"] . '</a>',
		'<a class="delete" href="' . $base . AMP . 'method=delete_asset' . AMP . 'id=' . $asset["id"] . '">' . lang("ajw_client_downloads_delete") . '</a>'
	);
	
}

?>

<?php echo $this->table->generate(); ?>

<p class="cp_button"><a href="<?php echo $new_asset_url; ?>"><?php echo lang("ajw_client_downloads_asset_upload"); ?></a></p>
<div class="clear_left">&nbsp;</div>
