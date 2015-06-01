# Change log

## v2.0.4
- Fixed error message when unauthenticated users try to upload something
- Fixed a bug where files that exist in MongoDB, but not in the filesystem, could not be deleted

## v2.0.3
- It is now forbidden to grant privileges to a home folder (members of /home/) without specifying a specific group or user. In other words, "DAV: all", "DAV: authenticated" and "DAV: unauthenticated" are no longer allowed to have an ACE granting them privileges to these folders.

## v2.0.2
- Now really fixed the bugs with URL encoded paths and file names; files and directories with all sorts of crazy characters should now work fine again

## v2.0.1
- Reverted the lock system back to the mySQL implementation
- Tried to solve some bugs with URL encoding

## v2.0
- js-webdav-lib is now no longer linked as a submodule, but instead is copied directly into the repository
- E-mail is now being send using the Zend\Mail classes. This means it can be sent over SMTP.

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
