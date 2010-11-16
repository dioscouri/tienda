package XML::Mini::TreeComponent;
use strict;
$^W = 1;

use Data::Dumper;
use XML::Mini;

use vars qw ( $VERSION );
$VERSION = '1.24';

sub new
{
    my $class = shift;
    my $self = {};
    bless $self, ref $class || $class;
    return $self;
}

sub name
{
    my $self = shift;
    my $setTo = shift; # optionally set
    return undef;
}

sub getElement
{
    my $self = shift;
    my $name = shift;
    return undef;
}

sub getValue
{
    my $self = shift;
    return undef;
}

sub parent
{
    my $self = shift;
    my $newParent = shift; # optionally set
    
    if ($newParent)
    {
	my $ownType = ref $self;
	my $type = ref $newParent;
	if ($type && $type =~ /^$ownType/)
	{
	    $self->{'_parent'} = $newParent;
	} else {
	    return XML::Mini->Error("XML::MiniTreeComponent::parent(): Must pass an instance derived from "
				    . "XML::MiniTreeComponent to set.");
	}
    }
    return $self->{'_parent'};
}

sub toString
{
    my $self = shift;
    my $depth = shift || 0;
    return undef;
}

sub dump
{
    my $self = shift;
    return Dumper($self);
}

sub _spaceStr
{
    my $self = shift;
    my $numSpaces = shift;
    my $retStr = '';
    if ($numSpaces)
    {
	$retStr = ' ' x $numSpaces;
    }
    return $retStr;
}

1;

__END__

=head1 NAME

XML::Mini::TreeComponent - Perl implementation of the XML::Mini TreeComponent API.

=head1 SYNOPSIS

Don't use this class - only presents an interface for other derived classes.

=head1 DESCRIPTION

This class is only to be used as a base class
for others.

It presents the minimal interface we can expect
from any component in the XML hierarchy.

All methods of this base class 
simply return NULL except a little default functionality
included in the parent() method.

Warning: This class is not to be instatiated.
Derive and override.

=head2 parent [NEWPARENT]

The parent() method is used to get/set the element's parent.

If the NEWPARENT parameter is passed, sets the parent to NEWPARENT
(NEWPARENT must be an instance of a class derived from XML::MiniTreeComponent)

Returns a reference to the parent XML::MiniTreeComponent if set, NULL otherwise.


=head2 toString [DEPTH]

Return a stringified version of the XML representing
this component and all sub-components

=head2 dump

Debugging aid, dump returns a nicely formatted dump of the current structure of the
XML::Mini::TreeComponent-derived object.

=head1 AUTHOR

LICENSE

    XML::Mini::TreeComponent module, part of the XML::Mini XML parser/generator package.
    Copyright (C) 2002 Patrick Deegan
    All rights reserved
    
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


Official XML::Mini site: http://minixml.psychogenic.com

Contact page for author available at http://www.psychogenic.com/en/contact.shtml

=head1 SEE ALSO


XML::Mini, XML::Mini::Document, XML::Mini::Element

http://minixml.psychogenic.com

=cut

