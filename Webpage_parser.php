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
			foreach ($ret as $i => $block){
				//echo "block ".$i."\n".$block->innertext."\n";
				$content = str_get_html($block->innertext);
				$flag = $content->find("fb:like");
				if ($flag){
					$ParseFlag = true;
					$title = $content->find("font[size]");
					$this->AppsName = $title[0]->plaintext;
					$this->Price = $title[1]->plaintext;
					$spec = $content->find("p[class=rtecenter]");
					print_r($spec);
					//$des = mb_ereg_replace("/&nbsp;/u", "", $block->plaintext);
					//$des = mb_ereg_replace("/bsp;/", "", $block->plaintext);
					$des = preg_replace("/&nbsp;/u", "", $block->plaintext);
					//$this->Description = $block->plaintext;
					$this->Description .= $des;
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
		return ;
	}
}

class iapp extends WebpageParser{
	public $Price;
	public $SoftwareType;
	public function DomFind($html, $target){
		$ret = $html->find($target);
		if ($ret == NULL){
			fprintf(STDERR, "html parse fail: ". $target." not found");
			return NULL;
		}else{
			return $ret;
		}
	}
	public function SpecialWrite($fp){
		//virtuaL
		fprintf($fp, "<Price>%s</Price>\n", $this->Price);
		fprintf($fp, "<SoftwareType>%s</SoftwareType>\n",$this->SoftwareType);
		return ;
	}
	public function Parse(){
		try {
			$html = file_get_html($this->infile);
			
			$ret = $this->DomFind($html,"title");
			$this->Description = $ret[0]->plaintext;
			
			$ret = $this->DomFind($html, "div[id=app_show_data]");
			$spec = str_get_html($ret[0]->innertext);
			$title = $this->DomFind($spec, "h3");
			$this->AppsName = $title[0]->plaintext;
			$spec_ret = $this->DomFind($spec, "span[class=info]");
			$this->SoftwareType = $spec_ret[0]->plaintext;
			$this->Price = $spec_ret[1]->plaintext;
			$this->AppsLang = $spec_ret[6]->plaintext;

			$ret = $this->DomFind($html, "div[class=poster_papercontent]");
			foreach ($ret as $i => $block){
				//echo "block ".$i."\n".$block->innertext."\n";
				$des = preg_replace("/&nbsp;/u", "", $block->plaintext);
				$des = preg_replace("/\r\n/u", "\n", $des);
				$this->Description .= $des;
			}
			$ParseFlag = true;
			return $ParseFlag;
		}catch (Execption $e){
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			return false;
		}	
	}
}
?>
