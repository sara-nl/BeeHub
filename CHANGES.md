# Change log

## Next version (still to be numbered)
- Configuration changed; no longer a wheel_path, but an admin_group is required. All users in this group will be regarded as administrator
- If the client does not set the Content-Type header on a PUT request, the content type is guessed. This guess is also based on the file extension now.
- Added configuration option to config_example.ini to enable running server configuration
- Changed config_example.ini, it now points to the simpleSAMLphp base dir instead of the include path

## v1.3
