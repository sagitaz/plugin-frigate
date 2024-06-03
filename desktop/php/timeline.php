<?php

use frigate;

if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

?>
<br>
<div class="col-lg-12">
	<div class="input-group" style="display:inline-flex">
		<span class="input-group-btn">
			<a class="btn btn-sm roundedLeft notifAction" data-action="exit"><i class="fa fa-arrow-circle-left"></i> </a>
			<a class="btn btn-sm roundedRight btn-success notifAction " data-action="new"><i class="fas fa-plus-circle"></i> {{Nouvelle Notification}}</a>
		</span>
	</div>
	<br>
	<div class="col-lg-12">
				<table id="timelineTable" class="table">
					<thead>
						<tr>
							<th width="10%">{{Caméra}}</th>
							<th width="15%">{{Type}}</th>
							<th width="25%">{{Source}}</th>
							<th width="35%">{{Options}}</th>
							<th width="15%">{{Date}}</th>
						</tr>
					</thead>

					<?php
					$timelines = frigate::getTimeline();

					foreach ($timelines as $timeline) {
						echo '<tr><th>' . $timeline['camera'] . '</th></tr>';
						echo '<tr><th> ' . $timeline['class_type '] . '</th></tr>';
						echo '<tr><th>' . $timeline['source'] . '</th></tr>';
						echo '<tr><th> ' . $timeline['source_id'] . '</th></tr>';
						echo '<tr><th>' . $timeline['timestamp'] . '</th></tr>';
					}
					?>
				</table>
		</br>
	</div>
</div>