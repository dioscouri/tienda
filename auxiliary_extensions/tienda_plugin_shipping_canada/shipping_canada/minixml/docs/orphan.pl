#!/usr/bin/perl

use XML::Mini::Document;
	
	print "Content-type: text/plain\r\n\r\n";

	my $xmlDoc = XML::Mini::Document->new();
	
	# Fetch the ROOT element for the document
	# (an instance of XML::Mini::Element)
	my $xmlElement = $xmlDoc->getRoot();
	
	# Create a sub element
	my $newChild = $xmlElement->createChild('mychild');
	
	$newChild->text('hello mommy');
	
	
	# Create an orphan element
	
	my $orphan = $xmlDoc->createElement('annie');
	$orphan->attribute('hair', '#ff0000');
	$orphan->text('tomorrow, tomorrow');
	
	# Adopt the orphan
	$newChild->prependChild($orphan);
	


	my $toy = $xmlDoc->createElement('toy');
	$toy->attribute('color', '#0000ff');
	$toy->createChild('type', 'teddybear');

	$newChild->insertChild($toy, 1);
	
	print $xmlDoc->toString();

	print "\nUhm, it's not working out - she won't stop singing... Calling removeChild()\n\n";

	$newChild->removeChild($orphan);
	$newChild->text('???');

	print $xmlDoc->toString();
	
	exit(0);


