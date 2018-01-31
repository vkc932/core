Feature: provisioning
	Background:
		Given using api version "1"

	Scenario: Getting a not existing user
		Given as user "admin"
		When the user sends HTTP method "GET" to API endpoint "/cloud/users/test"
		Then the OCS status code should be "998"
		And the HTTP status code should be "200"

	Scenario: Listing all users
		Given as user "admin"
		When the user sends HTTP method "GET" to API endpoint "/cloud/users"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"

	Scenario: Create a user
		Given as user "admin"
		And user "brand-new-user" has been deleted
		When the user sends HTTP method "POST" to API endpoint "/cloud/users" with body
			| userid | brand-new-user |
			| password | 123456 |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And user "brand-new-user" should exist

	Scenario: Create an existing user
		Given as user "admin"
		And user "brand-new-user" has been created
		When the user sends HTTP method "POST" to API endpoint "/cloud/users" with body
			| userid | brand-new-user |
			| password | 123456 |
		Then the OCS status code should be "102"
		And the HTTP status code should be "200"

	Scenario: Get an existing user
		Given as user "admin"
		And user "brand-new-user" has been created
		When the user sends HTTP method "GET" to API endpoint "/cloud/users/brand-new-user"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"

	Scenario: Getting all users
		Given as user "admin"
		And user "brand-new-user" has been created
		And user "admin" has been created
		When the user sends HTTP method "GET" to API endpoint "/cloud/users"
		Then the users returned by the API should be
			| brand-new-user |
			| admin |

	Scenario: Edit a user
		Given as user "admin"
		And user "brand-new-user" has been created
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/brand-new-user" with body
			| key | quota |
			| value | 12MB |
			| key | email |
			| value | brand-new-user@gmail.com |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And user "brand-new-user" should exist

	Scenario: Create a group
		Given as user "admin"
		And group "new-group" has been deleted
		When the user sends HTTP method "POST" to API endpoint "/cloud/groups" with body
			| groupid | new-group |
			| password | 123456 |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And group "new-group" should exist

	Scenario: Create a group with special characters
		Given as user "admin"
		And group "España" has been deleted
		When the user sends HTTP method "POST" to API endpoint "/cloud/groups" with body
			| groupid | España |
			| password | 123456 |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And group "España" should exist

	Scenario: Create a group named "0"
		Given as user "admin"
		And group "0" has been deleted
		When the user sends HTTP method "POST" to API endpoint "/cloud/groups" with body
			| groupid | 0 |
			| password | 123456 |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And group "0" should exist

	Scenario: adding user to a group without sending the group
		Given as user "admin"
		And user "brand-new-user" has been created
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid |  |
		Then the OCS status code should be "101"
		And the HTTP status code should be "200"

	Scenario: adding user to a group which doesn't exist
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "not-group" has been deleted
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid | not-group |
		Then the OCS status code should be "102"
		And the HTTP status code should be "200"

	Scenario: adding user to a group without privileges
		Given as user "brand-new-user"
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid | new-group |
		Then the OCS status code should be "997"
		And the HTTP status code should be "401"

	Scenario Outline: adding a user to a group
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "<group_id>" has been created
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid | <group_id> |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		Examples:
			| group_id  |
			| new-group |
			| 0         |

	Scenario: getting groups of an user
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "new-group" has been created
		And group "0" has been created
		And user "brand-new-user" has been added to group "new-group"
		And user "brand-new-user" has been added to group "0"
		When the user sends HTTP method "GET" to API endpoint "/cloud/users/brand-new-user/groups"
		Then the groups returned by the API should be
			| new-group |
			| 0 |
		And the OCS status code should be "100"

	Scenario: adding a user which doesn't exist to a group
		Given as user "admin"
		And user "not-user" has been deleted
		And group "new-group" has been created
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/not-user/groups" with body
			| groupid | new-group |
		Then the OCS status code should be "103"
		And the HTTP status code should be "200"

	Scenario: getting a group
		Given as user "admin"
		And group "new-group" has been created
		When the user sends HTTP method "GET" to API endpoint "/cloud/groups/new-group"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"

	Scenario: Getting all groups
		Given as user "admin"
		And group "0" has been created
		And group "new-group" has been created
		And group "admin" has been created
		And group "España" has been created
		When the user sends HTTP method "GET" to API endpoint "/cloud/groups"
		Then the groups returned by the API should be
			| España |
			| admin |
			| new-group |
			| 0 |

	Scenario: create a subadmin
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "new-group" has been created
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/brand-new-user/subadmins" with body
			| groupid | new-group |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"

	Scenario: get users using a subadmin
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "new-group" has been created
		And user "brand-new-user" has been added to group "new-group"
		And user "brand-new-user" has been made a subadmin of group "new-group"
		And as user "brand-new-user"
		When the user sends HTTP method "GET" to API endpoint "/cloud/users"
		Then the users returned by the API should be
			| brand-new-user |
		And the OCS status code should be "100"
		And the HTTP status code should be "200"

	Scenario: removing a user from a group which doesn't exist
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "not-group" has been deleted
		When the user sends HTTP method "DELETE" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid | not-group |
		Then the OCS status code should be "102"

	Scenario Outline: removing a user from a group
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "<group_id>" has been created
		And user "brand-new-user" has been added to group "<group_id>"
		When the user sends HTTP method "DELETE" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid | <group_id> |
		Then the OCS status code should be "100"
		And user "brand-new-user" should not belong to group "<group_id>"
		Examples:
			| group_id  |
			| new-group |
			| 0         |

	Scenario: create a subadmin using a user which does not exist
		Given as user "admin"
		And user "not-user" has been deleted
		And group "new-group" has been created
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/not-user/subadmins" with body
			| groupid | new-group |
		Then the OCS status code should be "101"
		And the HTTP status code should be "200"

	Scenario: create a subadmin using a group which does not exist
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "not-group" has been deleted
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/brand-new-user/subadmins" with body
			| groupid | not-group |
		Then the OCS status code should be "102"
		And the HTTP status code should be "200"

	Scenario: Getting subadmin groups of a user
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "new-group" has been created
		And user "brand-new-user" has been made a subadmin of group "new-group"
		When the user sends HTTP method "GET" to API endpoint "/cloud/users/brand-new-user/subadmins"
		Then the subadmin groups returned by the API should be
			| new-group |
		And the OCS status code should be "100"
		And the HTTP status code should be "200"

	Scenario: Getting subadmin groups of a user which do not exist
		Given as user "admin"
		And user "not-user" has been deleted
		And group "new-group" has been created
		When the user sends HTTP method "GET" to API endpoint "/cloud/users/not-user/subadmins"
		Then the OCS status code should be "101"
		And the HTTP status code should be "200"

	Scenario: Getting subadmin users of a group
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "new-group" has been created
		And user "brand-new-user" has been made a subadmin of group "new-group"
		When the user sends HTTP method "GET" to API endpoint "/cloud/groups/new-group/subadmins"
		Then the subadmin users returned by the API should be
			| brand-new-user |
		And the OCS status code should be "100"
		And the HTTP status code should be "200"

	Scenario: Getting subadmin users of a group which doesn't exist
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "not-group" has been deleted
		When the user sends HTTP method "GET" to API endpoint "/cloud/groups/not-group/subadmins"
		Then the OCS status code should be "101"
		And the HTTP status code should be "200"

	Scenario: Removing subadmin from a group
		Given as user "admin"
		And user "brand-new-user" has been created
		And group "new-group" has been created
		And user "brand-new-user" has been made a subadmin of group "new-group"
		When the user sends HTTP method "DELETE" to API endpoint "/cloud/users/brand-new-user/subadmins" with body
			| groupid | new-group |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"

	Scenario: Delete a user
		Given as user "admin"
		And user "brand-new-user" has been created
		When the user sends HTTP method "DELETE" to API endpoint "/cloud/users/brand-new-user"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And user "brand-new-user" should not exist

	Scenario: Delete a group
		Given as user "admin"
		And group "new-group" has been created
		When the user sends HTTP method "DELETE" to API endpoint "/cloud/groups/new-group"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And group "new-group" should not exist

	Scenario: Delete a group with special characters
	    Given as user "admin"
		And group "España" has been created
		When the user sends HTTP method "DELETE" to API endpoint "/cloud/groups/España"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And group "España" should not exist

	@no_encryption
	Scenario: get enabled apps
		Given as user "admin"
		When the user sends HTTP method "GET" to API endpoint "/cloud/apps?filter=enabled"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And the apps returned by the API should include
			| comments |
			| dav |
			| federatedfilesharing |
			| federation |
			| files |
			| files_sharing |
			| files_trashbin |
			| files_versions |
			| provisioning_api |
			| systemtags |
			| updatenotification |
			| files_external |

	Scenario: get app info
		Given as user "admin"
		When the user sends HTTP method "GET" to API endpoint "/cloud/apps/files"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And the XML "data" "id" value should be "files"
		And the XML "data" "name" value should be "Files"
		And the XML "data" "types" "element" value should be "filesystem"
		And the XML "data" "dependencies" "owncloud" "min-version" attribute value should be a valid version string
		And the XML "data" "dependencies" "owncloud" "max-version" attribute value should be a valid version string

#	Scenario: enable an app
#		Given as user "admin"
#		And app "comments" is disabled
#		When the user sends HTTP method "POST" to API endpoint "/cloud/apps/comments"
#		Then the OCS status code should be "100"
#		And the HTTP status code should be "200"
#		And app "comments" is enabled
#
#	Scenario: disable an app
#		Given as user "admin"
#		And app "comments" is enabled
#		When the user sends HTTP method "DELETE" to API endpoint "/cloud/apps/comments"
#		Then the OCS status code should be "100"
#		And the HTTP status code should be "200"
#		And app "comments" is disabled

	Scenario: disable an user
		Given as user "admin"
		And user "user1" has been created
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/user1/disable"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And user "user1" should be disabled

	Scenario: enable an user
		Given as user "admin"
		And user "user1" has been created
		And user "user1" has been disabled
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/user1/enable"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And user "user1" should be enabled

	Scenario: Subadmin should be able to enable or disable an user in their group
		Given as user "admin"
		And user "subadmin" has been created
		And user "user1" has been created
		And group "new-group" has been created
		And user "subadmin" has been added to group "new-group"
		And user "user1" has been added to group "new-group"
		And user "subadmin" has been made a subadmin of group "new-group"
		And as user "subadmin"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/user1/disable"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And as user "admin"
		And user "user1" should be disabled

	Scenario: Subadmin should not be able to enable or disable an user not in their group
		Given as user "admin"
		And user "subadmin" has been created
		And user "user1" has been created
		And group "new-group" has been created
		And group "another-group" has been created
		And user "subadmin" has been added to group "new-group"
		And user "user1" has been added to group "another-group"
		And user "subadmin" has been made a subadmin of group "new-group"
		And as user "subadmin"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/user1/disable"
		Then the OCS status code should be "997"
		And the HTTP status code should be "401"
		And as user "admin"
		And user "user1" should be enabled

	Scenario: Subadmins should not be able to disable users that have admin permissions in their group
		Given as user "admin"
		And user "another-admin" has been created
		And user "subadmin" has been created
		And group "new-group" has been created
		And user "another-admin" has been added to group "admin"
		And user "subadmin" has been added to group "new-group"
		And user "another-admin" has been added to group "new-group"
		And user "subadmin" has been made a subadmin of group "new-group"
		And as user "subadmin"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/another-admin/disable"
		Then the OCS status code should be "997"
		And the HTTP status code should be "401"
		And as user "admin"
		And user "another-admin" should be enabled

	Scenario: Admin can disable another admin user
		Given as user "admin"
		And user "another-admin" has been created
		And user "another-admin" has been added to group "admin"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/another-admin/disable"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And user "another-admin" should be disabled

	Scenario: Admin can enable another admin user
		Given as user "admin"
		And user "another-admin" has been created
		And user "another-admin" has been added to group "admin"
		And user "another-admin" has been disabled
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/another-admin/enable"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And user "another-admin" should be enabled

	Scenario: Admin can disable subadmins in the same group
		Given as user "admin"
		And user "subadmin" has been created
		And group "new-group" has been created
		And user "subadmin" has been added to group "new-group"
		And user "admin" has been added to group "new-group"
		And user "subadmin" has been made a subadmin of group "new-group"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/subadmin/disable"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And user "subadmin" should be disabled

	Scenario: Admin can enable subadmins in the same group
		Given as user "admin"
		And user "subadmin" has been created
		And group "new-group" has been created
		And user "subadmin" has been added to group "new-group"
		And user "admin" has been added to group "new-group"
		And user "subadmin" has been made a subadmin of group "new-group"
		And user "another-admin" has been disabled
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/subadmin/disable"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And user "subadmin" should be disabled

	Scenario: Admin user cannot disable himself
		Given as user "admin"
		And user "another-admin" has been created
		And user "another-admin" has been added to group "admin"
		And as user "another-admin"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/another-admin/disable"
		Then the OCS status code should be "101"
		And the HTTP status code should be "200"
		And as user "admin"
		And user "another-admin" should be enabled

	Scenario:Admin user cannot enable himself
		Given as user "admin"
		And user "another-admin" has been created
		And user "another-admin" has been added to group "admin"
		And user "another-admin" has been disabled
		And as user "another-admin"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/another-admin/enable"
		And as user "admin"
		Then user "another-admin" should be disabled

	Scenario: disable an user with a regular user
		Given as user "admin"
		And user "user1" has been created
		And user "user2" has been created
		And as user "user1"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/user2/disable"
		Then the OCS status code should be "997"
		And the HTTP status code should be "401"
		And as user "admin"
		And user "user2" should be enabled

	Scenario: enable an user with a regular user
		Given as user "admin"
		And user "user1" has been created
		And user "user2" has been created
		And user "user2" has been disabled
		And as user "user1"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/user2/enable"
		Then the OCS status code should be "997"
		And the HTTP status code should be "401"
		And as user "admin"
		And user "user2" should be disabled

	Scenario: Subadmin should not be able to disable himself
		Given as user "admin"
		And user "subadmin" has been created
		And group "new-group" has been created
		And user "subadmin" has been added to group "new-group"
		And user "subadmin" has been made a subadmin of group "new-group"
		And as user "subadmin"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/subadmin/disable"
		Then the OCS status code should be "101"
		And the HTTP status code should be "200"
		And as user "admin"
		And user "subadmin" should be enabled

	Scenario: Subadmin should not be able to enable himself
		Given as user "admin"
		And user "subadmin" has been created
		And group "new-group" has been created
		And user "subadmin" has been added to group "new-group"
		And user "subadmin" has been made a subadmin of group "new-group"
		And user "subadmin" has been disabled
		And as user "subadmin"
		When the user sends HTTP method "PUT" to API endpoint "/cloud/users/subadmin/enabled"
		Then as user "admin"
		And user "subadmin" should be disabled

	Scenario: a subadmin can add users to groups the subadmin is responsible for
		Given as user "admin"
		And user "subadmin" has been created
		And user "brand-new-user" has been created
		And group "new-group" has been created
		And user "subadmin" has been made a subadmin of group "new-group"
		And as user "subadmin"
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid | new-group |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And as user "admin"
		And user "brand-new-user" should belong to group "new-group"

	Scenario: a subadmin cannot add users to groups the subadmin is not responsible for
		Given as user "admin"
		And user "other-subadmin" has been created
		And user "brand-new-user" has been created
		And group "new-group" has been created
		And group "other-group" has been created
		And user "other-subadmin" has been made a subadmin of group "other-group"
		And as user "other-subadmin"
		When the user sends HTTP method "POST" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid | new-group |
		Then the OCS status code should be "104"
		And the HTTP status code should be "200"
		And as user "admin"
		And user "brand-new-user" should not belong to group "new-group"

	Scenario: a subadmin can remove users from groups the subadmin is responsible for
		Given as user "admin"
		And user "subadmin" has been created
		And user "brand-new-user" has been created
		And group "new-group" has been created
		And user "brand-new-user" has been added to group "new-group"
		And user "subadmin" has been made a subadmin of group "new-group"
		And as user "subadmin"
		When the user sends HTTP method "DELETE" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid | new-group |
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And as user "admin"
		And user "brand-new-user" should not belong to group "new-group"

	Scenario: a subadmin cannot remove users from groups the subadmin is not responsible for
		Given as user "admin"
		And user "other-subadmin" has been created
		And user "brand-new-user" has been created
		And group "new-group" has been created
		And group "other-group" has been created
		And user "brand-new-user" has been added to group "new-group"
		And user "other-subadmin" has been made a subadmin of group "other-group"
		And as user "other-subadmin"
		When the user sends HTTP method "DELETE" to API endpoint "/cloud/users/brand-new-user/groups" with body
			| groupid | new-group |
		Then the OCS status code should be "104"
		And the HTTP status code should be "200"
		And as user "admin"
		And user "brand-new-user" should belong to group "new-group"

	Scenario: Making a web request with an enabled user
	    Given as user "admin"
		And user "user0" has been created
		And as user "user0"
		When sending "GET" with exact url to "/index.php/apps/files"
		Then the HTTP status code should be "200"

	Scenario: Making a web request with a disabled user
	    Given as user "admin"
		And user "user0" has been created
		And user "user0" has been disabled
		And as user "user0"
		When sending "GET" with exact url to "/index.php/apps/files"
		Then the HTTP status code should be "403"

	Scenario: Edit a user email twice
		Given as user "admin"
		And user "brand-new-user" has been created
		And the user has sent HTTP method "PUT" to API endpoint "/cloud/users/brand-new-user" with body
			| key | email |
			| value | brand-new-user@gmail.com |
		And the OCS status code should be "100"
		And the HTTP status code should be "200"
		And the user has sent HTTP method "PUT" to API endpoint "/cloud/users/brand-new-user" with body
			| key | email |
			| value | brand-new-user@example.com |
		And the OCS status code should be "100"
		And the HTTP status code should be "200"
		When the user sends HTTP method "GET" to API endpoint "/cloud/users/brand-new-user"
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"
		And the user attributes returned by the API should include
			| email | brand-new-user@example.com |
