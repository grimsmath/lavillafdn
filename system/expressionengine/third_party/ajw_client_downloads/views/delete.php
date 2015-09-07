<?php echo form_open( $delete_action ); ?>
<?php echo form_hidden('confirm', 'confirm'); ?>
<?php echo form_hidden('id', $id); ?>

<p class="shun"><?php echo $confirm_message; ?></p>

<p class="notice"><?php echo lang('action_can_not_be_undone'); ?></p>

<p>
	<?php echo 
		form_submit(
			array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')
		);
	?>
</p>

<?php echo form_close()?>