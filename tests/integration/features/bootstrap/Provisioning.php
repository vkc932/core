<?php

use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;

require __DIR__ . '/../../../../lib/composer/autoload.php';

trait Provisioning {

	/** @var array */
	private $createdUsers = [];

	/** @var array */
	private $createdRemoteUsers = [];

	/** @var array */
	private $createdRemoteGroups = [];

	/** @var array */
	private $createdGroups = [];

	/**
	 * @When /^the administrator creates user "([^"]*)"$/
	 * @param string $user
	 */
	public function adminCreatesUser($user) {
		if ( !$this->userExists($user) ) {
			$previous_user = $this->currentUser;
			$this->currentUser = "admin";
			$this->creatingTheUser($user);
			$this->currentUser = $previous_user;
		}
		PHPUnit_Framework_Assert::assertTrue($this->userExists($user));
	}

	/**
	 * @Given /^user "([^"]*)" has been created$/
	 * @param string $user
	 */
	public function userHasBeenCreated($user) {
		$this->adminCreatesUser($user);
	}

	/**
	 * @Given /^user "([^"]*)" exists$/
	 * @param string $user
	 */
	public function assureUserExists($user) {
		$this->adminCreatesUser($user);
	}

	/**
	 * @Then /^user "([^"]*)" should exist$/
	 * @param string $user
	 */
	public function userShouldExist($user) {
		PHPUnit_Framework_Assert::assertTrue($this->userExists($user));
		$this->rememberTheUser($user);
	}

	/**
	 * @Then /^user "([^"]*)" already exists$/
	 * @param string $user
	 */
	public function userAlreadyExists($user) {
		$this->userShouldExist($user);
	}

	/**
	 * @Then /^user "([^"]*)" should not exist$/
	 * @param string $user
	 */
	public function userShouldNotExist($user) {
		PHPUnit_Framework_Assert::assertFalse($this->userExists($user));
	}

	/**
	 * @Then /^user "([^"]*)" does not already exist$/
	 * @param string $user
	 */
	public function userDoesNotAlreadyExist($user) {
		$this->userShouldNotExist($user);
	}

	/**
	 * @Then /^group "([^"]*)" should exist$/
	 * @param string $group
	 */
	public function groupShouldExist($group) {
		PHPUnit_Framework_Assert::assertTrue($this->groupExists($group));
		$this->rememberTheGroup($group);
	}

	/**
	 * @Then /^group "([^"]*)" already exists$/
	 * @param string $group
	 */
	public function groupAlreadyExists($group) {
		$this->groupShouldExist($group);
	}

	/**
	 * @Then /^group "([^"]*)" should not exist$/
	 * @param string $group
	 */
	public function groupShouldNotExist($group) {
		PHPUnit_Framework_Assert::assertFalse($this->groupExists($group));
	}

	/**
	 * @Then /^group "([^"]*)" does not already exist$/
	 * @param string $group
	 */
	public function groupDoesNotAlreadyExist($group) {
		$this->groupShouldNotExist($group);
	}

	/**
	 * @When /^the administrator deletes user "([^"]*)"$/
	 * @param string $user
	 */
	public function adminDeletesUser($user) {
		if ($this->userExists($user)) {
			$previous_user = $this->currentUser;
			$this->currentUser = "admin";
			$this->deleteTheUser($user);
			$this->currentUser = $previous_user;
		}
		PHPUnit_Framework_Assert::assertFalse($this->userExists($user));
	}

	/**
	 * @Given /^user "([^"]*)" has been deleted$/
	 * @param string $user
	 */
	public function userHasBeenDeleted($user) {
		$this->adminDeletesUser($user);
	}

	/**
	 * @Given /^user "([^"]*)" does not exist$/
	 * @param string $user
	 */
	public function assureUserDoesNotExist($user) {
		$this->adminDeletesUser($user);
	}

	public function rememberTheUser($user) {
		if ($this->currentServer === 'LOCAL') {
			$this->createdUsers[$user] = $user;
		} elseif ($this->currentServer === 'REMOTE') {
			$this->createdRemoteUsers[$user] = $user;
		}
	}

	public function creatingTheUser($user) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$options['body'] = [
							'userid' => $user,
							'password' => '123456'
							];

		$this->response = $client->send($client->createRequest("POST", $fullUrl, $options));
		$this->rememberTheUser($user);

		//Quick hack to login once with the current user
		$options2 = [
			'auth' => [$user, '123456'],
		];
		$url = $fullUrl.'/'.$user;
		$client->send($client->createRequest('GET', $url, $options2));
	}

	public function createUser($user) {
		$previous_user = $this->currentUser;
		$this->currentUser = "admin";
		$this->creatingTheUser($user);
		PHPUnit_Framework_Assert::assertTrue($this->userExists($user));
		$this->currentUser = $previous_user;
	}

	public function deleteUser($user) {
		$previous_user = $this->currentUser;
		$this->currentUser = "admin";
		$this->deleteTheUser($user);
		PHPUnit_Framework_Assert::assertFalse($this->userExists($user));
		$this->currentUser = $previous_user;
	}

	public function createGroup($group) {
		$previous_user = $this->currentUser;
		$this->currentUser = "admin";
		$this->creatingTheGroup($group);
		PHPUnit_Framework_Assert::assertTrue($this->groupExists($group));
		$this->currentUser = $previous_user;
	}

	public function deleteGroup($group) {
		$previous_user = $this->currentUser;
		$this->currentUser = "admin";
		$this->deleteTheGroup($group);
		PHPUnit_Framework_Assert::assertFalse($this->groupExists($group));
		$this->currentUser = $previous_user;
	}

	public function userExists($user) {
		$fullUrl = $this->baseUrl . "v2.php/cloud/users/$user";
		$client = new Client();
		$options = [];
		$options['auth'] = $this->adminUser;
		try {
			$this->response = $client->get($fullUrl, $options);
			return True;
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			$this->response = $e->getResponse();
			return False;
		}
	}

	/**
	 * @Then /^user "([^"]*)" should belong to group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function userShouldBelongToGroup($user, $group) {
		$fullUrl = $this->baseUrl . "v2.php/cloud/users/$user/groups";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->get($fullUrl, $options);
		$respondedArray = $this->getArrayOfGroupsResponded($this->response);
		sort($respondedArray);
		PHPUnit_Framework_Assert::assertContains($group, $respondedArray);
		PHPUnit_Framework_Assert::assertEquals(200, $this->response->getStatusCode());
	}

	/**
	 * @Then /^check that user "([^"]*)" belongs to group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function checkThatUserBelongsToGroup($user, $group) {
		$this->userShouldBelongToGroup($user, $group);
	}

	/**
	 * @Then /^user "([^"]*)" should not belong to group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function userShouldNotBelongToGroup($user, $group) {
		$fullUrl = $this->baseUrl . "v2.php/cloud/users/$user/groups";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->get($fullUrl, $options);
		$respondedArray = $this->getArrayOfGroupsResponded($this->response);
		sort($respondedArray);
		PHPUnit_Framework_Assert::assertNotContains($group, $respondedArray);
		PHPUnit_Framework_Assert::assertEquals(200, $this->response->getStatusCode());
	}

	/**
	 * @Then /^check that user "([^"]*)" does not belong to group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function checkThatUserDoesNotBelongToGroup($user, $group) {
		$this->userShouldNotBelongToGroup($user, $group);
	}

	public function userBelongsToGroup($user, $group) {
		$fullUrl = $this->baseUrl . "v2.php/cloud/users/$user/groups";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->get($fullUrl, $options);
		$respondedArray = $this->getArrayOfGroupsResponded($this->response);

		if (in_array($group, $respondedArray)) {
			return True;
		} else {
			return False;
		}
	}

	/**
	 * @When /^the administrator adds user "([^"]*)" to group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function adminAddsUserToGroup($user, $group) {
		$previous_user = $this->currentUser;
		$this->currentUser = "admin";

		if (!$this->userBelongsToGroup($user, $group)) {
			$this->addingUserToGroup($user, $group);
		}

		$this->checkThatUserBelongsToGroup($user, $group);
		$this->currentUser = $previous_user;
	}

	/**
	 * @Given /^user "([^"]*)" has been added to group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function userHasBeenAddedToGroup($user, $group) {
		$this->adminAddsUserToGroup($user, $group);
	}

	/**
	 * @Given /^user "([^"]*)" belongs to group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function assureUserBelongsToGroup($user, $group) {
		$this->adminAddsUserToGroup($user, $group);
	}

	/**
	 * @param string $group
	 */
	public function rememberTheGroup($group) {
		if ($this->currentServer === 'LOCAL') {
			$this->createdGroups[$group] = $group;
		} elseif ($this->currentServer === 'REMOTE') {
			$this->createdRemoteGroups[$group] = $group;
		}
	}

	/**
	 * @When /^the administrator creates group "([^"]*)"$/
	 * @param string $group
	 */
	public function adminCreatesGroup($group) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/groups";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$options['body'] = [
							'groupid' => $group,
							];

		$this->response = $client->send($client->createRequest("POST", $fullUrl, $options));
		$this->rememberTheGroup($group);
	}

	/**
	 * @Given /^group "([^"]*)" has been created$/
	 * @param string $group
	 */
	public function groupHasBeenCreated($group) {
		$this->adminCreatesGroup($group);
	}

	/**
	 * @When /^creating the group "([^"]*)"$/
	 * @param string $group
	 */
	public function creatingTheGroup($group) {
		$this->adminCreatesGroup($group);
	}

	/**
	 * @When /^the administrator disables user "([^"]*)"$/
	 */
	public function adminDisablesUser($user) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users/$user/disable";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->send($client->createRequest("PUT", $fullUrl, $options));
	}

	/**
	 * @Given /^user "([^"]*)" has been disabled$/
	 */
	public function userHasBeenDisabled($user) {
		$this->adminDisablesUser($user);
	}

	/**
	 * @When /^assure user "([^"]*)" is disabled$/
	 */
	public function assureUserIsDisabled($user) {
		$this->adminDisablesUser($user);
	}

	/**
	 * @param string $user
	 */
	public function deleteTheUser($user) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users/$user";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->send($client->createRequest("DELETE", $fullUrl, $options));
	}

	/**
	 * @When /^the administrator deletes group "([^"]*)"$/
	 * @param string $group
	 */
	public function adminDeletesGroup($group) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/groups/$group";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->send($client->createRequest("DELETE", $fullUrl, $options));
	}

	/**
	 * @Given /^group "([^"]*)" has been deleted$/
	 * @param string $group
	 */
	public function groupHasBeenDeleted($group) {
		$this->adminDeletesGroup($group);
	}

	/**
	 * @param string $group
	 */
	public function deleteTheGroup($group) {
		$this->adminDeletesGroup($group);
	}

	/**
	 * @Given /^add user "([^"]*)" to the group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function addUserToGroup($user, $group) {
		PHPUnit_Framework_Assert::assertTrue($this->userExists($user));
		PHPUnit_Framework_Assert::assertTrue($this->groupExists($group));
		$this->addingUserToGroup($user, $group);
	}

	/**
	 * @When /^user "([^"]*)" is added to the group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function addingUserToGroup($user, $group) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users/$user/groups";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$options['body'] = [
							'groupid' => $group,
							];

		$this->response = $client->send($client->createRequest("POST", $fullUrl, $options));
	}

	public function groupExists($group) {
		$fullUrl = $this->baseUrl . "v2.php/cloud/groups/$group";
		$client = new Client();
		$options = [];
		$options['auth'] = $this->adminUser;
		try {
			$this->response = $client->get($fullUrl, $options);
			return True;
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			$this->response = $e->getResponse();
			return False;
		}
	}

	/**
	 * @Given /^group "([^"]*)" exists$/
	 * @param string $group
	 */
	public function assureGroupExists($group) {
		if (!$this->groupExists($group)) {
			$previous_user = $this->currentUser;
			$this->currentUser = "admin";
			$this->creatingTheGroup($group);
			$this->currentUser = $previous_user;
		}
		PHPUnit_Framework_Assert::assertTrue($this->groupExists($group));
	}

	/**
	 * @Given /^group "([^"]*)" does not exist$/
	 * @param string $group
	 */
	public function assureGroupDoesNotExist($group) {
		if ($this->groupExists($group)) {
			$previous_user = $this->currentUser;
			$this->currentUser = "admin";
			$this->deleteTheGroup($group);
			$this->currentUser = $previous_user;
		}
		PHPUnit_Framework_Assert::assertFalse($this->groupExists($group));
	}

	/**
	 * @Given /^user "([^"]*)" is subadmin of group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function userIsSubadminOfGroup($user, $group) {
		$fullUrl = $this->baseUrl . "v2.php/cloud/groups/$group/subadmins";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->get($fullUrl, $options);
		$respondedArray = $this->getArrayOfSubadminsResponded($this->response);
		sort($respondedArray);
		PHPUnit_Framework_Assert::assertContains($user, $respondedArray);
		PHPUnit_Framework_Assert::assertEquals(200, $this->response->getStatusCode());
	}

	/**
	 * @Given /^assure user "([^"]*)" is subadmin of group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function assureUserIsSubadminOfGroup($user, $group) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users/$user/subadmins";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}
		$options['body'] = [
							'groupid' => $group
							];
		$this->response = $client->send($client->createRequest("POST", $fullUrl, $options));
		PHPUnit_Framework_Assert::assertEquals(200, $this->response->getStatusCode());
	}

	/**
	 * @Given /^user "([^"]*)" is not a subadmin of group "([^"]*)"$/
	 * @param string $user
	 * @param string $group
	 */
	public function userIsNotSubadminOfGroup($user, $group) {
		$fullUrl = $this->baseUrl . "v2.php/cloud/groups/$group/subadmins";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->get($fullUrl, $options);
		$respondedArray = $this->getArrayOfSubadminsResponded($this->response);
		sort($respondedArray);
		PHPUnit_Framework_Assert::assertNotContains($user, $respondedArray);
		PHPUnit_Framework_Assert::assertEquals(200, $this->response->getStatusCode());
	}

	/**
	 * @Then /^users returned are$/
	 * @param \Behat\Gherkin\Node\TableNode|null $usersList
	 */
	public function theUsersShouldBe($usersList) {
		if ($usersList instanceof \Behat\Gherkin\Node\TableNode) {
			$users = $usersList->getRows();
			$usersSimplified = $this->simplifyArray($users);
			$respondedArray = $this->getArrayOfUsersResponded($this->response);
			PHPUnit_Framework_Assert::assertEquals($usersSimplified, $respondedArray, "", 0.0, 10, true);
		}

	}

	/**
	 * @Then /^groups returned are$/
	 * @param \Behat\Gherkin\Node\TableNode|null $groupsList
	 */
	public function theGroupsShouldBe($groupsList) {
		if ($groupsList instanceof \Behat\Gherkin\Node\TableNode) {
			$groups = $groupsList->getRows();
			$groupsSimplified = $this->simplifyArray($groups);
			$respondedArray = $this->getArrayOfGroupsResponded($this->response);
			PHPUnit_Framework_Assert::assertEquals($groupsSimplified, $respondedArray, "", 0.0, 10, true);
		}

	}

	/**
	 * @Then /^subadmin groups returned are$/
	 * @param \Behat\Gherkin\Node\TableNode|null $groupsList
	 */
	public function theSubadminGroupsShouldBe($groupsList) {
		if ($groupsList instanceof \Behat\Gherkin\Node\TableNode) {
			$groups = $groupsList->getRows();
			$groupsSimplified = $this->simplifyArray($groups);
			$respondedArray = $this->getArrayOfSubadminsResponded($this->response);
			PHPUnit_Framework_Assert::assertEquals($groupsSimplified, $respondedArray, "", 0.0, 10, true);
		}

	}

	/**
	 * @Then /^apps returned are$/
	 * @param \Behat\Gherkin\Node\TableNode|null $appList
	 */
	public function theAppsShouldBe($appList) {
		if ($appList instanceof \Behat\Gherkin\Node\TableNode) {
			$apps = $appList->getRows();
			$appsSimplified = $this->simplifyArray($apps);
			$respondedArray = $this->getArrayOfAppsResponded($this->response);
			foreach ($appsSimplified as $app) {
				PHPUnit_Framework_Assert::assertContains($app, $respondedArray);
			}
		}
	}

	/**
	 * @Then /^subadmin users returned are$/
	 * @param \Behat\Gherkin\Node\TableNode|null $groupsList
	 */
	public function theSubadminUsersShouldBe($groupsList) {
		$this->theSubadminGroupsShouldBe($groupsList);
	}

	/**
	 * Parses the xml answer to get the array of users returned.
	 * @param ResponseInterface $resp
	 * @return array
	 */
	public function getArrayOfUsersResponded($resp) {
		$listCheckedElements = $resp->xml()->data[0]->users[0]->element;
		$extractedElementsArray = json_decode(json_encode($listCheckedElements), 1);
		return $extractedElementsArray;
	}

	/**
	 * Parses the xml answer to get the array of groups returned.
	 * @param ResponseInterface $resp
	 * @return array
	 */
	public function getArrayOfGroupsResponded($resp) {
		$listCheckedElements = $resp->xml()->data[0]->groups[0]->element;
		$extractedElementsArray = json_decode(json_encode($listCheckedElements), 1);
		return $extractedElementsArray;
	}

	/**
	 * Parses the xml answer to get the array of apps returned.
	 * @param ResponseInterface $resp
	 * @return array
	 */
	public function getArrayOfAppsResponded($resp) {
		$listCheckedElements = $resp->xml()->data[0]->apps[0]->element;
		$extractedElementsArray = json_decode(json_encode($listCheckedElements), 1);
		return $extractedElementsArray;
	}

	/**
	 * Parses the xml answer to get the array of subadmins returned.
	 * @param ResponseInterface $resp
	 * @return array
	 */
	public function getArrayOfSubadminsResponded($resp) {
		$listCheckedElements = $resp->xml()->data[0]->element;
		$extractedElementsArray = json_decode(json_encode($listCheckedElements), 1);
		return $extractedElementsArray;
	}


	/**
	 * @Given /^app "([^"]*)" is disabled$/
	 * @param string $app
	 */
	public function appIsDisabled($app) {
		$fullUrl = $this->baseUrl . "v2.php/cloud/apps?filter=disabled";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->get($fullUrl, $options);
		$respondedArray = $this->getArrayOfAppsResponded($this->response);
		PHPUnit_Framework_Assert::assertContains($app, $respondedArray);
		PHPUnit_Framework_Assert::assertEquals(200, $this->response->getStatusCode());
	}

	/**
	 * @Given /^app "([^"]*)" is enabled$/
	 * @param string $app
	 */
	public function appIsEnabled($app) {
		$fullUrl = $this->baseUrl . "v2.php/cloud/apps?filter=enabled";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->get($fullUrl, $options);
		$respondedArray = $this->getArrayOfAppsResponded($this->response);
		PHPUnit_Framework_Assert::assertContains($app, $respondedArray);
		PHPUnit_Framework_Assert::assertEquals(200, $this->response->getStatusCode());
	}

	/**
	 * @Then /^user "([^"]*)" is disabled$/
	 * @param string $user
	 */
	public function userIsDisabled($user) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users/$user";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->get($fullUrl, $options);
		PHPUnit_Framework_Assert::assertEquals("false", $this->response->xml()->data[0]->enabled);
	}

	/**
	 * @Then /^user "([^"]*)" is enabled$/
	 * @param string $user
	 */
	public function userIsEnabled($user) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users/$user";
		$client = new Client();
		$options = [];
		if ($this->currentUser === 'admin') {
			$options['auth'] = $this->adminUser;
		}

		$this->response = $client->get($fullUrl, $options);
		PHPUnit_Framework_Assert::assertEquals("true", $this->response->xml()->data[0]->enabled);
	}

	/**
	 * @Given user :user has a quota of :quota
	 * @param string $user
	 * @param string $quota
	 */
	public function userHasAQuotaOf($user, $quota)
	{
		$body = new \Behat\Gherkin\Node\TableNode([
			0 => ['key', 'quota'],
			1 => ['value', $quota],
		]);

		// method used from BasicStructure trait
		$this->sendingToWith("PUT", "/cloud/users/" . $user, $body);
		PHPUnit_Framework_Assert::assertEquals(200, $this->response->getStatusCode());
	}

	/**
	 * @Given user :user has unlimited quota
	 * @param string $user
	 */
	public function userHasUnlimitedQuota($user)
	{
		$this->userHasAQuotaOf($user, 'none');
	}

	/**
	 * Returns home path of the given user
	 * @param string $user
	 */
	public function getUserHome($user) {
		$fullUrl = $this->baseUrl . "v{$this->apiVersion}.php/cloud/users/$user";
		$client = new Client();
		$options = [];
		$options['auth'] = $this->adminUser;
		$this->response = $client->get($fullUrl, $options);
		return $this->response->xml()->data[0]->home;
	}

	/**
	 * @Then /^user attributes match with$/
	 * @param \Behat\Gherkin\Node\TableNode|null $body
	 */
	public function checkUserAttributes($body) {
		$data = $this->response->xml()->data[0];
		if ($body instanceof \Behat\Gherkin\Node\TableNode) {
			$fd = $body->getRowsHash();
			foreach ($fd as $field => $value) {
				if ($data->$field != $value) {
					PHPUnit_Framework_Assert::fail("$field" . " has value " . "$data->$field");
				}
			}
		}
	}

	/**
	 * @BeforeScenario
	 * @AfterScenario
	 */
	public function cleanupUsers()
	{
		$previousServer = $this->currentServer;
		$this->usingServer('LOCAL');
		foreach ($this->createdUsers as $user) {
			$this->deleteUser($user);
		}
		$this->usingServer('REMOTE');
		foreach ($this->createdRemoteUsers as $remoteUser) {
			$this->deleteUser($remoteUser);
		}
		$this->usingServer($previousServer);
	}

	/**
	 * @BeforeScenario
	 * @AfterScenario
	 */
	public function cleanupGroups()
	{
		$previousServer = $this->currentServer;
		$this->usingServer('LOCAL');
		foreach ($this->createdGroups as $group) {
			$this->deleteGroup($group);
		}
		$this->usingServer('REMOTE');
		foreach ($this->createdRemoteGroups as $remoteGroup) {
			$this->deleteGroup($remoteGroup);
		}
		$this->usingServer($previousServer);
	}

}
