<?php
/**
 * @author Lukas Reschke <lukas@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

require __DIR__ . '/../../../../lib/composer/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;

class CalDavContext implements \Behat\Behat\Context\Context {
	/** @var string  */
	private $baseUrl;
	/** @var Client */
	private $client;
	/** @var ResponseInterface */
	private $response;
	/** @var array */
	private $responseXml = '';

	/**
	 * @param string $baseUrl
	 */
	public function __construct($baseUrl) {
		$this->baseUrl = $baseUrl;

		// in case of ci deployment we take the server url from the environment
		$testServerUrl = getenv('TEST_SERVER_URL');
		if ($testServerUrl !== false) {
			$this->baseUrl = substr($testServerUrl, 0, -5);
		}
	}

	/** @BeforeScenario @caldav */
	public function setUpScenario() {
		$this->client = new Client();
		$this->responseXml = '';
	}

	/** @AfterScenario @caldav */
	public function afterScenario() {
		$davUrl = $this->baseUrl. '/remote.php/dav/calendars/admin/MyCalendar';
		try {
			$this->client->delete(
				$davUrl,
				[
					'auth' => [
						'admin',
						'admin',
					],
				]
			);
		} catch (\GuzzleHttp\Exception\ClientException $e) {}
	}

	/**
	 * @When user :user requests calendar :calendar using the API
	 * @param string $user
	 * @param string $calendar
	 */
	public function userRequestsCalendarUsingTheAPI($user, $calendar) {
		$davUrl = $this->baseUrl . '/remote.php/dav/calendars/'.$calendar;

		$password = ($user === 'admin') ? 'admin' : '123456';
		try {
			$this->response = $this->client->get(
				$davUrl,
				[
					'auth' => [
						$user,
						$password,
					]
				]
			);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			$this->response = $e->getResponse();
		}
	}

	/**
	 * @Then the CalDAV HTTP status code should be :code
	 * @param int $code
	 * @throws \Exception
	 */
	public function theCaldavHttpStatusCodeShouldBe($code) {
		if ((int)$code !== $this->response->getStatusCode()) {
			throw new \Exception(
				sprintf(
					'Expected %s got %s',
					(int)$code,
					$this->response->getStatusCode()
				)
			);
		}

		$body = $this->response->getBody()->getContents();
		if ($body && substr($body, 0, 1) === '<') {
			$reader = new Sabre\Xml\Reader();
			$reader->xml($body);
			$this->responseXml = $reader->parse();
		}
	}

	/**
	 * @Then the exception should be :message
	 * @param string $message
	 * @throws \Exception
	 */
	public function theExceptionShouldBe($message) {
		$result = $this->responseXml['value'][0]['value'];

		if ($message !== $result) {
			throw new \Exception(
				sprintf(
					'Expected %s got %s',
					$message,
					$result
				)
			);
		}
	}

	/**
	 * @Then the error message should be :message
	 * @param string $message
	 * @throws \Exception
	 */
	public function theErrorMessageShouldBe($message) {
		$result = $this->responseXml['value'][1]['value'];

		if ($message !== $result) {
			throw new \Exception(
				sprintf(
					'Expected %s got %s',
					$message,
					$result
				)
			);
		}
	}

	/**
	 * @Given user :user has created a calendar named :name
	 * @param string $user
	 * @param string $name
	 */
	public function createsACalendarNamed($user, $name) {
		$davUrl = $this->baseUrl . '/remote.php/dav/calendars/'.$user.'/'.$name;
		$password = ($user === 'admin') ? 'admin' : '123456';

		$request = $this->client->createRequest(
			'MKCALENDAR',
			$davUrl,
			[
				'body' => '<c:mkcalendar xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:" xmlns:a="http://apple.com/ns/ical/" xmlns:o="http://owncloud.org/ns"><d:set><d:prop><d:displayname>test</d:displayname><o:calendar-enabled>1</o:calendar-enabled><a:calendar-color>#21213D</a:calendar-color><c:supported-calendar-component-set><c:comp name="VEVENT"/></c:supported-calendar-component-set></d:prop></d:set></c:mkcalendar>',
				'auth' => [
					$user,
					$password,
				],
			]
		);

		$this->response = $this->client->send($request);
	}

}
