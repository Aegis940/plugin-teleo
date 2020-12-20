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

    /*     * ***********************Methode static*************************** */

  public static function cron()
  {
    $cronMinute = config::byKey('cronMinute', __CLASS__);
    if (!empty($cronMinute) && date('i') != $cronMinute) return;

    $eqLogics = self::byType(__CLASS__, true);

    foreach ($eqLogics as $eqLogic)
    {
      if (date('G') < 4 || date('G') >= 22)
      {
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

    public function pullTeleo()
    {
      $need_refresh = false;

      foreach ($this->getCmd('info') as $eqLogicCmd)
      {
        $eqLogicCmd->execCmd();
        if ($eqLogicCmd->getCollectDate() == date('Y-m-d 23:55:00', strtotime('-1 day')) && $this->getConfiguration('forceRefresh') != 1)
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
           $this->getTeleoData();
        }
        else {
          log::add(__CLASS__, 'info', $this->getHumanName() . ' Erreur connexion - Abandon');
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

    public function connectTeleo()
	{
	  log::add(__CLASS__, 'info', $this->getHumanName() . ' Récupération des données ' . " - 1ère étape"); 
	  
	  $dataDirectory = $this->getConfiguration('outputData');
	  if (is_null($dataDirectory)) 
	  {
		 $dataDirectory = '/var/www/html/tmp/teleo';
	  }
	    
	  $dataFile = $dataDirectory . "/historique_jours_litres.csv";
	  
	  if ($this->getConfiguration('connectToVeoliaWebsiteFromThisMachine') == 1) {

		  log::add(__CLASS__, 'info', $this->getHumanName() . ' 1ère étape d\'authentification Veolia');

		  $veoliaWebsite = $this->getConfiguration('type');
		  if ($veoliaWebsite == "IDF") {
			  $scriptName = "get_veolia_idf_consommation.py";
		  }
		  else 
		  {
			  $scriptName = "get_veolia_other_consommation.py";
		  }

		  $login = $this->getConfiguration('login');
		  $password = $this->getConfiguration('password');
	 
		  $cmdPython = 'python3 /var/www/html/plugins/teleo/resources/' . $scriptName . ' ' . $login . ' ' . $password . ' ' . $dataDirectory;
		  
		  log::add(__CLASS__, 'debug', $this->getHumanName() . ' Commande : ' . $cmdPython);
		  $output = shell_exec($cmdPython);

		  if (is_null($output))
		  {   
			log::add(__CLASS__, 'error', $this->getHumanName() . ' Erreur de lancement du script python - Abandon');
			return null;
		  }
	  }  
	
	  if (!file_exists($dataFile)) {   
		log::add(__CLASS__, 'info', $this->getHumanName() . ' Fichier <' . $dataFile . '> non trouvé - Abandon');
		return null;
	  }
	  else 
	  {
		return 1;
	  }
   }

   public function getTeleoData()
   {
     log::add(__CLASS__, 'info', $this->getHumanName() . ' Récupération des données ' . " - 2ème étape"); 
     
	 $dataDirectory = $this->getConfiguration('outputData');
	 if (empty($dataDirectory)) 
	 {
		 $dataDirectory = '/var/www/html/tmp/teleo';
	 }
	
	 // récupère le dernier index
	 $cmdtail = "tail -1 " . $dataDirectory . "/historique_jours_litres.csv";
	 
	 log::add(__CLASS__, 'debug', $this->getHumanName() . ' Commande : ' . $cmdtail);
	 
	 $output = shell_exec($cmdtail);
	 if (is_null($output)) {
		 log::add(__CLASS__, 'error', $this->getHumanName() . 'Erreur dans la commande de lecture du fichier résultat <' . $dataDirectory . '/historique_jours_litres.csv>');
	 }
	 else {
		 
		// Stucture du résultat : 2020-12-17 19:00:00;321134;220;Mesuré (seuls les deux premiers champs sont utilisés)
		$mesure = explode(";",$output); 
		$dateMesure = substr($mesure[0],0,10);
		$valeurMesure = $mesure[1];
		 
		// Check si la date de la dernière mesure est bien celle d'hier
		$dateLastMeasure = date('Y-m-d', strtotime($dateMesure));
		$dateYesterday = date('Y-m-d', strtotime('-1 day'));
		
        log::add(__CLASS__, 'debug', $this->getHumanName() . ' Vérification date dernière mesure : ' . $dateLastMeasure);
		
		if ($dateLastMeasure < $dateYesterday) {
			log::add(__CLASS__, 'info', $this->getHumanName() . ' Récupération des données ' . " le relevé n'est pas encore disponible, la derniere valeur est en date du " . $dateLastMeasure);
		}
		else {
			$this->recordData($valeurMesure,$dateLastMeasure);    	
		}
		
		// clean data file
		//shell_exec("rm -f " . $dataDirectory . "/historique_jours_litres.csv");
	 }
   }

   public function getDateCollectPreviousIndex() 
   {
	   
	    $cmd = $this->getCmd(null, 'index');
		$cmdId = $cmd->getId();
		
		$dateBegin = date('Y-m-d 23:55:00', strtotime(date("Y") . '-01-01 -1 day'));		
		$dateEnd = date("Y-m-d 23:55:00", strtotime('-2 day'));
		
		$all = history::all($cmdId, $dateBegin, $dateEnd);
		$dateCollectPreviousIndex = count($all) ? $all[count($all) - 1]->getDatetime() : null;

		log::add(__CLASS__, 'debug', $this->getHumanName() . ' Dernière date de collecte de l\'index = '. $dateCollectPreviousIndex);

		return $dateCollectPreviousIndex;			
   }
   
   public function computeMeasure($cmdName, $dateBegin, $dateEnd) 
   {
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
    
   public function recordData($index, $dateLastMeasure) {
	  
		$cmdInfos = ['index','consod','consoh','consom','consoa'];
		
		$dateCollectPreviousIndex = $this->getDateCollectPreviousIndex();
		$dateReal = date("Y-m-d 23:55:00", strtotime('-1 day'));
		
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

					$dateBegin = date('Y-m-d 23:55:00', strtotime('-2 day'));
					
					if ($dateCollectPreviousIndex < $dateBegin) {
						
						$diff = (abs(strtotime($dateBegin) - strtotime($dateCollectPreviousIndex))/86400) + 1;
						log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le dernier index collecté date du '. $dateCollectPreviousIndex . '. La consommation quotidienne sera calculée sur ' . $diff . ' jours.');
						
						$dateBegin = $dateCollectPreviousIndex;
					}
				
					$measure = $this->computeMeasure($cmdName,$dateBegin,$dateReal);	

					break;				

				case 'consoh':
					log::add(__CLASS__, 'debug', $this->getHumanName() . '--------------------------');
					
					$dateBegin = date('Y-m-d 23:55:00', strtotime('monday this week'));
									
					if ($dateLastMeasure < $dateBegin) {
						$dateBegin = date('Y-m-d 23:55:00', strtotime('monday this week -1 week'));				
					}

					if ($dateCollectPreviousIndex < $dateBegin) {
	
						log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le dernier index collecté date du '. $dateCollectPreviousIndex . '. Impossible de calculer la consommation hebomadaire pour aujourdh\'ui car la valeur est à cheval sur plusieurs semaines.');
											
						continue;		
					}
					else {
						
						$measure = $this->computeMeasure($cmdName,$dateBegin,$dateReal);	
					}
					
					break;				

				case 'consom':
					log::add(__CLASS__, 'debug', $this->getHumanName() . '--------------------------');
					
					$dateBegin = date('Y-m-d 23:55:00', strtotime('first day of this month'));
					
					if ($dateLastMeasure < $dateBegin) {
						$dateBegin = date('Y-m-d 23:55:00', strtotime('first day of this month - 1 month'));		
					}

					if ($dateCollectPreviousIndex < $dateBegin) {

						log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le dernier index collecté date du '. $dateCollectPreviousIndex . '. Impossible de calculer la consommation mensuelle pour aujourdh\'ui car la valeur est à cheval sur plusieurs mois.');
											
						continue;		
					}
					else {
						
						$measure = $this->computeMeasure($cmdName,$dateBegin,$dateReal);	
					}

					break;				

				case 'consoa':
					log::add(__CLASS__, 'debug', $this->getHumanName() . '--------------------------');

					$dateBegin = date('Y-m-d 23:55:00', strtotime(date("Y") . '-01-01'));
					
					if ($dateLastMeasure < $dateBegin) {
						$dateBegin = date('Y-m-d 23:55:00', strtotime(date("Y") . '-01-01 -1 year'));					
					}
					
					if ($dateCollectPreviousIndex < $dateBegin) {

						log::add(__CLASS__, 'warning', $this->getHumanName() . ' Le dernier index collecté date du '. $dateCollectPreviousIndex . '. Impossible de calculer la consommation annuelle pour aujourdh\'ui car la valeur est à cheval sur plusieurs années.');
											
						continue;		
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
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' Mesure en historique - Aucune action : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => Mesure = ' . $measure);
			}
			else {
				#Pour les période Hebdo, Mois et Année on ne garde que la dernière valeur
				if ($cmdName != 'index' && $cmdName != 'consod') {
					log::add(__CLASS__, 'debug', $this->getHumanName() . ' Suppression historique entre le ' . $dateBegin . ' et le ' . $dateReal);
					history::removes($cmdId, $dateBegin, $dateReal);				
				}
				
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' Enregistrement mesure : ' . ' Cmd = ' . $cmdId . ' Date = ' . $dateReal . ' => Mesure = ' . $measure);
				$cmd->event($measure, $dateReal);
			}
		
		}
					
   }

 // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {
      $this->setDisplay('height','332px');
      $this->setDisplay('width', '192px');
      $this->setConfiguration('forceRefresh', 0);
	  $this->setConfiguration('outputData', '/var/www/html/tmp/teleo');
	  $this->setConfiguration('connectToVeoliaWebsiteFromThisMachine', 1);
      $this->setCategory('energy', 1);
      $this->setIsEnable(1);
      $this->setIsVisible(1);
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
		
      $cmdInfos = [
			'index' => 'Index',
    		'consoa' => 'Conso Annuelle',
    		'consom' => 'Conso Mensuelle',
            'consoh' => 'Conso Hebdo',
            'consod' => 'Conso Jour'
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
		
        }

        $cmd->setName($name);
        $cmd->setUnite('L');
 
		$cmd->setType('info');
        $cmd->setSubType('numeric');
        $cmd->save();
      }

	  $outDir = $this->getConfiguration('outputData');
	  if(!is_dir($outDir)) {
		if(!mkdir($outDir))  
		{
			throw new Exception(__('Impossible de créer le répertoire destination',__FILE__));
		}    
      }	  
	  
	  if ($this->getIsEnable() == 1) {
			$this->pullTeleo();
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
      }

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
     }

    /*     * **********************Getteur Setteur*************************** */
}