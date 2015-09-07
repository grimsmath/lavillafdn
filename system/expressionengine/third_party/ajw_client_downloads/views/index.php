<h3>Recently added clients</h3>

<?php

$this->table->set_template( $cp_table_template );

$t = array();
$t[] = lang("screen_name");
$t[] = lang("ajw_client_downloads_created");
$t[] = "";
$t[] = "";

$this->table->set_heading(
	$t
);

if( count( $users ) == 0 ) {
	$this->table->add_row(
		array(
			'colspan' => 4 + ( isset($fields) ? count( $fields ) : 0),
			'data' => lang("ajw_client_downloads_no_users")
		)
	);
}

foreach( $users as $user ) {
	
	$row = array(
		'<a href="' . $base . AMP . 'method=user' . AMP . 'id=' . $user["member_id"] . '" class="user">' . $user["screen_name"] . '</a>'
	);
	$row[] = $user["date"];
	$row[] = '<a href="' . $base . AMP . 'method=folders' . AMP . 'member_id=' . $user["member_id"] . '" class="folder">' . lang("ajw_client_downloads_folders_view") . '</a>&nbsp;(' . $user["num_folders"] . ')';
	$row[] = '<a href="' . $base . AMP . 'method=reports' . AMP . 'member_id=' . $user["member_id"] . '" class="download">' . lang("ajw_client_downloads_downloads_view") . '</a>';
	
	$this->table->add_row(
		$row
	);
	
}

?>

<?php 
	echo $this->table->generate();
	echo $this->table->clear();
?>

<p class="cp_button"><a href="<?php echo $new_user_url; ?>"><?php echo lang("ajw_client_downloads_user_add")?></a></p>
<div class="clear_left">&nbsp;</div>

<h3>Recently uploaded assets</h3>

<?php

$this->table->set_template( $cp_table_template );

$this->table->set_heading(
	array( 
		lang("ajw_client_downloads_title"), 
		lang("ajw_client_downloads_upload_date"),
		lang("ajw_client_downloads_filename"), 
		lang("ajw_client_downloads_filetype"), 
		lang("ajw_client_downloads_size")
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
		$asset["date"],
		$asset["path"],
		$asset["extension"],
		$asset["size"]
	);
	
}

?>

<?php 
	echo $this->table->generate();
	echo $this->table->clear();
?>

<p class="cp_button"><a href="<?php echo $new_asset_url; ?>"><?php echo lang("ajw_client_downloads_asset_upload"); ?></a></p>
<div class="clear_left">&nbsp;</div>
