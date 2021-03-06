<?php

define("FAIL", -1);
define("READMORE", 1);
include_once dirname(__FILE__)."/simple_html_dom.php";

class WebpageParser{
	public $infile;
	public $outfile;
	//private $html;
	public $AppsName;
	public $AppsLang;
	public $Description;
	public $ParserName;
	public function __construct($infile, $outfile){
		$this->infile = $infile;
		$this->outfile = $outfile;
		$ret = mb_internal_encoding("UTF-8");
		if ($ret == false){
			fprintf(STDERR, "internal encoding fail\n");
		}
		$ret = mb_regex_encoding("UTF-8");
		if ($ret == false){
			fprintf(STDERR, "regex encoding fail\n");
		}
	}
	public function __destruct(){
		//system("rm $indexfile");
	}
	public static function GenerateNutchSeedURL($UrlsFileName){
		//virtual
		return false;
	}
	public function Parse(){
		return false;
	}
	public function SpecialWrite($fp){
		//virtual
	}
	public function ConstructUniqueKeyValue(){
		//virtual
	}
	public function ConstructField($name, $value){
		$s = sprintf("<field name=\"%s\">%s</field>", $name, $value);
		return $s;
	}
	public function WriteToFile(){
		$fp = fopen($this->outfile,"w");
		if ($fp == NULL){
			fprintf(STDERR, "%s can't be open(for write)\n", $this->outfile);
			return false;
		}
		fprintf($fp, "<add>\n<doc>\n");
		
		$unikey = $this->ConstructUniqueKeyValue();
		$tmp = $this->ConstructField("id", $unikey);
		fprintf($fp, "%s\n" ,$tmp);
		$tmp = $this->ConstructField("AppsName", $this->AppsName);
		fprintf($fp, "%s\n" ,$tmp);
		$tmp = $this->ConstructField("AppsLang", $this->AppsLang);
		fprintf($fp, "%s\n", $tmp);
		$tmp = $this->ConstructField("Description", $this->Description);
		fprintf($fp, "%s\n", $tmp);
		$this->SpecialWrite($fp);
		fprintf($fp, "</doc>\n</add>\n");
		fclose($fp);
	}
	public static function DomFind($html, $target){
		$ret = $html->find($target);
		if ($ret == NULL){
			fprintf(STDERR, "html parse fail: ". $target." not found\n");
			return NULL;
		}else{
			return $ret;
		}
	}
}

class MacUknowParser extends WebpageParser{
	//public $Price;
	public $SoftwareType;
	public function __construct($infile, $outfile){
		parent::__construct($infile,$outfile);
		$this->ParserName = "MacUKnow";
	}
	public function Parse(){
		try {
			$html = file_get_html($this->infile);
			$ret = $html->find("title");
			$this->Description = $ret[0]->plaintext."\n";
			$ret = $html->find("div[class=content]");
			$ParseFlag = false;
			foreach ($ret as $i => $block){
				//echo "block ".$i."\n".$block->innertext."\n";
				$content = str_get_html($block->innertext);
				$flag = $content->find("fb:like");
				if ($flag){
					$des = preg_replace("/&nbsp;/u", "", $block->plaintext);
					$des = preg_replace("/&/u", "", $des);
					$this->Description .= $des;
					
					$spec = $content->find("p[class=rtecenter]");
					if ($spec == NULL){
						fprintf(STDERR, "<AppsName> not found, skip %s\n", $this->infile);
						return false;
					}

					$spec_content = str_get_html($spec[1]->innertext);

					$title = $spec_content->find("span[style]");
					if ($title == NULL){
						fprintf(STDERR, "<AppsName> not found, skip %s\n", $this->infile);
						return false;
					}
					$this->AppsName = $title[0]->plaintext;
					// because the format is not consistent
					// I skip the AppsLang and Price
					$ParseFlag = true;
				}	
			}
			return $ParseFlag;
		}catch (Execption $e){
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			return false;
		}
	}
	public function SpecialWrite($fp){
		//virtual
		//fprintf($fp, "<Price>%s</Price>\n", $this->Price);
		$tmp = $this->ConstructField("SoftwareType", $this->SoftwareType);
		fprintf($fp, "%s\n",$tmp);
		return ;
	}
	public static function GenerateNutchSeedURL($UrlsFileName){
		try{
			$homepage = file_get_contents("http://www.macuknow.com/iphoneapps");
			$content = str_get_html($homepage);
			$ret = parent::DomFind($content, "li[class=pager-last last]");
			if ($ret ==NULL) {
				return false;
			}
			$content = str_get_html($ret[0]->innertext);
			$ret = parent::DomFind($content,"a[href]");
			if ($ret ==NULL) {
				return false;
			}
			$path = $ret[0]->href;
			$ret = preg_match("/iphoneapps\?page=(.*)/u", $path, $matches);
			if ($ret <= 0){
				fprintf(STDERR, "%s doesn't match\n", $path);
				return false;
			}
			$max = intval($matches[1]);

			$fp = fopen($UrlsFileName, "w");
			if ($fp == NULL){
				fprintf(STDERR, "%s open fail\n", $UrlsFileName);
				return false;
			}
			for ($i = 0;$i <= $max; $i++){
				fprintf($fp, "http://www.macuknow.com/iphoneapps?page=%d\n",$i);
			}
			fclose($fp);
			
			return true;
		}catch (Execption $e){
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			return false;
		}	
	}
	public function ConstructUniqueKeyValue(){
		//virtual
		return $this->ParserName."\t".$this->AppsName;
	}
}

class iappParser extends WebpageParser{
	public $Price;
	public $SoftwareType;
	public $Requirement;
	public function __construct($infile, $outfile){
		parent::__construct($infile,$outfile);
		$this->ParserName = "iapp";
	}
	public function SpecialWrite($fp){
		//virtual
		$tmp = $this->ConstructField("Price", $this->Price);
		fprintf($fp, "%s\n", $tmp);
		$tmp = $this->ConstructField("SoftwareType", $this->SoftwareType);
		fprintf($fp, "%s\n", $tmp);
		$tmp = $this->ConstructField("Requirement", $this->Requirement);
		fprintf($fp, "%s\n", $tmp);
		return ;
	}
	public function Parse(){
		try {
			$html = file_get_html($this->infile);

			$ret = parent::DomFind($html,"title");
			$this->Description = $ret[0]->plaintext;

			$ret = parent::DomFind($html, "div[id=app_show_data]");
			if ($ret == NULL){
				fprintf(STDERR, "skip %s\n", $this->infile);
				return false;
			}
			$spec = str_get_html($ret[0]->innertext);
			$title = parent::DomFind($spec, "h3");
			$this->AppsName = $title[0]->plaintext;
			$spec_ret = parent::DomFind($spec, "span[class=info]");
			$this->SoftwareType = $spec_ret[0]->plaintext;
			$this->Price = $spec_ret[1]->plaintext;
			$this->Requirement = $spec_ret[4]->plaintext;
			$this->AppsLang = $spec_ret[6]->plaintext;

			$ret = parent::DomFind($html, "div[class=poster_papercontent]");
			foreach ($ret as $i => $block){
				//echo "block ".$i."\n".$block->innertext."\n";
				$des = preg_replace("/&nbsp;/u", "", $block->plaintext);
				$des = preg_replace("/\r\n/u", "\n", $des);
				$des = preg_replace("/&/u", " ", $des);
				//$des = preg_replace("/\n/u", "", $des);

				$des = preg_replace("/<\/embed>/u", "", $des);
				$this->Description .= $des;
			}
			$ParseFlag = true;
			return $ParseFlag;
		}catch (Execption $e){
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			return false;
		}	
	}
	public static function GenerateNutchSeedURL($UrlsFileName){
		try{
			$homepage = file_get_contents("http://iapp.com.tw/all_rating.php?d=2");
			$content = str_get_html($homepage);
			$ret = parent::DomFind($content, "div[class=paper getByCatPaper]");
			if ($ret ==NULL) {
				return false;
			}
			$content = str_get_html($ret[0]->innertext);
			$ret = parent::DomFind($content, "a[href]");
			if ($ret == NULL){
				return false;
			}
			$max = intval($ret[9]->innertext);
			$fp = fopen($UrlsFileName, "w");
			if ($fp == NULL){
				fprintf(STDERR, "%s open fail\n", $UrlsFileName);
				return false;
			}
			for ($i = 0;$i <= $max; $i++){
				fprintf($fp, "http://iapp.com.tw/all_rating.php?d=2&page=%d\n",$i);
			}
			fclose($fp);
			return true;
		}catch (Execption $e){
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			return false;
		}	
	}
	public function ConstructUniqueKeyValue(){
		//virtual
		return $this->ParserName."\t".$this->AppsName;
	}
}
?>
