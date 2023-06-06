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

class nasaapi extends eqLogic {
  /*     * *************************Attributs****************************** */
  public function getAPOD() {
	  log::add(__CLASS__,"debug", "Start getAPOD");
	  // Clé API de la NASA
	  $api_key = $this->getConfiguration('param1');
	  // URL de l'API de la NASA
	  $api_url = "https://api.nasa.gov/planetary/apod?api_key=$api_key";
	  // Récupération des données de l'API
	  $response = file_get_contents($api_url);
	  $data = json_decode($response, true);
	  // Récupération de l'explication de la photo
	  $explication = $data['explanation'];
	  $url = $data['url'];
	  $titre = $data['title'];
	  
	  $imageInfo = getimagesize($url);
	  $width = $imageInfo[0]/4;
      $height = $imageInfo[1]/4;
	  //log::add(__CLASS__,"debug", "taille de l'image: width = $width , height = $height");
	  
	  log::add(__CLASS__,"debug", "Update des commandes ...");
	  $this->downloadImage($url, __DIR__ ."/../../data/image");
      $this->checkAndUpdateCmd('explanation', $explication); // Met à jour la commande "explication" avec l'explication de la photo
      $this->checkAndUpdateCmd('url', $url); // Met à jour la commande "url" avec l'URL de la photo
      $this->checkAndUpdateCmd('title', $titre); // Met à jour la commande "titre" avec le titre de la photo
	  $this->checkAndUpdateCmd('width', $width); // Met à jour la commande "width" avec la largeur de la photo
	  $this->checkAndUpdateCmd('height', $height); // Met à jour la commande avec la hauteur de la photo
	  log::add(__CLASS__,"debug", "Update des commandes réussi: explanation = $explication, url = $url, title = $titre, width = $width, height = $height");
	  
	  
	  log::add(__CLASS__,"debug", "End getAPOD");
	  
	  
    }
  public function downloadImage($url, $destinationPath) {
	  $filename = basename($url); // Récupère le nom du fichier à partir de l'URL
	  $destinationFile = $destinationPath . '/' . $filename; // Chemin complet du fichier de destination
	  
      $imageContent = file_get_contents($url); // Télécharge le contenu de l'image à partir de l'URL
      if ($imageContent === false) {
        log::add(__CLASS__, 'debug', "Unable to download image from URL: $url");
        return false;
      }

      $imageSaved = file_put_contents($destinationFile, $imageContent); // Sauvegarde le contenu de l'image localement
      if ($imageSaved === false) {
        log::add(__CLASS__, 'debug', "Unable to save image to destination: $destinationFile");
        return false;
      }

      log::add(__CLASS__, 'debug', "Image downloaded and saved successfully: $destinationFile");
      $this->checkAndUpdateCmd("image", $destinationFile);
    }

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */

  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {}
  */

  
  // Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly() {
	  /*foreach (self::byType(__CLASS__,true) as $eqLogic) {
    $eqLogic->getAPOD();
	  }*/
  }
  

  
  // Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily() {
	  foreach (self::byType(__CLASS__,true) as $eqLogic) {
    $eqLogic->getAPOD();
	  }
  }

  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {
	  $info = $this->getCmd(null, 'explanation');
	  if (!is_object($info)) {
		  $info = new nasaapiCmd();
		  $info->setName(__('Explication', __FILE__));
	  }
	  $info->setLogicalId('explanation');
	  $info->setEqLogic_id($this->getId());
	  $info->setType('info');
	  $info->setSubType('string');
	  $info->save();
	  
	  $refresh = $this->getCmd(null, 'refresh');
	  if (!is_object($refresh)) {
		  $refresh = new nasaapiCmd();
		  $refresh->setName(__('Rafraichir', __FILE__));
	  }
	  $refresh->setEqLogic_id($this->getId());
	  $refresh->setLogicalId('refresh');
	  $refresh->setType('action');
	  $refresh->setSubType('other');
	  $refresh->save();
	  
	  $title = $this->getCmd(null, 'title');
	  if (!is_object($title)) {
		  $title = new nasaapiCmd();
		  $title->setName(__('Titre', __FILE__));
	  }
	  $title->setEqLogic_id($this->getId());
	  $title->setLogicalId('title');
	  $title->setType('info');
	  $title->setSubType('string');
	  $title->save();
	  
	  $urldelimage = $this->getCmd(null, 'url');
	  if (!is_object($urldelimage)) {
		  $urldelimage = new nasaapiCmd();
		  $urldelimage->setName(__('URL', __FILE__));
	  }
	  $urldelimage->setEqLogic_id($this->getId());
	  $urldelimage->setLogicalId('url');
	  $urldelimage->setType('info');
	  $urldelimage->setSubType('string');
	  $urldelimage->save();
	  
	  $image = $this->getCmd(null, 'image');
	  if (!is_object($image)) {
		  $image = new nasaapiCmd();
		  $image->setName(__('Image', __FILE__));
	  }
	  $image->setEqLogic_id($this->getId());
	  $image->setLogicalId('image');
	  $image->setType('info');
	  $image->setSubType('string');
	  $image->save();
	  
	  $width = $this->getCmd(null, 'width');
	  if (!is_object($width)) {
		  $width = new nasaapiCmd();
		  $width->setName(__('width', __FILE__));
	  }
	  $width->setEqLogic_id($this->getId());
	  $width->setLogicalId('width');
	  $width->setType('info');
	  $width->setSubType('numeric');
	  $width->save();
	  
	  $height = $this->getCmd(null, 'height');
	  if (!is_object($height)) {
		  $height = new nasaapiCmd();
		  $height->setName(__('height', __FILE__));
	  }
	  $height->setEqLogic_id($this->getId());
	  $height->setLogicalId('height');
	  $height->setType('info');
	  $height->setSubType('numeric');
	  $height->save();
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  /*
  * Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard') {}
  */

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */

  /*     * **********************Getteur Setteur*************************** */
  public function toHtml($_version = 'dashboard') {
	  $replace = $this->preToHtml($_version);
      if (!is_array($replace)) {
          return $replace;
      }
      $version = jeedom::versionAlias($_version);
	  
      $url = $this->getCmd('info', 'url');
	  $replace['#url#'] = (is_object($url)) ? $url->execCmd() : "";
	  
      $title = $this->getCmd('info','title');
	  $replace['#title#'] = (is_object($title)) ? $title->execCmd() : "";
	  
      $explanation = $this->getCmd('info','explanation');
	  $replace['#explanation#'] = (is_object($explanation)) ? $explanation->execCmd() : "";
	  
	  $image = $this->getCmd('info','image');
	  $replace['#image#'] = (is_object($image)) ? $image->execCmd() : "";
	  
	  $height = $this->getCmd('info','height');
	  $replace['#height#'] = (is_object($height)) ? $height->execCmd() : "";
	  
	  $width = $this->getCmd('info','width');
	  $replace['#width#'] = (is_object($width)) ? $width->execCmd() : "";
      


      if ($version == 'v4') {
          $replace['#background#'] = $this->getDisplay('background', 'dashboard');
      }
      return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'eqLogic', 'nasaapi')));//  retourne notre template qui se nomme eqlogic pour le widget	  

    }
}

class nasaapiCmd extends cmd {
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array()) {
	  $eqlogic = $this->getEqLogic(); //récupère l'éqlogic de la commande $this
	  switch ($this->getLogicalId()) { //vérifie le logicalid de la commande
	    case 'refresh': // LogicalId de la commande rafraîchir que l’on a créé dans la méthode Postsave de la classe vdm .
		log::add(__CLASS__,"debug", "Case refresh");
		$eqlogic->getAPOD(); // Récupère les données de l'API de la NASA
		
		
	    }
	}

		 

  /*     * **********************Getteur Setteur*************************** */

}
