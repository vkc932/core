<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Michael Jobst <mjobst+github@tecratech.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Philipp Schaffrath <github@philippschaffrath.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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

namespace OC\Template;

class CSSResourceLocator extends ResourceLocator {
	/**
	 * @param string $style
	 */
	public function doFind($style) {
		if (
			$this->appendOnceIfExist($this->serverroot, $style.'.css')
			|| $this->appendOnceIfExist($this->serverroot, 'core/'.$style.'.css')
		) {
			return;
		}
		$app = substr($style, 0, strpos($style, '/'));
		$style = substr($style, strpos($style, '/')+1);

		$app_path = \OC_App::getAppPath($app);
		if( $app_path === false ) { return; }
		$app_url = \OC_App::getAppWebPath($app);
		$app_url = ($app_url !== false) ? $app_url : null;
		$this->appendOnceIfExist($app_path, $style.'.css', $app_url);
	}

	/**
	 * @param string $style
	 */
	public function doFindTheme($style) {
		$fullStyle = $style . '.css';
		$themeDirectory = $this->theme->getDirectory();
		$baseDirectory = $this->theme->getBaseDirectory();
		$webRoot = '';
		if ($baseDirectory !== $this->serverroot) {
			$webRoot = substr($this->theme->getWebPath(), 0, -strlen($themeDirectory));
		}

		$searchLocations = [
			$this->buildPath([$themeDirectory, '/apps', $fullStyle]),
			$this->buildPath([$themeDirectory, $fullStyle]),
			$this->buildPath([$themeDirectory, '/core', $fullStyle]),
		];

		foreach ($searchLocations as $location) {
			if ($this->appendOnceIfExist($baseDirectory, $location, $webRoot)) {
				break;
			}
		}
	}
}
