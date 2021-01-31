<?php

/*
 * This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
 * Copyright (c) 2018 NextDom.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';
require_once "AndroidRemoteControlCmd.class.php";

class AndroidRemoteControl extends eqLogic {

	public static $_sudo_prefix = null;

	public static $_widgetPossibility = array(
		'custom' => true,
		'custom::layout' => false,
		'parameters' => array(
			'sub-background-color' => array(
				'name' => 'Couleur de la barre de contrôle',
				'type' => 'color',
				'default' => 'rgba(0,0,0,0.5)',
				'allow_transparent' => true,
				'allow_displayType' => true,
			),
		),
	);

	public static function getSudoPrefix() {
		if(AndroidRemoteControl::$_sudo_prefix == null) {
			if (exec("\$EUID") != "0") {
				AndroidRemoteControl::$_sudo_prefix = "sudo ";
			} else {
				AndroidRemoteControl::$_sudo_prefix = "";
			}
		}
		return AndroidRemoteControl::$_sudo_prefix;
	}

	public static function cron() {
		foreach (eqLogic::byType(__CLASS__, true) as $eqLogic) {
			$eqLogic->updateInfo();
		}
	}

	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'AndroidRemoteControl_dep';
		$return['progress_file'] = '/tmp/AndroidRemoteControl_dep';
		$adb = '/usr/bin/adb';
		if (is_file($adb)) {
			$return['state'] = 'ok';
		} else {
			exec('echo AndroidRemoteControl dependency not found : ' . $adb . ' > ' . log::getPathToLog('AndroidRemoteControl_log') . ' 2>&1 &');
			$return['state'] = 'nok';
		}
		return $return;
	}

	public static function dependancy_install() {
		log::add(__CLASS__, 'info', 'Installation des dépéndances android-tools-adb');
		$resource_path = realpath(__DIR__ . '/../../3rdparty');
		passthru('/bin/bash ' . $resource_path . '/install.sh ' . $resource_path . ' > ' . log::getPathToLog('AndroidRemoteControl_dep') . ' 2>&1 &');
	}

	public function runcmd($_cmd) {
		$type_connection = $this->getConfiguration('type_connection');
		$ip_address = $this->getConfiguration('ip_address');
		$sudo_prefix = AndroidRemoteControl::getSudoPrefix();
		switch ($type_connection) {
			case "TCPIP": 
				$data = shell_exec($sudo_prefix . "adb -s " . $ip_address . ":5555 " . $_cmd);
				break;
			default :
				$data = shell_exec($sudo_prefix . "adb " . $_cmd);
		}
		return $data;
	}

	public static function resetADB() {
		$sudo_prefix = AndroidRemoteControl::getSudoPrefix();
		log::add(__CLASS__, 'debug', 'Arret du service ADB');
		shell_exec($sudo_prefix . "adb kill-server");
		sleep(3);
		log::add(__CLASS__, 'debug', 'Lancement du service ADB');
		shell_exec($sudo_prefix . "adb start-server");
	}

	public function connectADB($ip_address = null) {
		$sudo_prefix = AndroidRemoteControl::getSudoPrefix();
		if ($ip_address == null) {
			$ip_address = $this->getConfiguration('ip_address');
		}
		log::add(__CLASS__, 'debug', 'Connection au périphérique ' . $ip_address . ' encours');
		shell_exec($sudo_prefix . "adb connect " . $ip_address);
	}

	public function postSave() {
		$json_cmd_list = json_decode(file_get_contents(__DIR__ . '/../../3rdparty/commandes.json'));
		foreach ($json_cmd_list as $json_cmd) {
			$configurationList['categorie'] =  $json_cmd->categorie;
			$configurationList['icon'] =  $json_cmd->icon;
			$configurationList['commande'] =  $json_cmd->commande;
			$this->createCmd($json_cmd->id, $json_cmd->name, $json_cmd->type, $json_cmd->subtype, false, null, $configurationList)->save();
		}

		$sudo_prefix = AndroidRemoteControl::getSudoPrefix();
		$type_connection = $this->getConfiguration('type_connection');
		switch ($type_connection) {
			case "TCPIP":
				log::add(__CLASS__, 'debug', "Restart ADB en mode TCP");
				$check = shell_exec($sudo_prefix . "adb devices TCPIP 5555");
				break;
			case "USB":
				log::add(__CLASS__, 'debug', "Restart ADB en mode USB");
				$check = shell_exec($sudo_prefix . "adb devices USB");
				break;
			default :
				log::add(__CLASS__, 'info', "Le type de connection " . $type_connection . " n’est pas pris en compte.");
		}
	}
	

	public function createCmd($logicalId, $name, $type = 'info', $subtype = 'string', $icon = false, $generic_type = null, $configurationList = [], $placeholderList = []) {
		$cmd = $this->getCmd(null, $logicalId);
		if (!is_object($cmd)) {
			$cmd = new AndroidRemoteControlCmd();
			$cmd->setLogicalId($logicalId);
			$cmd->setName(__($name, __FILE__));
		}
		$cmd->setType($type);
		$cmd->setSubType($subtype);
		$cmd->setGeneric_type($generic_type);
		if($icon) {
			$cmd->setDisplay('icon',$icon);
		}
		foreach ($configurationList as $key => $value){
			$cmd->setConfiguration($key, $value);
		}
		foreach ($placeholderList as $value){
			$cmd->setDisplay($value . '_placeholder', __('placeholder.'.$value, __FILE__));
		}
		$cmd->setEqLogic_id($this->getId());
		return $cmd;
	}


	public function preUpdate() {
		if ($this->getConfiguration('ip_address') == '') {
			throw new \Exception(__('L’adresse IP doit être renseignée', __FILE__));
		}
	}

	public function getInfo() {
		$isConnect = $this->checkAndroidRemoteControlStatus();
		if(!$isConnect) {
			return null;
		} else {
			$power_state = substr($this->runcmd(" "), 0, -1);
			log::add(__CLASS__, 'debug', "power_state: " . $power_state);
			$encours = substr($this->runcmd("shell dumpsys window windows | grep -E 'mFocusedApp'| cut -d / -f 1 | cut -d ' ' -f 7"), 0, -1);
			log::add(__CLASS__, 'debug', "encours: " . $encours);
			$version_android = substr($this->runcmd("shell getprop ro.build.version.release"), 0, -1);
			log::add(__CLASS__, 'debug', "version_android: " . $version_android);
			$name = substr($this->runcmd("shell getprop ro.product.model"), 0, -1);
			log::add(__CLASS__, 'debug', "name: " . $name);
			$type = substr($this->runcmd("shell getprop ro.build.characteristics"), 0, -1);
			log::add(__CLASS__, 'debug', "type: " . $type);
			$resolution = substr($this->runcmd("shell dumpsys window displays | grep init | cut -c45-53"), 0, -1);
			log::add(__CLASS__, 'debug', "resolution: " . $resolution);
			$disk_free = substr($this->runcmd("shell dumpsys diskstats | grep Data-Free | cut -d' ' -f7"), 0, -1);
			log::add(__CLASS__, 'debug', "disk_free: " . $disk_free);
			$disk_total = round(substr($this->runcmd("shell dumpsys diskstats | grep Data-Free | cut -d' ' -f4"), 0, -1) / 1000000, 1);
			log::add(__CLASS__, 'debug', "disk_total: " . $disk_total);
			$title = substr($this->runcmd("shell dumpsys bluetooth_manager | grep MediaPlayerInfo | grep .$encours. |cut -d')' -f3 | cut -d, -f1 | grep -v null | sed 's/^\ *//g'"), 0);
			log::add(__CLASS__, 'debug', "title: " . $title);
			$volume = substr($this->runcmd("shell media volume --stream 3 --get | grep volume |grep is | cut -d\ -f4"), 0, -1);
			log::add(__CLASS__, 'debug', "volume: " . $volume);
			$play_state = substr($this->runcmd("shell dumpsys bluetooth_manager | grep mCurrentPlayState | cut -d,  -f1 | cut -c43-"), 0, -1);
			log::add(__CLASS__, 'debug', "play_state: " . $play_state);
			$battery_level = substr($this->runcmd("shell dumpsys battery | grep level | cut -d: -f2"), 0, -1);
			log::add(__CLASS__, 'debug', "battery_level: " . $battery_level);
			$battery_status = substr($this->runcmd("shell dumpsys battery | grep status"), -3);
			log::add(__CLASS__, 'debug', "battery_status: " . $battery_status);
		}

		return array('power_state' => $power_state, 'encours' => $encours, 'version_android' => $version_android, 'name' => $name, 'type' => $type, 'resolution' => $resolution, 'disk_total' => $disk_total, 'disk_free' => $disk_free, 'title' => $title, 'volume' => $volume, 'play_state' => $play_state, 'battery_level' => $battery_level, 'battery_status' => $battery_status);
	}

	public function updateInfo() {
		try {
			$infos = $this->getInfo();
			if (!is_array($infos)) {
				return;
			}
			log::add(__CLASS__, 'info', 'Rafraichissement des informations');
			if (isset($infos['power_state'])) {
				$this->checkAndUpdateCmd('power_state', ($infos['power_state'] == "ON") ? 1 : 0 );
			}
			if (isset($infos['encours'])) {
				$this->checkAndUpdateCmd('encours', $infos['encours']);
				$cmd = $this->getCmd(null, $infos['encours']);
				if (!is_object($cmd)) {
					$json_appli_list = json_decode(file_get_contents(__DIR__ . '/../../3rdparty/appli.json'));
					$appli_found = false;
					foreach ($json_appli_list as $json_cmd) {
						if ($infos['encours'] == $json_cmd->id) {
							$appli_found = true;
							$configurationList['categorie'] =  $json_cmd->categorie;
							$configurationList['icon'] =  $json_cmd->icon;
							$configurationList['commande'] =  $json_cmd->commande;
							$this->createCmd($json_cmd->id, $json_cmd->name, $json_cmd->type, $json_cmd->subtype, false, null, $configurationList)->save();
							log::add(__CLASS__, 'info', 'Nouvelle commande application ' . $infos['encours'] . ' crée.');
							break;
						}
					}
					if(!$appli_found) {
						$configurationList['categorie'] = "appli";
						$configurationList['icon'] = '<i class="fa fa-cogs"></i>';
						$configurationList['commande'] = "shell monkey -p " . $infos['encours'] . " -c android.intent.category.LAUNCHER 1";
						$this->createCmd($infos['encours'], $infos['encours'], "action", "other", false, null, $configurationList)->save();
						log::add(__CLASS__, 'warning', 'Nouvelle commande application ' . $infos['encours'] . ' doit être crée.');
					}
				}
			}
			if (isset($infos['version_android'])) {
				$this->checkAndUpdateCmd('version_android', $infos['version_android']);
			}
			if (isset($infos['name'])) {
				$this->checkAndUpdateCmd('name', $infos['name']);
			}

			if (isset($infos['type'])) {
				$this->checkAndUpdateCmd('type', $infos['type']);
			}
			if (isset($infos['resolution'])) {
				$this->checkAndUpdateCmd('resolution', $infos['resolution']);
			}
			if (isset($infos['disk_free'])) {
				$this->checkAndUpdateCmd('disk_free', $infos['disk_free']);
			}
			if (isset($infos['disk_total'])) {
				$this->checkAndUpdateCmd('disk_total', $infos['disk_total']);
			}
			if (isset($infos['title'])) {
				$this->checkAndUpdateCmd('title', $infos['title']);
			}
			if (isset($infos['volume'])) {
				$this->checkAndUpdateCmd('volume', $infos['volume']);
			}
			if (isset($infos['play_state'])) {
				switch ($infos['play_state']) {
					case 0: $play_state = "arret"; break;
					case 2: $play_state = "pause"; break;
					case 3: $play_state = "lecture"; break;
					default : $play_state = "inconnue:" . $infos['play_state'];
				}
				$this->checkAndUpdateCmd('play_state', $play_state);
			}

			if (isset($infos['battery_level'])) {
				$this->checkAndUpdateCmd('battery_level', $infos['battery_level']);
			}
			if (isset($infos['battery_status'])) {
				switch ($infos['battery_status']) {
					case 2: $battery_status = "en charge"; break;
					case 3: $battery_status = "en décharge"; break;
					case 4: $battery_status = "pas de charge"; break;
					case 5: $battery_status = "pleine"; break;
					default : $battery_status = "inconnue:" . $infos['play_state'];
				}
				$this->checkAndUpdateCmd('battery_status', $battery_status);
			}
		} catch (\Exception $e) {
			return;
		}
	}

	public function checkAndroidRemoteControlStatus() {
		$sudo_prefix = AndroidRemoteControl::getSudoPrefix();
		$ip_address = $this->getConfiguration('ip_address');

		$type_connection = $this->getConfiguration('type_connection');
		log::add(__CLASS__, 'debug', "Check de la connection " . $type_connection);
		$check = null;
		switch ($type_connection) {
			case "TCPIP":
				$check = shell_exec($sudo_prefix . "adb devices | grep " . $ip_address . " | cut -f2 | xargs");
				break;
			case "USB":
				$check = shell_exec($sudo_prefix . "adb devices | grep " . $ip_address . " | cut -f2 | xargs");
				break;
			default :
				log::add(__CLASS__, 'info', "Le type de connection " . $type_connection . " n’est pas pris en compte.");
		}

		$this->checkAndUpdateCmd('encours', $check);
		switch ($check) {
			case "offline":
				log::add(__CLASS__, 'info', 'Votre appareil est offline');
				break;
			case "device":
				$this->connectADB($ip_address);
				break;
			case "unauthorized":
				log::add(__CLASS__, 'info', 'Votre connection n’est pas autorisé');
				break;
			default :
				log::add(__CLASS__, 'info', 'Votre appareil n’est pas détecté par ADB : ' .$check . ".");
		}
		return $check === "device";
	}

	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		$replace['#version#'] = $_version;
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}

		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
			$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
			$replace['#' . $cmd->getLogicalId() . '_icon#'] = $cmd->getDisplay('icon');

			if ($cmd->getIsHistorized() == 1) {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
			}
			$replace['#' . $cmd->getLogicalId() . '_id_display#'] = ($cmd->getIsVisible()) ? '#' . $cmd->getLogicalId() . "_id_display#" : "none";
		}
		$replace['#applis#'] = "";
		foreach ($this->getCmd('action') as $cmd) {
			if ($cmd->getConfiguration('categorie') == 'appli') {
				$replace['#applis#'] .= '<a class="nav-item nav-link cmd btn" style="display:#' . $cmd->getLogicalId() . '_id_display#; padding:3px" data-cmd_id="' . $cmd->getId() . '" '
													. 'onclick="jeedom.cmd.execute({id: ' . $cmd->getId() . '});">'
											. '<img src="plugins/AndroidRemoteControl/desktop/images/' . $cmd->getLogicalId() . '.png">'
											. '<span>' .$cmd->getName(). '</span>'
										. '</a>';
			} else {
				$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
				$replace['#' . $cmd->getLogicalId() . '_id_display#'] = (is_object($cmd) && $cmd->getIsVisible()) ? '#' . $cmd->getId() . "_id_display#" : 'none';
			}
			$replace['#' . $cmd->getLogicalId() . '_id_display#'] = ($cmd->getIsVisible()) ? '#' . $cmd->getLogicalId() . "_id_display#" : "none";
		}

		$replace['#ip#'] = $this->getConfiguration('ip_address');

		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'eqLogic', __CLASS__)));
	}
}