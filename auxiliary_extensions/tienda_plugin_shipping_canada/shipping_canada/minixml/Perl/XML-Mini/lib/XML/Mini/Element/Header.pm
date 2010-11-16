package XML::Mini::Element::Header;
use strict;
$^W = 1;

use XML::Mini;
use XML::Mini::Element;

use vars qw ( $VERSION @ISA );
$VERSION = '1.00';
push @ISA, qw ( XML::Mini::Element );

sub new
{
    my $class = shift;
    my $name = shift || XML::Mini->Error("Must pass a NAME to XML::Mini::Element::Header::new()");
    
    my $self = {};
    bless $self, ref $class || $class;
    $self->{'_attributes'} = {};
    $self->{'_numChildren'} = 0;
    $self->{'_numElementChildren'} = 0;
    $self->{'_children'} = [];
    $self->{'_avoidLoops'} = $XML::Mini::AvoidLoops;
    $self->name($name);
    return $self;
}

sub toString
{
    my $self = shift;
    my $depth = shift;
    
    if ($depth == $XML::Mini::NoWhiteSpaces)
    {
	return $self->toStringNoWhiteSpaces();
    }
    
    my $spaces = $self->_spaceStr($depth);
    
    my $retString = "$spaces<?". $self->name() . ' ';
    
    my $attribString;
    foreach my $atName (sort keys %{$self->{'_attributes'}})
    {
	$attribString .= qq|$atName="$self->{'_attributes'}->{$atName}" |;
    }
    
    if (defined $attribString && $attribString =~ m|\S|)
    {
    	$retString .= $attribString;
    }
    
    $retString =~ s/\s+$//;
    
    $retString .= "?>\n";
    return $retString;
    
}


sub toStringNoWhiteSpaces
{
    my $self = shift;
    
    my $retString = '<? ' . $self->name();
    my $attribString;
    foreach my $atName (sort keys %{$self->{'_attributes'}})
    {
	$attribString .= qq|$atName="$self->{'_attributes'}->{$atName}" |;
    } 
    if (defined $attribString && $attribString =~ m|\S|)
    {
    	$retString .= $attribString;
    }
    $retString =~ s/\s+$//;
    $retString .= "?>";
    return $retString;
}

1;

__END__

=head1 NAME

XML::Mini::Element::Header

=head1 DESCRIPTION

The XML::Mini::Element::Header is used internally to represent <? xml blah="hohoho" ?> type headers.

You shouldn't need to use it directly, see XML::Mini::Element's header() method.

=head1 AUTHOR

LICENSE

    XML::Mini::Element::Header module, part of the XML::Mini XML parser/generator package.
    Copyright (C) 2003 Patrick Deegan
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
