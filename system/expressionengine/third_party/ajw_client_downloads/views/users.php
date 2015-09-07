<?php

if( $display_folder ) {
	print "<h3>Assigned to: " . $folder_title["title"] . "</h3>";
}

$this->table->set_template( $cp_table_template );

$t = array();
$t[] = lang("screen_name");
foreach( $fields as $value ) {
	if( $value == "" ) continue;
	$t[] = $titles[ $value ];
}
$t[] = "";
$t[] = "";
$t[] = "";

$this->table->set_heading(
	$t
);

if( count( $users ) == 0 ) {
	$this->table->add_row(
		array(
			'colspan' => 4 + count( $fields ),
			'data' => lang("ajw_client_downloads_no_users")
		)
	);
}

foreach( $users as $user ) {
	
	$row = array(
		'<a href="' . $base . AMP . 'method=user' . AMP . 'id=' . $user["member_id"] . '" class="user">' . $user["screen_name"] . '</a>'
	);
	foreach( $user as $idx => $value ) {
		if( $idx != "id" && $idx != "member_id" && $idx != "screen_name" && $idx != "num_folders" ) {
			$row[] = $value;
		}
	}
	$row[] = '<a href="' . $base . AMP . 'method=folders' . AMP . 'member_id=' . $user["member_id"] . '" class="folder">' . lang("ajw_client_downloads_folders_view") . '</a>&nbsp;(' . $user["num_folders"] . ')';
	$row[] = '<a href="' . $base . AMP . 'method=reports' . AMP . 'member_id=' . $user["member_id"] . '" class="download">' . lang("ajw_client_downloads_downloads_view") . '</a>';
	$row[] = '<a href="' . $base . AMP . 'method=delete_user' . AMP . 'id=' . $user["id"] . '" class="delete">' . lang("ajw_client_downloads_delete") . '</a>';
	
	$this->table->add_row(
		$row
	);
	
}

?>

<?php echo $this->table->generate(); ?>

<p class="cp_button"><a href="<?php echo $new_user_url; ?>"><?php echo lang("ajw_client_downloads_user_add")?></a></p>
<div class="clear_left">&nbsp;</div>
