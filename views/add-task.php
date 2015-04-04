<?php $projects = get_terms( array( 'project' ), array( 'hide_empty' => false ) ); ?>
<div id="add-task-button-view"><button id="add-task-button"><i class="fa fa-plus-circle"></i></button></div>
<div id="add-task-view" class="hide">
	<div id="add-task-form">
		<form method="POST">
			<input type="hidden" name="action" value="submit_task_form" />
			<div class="field">
				<label>Task</label>
				<textarea name="task"></textarea>
			</div>
			<div class="field">
				<label>Project</label>
				<select name="task_project">
					<option value="">--</option>
					<?php foreach ( $projects as $project ): ?>
						<option value="<?php echo $project->slug; ?>"><?php echo $project->name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="field">
				<label>Due</label>
				<select name="task_due_date">
					<option value="<?php echo date('l'); ?>"><?php echo date('l'); ?></option>
					<option value="<?php echo date('l', strtotime('+1 Day')); ?>"><?php echo date('l', strtotime('+1 Day')); ?></option>
					<option value="<?php echo date('l', strtotime('+2 Days')); ?>"><?php echo date('l', strtotime('+2 Days')); ?></option>
					<option value="<?php echo date('l', strtotime('+3 Days')); ?>"><?php echo date('l', strtotime('+3 Days')); ?></option>
					<option value="<?php echo date('l', strtotime('+4 Days')); ?>"><?php echo date('l', strtotime('+4 Days')); ?></option>
					<option value="<?php echo date('l', strtotime('+5 Days')); ?>"><?php echo date('l', strtotime('+5 Days')); ?></option>
					<option value="<?php echo date('l', strtotime('+6 Days')); ?>"><?php echo date('l', strtotime('+6 Days')); ?></option>
				</select>
			</div>
			<div class="field">
				<button type="submit">Add Task</button>
			</div>
		</form>
	</div>
</div>