#!/bin/bash
inputdir=$1
outputdir=$2
parserdir=$3

#currentdir=`pwd`
mkdir $outputdir

#cd $inputdir

for input in `ls $inputdir/*.html`
do
	filename=`basename $input`
	php $parserdir/parse_it.php $inputdir/$filename $outputdir/$filename.xml 1
done

