#Simple PHP Crawler

##Motivation
----------
After writing several crawlers of specific web site, I decided to write a easy framework to reduce my work.Now it is.

I know PHP is `sucks` for crawl websites for not supporting multi thread, but since it's the only script language I'm familiar with, this work is started.

##Features
----------
*	easy use
*	object-oriented
*	support crawl multi pages in a time,like multi thread which is not (= =)

##Usage	
----------
To use this is really simple.

*	first extend class CrawlJob
*	complete three function
	*	process()
	*	jobDone()
	*	onError()	

That's it!!!

Demo:

```php

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
		//this is called after all url is proceeded(fetched or error occur)
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
`````

Actually, the crawler is based on `curl_multi_*`. `curl_multi_select` is used. So you can add a lot urls into `crawler` and expect all the request are sended in one time. But as you know, php is a single process script, so only one url can be processed in one time and process will block at curl_multi_select().

###Crawler Setting

**Use Proxy**

```php

$soft = new DemoJob(
array(      
    new Url('http://en.wikipedia.org/wiki/Aerosmith'),                               
    new Url('http://en.wikipedia.org/wiki/Dreamtheater')                             
), array(   
    'useProxy' => CRAWL_PROXY_HTTP,                                                  
    'proxyAddr' => '127.0.0.1:8087'                                                  
));  
```

**Change referer**

By default, referer is set as your site to crawl.If your crawl object is `test.com/a.php`,then referer is set to `test.com`.However, you can change it by

```php

$soft = new DemoJob(
    array(
        new Url('http://en.wikipedia.org/wiki/Aerosmith'),                               
        new Url('http://en.wikipedia.org/wiki/Dreamtheater')                             
    ), array(
        'referer' => 'www.a.com'                                                                                                                                                  
    ));

```

**Set timeout**
By default, `connection time out` is 30s, `response time out` is 20s, you can change it like:

```php
$soft = new DemoJob(
    array(      
        new Url('http://en.wikipedia.org/wiki/Aerosmith'),                               
        new Url('http://en.wikipedia.org/wiki/Dreamtheater')                             
    ), array(       
        'conn_timeout' => 10,                                                            
        'resp_timeout' => 50                                                                                                                                                      
    ));   
```
 
**Post field in Url**
By default, urls is requested in 'GET' mode.If your sites need post param, you need set in your URL object.

```php
$soft = new DemoJob(
        new Url('http://en.wikipedia.org/wiki/Aerosmith', 'POST', 'para1=val1&para2=val2')
    );   
    
```


##ToDo
Todo List:
 - [ ] support all curl setting
 - [ ] complete error handling
 - [ ] change abstract class + abstract method into class+function

#Thanks
*	[simple_html_dom](http://simplehtmldom.sourceforge.net/): written by S.C. Chen. For usage, please [refer](http://simplehtmldom.sourceforge.net/).
