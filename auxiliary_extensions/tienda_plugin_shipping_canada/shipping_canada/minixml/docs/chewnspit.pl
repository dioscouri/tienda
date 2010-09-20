#!/usr/bin/perl

use XML::Mini::Document;

print "Content-type: text/plain\r\n\r\n";

$xmlDoc = XML::Mini::Document->new();
 
$XML::Mini::AutoEscapeEntities = 0;

$xmlDoc->fromFile('./test.xml');

print $xmlDoc->toString();
