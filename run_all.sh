#!/bin/bash

nutch_bin_dir=../nutch-1.3/runtime/local/bin/
nutch_url_dir=../nutch-1.3/url_test/
crawl_db_dir=../nutch-1.3/crawl_test/
crawl_dump_dir=../nutch-1.3/dump_test/
parser_dir=./
html_output_dir=../nutch-1.3/html_test/
xml_output_dir=../nutch-1.3/xml_test/
parser_type=0

#crawl
#bash crawler.sh $nutch_bin_dir $nutch_url_dir $crawl_db_dir $crawl_dump_dir $parser_dir $parser_type

#parse
bash parse_it.sh $parser_dir $crawl_dump_dir $html_output_dir $xml_output_dir $parser_type
