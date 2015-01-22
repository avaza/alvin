<?php
if(!empty($raw->list)){
	foreach($raw->list as $call){
		if($call->callstate == 'ACTIVE' && $call->bleg_callstate == 'ACTIVE'){
			echo '<table>';
			echo '<tr><td>' . $call->uuid . '</td></tr>';
			if(strlen($call->caller_num) != 4){
				echo '<tr><td>' . $call->caller_num . '</td></tr>';
			} else{
				echo '<tr><td>' . $call->callee_num . '</td></tr>';
			}
			echo '</table>';
		}
	}
} else{
	echo 'NONE';
}
?>