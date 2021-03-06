<?php
 	require_once("Rest.inc.php");

	class API extends REST {

		public $data = "";

		const DB_SERVER = "localhost";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "flydream_works_db";

		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}

		/*
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}

		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404); // If the method not exist with in this class "Page not found".
		}

		private function customers(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM customers c order by c.customerNumber desc";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}

		private function events(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT * FROM events e order by e.event_id desc";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}


		private function getorderslist(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT * FROM orders o,events e, customers c where o.event_id = e.event_id and o.cust_id = c.cust_id order by o.order_num desc";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function Logindemo(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$uname =  $this->_request['uname'];
			$password =   $this->_request['password'];
			$query="SELECT user_name FROM admin_det where user_name = $uname and password=$password";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('nkmk',204);	// If no records "No Content" status
		}


		private function CustomerLogin(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$email =  $this->_request['email'];
			$password =   $this->_request['password'];
			$query="SELECT cust_id, cust_name, email_id, organization_name FROM customers where email_id = $email and password=$password";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('nkmk',204);	// If no records "No Content" status
		}


		private function customer(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){
				$query="SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM customers c where c.customerNumber=$id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();
					$this->response($this->json($result), 200); // send user details
				}
			}
			$this->response('',204);	// If no records "No Content" status
		}

		private function insertCustomer(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			$customer = json_decode(file_get_contents("php://input"),true);
			$column_names = array('cust_name','password','email_id','organization_name');
			$keys = array_keys($customer);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the customer received. If blank insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = $customer[$desired_key];
				}
				$columns = $columns.$desired_key.',';
				$values = $values."'".$$desired_key."',";
			}
			$query = "INSERT INTO customers(".trim($columns,',').") VALUES(".trim($values,',').")";
			if(!empty($customer)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Customer Created Successfully.", "data" => $customer);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	//"No Content" status
		}

		private function insertEvents(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$events = json_decode(file_get_contents("php://input"),true);
			$column_names = array('event_name', 'event_desc','PriceDesc','Contact', 'created_by', 'created_date');
			$keys = array_keys($events);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the events received. If blank insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = $events[$desired_key];
				}
				$columns = $columns.$desired_key.',';
				$values = $values."'".$$desired_key."',";
			}
			$query = "INSERT INTO events(".trim($columns,',').") VALUES(".trim($values,',').")";
			if(!empty($events)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "event Created Successfully.", "data" => $events);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	//"No Content" status
		}

private function insertorders(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			$events = json_decode(file_get_contents("php://input"),true);
			$column_names = array('event_id','event_date','event_time','event_place','cust_id','city','pincode');
			$keys = array_keys($events);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the events received. If blank insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = $events[$desired_key];
				}
				$columns = $columns.$desired_key.',';
				$values = $values."'".$$desired_key."',";
			}
			$query = "INSERT INTO orders(".trim($columns,',').") VALUES(".trim($values,',').")";
			if(!empty($events)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "event Created Successfully.", "data" => $events);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	//"No Content" status
		}
		private function updateStatus()
		{
		if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$order_num =  $this->_request['ordernumber'];
      $status = $this->_request['status'];
		$query = "update orders set approved = $status where order_num = $order_num";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "order ".$order_num." Updated Successfully.");
				$this->response($this->json($success),200);
		}


		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}
	// Initiiate Library
	$api = new API;
	$api->processApi();
?>
