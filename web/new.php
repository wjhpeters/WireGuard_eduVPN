<?php require "conn.php";

$username = $_GET['user']; 
$password = $_GET['pass']; 
$dev_name = $_GET['devname']; 
$exp_date = $_GET['expdate']; 
$exp_date = strtotime($exp_date);
$exp_date = date('Y-m-d', $exp_date);
 
try {
	$stmt = $conn->prepare("SELECT user_pass FROM users WHERE username = '" . $username . "'"); 
	$stmt->execute();
	$hash = $stmt->fetchColumn();

	$password = strtoupper(hash('sha256', $password));

	if($password == $hash) { 

		$encryptedName = hash('sha256', $username);
		$stmt = $conn->prepare("SELECT ID FROM users WHERE username = '" . $username . "'"); 
		$stmt->execute();
		$userID = $stmt->fetchColumn();
		
		shell_exec('mkdir '.$encryptedName);
		shell_exec('sudo wg genkey | tee '.$encryptedName.'/'.$encryptedName.'_private | wg pubkey > '.$encryptedName.'/'.$encryptedName.'_public');

		$privateKey = shell_exec('cat '.$encryptedName.'/'.$encryptedName.'_private');
		$publicKey = shell_exec('cat '.$encryptedName.'/'.$encryptedName.'_public');

		$privateKey = substr($privateKey, 0, -1);
		$publicKey = substr($publicKey, 0, -1);

		$stmt = $conn->prepare("SELECT COUNT(ID) FROM devices");
		$stmt->execute();
		$ip_count = $stmt->fetchColumn();
		$ip_count = $ip_count + 2;

		$QR_code_content = '[Interface]
		Address = 10.200.200.'.$ip_count.'/32
		PrivateKey = '.$privateKey.'
		DNS = 1.1.1.1
		ListenPort = 51820

		[Peer]
		PublicKey = 15dMcxtL+ibbQJQoClOVyL0ewKPgWFId6QlPL8D0pUY=
		Endpoint = 145.100.181.164:51820
		AllowedIPs = 0.0.0.0/0
		PersistentKeepalive = 25';

		$conf_file = $encryptedName.'/tmp.conf';
		$handle = fopen($conf_file, 'w') or die('Cannot open file:  '.$conf_file);
		fwrite($handle, $QR_code_content);
		fclose($handle);

		shell_exec('sudo wg set wg0 peer '.$publicKey.' allowed-ips 10.200.200.'.$ip_count.'/32');
		
		$stmt = $conn->prepare("INSERT INTO devices (user_id, device_name, config, experation_date) VALUES (".$userID.", '".$dev_name."', '".$QR_code_content."', '".$exp_date."')"); 
		$stmt->execute();
		
		shell_exec('rm -R '.$encryptedName);
		
		echo $QR_code_content;
		
	} else {
		echo "Fail!";
	}
}
catch(PDOException $e) {
	echo $e;
	echo "Fail!";
}
$conn = null;
$stmt = null;
?>