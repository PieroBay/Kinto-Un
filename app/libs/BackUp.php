<?php 

	namespace KintoUn\libs;
	
/**
 * Class who create a dumb from db
 */
class BackUp{
	private $dataBase;
	private $db;
	private $dir;

	/**
	 * Construct class
	 *
	 * @param string $base [DB name]
	 * @param object $db [DB Object]
	 * @param string $dir [folder in files ressources folder]
	 */
	public function __construct($base,$db,$dir="backup"){
		$this->db       = $db;
		$this->dataBase = $base;
		$this->dir      = ROOT.'src/ressources/files/'.$dir.'/';
	}

	/**
	 * Dump DB
	 *
	 * @return void
	 */
	public function dump(){
		$sQuery  = "SHOW tables FROM " . $this->dataBase;
		$sResult = $this->db->query($sQuery);
		$sData   = "
		-- PDO SQL Dump --
		 
		SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";
		 
		--
		-- Database: `$this->dataBase`
		--
		 
		-- --------------------------------------------------------
		";
		 
		while ($aTable = $sResult->fetch(PDO::FETCH_ASSOC)) {
		 
			$sTable     = $aTable['Tables_in_' . $this->dataBase];
			
			$sQuery     = "SHOW CREATE TABLE $sTable";
			
			$sResult2   = $this->db->query($sQuery);
			
			$aTableInfo = $sResult2->fetch(PDO::FETCH_ASSOC);
		 
			$sData     .= "\n\n--
			-- Tabel structur for table `$sTable`
			--\n\n";
			$sData     .= $aTableInfo['Create Table'] . ";\n";
			
			$sData     .= "\n\n--
			-- Data is carried out for table `$sTable`
			--\n\n";
		 
		 
		  $sQuery = "SELECT * FROM $sTable\n";
		 
		  $sResult3 = $this->db->query($sQuery);
		 
		  while ($aRecord = $sResult3->fetch(PDO::FETCH_ASSOC)) {
		 
		    $sData  .= "INSERT INTO $sTable VALUES (";
		    $sRecord = "";
		    foreach( $aRecord as $sField => $sValue ) {
		      $sRecord .= "'$sValue',";
		    }
		    $sData .= substr( $sRecord, 0, -1 );
		    $sData .= ");\n";
		  }
		}

		$fileN['link'] = $this->dir.'backUp_'.$this->dataBase.'_'.date('Y-m-d_H:i:s').'.sql';
		$fileN['name'] = 'backUp_'.$this->dataBase.'_'.date('Y-m-d_H:i:s').'.sql';
		$file = fopen($fileN['link'], 'w+');
		fwrite($file,$sData);
		fclose($file);

		return $fileN;
	}
}