<?php
require_once('crawler.php');

//can change this to anoymous function
//replace class 
//get list
class SoftwareListJob extends CrawlJob
{
	public function __construct($url, $setting = array())
	{
		parent::__construct($url, $setting);
	}
	public function process($html, $urlobj, $crawler)
	{
		echo curl_getinfo($urlobj->hd, CURLINFO_HTTP_CODE)."\n";
		$ul = $html->find('ul.page_news_list', 0);
		$lis = $ul->find('li');
		$items = array();
		foreach($lis as $li)
		{
			$item = array();
			$href = $li->find('a', 0);
			$item['url'] = $href->href;
			$item['title'] = $href->innertext;
			$item['time'] = $li->find('span', 0)->innertext;
			$items[] = $item;
		}
		//echo $html;
		return $items;
	}
	public function jobDone($results, $crawler)
	{
		var_dump($this->results);
		echo "all job done\n";

		$newjobs = array();
		foreach($results as $result)
		{
			//foreach($result as &$res)
			//{
			//    $res['url'] = 'http://software.hit.edu.cn'.$res['url'];
			//}
			$itemjob = new SoftwareItemJob($result);
			$newjobs[] = $itemjob;
		}
		$crawler->addJobs($newjobs);
		echo "add done\n";
		//var_dump($newjobs);
	}
	public function onError()
	{
		echo "error occur\n";
	}
}

//get each item
class SoftwareItemJob extends CrawlJob
{
	public function __construct($url, $setting = array())
	{
		parent::__construct($url, $setting);
	}
	public function process($html, $urlobj, $crawler)
	{
		//echo "get html\n $html\n";
		echo curl_getinfo($urlobj->hd, CURLINFO_HTTP_CODE)."\n";

		$item = array();
		$title = $html->find('h3#page_news_title', 0);
		if (!is_null($title))
			$item['title'] = $title->innertext;
		$newdate = $html->find('i#page_news_date', 0);
		if (!is_null($newdate))
		{
			var_dump($newdate->innertext);
		}

		return "success";
	}
	public function jobDone($results, $crawler)
	{
		var_dump($this->results);
		echo "all job done\n";

	}
	public function onError()
	{
		echo "error occur\n";
	}
}

$soft = new SoftwareListJob(
	array(
		//new Url('http://software.hit.edu.cn/article/show/763.aspx'), 
		new Url('http://software.hit.edu.cn/article/0/1.aspx')
	));

$crawler = new Crawler;
$crawler->start($soft);


?>
