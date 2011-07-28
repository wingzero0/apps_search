#!/bin/bash
parser_dir=$1
crawl_dump_dir=$2
html_output_dir=$3
xml_output_dir=$4
parser_type=$5

mkdir $html_output_dir
mkdir $xml_output_dir

#parse into html
php $parser_dir/Nutch_parser.php $crawl_dump_dir/dump $html_output_dir/iphone $html_output_dir/index.txt

for input in `ls $html_output_dir/*.html`
do
	filename=`basename $input`
	php $parser_dir/parse_it.php $html_output_dir/$filename $xml_output_dir/$filename.xml $parser_type
done

