<?php
require_once('crawler.php');

class DemoJob extends CrawlJob
{
	//$html: simple_html_dom object, refer to simple_html_dom
	//$urlobj: Url object
	//$crawler: Crawler object
	//return processed data to handle in jobDone()
	//like:
	//$result = '123';
	//return $result;
	//this is called when a url site is fetched.
	public function process($html, $urlobj, $crawler)
	{
		//urlobj->hd is curl handler object
		$code = curl_getinfo($urlobj->hd, CURLINFO_HTTP_CODE)."\n";
		switch(intval($code))
		{
		case 200: //normal return code
			echo $html."\n";
			break;
		case 302://object removed
			$redir = $html->find('h2 a', 0);
			$item['redir'] = $redir->href;
			break;
		case 404:
			break;
		default:
			echo "unknown return code $code\n";
			break;
		}

		return $code;
	}
	//this is called when all url is proceeded(fetched or error occur)
	public function jobDone($crawler)
	{
		echo "done count: ".$this->urlGetCount."\n";
		var_dump($this->results);
		echo "all job done\n";

	}
	public function onError()
	{
		echo "error occur\n";
		var_dump($this->errors);
	}
}

$soft = new DemoJob(
	array(
		new Url('http://en.wikipedia.org/wiki/Aerosmith'),
		new Url('http://en.wikipedia.org/wiki/Dreamtheater')
	));

$crawler = new Crawler;
$crawler->start($soft);


?>
