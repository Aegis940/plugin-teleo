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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

// Fonction exécutée automatiquement après l'installation du plugin
  function teleo_install() {
    $cronMinute = config::byKey('cronMinute', 'teleo');
    if (empty($cronMinute)) {
      $randMinute = rand(3, 59);
      config::save('cronMinute', $randMinute, 'teleo');
    }

	$startCheckHour = config::byKey('startCheckHour', 'teleo');
    if (empty($startCheckHour)) {
      $startCheckHour = 4;
      config::save('startCheckHour', $startCheckHour, 'teleo');
    }	
	
	chmod("/var/www/html/plugins/teleo/resources/get_veolia_data.sh", 0755);
	chmod("/var/www/html/plugins/teleo/resources/install_apt_deb10.sh", 0755);  
  }

// Fonction exécutée automatiquement après la mise à jour du plugin
  function teleo_update() {
    $cronMinute = config::byKey('cronMinute', 'teleo');
    if (empty($cronMinute)) {
      $randMinute = rand(3, 59);
      config::save('cronMinute', $randMinute, 'teleo');
    }

	$startCheckHour = config::byKey('startCheckHour', 'teleo');
    if (empty($startCheckHour)) {
      $startCheckHour = 4;
      config::save('startCheckHour', $startCheckHour, 'teleo');
    }

	chmod("/var/www/html/plugins/teleo/resources/get_veolia_data.sh", 0755);
	chmod("/var/www/html/plugins/teleo/resources/install_apt_deb10.sh", 0755); 
	
	$file = '/var/www/html/plugins/teleo/plugin_info/packages.json';
	if (file_exists($file)) {
		$cmdCleanBash = system::getCmdSudo() . "rm " . $file;
		shell_exec($cmdCleanBash);		
	}
   }

// Fonction exécutée automatiquement après la suppression du plugin
  function teleo_remove() {

  }

?>
