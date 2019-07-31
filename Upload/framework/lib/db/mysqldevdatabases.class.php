<?php


class mysqldevdatabases {


	private $appname  = "SchuleIntern";

	private $mysqli = null;

	private $query_id;

	private $record;

	private $errdesc;

	private $errno;

	public function connect() {
    		if ($this->mysqli == null) {

      			$this->mysqli = mysqli_connect(
      					'databaseserver.schule-intern.de',
      					'root',
      					'efwgvb3rv3g354gh54gh4',
      					'mysql'
      			);

      			mysqli_set_charset($this->mysqli, "UTF-8");

    		}
    		
    		if(!$this->mysqli) return false;
    		else return true;
    		
    		
  	}

	public function geterrdesc() {
    		$this->error=mysqli_error();
    		return $this->error;
  	}

  	public function geterrno() {
    		$this->errno=mysqli_errno();
    		return $this->errno;
  	}

  	public function query($query_string, $silent = 0) {
    		$this->query_id = mysqli_query($this->mysqli, $query_string);

    		if (!$this->query_id && $silent == 0) {
      			$this->print_error("Invalid SQL: ".$query_string);
    		}
    		return $this->query_id;
  	}

	public function fetch_array($query_id = null) {
		if ($query_id != null) {
			$this->query_id = $query_id;
		}

		$this->record = mysqli_fetch_array($this->query_id);

		return $this->record;
	 }

  	public function free_result($query_id=-1) {
    		if ($query_id != null) {
      			$this->query_id = $query_id;
    		}
    		return @mysqli_free_result($this->query_id);
  	}

  	public function query_first($query_string) {
    		$this->query($query_string);

    		$returnarray = $this->fetch_array($this->query_id);

    		$this->free_result($this->query_id);

    		return $returnarray;
  	}

  	public function num_rows($query_id=-1) {
    		if ($query_id!=-1) {
      			$this->query_id = $query_id;
    		}
    		return mysqli_num_rows($this->query_id);
  	}

  	public function affected_rows() {
    		return mysqli_affected_rows($this->link_id);
  	}

  	public function insert_id() {
    		return mysqli_insert_id($this->mysqli);
  	}


  	/**
  	 * Escaped einen String (gegen SQL Injections)
  	 * @param String $string SQL Abfrage
  	 * // TODO: Umstellung alles auf prepared Statements
  	 */
  	public function escapeString($string) {
  		return mysqli_real_escape_string($this->mysqli, $string);
  	}

	// TODO: Error nur Anzeigen, wenn im Debugging Modus!
  	public function print_error($msg) {
			$this->errdesc = mysqli_error($this->mysqli);
    		$this->errno = mysqli_errno($this->mysqli);

    		$message="Database error in $this->appname: $msg\n<br>";
    		$message.="mysql error: $this->errdesc\n<br>";
    		$message.="mysql error number: $this->errno\n<br>";
    		$message.="Date: ".date("d.m.Y @ H:i")."\n<br>";
    		$message.="Script: ".getenv("REQUEST_URI")."\n<br>";
    		$message.="Referer: ".getenv("HTTP_REFERER")."\n<br><br>";

			//$db->query("INSERT INTO bazar_errors (error) values('".$message."')");


    		echo("$message");

      		echo("<pre>");
      		debug_print_backtrace();
      		echo("</pre>");
      		die("");
    	}
}
?>