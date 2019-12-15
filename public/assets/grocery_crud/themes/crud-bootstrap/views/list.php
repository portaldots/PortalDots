<?php

	$column_width = (int)(80/count($columns));

	if(!empty($list)){
?><div class="bDiv" >
		<table class="table" cellspacing="0" cellpadding="0" border="0" id="flex1">
		<thead>
			<tr class='hDiv'>
				<?php if(!$unset_delete || !$unset_edit || !$unset_read || !empty($actions)){?>
				<th align="left" abbr="tools" axis="col1" class="" width='20%'>
					<div class="text-left">
						<?php echo $this->l('list_actions'); ?>
					</div>
				</th>
				<?php }?>
				<?php foreach($columns as $column){?>
				<th width='<?php echo $column_width?>%'>
					<div class="text-left field-sorting <?php if(isset($order_by[0]) &&  $column->field_name == $order_by[0]){?><?php echo $order_by[1]?><?php }?>"
						rel='<?php echo $column->field_name?>'>
						<?php echo $column->display_as?>
					</div>
				</th>
				<?php }?>
			</tr>
		</thead>
		<tbody>
<?php foreach($list as $num_row => $row){ ?>
		<tr  <?php if($num_row % 2 == 1){?>class="erow"<?php }?>>
			<?php if(!$unset_delete || !$unset_edit || !$unset_read || !empty($actions)){?>
			<td align="left" width='20%'>
				<div class='tools'>
					<?php if(!$unset_delete){?>
						<a href='<?php echo $row->delete_url?>' title='<?php echo $this->l('list_delete')?> <?php echo $subject?>' class="btn btn-danger delete-row">
							<i class="fa fa-trash" aria-hidden="true"></i>
						</a>
					<?php }?>
					<?php if(!$unset_edit){?>
						<a href='<?php echo $row->edit_url?>' title='<?php echo $this->l('list_edit')?> <?php echo $subject?>' class="btn btn-primary edit-row">
							<i class="fa fa-pencil" aria-hidden="true"></i>
						</a>
					<?php }?>
					<?php if($set_editor){?>
						<a href='<?php echo base_url($row->editor_url) ?>' title='エディターで編集' class="btn btn-primary edit-row">
							<i class="fa fa-edit" aria-hidden="true"></i>
						</a>
					<?php }?>
					<?php if(!$unset_read){?>
						<a href='<?php echo $row->read_url?>' title='<?php echo $this->l('list_view')?> <?php echo $subject?>' class="btn btn-info view-row">
							<i class="fa fa-eye" aria-hidden="true"></i>
						</a>
					<?php }?>
					<?php
					if(!empty($row->action_urls)){
						foreach($row->action_urls as $action_unique_id => $action_url){
							$action = $actions[$action_unique_id];
					?>
							<a href="<?php echo $action_url; ?>" class="<?php echo $action->css_class; ?> crud-action" title="<?php echo $action->label?>"><?php
								if(!empty($action->image_url))
								{
									?><img src="<?php echo $action->image_url; ?>" alt="<?php echo $action->label?>" /><?php
								}
							?></a>
					<?php }
					}
					?>
										<div class='clear'></div>
				</div>
			</td>
			<?php }?>
			<?php foreach($columns as $column){?>
			<td width='<?php echo $column_width?>%' class='<?php if(isset($order_by[0]) &&  $column->field_name == $order_by[0]){?>sorted<?php }?>'>
				<div class='text-left'><?php echo $row->{$column->field_name} != '' ? $row->{$column->field_name} : '&nbsp;' ; ?></div>
			</td>
			<?php }?>
		</tr>
<?php } ?>
		</tbody>
		</table>
	</div>
<?php }else{?>
	<br/>
	&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $this->l('list_no_items'); ?>
	<br/>
	<br/>
<?php }?>
