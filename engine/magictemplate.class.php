<?php
	class MagicTemplate{
		private $result = "";
		private $left = "{{";
		private $right = "}}";
		
		function __construct($a = ""){
			if (is_string($a)) $this->result=$a;
		}
		
		function setLeft($a="{{"){$left=$a;}
		function setRight($a="}}"){$right=$a;}
		
		
		public function __invoke(){
			if (func_num_args() == 0) echo $this->result;
			$z = func_get_arg(0);
			switch (gettype($z)){
				case "string":
					$this->result = $z;
					break;
				case "array":
					foreach ($z as $k => $v) $this->result = str_ireplace($this->left.$k.$this->right,$v,$this->result);
					break;
				default:
					die("MagicTemplate error: unknown parameter");
			}
		}
		
		public function cleared(){
			return preg_replace("#".$this->left.".*?".$this->right."#", "", $this->result);
		}
		
		public function __toString(){
			return $this->result;
		}
	}
?>