<?php

//rely on simple_html_dom 
require_once('simple_html_dom.php');

define('CRAWL_URL_ERROR', -1);
define('CRAWL_PROXY_HTTP', CURLPROXY_HTTP);
define('CRAWL_PROXY_SOCKS5', CURLPROXY_SOCKS5);

class Crawler
{
	private $jobs; //array contain CrawlJob objects
	private $has_start;//
	private $mh;

	public function __construct()
	{
		$this->jobs = array();
		$this->mh = curl_multi_init();
		$this->has_start = false;
	}
	public function __destruct()
	{
		curl_multi_close($this->mh);
	}

	public function getUrlContent()
	{
		$this->has_start = true;
		$active = null;

		do {
			$mrc = curl_multi_exec($this->mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) 
		{
			if (curl_multi_select($mh) != -1) 
			{
				do {
					$mrc = curl_multi_exec($this->mh, $active);
					$info = curl_multi_info_read($mh);
					if (false !== $info) {
						var_dump($info);
					}
					$this->process($info);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
	}

	public function process($info)
	{
		$cjob = null;
		$jobkey = -1;
		$urlNum = -1;
		foreach($this->jobs as $key => $&job)
		{
			foreach($job->urlArray as $num => $url)
			{
				if ($url->hd == $info->handle)
				{
					$cjob = &$job;
					$jobkey = $key;
					$urlNum = $num;
					break 2;
				}
			}
		}

		if (is_null($cjob))
		{
			echo "handle could not be found.\n";
			var_dump($info);
			return false;
		}
		if ($info->result != CURLE_OK)
		{
			$cjob->setError(array($info->result, $info));
			return false;
		}
		if ($cjob->urlDone($urlNum))
		{
			unset($this->jobs[$jobkey]);
		}	
		curl_multi_remove_handle($this->mh, $info->handle);
	}

	//jobs: object or array contain objects
	public function addJobs($jobs)
	{

		if ($this->has_start)
		{
			if ($jobs instanceof CrawlJob)
			{ 
				$this->processJob($jobs);
				$this->jobs[] = $jobs;
			}else{
				foreach($jobs as &$job)
				{
					$this->processJob($job);
				}
				$this->jobs = array_merge($this->jobs, $jobs);
			}
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}else{
			if ($jobs instanceof CrawlJob)
			{ 
				$this->jobs[] = $jobs;
			}else{
				$this->jobs = array_merge($this->jobs, $jobs);
			}
		}

	}

	private function processJob($job)
	{
		$setting = $job->getSetting();

		foreach($job->urlArray as &$url)
		{
			$options = array(
				CURLOPT_AUTOREFERER => $setting['autoref'],
				CURLOPT_HEADER => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => $setting['agent'],
				CURLOPT_CONNECTTIMEOUT => $setting['conn_timeout'],
				CURLOPT_TIMEOUT => $setting['resp_timeout']
			);

			if (!$setting['autoref'])
			{
				$options[CURLOPT_REFERER] = $setting['referer'];
			}

			if ($setting['useProxy'])
			{
				$options[CURLOPT_PROXYTYPE] = $setting['useProxy'];
				$options[CURLOPT_PROXY] = $setting['proxyAddr'];
				if (strlen($setting['proxyUsrPwd']))
				{
					$options[CURLOPT_PROXYUSERPWD] = $setting['proxyUsrPwd'];
				}
			}

			if (strcmp(strtoupper($url->method), 'GET' ) == 0)
			{
				$options[CURLOPT_URL] = $url->url;
			}else if (strcmp(strtoupper($url->method), 'POST') == 0)
			{
				$options[CURLOPT_URL] = $url->url;
				$options[CURLOPT_POST] = true;
				$options[CURLOPT_POSTFIELDS] = $url->data;
			}else{
				$job->setError(array(CRAWL_URL_ERROR=> $url));
				$job->onError();
				continue;
			}

			$hd = curl_init($options);
			curl_multi_add_handle($this->mh,$hd);
			$url->hd = $hd;
		}
	}

	//jobs: CrawlJob object or array contain CrawlJob objects
	public function start($jobs = null)
	{
		if (!is_null($jobs))
		{
			if ($jobs instanceof CrawlJob)
			{ 
				$this->jobs[] = $jobs;
			}else{
				$this->jobs = array_merge($this->jobs, $jobs);
			}
		}

		if (count($this->jobs) == 0)
		{
			echo "empty job array\n";
			return false;
		}

		//add jobs
		foreach($this->jobs as $job)
		{
			$this->processJob($job);
		}

		$this->getUrlContent();
		return true;
	}
}	

//Use this by extend and complete two function
abstract class CrawlJob
{
	//array contain Url objects 
	public $urlArray; 
	private $setting;
	private $errors;
	private $urlGetCount;
	//$url:
	//     Url object or array('url' => '', 'method' => 'GET', 'data' => '')
	//$setting: array
	//         ('referer', 'agent', 'useProxy', 'proxyPort', 'proxyIp')
	public function __construct($url, $setting)
	{
		if ($url instanceof Url)
		{ 
			$this->urlArray = array($url);
		}

		if (is_array($url))
		{
			if (array_key_exists('url', $url))
			{
				$obj = new Url($url['url']);
				foreach($url as $key => $val)
				{
					$obj->$key = $val;
				}	
				$this->urlArray = array($obj);
			}else{
				$this->urlArray = $url;
			}
		}

		DefaultCrawlSetting::completeSetting($setting);
		$this->setting = $setting;
		$this->urlGetCount = 0;
	}

	public function urlDone($no)
	{
		$this->urlGetCount++;

		$data = curl_multi_getcontent($this->urlArray[$no]->hd);
		$html = new simple_html_dom($data);

		$this->process($html, $this->urlArray[$no]->hd);
		if ($this->urlGetCount === count($this->urlArray))
		{
			$this->jobDone();
			return true;
		}
		return false;
	}
	public function getSetting()
	{
		return $setting;
	}
	public function setError($err)
	{
		$this->errors = $err;
	}
	//return processed data to save
	//like:
	//$result = '123';
	//return $result;
	abstract public function process();
	//$result: result return by process();
	abstract public function save($result);
	abstract public function onError();
	abstract public function jobDone();

}

class DefaultCrawlSetting
{
	private static $conn_timeout = 30;//sec
	private static $resp_timeout = 20;//sec
	private static $referer = '';
	private static $agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.65 Safari/537.31';
	private static $useProxy = false;//default to false, otherwise should be CRAWL_PROXY_HTTP or CRAWL_PROXY_SOCKS5
	//proxy addr
	//192.168.0.1:8087
	private static $proxyAddr;
	private static $proxyUsrPwd = '';

	public static function completeSetting($setting)
	{
		if (is_array($setting))
		{
			$array = get_class_vars(get_class(new DefaultCrawlSetting));
			foreach($array as $key => $val)
			{
				if (!array_key_exists($key, $setting))
				{
					$setting[$key] = $val;
				}
			}
			if (strlen($setting['referer']) == 0)
			{
				$setting['autoref'] = true;
			}else{
				$setting['autoref'] = false;
			}
		}
	}

}

class Url
{
	public $url;
	public $method;
	//data could be string or array
	//ref: php curl_setopt CURLOPT_POSTFIELDS options
	public $data;
	public $hd;
	public function __construct($url, $method = 'GET', $data = null)
	{
		$this->url = $url;
		$this->method = $method;
		$this->data = $data;
	}
}
?>
