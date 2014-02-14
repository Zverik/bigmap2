# BigMap 2

This is a successor to [BigMap](http://wiki.openstreetmap.org/wiki/Bigmap) script: a tool to
stitch map tiles and produce a PNG image. Among improvements are:

* Better landing page with Leaflet and URL parsing.
* More than fifteen popular tile layers to choose from.
* KML, WLD and OziExplorer MAP meta files generation.
* Map downloading script can be produced in Python language.
* Fixed user agent and small pauses while downloading.
* Attribution on generated images.
* Server-side stitching with a queue.

## Installation

The following directory structure is recommended:

| Path | Mode | Description
|---|---|---
| `./` | `0755` | Place scripts from `scripts` directory here, with `0755` mode on them.
| `./queue/` | `0777` | Working directory that should be outside wwwroot.
| `./queue/tasks/` | `0777` | Task files will be put here by PHP scripts.
| `./queue/queue` | `0666` | A queue file that will be modified by PHP scripts.
| `./www/` | `0755` | WWWRoot. Place PHP scripts here and point HTTP server to it.
| `./www/result/` | `0777` | A directory for generated image files.

Then modify paths to be absolute in `bigmap_download.pl` and `purge_images.pl` scripts, so they can be
called by cron. Also change server address in `bigmap_download.pl` and size limit in `purge_images.pl`.
And add those two lines in `crontab -e` editor (your intervals may vary):

    */2 * * * * /var/www/.../bigmap_download.pl
    2 */6 * * * /var/www/.../purge_images.pl

Of all PHP scripts only `queue.php` needs configuring: you should change e-mail in there, and may want to alter limits.
Tile limit is specified in `$max_tiles` variable in `bigmap.php`.

After changing `tiles.txt` you would want to update Leaflet layers in `index.html`. To do this, just check paths
in `scripts/tiles2html.pl` and run it.

## License

All scripts were written by Ilya Zverev, partly based on public domain code by Frederik Ramm.
Published under WTFPL license.
