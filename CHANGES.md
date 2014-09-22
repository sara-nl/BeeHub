# Change log

## v1.6.1
POST requests now require a POST auth code which can be obtained by performing a
GET request to /system/?POST_auth_code. The code itself must be included in the
POST request in the field POST_auth_code. It changes after a certain amount of
failed attempts and after every succesful attempt.

## v1.5 & v1.6
Apparently forgot to maintain this file for these versions

## v1.4
- Configuration changed; no longer a wheel_path, but an admin_group is required. All users in this group will be regarded as administrator
- If the client does not set the Content-Type header on a PUT request, the content type is guessed. This guess is also based on the file extension now.
- Added configuration option to config_example.ini to enable running server configuration
- Changed config_example.ini, it now points to the simpleSAMLphp base dir instead of the include path

## v1.3
