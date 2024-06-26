#!/usr/bin/perl

# Converts tiles.txt to a javascript code to be included in index.html
# Written by Ilya Zverev, licensed WTFPL.

use strict;
use Cwd 'abs_path';
use File::Basename;

my $wwwroot = abs_path(dirname(__FILE__)).'/../www';

open INDEX, '<'.$wwwroot.'/index.html' or die "No index.html found: $!";
my @index;
push @index, $_ while <INDEX>;
close INDEX;

open TILES, '<'.$wwwroot.'/tiles.txt' or die "No tiles.txt found: $!";
my @base;
my @overlay;
while(<TILES>) {
	next if /^\s*#/;
	chomp;
	my @arr = split /\s*,\s*/;
	push @{ $arr[1] ? \@overlay : \@base }, \@arr if $#arr >= 4;
}
close TILES;

my $res = "\tbase = {\n";
$res .= print_code($_) foreach @base;
$res =~ s/,\n$/\n/;
$res .= "\t};\n";

$res .= "\toverlay = {\n";
$res .= print_code($_) foreach @overlay;
$res =~ s/,\n$/\n/;
$res .= "\t};\n";

open INDEX, '>'.$wwwroot.'/index.html' or die "Cannot write to index.html: $!";
my $print = 1;
foreach my $line (@index) {
	$print = 1 if $line =~ m#// END GENERATED#;
	print INDEX $line if $print;
	if( $line =~ m#// GENERATED# ) {
		$print = 0;
		print INDEX $res;
	}
}
close INDEX;

sub print_code {
	my $line = shift;
	my $url = $line->[4];
	my $sub = '';
	if( $url =~ /{([a-z0-9]+)}/ ) {
		$sub = ", subdomains: '$1'";
		$url =~ s/$1/s/;
	}
	$url =~ s/!([xyz])/{\1}/g;
	my $attr = $line->[5];
	$attr = 'Map data &copy; <a href="https://www.openstreetmap.org">OpenStreetMap</a>'.($attr ? ' | ' : '').$attr if !$line->[1];
	$attr =~ s/"/\\"/g;

	my $res = '';
	$res .= "\t\t'".$line->[0]."': L.tileLayer('$url', {\n";
	$res .= "\t\t\tname: '".$line->[0]."', minZoom: ".$line->[2].", maxZoom: ".$line->[3].$sub.",\n";
	$res .= "\t\t\tattribution: '$attr'\n";
	$res .= "\t\t}),\n";
	return $res;
}
