#!/usr/bin/perl

# Check the download queue of BigMap 2 and download some tiles.
# There can be tiles with errors, need to redownload them.
# When all tiles are downloaded, make an image out of it.
# And slap attribution on top.

# Written by Ilya Zverev, licensed WTFPL.

use strict;
use LWP;
use GD;

# use absolute paths!
my $work_path = 'queue';
my $result_path = 'www/result';
my $address = 'http://bigmap.osmz.ru';

# create mutex
exit if -e $work_path.'/working';
open TTT, ">$work_path/working";
close TTT;
open(my $log, ">>$work_path/log");
#print $log "\n";

# "finally" equivalent
eval {
	# find the task
	open QUEUE, "<$work_path/queue" or die 'cannot open queue file: '.$!;
	if( open DONE, "<$work_path/done" ) {
		# skipping all finished tasks
		while(my $taskid = <DONE>) {
			chomp $taskid;
			while(<QUEUE>) {
				last if /^$taskid,/;
			}
		}
		close DONE;
	}
	my $task = <QUEUE>;
	close QUEUE;

	if( $task && $task =~ /^([a-z]+\d+),/ ) {
		# found task, great! Save task id and read data
		my $taskid = $1;
		open TASK, "<$work_path/tasks/$taskid" or die "Cannot open task file $taskid: $!";
		my @contents;
		while(<TASK>) {
			chomp;
			push @contents, $_;
		}
		close TASK;

		my ($status) = split /,/, $contents[1];
		if( $status >= 2 ) {
			write_task($taskid, \@contents, $status);
			die "Task $taskid with status $status was not marked as done";
		}

		wlog("Found task $taskid, status $status");
		write_task($taskid, \@contents, 1);
		clear_tiles() if $status >= 0;
		my $ua = LWP::UserAgent->new();
		$ua->env_proxy;
		$ua->agent('BigMap/2.0 ('.$address.')');
		$ua->timeout(120);
		if( download_tiles($ua, $contents[2], $contents[3]) ) {
			if( create_image($taskid, $contents[2], $contents[4]) ) {
				$status = 2;
			} else {
				$status = 3;
			}
			clear_tiles();
		} else {
			$status = $status < 0 ? $status-1 : -1;
			$status = 3 if $status <= -5;
		}
		write_task($taskid, \@contents, $status);
		wlog("Task done with status $status");
	}
};
wlog('Error: '.$@) if $@;
close $log;

# remove mutex
unlink "$work_path/working";

sub wlog {
	my $text = shift;
	my @t = localtime;
	printf $log "[%02d.%02d.%04d %02d:%02d:%02d] %s\n", $t[3], $t[4]+1, $t[5]+1900, $t[2], $t[1], $t[0], $text;
}

sub write_task {
	my($taskid, $contents, $status) = @_;
	open TASK, ">$work_path/tasks/$taskid" or die "Cannot open task file: $!";
	print TASK $contents->[0]."\n";
	print TASK $status.','.time()."\n";
	print TASK $contents->[$_]."\n" for 2..$#{$contents};
	close TASK;
	if( $status >= 2 && open DONE, ">>$work_path/done") {
		print DONE "$taskid\n";
		close DONE;
	}
}

sub clear_tiles {
	return if !-d "$work_path/tiles";
	opendir(my $dh, "$work_path/tiles") or die "Cannot open tile directory: $!";
	my @tiles = grep {/\.png/} readdir $dh;
	closedir $dh;
	wlog("Removing cached tiles") if scalar @tiles;
	unlink "$work_path/tiles/$_" foreach @tiles;
}

sub download_tiles {
	my ($ua, $task, $urls) = @_;
	my ($zoom, $xmin, $ymin, $xmax, $ymax) = split /,/, $task;
	my @layers = split /\|/, $urls;
	my $count = 0;
	my $good = 1;
	wlog('Downloading tiles...');
	my %stat;
	my $stat_ar;
	for( my $x = $xmin; $x <= $xmax; $x++ ) {
		for( my $y = $ymin; $y <= $ymax; $y++ ) {
			my $img = GD::Image->new(256, 256, 1);
			for( my $l = 0; $l <= $#layers; $l++ ) {
				my $url = $layers[$l];
				$url =~ s/!z/$zoom/g;
				$url =~ s/!x/$x/g;
				$url =~ s/!y/$y/g;
				$url =~ s/{([a-z0-9]+)}/substr($1,int(rand(length($1))),1)/e;

				if( exists $stat{$layers[$l]} ) { $stat_ar = $stat{$layers[$l]}; } else {
					$stat_ar = [0, 0];
					$stat{$layers[$l]} = $stat_ar;
				}
				my $resp = $ua->get($url);
				#wlog($url.' - '.$resp->status_line);
				if( !$resp->is_success ) {
					$img = 0;
					$good = 0;
					$stat_ar->[1]++;
					last;
				}
				my $tile = GD::Image->new($resp->content);
				next if $tile->width == 1;
				$stat_ar->[0]++;
				if( $url =~ /seamark/ ) {
					my $black = $tile->colorClosest(0,0,0);
					$tile->transparent($black);
				}
				$img->copy($tile,0,0,0,0,256,256);
				if( ++$count == 5 ) { sleep 2; $count = 0; }
			}
			if( $img ) {
				mkdir "$work_path/tiles" if !-d "$work_path/tiles";
				open PIC, sprintf(">$work_path/tiles/%d_%06d_%06d.png", $zoom, $x, $y) or return 0;
				binmode PIC;
				print PIC $img->gd2();
				close PIC;
			}
		}
	}
	wlog($_.': '.$stat{$_}->[0].' good, '.$stat{$_}->[1].' failed') foreach keys %stat;
	return $good;
}

sub create_image {
	my ($taskid, $task, $attribution) = @_;
	my ($zoom, $xmin, $ymin, $xmax, $ymax) = split /,/, $task;
	my @t = gmtime();
	my $filename = sprintf('%s/%s-%02d%02d%02d-%02d%02d.png', $result_path, $taskid, $t[5]%100, $t[4]+1, $t[3], $t[2], $t[1]);
	wlog("Creating image $filename");
	my $xsize = $xmax - $xmin + 1;
	my $ysize = $ymax - $ymin + 1;
	my $img = GD::Image->new($xsize*256, $ysize*256, 1);
	my $white = $img->colorAllocate(248,248,248);
	$img->filledRectangle(0,0,$xsize*256,$ysize*256,$white);
	for( my $x = $xmin; $x <= $xmax; $x++ ) {
		for( my $y = $ymin; $y <= $ymax; $y++ ) {
			my $fn = sprintf("$work_path/tiles/%d_%06d_%06d.png", $zoom, $x, $y);
			my $tile = GD::Image->new($fn);
			return 0 if !$tile;
			$img->copy($tile,($x-$xmin)*256,($y-$ymin)*256,0,0,256,256);
		}
	}
	my $black = $img->colorClosest(0,0,0);
	$img->string(gdSmallFont, 10, $ysize*256 - 20, $attribution, $black);
	open PIC, '>'.$filename;
	binmode PIC;
	print PIC $img->png();
	close PIC;
	return 1;
}
