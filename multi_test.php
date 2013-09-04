<?php
//create both cURL resources
$ch1 = curl_init();
$ch2 = curl_init();


// set URL and other appropriate options
curl_setopt($ch1, CURLOPT_URL, "http://www.qq.com");
curl_setopt($ch1, CURLOPT_HEADER, 0);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1); 	
curl_setopt($ch2, CURLOPT_URL, "http://software.hit.edu.cn");
curl_setopt($ch2, CURLOPT_HEADER, 0);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1); 	


//create the multiple cURL handle
$mh = curl_multi_init();

//add the two handles
curl_multi_add_handle($mh,$ch1);
curl_multi_add_handle($mh,$ch2);

$active = null;
//execute the handles
echo "start multi\n";
do {
	$mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);
echo "first while stop\n";

echo "enter second while\n";
while ($active && $mrc == CURLM_OK) {
	if (curl_multi_select($mh) != -1) {
		do {
			$mrc = curl_multi_exec($mh, $active);
			$info = curl_multi_info_read($mh);
			if (false !== $info) {
				var_dump($info);
			}
			if ($info['handle'] == $ch2)
			{
				echo "in\n";
				$ch3 = curl_init();
				curl_setopt($ch3, CURLOPT_URL, "http://www.hit.edu.cn");
				curl_setopt($ch3, CURLOPT_HEADER, 0);
				curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1); 	
				curl_multi_add_handle($mh,$ch3);
				do {
					$mrc = curl_multi_exec($mh, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
				echo "end select\n";
			}
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	}
}

echo "second while stop\n";
//close the handles
curl_multi_remove_handle($mh, $ch1);
curl_multi_remove_handle($mh, $ch2);
//curl_multi_remove_handle($mh, $ch3);
curl_multi_close($mh);

?>
