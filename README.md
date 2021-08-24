# WPSSG

Experimental WordPress static site generator (SSG), with an "inside-out" approach.

Most WordPress static site generation projects work from the outside-in, requesting known WP URLs or crawling from the homepage, discovering new URLs on each page.

Making a request for every URL adds a lot of latency, requiring horizontal scaling to overcome.

Purpose-built SSGs don't require web servers, but programmatically generate the static HTML/XML/RSS content logically.

The WordPress CMS isn't designed at all to generate static files (unlike its early competitor, Movable Type!).

This project aims to deliver a drop-in, highly performant script to generate a static copy of your WordPress site.

[Originally conceived in this GitHub Issue](https://github.com/leonstafford/wp2static/issues/799)

### Design decisions

Aiming for a chunk of the 80% market of sites which can easily be converted to static, but not aiming for the masses.

 - CLI-first development (may work via web server, not testing that initially)
 - does care about code quality (`composer run-script test`)
 - doesn't care about Windows users (run in WSL or something UNIX-y)
 - doesn't care about Multisite installations (no support)
 - doesn't care about multilingual sites (actively strips i18n functionality)
 - doesn't care about w0rDpReSS capitalisation (dequeues useless filter)
 - doesn't care about emojis `¯\_(ツ)_/¯`
 - doesn't care about PHP 7 (we're here for a fast time, not a compatible time)
 - doesn't care about one very long file or OOP (this is WP!) 

### Usage

 - copy this repo's `wpssg.php` into your WordPress site's root directory
 - run it `php wpssg.php`
 - follow the usage instructions presented

### Development

 - clone repo
 - `composer install`
 - `composer run-script test`

### Documentation

I'm unlikely to have decent docs in early project days. Refer to the source code/unit tests/usage instructions from the script.
