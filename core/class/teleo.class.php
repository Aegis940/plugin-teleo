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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class teleo extends eqLogic {
    /*     * *************************Attributs****************************** */

  /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */
	const PYTHON_PATH = __DIR__ . '/../../resources/venv/bin/python3';
	
    /*     * ***********************Methode static*************************** */
	public static function backupExclude() {
		return [
			'resources/venv',
          	'resources/geckodriver',
          	'resources/data'
		];
	}
  
	public static function dependancy_info() {
		
		// check OS version
		$lsb_release = self::os_version();
		
		if ($lsb_release <= 10) {
			return self::dependancy_info_v1();
		}
	
		$return = array();
		$return['log'] = log::getPathToLog(__CLASS__ . '_update');
		$return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependance';
		$return['state'] = 'ok';
		if (file_exists(jeedom::getTmpFolder(__CLASS__) . '/dependance')) {
			$return['state'] = 'in_progress';
		} elseif (!self::pythonRequirementsInstalled(self::PYTHON_PATH, __DIR__ . '/../../resources/requirements.txt')) {
			$return['state'] = 'nok';
		} elseif (!file_exists('/usr/local/bin/geckodriver')) {
			$return['state'] = 'nok';
		} elseif (exec(system::getCmdSudo() . system::get('cmd_check') . '-Ec "xvfb|firefox"') < 2) {
			$return['state'] = 'nok';
		}
	
		return $return;
	}

   	private static function os_version() {
		$linux_distro = null;
		$os_info = file_exists("/etc/os-release") ? explode("\n", shell_exec("cat /etc/os-release")) : [];
		foreach ($os_info as $line) {
			if (preg_match('/^VERSION_ID=(")?[0-9][1-9]+(")?/i', trim($line))) {
				$linux_distro = strtolower(preg_replace('/(^VERSION_ID=(")?|")/i', "", trim($line)));         	
				break;
			}
		}
		
		return $linux_distro;
	}
	
	private static function dependancy_info_v1() {
		$return = array();
		$return['log'] = log::getPathToLog(__CLASS__.'_update');
		$return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependency';
		$return['state'] = 'ok';
		if (file_exists(jeedom::getTmpFolder(__CLASS__) . '/dependencies')) {
			$return['state'] = 'in_progress';
		} else if (exec(system::getCmdSudo() . system::get('cmd_check') . '-Ec "xvfb|firefox|python3\-pip"') < 3) {
			$return['state'] = 'nok';
		} elseif (exec(system::getCmdSudo() . 'pip3 list | grep -Ec "selenium|PyVirtualDisplay|urllib3"') < 3) {
			$return['state'] = 'nok';
		} elseif (!file_exists('/usr/local/bin/geckodriver')) {
			$return['state'] = 'nok';
		}			
				
		return $return;
	}
	
	private static function pythonRequirementsInstalled(string $pythonPath, string $requirementsPath) {
		if (!file_exists($pythonPath) || !file_exists($requirementsPath)) {
			return false;
		}
		exec("{$pythonPath} -m pip freeze", $packages_installed);
		$packages = join("||", $packages_installed);
		exec("cat {$requirementsPath}", $packages_needed);
		foreach ($packages_needed as $line) {
			if (preg_match('/([^\s]+)[\s]*([>=~]=)[\s]*([\d+\.?]+)$/', $line, $need) === 1) {
				if (preg_match('/' . $need[1] . '==([\d+\.?]+)/', strtolower($packages), $install) === 1) {
					if ($need[2] == '==' && $need[3] != $install[1]) {
						return false;
					} elseif (version_compare($need[3], $install[1], '>')) {
						return false;
					}
				} else {
					return false;
				}
			}
		}
		return true;
	}

  public static function dependancy_install() {
		log::remove(__CLASS__ . '_update');
		return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder(__CLASS__) . '/dependancy', 'log' => log::getPathToLog(__CLASS__ . '_update'));
  }

  public static function cron()
  {
    $cronMinute = config::byKey('cronMinute', __CLASS__);
    if (!empty($cronMinute) && date('i') != $cronMinute) return;

	$startCheckHour = config::byKey('startCheckHour', __CLASS__);
	if (empty($startCheckHour)) {
		$startCheckHour = 4;
		config::save('startCheckHour', $startCheckHour, __CLASS__);
	}
	
    if ($startCheckHour < 1) {
       $startCheckHour = 1;
	   config::save('startCheckHour', $startCheckHour, __CLASS__);
	}
	elseif ($startCheckHour > 20) {
		$startCheckHour = 20;
		config::save('startCheckHour', $startCheckHour, __CLASS__);
	}

    $eqLogics = self::byType(__CLASS__, true);


    foreach ($eqLogics as $eqLogic)
    { 
	
      if (date('G') < $startCheckHour || date('G') >= 23)
      {
		if ((date('G') == 23) && ($eqLogic->getCache('getTeleoData') != 'done'))
		{
			# Aucune donnée récupérée
			$dateYesterday = date('Y-m-d', strtotime('-1 day'));
			
			log::add(__CLASS__, 'error', $eqLogic->getHumanName() . ' Aucune donnée n\'a pu être récupérée pour la journée du ' . $dateYesterday);
		}
		
        if ($eqLogic->getCache('getTeleoData') == 'done')
    	{
          $eqLogic->setCache('getTeleoData', null);
        }
		
        return;
      }

      if ($eqLogic->getCache('getTeleoData') != 'done')
      {
        $eqLogic->pullTeleo();
      }
    }
  }

    /*     * *********************Méthodes d'instance************************* */

    public function pullTeleo($callRefreshByUser=false) {
      $need_refresh = false;

	  $cmdInfos = ['index','consod','consoh','consom','consoa'];
	  
	  foreach ($cmdInfos as $logicalId)
	  {
		$eqLogicCmd = $this->getCmd(null, $logicalId);      
        $eqLogicCmd->execCmd();
        if ($eqLogicCmd->getCollectDate() == date('Y-m-d 00:00:00', strtotime('-1 day')) && $this->getConfiguration('forceRefresh') != 1 && $callRefreshByUser==false) 
        {
          log::add(__CLASS__, 'debug', $this->getHumanName() . ' le ' . date('d/m/Y', strtotime('-1 day')) . ' : données déjà présentes pour la commande ' . $eqLogicCmd->getName());
        }
        else
        {
          $need_refresh = true;
          if ($this->getConfiguration('forceRefresh') == 1) {
            log::add(__CLASS__, 'debug', $this->getHumanName() . ' le ' . date('d/m/Y', strtotime('-1 day')) . ' : données déjà présentes pour la commande ' . $eqLogicCmd->getName() . ' mais Force Refresh activé');
          }
          else {
            log::add(__CLASS__, 'debug', $this->getHumanName() . ' le ' . date('d/m/Y', strtotime('-1 day')) . ' : absence de données pour la commande ' . $eqLogicCmd->getName());
          }
        }
      }

      if ($need_refresh == true)
      {
        sleep(rand(5,50));

		$result = $this->connectTeleo();

        if (!is_null($result)) {
           $result = $this->getTeleoData();
		
		   // Last try 
		   if (date('G') >= 22 && $result == 1) {
			   $this->setCache('getTeleoData', 'done');
		   }
        }
        else {
          log::add(__CLASS__, 'warning', $this->getHumanName() . ' Erreur de récupération des données - Abandon');
        }
      }
      else
      {
        if ($this->getCache('getTeleoData') != 'done')
        {
          $this->setCache('getTeleoData', 'done');
          log::add(__CLASS__, 'info', $this->getHumanName() . ' le ' . date('d/m/Y', strtotime('-1 day')) . ' : toutes les données sont à jour - désactivation de la vérification automatique pour aujourd\'hui');
        }
      }
    }

    private function connectTeleo() {
	  log::add(__CLASS__, 'info', $this->getHumanName() . ' Récupération des données ' . " - 1ère étape"); 
	  
	  $dataDirectory = $this->getConfiguration('outputData');
	  if (is_null($dataDirectory)) 
	  {
		 $dataDirectory = '/tmp/teleo';
	  }
	    
	  $dataFile = $dataDirectory . "/historique_jours_litres.csv";
	  
	  if ($this->getConfiguration('connectToVeoliaWebsiteFromThisMachine') == 1) {

		  // Clean old csv files if cron time out happed previously
		  $cmdCleanBash = system::getCmdSudo() . "rm -f " . $dataDirectory . "/*.csv";
		  shell_exec($cmdCleanBash);		
		  
		  log::add(__CLASS__, 'info', $this->getHumanName() . ' 1ère étape d\'authentification Veolia');

		  $veoliaWebsite = $this->getConfiguration('type');
		  $login = $this->getConfiguration('login');
		  $password = $this->getConfiguration('password');
		  $logLevel = log::getLogLevel(__CLASS__);
		  $contractID = $this->getConfiguration('contract_id');
		  
		  // Protect simple/double quotes and $ in password
          $password = preg_replace('/\'/', '\\\'',$password, -1);
		  $password = preg_replace('/\"/', '\\\"',$password, -1);
		  $password = preg_replace('/\$/', '\\\$',$password, -1);
        
		  // Sorry, others Veolia Website than Velia IDF are no more supported
	      if ($veoliaWebsite != 'IDF')
		  {   
			log::add(__CLASS__, 'error', $this->getHumanName() . ' Les sites autres que Veolia IdF ne sont plus supportés par le plugin dû à l\'ajout d\'un Capcha.');
			return null;
		  }
		  
		  $cmdBash = system::getCmdSudo() . ' ' . realpath(dirname(__FILE__) . '/../../resources/get_veolia_data.sh') . ' ' . $veoliaWebsite . ' \'' . $login . '\' "' . $password . '" ' . $dataDirectory . ' ' . $logLevel . ' ' . $contractID;

		  log::add(__CLASS__, 'debug', $this->getHumanName() . ' Commande : ' . $cmdBash);
		  $output = shell_exec($cmdBash);

          $logFile = $dataDirectory . '/teleo_python.log';
          if (file_exists($logFile)) {
		  	copy($logFile, '/var/www/html/log/teleo_python');
          }
		  
		  if (is_null($output))
		  {   
			log::add(__CLASS__, 'error', $this->getHumanName() . ' Erreur de lancement du script : problème de droits d\'exécution - Abandon');
			return null;
		  }
		  
		  if ($output != 1)
		  {   
			log::add(__CLASS__, 'error', $this->getHumanName() . ' Erreur de lancement du script : [ ' . $output . ' ] consulter le log <teleo_python> pour plus d\'info - Abandon');
			return null;
		  }
	  }  
	
	  if (!file_exists($dataFile)) {  

		if ($this->getConfiguration('connectToVeoliaWebsiteFromThisMachine') == 1) {
			$loglevel = 'error';
		}
		else {
			$loglevel = 'warning';
		}
		
		log::add(__CLASS__, $loglevel, $this->getHumanName() . ' Fichier <' . $dataFile . '> non trouvé - Abandon');
		return null;
	  }
	  else 
	  {
		return 1;
	  }
   }

   private function getTeleoData() {
	   
	 $resultStatus = null;
	 
     log::add(__CLASS__, 'info', $this->getHumanName() . ' Récupération des données ' . " - 2ème étape"); 
     
	 $dataDirectory = $this->getConfiguration('outputData');
	 if (empty($dataDirectory)) 
	 {
		 $dataDirectory = '/tmp/teleo';
	 }
	
	 // Clean commande
	 $cmdCleanBash = system::getCmdSudo() . "rm -f " . $dataDirectory . "/historique_jours_litres.csv";
	 
	 // On essai d'importer les indexes manquants précédant la dernière valeur
	 $this->fillMissingIndexes(); 
 
	 // récupère le dernier index
	 $cmdtail = "tail -1 " . $dataDirectory . "/historique_jours_litres.csv";
	 
	 log::add(__CLASS__, 'debug', $this->getHumanName() . ' Commande : ' . $cmdtail);
	 
	 $output = shell_exec($cmdtail);
	 if (is_null($output)) {
		 log::add(__CLASS__, 'error', $this->getHumanName() . ' Erreur dans la commande de lecture du fichier résultat <' . $dataDirectory . '/historique_jours_litres.csv>');
	 }
	 else {
		 
		// Stucture du résultat : 2020-12-17 19:00:00;321134;220;Mesuré
		log::add(__CLASS__, 'debug', $this->getHumanName() . ' Data : ' . $output);
		
		$mesure = explode(";",$output); 
		
		if (count($mesure) < 4) {
			log::add(__CLASS__, 'error', $this->getHumanName() . ' Erreur de structure du fichier <' . $dataDirectory . '/historique_jours_litres.csv>');
			
			// clean data file
			shell_exec($cmdCleanBash);				
				
			return $resultStatus;			
		}
		
		$dateMesure = str_replace('/','-',substr($mesure[0],0,10));
		$valeurMesure = $mesure[1];
		$type = rtrim ($mesure[3]);
		
		if ($type != 'Mesuré' && $type != 'M') {
			log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le dernier relevé de l\'index indique une estimation pas une mesure réelle');
			
			if ($this->getConfiguration('ignoreEstimation') == 1) {
				log::add(__CLASS__, 'warning', $this->getHumanName() . ' La valeur ' . $valeurMesure . ' n\'est pas prise en compte.');
				
				// clean data file
				shell_exec($cmdCleanBash);				
				
				return $resultStatus;
			}				
		}

			
		// check is index value is inf to last index value
		$cmd = $this->getCmd(null, 'index');
		$value = $cmd->execCmd();
		
		if (($value > $valeurMesure) && ($type != 'Mesuré' && $type != 'M')) {
			log::add(__CLASS__, 'warning', $this->getHumanName() . ' La valeur estimée de l\'index (' . $valeurMesure . ') est inférieure à la valeur précédente (' . $value . '). Valeur ignorée.');
			
			// clean data file
			shell_exec($cmdCleanBash);				
				
			return $resultStatus;			
		}
		
		// Check si la date de la dernière mesure est bien celle d'hier
		$dateLastMeasure = date('Y-m-d 00:00:00', strtotime($dateMesure));
		$dateYesterday = date('Y-m-d 00:00:00', strtotime('-1 day'));
		
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' Vérification date dernière mesure : ' . $dateLastMeasure);
		
		if ($dateLastMeasure < $dateYesterday) {
			
			// Check la date de collect de l'index et si elle est antérieure à la date de mesure, on la prend en compte
			$cmd = $this->getCmd(null, 'index');
			$value = $cmd->execCmd();
			$lastCollectDate = $cmd->getValueDate();
	
			log::add(__CLASS__, 'debug', $this->getHumanName() . ' Dernière date de collecte = ' . $lastCollectDate);

		
			if (is_null($lastCollectDate) || ($lastCollectDate <= $dateLastMeasure)) {
				$diffDays = floor(abs(strtotime($dateMesure) - strtotime('today'))/(60 * 60 * 24));
				$this->recordData($valeurMesure,$dateLastMeasure,$diffDays); 					
			}
			else {
				log::add(__CLASS__, 'warning', $this->getHumanName() . ' Récupération des données ' . " le relevé n'est pas encore disponible, la derniere valeur est en date du " . $dateLastMeasure);
			}
		}
		else if ($dateLastMeasure == $dateYesterday) {
			$this->recordData($valeurMesure,$dateLastMeasure);
			$resultStatus = 1;			
		}
		else {
			log::add(__CLASS__, 'warning', $this->getHumanName() . ' Récupération des données ' . " pb dans le relevé, la derniere valeur est en date du " . $dateLastMeasure);
		}
		
		// clean data file
		shell_exec($cmdCleanBash);
	 }
	 
	 return $resultStatus;			
   }
	
   private function fillMissingIndexes() {

		$dataDirectory = $this->getConfiguration('outputData');
		if (empty($dataDirectory)) {
			$dataDirectory = '/tmp/teleo';
		}
		
		$fichier = file($dataDirectory . "/historique_jours_litres.csv");
    
		// Nombre total de ligne du fichier
		$total = count($fichier);

		log::add(__CLASS__, 'info', $this->getHumanName() . ' Vérification et rattrapage éventuel des index non reçus.');
		
		for($i = 1; $i < $total-1; $i++) {
			
			$mesure = explode(";",$fichier[$i]); 
			if (count($mesure) < 4) {
				log::add(__CLASS__, 'error', $this->getHumanName() . ' Erreur de structure du fichier <' . $dataDirectory . '/historique_jours_litres.csv>');
					
				continue;			
			}
		
			$dateMesure = str_replace('/','-',substr($mesure[0],0,10));
			$valeurMesure = $mesure[1];
			$type = rtrim($mesure[3]);
			
			if ($type != 'Mesuré' && $type != 'M') {
				log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le relevé de l\'index à la date du ' . $dateMesure . ' indique une estimation pas une mesure réelle');

				if ($this->getConfiguration('ignoreEstimation') == 1) {
					log::add(__CLASS__, 'warning', $this->getHumanName() . ' La valeur ' . $valeurMesure . ' n\'est pas prise en compte.');
					
					continue;
				}
			}

			$cmd = $this->getCmd(null, 'index');
			$cmdId = $cmd->getId();
			$dateReal =  date('Y-m-d 00:00:00', strtotime($dateMesure));
			$dateReal_oldformat =  date('Y-m-d 23:55:00', strtotime($dateMesure));
			
			$cmdHistory = history::byCmdIdDatetime($cmdId, $dateReal);
			$cmdHistory_oldformat = history::byCmdIdDatetime($cmdId, $dateReal_oldformat);
			
			if (is_object($cmdHistory) && $cmdHistory->getValue() == $valeurMesure) {
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' Mesure en historique - Aucune action : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => Mesure = ' . $valeurMesure);
			}
			else {
				log::add(__CLASS__, 'info', $this->getHumanName() . ' Enregistrement mesure manquante : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => Mesure = ' . $valeurMesure);

				if (is_object($cmdHistory)) {
					history::removes($cmdId, $dateReal, $dateReal);	
				}
				if (is_object($cmdHistory_oldformat)) {
					history::removes($cmdId, $dateReal_oldformat, $dateReal_oldformat);	
				}				
				$cmd->addHistoryValue( $valeurMesure, $dateReal);
              
				$dateYesterday = date('Y-m-d 00:00:00', strtotime(date('Y-m-d 00:00:00', strtotime($dateReal)) . ' -1 day'));
				if ($dateReal > $dateYesterday)
					$lastIndex = history::getStatistique($cmdId, $dateYesterday, $dateYesterday)["last"];
				else
					$lastIndex = history::getStatistique($cmdId, $dateReal, $dateYesterday)["last"];
				
              	if ($lastIndex == 0) {
                  	$lastIndex = $valeurMesure;
                }
              
				$consoDay = $valeurMesure - $lastIndex;
				$cmdConsoDay = $this->getCmd(null, 'consod');
				$cmdId = $cmdConsoDay->getId();
				$cmdHistory = history::byCmdIdDatetime($cmdId, $dateReal);

				log::add(__CLASS__, 'info', $this->getHumanName() . ' Calcul conso day : ' . ' last Index = ' . sprintf('%0.0f', $lastIndex) . ' Date = ' . $dateReal . ' => Mesure = ' . $consoDay);
					
				if (is_object($cmdHistory) && $cmdHistory->getValue() == $cmdConsoDay) {
					log::add(__CLASS__, 'debug', $this->getHumanName() . ' Mesure en historique - Aucune action : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => Mesure = ' . $valeurMesure);
				}
				else {					
					log::add(__CLASS__, 'info', $this->getHumanName() . ' Enregistrement mesure manquante : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => Mesure = ' . $consoDay);

					if (is_object($cmdHistory)) {
						history::removes($cmdId, $dateReal, $dateReal);
					}
					
					$cmdConsoDay->addHistoryValue( $consoDay, $dateReal);
                  
                  	$this->computePrice ($consoDay, $dateReal, false);
				}
				
			}
		}
   }

   public function sortHisto($a, $b)
   {
	if (is_null($a) || is_null($b)) {
	    return 0;
        }
      
        $dateA = $a->getDatetime();
        $dateB = $b->getDatetime();

        if ($dateA == $dateB) {
            return 0;
        }
        elseif ($dateA < $dateB) {
            return -1;
        }
        else {
            return 1;
        }
   }

   private function recomputeQuarterPrices($dateBegin) {
 
        log::add(__CLASS__, 'info', $this->getHumanName() .' Prix recalculés pour le trimestre commençant le ' . $dateBegin);
     
        $dateEnd = date('Y-m-d 23:59:59', strtotime(date('Y-m-d 00:00:00', strtotime('now')) . ' -1 day'));

        $valueUniteconso = $this->getCmd(null, 'histo_cost');
        $coutEnergie = $valueUniteconso->execCmd();

        $cmd = $this->getCmd(null, 'consod');
        $cmdId = $cmd->getId();
        $all = history::all($cmdId, $dateBegin, $dateEnd);

        $cmdToUpdate = $this->getCmd(null, 'cost_day');
        $cmdIdToUpdate = $cmdToUpdate->getId();

     	log::add(__CLASS__, 'debug', $this->getHumanName() .' Date Begin = ' . $dateBegin . ' Date End = ' . $dateEnd . ' count = ' . count($all));
     
        for($i = 0; $i < count($all); $i++) {

          $dateVal = $all[$i]->getDatetime();
          if (!is_null($dateVal)) {
            $valeurMesure = $all[$i]->getValue();
            $price = history::getStatistique($valueUniteconso->getId(), date('Y-m-d 00:00:00', strtotime('2020-01-01')), $dateVal)["last"];
            if (is_null($price)) {
              $price = $coutEnergie;
            }          
            $cout = $valeurMesure * $price / 1000;

            $dateNorm = date('Y-m-d 00:00:00', strtotime($dateVal));
            log::add(__CLASS__, 'debug', $this->getHumanName() .'Date val = ' . $dateVal . ' mesure = ' . $valeurMesure . ' cout = ' . $cout . ' cout unitaire par m3 = ' . $price);
            
            history::removes($cmdIdToUpdate, $dateNorm, $dateNorm);
            $cmdToUpdate->addHistoryValue($cout, $dateNorm);
          }
        }

   }
  
  
   private function computePrice($measure, $dateReal,$lastValue=true) {

		$dateBegin = date('Y-m-d 00:00:00', strtotime('2020-01-01'));
		$unit_Cost = 0;
     
		if (!is_null($dateReal)) {
    		$dateEnd = date('Y-m-d 23:59:59', strtotime($dateReal));
  			$unit_Cost = history::getStatistique($this->getCmd(null, 'histo_cost')->getId(), $dateBegin, $dateEnd)["last"];
    		if (is_null($unit_Cost)) {
     			$unit_Cost = floatval($this->getConfiguration('unit_Cost', 5.27));
   			}
        }
     
     	$cost_day = $measure * $unit_Cost / 1000;
	
		$cmd = $this->getCmd(null, 'cost_day');
		$cmdId = $cmd->getId();
			
		$cmdHistory = history::byCmdIdDatetime($cmdId, $dateReal);
		if (is_object($cmdHistory) && $cmdHistory->getValue() == $cost_day) {
			log::add(__CLASS__, 'debug', $this->getHumanName() . ' Prix en historique - Aucune action : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => price = ' . $cost_day);
		}
		else {
			history::removes($cmdId, $dateReal, $dateReal);
			log::add(__CLASS__, 'info', $this->getHumanName() . ' Enregistrement prix : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => cout = ' . $cost_day . ' (cout unitaire par m3 = ' . $unit_Cost . ')');
			if ($lastValue == true) {
              	$cmd->event($cost_day, $dateReal);
            }
          	else {
                $cmd->addHistoryValue($cost_day, $dateReal);
            }
		}		
		
   }
  
   private function getDateCollectPreviousIndex($diffDay=1) {
	   
	    $cmd = $this->getCmd(null, 'index');
		$cmdId = $cmd->getId();
		
		$dateBegin = date('Y-m-d 00:00:00', strtotime(date("Y") . '-01-01 -1 day'));
		$dateEnd = date('Y-m-d 00:00:00', strtotime(date('Y-m-d 00:00:00', strtotime('now')) . ' -' . ($diffDay+1) . ' day'));		
				
		# Cas spécial dernière valeur de l'année 
		if ($dateBegin > $dateEnd) {
			$dateBegin = date('Y-m-d 00:00:00', strtotime((date("Y") . '-01-01') . ' -' . ($diffDay+1) . ' day'));	
			$value = $cmd->execCmd();
			$lastCollectDate = $cmd->getValueDate();
			
			if ($lastCollectDate < $dateBegin) {
				$dateBegin = $lastCollectDate;
			}

		}
		
		log::add(__CLASS__, 'debug', $this->getHumanName() . ' Commande = ' . $cmdId . ' Récupération historique index entre le ' . $dateBegin . ' et le ' . $dateEnd . ' Diff = ' . $diffDay);
		
		$all = history::all($cmdId, $dateBegin, $dateEnd);
	   	usort($all, array($this, "sortHisto"));   	
		$dateCollectPreviousIndex = count($all) ? $all[count($all) - 1]->getDatetime() : null;
			   	
		if (is_null($dateCollectPreviousIndex)) {
			$dateCollectPreviousIndex = $dateEnd;
			log::add(__CLASS__, 'warning', $this->getHumanName() . ' Aucune valeur de l\'index historisé, date de collecte précédente par défaut = '. $dateCollectPreviousIndex);		
		}
		else {
			log::add(__CLASS__, 'debug', $this->getHumanName() . ' Date de collecte précédente de l\'index = '. $dateCollectPreviousIndex);
		}

		return $dateCollectPreviousIndex;			
   }
   
   private function computeMeasure($cmdName, $dateBegin, $dateEnd) {
		$cmdId = $this->getCmd(null, 'index')->getId();
	   
		$valueMin = history::getStatistique($cmdId, $dateBegin, $dateEnd)["min"];
		$valueMax = history::getStatistique($cmdId, $dateBegin, $dateEnd)["max"];
		
		log::add(__CLASS__, 'debug', $this->getHumanName() . ' Commande = ' . $cmdName . ' Récupération valeur index entre le ' . $dateBegin . ' et le ' . $dateEnd . ' Min = ' . $valueMin . ' et Max = ' . $valueMax);
		
		if (is_null($valueMin) || is_null($valueMax)) {
			$measure = 0;
		}
		else {
			$measure = $valueMax - $valueMin;								
		}	   
	
		return $measure;			
   }
    
   private function recordData($index, $dateLastMeasure, $diffDay=1) {
	  
		$cmdInfos = ['index','consod','consoh','consom','consoa'];
		
		$dateCollectPreviousIndex = $this->getDateCollectPreviousIndex($diffDay);
		$dateReal = date('Y-m-d 00:00:00', strtotime(date('Y-m-d 00:00:00', strtotime('now')) . ' -' . $diffDay . ' day'));	
		
		foreach ($cmdInfos as $cmdName)
		{
            switch($cmdName)
            {		
				case 'index':
					log::add(__CLASS__, 'debug', $this->getHumanName() . '--------------------------');
                    log::add(__CLASS__, 'debug', $this->getHumanName() . ' Commande = ' . $cmdName . ' Valeur du relevé ' . $index . ' à la date du ' . $dateLastMeasure);
								
 					$measure = $index;

					break;

				case 'consod':
					log::add(__CLASS__, 'debug', $this->getHumanName() . '--------------------------');

					$dateBegin = date('Y-m-d 00:00:00', strtotime(date('Y-m-d 00:00:00', strtotime('now')) . ' -' . ($diffDay+1) . ' day'));	
					
					if ($dateCollectPreviousIndex < $dateBegin) {
						
						$diff = (abs(strtotime($dateBegin) - strtotime($dateCollectPreviousIndex))/86400) + 1;
						log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le dernier index collecté date du '. $dateCollectPreviousIndex . '. La consommation quotidienne sera calculée sur ' . $diff . ' jours.');
						
						$dateBegin = $dateCollectPreviousIndex;
					}
				
					$measure = $this->computeMeasure($cmdName,$dateBegin,$dateReal);	
					
                	$this->computePrice ($measure,$dateReal);
                
					break;				

				case 'consoh':
					log::add(__CLASS__, 'debug', $this->getHumanName() . '--------------------------');
					
					$dateBeginPeriod = date('Y-m-d 00:00:00', strtotime('monday this week'));
					$dateBegin = $dateBeginPeriod;
												
					if ($dateLastMeasure < $dateBegin) {
						# Last measure of previous week
						$dateBegin = date('Y-m-d 00:00:00', strtotime('monday this week -1 week -1 day'));
						$dateBeginPeriod = date('Y-m-d 00:00:00', strtotime('monday this week -1 week'));						
					}
					else {
						# New week
						$dateBegin = date('Y-m-d 00:00:00', strtotime('monday this week -1 day'));
					}
					
					if ($dateCollectPreviousIndex < $dateBegin) {
	
						log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le dernier index collecté date du '. $dateCollectPreviousIndex . '. Impossible de calculer la consommation hebomadaire pour ce début de période car la valeur est à cheval sur plusieurs semaines. La valeur est mise à 0.');
						
						$measure = 0;
					}
					else {
						
						$measure = $this->computeMeasure($cmdName,$dateBegin,$dateReal);	
					}
					
					break;				

				case 'consom':
					log::add(__CLASS__, 'debug', $this->getHumanName() . '--------------------------');
					
					$dateBeginPeriod = date('Y-m-d 00:00:00', strtotime('first day of this month'));
					$dateBegin = $dateBeginPeriod;
					
					if ($dateLastMeasure < $dateBegin) {
						# Last measure of previous month
						$dateBegin = date('Y-m-d 00:00:00', strtotime(date('Y-m-d 00:00:00', strtotime('first day of this month -1 month')) . ' -1 day'));
						$dateBeginPeriod = date('Y-m-d 00:00:00', strtotime('first day of this month - 1 month'));						
					}
					else {
						# New month
						$dateBegin = date('Y-m-d 00:00:00', strtotime(date('Y-m-d 00:00:00', strtotime('first day of this month')) . ' -1 day'));
					}
					
					if ($dateCollectPreviousIndex < $dateBegin) {

						log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le dernier index collecté date du '. $dateCollectPreviousIndex . '. Impossible de calculer la consommation mensuelle pour ce début de période car la valeur est à cheval sur plusieurs mois. La valeur est mise à 0.');
						
						$measure = 0;	
					}
					else {
						
						$measure = $this->computeMeasure($cmdName,$dateBegin,$dateReal);	
					}

					break;				

				case 'consoa':
					log::add(__CLASS__, 'debug', $this->getHumanName() . '--------------------------');

					$dateBeginPeriod = date('Y-m-d 00:00:00', strtotime(date("Y") . '-01-01'));
					$dateBegin = $dateBeginPeriod;
					
					if ($dateLastMeasure < $dateBegin) {
						# Last measure of previous year
						$dateBegin = date('Y-m-d 00:00:00', strtotime(date("Y") . '-01-01 -1 year -1 day'));
						$dateBeginPeriod = date('Y-m-d 00:00:00', strtotime(date("Y") . '-01-01 -1 year'));
					}
					else {
						# New year
						$dateBegin = date('Y-m-d 00:00:00', strtotime(date("Y") . '-01-01 -1 day'));
					}
					
					if ($dateCollectPreviousIndex < $dateBegin) {

						log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le dernier index collecté date du '. $dateCollectPreviousIndex . '. Impossible de calculer la consommation annuelle pour ce début de période car la valeur est à cheval sur plusieurs années. La valeur est mise à 0.');
						
						$measure = 0;
					}
					else {

						$measure = $this->computeMeasure($cmdName,$dateBegin,$dateReal);	
					}
					
					break;				
				
			}

			$cmd = $this->getCmd(null, $cmdName);
			$cmdId = $cmd->getId();
			
			$cmdHistory = history::byCmdIdDatetime($cmdId, $dateReal);
			if (is_object($cmdHistory) && $cmdHistory->getValue() == $measure) {
				log::add(__CLASS__, 'info', $this->getHumanName() . ' Mesure en historique - Aucune action : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => Mesure = ' . $measure);
			}
			else {
				# Pour les période Hebdo, Mois et Année on ne garde que la dernière valeur de la période en cours
				if ($cmdName != 'index' && $cmdName != 'consod') {
					log::add(__CLASS__, 'debug', $this->getHumanName() . ' Suppression historique entre le ' . $dateBeginPeriod . ' et le ' . $dateReal);
					if (!is_null($dateBeginPeriod)&& !is_null($dateReal)) {
						history::removes($cmdId, $dateBeginPeriod, $dateReal);				
					}
					
				}
				else {
					history::removes($cmdId, $dateReal, $dateReal);
				}
		
				log::add(__CLASS__, 'info', $this->getHumanName() . ' Enregistrement mesure : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => Mesure = ' . $measure);
				$cmd->event($measure, $dateReal);
			}
		
		}
					
   }

 // Fonction exécutée automatiquement après la mise à jour de l'équipement : passage de 23:55:00 à 00:00:00 pour la date de l'enregistrement des valeurs
   private function NormalizeData($cmdName) {
		$cmd = $this->getCmd(null, $cmdName);
		$cmdId = $cmd->getId();

		$dateBegin = date('Y-m-d 23:55:00', strtotime('2020-01-01 -1 day'));
		$dateEnd = date('Y-m-d 23:55:00', strtotime(date('Y-m-d 23:55:00', strtotime('now')) . ' -1 day'));	

		$all = history::all($cmdId, $dateBegin, $dateEnd);
		usort($all, array($this, "sortHisto"));   	

		$dateVal = count($all) ? $all[0]->getDatetime() : null;
		if (is_null($dateVal) || (date('G:i:s',strtotime($dateVal)) == date('00:00:00'))) {
			return;
		}

        log::add(__CLASS__, 'debug', $this->getHumanName() . ' Cmd = ' . $cmdId . ' Analyse de ' . count($all) . ' historique(s)');
     
		for($i = 0; $i < count($all); $i++) {
			$dateVal = $all[$i]->getDatetime();
			$timemesure = date('G:i:s',strtotime($dateVal)); 
			
			if (!is_null($dateVal) && $timemesure == date('23:55:00')) {
				$valeurMesure = $all[$i]->getValue();
				$dateReal = $dateReal =  date('Y-m-d 00:00:00', strtotime($dateVal));
				
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' Cmd = ' . $cmdId . ' Modification de l\'historique du ' . $dateVal . ' en ' . $dateReal . ' pour la valeur ' . $valeurMesure);
				
				history::removes($cmdId, $dateVal, $dateVal);
				$cmd->addHistoryValue($valeurMesure, $dateReal);
				
			}
			else {
				break;
			}
		}	
   }

	private function str_to_noaccent($str)
    {
        $url = $str;
        $url = preg_replace('#Ç#', 'C', $url);
        $url = preg_replace('#ç#', 'c', $url);
        $url = preg_replace('#è|é|ê|ë#', 'e', $url);
        $url = preg_replace('#È|É|Ê|Ë#', 'E', $url);
        $url = preg_replace('#à|á|â|ã|ä|å#', 'a', $url);
        $url = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $url);
        $url = preg_replace('#ì|í|î|ï#', 'i', $url);
        $url = preg_replace('#Ì|Í|Î|Ï#', 'I', $url);
        $url = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $url);
        $url = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $url);
        $url = preg_replace('#ù|ú|û|ü#', 'u', $url);
        $url = preg_replace('#Ù|Ú|Û|Ü#', 'U', $url);
        $url = preg_replace('#ý|ÿ#', 'y', $url);
        $url = preg_replace('#Ý#', 'Y', $url);

        return ($url);
    }  
  
  
 // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {
		$this->setDisplay('height','332px');
		$this->setDisplay('width', '192px');
		$this->setConfiguration('forceRefresh', 0);
		$this->setConfiguration('ignoreEstimation', 0);	  
		$this->setConfiguration('outputData', '/tmp/teleo');
		$this->setConfiguration('connectToVeoliaWebsiteFromThisMachine', 1);
		$this->setCategory('energy', 1);
		$this->setIsEnable(1);
		$this->setIsVisible(1);
		$this->setConfiguration('widgetTemplate', 1);
		$this->setConfiguration('widgetBGColor', '#74b5e4');	  
    }

 // Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {
      if (empty($this->getConfiguration('login'))) {
        throw new Exception(__('L\'identifiant du compte Véolia doit être renseigné',__FILE__));
      }
      if (empty($this->getConfiguration('password'))) {
        throw new Exception(__('Le mot de passe du compte Véolia doit être renseigné',__FILE__));
      }
      
    }

 // Fonction exécutée automatiquement après la mise à jour de l'équipement
    public function postUpdate() {

		if ($this->getIsEnable() == 1) {

			$cmdInfos = [
					'index' => 'Index',
					'consoa' => 'Conso Annuelle',
					'consom' => 'Conso Mensuelle',
					'consoh' => 'Conso Hebdo',
					'consod' => 'Conso Jour',
					'cost_day' => 'Coût jour',
              		'histo_cost' => 'Coût unitaire'
				];

			foreach ($cmdInfos as $logicalId => $name)
			{
				$cmd = $this->getCmd(null, $logicalId);
				if (!is_object($cmd))
				{
                  log::add(__CLASS__, 'debug', $this->getHumanName() . ' Création commande :'.$logicalId.'/'.$name);
                  $cmd = new TeleoCmd();
                  $cmd->setLogicalId($logicalId);
                  $cmd->setEqLogic_id($this->getId());
                  $cmd->setGeneric_type('CONSUMPTION');
                  $cmd->setIsHistorized(1);
                  $cmd->setDisplay('showStatsOndashboard', 0);
                  $cmd->setDisplay('showStatsOnmobile', 0);
                  $cmd->setTemplate('dashboard','tile');
                  $cmd->setTemplate('mobile','tile');

                  if ($logicalId == 'index') {
                          $cmd->setIsVisible(0);
                  }
	
                  if ($logicalId == 'cost_day') {
                    $unit = '€';
                    $cmd->setConfiguration('historizeMode','none');
                    $cmd->setConfiguration('historizeRound',2);
                    $cmd->setConfiguration('periodicity','day');
                    $cmd->setDisplay('graphType','bar');
                    $cmd->setDisplay('graphStep','1');
                  }
                  else if ($logicalId == 'histo_cost') {
                    $unit = '€/m3';
                    $cmd->setConfiguration('historizeMode','none');
                    $cmd->setIsVisible(0);
                  	}
                  else {
                    	$unit = 'L';
                  }
                  
                  
                  $cmd->setUnite($unit);
                }

                $cmd->setName($name);
                $cmd->setType('info');
                $cmd->setSubType('numeric');
                $cmd->save();
                              
            }

          $cmd = $this->getCmd(null, 'histo_cost');
          if (is_object($cmd)) {
            
            
        	$coutEnergie = $cmd->execCmd();	         
            $dateCollect = $cmd->getCollectDate();
            
            $unit_Cost = floatval($this->getConfiguration('unit_Cost', 5.27));
            $mois_en_cours=date('n');
            $date_mois1_tri = ($mois_en_cours<=3?'january':($mois_en_cours<=6?'april':($mois_en_cours<=9?'july':'october')));
            $trimestre = 'first day of ' . $date_mois1_tri . ' this year';
            $dateBeginPeriod = date('Y-m-d 00:00:00', strtotime($trimestre));
            
            
            if (($coutEnergie != $unit_Cost) || ($dateCollect < $dateBeginPeriod)) {

                $dateNow = date('Y-m-d 00:00:00', strtotime('now'));
                $cmdId = $cmd->getId();
                history::removes($cmdId, $dateBeginPeriod, $dateNow);
                $cmd->event($unit_Cost, $dateBeginPeriod);
                $cmd->addHistoryValue($unit_Cost, $dateBeginPeriod);

                log::add(__CLASS__, 'info', $this->getHumanName() . ' Enregistrement/Mise à jour : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateBeginPeriod . ' => Unit price = ' . $unit_Cost);

                $this->recomputeQuarterPrices($dateBeginPeriod);
              }
          }
          
          $refreshCmd = $this->getCmd(null, 'refresh');
          if (!is_object($refreshCmd)) {

            $refreshCmd = (new TeleoCmd)
              ->setLogicalId('refresh')
              ->setEqLogic_id($this->getId())
              ->setName(__('Rafraîchir', __FILE__))
              ->setType('action')
              ->setSubType('other')
              ->setOrder(0)
              ->save();          
          }			

          
          //$outDir = $this->getConfiguration('outputData') . '/eq_' . $this->str_to_noaccent($this->getName());          
          $outDir = $this->getConfiguration('outputData');
          if(!is_dir($outDir)) {
            if(!mkdir($outDir, 0777, true))  
            {
              throw new Exception(__('Impossible de créer le répertoire destination',__FILE__));
            }    
          }
          else {
            chmod($outDir, 0777);  
          }		  

          $traceDir = $outDir . '/trace';
          if(!is_dir($traceDir)) {
            if(!mkdir($traceDir, 0777, true))  
            {
              throw new Exception(__('Impossible de créer le répertoire de trace',__FILE__));
            }    
          }
          else {
            chmod($traceDir, 0777);  
          }				
		
          if ($this->getConfiguration('forceRefresh', 0) == 1) {
          	$this->pullTeleo();
          }
          
		
		}
		else {
			self::cleanCrons(intval($this->getId()));
		}


    }
    
    public function toHtml($_version = 'dashboard') {
		if ($this->getConfiguration('widgetTemplate') != 1)
		{
			return parent::toHtml($_version);
		}

		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);

		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
			$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
			
            if (substr($cmd->getLogicalId(), 0, 6) === "consoa") {
                $year = date_fr(date('Y', strtotime($cmd->getCollectDate())));
                $replace['#' . $cmd->getLogicalId() . '_name#'] = $year;
            }           
            if (substr($cmd->getLogicalId(), 0, 6) === "consom") {
                $month = date_fr(date('F', strtotime($cmd->getCollectDate())));
              	$year = date('y', strtotime($cmd->getCollectDate()));
                $replace['#' . $cmd->getLogicalId() . '_name#'] = $month;
            } 
            if (substr($cmd->getLogicalId(), 0, 6) === "consoh") {
                $monday = date('d/m', strtotime('monday this week',strtotime($cmd->getCollectDate())));
                $sunday = date('d/m', strtotime('sunday this week',strtotime($cmd->getCollectDate())));
                $week = date_fr(date('W', strtotime($cmd->getCollectDate())));  
              	$replace['#' . $cmd->getLogicalId() . '_name#'] = $monday . '-' . $sunday . ' (' . $week . ')';
            } 
            if (substr($cmd->getLogicalId(), 0, 6) === "consod") {
              $month = date_fr(date('F', strtotime($cmd->getCollectDate())));
              $day = date('j', strtotime($cmd->getCollectDate()));
              $year = date('y', strtotime($cmd->getCollectDate()));
              //$replace['#' . $cmd->getLogicalId() . '_name#'] = $day . ' ' . $month . ' ' . $year;
              $replace['#' . $cmd->getLogicalId() . '_name#'] = $day . ' ' . $month;
            }           

		}

		$replace['#refresh_id#'] = $this->getCmd('action', 'refresh')->getId();
		
		$bgcolor = $this->getConfiguration('widgetBGColor');
		if (empty($bgcolor)) {
			$bgcolor = '#9fe7e7';
			$this->setConfiguration('widgetBGColor', $bgcolor);			
		}
		
		$replace['#BGTeleo#'] = ($this->getConfiguration('widgetTransparent') == 1) ? 'transparent' : $bgcolor;

		$html = template_replace($replace, getTemplate('core', $version, 'teleo.template', __CLASS__));
		cache::set('widgetHtml' . $_version . $this->getId(), $html, 0);
		return $html;
	}

}

class teleoCmd extends cmd {
    /*     * *************************Attributs****************************** */

    /*
      public static $_widgetPossibility = array();
    */

    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

  // Exécution d'une commande
     public function execute($_options = array()) {
		if ($this->getLogicalId() == 'refresh') {
			return $this->getEqLogic()->pullTeleo(true);
		  }
     }

    /*     * **********************Getteur Setteur*************************** */
}