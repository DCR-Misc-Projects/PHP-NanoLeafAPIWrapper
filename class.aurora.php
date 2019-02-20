<?PHP
// V1
class Aurora {
	
	private $config = array();
	private $BASE_URI = null;
	private $SOCK_STREAM = null;
	
	function __construct($ip, $port, $key, $apiversion = "v1") {
		$this->config['ip'] = $ip;
		$this->config['port'] = $port;
		$this->config['key'] = $key;
		$this->config['apiversion'] = $apiversion;
		
		$this->BASE_URI	= sprintf("http://%s:%s/api/%s/%s/", $ip, $port, $apiversion, $key);
		
	}
	
	
	
	function getInfo() {		
		return json_decode($this->HTTP_GET("")[1]);
	}	
	function getNewKey() {
		
		$return = $this->HTTP_GET("new", false);
		
		$code = $return[0];
		$data = $return[1];
		
		if ($code == 404)
			throw new Exception("New key can not be generated, please press the power button for 5 sec, until the led flash and retry the call");
		else
			return json_decode($data);		
		
	}
	function isOn() {
		return json_decode($this->HTTP_GET("state/on")[1])->value;
	}
	function turnOn() {
		$this->HTTP_PUT("state", '{"on":{"value":true}}');
	}
	function turnOff() {
		$this->HTTP_PUT("state", '{"on":{"value":false}}');
	}
	
	function startStreamingMode() {

		$command = json_encode(array("command" => "display",
									 "animType" => "extControl"));

		$d = json_decode($this->write($command)[1]);
		
		if ($this->SOCK_STREAM = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
			socket_connect($this->SOCK_STREAM, $d->streamControlIpAddr, $d->streamControlPort);
		else
			throw new Exception("Streaming socket creation error!");
		
		return sprintf("Socket is ready on %s:%s, you may use function sendStreamMessage.", $d->streamControlIpAddr, $d->streamControlPort);
		
	
	}

	function sendStreamMessage($str) {

		$message     = explode(" ", $str);		
		$msg = "";
		
		foreach ($message as $m) {			
			$msg .= chr($m);
		}
	
		socket_write($this->SOCK_STREAM, $msg, strlen($msg));
		
	}	
	
	// ------------------- COLOR MODES ------------------------------
	
	function getCurrentColorMode() {
		return trim($this->HTTP_GET("state/colorMode")[1], '"');
	}
	
	function getSaturation() {
		return json_decode($this->HTTP_GET("state/sat")[1])->value;
	}	
	function setSaturationStaticValue($value) {
		$this->HTTP_PUT("state", '{"sat":{"value":'.$value.'}}');
	}
	function setSaturationRelativeValue($value) {
		$this->HTTP_PUT("state", '{"sat":{"increment":'.$value.'}}');
	}
	
	function getHue() {
		return json_decode($this->HTTP_GET("state/hue")[1])->value;
	}	
	function setHueStaticValue($value) {
		$this->HTTP_PUT("state", '{"hue":{"value":'.$value.'}}');
	}
	function setHueRelativeValue($value) {
		$this->HTTP_PUT("state", '{"hue":{"increment":'.$value.'}}');
	}
	
	function getColorTemperature() {
		return json_decode($this->HTTP_GET("state/ct")[1])->value;
	}	
	function setColorTemperatureStaticValue($value) {
		$this->HTTP_PUT("state", '{"ct":{"value":'.$value.'}}');
	}
	function setColorTemperatureRelativeValue($value) {
		$this->HTTP_PUT("state", '{"ct":{"increment":'.$value.'}}');
	}	
	
	function getBrightness() {
		return json_decode($this->HTTP_GET("state/brightness")[1])->value;
	}
	function setBrightnessStaticValue($value) {
		$this->HTTP_PUT("state", '{"brightness":{"value":'.$value.'}}');
	}
	function setBrightnessRelativeValue($value) {
		$this->HTTP_PUT("state", '{"brightness":{"increment":'.$value.'}}');
	}
	
	// --------------------------------------------------------------
	
	
	
	// --------------------------- EFFECTS --------------------------
	
	function getCurrentEffect() {
		return trim($this->HTTP_GET("effects/select")[1], '"');
	}
	function getEffectList() {
		return $this->HTTP_GET("effects/effectsList")[1];
	}
	function setEffect($effect_name) {		
		$d = $this->HTTP_PUT("effects", '{"select":"'.$effect_name.'"}', false);
		
		if ($d[0] > 200) 
			throw new Exception("Operation failed, does the effect name exist ?");
	}
	function setTempEffect($effect_name, $duration) {
	
		$command = json_encode(array("command" => "displayTemp",
										"duration" => $duration,
										"animName" => $effect_name));


		$this->write($command);
									
		
	}
	function write($command_object) {
		
		$d = $this->HTTP_PUT("effects", '{"write":'.$command_object.'}', false);
		
		//echo '{"write":'.$command_object.'}';
		
		if ($d[0] > 400) 
			throw new Exception("Write command failed. Please review your command object.");
		
		return $d;
		
	}
	
	// --------------------------------------------------------------
	
	

	
	
	private function HTTP_GET($endpoint, $manage_error = true) {
		
		$d = $this->HTTP_QUERY($this->BASE_URI . $endpoint, "GET");
		
		if ($manage_error)
			if ($d[0] >= 400)
				throw new Exception("The request return an error.", $d[0]);
		
		return $d;
		
	}
	private function HTTP_PUT($endpoint, $json_payload, $manage_error = true) {
		
		$d = $this->HTTP_QUERY($this->BASE_URI . $endpoint, "PUT", $json_payload);		
		
		if ($manage_error)
			if ($d[0] >= 400)
				throw new Exception("The request return an error.", $d[0]);
		
		return $d;
		
	}
	private function HTTP_QUERY($uri, $method, $payload = "") {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		if ($method == "PUT") {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}
		
		$data = curl_exec($ch);
		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return [$return_code, $data];
	}
	
	
}

?>
