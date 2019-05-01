<?php

	$this->set_css($this->default_theme_path.'/crud-bootstrap/css/crud-bootstrap.css');

    $this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.form.min.js');
	$this->set_js_config($this->default_theme_path.'/crud-bootstrap/js/crud-bootstrap-edit.js');

	$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/jquery.noty.js');
	$this->set_js_lib($this->default_javascript_path.'/jquery_plugins/config/jquery.noty.config.js');
?>
<div class="crud-bootstrap panel panel-default" style='width: 100%;' data-unique-hash="<?php echo $unique_hash; ?>">
	<div class="panel-heading">
		<div class="panel-title">
			<div class='ftitle-left'>
				<?php echo $this->l('form_edit'); ?> <?php echo $subject?>
			</div>
			<div class='clear'></div>
		</div>
	</div>
<?php echo form_open( $update_url, 'method="post" id="crudForm"  enctype="multipart/form-data"'); ?>
<div class="panel-body" id='main-table-box'>
	<div id='report-error' class='report-div error'></div>
	<div id='report-success' class='report-div success'></div>
	<div class='form-horizontal'>
		<?php
		$counter = 0;
			foreach($fields as $field)
			{
				$even_odd = $counter % 2 == 0 ? 'odd' : 'even';
				$counter++;
		?>
			<div class='form-group form-field-box <?php echo $even_odd?>' id="<?php echo $field->field_name; ?>_field_box">
				<label for="field-<?php echo $field->field_name; ?>" class='col-lg-1 col-sm-3 control-label' id="<?php echo $field->field_name; ?>_display_as_box">
					<?php echo $input_fields[$field->field_name]->display_as; ?><?php echo ($input_fields[$field->field_name]->required)? "<span class='label label-danger'>必須</span> " : ""; ?>
				</label>
				<div class='col-lg-6 col-sm-9 form-input-box' id="<?php echo $field->field_name; ?>_input_box">
					<?php echo $input_fields[$field->field_name]->input?>
				</div>
				<div class='clear'></div>
			</div>
		<?php }?>
		<?php if(!empty($hidden_fields)){?>
		<!-- Start of hidden inputs -->
			<?php
				foreach($hidden_fields as $hidden_field){
					echo $hidden_field->input;
				}
			?>
		<!-- End of hidden inputs -->
		<?php }?>
		<?php if ($is_ajax) { ?><input type="hidden" name="is_ajax" value="true" /><?php }?>
	</div>
</div>
<div class="panel-footer">
	<input  id="form-button-save" type='submit' value='<?php echo $this->l('form_update_changes'); ?>' class="btn btn-primary btn-lg"/>
<?php 	if(!$this->unset_back_to_list) { ?>
	<input type='button' value='<?php echo $this->l('form_update_and_go_back'); ?>' id="save-and-go-back-button" class="btn btn-default btn-lg"/>
	<input type='button' value='<?php echo $this->l('form_cancel'); ?>' class="btn btn-default btn-lg" id="cancel-button" />
<?php 	} ?>
	<div class='small-loading' id='FormLoading'></div>
	<div class='clear'></div>
</div>
</div>
<?php echo form_close(); ?>
<script>
	var validation_url = '<?php echo $validation_url?>';
	var list_url = '<?php echo $list_url?>';

	var message_alert_edit_form = "<?php echo $this->l('alert_edit_form')?>";
	var message_update_error = "<?php echo $this->l('update_error')?>";
</script>
