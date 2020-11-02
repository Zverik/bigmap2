#!/usr/bin/perl

# Removes all old images that exceed the quota.
# Written by Ilya Zverev, licensed WTFPL.

use strict;
use Cwd 'abs_path';
use File::Basename;

my $limit = 100 * 1024 * 1024; # in bytes
my $path = abs_path(dirname(__FILE__)).'../www/result';

opendir(my $dh, $path) or die "Cannot open path: $!";
my @images = sort { $b->[1] <=> $a->[1] } map { ["$path/$_", (stat "$path/$_")[9], (stat "$path/$_")[7]] } grep {/\.png$/} readdir $dh;
closedir $dh;

my $size = 0;
foreach(@images) {
	$size += $_->[2];
	#printf "%s: ctime=%d, size=%d, diff=%.1f MB\n", $_->[0], $_->[1], $_->[2], ($limit-$size)/1024/1024;
	unlink $_->[0] if $size > $limit;
}
