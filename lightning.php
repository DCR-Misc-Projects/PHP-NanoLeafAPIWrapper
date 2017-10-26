<?PHP
	//V1
	
	include("class.aurora.php");

	$c = new Aurora("192.168.2.229", 16021, "LQ6x9gHzrSINMxBcisMtF8LQ6YHdIzfd");

	$base_color = "3 3 5";

	$r = array(221,174,35,229,250,193,155,178,11,105,65,212,217,162,130);
	
	$c->startStreamingMode();
	
	$reset = count($r);
	foreach($r as $t)
		$reset .= " $t 1 $base_color 0 1";
		
	$c->sendStreamMessage($reset);
	
	while (1) {
		
		$tm0 = "";
		$tm1 = "";
		
		$mx = rand(1,15);
		
		for ($i=0; $i<=$mx; $i++) {
			$id = array_rand($r);
			$id = $r[$id];
			$tm0 .= " $id 1 255 255 255 0 1";
			$tm1 .= " $id 1 $base_color 0 14";
		}
		echo "$mx $tm0\n\n";
		$c->sendStreamMessage("$mx$tm0");
		usleep(100000);
		$c->sendStreamMessage("$mx$tm1");
		
		usleep(rand(700,3000)*1000);
	}

?>