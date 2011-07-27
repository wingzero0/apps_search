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
	public function WriteToFile(){
		$fp = fopen($this->outfile,"w");
		if ($fp == NULL){
			fprintf(STDERR, "%s can't be open(for write)\n", $this->outfile);
			return false;
		}
		fprintf($fp, "<Apps>\n<AppsName>%s</AppsName>\n", $this->AppsName);
		fprintf($fp, "<AppsLang>%s</AppsLang>\n", $this->AppsLang);
		fprintf($fp, "<Description>%s</Description>\n", $this->Description);
		$this->SpecialWrite($fp);
		fprintf($fp, "</Apps>\n");
		fclose($fp);
	}
}

class MacUknowParser extends WebpageParser{
	public $Price;
	public $SoftwareType;
	public function Parse(){
		try {
			$html = file_get_html($this->infile);
			$ret = $html->find("title");
			$this->Description = $ret[0]->plaintext."\n";
			$ret = $html->find("div[class=content]");
			$ParseFlag = false;
			fprintf(STDERR,"Parse function not complete\nThis parser only for MacUKnow iphone page\n");
			foreach ($ret as $i => $block){
				//echo "block ".$i."\n".$block->innertext."\n";
				$content = str_get_html($block->innertext);
				$flag = $content->find("fb:like");
				if ($flag){
					$des = preg_replace("/&nbsp;/u", "", $block->plaintext);
					$this->Description .= $des;
					
					$spec = $content->find("p[class=rtecenter]");
					$spec_content = str_get_html($spec[1]->innertext);

					$title = $spec_content->find("font[size]");
					$this->AppsName = $title[0]->plaintext;
					$this->Price = $title[1]->plaintext;
					$title = $spec_content->find("font[class=Apple-style-span]");
					$this->AppsLang = $title[0]->plaintext;
					
					$pattern = sprintf("/%s/u", preg_quote($this->AppsLang));
					$tmp = preg_replace($pattern, "", $spec_content->plaintext);
					$pattern = sprintf("/%s/u", preg_quote($this->Price));
					$tmp = preg_replace($pattern, NULL, $tmp);
					$pattern = sprintf("/%s/u", preg_quote($this->AppsName));
					$this->SoftwareType = preg_replace($pattern, NULL, $tmp);
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
		fprintf($fp, "<Price>%s</Price>\n", $this->Price);
		fprintf($fp, "<SoftwareType>%s</SoftwareType>\n",$this->SoftwareType);
		return ;
	}
}

class iapp extends WebpageParser{
	public $Price;
	public $SoftwareType;
	public $Requirement;
	public static function DomFind($html, $target){
		$ret = $html->find($target);
		if ($ret == NULL){
			fprintf(STDERR, "html parse fail: ". $target." not found\n");
			return NULL;
		}else{
			return $ret;
		}
	}
	public function SpecialWrite($fp){
		//virtuaL
		fprintf($fp, "<Price>%s</Price>\n", $this->Price);
		fprintf($fp, "<SoftwareType>%s</SoftwareType>\n",$this->SoftwareType);
		fprintf($fp, "<Requirement>%s</Requirement>\n",$this->Requirement);
		return ;
	}
	public function Parse(){
		try {
			$html = file_get_html($this->infile);
			
			$ret = iapp::DomFind($html,"title");
			$this->Description = $ret[0]->plaintext;
			
			$ret = iapp::DomFind($html, "div[id=app_show_data]");
			if ($ret == NULL){
				fprintf(STDERR, "skip %s\n", $this->infile);
				return false;
			}
			$spec = str_get_html($ret[0]->innertext);
			$title = iapp::DomFind($spec, "h3");
			$this->AppsName = $title[0]->plaintext;
			$spec_ret = iapp::DomFind($spec, "span[class=info]");
			$this->SoftwareType = $spec_ret[0]->plaintext;
			$this->Price = $spec_ret[1]->plaintext;
			$this->Requirement = $spec_ret[4]->plaintext;
			$this->AppsLang = $spec_ret[6]->plaintext;

			$ret = iapp::DomFind($html, "div[class=poster_papercontent]");
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
			$ret = iapp::DomFind($content, "div[class=paper getByCatPaper]");
			if ($ret ==NULL) {
				return false;
			}
			$content = str_get_html($ret[0]->innertext);
			$ret = iapp::DomFind($content, "a[href]");
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
}
?>
