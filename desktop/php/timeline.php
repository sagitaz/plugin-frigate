<?php

use frigate;
use log;

if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

?>
<br>
<div class="col-lg-12">
	<div class="col-lg-12">
		<table id="timelineTable" class="table">
			<thead>
				<tr>
					<th width="20%">{{Caméra}}</th>
					<th width="20%">{{Type}}</th>
					<th width="20%">{{Source}}</th>
					<th width="20%">{{Options}}</th>
					<th width="20%">{{Date}}</th>
				</tr>
			</thead>

			<?php
			$timelines = frigate::getTimeline();
			$timelines = array_reverse($timelines);

			foreach ($timelines as $timeline) {
				// Conversion du timestamp en un format de date lisible
				$date_time = date("d-m-Y H:i:s", $timeline['timestamp']);

				echo '<tr><th>' . $timeline['camera'] . '</th>';
				echo '<th>' . $timeline['source'] . '</th>';
				echo '<th>' . $timeline['data']['label'] . '</th>';
				echo '<th>' . $timeline['source_id'] . '</th>';
				echo '<th>' . $date_time . '</th></tr>';
			}
			?>
		</table>
		</br>
	</div>
</div>