<?php // BigMap 2 queue manager. Written by Ilya Zverev, licensed WTFPL.
$workpath = '../queue';
$imgpath = 'result';
$min_interval = 3600*3;
$max_tiles = 100;
$max_queue = 10;
$email = 'zverik@textual.ru'; // for nominatim

$redirect = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/';
date_default_timezone_set('UTC');

if( defined('IN_BIGMAP') ) {
	// 0. validate number of tiles
	$total = ($xmax-$xmin+1) * ($ymax-$ymin+1);
	if( $total > (count($layers) > 1 ? $max_tiles / 1.5 : $max_tiles) ) {
		echo '<p>Too many tiles for this server. Sorry.</p><p><a href="'.$permalink.'">Return to BigMap</a></p>';
		exit;
	}
	// 1. read queue and check that there were no tasks with given params
	$ident = "$zoom,$xmin,$ymin,$xmax,$ymax,$tiles";
	$taskid = false;
	$queue = @fopen($workpath.'/queue', 'r');
	if( $queue ) {
		while( !$taskid && ($line = fgets($queue)) !== false ) {
			$p = strpos($line, ','.$ident.',');
			if( $p > 0 )
				$taskid = substr($line, 0, $p);
		}
		fclose($queue);
	}
	// 2. if there weren't, add task to queue
	if( !$taskid ) {
		if( get_queue_pos('_') >= $max_queue ) {
			echo '<p>Too many tasks in the queue already. Sorry.</p><p><a href="'.$permalink.'">Return to BigMap</a></p>';
			exit;
		}
		$taskid = get_taskid();
		if( !is_dir($workpath.'/tasks') )
			mkdir($workpath.'/tasks');
		$task = fopen($workpath.'/tasks/'.$taskid, 'w');
		if( $task ) {
			$place = nominatim();
			// start time; status; last time; ident; layer urls; attribution; nominatim response
			fwrite($task, time()."\n0,".time()."\n");
			fwrite($task, "$ident\n");
			fwrite($task, implode('|', $layers)."\n");
			fwrite($task, "$attrib_plain\n");
			fwrite($task, "$place\n");
			fclose($task);
			chmod($workpath.'/tasks/'.$taskid, 0666);
			$queue = fopen($workpath.'/queue', 'a');
			fwrite($queue, "$taskid,$ident,$place\n");
			fclose($queue);
		}
	}
	// 3. redirect to the task page
	header("Location: ${redirect}queue.php?task=$taskid");
	exit;
}

function get_taskid() {
	$t = '';
	for( $i = 0; $i < 3; $i++ )
		$t .= chr(rand(97,122));
	return $t.(time() - 1390000000);
}

function nominatim() {
	global $lat_min, $lat_max, $lon_min, $lon_max, $zoom;
	$url = 'http://nominatim.openstreetmap.org/reverse?format=json&zoom='.min($zoom, 8).'&addressdetails=0'
			.'&email='.urlencode($email).'&lat='.(($lat_min + $lat_max) / 2).'&lon='.(($lon_min + $lon_max) / 2);
	$response = json_decode(file_get_contents($url), true);
	if( isset($response) && isset($response['display_name']) )
		return $response['display_name'];
	return 'Unknown place';
}

function get_queue_pos($taskid) {
	global $workpath;
	$queue = fopen($workpath.'/queue', 'r');
	if( !$queue )
		return 999;
	$found_done = false;
	$done = @fopen($workpath.'/done', 'r');
	if( $done ) {
		while( ($donetask = fgets($done)) !== false ) {
			$donetask = chop($donetask);
			if( $donetask == $taskid )
				$found_done = true;
			while( ($line = fgets($queue)) !== false ) {
				if( substr($line, 0, strlen($donetask)+1 ) == $donetask.',' )
					break;
			}
		}
		fclose($done);
	}
	$cnt = 0;
	while( ($line = fgets($queue)) !== false ) {
		$cnt++;
		if( substr($line, 0, strlen($taskid)+1 ) == $taskid.',' ) {
			$found_done = false;
			break;
		}
	}
	fclose($queue);
	return $found_done ? 0 : $cnt;
}

?>
<!doctype html>
<html>
<head>
<title>BigMap 2</title>
<meta charset="utf-8">
</head>
<body>
<?php

$taskid = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';
if( preg_match('/^[a-z]+\d+$/', $taskid) ) {
	$task = @file($workpath.'/tasks/'.$taskid);
	if( $task !== false ) {
		// task found
		$statusar = explode(',', chop($task[1]));
		if( isset($_REQUEST['restart']) && time() - $statusar[1] > $min_interval ) {
			$task[1] = '0,'.time()."\n";
			if( $ftask = fopen($workpath.'/tasks/'.$taskid, 'w') ) {
				foreach( $task as $taskline )
					fwrite($ftask, $taskline);
				fclose($ftask);
				$queue = fopen($workpath.'/queue', 'a');
				fwrite($queue, $taskid.','.chop($task[2]).','.chop($task[5])."\n");
				fclose($queue);
			}
		}
		$statusid = $statusar[0];
		if( $statusid == 0 ) $status = 'queued';
		elseif( $statusid == 1 ) $status = 'processing';
		elseif( $statusid == 2 ) $status = 'ready';
		elseif( $statusid == 3 ) $status = 'failed';
		elseif( $statusid < 0 ) $status = 'error, waiting';
		else $status = 'unknown status';
		$queuepos = get_queue_pos($taskid);
		$ident = explode(',', chop($task[2]));
		$link = $redirect.'bigmap.php?xmin='.$ident[1].'&ymin='.$ident[2].'&xmax='.$ident[3].'&ymax='.$ident[4].'&zoom='.$ident[0].'&tiles='.$ident[5];
?>
		<h1>Task <?=$taskid ?>: <?=$status ?></h1>
		<p><a href="<?=$link ?>"><?=htmlspecialchars(chop($task[5])) ?></a></p>
		<p><?=$ident[3]-$ident[1]+1 ?>x<?=$ident[4]-$ident[2]+1 ?> tiles at zoom <?=$ident[0] ?>; layers: <?=join(', ', explode('|', $ident[5])) ?>.</p>
		<p>Opened on <?=date('Y-m-d H:i', chop($task[0])) ?>, last updated on <?=date('Y-m-d H:i', $statusar[1]) ?>.</p>
<?php
		if( $queuepos > 0 ) {
			echo "<p>Queue position: $queuepos.</p>";
		} elseif( time() - $statusar[1] > $min_interval ) {
			echo '<p><a href="queue.php?task='.$taskid.'&restart=1">Restart task</a></p>';
		}

		// list all generated images
		if( $dh = opendir($imgpath) ) {
			$taskidlen = strlen($taskid);
			$images = array();
			while( false !== ($filename = readdir($dh)) ) {
				if( substr($filename, 0, $taskidlen) == $taskid )
					$images[] = $filename;
			}
			closedir($dh);
			rsort($images);
			if( count($images) ) {
				echo '<h2>Generated images</h2>';
			}
			foreach( $images as $image ) {
				$name = substr($image, $taskidlen + 5, 2).'.'.substr($image, $taskidlen + 3, 2).'.'.substr($image, $taskidlen + 1, 2)
					.' '.substr($image, $taskidlen + 8, 2).':'.substr($image, $taskidlen + 10, 2);
				$meta = $link.'&basename='.preg_replace('/\.\w+$/', '', $image).'&action';
				echo "<a href=\"$imgpath/$image\">$name</a> <a href=\"$meta=ozimap\">.map</a> <a href=\"$meta=wld\">.wld</a> <a href=\"$meta=kml\">.kml</a><br>\n";
			}
		}
	} else {
		echo '<h1>Task was not found</h1>';
	}
	echo '<p style="margin-top: 2em;"><a href="queue.php">List all tasks</a></p>';
} else {
	// list all tasks
	echo '<h1><a href="index.html">BigMap 2</a> Download Queue</h1>';
	$queue = fopen($workpath.'/queue', 'r');
	if( !$queue ) {
		echo '<p>Error: queue was not found.</p>';
		exit;
	}
	$tasks_done = array();
	$tasks_pending = array();

	$done = @fopen($workpath.'/done', 'r');
	if( $done ) {
		while( ($donetask = fgets($done)) !== false ) {
			$donetask = chop($donetask);
			while( ($line = fgets($queue)) !== false ) {
				if( substr($line, 0, strlen($donetask)+1 ) == $donetask.',' ) {
					$tasks_done[] = explode(',', chop($line), 8);
					break;
				}
			}
		}
		fclose($done);
	}
	while( ($line = fgets($queue)) !== false ) {
		$tasks_pending[] = explode(',', chop($line), 8);
	}
	fclose($queue);

	if( count($tasks_pending) ) {
		// check for active task
		$task = @file($workpath.'/tasks/'.$tasks_pending[0][0]);
		if( $task && substr($task[1], 0, 1) != '0' ) {
			echo "<h2>Now processing</h2>\n";
			echo '<p>'.get_task_line($tasks_pending[0])."</p>\n";
			array_shift($tasks_pending);
		}
	}

	if( count($tasks_pending) ) {
		echo "<h2>Pending tasks</h2>\n<p>\n";
		foreach( $tasks_pending as $task )
			echo get_task_line($task)."<br>\n";
		echo "</p>\n";
	}

	$cnt = count($tasks_done);
	if( $cnt ) {
		echo "<h2>Finished tasks</h2>\n<p>\n";
		for( $i = $cnt-1; $i >= max(0, $cnt-50); $i-- )
			echo get_task_line($tasks_done[$i])."<br>\n";
		echo "</p>\n";
	}
}

function get_task_line($task) {
	return '<a href="queue.php?task='.$task[0].'">'
		.($task[4]-$task[2]+1).'x'.($task[5]-$task[3]+1).' tiles at zoom '.$task[1].'</a>: '.htmlspecialchars($task[7]);
}
?>
</body>
</html>
