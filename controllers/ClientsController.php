<?php 
/**
 * Clients Page Controller
 * @category  Controller
 */
class ClientsController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "clients";
	}
	/**
     * List page records
     * @param $fieldname (filter record by a field) 
     * @param $fieldvalue (filter field value)
     * @return BaseView
     */
	function index($fieldname = null , $fieldvalue = null){
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array("id", 
			"document_type", 
			"document_id", 
			"name", 
			"email", 
			"phone", 
			"last_purchase", 
			"total_purchases", 
			"total_paid", 
			"created_at", 
			"updated_at", 
			"deleted_at", 
			"balance");
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if(!empty($request->search)){
			$text = trim($request->search); 
			$search_condition = "(
				clients.id LIKE ? OR 
				clients.document_type LIKE ? OR 
				clients.document_id LIKE ? OR 
				clients.name LIKE ? OR 
				clients.email LIKE ? OR 
				clients.phone LIKE ? OR 
				clients.last_purchase LIKE ? OR 
				clients.total_purchases LIKE ? OR 
				clients.total_paid LIKE ? OR 
				clients.created_at LIKE ? OR 
				clients.updated_at LIKE ? OR 
				clients.deleted_at LIKE ? OR 
				clients.balance LIKE ?
			)";
			$search_params = array(
				"%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%"
			);
			//setting search conditions
			$db->where($search_condition, $search_params);
			 //template to use when ajax search
			$this->view->search_template = "clients/search.php";
		}
		if(!empty($request->orderby)){
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		}
		else{
			$db->orderBy("clients.id", ORDER_TYPE);
		}
		if($fieldname){
			$db->where($fieldname , $fieldvalue); //filter by a single field name
		}
		$tc = $db->withTotalCount();
		$records = $db->get($tablename, $pagination, $fields);
		$records_count = count($records);
		$total_records = intval($tc->totalCount);
		$page_limit = $pagination[1];
		$total_pages = ceil($total_records / $page_limit);
		$data = new stdClass;
		$data->records = $records;
		$data->record_count = $records_count;
		$data->total_records = $total_records;
		$data->total_page = $total_pages;
		if($db->getLastError()){
			$this->set_page_error();
		}
		$page_title = $this->view->page_title = "Clients";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "portrait";
		$this->render_view("clients/list.php", $data); //render the full page
	}
	/**
     * View record detail 
	 * @param $rec_id (select record by table primary key) 
     * @param $value value (select record by value of field name(rec_id))
     * @return BaseView
     */
	function view($rec_id = null, $value = null){
		$request = $this->request;
		$db = $this->GetModel();
		$rec_id = $this->rec_id = urldecode($rec_id);
		$tablename = $this->tablename;
		$fields = array("id", 
			"document_type", 
			"document_id", 
			"name", 
			"email", 
			"phone", 
			"last_purchase", 
			"total_purchases", 
			"total_paid", 
			"created_at", 
			"updated_at", 
			"deleted_at", 
			"balance");
		if($value){
			$db->where($rec_id, urldecode($value)); //select record based on field name
		}
		else{
			$db->where("clients.id", $rec_id);; //select record based on primary key
		}
		$record = $db->getOne($tablename, $fields );
		if($record){
			$page_title = $this->view->page_title = "View  Clients";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "portrait";
		}
		else{
			if($db->getLastError()){
				$this->set_page_error();
			}
			else{
				$this->set_page_error("No record found");
			}
		}
		return $this->render_view("clients/view.php", $record);
	}
	/**
     * Insert new record to the database table
	 * @param $formdata array() from $_POST
     * @return BaseView
     */
	function add($formdata = null){
		if($formdata){
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$request = $this->request;
			//fillable fields
			$fields = $this->fields = array("document_type","document_id","name","email","phone","last_purchase","total_purchases","total_paid","created_at","updated_at","deleted_at","balance");
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'document_type' => 'required',
				'document_id' => 'required|numeric',
				'name' => 'required',
				'email' => 'required|valid_email',
				'phone' => 'required',
				'last_purchase' => 'required',
				'total_purchases' => 'required|numeric',
				'total_paid' => 'required|numeric',
				'created_at' => 'required',
				'updated_at' => 'required',
				'deleted_at' => 'required',
				'balance' => 'required|numeric',
			);
			$this->sanitize_array = array(
				'document_type' => 'sanitize_string',
				'document_id' => 'sanitize_string',
				'name' => 'sanitize_string',
				'email' => 'sanitize_string',
				'phone' => 'sanitize_string',
				'last_purchase' => 'sanitize_string',
				'total_purchases' => 'sanitize_string',
				'total_paid' => 'sanitize_string',
				'created_at' => 'sanitize_string',
				'updated_at' => 'sanitize_string',
				'deleted_at' => 'sanitize_string',
				'balance' => 'sanitize_string',
			);
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if($rec_id){
					$this->set_flash_msg("Record added successfully", "success");
					return	$this->redirect("clients");
				}
				else{
					$this->set_page_error();
				}
			}
		}
		$page_title = $this->view->page_title = "Add New Clients";
		$this->render_view("clients/add.php");
	}
	/**
     * Update table record with formdata
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function edit($rec_id = null, $formdata = null){
		$request = $this->request;
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		 //editable fields
		$fields = $this->fields = array("id","document_type","document_id","name","email","phone","last_purchase","total_purchases","total_paid","created_at","updated_at","deleted_at","balance");
		if($formdata){
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'document_type' => 'required',
				'document_id' => 'required|numeric',
				'name' => 'required',
				'email' => 'required|valid_email',
				'phone' => 'required',
				'last_purchase' => 'required',
				'total_purchases' => 'required|numeric',
				'total_paid' => 'required|numeric',
				'created_at' => 'required',
				'updated_at' => 'required',
				'deleted_at' => 'required',
				'balance' => 'required|numeric',
			);
			$this->sanitize_array = array(
				'document_type' => 'sanitize_string',
				'document_id' => 'sanitize_string',
				'name' => 'sanitize_string',
				'email' => 'sanitize_string',
				'phone' => 'sanitize_string',
				'last_purchase' => 'sanitize_string',
				'total_purchases' => 'sanitize_string',
				'total_paid' => 'sanitize_string',
				'created_at' => 'sanitize_string',
				'updated_at' => 'sanitize_string',
				'deleted_at' => 'sanitize_string',
				'balance' => 'sanitize_string',
			);
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("clients.id", $rec_id);;
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount(); //number of affected rows. 0 = no record field updated
				if($bool && $numRows){
					$this->set_flash_msg("Record updated successfully", "success");
					return $this->redirect("clients");
				}
				else{
					if($db->getLastError()){
						$this->set_page_error();
					}
					elseif(!$numRows){
						//not an error, but no record was updated
						$page_error = "No record updated";
						$this->set_page_error($page_error);
						$this->set_flash_msg($page_error, "warning");
						return	$this->redirect("clients");
					}
				}
			}
		}
		$db->where("clients.id", $rec_id);;
		$data = $db->getOne($tablename, $fields);
		$page_title = $this->view->page_title = "Edit  Clients";
		if(!$data){
			$this->set_page_error();
		}
		return $this->render_view("clients/edit.php", $data);
	}
	/**
     * Update single field
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function editfield($rec_id = null, $formdata = null){
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		//editable fields
		$fields = $this->fields = array("id","document_type","document_id","name","email","phone","last_purchase","total_purchases","total_paid","created_at","updated_at","deleted_at","balance");
		$page_error = null;
		if($formdata){
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'document_type' => 'required',
				'document_id' => 'required|numeric',
				'name' => 'required',
				'email' => 'required|valid_email',
				'phone' => 'required',
				'last_purchase' => 'required',
				'total_purchases' => 'required|numeric',
				'total_paid' => 'required|numeric',
				'created_at' => 'required',
				'updated_at' => 'required',
				'deleted_at' => 'required',
				'balance' => 'required|numeric',
			);
			$this->sanitize_array = array(
				'document_type' => 'sanitize_string',
				'document_id' => 'sanitize_string',
				'name' => 'sanitize_string',
				'email' => 'sanitize_string',
				'phone' => 'sanitize_string',
				'last_purchase' => 'sanitize_string',
				'total_purchases' => 'sanitize_string',
				'total_paid' => 'sanitize_string',
				'created_at' => 'sanitize_string',
				'updated_at' => 'sanitize_string',
				'deleted_at' => 'sanitize_string',
				'balance' => 'sanitize_string',
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("clients.id", $rec_id);;
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount();
				if($bool && $numRows){
					return render_json(
						array(
							'num_rows' =>$numRows,
							'rec_id' =>$rec_id,
						)
					);
				}
				else{
					if($db->getLastError()){
						$page_error = $db->getLastError();
					}
					elseif(!$numRows){
						$page_error = "No record updated";
					}
					render_error($page_error);
				}
			}
			else{
				render_error($this->view->page_error);
			}
		}
		return null;
	}
	/**
     * Delete record from the database
	 * Support multi delete by separating record id by comma.
     * @return BaseView
     */
	function delete($rec_id = null){
		Csrf::cross_check();
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$this->rec_id = $rec_id;
		//form multiple delete, split record id separated by comma into array
		$arr_rec_id = array_map('trim', explode(",", $rec_id));
		$db->where("clients.id", $arr_rec_id, "in");
		$bool = $db->delete($tablename);
		if($bool){
			$this->set_flash_msg("Record deleted successfully", "success");
		}
		elseif($db->getLastError()){
			$page_error = $db->getLastError();
			$this->set_flash_msg($page_error, "danger");
		}
		return	$this->redirect("clients");
	}
}
