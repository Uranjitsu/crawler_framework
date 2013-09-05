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
	public function jobDone($crawler)
	{
		//var_dump($this->results);
		echo "all job done\n";

		$newjobs = array();
		foreach($this->results as $result)
		{
			foreach($result as &$res)
			{
			   $res['url'] = 'http://software.hit.edu.cn'.$res['url'];
			}
			$itemjob = new SoftwareItemJob($result);
			$newjobs[] = $itemjob;
		}
		$crawler->addJobs($newjobs);
		var_dump($newjobs);
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
		$code = curl_getinfo($urlobj->hd, CURLINFO_HTTP_CODE)."\n";
		$item = array();
		switch(intval($code))
		{
		case 200: //normal return code
			$title = $html->find('h3.page_news_title', 0);
			if (!is_null($title))
				$item['title'] = $title->innertext;
			$newdate = $html->find('i.page_news_date', 0);
			if (!is_null($newdate))
			{
				$data = preg_split('/&nbsp;&nbsp;/', $newdate->innertext, -1, PREG_SPLIT_NO_EMPTY);
				$item['time'] = $data[0];
				$srstr = preg_split('/：/', trim($data[1]));
				$item['source'] = trim($srstr[1]);
				$adstr = preg_split('/：/', $data[2]);
				$item['admin'] = trim($adstr[1]);
			}
			$content = $html->find('div.page_content', 0);
			$item['content'] = $content->innertext;
			break;
		case 302://object removed
			echo $html."\n";
			$redir = $html->find('h2 a', 0);
			$item['redir'] = $redir->href;
			$item['title'] = $urlobj->title;
			$item['time'] = $urlobj->time;
			break;
		default:
			echo "unknown return code $code\n";
			break;

		}

		return $item;
	}
	public function jobDone($crawler)
	{
		echo "done count: ".$this->urlGetCount."\n";
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
