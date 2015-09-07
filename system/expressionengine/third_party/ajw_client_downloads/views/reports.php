<?php 
if( count( $downloads ) != 0 ) {
	if( isset($display_member) ) {
		print "<h3>Filtered by member: " . $downloads[0]["screen_name"] . "</h3>";
	} 
	if( isset($display_asset) ) {
		print "<h3>Filtered by asset: " . $downloads[0]["title"] . "</h3>";
	} 
}
?>

<?php

$this->table->set_template( $cp_table_template );

$this->table->set_heading(
	array(
		lang("ajw_client_downloads_asset"), 
		lang("ajw_client_downloads_user"), 
		lang("ajw_client_downloads_time") 
	)
);

if( count( $downloads ) == 0 ) {
	$this->table->add_row(
		array(
			'colspan' => 3,
			'data' => 'No downloads'
		)
	);
}


foreach( $downloads as $download ) {
	
	$this->table->add_row(
		'<a class="asset" href="' . $base . AMP . 'method=asset' . AMP . 'id=' . $download["asset_id"] . '">' . $download["title"] . '</a>',
		'<a class="user" href="' . $base . AMP . 'method=user' . AMP . 'id=' . $download["member_id"] . '">' . $download["screen_name"] . '</a>',
		$download["time"]
	);
	
}

?>

<?php echo $this->table->generate(); ?>

