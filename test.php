<?php
require_once('crawler.php');

//can change this to anoymous function
//replace class 
class SoftwareJob extends CrawlJob
{
	public function __construct($url, $setting = array())
	{
		parent::__construct($url, $setting);
	}
	public function process($html, $urlobj)
	{
		//echo "get html\n $html\n";
		echo "get html: ".$urlobj->url."\n";
		return "success";
	}
	public function jobDone($results)
	{
		var_dump($this->results);
		echo "all job done\n";

	}
	public function onError()
	{
		echo "error occur\n";
	}
}

$soft = new SoftwareJob(
	array(
		new Url('http://software.hit.edu.cn/article/show/763.aspx'), 
		new Url('http://software.hit.edu.cn/article/show/762.aspx')
	));

$crawler = new Crawler;
$crawler->start($soft);


?>
