<?php

define("FAIL", -1);
define("READMORE", 1);

if ($argc >= 4){
	$infile = $argv[1];
	$outfile = $argv[2];
	$indexfile = $argv[3];
}else{
	$infile = "iphone_MacUknow/dump";
	$outfile = "html_MacUknow/iphone";
 	$indexfile = "html_MacUknow/index.txt";	
}
$N = new NutchesParser($infile, $outfile, $indexfile);
$N->Split_file();

class NutchesParser{
	public $infile;
	public $indexfile;
	public $outfile;
	public $RecnoCount;
	public function __construct($infile, $outfile, $indexfile){
		$this->infile = $infile;
		$this->indexfile = $indexfile;
		$this->outfile = $outfile;
		$this->RecnoCount = 0;
		mb_internal_encoding("UTF-8");
	}
	public function __destruct(){
		//system("rm $indexfile");
	}
	public function Split_file(){
		$fp = fopen($this->infile, "r");
		$fpi = fopen($this->indexfile, "w");
		if ($fp == NULL){
			fprintf(STDERR, "%s can't be open(for read)\n", $this->infile);
			return -1;
		}
		if ($fpi == NULL){
			fprintf(STDERR, "%s can't be open(for write)\n", $this->index);
			return -1;
		}
		while (!feof($fp)){
			$line = fgets($fp);
			//$ret = mb_ereg_match("/^Recno/", $line);
			//$ret = mb_ereg_match("/Recno/", $line);
			$ret = preg_match("/^Recno/", $line);
			if ($ret == true){
				echo $this->RecnoCount."\n";
				while ($this->GetContent($fp, $fpi) == READMORE){
					echo $this->RecnoCount."\n";
				}
			}
		}
		fclose($fp);
		return 1;
	}
	public function GetContent($fp, $fpi){
		$filename = $this->outfile.$this->RecnoCount.".html";
		$this->RecnoCount++;
		$fpout = fopen($filename, "w");
		if ($fpout == NULL){
			fprintf(STDERR, "%s can't be open(for write)\n", $filename);
			return FAIL;
		}

		try{
			// find URL::
			$line = fgets($fp);
			//echo $line;
			$ret = preg_match("/^URL:: (.*)/", $line, $matches);
			if ($ret == true){
				fprintf($fpi, "%s %s\n", $filename, $matches[1]);
			}else{
				fprintf(STDERR, "error file format:\n%s", $line);
				return -1; // error file format;
			}
			for ($i = 0;$i< 7 ;$i++){
				// the next 7 lines in nutches dump file should be 
				// empty line, Content::, Version:, url:, base:, contentType:, metadata:
				$line = fgets($fp);
			}
			$line = fgets($fp);
			$ret = preg_match("/^Content:$/", $line, $matches);
			if ($ret == true){
				fprintf(STDERR, "%s GET_CONTENT\n", $filename);
			}else{
				fprintf(STDERR, "error file format:\n%s", $line);
				return FAIL; // error file format;
			}

			while (!feof($fp)){
				$line = fgets($fp);
				$ret = preg_match("/^Recno::/", $line, $matches);
				if ($ret == true){
					fclose($fpout);
					return READMORE;
				}else{
					fprintf($fpout, "%s", $line);
				}
			}
			fclose($fpout);
			return 0;
		} catch(Exception $e){
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			fclose($fpout);
			return FAIL;
		}
	}
}
?>
