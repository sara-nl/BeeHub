# MongoDB database structure

## Collection: users
A complete document in the 'users' collection could look like this:
```
{
  "name": "jane",
  "displayname": "Jane Doe",
  "email": "jane.doe@mailservice.com",
  "password": "$6$rounds=5000$cvGKahLo6.q/TSTe$20dAPKtCcskhKC7SJ0ObfQodGu8dKhj5Eb4ipm09NK7RRJzTONJTsZgxASn3I1PtV6Yrwi186Xw9mn2mOxqKc.",
  "surfconext_id": "qwertyuiop",
  "surfconext_description": "Account at the top row of letters on my keyboard",
  "x509": "CN=Jane Doe",
  "unverified_email": "j.doe@mailservice.com",
  "verification_code": "somesecretcode",
  "verification_expiration": "2147480047",
  "password_reset_code": "someothersecretcode",
  "password_reset_expiration": "2147480047",
  "default_sponsor": "sponsor_a",
  "groups": [ "foo", "bar" ],
  "sponsors": [ "sponsor_a", "sponsor_b" ]
}
```
This describes user Jane Doe. She has a BeeHub password (encrypted here), a SURFconext account attached and has an X509 certificate with DN 'CN=Jane Doe'.
She is in the process of changing her e-mail address from jane.doe@mailservice.com to j.doe@mailservice.com, but didn't verify this change yet. In order to do this, she needs to fill out the verification code 'somesecretcode' on her profile page before the current unix timestamp is greater than 2147480047.
She is also in the process of resetting her password. For that she received an e-mail with the reset code 'someothersecretcode' which she can use until the current unix timestamp is greater than 2147480047.
Her default sponsor is 'sponsor_a', but she is also sponsored by 'sponsor_b'. Furthermore, she is a member of groups 'foo' and 'bar'.

## Collection: groups
A complete document in the 'groups' collection could look like this:
```
{
  "name": "foo",
  "displayname": "Foo",
  "description": "Group of all Foo members",
  "admins": [ "jane" ],
  "members": [ "john" ],
  "admin_accepted_memberships": [ "jany" ],
  "user_accepted_memberships": [ "johny" ]
}
```
This describes the group Foo. Jane is currently the only administrator of this group, but there is also a (regular) member John. Jany is invited to become a member, but she did not accept this invitation yet. Johny has requested to become a member, but this is not approved by a group administrator yet.

## Collection: sponsors
A complete document in the 'sponsors' collection could look like this:
```
{
  "name": "sponsor_a",
  "displayname": "Company A",
  "description": "Company A has paid for storage",
  "admins": [ "jane" ],
  "members": [ "john" ],
  "user_accepted_memberships": [ "johny" ]
}
```
This describes the sponsor 'sponsor_a'. Jane is currently the only administrator of this group, but there is also a (regular) member John. Johny requested to be sponsored by sponsor_a, but this has not yet been approved by a sponsor administrator. Note that users are not invited to sponsors; if a sponsor administrator adds a user, this is not an invite, but the user is immediately sponsored.

## Collection: beehub_system
This collections contains just one document:
```
{
  "name": "etag",
  "counter": 666
}
```
This is used as a counter to determine the next ETag. This value should never be changed manually! However, after a fresh install, before any files are uploaded, you can set the 'counter' field to 0.

## Collection: files
A complete document in the 'files' collection could look like this:
```
{
  "path": "foo/file.txt",
  "props" :
  {
    "DAV: owner": "john",
    "http://beehub%2Enl/ sponsor": "sponsor_a",
    "DAV: acl": "[[\"/system/groups/bar\",false,[\"DAV: read\"],false]]",
    "DAV: getcontenttype": "text/plain; charset=UTF-8",
    "DAV: getetag": "\"EA\"",
    "test_namespace test_property": "this is a random dead property"
  },
  "collection": true
}
```
This describes the properties for the file 'foo/file.txt'. This path should NOT have leading or trailing slashes.
The object/hash/map/array in the 'props' contains key-value pairs for webDAV properties. The key should contain exactly one space which separates the XML namespace from the XML node name. Also, these characters: . % $ should be replaced by the URL encoded counterparts: '%2E', '%25', '%24'
Most properties are passed through directly to the client as webDAV properties. 'DAV: owner' and 'DAV: sponsor' are exceptions, they get respectively the users and sponsors path prepended automatically. The 'DAV: acl' property is also an exception, it contains a string that is interpreted by the BeeHub code to form the ACL.

## Collection: locks
A complete document in the 'locks' collection could look like this:
```
{
  "path": "foo/file.txt",
  "shallowWriteLock" : { "lockerId" : "beehub-devel53f49ae091feb6.35129299", "time" : NumberLong(1408539360) },
  "shallowReadLock" : { "counter" : 1, "latest_lock" : NumberLong(1408539360) }
}
```
This describes the shallow locks for the file 'foo/file.txt'. This path should NOT have leading or trailing slashes.