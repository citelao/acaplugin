# ACAC Features

This plugin sets up all content for the ACAC site.

## Features

1. Adds new content types:
	- **Auditionee**
	- **Group**
	- **Song**

## Install

This plugin is dependent on [CMB2](https://github.com/WebDevStudios/CMB2).
Luckily for all of us, they have made it play *very* nicely with composer.

The makefile in this repository will automatically download `composer.phar`
for you (locally, of course) and install our dependencies.

1. `make` in this directory.
2. Activate this plugin.
3. Install [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/) to make sure emails go through.

## TODO

- Registration
	- Make registration not kill all your data if there's an error
- Songboard
	- Fix many-to-many field with multiple fields
	- Repeating fields & required
- Auditions
	- Signup response body needs to be modifiable
	- Document the process for next webdev
	- Document required plugins for running the show
	- Proper filtering for all fields
	- Unify dropdown/selection interface for fields in Groups.
- BSTypes
	- Refactor to natively support relational types
	- Refactor to easily make interface changes

## Useful links
http://192.168.33.10/
https://github.com/WebDevStudios/CMB2/wiki/Field-Parameters
https://developer.wordpress.org/plugins/users/
https://codex.wordpress.org/Function_Reference/register_post_type#Arguments

https://wordpress.stackexchange.com/questions/128622/creating-a-relationship-between-two-post-types
https://github.com/scribu/wp-posts-to-posts
https://wordpress.org/plugins/posts-to-posts/
https://wordpress.org/plugins/cpt-onomies/

https://www.smashingmagazine.com/2013/12/modifying-admin-post-lists-in-wordpress/
https://en.bainternet.info/custom-post-types-columns/
https://github.com/justintadlock/members
http://pods.io/2013/12/13/pods-project-status-update/
https://wordpress.org/plugins/simple-history/
https://codex.wordpress.org/Database_Description
https://codex.wordpress.org/Creating_Tables_with_Plugins