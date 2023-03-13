<?php
	class Connection{
		
		private $con;
		
		
		function __construct($host, $user,$pass,$db) {
			$this->con = mysqli_connect($host,$user,$pass,$db);
			if (mysqli_connect_errno()) die("mysql connection error: " . mysqli_connect_error());
			mysqli_set_charset($this->con,"utf8");
		}
		
		public function query($query){
			$res = mysqli_query($this->con, $query);
			if (!$res){
				echo "<pre>";
				echo "SQL error: " . $this->con->error . "\n";
				echo "------------------------------------------------\n";
				debug_print_backtrace();
				die();
			} else {
				return $res;
			}
		}
		
		public function escape($string, $sophisticated = false){
			if (!$sophisticated) return mysqli_real_escape_string($this->con, $string);
			
			$matches = array();
			preg_match_all('/^\'|[^\\\\]\'/', $string, $matches, PREG_OFFSET_CAPTURE);
			$matches = $matches[0];
			$c = 0;
			foreach ($matches as $m){
				if ($m[1] > 0){
					$string = substr_replace($string, "\\", $m[1]+1+$c, 0);
				} else {
					if (strlen($m[0]) == 1){
						$string = substr_replace($string, "\\", 0, 0);
					} else {
						$string = substr_replace($string, "\\", 1, 0);
					}
				}
				$c++;
			}
			preg_match_all('/^"|[^\\\\]"/', $string, $matches, PREG_OFFSET_CAPTURE);
			$matches = $matches[0];
			$c = 0;
			foreach ($matches as $m){
				if ($m[1] > 0){
					$string = substr_replace($string, "\\", $m[1]+1+$c, 0);
				} else {
					if (strlen($m[0]) == 1){
						$string = substr_replace($string, "\\", 0, 0);
					} else {
						$string = substr_replace($string, "\\", 1, 0);
					}
				}
				$c++;
			}
			return $string;			
		}
		
		public function getArray($query){
			$ret = $this -> query($query);
			$ret = $this -> fetch_assoc($ret);
			return $ret;
		}
		
		public function insert($table, $array, $escape = false){
			
			if ($escape) foreach ($array as $k=>$v) $array[$k] = $this->escape($v);
			
			$query = "insert into " . $table . " (" . implode(",",array_keys($array)) . ") values ('" . implode("','", $array) . "')";
			//echo $query;
			$this -> query($query);
			return $this->con->insert_id;
		}
		
		public function update($table, $array, $where, $equals=""){
			$query = "update " . $table . " set ";
			$uarray = array();
			foreach ($array as $k=>$v) $uarray[] = $k.'="'.$v.'"';
			$query .= implode(', ',$uarray);
			$query .= ' where ';
			if ($equals != ""){
				$query .= $where . " = '" . $equals . "'";
			} else {
				$query .= $where;
			}
			$this -> query($query);
		}
		
		public function numRows($query){
			return mysqli_num_rows($this->query($query));
		}
		
		private function fetch_assoc($stuff){
			$ret = array();
			if ($stuff === FALSE){
				die("sql error: " . $mysqli->error);
				return $ret;
			}
			while ($row = mysqli_fetch_assoc($stuff)) $ret[] = $row;
			return $ret;
		}
		
		public function sqlInfo(){
			return $this->con->server_info;
		}
	}
?>