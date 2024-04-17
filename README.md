CSS/JS Versioning module for OpenMage/Magento1
=============================

<table><tr><td align=center>
<strong>If you find my work valuable, please consider sponsoring</strong><br />
<a href="https://github.com/sponsors/fballiano" target=_blank title="Sponsor me on GitHub"><img src="https://img.shields.io/badge/sponsor-30363D?style=for-the-badge&logo=GitHub-Sponsors&logoColor=#white" alt="Sponsor me on GitHub" /></a>
<a href="https://www.buymeacoffee.com/fballiano" target=_blank title="Buy me a coffee"><img src="https://img.shields.io/badge/Buy_Me_A_Coffee-FFDD00?style=for-the-badge&logo=buy-me-a-coffee&logoColor=black" alt="Buy me a coffee" /></a>
<a href="https://www.paypal.com/paypalme/fabrizioballiano" target=_blank title="Donate via PayPal"><img src="https://img.shields.io/badge/PayPal-00457C?style=for-the-badge&logo=paypal&logoColor=white" alt="Donate via PayPal" /></a>
</td></tr></table>

Quick description
---------

Adds `?v=` to all your CSS/JS and the value of `v` is the last git commit hash. 

Rationale
---------

To make search engines happy you need to use very long browser's cache lifetime
(with or without a CDN), but if you tell browsers to cache a css or a js for 1 year 
and then you need to modify it? The browsers won't receive the new version and your website
will break.

My solution is to add a `v=xxx` parameter to all CSS/JS URLs, this way the cache will be forced to
update and the new version of the files will be stored in cache until the next update.

In order to do that reliably and consistently I've created this module that intercepts the
http_response_send_before event, uses as little preg_regex as possible in order to be as
performant as possible to add the `v=xxx` parameter.

This approach should work also with full page cache modules.

Features
---------

**How to gen the version number to use in the `v=xxx` parameter?**

This module supports 2 approaches:
1. using the last `Git commit hash` (only the first 6 characters) as the version number
2. if that fails then a timestamp is used

The git method is preferred because the generated version number won't change unless a new change is pushed
on the repository. However, in order to work, OpenMage base dir must also be the Git project root directory
and the .git folder has to be present in all servers you need this functionality to work.

**Resuming:**
- Automatically reads the last Git commit hash, extracts the first 6 characters
- Saves this version number in OpenMage config cache for 1hour
  (if you flush config cache you also flush this value) in order to have a little
  impact on the filesystem as possible
- If the git approach doesn't work, the version number because the current timestamp
  (and gets cached for 24 hours)
- Parsed the output HTML and intercepts all `<script` and `<link` tags, extracts the URLs
  and adds the `v=xxx` parameter (using `?v` or `&v` accordingly)
- It only catches `<script` tags with `src` parameter and `<link` tags with `href` parameter
  but only if the link is of type `icon` or `stylesheet` (we must avoid modifying a canonical
  by mistake)

**Limitations:**
- You have to have the .git folder in the production server(s) too
  (be sure to deny HTTP access to .git/* in your .htaccess)

Warning
---------

This module is provided "as is" and I'll not be responsible for any problem or damage.

Installation
------------

Install via composer (`composer require fballiano/openmage-cssjs-versioning`),
modman (`modman clone https://github.com/fballiano/openmage-cssjs-versioning`)
or any other way you like

Support
-------
If you have any issues with this extension, open an issue on GitHub.

Contribution
------------
Any contributions are highly appreciated. The best way to contribute code is to open a
[pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Fabrizio Balliano  
[http://fabrizioballiano.com](http://fabrizioballiano.com)  
[@fballiano](https://twitter.com/fballiano)

Licence
-------
[OSL - Open Software Licence 3.0](https://opensource.org/license/osl-3)

Copyright
---------
(c) Fabrizio Balliano