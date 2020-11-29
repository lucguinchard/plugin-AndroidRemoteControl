<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once "AndroidRemoteControl.class.php";

class AndroidRemoteControlCmd extends cmd {

	public function execute($_options = null) {
		if ($this->getType() == 'info') {
			return;
		}

		$ARC = $this->getEqLogic();
		$ARC->checkAndroidRemoteControlStatus();

		$sudo_prefix = AndroidRemoteControl::getSudoPrefix();
		$ip_address = $ARC->getConfiguration('ip_address');

		log::add(AndroidRemoteControl::__CLASS__, 'info', 'Command ' . $this->getConfiguration('commande') . ' sent to android device at ip address : ' . $ip_address);
		shell_exec($sudo_prefix . "adb -s " . $ip_address . ":5555 " . $this->getConfiguration('commande'));

		if (stristr($this->getLogicalId(), 'setVolume')) {
			shell_exec($sudo_prefix . "adb -s " . $ip_address . ":5555 shell media volume --stream 3  --set " . $_options['slider']);
		}

		$ARC->updateInfo();
	}

}
