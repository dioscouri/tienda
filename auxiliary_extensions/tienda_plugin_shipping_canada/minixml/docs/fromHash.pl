#!/usr/bin/perl

use Data::Dumper;


use strict;
use XML::Mini::Document;


print "Content-type: text/plain\r\n\r\n";





my $xmlDoc = XML::Mini::Document->new();

my $xmlStructure = {
   # Create a <spies> element to hold our team
   "spies" => {
   			
		# Make a list of SPY people
		"spy"	=> [
				{
					# Bond, J. Bond
					'id'	=> '007',
					'type'	=> 'SuperSpy',
					'name'	=> 'James Bond',
					'email'	=> 'mi5@london.uk',
					'address'	=> 'Wherever he is needed most',
				},

				{
					# You are # 6
					'id'	=> '6',
					'type'	=> 'RetiredSpy',
					'name'	=> 'Number 6',
					'email'	=> {
										'type'	=> 'private',
										'-content' => 'mi6@london.uk',
										'location'	=> 'office'},
					'address'	=> '123 Island Prison Lane',
				},
				
				{	
					# inspector Gadget
					
					'name'	=> 'Inspector Gadget',
					'id'	=> '13',
					'type'	=> 'NotReallyASpy',
					'email'	=> 'lost@aol.com',
					'friends' => {

						'friend' => [
								# Gadget's first  friend
								{
										'name' => {
												'first' => 'little',
												'last'	=> 'girl',
											},
										'age'	=> 12,

										'hair'	=> {
												'color'	=> 'brown',
												'length'=> 'long',
											},
								},

								# Gadget's only other friend
								{
										'name'	=> {
												'first'	=> 'smelly',
												'last'	=> 'dog'},
										'age'	=> 14,

										'hair'	=> {
												'color'	=> 'dirtry blond',
												'length'=> 'short',
											},
									},

							] # end of list of Gadget's individual FRIENDs 

					} # end of the 'friends' element

				}, # end description of SPY "Inspector Gadget"

			], # end list of individual spies

		}, # end spies element
};

				

my $options = {
	'attributes'	=> {
				'-all'	=> ['type', 'color'],
				'spy'	=> 'id',
				'email' => ['location'],
				'hair'	=> 'length',
	},
};



$xmlDoc->fromHash($xmlStructure, $options);
print "\n\n\nParsed ARRAY looks like this:\n";
print Dumper($xmlDoc->toHash());

print "\n\nOUTPUT of fromArray() *with* OPTIONAL 'attributes' options set (for spy:id, email:location, hair:length, type, color)\n";

print $xmlDoc->toString();


