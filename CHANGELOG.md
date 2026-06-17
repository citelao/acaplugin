# 1.1.0
- Added: local Docker/Podman dev environment in `contrib/docker-host/`
- Added: `make dist` target to build a deployable zip
- Fixed: PHP 8 compatibility — `array_key_exists` crash on fresh install with no config
- Fixed: updated Composer dependencies to work with modern Composer

# 1.0.1
- Added: old example emails are in `docs/emails/`
- Changed: documentation now explains the built-in export feature
- Changed: documentation is clearer in-app for pref cards
- Fixed: properly check for `?post_type=` query string to avoid errors on Posts page

# 1.0
- Added: changelog
- Added: can now customize the registration confirmation message
- Fixed: if there are no callback dates, we show a nice error instead of a fatal one
- Fixed: properly check for search keys (`?s=`) in custom type's search

# 0.0
- Initial release