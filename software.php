<?php
//$proxy = "http://127.0.0.1:8087";
$agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.65 Safari/537.31";
//$referer = "http://www.apple.com.cn/itunes/50-billion-app-countdown/";

$curl = curl_init();
$url = "http://software.hit.edu.cn/article/show/759.aspx";
// 设置你需要抓取的URL
curl_setopt($curl, CURLOPT_URL, $url);
//curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
//curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1");         
//curl_setopt($curl, CURLOPT_PROXYPORT, 8087);
curl_setopt($curl, CURLOPT_USERAGENT, $agent);
//curl_setopt($curl, CURLOPT_REFERER, $referer);
//curl_setopt ($curl, CURLOPT_PROXY, $proxy);
curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);


curl_setopt($curl, CURLOPT_POST, false);
//curl_setopt($curl, CURLOPT_POSTFIELDS, urlencode("channel_type=article&digg_type=bad&id=673"));

// 设置header
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 	

// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。

// 运行cURL，请求网页
$i = 0;
$s=microtime(true);
$data = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
echo "$code \n$data\n";
$e = microtime(true);
echo ($e - $s)."s\n";

print curl_error($curl);
?>
