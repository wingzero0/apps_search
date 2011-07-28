#!/bin/bash
nutch_dir=$1
urls_dir=$2
crawl_db_dir=$3
crawl_dump_dir=$4
parser_dir=$5
parser_type=$6

JAVA_HOME=/usr/
export JAVA_HOME

#generate seed urls for nutch
mkdir $urls_dir
php $parser_dir/Nutch_Url_Generate.php $urls_dir/nutch $parser_type

#crawl
$nutch_dir/nutch crawl $urls_dir -dir $crawl_db_dir -depth 2 -topN 1000000

#dump html
#only the last target_dir in crawl_db_dir/segments/ is useful for our program
target_dir=.
for tmp in `ls $crawl_db_dir/segments/`
do
	#echo $tmp
	target_dir=$tmp
done

$nutch_dir/nutch readseg -dump $crawl_db_dir/segments/$target_dir/ $crawl_dump_dir -nofetch -nogenerate -noparse -noparsedata -noparsetext
