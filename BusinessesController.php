<?php

Yii::import("application.extensions.bmptojpg.Bmptojpgconvert");
Yii::import('application.models.Users');
Yii::import('application.extensions.treeview.ITreeManager');
Yii::import('application.extensions.treeview.MySQL');
Yii::import('application.extensions.treeview.TreeManagerLocation');
Yii::import('application.extensions.treeview.ManagementTree');

/*commented by aruna on 14th march 2017*/
	/*require __DIR__ .'/DeviceDetector/DeviceDetector.php';

    use DeviceDetector\DeviceDetector;
	use DeviceDetector\Parser\Device\DeviceParserAbstract;*/
/*commented  by aruna ends here*/


class BusinessesController extends Controller {

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
		            /*code updated by aruna on 29th may 2017*/

            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array('index', 'view','show_business','offers','Questions','reviews','media','expertise','social','location','contact','similarbusinesses','taxregistrations',),
                'users' => array('*'),
            ),
			
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('AutocompleteAdmin','AutocompleteEmployee','Searchbusiness','Adminsearch','getMylokaladdress','Businesslist','Createbusiness','Getroleobject','Business_Location','BusLocStructure','create', 'update', 'business_management', 'Edit_business','error_business','updatebusiness','updategeneralcontact','updatekeycontact','updatetaxregistrations','deletetax','updatesocial','deletesocial','addsocial','updatelocation','editlocation','employee','addemployee','activate','deactivatedemployee','empdeactivate','finance','customers','intellegence','priviliges','transactions','updates','views','favorites','shares','mapView','MapviewAdmin','GetBusName','Categoryname','ChildBusinessNew','fetchCategoryTreeList','createinvite','manageinvite','add_BusinessEmployee','get_BusinessEmployeeDetails','view','Sharemapview','Favmapview','Viewsmapview','Updatesmapview','InvitationStatus','activate_BusinessEmployee','feedback','getproposedDate','userprivacy','save_Privacy','othersview','othersviewPrivacy','Messages','Adminmessages','sales','adminpriviliges','Businessnamelist','Usernamelist','Businessusernamelist','Businessemaillist','Useremaillist','Businessphonelist','Userphonelist', 'Onprocessing', 'Business_Categories', 'business_LocationList', 'Contextual_intelligence', 'checkBusinessStatus', 'edit_business_name_desc','Priviledge','Assign_privilege','Employees','user_Suggestion','autocomplete','add_BusinessUser','check_UserExist','Summary','business_location_category_role_based','get_BusinessUsersForRoles','business_location_category_menu','get_UserRoles_for_all','get_Assigned_Business_Users','update_Business_roles','get_BusinessUserDetails','Summary','Business_history','business_expertise','getMoreBusinessDetails','getBusinessAsset','getAllBusinessAsset','addBusinessAsset','Terms','getEmployeePrivilege','getEmployeeOtherLocation','CheckBusinessName','location_name_unique_check','assignprivilege','getcategorybasedonrole','getuserbasedonrole','checkbusinessuserexist','authentication','businessnamealreadyexist','Business_approval','Approval','getcategoryanduserbasedrole','copy_create_business','assignPrivilegeForUser','assignPrivilegeCatForUser','autoSuggestionSearch','autoSuggestionApprovedBy','autoSuggestionEmployee','getMoreBusinessCats','getBusDesc'),
				  /*code updated by aruna on 29th may 2017 ends here*/

				
				'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function moduleStatus() {
        $sql2 = "SELECT * FROM  ML_Menu_Sections WHERE MS_Name='Administrator'";
        $data2 = Yii::app()->db->createCommand($sql2)->queryRow();
        if ($data2['MS_audit_status'] == 1)
            return TRUE;
        else
            return FALSE;
    }

    public function getAdminName($adminId) {
        $sql1 = "SELECT * FROM ML_Users WHERE USR_ID=" . $adminId;
        $data1 = Yii::app()->db->createCommand($sql1)->queryRow();
        if (!empty($data1))
            return $data1['USR_Screen_Name'];
    }

    public function actionCreate() {
//        echo '<pre>';print_r($_POST);exit;
        $businessModel = new Businesses;
        /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
        $locations = UserLocations::model()->findAll(array('condition' => 'LCN_Created_By="' . Yii::app()->user->id . '"', 'order' => 'LCN_IsDefault desc'));

        $userID = Yii::app()->user->id;
        $act = 'Active';


        $obj_id = Objects::model()->findByAttributes(array('BJ_Name' => 'Business'));
        $auth = Yii::app()->authorization;
        $edit_access = $auth->canEdit($userID, $obj_id->BJ_ID, 1, 1, 0, 0);
        $add_access = $auth->canCreate($userID, $obj_id->BJ_ID, 1, 1, 0, 0);
        $delete_access = $auth->canDelete($userID, $obj_id->BJ_ID, 1, 1, 0, 0);
        $approve_access = $auth->canApprove($userID, $obj_id->BJ_ID, 1, 1, 0, 0);
        $approve_access = $auth->checkAdminRights($userID);



        $locationModel = new UserLocations('save');
        $businessModel = new Businesses('save');
        $phoneModel = new UserPhones; 
        $emailModel = new UserEmails;
        $this->performAjaxValidation(array($locationModel, $businessModel,$phoneModel,$emailModel));

        $slug = '';

        if ((isset($_POST['business_save']) || isset($_POST['createBusiness_draft']) || isset($_POST['createBusiness_approval'])) && isset($_POST['Businesses']) && isset($_POST['UserLocations']) && isset($_POST['UserPhones']) && isset($_POST['UserEmails'])) {

            /** multiple model validation --start* */
            $businessModel->attributes = $_POST['Businesses'];
            if (isset($_POST['QKPQuickpoll']['categories'])) {
                foreach ($_POST['QKPQuickpoll']['categories'] as $category) {
                    $businessModel->categories = $category;
                }
            } else {
                if (!isset($_POST['categories'])) {

                    $businessModel->categories = '';
                } else {
                    $businessModel->categories = $_POST['categories'];
                }
            }

                $locationModel->attributes = $_POST['UserLocations'];
                $country_name_id = Countries::model()->find('CNT_ISO2=:CNT_ISO2', array(':CNT_ISO2' => $_POST['UserLocations']['LCN_Country_id']));
                $locationModel->LCN_Country_id = $country_name_id['CNT_Code'];
				
                $emailModel->attributes = $_POST['UserEmails'];  
                $phoneModel->attributes = $_POST['UserPhones'];
                $validEmail = $emailModel->validate();
                $validPhone = $phoneModel->validate();
                $valid = $businessModel->validate();
                $valid = $locationModel->validate() && $valid && $validEmail  && $validPhone;
            /** multiple model validation --end**/     
                

            if ($valid) {
                $func = new Functions();
                $rand_id = $func->getBusinessRandomNumber();

                /** roles for business module * */
                $modelUserRoles = Roles::model()->findAllByAttributes(array('MLR_Name' => array('User Business Administrator', 'User Location Administrator', 'User Category Administrator', 'User Customer Manager')));
                /**/
                $criteria = new CDbCriteria();
                $criteria->condition = "SLOB_REF_OBJ_TYPE = 11";
                $criteria->order = 'SLOB_ID DESC';
                $lastbackup = SiteLOBS::model()->find($criteria);

                $connection = Yii::app()->db;
                $transaction = $connection->beginTransaction();
                try {

                    $businessModel = new Businesses('save');
                    $businessModel = $businessModel->saveBusinessData($_POST, $rand_id, $slug, $approve_access);
                    if ($businessModel != true) {
                        throw new Exception('error while saving business data.');
                    }
                    $business_id = $connection->getLastInsertID();



                    if (!empty($lastbackup)) {
                        $businessTermsModel = new BusinessTermsLog;
                        $businessTermsModel = $businessTermsModel->saveBusinessTermsData($_POST, $userID, $lastbackup, $business_id);

                        if ($businessTermsModel != true) {
                            throw new Exception('error while saving business terms data.');
                        }
                    }



                    $locationModel = $locationModel->saveLocationDataForBusiness($_POST, $business_id);
                    if ($locationModel['status'] != 1) {

                        throw new Exception('error while saving location data.');
                    }
                    $location_id = $locationModel['location_id'];



                    $phoneModel = new UserPhones;
                    $phoneModel = $phoneModel->savePhoneDataForBusiness($userID, $_POST, $location_id);
                    if ($phoneModel != true) {
                        throw new Exception('error while saving user phone data.');
                    }

                    $emailModel = new UserEmails;
                    $emailModel = $emailModel->saveEmailDataForBusiness($userID, $_POST, $location_id);
                    if ($emailModel != true) {
                        throw new Exception('error while saving user email data.');
                    }
                    $emailId = $connection->getLastInsertID();



                    $roles_arr = array();
                    foreach ($modelUserRoles as $key => $value) {

                        $businessRolesModel = new SiteUserRoles;
                        $businessRolesModel = $businessRolesModel->saveSiteUserRolesForBusiness($userID, $value->MLR_ID, $location_id, $business_id, $emailId);
                        $roles_arr[$key]['role_name'] = $value->MLR_Name;
                        $roles_arr[$key]['role_id'] = $value->MLR_ID;
                        $roles_arr[$key]['sur_id'] = Yii::app()->db->getLastInsertId();
                        if ($businessRolesModel != true) {
                            throw new Exception('error while saving site user roles data.');
                            $roles_arr = array();
                        }
                    }



                    if (isset($_POST['QKPQuickpoll']['categories'])) {
                        $businessCategoriesModel = new BusinessCategories;
                        $businessCategoriesModel = $businessCategoriesModel->saveBusinessCategories($_POST, $userID, $business_id, $location_id);
                        if ($businessCategoriesModel != true) {
                            throw new Exception('error while saving business categories data.');
                        }
                    }



                    foreach ($roles_arr as $key => $datavalue) {

                        if (($datavalue['role_name'] == 'User Business Administrator') || ($datavalue['role_name'] =='User Category Administrator')) {

                            $userSiteCategoryModel = new SiteUserCategories;
                            $userSiteCategoryModel = $userSiteCategoryModel->saveSiteUserCategoriesDataForBusiness($_POST, $userID, $datavalue);

                            if ($userSiteCategoryModel != true) {
                                throw new Exception('error while saving site category for business.');
                            }
                        }
                    }

                    $country_name_id = Countries::model()->find('CNT_ISO2=:CNT_ISO2', array(':CNT_ISO2' => $_POST['UserLocations']['LCN_Country_id']));
                    $countryName = $country_name_id['CNT_Name'];

                    $businessData = "Business Name      :  " . $_POST['Businesses']['BUS_Name'] . "<br/>" .
                            "Business Url       :  " . $_POST['Businesses']['BUS_URL'] . "<br/>" .
                            "Business Description   :  " . $_POST['Businesses']['BUS_Description'] . "<br/>" .
                            "Location Name      :  " . $_POST['UserLocations']['LCN_Name'] . "<br/>" .
                            "Country            :  " . $countryName . "<br/>" .
                            "Email Id       :  " . $_POST['UserEmails']['UEM_Email'] . "<br/>" .
                            "Phone Number       :  " . $_POST['UserPhones']['UPH_Phone'] . "<br/>" .
                            "Radius         :  " . $_POST['UserLocations']['LCN_Radius_miles'] . "<br/>";
                    $moduleStatus = $this->moduleStatus();

                    
                    if(isset($_POST['createBusiness_approval'])){
                     $sqlq     = "SELECT UEM_Email FROM ML_User_Emails  WHERE UEM_USR_ID = $userID AND UEM_IiDefault =1";
                    
                    $data_email    = Yii::app()->db->createCommand($sqlq)->queryRow();
                    if(!empty($data_email)){
                                $to = 'test.master@mylokals.com';
                                $from = $data_email['UEM_Email'];
                                $subject = "Request to add Business approve";
                                $html_file = "Business-created";
                                $lan = "en";
                                $toEmail = $to;
                                $obj = new Users();
                                $var  = $obj->getUserData($userID);
                                $data['name'] = $var['USR_FirstName'];
                                $data["html_file"] = $html_file;
                                $data["language"] = $lan;
                                $data["to_email"] = $toEmail;                                
                                $mail_status = $this->Sendmail($data);
                    }
                    }
                    
                    if ($moduleStatus == TRUE) {
                        $adminId = Yii::app()->user->id;
                        $getAdminName = $this->getAdminName($adminId);
                        $activity = "Business Management - Create";
                        $description = $businessData . " has been created by " . $getAdminName;
                        $audit = new Functions();
                        $activity = $activity;
                        $description = $description;
                        $descriptionDevp = $businessData . " has been created by " . $getAdminName;
                        $setAudit = $audit->userAudit($description, $activity, $descriptionDevp);
                    }

                    $transaction->commit();


                    $this->redirect(array('business_management'));
                } catch (Exception $e) {
                    $transaction->rollback();
                    //Yii::trace($e->getMessage());
                    Yii::app()->user->setFlash("failure", "Please enter the required fields correctly.");
                }
            }
        }

        $countryRs = Countries::model()->findAll(array('condition'=>'CNT_Status=1','order'=>'CNT_Name ASC'));
	    $countries = array();
	    $countriesFlag = array();
	    foreach($countryRs as $country)
	    {
	        $countries[$country->CNT_Code]                  = '+'.$country->CNT_Phone_Code.' '.$country->CNT_Name;
	        $countriesFlag[$country->CNT_Code]['data-flag'] = Yii::app()->baseUrl.'/images/flags/24x24/'.strtolower($country->CNT_ISO2).'.png';
	        $countriesFlag[$country->CNT_Code]['data-code'] = $country->CNT_Phone_Code;
	    }

        $this->render('create', array(
            'model' => $businessModel,
            'location' => $locationModel,
            'approve_access' => $approve_access,
            'phoneModel'=>$phoneModel,
            'emailModel'=>$emailModel,
            'countries' => $countries, 'countriesFlag'=> $countriesFlag
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Businesses'])) {
            $model->attributes = $_POST['Businesses'];
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->BUS_ID));
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('Businesses');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new Businesses('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['Businesses']))
            $model->attributes = $_GET['Businesses'];

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id) {
        $model = Businesses::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'businesses-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function BusinessCategoryJson() {
        $categoryJson = array();
        $businessArray = BusinessCategories::model()->findAll(array('select' => 't.MBC_ACT_ID', 'distinct' => true));
        foreach ($businessArray as $business) {
            $categoryJson[] = $business['MBC_ACT_ID'];
        }
        return join(',', $categoryJson);
    }

    public function actionEdit_business() {
		$bus_id = Yii::app()->getRequest()->getQuery('id');
		$cur_user_id = yii::app()->user->id;
		$obj_id         = Objects::model()->findByAttributes(array('BJ_Name'=>'Business Content'));
        $auth           = Yii::app()->authorization;
        $privilege = $auth->getPrivilege($cur_user_id,$obj_id->BJ_ID,$bus_id,'Business'); 
        if($privilege['read'] == '1'){
        if(Yii::app()->user->hasState("status")) {
            Yii::app()->user->setState("status", null);
        }
        if (Yii::app()->request->isAjaxRequest) {
            $this->layout = false;
            $bus_id = $_POST['business_id'];
        } 
        $session_id = Yii::app()->user->id;
        $categoryJson = $this->BusinessCategoryJson();
        $criteria = new CDbCriteria();
        $criteria->condition = "SLOB_REF_OBJ_TYPE = 11";
        $criteria->order = 'SLOB_ID DESC';

        $lastbackup = SiteLOBS::model()->find($criteria);
        if (!empty($lastbackup)) {
            $tc_data = SiteLOBS::model()->findByPk($lastbackup->SLOB_ID);
        } else {
            $tc_data = '';
        }


        $act = 'Active';


        $userId = Yii::app()->user->id;
        /* ----  Authorization check  */
        $obj_id = Objects::model()->findByAttributes(array('BJ_Name' => 'Business'));
        $auth = Yii::app()->authorization;
        $edit_access = $auth->canEdit(Yii::app()->session['id'], $obj_id->BJ_ID, 1, 1, 0, 0);
        $add_access = $auth->canCreate(Yii::app()->session['id'], $obj_id->BJ_ID, 1, 1, 0, 0);
        $delete_access = $auth->canDelete(Yii::app()->session['id'], $obj_id->BJ_ID, 1, 1, 0, 0);
        $approve_access = $auth->canApprove(Yii::app()->session['id'], $obj_id->BJ_ID, 1, 1, 0, 0);
        /* ----  Authorization check  */

        /* ----  Authorization check for create new business link  */
        $site_user_roles_create = SiteUserRoles::model()->findAll(array('condition' => "SUR_USR_ID=$userId and ML_BUS_ID != 0"));
        $add_access_approve = null;
        $add_access_check = array();
        $approve_access_t_and_c = array();
        foreach ($site_user_roles_create as $site_user_roles) {

            $obj_id_create = Objects::model()->findByAttributes(array('BJ_Name' => 'Business'));
            $obj_id_approve = Objects::model()->findByAttributes(array('BJ_Name' => 'Business Contracts'));
            $auth_create = Yii::app()->authorization;
            $add_access_create = $auth_create->canCreate(Yii::app()->user->id, $obj_id_create->BJ_ID, 1, 1, $site_user_roles->ML_BUS_ID, 0);
            $add_access_approve = $auth->canApprove(Yii::app()->user->id, $obj_id_approve->BJ_ID, 1, 1, $site_user_roles->ML_BUS_ID, 0);

            if ($add_access_create == 1) {
                $add_access_check[] = $add_access_create;
            }
            if ($add_access_approve == 1) {
                $approve_access_t_and_c[] = $add_access_approve;
            }
        }

        /* ----  Authorization check for create new business link  */


        //get business data from Functions.php component

        $models = array();
        /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
        $sql = "SELECT ML_Businesses.*, ML_User_Locations.LCN_Created_By,ML_User_Locations.LCN_Name,ML_User_Locations.LCN_Radius_miles,ML_User_Locations.LCN_ID,ML_User_Locations.LCN_Geo_longitude,ML_User_Locations.LCN_Geo_latitude,ML_Businesses.BUS_ID,ML_Businesses.BUS_Name,ML_Businesses.BUS_Description,ML_Businesses.BUS_Status,ML_Master_Type_Items.MSTT_Name FROM ML_User_Locations
                            RIGHT JOIN ML_Businesses ON ML_User_Locations.LCN_REF_OBJ_KEY = ML_Businesses.BUS_ID
                            RIGHT JOIN ML_Master_Type_Items ON ML_Businesses.BUS_Status = ML_Master_Type_Items.MSTT_ID
                            RIGHT JOIN ML_Business_Categories ON ML_Businesses.BUS_ID = ML_Business_Categories.MBC_BUS_ID
                            WHERE ML_User_Locations.LCN_REF_OBJ_TYPE='11' AND BUS_ID=$bus_id";
        $models = Yii::app()->db->createCommand($sql)->queryRow();

        // $func = new Functions;
        //$funccall = $func->Business_data('true');
        // $models = $funccall['content'];

        $data = array();
        $bus_tiny_data = array();
        $y = 1;
        // foreach ($models as $k => $model) {
        $bus_tiny_data['sno'] = $y++;
        $modeluser_roles = SiteUserRoles::model()->findAllByAttributes(array('SUR_USR_ID' => Yii::app()->session['id'], 'ML_BUS_ID' => $models['BUS_ID']));
        $role_name = "";
        $sessionUser = Yii::app()->user->id;
        /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
        $businessUser = $models['LCN_Created_By'];

        foreach ($modeluser_roles as $roles) {

            $User_Roles = Roles::model()->findByPk($roles->SUR_MLR_ID);
            $role_name = $role_name . $User_Roles->MLR_Name . ",";
        }
        //  $BusinessLocationName = UserLocations::model()->findBypk($lcn_id);
        $bus_tiny_data['BUS_ID'] = $models['BUS_ID'];
        $bus_tiny_data['BUS_Status'] = $models['BUS_Status'];
        $bus_tiny_data['sessionUser'] = $sessionUser;
        $bus_tiny_data['businessUser'] = $businessUser;
        $bus_tiny_data['Business'] = $models['BUS_Name'] . "-" . $models['LCN_Name'];
        if (!empty($role_name)) {
            $bus_tiny_data['Rolename'] = substr($role_name, 0, -1);
        } else {
            $bus_tiny_data['Rolename'] = "";
        }
        $bus_tiny_data['flag'] = $approve_access;
        $bus_tiny_data['MSTT_Name'] = $models['MSTT_Name'];
        $bus_tiny_data['LCN_ID'] = $models['LCN_ID'];
        $data[] = $bus_tiny_data;
        //Pagination Code End Here               
        // On browse image load image
        if (isset($_FILES['file']) && isset($_POST['imagepreview'])) {

            $des_photo = './images/default_albem.jpg';

            echo $this->imagecrop($_FILES['file']['tmp_name'], $des_photo, $_FILES['file']['type'], 200, 200);
            exit;
        }

        $modelLocation = new UserLocations;
        $modelphone = new UserPhones;
        $modelemail = new UserEmails;
        $model1 = new Businesses;

        $master_type = MasterTypes::model()->find('MST_Name=:MST_Name', array(':MST_Name' => 'BusinessLocation',));
        $master_type_id = $master_type['MST_ID'];
        $master_type_item = MasterTypeItems::model()->findall('MSTT_MST_ID=:MSTT_MST_ID', array(':MSTT_MST_ID' => $master_type_id,));
        $list = CHtml::listData($master_type_item, 'MSTT_ID', 'MSTT_Value');

        if (isset($_POST['adminaction'])) {
            $adminaction = 1;
        } else {
            $adminaction = 0;
        }


        $userId = Yii::app()->user->id;
        $modelBusiness = Businesses::model()->findByPk($bus_id);

        $auth_array = array();

        $obj_Business = Objects::model()->findByAttributes(array('BJ_Name' => 'Business Content'));
        $obj_BImage = Objects::model()->findByAttributes(array('BJ_Name' => 'Business Image'));
        $obj_business_id = $bus_id;
        $auth = Yii::app()->authorization;

        $auth_array['Bus_cont_edit_access'] = $auth->canEdit(Yii::app()->user->id, $obj_Business->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_cont_add_access'] = $auth->canCreate(Yii::app()->user->id, $obj_Business->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_cont_delete_access'] = $auth->canDelete(Yii::app()->user->id, $obj_Business->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_cont_approve_access'] = $auth->canApprove(Yii::app()->user->id, $obj_Business->BJ_ID, 1, 1, $obj_business_id, 0);

        $auth_array['Bus_image_edit_access'] = $auth->canEdit(Yii::app()->user->id, $obj_BImage->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_image_add_access'] = $auth->canCreate(Yii::app()->user->id, $obj_BImage->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_image_delete_access'] = $auth->canDelete(Yii::app()->user->id, $obj_BImage->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_image_approve_access'] = $auth->canApprove(Yii::app()->user->id, $obj_BImage->BJ_ID, 1, 1, $obj_business_id, 0);


        if (isset($_POST['edit']) && isset($_POST['Businesses'])) {

            /*             * **user Audit start*** */
//echo "koppppp";exit;



            $bus_id = $_POST['Businesses']['BUS_ID'];
            $model = new Businesses();
            $businessData = $model->getBusinessDetails($bus_id);
            $modelBusiness = Businesses::model()->findByPk($bus_id);

            $modelBusiness->scenario = 'edit';
            //$model1->BUS_ID = $bus_id;

            $modelBusiness->BUS_Name = $_POST['Businesses']['BUS_Name'];

            $modelBusiness->BUS_URL = $_POST['Businesses']['BUS_URL'];

            $modelBusiness->BUS_Summary = $_POST['Businesses']['BUS_Summary'];

            $modelBusiness->BUS_Description = $_POST['Businesses']['BUS_Description'];


            if ($modelBusiness->BUS_Status == '220') {
                $buinessname = Businesses::model()->findByPk($busid = $_POST['Businesses']['BUS_ID']);
                //echo '<pre>'; print_r($buinessname->BUS_Type);exit;
                $auth = Yii::app()->authorization;
                $rites = $auth->checkAdminRights($userId);
                if ($buinessname->BUS_Type == 3) {
                    if ($rites == 1) {
                        $modelBusiness->BUS_Status = 220;
                    } else {
                        $modelBusiness->BUS_Status = 219;
                    }
                } else {
                    $modelBusiness->BUS_Status = 219;
                }
                $modelBusiness->BUS_ReasonForChange = $_POST['Businesses']['BUS_ReasonForChange'];
            }


            // $modelBusiness->save(false);

            if ($modelBusiness->save()) {

                /* Audit code new starts here */

                $old_bus_name = $businessData['BUS_Name'];
                $old_bus_website = $businessData['BUS_URL'];
                $old_bus_description = $businessData['BUS_Description'];
                $old_bus_status = $businessData['BUS_Status'];
                $old_bus_summary = $businessData['BUS_Summary'];

                $busName = $_POST['Businesses']['BUS_Name'];

                $busDescription = $_POST['Businesses']['BUS_Description'];
                $busWebsite = $_POST['Businesses']['BUS_URL'];
                //   $bussummary = $_POST['MLA_Website'];
                if (isset($_POST['Businesses']['BUS_ReasonForChange'])) {
                    $reason = $_POST['Businesses']['BUS_ReasonForChange'];
                } else {
                    $reason = "";
                }


                $ass_name = Yii::t('app', 'Business Name');
                $web_site = Yii::t('app', 'Website');
                $des = Yii::t('app', 'Description has been modified');
                $desc = Yii::t('app', 'Description');
                if (strcmp($old_bus_description, $busDescription) == 0) {

                    $old_val = "<li><strong>" . $ass_name . "</strong>:" . $old_bus_name . "</li>
                                        <li><b>" . $web_site . "</b>:" . $old_bus_website . "</li>";
                    $new_val = "<li><strong>" . $ass_name . ":</strong>" . $busName . "</li>
                                        <li><b>" . $web_site . "</b>:" . $busWebsite . "</li>";
                } else {
                    $old_val = "<li><b>" . $ass_name . "</b>:" . $old_bus_name . "</li>
                                        <li><b>" . $web_site . "</b>:" . $old_bus_website . "</li>";
                    $new_val = "<li><b>" . $ass_name . "</b>:" . $busName . "</li>
                                        <li><b>" . $web_site . "</b>:" . $busWebsite . "</li>"
                            . "<li><b>" . $desc . "</b>:" . $des . "</li>";
                }

                $user = new Users;
                $getUserName = $user->getUserName($userId);
                $update_msg = Yii::t('app', 'Name and Description Updated-Business');
                $update = Yii::t('app', 'has updated the business');
                if ($reason != "") {
                    $userId = $userId;
                    $getUserName = $getUserName;
                    $audit = new Functions();
                    $activity = $update_msg;
                    $description = $userId . " " . $update;
                    $descriptionDevp = $reason;
                    $submodid = ' ';
                    $oldvalue = $old_val;
                    $newvalue = $new_val;
                    $objtype = 'Business';
                    $objkey = $bus_id;
                    $audit->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
                } else {
                    $userId = $userId;
                    $getUserName = $getUserName;
                    $audit = new Functions();
                    $activity = Yii::t('app', 'Name and Description Updated-Business');
                    $description = $old_bus_status;
                    $descriptionDevp = $getUserName . " " . $update;
                    $submodid = ' ';
                    $oldvalue = $old_val;
                    $newvalue = $new_val;
                    $objtype = 'Business';
                    $objkey = $bus_id;
                    $setAudit = $audit->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
                }
                    $message = Yii::t('app','Name and description updated successfully');
                    Yii::app()->user->setFlash('success',$message);
                /* Audit code new ends here */
            }
            //*************************** End ******************************
        }
         
		
        $this->render("edit_business", array('modelBusiness' => $modelBusiness,
            'business_id' => $bus_id,
            'auth' => $auth_array,
            'modelLocation' => $modelLocation,
            'modelphone' => $modelphone,
            'modelemail' => $modelemail,
            'list' => $list,
            'models' => $data,
            'add_access' => $add_access,
            'edit_access' => $edit_access,
            'delete_access' => $delete_access,
            'approve_access' => $approve_access,
            'add_access_check' => $add_access_check,
            'tc_data' => $tc_data,
            'approve_access_t_and_c' => $approve_access_t_and_c,
            'flag' => $add_access_approve,
            'categoryJson' => $categoryJson,
            'adminaction' => $adminaction,
            'access'=>$privilege,
            ));
		}else{
			$this->redirect(array('/site/restricted'));
		}
    }
	
	public function actionShow_business() {
		$get_id = Yii::app()->getRequest()->getQuery('id');
		$getid= Yii::app()->db->createCommand("select * from ML_Businesses where BUS_ID='$get_id' or BUS_Short_name='$get_id'")->queryAll();
        $rowCount=count($getid); // execute the non-query SQL

	   if($rowCount>0){

	    $bus_id=$getid[0]['BUS_ID'];
        $models = array();
							 $sql = "SELECT ML_Businesses.*, ML_User_Locations.LCN_Created_By,ML_User_Locations.LCN_Name,ML_User_Locations.LCN_Radius_miles,ML_User_Locations.LCN_ID,ML_User_Locations.LCN_Geo_longitude,ML_User_Locations.LCN_Geo_latitude,ML_Businesses.BUS_ID,ML_Businesses.BUS_Name,ML_Businesses.BUS_Description,ML_Businesses.BUS_Status,ML_Master_Type_Items.MSTT_Name FROM ML_User_Locations
                            RIGHT JOIN ML_Businesses ON ML_User_Locations.LCN_REF_OBJ_KEY = ML_Businesses.BUS_ID
                            RIGHT JOIN ML_Master_Type_Items ON ML_Businesses.BUS_Status = ML_Master_Type_Items.MSTT_ID
                            RIGHT JOIN ML_Category_Association ON ML_Businesses.BUS_ID = ML_Category_Association.MCA_OBJ_Key
                            WHERE ML_User_Locations.LCN_REF_OBJ_TYPE='11' AND BUS_ID=$bus_id ";
        $models = Yii::app()->db->createCommand($sql)->queryRow();
        $data = array();
        $bus_tiny_data = array();
        $y = 1;
        // foreach ($models as $k => $model) {
        $bus_tiny_data['sno'] = $y++;
        $modeluser_roles = SiteUserRoles::model()->findAllByAttributes(array('ML_BUS_ID' => $models['BUS_ID']));
        $role_name = "";
       // $sessionUser = Yii::app()->user->id;
        $businessUser = $models['LCN_Created_By'];

        foreach ($modeluser_roles as $roles) {

            $User_Roles = Roles::model()->findByPk($roles->SUR_MLR_ID);
            $role_name = $role_name . $User_Roles->MLR_Name . ",";
        }


        //  $BusinessLocationName = UserLocations::model()->findBypk($lcn_id);

        $bus_tiny_data['BUS_ID'] = $models['BUS_ID'];
        $bus_tiny_data['BUS_Status'] = $models['BUS_Status'];
       // $bus_tiny_data['sessionUser'] = $sessionUser;
        $bus_tiny_data['businessUser'] = $businessUser;
        $bus_tiny_data['Business'] = $models['BUS_Name'] . "-" . $models['LCN_Name'];
        if (!empty($role_name)) {
            $bus_tiny_data['Rolename'] = substr($role_name, 0, -1);
        } else {
            $bus_tiny_data['Rolename'] = "";
        }
       // $bus_tiny_data['flag'] = $approve_access;
        $bus_tiny_data['MSTT_Name'] = $models['MSTT_Name'];
        $bus_tiny_data['LCN_ID'] = $models['LCN_ID'];
        $data[] = $bus_tiny_data;

$modelBusiness = Businesses::model()->findByPk($bus_id);

        $userId = Yii::app()->user->id;
        $modelBusiness = Businesses::model()->findByPk($bus_id);

        $auth_array = array();

        $obj_Business = Objects::model()->findByAttributes(array('BJ_Name' => 'Business Content'));
        $obj_BImage = Objects::model()->findByAttributes(array('BJ_Name' => 'Business Image'));
        $obj_business_id = $bus_id;
        $auth = Yii::app()->authorization;

        $auth_array['Bus_cont_edit_access'] = $auth->canEdit(Yii::app()->user->id, $obj_Business->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_cont_add_access'] = $auth->canCreate(Yii::app()->user->id, $obj_Business->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_cont_delete_access'] = $auth->canDelete(Yii::app()->user->id, $obj_Business->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_cont_approve_access'] = $auth->canApprove(Yii::app()->user->id, $obj_Business->BJ_ID, 1, 1, $obj_business_id, 0);

        $auth_array['Bus_image_edit_access'] = $auth->canEdit(Yii::app()->user->id, $obj_BImage->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_image_add_access'] = $auth->canCreate(Yii::app()->user->id, $obj_BImage->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_image_delete_access'] = $auth->canDelete(Yii::app()->user->id, $obj_BImage->BJ_ID, 1, 1, $obj_business_id, 0);
        $auth_array['Bus_image_approve_access'] = $auth->canApprove(Yii::app()->user->id, $obj_BImage->BJ_ID, 1, 1, $obj_business_id, 0);

        if (isset($_POST['edit']) && isset($_POST['Businesses'])) {

            /*             * **user Audit start*** */
            $bus_id = $_POST['Businesses']['BUS_ID'];
            $model = new Businesses();
            $businessData = $model->getBusinessDetails($bus_id);
            $modelBusiness = Businesses::model()->findByPk($bus_id);

            $modelBusiness->scenario = 'edit';
            $modelBusiness->BUS_Name = $_POST['Businesses']['BUS_Name'];
            $modelBusiness->BUS_Description = $_POST['Businesses']['BUS_Description'];
            if ($modelBusiness->BUS_Status == '220') {
                $buinessname = Businesses::model()->findByPk($busid = $_POST['Businesses']['BUS_ID']);
                $auth = Yii::app()->authorization;
                $rites = $auth->checkAdminRights($userId);
                if ($buinessname->BUS_Type == 3) {
                    if ($rites == 1) {
                        $modelBusiness->BUS_Status = 220;
                    } else {
                        $modelBusiness->BUS_Status = 219;
                    }
                } else {
                    $modelBusiness->BUS_Status = 219;
                }
            }
            if ($modelBusiness->save()) {
                /* Audit code new starts here */
                $old_bus_name = $businessData['BUS_Name'];
                $old_bus_website = $businessData['BUS_URL'];
                $old_bus_description = $businessData['BUS_Description'];
                $old_bus_status = $businessData['BUS_Status'];
                $old_bus_summary = $businessData['BUS_Summary'];
                $busName = $_POST['Businesses']['BUS_Name'];
                $busDescription = $_POST['Businesses']['BUS_Description'];
                //$busWebsite = $_POST['Businesses']['BUS_URL'];
				$busWebsite = '';
                //   $bussummary = $_POST['MLA_Website'];
                if (isset($_POST['Businesses']['BUS_ReasonForChange'])) {
					 $reason='';
                   // $reason = $_POST['Businesses']['BUS_ReasonForChange'];
                } else {
                    $reason = "";
                }


                $ass_name = Yii::t('app', 'Business Name');
                $web_site = Yii::t('app', 'Website');
                $des = Yii::t('app', 'Description has been modified');
                $desc = Yii::t('app', 'Description');
                if (strcmp($old_bus_description, $busDescription) == 0) {

                    $old_val = "<li><strong>" . $ass_name . "</strong>:" . $old_bus_name . "</li>
                                        <li><b>" . $web_site . "</b>:" . $old_bus_website . "</li>";
                    $new_val = "<li><strong>" . $ass_name . ":</strong>" . $busName . "</li>
                                        <li><b>" . $web_site . "</b>:" . $busWebsite . "</li>";
                } else {
                    $old_val = "<li><b>" . $ass_name . "</b>:" . $old_bus_name . "</li>
                                        <li><b>" . $web_site . "</b>:" . $old_bus_website . "</li>";
                    $new_val = "<li><b>" . $ass_name . "</b>:" . $busName . "</li>
                                        <li><b>" . $web_site . "</b>:" . $busWebsite . "</li>"
                            . "<li><b>" . $desc . "</b>:" . $des . "</li>";
                }

                $user = new Users;
                $getUserName = $user->getUserName($userId);
                $update_msg = Yii::t('app', 'Name and Description Updated-Business');
                $update = Yii::t('app', 'has updated the business');
                if ($reason != "") {
                    $userId = $userId;
                    $getUserName = $getUserName;
                    $audit = new Functions();
                    $activity = $update_msg;
                    $description = $userId . " " . $update;
                    $descriptionDevp = $reason;
                    $submodid = ' ';
                    $oldvalue = $old_val;
                    $newvalue = $new_val;
                    $objtype = 'Business';
                    $objkey = $bus_id;
                    $audit->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
                } else {
                    $userId = $userId;
                    $getUserName = $getUserName;
                    $audit = new Functions();
                    $activity = Yii::t('app', 'Name and Description Updated-Business');
                    $description = $old_bus_status;
                    $descriptionDevp = $getUserName . " " . $update;
                    $submodid = ' ';
                    $oldvalue = $old_val;
                    $newvalue = $new_val;
                    $objtype = 'Business';
                    $objkey = $bus_id;
                    $setAudit = $audit->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
                }
                    $message = Yii::t('app','Name and Description updated successfully');
                    Yii::app()->user->setFlash('success',$message);
                /* Audit code new ends here */
            }
            //*************************** End ******************************
        }
		$this->render("show_business", array('modelBusiness' => $modelBusiness,
            'business_id' => $bus_id,

            ));
		}
		else
		{
			 $this->render('error_business');
		}
    }

    public function actionbusiness_management() {
        $cur_usr_id = Yii::app()->user->id;		
        $auth = Yii::app()->authorization;
        $rites = $auth->checkAdminRights($cur_usr_id);
        $criteria = new CDbCriteria;
        if($rites == 1){			
			$criteria->addInCondition('MSTT_Value', array('Pending Approval', 'Active', 'Inactive'));
		}else{
			$criteria->addInCondition('MSTT_Value', array('Draft', 'Pending Approval', 'Active', 'Inactive'));
		}
        $getstatuslist = MasterTypeItems::model()->findall($criteria);

        if (!empty(Yii::app()->request->cookies)) {
            $cookies = Yii::app()->request->cookies;
            //echo '<pre>';print_r($cookies['session:lang']->value);echo'</pre>';                exit;
            if (!empty($_SESSION['lang']))
                $lang = $_SESSION['lang'];
            else
                $lang = 'en';
        }else {
            $lang = 'en';
        }
        if ($lang == 'en') {
            $statuslist = CHtml::listData($getstatuslist, 'MSTT_ID', 'MSTT_Name');
        } else if ($lang == 'te') {
            $statuslist = CHtml::listData($getstatuslist, 'MSTT_ID', 'MSTT_Value_te');
        } else if ($lang == 'kn') {
            $statuslist = CHtml::listData($getstatuslist, 'MSTT_ID', 'MSTT_Value_kn');
        } else if ($lang == 'hi') {
            $statuslist = CHtml::listData($getstatuslist, 'MSTT_ID', 'MSTT_Value_hi');
        }else{
             $statuslist = CHtml::listData($getstatuslist, 'MSTT_ID', 'MSTT_Name');

        }
        $model = new Businesses();
        $model->unsetAttributes();  // clear any default values       
        if (isset($_GET['business_management'])) {			
            $model->attributes = $_GET['business_management'];
        }
        $func = new Functions();
        $total_count = $func->num_format(VisitorLog::getTotalCount());
            $this->render('Business_management', array('statuslist' => $statuslist, 'model' => $model, 'total_count' => $total_count));
        
    }
    public function actionBusLocStructure() {


        $username = Yii::app()->db->username;
        $pwd = Yii::app()->db->password;
        $array = preg_split('/;/', Yii::app()->db->connectionString);
        $arr1 = preg_split('/=/', $array[1]);
        //$db = new MySQL('localhost', $username, $pwd, $arr1[1]);
        $host = $_SERVER['SERVER_NAME'];
        if(($host == "localhost") || ($host == "test4.mylokals.com")) {
            $db = new MySQL('localhost', $username, $pwd , $arr1[1]);
        } else if($host == "test6.mylokals.com") {
            $db = new MySQL('test4.mylokals.com', $username, $pwd , $arr1[1]);
        }
        if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
            $action = $_REQUEST['action'];
        } else {
            die(FAILED);
        }
        define("IN_PHP", true);

        $out = NULL;
        switch ($action) {

            case "getElementList": {
                    /**
                     * getting element list
                     */
                    if (isset($_REQUEST['ownerEl']) == true && $_REQUEST['ownerEl'] != NULL) {
                        $ownerEl = $_REQUEST['ownerEl'];
                    } else {
                        $ownerEl = 0;
                    }
                    if (isset($_REQUEST['child'])) {
                        $child = $_REQUEST['child'];
                    } else {
                        $child = null;
                    }
                    if (isset($_REQUEST['parent'])) {
                        $parent = $_REQUEST['parent'];
                    } else {
                        $parent = null;
                    }
                    if (isset($_REQUEST['parenth'])) {
                        $parenth = $_REQUEST['parenth'];
                    } else {
                        $parenth = null;
                    }
                    if (isset($_REQUEST['childh'])) {
                        $childh = $_REQUEST['childh'];
                    } else {
                        $childh = null;
                    }
                    $treeManager = new TreeManagerLocation($db);
                    $out = $treeManager->getBusinessLocList($ownerEl, Yii::app()->request->baseUrl . '/businesses/businesses/BusLocStructure', $_REQUEST['child'], $parent, $parenth, $childh);
                }
                break;


            default:
                /**
                 * if an unsupported action is requested, reply it with FAILED
                 */
                $out = FAILED;
                break;
        }
        echo $out;
    }

    public function actiongetMoreBusinessDetails() {
        $this->layout = false;
        $this->render('morebusinessdetails');
    }

    public function actionOnprocessing() {
        echo 'we are working on it';
    }

    /* Code for getting BUS ID By BUSINESS SLUG  starts here */

    public function getIdBySlug($bus_slug) {
        $slug = trim($bus_slug);
        $res = array();
        $res['flag'] = false;

        if (isset($slug) && (!empty($slug))) {

            $bus_model = Businesses::model()->findByAttributes(array('BUS_Slug' => $slug));

            if (isset($bus_model->BUS_ID)) {
                $res['bus_id'] = $bus_model->BUS_ID;
                $res['flag'] = true;
            }
        }

        return $res;
    }

    public function actionBusiness_Categories() {

        if (isset($_GET['id'])) {
            $busId = $_GET['id'];
        }else if(isset($_POST['bid'])){
			$busId = $_POST['bid'];
		}else{
			echo 'something went wrong';
			exit;
		}
		
        $cur_usrid = Yii::app()->user->id;		        
        $obj_id         = Objects::model()->findByAttributes(array('BJ_Name'=>'Business Category'));
        $auth           = Yii::app()->authorization;                 
        $privilege = $auth->getPrivilege($cur_usrid,$obj_id->BJ_ID,$busId,'Business'); 
        if($privilege['read'] == 1){
        if (isset($_POST['add_category'])) {
            $busid = $_POST['bid'];
            $catid = $_POST['cid'];
            $ursid = Yii::app()->user->id;
            $checkcat = CategoryAssociation::checkCategoryExist($busid, $catid);
            if ($checkcat) {
                CategoryAssociation::addBusinessCategory($busid, $catid, $ursid);
                $temp[] = 'User Business Administrator';				
				$temp[] = 'User Category Administrator';
                $roles = Roles::getRoles($temp);
                $rolesid = CHtml::listData($roles,'MLR_Name','MLR_ID');
				$adminsite = SiteUserRolesEmployee::getsitebasedonrolesandbus($busid,$rolesid['User Business Administrator']);
				$adminsiteid = CHtml::listData($adminsite,'SUR_ID','SUR_USR_ID');
				
				$ubasite = SiteUserRolesEmployee::getUserRolesForBusiness($adminsiteid,$busid,$rolesid);
				$ubslist = CHtml::listData($ubasite,'SUR_ID','SUR_USR_ID');		
				
				if(!in_array($ursid,$ubslist)){
					$ucarole = Roles::getRoles('User Category Administrator');
					$ucasite = SiteUserRolesEmployee::getuserbasedonrole($ursid,$busid,$ucarole->MLR_ID);
					if(!empty($ucasite)){
						$ucalist = CHtml::listData($ucasite,'SUR_ID','SUR_USR_ID');
						//$ubslist = array_merge($ubslist,$ucalist);
						$ubslist = $ubslist+$ucalist;						
					}						
				}							
				$surlist = array();
				foreach($ubslist as $key => $value){
					array_push($surlist,$key);
				}
						
				foreach($surlist as $value){					
					SiteUserCategories::addBusinessCategoryPrivilege($catid,$value,$ursid);
				}
                $newCat = array();
                $functions = new Functions;
                $lists = $functions->category_list($catid);
                $newcat = join(' > ', $lists);
                $newCat[] = $newcat;

                $new_value = "<ul>";
                foreach ($newCat as $eachCat) {
                    $new_value .= "<li>" . $eachCat . "</li>";
                }
                $new_value .= "</ul>";
                $cat_add = Yii::t('app', 'Category added');
                $has = Yii::t('app', 'has updated the Category');
                $cat_has = Yii::t('app', 'Category has been added');
                $has_been = Yii::t('app', 'has been added');
                $userId = Yii::app()->user->id;
                $getUserName = $this->getUserName($userId);
                $activity = $cat_add;
                $description = $getUserName . $has;
                $descriptionDevp = $cat_has;
                $submodid = ' ';
                $oldvalue = "";
                $newvalue = $new_value;
                $objtype = 'Business';
                $objkey = $busid;
                $setAudit = $functions->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
                $success =  Yii::t('app','Business category added successfully');
               // Yii::app()->user->setFlash('success',$success);

                echo('success');
            } else {
                echo('already exist');
            }
            exit;
        }

        if (isset($_POST['remove_category'])) { 
            $busid = $_POST['bid'];
            $catid = $_POST['cid'];
            
            foreach($catid as $catid_val){ 
            $data =  BusinessAsset::getBusinessAsset($busid, $catid_val);
            $busCat = new CategoryAssociation;
            $existingCategories = $busCat->getBusinessCategories($busid);
            if(count($existingCategories) > count($catid)){
            foreach($data as $val){
                
            if($val === 0 ){
            CategoryAssociation::removeBusinessCategory($busid, $catid_val);
            $success =  Yii::t('app','Business category removed successfully');
            //Yii::app()->user->setFlash('success',$success);
            }else{
            $message = Yii::t('app','Category is associated with asset card hence cannot be deleted');
            Yii::app()->user->setFlash('success',$message);
              }
           }
            }else{
                $message = Yii::t('app','Minimum one category should be associated with business hence cannot be deleted');
                Yii::app()->user->setFlash('success',$message);
            }
           }
            $newCat = array();
            foreach ($catid as $category) {
                $functions = new Functions;
                $lists = $functions->category_list($category);
                $newcat = join(' > ', $lists);
                $newCat[] = $newcat;
            }
            $new_value = "<ul>";
            foreach ($newCat as $eachCat) {
                $new_value .= "<li>" . $eachCat . "</li>";
            }
            $new_value .= "</ul>";
            $cat_rem = Yii::t('app', 'Category removed');
            $cat_has = Yii::t('app', 'has updated the Category');
            $cat_updated = Yii::t('app', 'Category has been removed');
            $userId = Yii::app()->user->id;
            $getUserName = $this->getUserName($userId);
            $activity = $cat_rem;
            $description = $getUserName . $cat_has;
            $descriptionDevp = $cat_updated;
            $submodid = ' ';
            $oldvalue = "";
            $newvalue = $new_value;
            $objtype = 'Business';
            $objkey = $busid;
            $setAudit = $functions->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
            
            exit;
        }

        if (isset($_POST['update_category'])) {
            $busid = $_POST['bid'];
            $ccatid = $_POST['ccid'];
            $pcatid = $_POST['pcid'];

            $functions = new Functions;
            $catlists = $functions->category_list($pcatid);
            $newcat = join(' > ', $catlists);
            $old_value = "";
            $old_value .= "<li>" . $newcat . "</li>";
            $old_value .= "";


            $newlists = $functions->category_list($ccatid);
            $newcat_val = join(' > ', $newlists);
            $new_value = "";
            $new_value .= "<li>" . $newcat_val . "</li>";
            $new_value .= "";

            CategoryAssociation::updateBusinessCategory($busid, $ccatid, $pcatid);
            $cat_updated = Yii::t('app', 'Category Updated');
            $has = Yii::t('app', 'has updated the Category');
            $cat = Yii::t('app', 'Category has been updated');
            $userId = Yii::app()->user->id;
            $getUserName = $this->getUserName($userId);
            $activity = $cat_updated;
            $description = $getUserName . $has;
            $descriptionDevp = $cat;
            $submodid = ' ';
            $oldvalue = $old_value;
            $newvalue = $new_value;
            $objtype = 'Business';
            $objkey = $busid;
            $setAudit = $functions->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
            $success =  Yii::t('app','Business category updated successfully');
           // Yii::app()->user->setFlash('success',$success);
            exit;
        }
              
        $getCategories = CategoryAssociation::getBusinessCategories($busId);
        
        $this->render('business_categories', array('busCategories' => $getCategories,'access'=>$privilege));
		}else{
			$this->redirect(array('/site/restricted'));
		}

    }

    public function actiongetBusinessAsset() {
        $this->layout = false;
        $catid = $_GET['catid'];
        $busid = $_GET['busid'];
        $getAsset = AssetCategory::getAssetBasedOnCategory($catid);
        $getBusAst = BusinessAsset::getBusinessAsset($busid, $catid);
        $this->render('getbusinessasset', array('getAsset' => $getAsset, 'catid' => $catid, 'getBusAst' => $getBusAst));
    }

    public function actiongetAllBusinessAsset() {
        $this->layout = false;
        $catid = $_GET['cid'];
        $busid = $_GET['bid'];
        $data = array();
        $temp = array();
        foreach ($catid as $value) {
            $getAsset = AssetCategory::getAssetBasedOnCategory($value);
            $getBusAst = BusinessAsset::getBusinessAsset($busid, $value);
            $temp['cat'] = array('id' => $value);
            if (!empty($getAsset)) {
                foreach ($getAsset as $ast) {
                    if (in_array($ast['MLA_ID'], $getBusAst)) {
                        $temp['cat']['data'][] = array('aid' => $ast['MLA_ID'], 'aname' => $ast['MLA_Title'], 'chkd' => 1);
                    } else {
                        $temp['cat']['data'][] = array('aid' => $ast['MLA_ID'], 'aname' => $ast['MLA_Title'], 'chkd' => 0);
                    }
                }

            } else {
                $temp['cat']['data'][] = '';
            }
            $data[] = $temp;
        }

        echo json_encode($data);
    }

    public function actionaddBusinessAsset() {
        $busid = $_POST['bid'];
        $catid = $_POST['cid'];
        $astid = $_POST['ast'];

        
        $busast = BusinessAsset::addBusinessAsset($busid, $catid, $astid);
        if ($busast === true) {
            $exp_added = Yii::t('app', 'Business Asset- Added');
            $has = Yii::t('app', 'has assigned asset card to category');
            $desc_exp = Yii::t('app', 'Asset card assigned to the category');
            $functions = new Functions();
            $userId = Yii::app()->user->id;
            $getUserName = $this->getUserName($userId);
            $activity = $exp_added;
            $description = $getUserName . $has;
            $descriptionDevp = $desc_exp;
            $submodid = '';
            $oldvalue = "";
            $newvalue = "";
            $objtype = 'Business';
            $objkey = $busid;
            $setAudit = $functions->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
            $message = Yii::t('app','Business asset updated successfully');
            Yii::app()->user->setFlash('success',$message);
            echo('success');
        } else if ($busast === false) {
            echo('failed');
        } else {
            echo('something went wrong');
        }
    }

    public function actionbusiness_expertise() {
        if (isset($_GET['id'])) {
            $busId = $_GET['id'];
        }
        $cur_usrid = Yii::app()->user->id;		        
        $obj_id         = Objects::model()->findByAttributes(array('BJ_Name'=>'Business Expertise'));
        $auth           = Yii::app()->authorization;          
        $privilege = $auth->getPrivilege($cur_usrid,$obj_id->BJ_ID,$busId,'Business');
        if($privilege['read'] == 1){
        if (isset($_POST['add_expertise'])) {
            $busid = $_POST['bid'];
            $name = $_POST['name'];
            $desc = $_POST['desc'];
            $usrid = Yii::app()->user->id;
            BusinessAttributes::addBusinessExpertise($busid, $name, $desc, $usrid);
            $exp_added = Yii::t('app', 'Business Expertise- Added');
            $has = Yii::t('app', 'has updated the Business Expertise');
            $desc_exp = Yii::t('app', 'Business Expertise- Description added');
            $functions = new Functions();
            $userId = $usrid;
            $getUserName = $this->getUserName($usrid);
            $activity = $exp_added;
            $description = $getUserName . $has;
            $descriptionDevp = $desc_exp;
            $submodid = ' ';
            $oldvalue = "";
            $newvalue = "";
            $objtype = 'Business';
            $objkey = $busid;
            $setAudit = $functions->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
            $message = Yii::t('app','Business expertise name and description saved successfully');
            Yii::app()->user->setFlash('success',$message);
            exit;
        }
        if (isset($_POST['remove_expertise'])) {
            $gbe = $_POST['gbe'];
            $bid = $_POST['bid'];
            $valid = BusinessAttributes::removeBusinessExpertise($gbe);
            if ($valid == 1) {

                $functions = new Functions();

                $valid = BusinessAttributes::removeBusinessExpertise($gbe);
                if ($valid == 1) {
                    $exp_descrem = Yii::t('app', 'Business Expertise- Description removed');
                    $exp_rem = Yii::t('app', 'Business Expertise- Removed');
                    $has_exp = Yii::t('app', 'has updated the Business Expertise');
                    $userId = Yii::app()->user->id;
                    $getUserName = $this->getUserName($userId);
                    $activity = $exp_rem;
                    $description = $getUserName . $has_exp;
                    $descriptionDevp = $exp_descrem;
                    $submodid = ' ';
                    $oldvalue = "";
                    $newvalue = "";
                    $objtype = 'Business';
                    $objkey = $bid;
                    $setAudit = $functions->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
                    $message = Yii::t('app','Business expertise name and description updated successfully');
                    Yii::app()->user->setFlash('success',$message);
                    echo('success');
                } else {
                    echo('something went wrong');
                }
                exit;
            }
        }
        if (isset($_POST['update_expertise'])) {
            $gbe = $_POST['gbe'];
            $name = $_POST['name'];
            $desc = $_POST['desc'];
            $busid = $_POST['bid'];

            $model = new BusinessAttributes();
            $businessData = $model->getBusinessExpertiseData($gbe);
            $old_bus_name = $businessData['MLBA_Name'];
            $old_bus_description = $businessData['MLBA_Description'];

            $exp_name = Yii::t('app', 'Name');
            $des = Yii::t('app', 'Description has been modified');
            $description = Yii::t('app', 'Description');

            $old_val = "<li><strong>" . $exp_name . "</strong>:" . $old_bus_name . "</li>
                                        <li><b>" . $description . "</b>:" . $old_bus_description . "</li>";

            $new_val = "<li><strong>" . $exp_name . ":</strong>" . $name . "</li>
                                        <li><b>" . $description . "</b>:" . $desc . "</li>";

            $user = new Users;
            $userId = Yii::app()->user->id;
            $getUserName = $user->getUserName($userId);
            $update_msg = Yii::t('app', 'Expertise Name and Description Updated');
            $update = Yii::t('app', 'has updated the business');

            $getUserName = $getUserName;
            $audit = new Functions();
            $activity = $update_msg;
            $description = $userId . " " . $update;
            $descriptionDevp = $update_msg;
            $submodid = ' ';
            $oldvalue = '';//$old_val;
            $newvalue = '';//$new_val;
            $objtype = 'Business';
            $objkey = $busid;
            $audit->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);

            $valid = BusinessAttributes::updateBusinessExpertise($gbe, $name, $desc);
            if ($valid == 1) {
                $message = Yii::t('app','Business expertise name and description updated successfully');
                Yii::app()->user->setFlash('success',$message);
                echo('success');
            } else {
                echo('something went wrong');
            }
            exit;
        }
        $getexpertise = BusinessAttributes::getBusinessexpertise($busId);
        if(isset($_GET['id'])){				
			$getbusstatus = Businesses::getbusstatus($_GET['id']);				
		}else{
			$getbusstatus = '';
		}  
		
        $this->render('business_expertise', array('expertise' => $getexpertise,'busstatus'=>$getbusstatus,'rites'=>$privilege));
		}else{
			$this->redirect(array('/site/restricted'));
		}
    }


    public function actionbusiness_LocationList() {

        $this->layout = false;
        $bus_Id = $_POST['bus_id'];
        $this->render('business_locationlist', array('bus_Id' => $bus_Id));
    }

    public function actionContextual_intelligence() {

        $this->layout = false;

        $bus_id = $_POST['bus_id'];

        if (isset($_POST['get_readperm'])) {

            $obj_id = Objects::model()->findByAttributes(array('BJ_Name' => 'Business Orders'));

            $auth = Yii::app()->authorization;

            $read_access = $auth->canRead(Yii::app()->session['id'], $obj_id->BJ_ID, 1, 1, $_POST['bus_id'], 0);

            echo $read_access;
            exit;
        }

        $orders = CustomerOrders::model()->findAllByAttributes(array('MLCO_BUS_ID' => $bus_id));
        $orders_data = array();
        $data = array();
        foreach ($orders as $key => $order) {
            $user_data = Users::model()->findByPk($order->MLCO_USR_ID);
            $cname = $user_data->USR_FirstName . ' ' . $user_data->USR_LastName;
            $orders_data['OrderedBy'] = $cname;
            $orders_data['OrderedOn'] = date('F d ,Y', strtotime($order->MLCO_DateOfPurchase));
            $orders_data['OrderDetails'] = $order->MLCO_Details;
            $orders_data['UnitPrice'] = $order->MLCO_Unit_Price;
            $orders_data['Quantity'] = $order->MLCO_Quantity;
            $orders_data['Subtotal'] = $order->MLCO_Subtotal;
            $data[$key] = $orders_data;
        }
        /*         * audit start* */

        $criteria = new CDbCriteria;
        $criteria->select = 'BUS_Name';
        $criteria->condition = 'BUS_ID =' . $_POST['bus_id'] . '';
        $audit = Businesses::model()->find($criteria);

        if (!empty($audit))
            $busId = $audit['BUS_Name'];
        else
            $busId = $_POST['bus_id'];
        $audit = new Functions();
        $activity = "Business Contextual Intelligence-View";
        $description = "Contextual Intelligence Details have been viewed for " . $busId;
        $descriptionDevp = "Contextual Intelligence Details have been viewed for " . $_POST['bus_id'];
        $setAudit = $audit->userAudit($description, $activity, $descriptionDevp);

        /*         * AUDIT END* */


        $this->render('contextual_intelligence', array('data' => $data));
    }

    public function actioncheckBusinessStatus() {
        $temp[] = array();
        $business = Businesses::model()->find('BUS_ID=:BUS_ID', array(':BUS_ID' => $_POST['bId']));
        $temp['status'] = $business['BUS_Status'];
        $obj_id = Objects::model()->findByAttributes(array('BJ_Name' => 'Business'));
        $auth = Yii::app()->authorization;
        $temp['access'] = $auth->canApprove(Yii::app()->session['id'], $obj_id->BJ_ID, 1, 1, 0, 0);
        $user_rite = UserLocations::model()->find('LCN_REF_OBJ_TYPE=:LCN_REF_OBJ_TYPE && LCN_REF_OBJ_KEY=:LCN_REF_OBJ_KEY', array(':LCN_REF_OBJ_TYPE' => 11, ':LCN_REF_OBJ_KEY' => $_POST['bId']));
        /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
        if ($user_rite['LCN_Created_By'] == Yii::app()->session['id']) {
            $temp['use_rite'] = 1;
        } else {
            $temp['use_rite'] = 0;
        }
        echo json_encode($temp);
    }
    public function getUserName($userId) {
        $sql1 = "SELECT * FROM ML_Users WHERE USR_ID=" . $userId;
        $data1 = Yii::app()->db->createCommand($sql1)->queryRow();
        if (!empty($data1))
        //return $data1['USR_Screen_Name'];
            return $data1['USR_FirstName'] . '  ' . $data1['USR_LastName'];
    }

    public function actionBusiness_location() {
    if(isset($_POST['business_save_new'])){
    }
        if (Yii::app()->request->isAjaxRequest) {
            $this->layout = false;
        }
        $data = "";
        $bus_id = Yii::app()->getRequest()->getQuery('id');
        if (isset($bus_id)) {
            /* ----  Authorization check  */
            $auth_array = array();
            $obj_Bus_loc = Objects::model()->findByAttributes(array('BJ_Name' => 'Business Location'));
            $obj_business_id = $bus_id;
            $auth = Yii::app()->authorization;
            $auth_array['Bus_loc_edit_access'] = $auth->canEdit(Yii::app()->user->id, $obj_Bus_loc->BJ_ID, 1, 1, $obj_business_id, 0);
            $auth_array['Bus_loc_add_access'] = $auth->canCreate(Yii::app()->user->id, $obj_Bus_loc->BJ_ID, 1, 1, $obj_business_id, 0);
            $auth_array['Bus_loc_delete_access'] = $auth->canDelete(Yii::app()->user->id, $obj_Bus_loc->BJ_ID, 1, 1, $obj_business_id, 0);
            $auth_array['Bus_loc_approve_access'] = $auth->canApprove(Yii::app()->user->id, $obj_Bus_loc->BJ_ID, 1, 1, $obj_business_id, 0);
            /* ----  Authorization check  */
            $id = $bus_id;
            $privacy_model = new PrivacyAttributes();
            $location_model = new UserLocations();
            $data = $location_model->getDataForBusinessLocation($id);
//            echo "<pre>"; print_r($data);exit;
            if (isset($_POST['edit_location'])) {exit;
                /*                 * ***********audit old value********* */
                if (isset($_POST['UserLocations'])) {
                    $userid = Yii::app()->user->id;
                    $locup = $_POST['UserLocations']['LCN_ID'];
                    /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
                    $sql = "SELECT * FROM ML_User_Locations WHERE  LCN_Created_By =$userid AND LCN_ID = '$locup'";
                    $locupdate = Yii::app()->db->createCommand($sql)->queryRow();
                    if (!empty($locupdate)) {
                        $old = "<ul><li style='margin-left: -23px;'>" . $locupdate['LCN_Name'] . '<br />' . $locupdate['LCN_House_number'] . '<br/>' . $locupdate['LCN_Street_name'] . "</li>
                                
                <li style='margin-left: -23px;'>" . $locupdate['LCN_State'] . '<br/>' . $locupdate['LCN_City'] . '<br/>' . $locupdate['LCN_ZipCode'] . "</li>
                </ul>";
                    } else {
                        $old = '';
                    }
                }
                /*                 * *****************audit oldvalue end****** */

                $lcn_id = $_POST['UserLocations']['LCN_ID'];
                $user_location_modal = new UserLocations();
                $user_location_status = $user_location_modal->updateBusinessLocation($lcn_id, $_POST);

                $modelphone = new UserPhones;
                $modelemail = new UserEmails;
                if ($user_location_status == 1) {
                    if (!empty($_POST['UserPhones']['phone'])) {
                        UserPhones::model()->updateByPk($_POST['phone_id'], array('UPH_Phone' => $_POST['UserPhones']['phone']));
                    }

                    if (!empty($_POST['UserEmails']['email'])) {

                        UserEmails::model()->updateByPk($_POST['email_id'], array('UEM_Email' => $_POST['email']));
                    }
                    /*                     * ****************user Audit start****************** */
                    if (isset($_POST['UserLocations']['LCN_ID'])) {

                        $locupdate = Yii::app()->db->createCommand($sql)->queryRow();
                        $new = "<ul><li style='margin-left: -23px;'>" . $locupdate['LCN_Name'] . '<br/>' . $locupdate['LCN_House_number'] . '<br/>' . $locupdate['LCN_Street_name'] . "</li>
                                <li style='margin-left: -23px;'>" . $locupdate['LCN_State'] . '<br/>' . $locupdate['LCN_City'] . '<br/>' . $locupdate['LCN_ZipCode'] . "</li>
                                     </ul>";
                        $userId = Yii::app()->user->id;
                        $getUserName = $this->getUserName($userId);
                        $audit = new Functions();
                        $activity = "Location - Updated";
                        $description = "Location has been updated by " . '' . $getUserName;
                        $descriptionDevp = $userId . "location updated";
                        $submodid = '';
                        $oldvalue = $old;
                        $newvalue = $new;
                        $objtype = 'Business';
                        $objkey = $bus_id;
                        $setAudit = $audit->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
                        $data = $location_model->getDataForBusinessLocation($id);
//                        echo "<pre>";print_r($data);exit;
                        //$this->render('business_location', array('data' => $data, 'id' => $id, 'auth' => $auth_array));
                    }
                    /*                     * **************user Audit End******************* */


                    $message = "Your location has been successfully updated";
                    Yii::app()->user->setFlash('success', $message);
                } else {
                    $message = "Your location unable to update.";
                    Yii::app()->user->setFlash('failure', $message);
                }
            } else if (isset($_POST['close_location'])) {
                $modellocation_close = UserLocations::model()->find('LCN_ID=:LCN_ID && LCN_REF_OBJ_KEY=:LCN_REF_OBJ_KEY', array(':LCN_ID' => $_POST['UserLocations']['LCN_ID'], ':LCN_REF_OBJ_KEY' => $bus_id));

                $modellocation_close->LCN_Status = 0;
                $modellocation_close->save();


                $criteria = new CDbCriteria;
                $criteria->select = 'LCN_Name';
                $criteria->addcondition('LCN_REF_OBJ_KEY=' . $bus_id);
                $location = UserLocations::model()->find($criteria);
                $locationNam = $location['LCN_Name'];


                $adminId = Yii::app()->user->id;
                $getAdminName = $this->getAdminName($adminId);
                $audit = new Functions();
                $activity = "Business Location - Close";
                $description = $locationNam . "-business Location has been closed by  " . $getAdminName;
                $descriptionDevp = $locationNam . "-business Location has been closed by  " . $getAdminName;
                $submodid = ' ';
                $oldvalue = 'location-closed';
                $newvalue = ' ';
                $objtype = 'Business';
                $objkey = $bus_id;
                $setAudit = $audit->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
                $message = "Your location has been closed";
                Yii::app()->user->setFlash('success', $message);
            } else if (isset($_POST['open_location'])) {
                $modellocation_close = UserLocations::model()->find('LCN_ID=:LCN_ID && LCN_REF_OBJ_KEY=:LCN_REF_OBJ_KEY', array(':LCN_ID' => $_POST['UserLocations']['LCN_ID'], ':LCN_REF_OBJ_KEY' => $bus_id));

                $modellocation_close->LCN_Status = 1;
                $modellocation_close->save(false);


                $criteria = new CDbCriteria;
                $criteria->select = 'LCN_Name';
                $criteria->addcondition('LCN_REF_OBJ_KEY=' . $bus_id);
                $locationo = UserLocations::model()->find($criteria);
                $locationName = $locationo['LCN_Name'];

                $adminId = Yii::app()->user->id;
                $getAdminName = $this->getAdminName($adminId);
                $audit = new Functions();
                $activity = "Business Location - Open";
                $description = $locationName . "-business Location has been Opened by  " . $getAdminName;
                $descriptionDevp = $locationName . "-business Location has been Opened by  " . $getAdminName;
                $submodid = '';
                $oldvalue = 'location-reopened';
                $newvalue = ' ';
                $objtype = 'Business';
                $objkey = $bus_id;
                $setAudit = $audit->userAuditOther($description, $activity, $descriptionDevp, $submodid, $oldvalue, $newvalue, $objtype, $objkey);
            }else if(isset($_POST['business_save_new'])) {
                $bus_id = $_GET['id'];
                $loc_id = '';
                $userId = Yii::app()->user->id;
                $obj_id = Objects::model()->findByAttributes(array('BJ_Name'=>'Business Content'));
                $auth = Yii::app()->authorization;
                $approve_access = $auth->canApprove($userId,$obj_id->BJ_ID,$bus_id,$loc_id);
                $location = new UserLocations();
                $connnection = Yii::app()->db;
                $transaction = $connnection->beginTransaction();
                try{
                     $business_data = Businesses::model()->findByPk($bus_id);
                $modelbusiness = new Businesses();
                $modelbusiness = $modelbusiness->saveNewBusinessData($business_data,$approve_access,$bus_id);
                    if($modelbusiness != true){

                    }
                    $bus_id = Yii::app()->db->getLastInsertID();
                } catch (Exception $ex) {

                }
               
            }

            $country_modal = new Countries();
            $country_data = $country_modal->getAllCountry();
			if(isset($_GET['id'])){				
				$getbusstatus = Businesses::getbusstatus($_GET['id']);				
			}else{
				$getbusstatus = '';
			}  
		$cur_user_id = yii::app()->user->id;
		$authorz = Yii::app()->authorization;
		$rites = $authorz->checkAdminRights($cur_user_id);
                
            $countryRs = Countries::model()->findAll(array('condition'=>'CNT_Status=1','order'=>'CNT_Name ASC'));
	    $countries = array();
	    $countriesFlag = array();
	    foreach($countryRs as $country)
	    {
	        $countries[$country->CNT_Code]                  = '+'.$country->CNT_Phone_Code.' '.$country->CNT_Name;
	        $countriesFlag[$country->CNT_Code]['data-flag'] = Yii::app()->baseUrl.'/images/flags/24x24/'.strtolower($country->CNT_ISO2).'.png';
	        $countriesFlag[$country->CNT_Code]['data-code'] = $country->CNT_Phone_Code;
	    }
            $this->render('business_location', array('data' => $data, 'id' => $id, 'auth' => $auth_array,'busstatus'=>$getbusstatus,'rites'=>$rites,'countries' => $countries, 'countriesFlag'=> $countriesFlag));
        } else if (isset($_GET['id']) && isset($_GET['url'])) {
            $id = $_GET['id'];
            $newphrase_rand = $id;
            $newphrase_rand1 = substr($newphrase_rand, 3);
            $numerics = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
            $chars = array("z", "y", "x", "w", "v", "u", "t", "s", "r", "q");
            $newphrase1 = str_replace($chars, $numerics, $newphrase_rand1);
            $idd = (int) $newphrase1;
            $id = $idd;
            $location_model = new UserLocations();


            $privacy_model = new PrivacyAttributes();

            $data = $location_model->getDataForMyLocation($id);
            $data['location_detail'] = $this->getprivacy_viewdata($data, 5);
            $country_modal = new Countries();
            $country_data = $country_modal->getAllCountry();
            $data['country'] = $country_data;

            // get here all the privacy attribute id from
            $data["privacy"]['LCN_House_Number'] = $privacy_model->getIdByAttribute("LCN_House_Number");
            $data["privacy"]['LCN_Street_Name'] = $privacy_model->getIdByAttribute("LCN_Street_Name");
            $data["privacy"]['LCN_Street_Direction'] = $privacy_model->getIdByAttribute("LCN_Street_Direction");
            $data["privacy"]['Lcn_City'] = $privacy_model->getIdByAttribute("Lcn_City");
            $data["privacy"]['LCN_State'] = $privacy_model->getIdByAttribute("LCN_State");
            $data["privacy"]['LCN_Zip'] = $privacy_model->getIdByAttribute("LCN_Zip");
            $data["privacy"]['LCN_Geo_country'] = $privacy_model->getIdByAttribute("LCN_Geo_country");
            $data["privacy"]['LCN_Name'] = $privacy_model->getIdByAttribute("LCN_Name");
            if(isset($_GET['id'])){				
				$getbusstatus = Businesses::getbusstatus($_GET['id']);				
			}else{
				$getbusstatus = '';
			}  
		$cur_user_id = yii::app()->user->id;
		$auth = Yii::app()->authorization;
		$rites = $auth->checkAdminRights($cur_user_id);
$countryRs = Countries::model()->findAll(array('condition'=>'CNT_Status=1','order'=>'CNT_Name ASC'));
	    $countries = array();
	    $countriesFlag = array();
	    foreach($countryRs as $country)
	    {
	        $countries[$country->CNT_Code]                  = '+'.$country->CNT_Phone_Code.' '.$country->CNT_Name;
	        $countriesFlag[$country->CNT_Code]['data-flag'] = Yii::app()->baseUrl.'/images/flags/24x24/'.strtolower($country->CNT_ISO2).'.png';
	        $countriesFlag[$country->CNT_Code]['data-code'] = $country->CNT_Phone_Code;
	    }

            $this->render('business_location', array('data' => $data, 'flag' => 1,'busstatus'=>$getbusstatus,'rites'=>$rites,'countries' => $countries, 'countriesFlag'=> $countriesFlag));
        } else {
            echo("Logout... login again");
        }
    }

    

    public function actionPriviledge() {
        $this->render('priviledge');
    }

    
    public function actionemployees(){
		$model = new SiteUserRolesEmployee();
		
		if(isset($_POST['add_bususer'])){		
			
				$userid = $_POST['uid'];
				$busid = $_POST['bid'];
				$cur_user_id = yii::app()->user->id;
				$auth = Yii::app()->authorization;
				$rites = $auth->checkAdminRights($cur_user_id);
				$getbus = Businesses::getBusinessDetails($busid);
				$getuser = Users::getUserData($userid);
				if(!empty($getbus) && !empty($getuser)){
					$getbusloc = UserLocations::getbusinesslocations($busid);
					$getuseremail = UserEmails::getuserdefaultemail($userid);
					if($getbus['BUS_Type'] == 3){
						if($rites == 1){
							$getrid = Roles::getRoles('Mylokal Employee');                                                        
							$mlrid = $getrid->MLR_ID;					
						}else{						
							$getrid = Roles::getRoles('Normal User');
							$mlrid = $getrid->MLR_ID;
						}
					}else{
						$getrid = Roles::getRoles('Normal User');
						$mlrid = $getrid->MLR_ID;
					}
					$model->SUR_Status     = 2;
					$model->SUR_USR_ID = $userid;				
					$model->SUR_MLR_ID = $mlrid;				
					$model->SUR_AssignedBy = $cur_user_id;
					if(!empty($getbusloc)){
						$model->ML_LCN_ID      = $getbusloc->LCN_ID;
					}
					$model->ML_BUS_ID      = $busid;
					if($getuseremail){
						$model->ML_UEM_ID      = $getuseremail->UEM_ID;
					}
					$model->save(false);
					
					$IFmodel = new InviteFormats;
					$IFmodel->MIF_Name = "email";
					$IFmodel->MIF_Description = "email";
					$IFmodel->MIF_Mail_Subject = "Invitation to assign employee by location";
					$IFmodel->MIF_Mail_Body = "Invitation to assign employee by location";
					$IFmodel->MIF_Applicability = 1;
					$IFmodel->MIF_Status = 1;
					$IFmodel->MIF_CreatedBy = Yii::app()->session['id'];
					$IFmodel->MIF_CreatedOn = date('Y-m-d H:i:s');				
					$IFmodel->save(false);                    
						
					$NImodel = new NewInvites;
					$NImodel->MNU_FirstName =$getuser['USR_FirstName'];       
					$NImodel->MNU_LastName = $getuser['USR_LastName'];
					$NImodel->MNU_BusinessName=$getbus['BUS_Name'];
					$NImodel->MNU_Email = $getuseremail->UEM_Email;
					$NImodel->MNU_Status = 1;
					$NImodel->MNU_CreatedBy = Yii::app()->session['id'];
					$NImodel->MNU_Source = $busid;
					$NImodel->MNU_Source_ID= $model->SUR_ID;
					$NImodel->MNU_Invitation_Status = 0;
					$NImodel->MNU_Invitation_SentOn = date('Y-m-d H:i:s');
					$NImodel->MNU_Comments = "";
					$NImodel->MNU_MIF_Id = $IFmodel->MIF_Id;				
					$NImodel->save(false);
                                        /** mail --start **/
                                        
                                $id = $IFmodel->MIF_Id;
                                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
								$randomString = '';
								for ($i = 0; $i < 3; $i++) {
									$randomString .= $characters[rand(0, strlen($characters) - 1)];
								}
								$phrase = (string) $id;            
								$numerics = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
								$chars = array("z", "y", "x", "w", "v", "u", "t", "s", "r", "q");
								$newphrase = str_replace($numerics, $chars, $phrase);								
								$newphrase_rand = (string) $randomString . $newphrase;
								
                                $to = $getuseremail->UEM_Email;
                                $from = "test.master@mylokals.com";
                                $subject = "Assigned Role For Business";
                                $host = $_SERVER['SERVER_NAME'];
                                if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                                    $protocol = 'https://';
                                } else {
                                    $protocol = 'http://';
                                }
                                $url = $protocol . $host . Yii::app()->baseUrl . "/site/acceptBusinessRole?id=" . $newphrase_rand;

                                $html_file = "Invite-Business-Role";
                                $lan = "en";
                                $toEmail = $to;

                                $data['name'] = $getuser['USR_FirstName'];
                                $data["html_file"] = $html_file;
                                $data["language"] = $lan;
                                $data["to_email"] = $toEmail;                                
                                $data['business_name'] = $getbus['BUS_Name'];
				$data['location_name'] = $getbusloc->LCN_Name;
                                $data["url"] = $url;
                                $mail_status = $this->Sendmail($data);
                                        /** mail --end **/
				}
                              $message = Yii::t('app','You have successfully added an employee.');
                              Yii::app()->user->setFlash('success',$message);  
                              
				exit;	
                                 
                                
		}		
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['id'])){
			$model->attributes = $_GET;		
			$getbusstatus = Businesses::getbusstatus($_GET['id']);				
		}else{
			$getbusstatus = '';
		}  
		$cur_user_id = yii::app()->user->id;
		$auth = Yii::app()->authorization;
		$rites = $auth->checkAdminRights($cur_user_id);
		$mst = MasterTypeItems::getMasterTypeItemsByName(array('Active','Draft','Inactive','Pending Approval'));
		$mst = CHtml::listData($mst,'MSTT_Name','MSTT_ID');
		$this->render('employees',array('model'=>$model,'busstatus'=>$getbusstatus,'rites'=>$rites,'mst'=>$mst,'busid'=>$_GET['id']));
	}
	public function actionauthentication($id){
            /*** Added by Vaiju ***/
            if(isset($_GET['action'])){
                $action = $_GET['action'];
                
                if($action == "approve") {
                    Yii::app()->user->setState("status", "approve");
                } else if($action == "reject") {
                    Yii::app()->user->setState("status", "reject");
                } else if($action == "block") {
                    Yii::app()->user->setState("status", "block");
                } else if($action == "question") {
                    Yii::app()->user->setState("status", "question");
                } 
            }
            $userID = Yii::app()->user->id;
            $model = new Businesses;
            $busData = $model->getBusinessDetails($id);
            $busStatus = $busData['BUS_Status'];
            $userRole = new SiteUserRoles;
            $chk_role = $userRole->getUserRoleByID($userID);
            //echo "<pre>";print_r($chk_role);exit;
            $roles=array();
            $role = new Roles;
            foreach($chk_role as $role_details) { 
                $user_role = $role->getUserRole($role_details['SUR_MLR_ID']);
				if(isset($_SESSION['lang'])){ 
				$lang = $_SESSION['lang'];
				 }else{
					$lang = "en";
				 }  
				if($lang == 'en'){
					$roles[$user_role['MLR_ID']] = $user_role['MLR_Name']; 
				}else if($lang == 'te'){
					$roles[$user_role['MLR_ID']] = $user_role['MLR_Name_te']; 
					}else if($lang == 'hi'){
						$roles[$user_role['MLR_ID']] = $user_role['MLR_Name_hi']; 
						}   else if($lang == 'kn'){
							$roles[$user_role['MLR_ID']] = $user_role['MLR_Name_kn']; 
							}       
            }   
            $this->render('authentication',array('roles'=>array_unique($roles),'model'=>$model,'busID'=>$id,'busStatus'=>$busStatus));
            /*** Added by Vaiju ***/
	}
        
        public function actionBusiness_approval() {
            /*** Added by Vaiju ***/
            $userID = Yii::app()->user->id;
            $model = new Businesses;
            $obj_id = Objects::model()->findByAttributes(array('BJ_Name'=>'Business'));
            $auth = Yii::app()->authorization;
            $approve_access = $auth->canApprove($userID,$obj_id->BJ_ID,1,1,0,0);
                if(isset($_POST['bus_id'])){
                    $bus_id = $_POST['bus_id'];
                    /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
                    $sql     = "SELECT a.UEM_Email ,c.BUS_Name,b.LCN_Created_By FROM ML_User_Locations b, ML_Businesses c,ML_User_Emails a WHERE  a.UEM_LCN_ID = b.LCN_ID AND b.LCN_REF_OBJ_KEY =c.BUS_ID and c.BUS_ID =$bus_id";
                    
                    $data    = Yii::app()->db->createCommand($sql)->queryRow();
                    $usrid =$data['LCN_Created_By'];
                     $sqlq     = "SELECT UEM_Email FROM ML_User_Emails  WHERE UEM_USR_ID = $usrid AND UEM_IiDefault =1";
                    
                    $data_email    = Yii::app()->db->createCommand($sqlq)->queryRow();
                    if(!empty($data))
                    {
                        $email       = $data_email['UEM_Email'];
                        $BusTitle  = $data['BUS_Name'];
                    }
               
                    if(isset($_POST['status']) && $approve_access==1)  
                    {
                        if($_POST['status']=='Active')
                        {
                            $mstName    = $_POST['status'];
                            $mstStatus  = 'Approval';
                            $msStatus   = 'Approved';
                        } else if($_POST['status'] == 'Inactive') {
                            $mstName    = $_POST['status'];
                            $mstStatus  = 'Inactive';
                            $msStatus   = 'Blocked';
                        } else {
                            $mstName = $_POST['status'];
                            $mstStatus = 'Pending Approval';
                            $msStatus="Pending Approval";
                        }
                   
                        if(isset($_POST['comments']))
                            $comments = $_POST['comments'];
                        else
                            $comments = '';
                   
                        $masterTypeItems = new MasterTypeItems;
                        $MasterTypeItemsId = $masterTypeItems->getMasterTypeItemsByName($mstName);
                        if(!empty($MasterTypeItemsId)){
                            $status = $MasterTypeItemsId['MSTT_ID'];
                        }

                        $MasterTypeItems = $masterTypeItems->getMasterTypeItemsByName($mstStatus);
                        if(!empty($MasterTypeItems)){
                            $actionType = $MasterTypeItems['MSTT_ID'];
                        }
                    
                        $busType = $obj_id->BJ_ID;
                    
                        $Actions = new Actions;
                        $Actions->MLA_REF_OBJ_Type= $busType;
                        $Actions->MLA_REF_OBJ_Key = $bus_id;
                        $Actions->MLA_Action_BY   = $userID;
                        $Actions->MLA_Action_on   = date('Y-m-d H:i:s');
                        $Actions->MLA_Approval_comments = $comments;
                        $Actions->MLA_Action_Type = $actionType;
                        $Actions->save(false);
                            $to     = $email;
                            $busname  = $BusTitle;
                            $mstatus = $msStatus;
                            $asset_name =  $busname; 
                            $data['to_email'] = $to;
                            $data['url'] ="";

                            $obj = new Users();
                            $data['name'] = $obj->getUsernameByEmail($to);

                            $data['asset'] = $busname;
                            $data['comment'] = $comments;

                            if($mstatus == "Approved"){
                                // send a mail for approved....
                                $html_file ="Business-Approved";
                                $lan = 'en';
                                $data['html_file'] = $html_file;
                                $data['language']  = $lan;
                            } else if($mstatus == "Blocked"){
                                // send mail for rejected......
                                $html_file ="Business-Blocked";
                                $lan = 'en';
                                $data['html_file'] = $html_file;
                                $data['language']  = $lan;
                            } else {
                                // send mail for rejected......
                                $html_file ="Business-Rejected";
                                $lan = 'en';
                                $data['html_file'] = $html_file;
                                $data['language']  = $lan;
                            }
                                $mail_status = $this->SendmailNew($data);
                    } else {
                        $MasterTypeItemsId = $masterTypeItems->getMasterTypeItemsByName('Pending Approval');
                        if(!empty($MasterTypeItemsId)){
                            $status = $MasterTypeItemsId['MSTT_ID'];
                        }
                    }        
                       
                    $criteria1 = new CDbCriteria;
                    $criteria1->addCondition("BUS_ID =".$bus_id); 
                    Businesses::model()->updateAll( array('BUS_Status'=>$status),$criteria1);
             
                    if(isset($_POST['role_id'])) {
                        $user_role=Roles::model()->findbyPk($_POST['role_id']);
                        $role_name = $user_role->MLR_Name;           
                        $user_name=Users::model()->findbyPk($userID);
                     
                        if($comments!='')
                            $comments = "Comments -". $comments;
		     
                        if($_POST['status']=='Active'){
                            $reason = $user_name->USR_FirstName." ".$user_name->USR_LastName."(".$role_name.") has been approved the business. ".$comments;
                            $reason_desc = "Status changed by ". $user_name->USR_FirstName." ".$user_name->USR_LastName."(".$role_name.")";
                            $app_status = 220;
                            $req_status= "Active";
                            $message = Yii::t('app','Business has been approved successfully');
                        } else if($_POST['status']=='Inactive'){
                            $reason = $user_name->USR_FirstName." ".$user_name->USR_LastName."(".$role_name.") has been blocked the business. ".$comments;
                            $reason_desc = "Status changed by ". $user_name->USR_FirstName." ".$user_name->USR_LastName."(".$role_name.")";
                            $app_status = 221;
                            $req_status= "Business blocked / rejected";
                            $message = Yii::t('app','Business has been blocked / rejected successfully');
                        } else {
                            $reason = $user_name->USR_FirstName." ".$user_name->USR_LastName."(".$role_name.") has been rejected the business. ".$comments;
                            $reason_desc = "Status changed by ". $user_name->USR_FirstName." ".$user_name->USR_LastName."(".$role_name.")";
                            $app_status = 221;
                            $req_status= "Question asked";
                            $message = Yii::t('app','Question asked to business admin');
                        }
                        Yii::app()->user->setFlash('success',$message);
                     
                        $userId          =  $userID;
                        $getUserName     =  $this->getUserName($userId); 
                        $audit           =  new Functions(); 
                        $activity        =  'Status Changed';
                        $description     =  $reason;
                        $descriptionDevp =  $reason_desc;
                        $submodid        =  ' ';
			$oldvalue        =  "<li>Pending Approval</li>";
                        $newvalue        =  "<li>$req_status</li>";
                        $objtype         =  'Business';
                        $objkey          =  $bus_id;
                        $setAudit        =  $audit->userAuditOther($description,$activity,$descriptionDevp,$submodid,$oldvalue,$newvalue,$objtype,$objkey);
                    }
      
                    /******************user Audit start*******************/
                    $sql1      = "SELECT a.BUS_Name,b.MSTT_Name FROM ML_Businesses a, ML_Master_Type_Items b WHERE a.BUS_ID=$bus_id AND b.MSTT_ID = a.BUS_Status";
                    $audit1    = Yii::app()->db->createCommand($sql1)->queryRow();
                    if(!empty($audit1))
                    {
                        $busTitle  = $audit1['BUS_Name'];
                        $busStatus = $audit1['MSTT_Name'];
                    }
                    $audit     = new Functions(); 
                    $activity  = "Business Status changed";
                    $description = $busTitle ." has been changed to ". $busStatus ." status";
                    $descriptionDevp = $bus_id."##".$status;
                    $setAudit  = $audit->userAudit($description,$activity,$descriptionDevp);
                   /****************user Audit End************************/
                }
                
                /*** Added by Vaiju ***/
           
        }
        
        public function actionApproval($id) {
            $busID = $id;
            $masterTypesItems = new MasterTypeItems;
            $MasterTypeItemsId = $masterTypesItems->getMasterTypeItemsByName('Pending Approval');
            $status = $MasterTypeItemsId['MSTT_ID'];
            $criteria1 = new CDbCriteria;
            $criteria1->addCondition("BUS_ID =".$busID); 
            if(Businesses::model()->updateAll( array('BUS_Status'=>$status),$criteria1)) {
                $roles = new Roles();
                $sytemAdminData = $roles->getSystemAdminID("Sys Admin");
                $systemAdminID = $sytemAdminData['MLR_ID'];
                $siteUserRoles = new SiteUserRoles();
                $userRole = $siteUserRoles->getSytemAdminID($systemAdminID);
                $adminID = $userRole['SUR_USR_ID'];
                $users = new Users();
                $userData = $users->getUserData($adminID);
                $userLanguage = $userData['USR_lang_preferred'];
                if($userLanguage == "") {
                    $userLanguage = "en";
                }
                if(isset($email)) {
                    $from   = $email;
                    $to     = 'admin@mylokals.com';
                    $asset  = $assetTitle; 
                    $mstatus = 'Route for Approval';
                    $url = " ";
                    $html_file ="Asset-Create";
                    $lan = $userLanguage;
                    $toEmail =Yii::app()->params['admin_email'];
                    $data = array();
                    $data[] = "";

                    $data['from_email'] = $from;
                    $data['language'] = $lan;
                    $data['to_email'] = $toEmail;
                    $data['data'] = $url;
                    $data['asset'] = $asset;
                    $data['html_file'] = $html_file;
                    $data['url'] = $url;
                    $data['name'] = $name;
                    $this->SendmailToAdminNew($data);
                }
                //echo "Before email function";exit;
                $st =Yii::t('app','Status Changed');
                $df_pd =Yii::t('app','Draft to Pending Approval');
                $change =Yii::t('app','Business status has been changed from Draft to Pending Approval');
                $draft = Yii::t('app','Draft');
                $pa   = Yii::t('app','Pending Approval');
                //if($this->SendmailToAdminNew($data)) {
                
                    $userId          =  Yii::app()->user->id;
                    $getUserName     =  $this->getUserName($userId); 
                    $audit           =  new Functions(); 
                    $activity        =  $st;
                    $description     =  $df_pd;
                    $descriptionDevp =  $change;
                    $submodid        =  ' ';
                    $oldvalue        =  $draft;
                    $newvalue        =  $pa;
                    $objtype         =  'Business';
                    $objkey          =  $busID;
                    $setAudit        =  $audit->userAuditOther($description,$activity,$descriptionDevp,$submodid,$oldvalue,$newvalue,$objtype,$objkey);
                    
                    $this->redirect(array('Edit_business','id'=>$busID));
                //}
            }
        }
        
	public function actioncheckbusinessuserexist(){
		$this->layout = false;
		$userid = $_GET['uid'];
		$busid = $_GET['bid'];
		$checkuser = SiteUserRolesEmployee::model()->find('SUR_USR_ID=:SUR_USR_ID && ML_BUS_ID=:ML_BUS_ID',array(':SUR_USR_ID'=>$userid,':ML_BUS_ID'=>$busid));
		if(count($checkuser)>0){
			$temp['valid'] = true;
			echo json_encode($temp);
		}else{
			$temp['valid'] = false;
			echo json_encode($temp);
		}
	}
	public function actiongetEmployeePrivilege(){
		$this->layout = false;
		if(isset($_GET['usrid']) && !empty($_GET['usrid']) && isset($_GET['busid']) && !empty($_GET['busid'])){
			$usrid = $_GET['usrid'];
			$busid = $_GET['busid'];
			$getprivilege = SiteUserRolesEmployee::getemployeeprivilege($usrid,$busid);
			
			$this->render('getemployeeprivilege',array('data'=>$getprivilege));
		}else{
			echo('something\'s went wrong');
		}
	}
	public function actiongetEmployeeOtherLocation(){
		$this->layout = false;
		$usrid = $_GET['usrid'];
		if(!empty($_GET['ptid'])){
			$busid = $_GET['ptid'];
		}else{
			$busid = $_GET['busid'];
		}
		$childbus = Businesses::getChildBusiness($busid);
		if(!empty($childbus)){
			$temp = array();
			$checkuserbus = SiteUserRolesEmployee::getUserFromBusiness($childbus,$usrid);		
			foreach($checkuserbus as $value){
				$temp[] = $value['ML_BUS_ID'];
			}	
			$otherlocation = UserLocations::getbusinesslocationname($temp);
		}else{
			$otherlocation = '';
		}
		$this->render('getEmployeeOtherLocation',array('otherlocation'=>$otherlocation));
	}
	public function actionassignprivilege(){
		$cur_user_id = yii::app()->user->id;
		$busid  = $_GET['id'];
		$temp = array();
		$auth = Yii::app()->authorization;
		$rites = $auth->checkAdminRights($cur_user_id);	
		if($rites == 1){
			$getroles = Roles::getrolesfromexception(array('Normal User','Sys Admin'));
			$temp = CHtml::listData($getroles,'MLR_ID','MLR_Name');
		}else{			
			$getparentbus = Businesses::getParentBusiness($busid);
			$getuserroles = SiteUserRolesEmployee::getUserFromBusiness($getparentbus,$cur_user_id);
			foreach($getuserroles as $value){
				if(isset($_SESSION['lang'])){ 
					$lang = $_SESSION['lang'];
					 }else{
						$lang = "en";
					 }
					 if($lang == 'en'){		
						  $temp[$value->SUR_MLR_ID] = $value->Role()->MLR_Name;
						 }
						 else if($lang == 'kn'){
							 $temp[$value->SUR_MLR_ID] = $value->Role()->MLR_Name_kn;
						 }else if($lang == 'te'){
								 $temp[$value->SUR_MLR_ID] = $value->Role()->MLR_Name_te;
						 }else if($lang == 'hi'){
									 $temp[$value->SUR_MLR_ID] = $value->Role()->MLR_Name_hi;
						 }
			}
		}	
		
		/* Code Begins For Business User Existing Privileges */
		$model = new SiteUserRolesEmployee();	
		$buscat = BusinessCategories::getBusinessCategories($busid);
		/* Code Ends For Business User Existing Privileges */
		if(isset($_GET['id'])){				
				$getbusstatus = Businesses::getbusstatus($_GET['id']);				
			}else{
				$getbusstatus = '';
			}  
			
			$auth = Yii::app()->authorization;
			$rites = $auth->checkAdminRights($cur_user_id);		
		$this->render('assignprivilege',array('rolelist'=>$temp,'model'=>$model,'busid'=>$busid,'bcat'=>$buscat,'busstatus'=>$getbusstatus,'rites'=>$rites));
		
	}
	public function actionassignPrivilegeForUser(){
		$cur_user_id = yii::app()->user->id;
		$busid = $_POST['business'];
		$role = $_POST['role'];
		$usrid = $_POST['uid'];	
		$sc = 0;
		$fc = 0;
		$msg = array();
		$getautorole = AutoAssignRoles::getAssignedRoles($role);
		$getatutoroleid = CHtml::listData($getautorole,'MLAR_Auto_Role_Id','MLAR_Auto_Role_Id');
		array_push($getatutoroleid,$role);
		//~ echo '<pre>';print_r($getatutoroleid);echo'</pre>';exit;
		$locid = UserLocations::getbusinesslocationid($busid);
		//~ $roles = Roles::getRoles('Normal User');		
		//~ $checkuserfistprivilege = SiteUserRolesEmployee::checkuserfirstprivilege($usrid,$busid,$roles->MLR_ID);
		$getparentbus = Businesses::getParentBusiness($busid);
		$checkuserexist = SiteUserRolesEmployee::getuserbasedonrole($usrid,$getparentbus,$getatutoroleid);
		$checkuserexistroleid = CHtml::listData($checkuserexist,'SUR_MLR_ID','SUR_MLR_ID');
		//echo '<pre>';print_r($checkuserexistroleid);echo'</pre>';exit;
		foreach($getatutoroleid as $rolevalue){
			if(!in_array($rolevalue,$checkuserexistroleid)){ echo $rolevalue.'-first';continue;
				$getemailid = UserEmails::getuserdefaultemailid($usrid);
				$add = SiteUserRolesEmployee::adduserprivilege($usrid,$busid,$rolevalue,$getemailid,$cur_user_id,$locid);
				if($add['valid'] == 1){
					if($checkuserfistprivilege){
						SiteUserRolesEmployee::updateuserfirstprivilege($usrid,$busid,$roles->MLR_ID);
					}
					$sc++;
					//~ $msg['message'] = 'User have been assign privilege successfully';
				}else{
					$fc++;
					//~ $msg['message'] = 'something went wrong';
				}
			}else{ echo $rolevalue.'-second';continue;
				$fc++;
				//~ $msg['message'] = 'This user already Assigned to particular role for buiness';
			}
		}
		if($fc < 1){
			$msg['message'] = 'User have been assign privilege successfully';
		}else{
			$msg['message'] = 'This user already Assigned to particular role for buiness';
		}
		echo json_encode($msg);
	}
	public function actionassignPrivilegeCatForUser(){
		$cur_user_id = yii::app()->user->id;
		$cat = $_POST['category'];
		$busid = $_POST['business'];
		$rid = $_POST['rid'];
		$userid = $_POST['uid'];		
		$msg =  array();
		$role = Roles::getUserRole($rid);
		$getsite = SiteUserRolesEmployee::getuserbasedonrole($userid,$busid,$rid);		
		$sc = 0; 
		$fc = 0;
		if(empty($getsite)){ 
			$getemailid = UserEmails::getuserdefaultemailid($userid);
			$locid = UserLocations::getbusinesslocationid($busid);
			if($role['MLR_Name'] == 'User Business Administrator'){
				$getAllSite = SiteUserRolesEmployee::getUserAllRoles($userid,$busid);
				$getAllSiteId = CHtml::listData($getAllSite,'SUR_ID','SUR_MLR_ID');
				$getAllSiteId_flip = array_flip($getAllSiteId);
				$temp[] = 'User Business Administrator';
				$temp[] = 'User Location Administrator';
				$temp[] = 'User Customer Manager';
				$temp[] = 'User Category Administrator';
				$roles = Roles::getRoles($temp);				
				foreach($roles as $value){
					if(in_array($value->MLR_ID,$getAllSiteId)){
						if($value->MLR_Name == 'User Business Administrator' || $value->MLR_Name == 'User Category Administrator'){
							$add = SiteUserRolesEmployee::adduserprivilege($userid,$busid,$value->MLR_ID,$getemailid,$cur_user_id,$locid);
							if($add['valid'] == 1){
								foreach($cat as $catvalue){
									$add_one = SiteUserCategories::addBusinessCategoryPrivilege($catvalue,$getAllSiteId_flip[$value->MLR_ID],$cur_user_id);
									if($add_one['valid'] == 1){
										$sc++;
									}else{
										$fc++;
									}
								}		
							}
						}
					}else{
						$add = SiteUserRolesEmployee::adduserprivilege($userid,$busid,$value->MLR_ID,$getemailid,$cur_user_id,$locid);
						if($value->MLR_Name == 'User Business Administrator'||$value->MLR_Name == 'User Category Administrator'){
							if($add['valid'] == 1){
								foreach($cat as $catvalue){
									$add_one = SiteUserCategories::addBusinessCategoryPrivilege($catvalue,$add['id'],$cur_user_id);
									if($add_one['valid'] == 1){
										$sc++;
									}else{
										$fc++;
									}
								}		
							}				
						}elseif($value->MLR_Name == 'User Location Administrator'||$value->MLR_Name == 'User Customer Manager'){
							if($add['valid'] == 1){
								$sc++;
							}else{
								$fc++;
							}
						}
					}		
				}	
			}elseif($role['MLR_Name'] == 'User Category Administrator'){
				$add = SiteUserRolesEmployee::adduserprivilege($userid,$busid,$rid,$getemailid,$cur_user_id,$locid);
				if($add['valid'] == 1){
					foreach($cat as $value){
						$add_one = SiteUserCategories::addBusinessCategoryPrivilege($value,$add['id'],$cur_user_id);
						if($add_one['valid'] == 1){
							$sc++;
						}else{
							$fc++;
						}
					}
				}
			}
			
		}else{ 
			$getsiteid = CHtml::listData($getsite,'SUR_MLR_ID','SUR_ID');
			$getcat = SiteUserCategories::getcatbasedonrole($getsiteid);
			$getcatid = CHtml::listData($getcat,'MLC_ACT_ID','MLC_ACT_ID');
			$getemailid = UserEmails::getuserdefaultemailid($userid);
			$locid = UserLocations::getbusinesslocationid($busid);			
			if($role['MLR_Name'] == 'User Business Administrator'){				
				$getAllSite = SiteUserRolesEmployee::getUserAllRoles($userid,$busid);
				$getAllSiteId = CHtml::listData($getAllSite,'SUR_ID','SUR_MLR_ID');
				$getAllSiteId_flip = array_flip($getAllSiteId);
				$temp[] = 'User Business Administrator';
				$temp[] = 'User Location Administrator';
				$temp[] = 'User Customer Manager';
				$temp[] = 'User Category Administrator';
				$roles = Roles::getRoles($temp);
				foreach($roles as $value){
					if(in_array($value->MLR_ID,$getAllSiteId)){
						if($value->MLR_Name == 'User Business Administrator' || $value->MLR_Name == 'User Category Administrator'){
							$add = SiteUserRolesEmployee::adduserprivilege($userid,$busid,$value->MLR_ID,$getemailid,$cur_user_id,$locid);
							if($add['valid'] == 1){
								foreach($cat as $catvalue){
									$add_one = SiteUserCategories::addBusinessCategoryPrivilege($catvalue,$getAllSiteId_flip[$value->MLR_ID],$cur_user_id);
									if($add_one['valid'] == 1){
										$sc++;
									}else{
										$fc++;
									}
								}		
							}
						}						
					}else{
						if($value->MLR_Name == 'User Business Administrator' || $value->MLR_Name == 'User Category Administrator'){
							$add = SiteUserRolesEmployee::adduserprivilege($userid,$busid,$value->MLR_ID,$getemailid,$cur_user_id,$locid);
							if($add['valid'] == 1){
								foreach($cat as $catvalue){
									$add_one = SiteUserCategories::addBusinessCategoryPrivilege($catvalue,$add['id'],$cur_user_id);
									if($add_one['valid'] == 1){
										$sc++;
									}else{
										$fc++;
									}
								}		
							}
						}else{
							$add = SiteUserRolesEmployee::adduserprivilege($userid,$busid,$value->MLR_ID,$getemailid,$cur_user_id,$locid);
							if($add['valid'] == 1){
								$sc++;
							}else{
								$fc++;
							}				
						}						
					}
				}				
			}elseif($role['MLR_Name'] == 'User Category Administrator'){ 
				
				foreach($cat as $value){
					if(!in_array($value,$getcatid)){ 
						$getparent = Category::getParentCategory($value);
						$getchild = Category::getChildCategory($value);
						$getchild = array_diff($getchild,array($value));
						$checkparentassigned = SiteUserCategories::getBusinessCategories($getsiteid,$getparent);
						$checkchildassigned = SiteUserCategories::getBusinessCategories($getsiteid,$getchild);
						$getemailid = UserEmails::getuserdefaultemailid($userid);
						if(count($checkparentassigned) >0){
							$fc++;
						}elseif(count($checkchildassigned)>0){
							$add_one = SiteUserCategories::addBusinessCategoryPrivilege($value,$getsiteid[$rid],$cur_user_id);
							if($add_one['valid'] == 1){
								SiteUserCategories::deactivateBusinessCategoryPrivilege($getsiteid[$rid],$getchild);
								$sc++;
							}else{
								$fc++;
							}
						}else{
							$add_one = SiteUserCategories::addBusinessCategoryPrivilege($value,$getsiteid[$rid],$cur_user_id);
							if($add_one['valid'] == 1){								
								$sc++;
							}else{
								$fc++;
							}
						}						
					}else{
						$fc++;
					}
				}
			}
		}
		
		$msg['message'] = 'total success '.$sc.' and failed '.$fc;
		echo json_encode($msg);
	}
	public function actiongetcategoryanduserbasedrole(){
		$this->layout =  false;
		$cur_user_id = yii::app()->user->id;
		$rid = $_GET['rid'];
		$busid = $_GET['bid'];
		$role = Roles::getUserRole($rid);
		$getbuscat = '';
		$userjson = '';
		if($role['MLR_Category_Based'] == 1){
			$getcatfromrole = SiteUserRolesEmployee::getuserbasedonrole($cur_user_id,$busid,$rid);
			$getsiteid = CHtml::listData($getcatfromrole,'SUR_ID','SUR_ID');	
			$getcat = SiteUserCategories::getcatbasedonrole($getsiteid);
			$getcatid = CHtml::listData($getcat,'MLC_ACT_ID','MLC_ACT_ID');
			$getcat = array();
			foreach($getcatid  as $value){
				$child = array();
				$getcat[] = Category::getchildcat($value,$child);				
			}
			$result_arr = array();
			foreach ($getcat as $sub_arr) 
				$result_arr = array_merge($result_arr, $sub_arr);		
			
			$getbuscat = BusinessCategories::getbuscatfromllist($busid,$result_arr);
		}else{
			$getadminuserid = array();
			$getusers = SiteUserRolesEmployee::getuserbasedonexceptrole($busid,$rid);
			$getusersid = CHtml::listData($getusers,'SUR_USR_ID','SUR_USR_ID');				
			array_push($getadminuserid,$cur_user_id); 
			$userlist = array_diff($getusersid,$getadminuserid); 
			$users = Users::getuesrbyid($userlist);
			$user = array();
			$temp = array();
			foreach($users as $k=>$value){
				$userLocation = $value->userLocations[0];
				$country      = $userLocation->country->CNT_Name;
				$location     = $userLocation->LCN_City." ".$userLocation->LCN_State." ".$country;
				$temp['value']       = $value['USR_ID'];
				$temp['text']     = $value['USR_FirstName'].' '.$value['USR_LastName'];
				$temp['description'] = $location;
				$temp['imageSrc']    = Users::getUserPic($value['USR_ID']);
				$user[] = $temp;
			}			
			$userjson =  CJSON::encode($user);			
		}

		$this->render('getcategoryanduserbasedrole',array('buscat'=>$getbuscat,'role_name'=>$role,'userjson'=>$userjson));
	}
	public function actiongetuserbasedonrole(){
		$this->layout =  false;
		$cur_user_id = yii::app()->user->id;
		$role = $_GET['role'];
		$busid = $_GET['business'];
		$cat = $_GET['category'];
		$userjson = '';
		if(!empty($cat)){
		$getBusSite = SiteUserRolesEmployee::getBusSite($busid);
		$getbussiteid = CHtml::listData($getBusSite,'SUR_ID','SUR_ID');
		$ex_cat = array();
		foreach($cat as $cvalue){
			$ex_cat_obj = SiteUserCategories::getexclusivetcat($getbussiteid,$cvalue);
			$ex_cat[] = CHtml::listData($ex_cat_obj,'MLC_SUR_ID','MLC_SUR_ID');;
		}	
		
		$result_arr = array();
		foreach ($ex_cat as $sub_arr) 
			$result_arr = array_merge($result_arr, $sub_arr);
		
		$getexrole = SiteUserRolesEmployee::getsitebasedonexceptrole($busid,$role);
		$getexroleid = CHtml::listData($getexrole,'SUR_ID','SUR_ID');		
		$usersite =   array_merge($result_arr, $getexroleid);
		$getusers = SiteUserRolesEmployee::getsite($usersite);		
		$getusersid = CHtml::listData($getusers,'SUR_USR_ID','SUR_USR_ID');		
		$roles = Roles::getRoles('User Business Administrator');
		$getadminusers = SiteUserRolesEmployee::getadminusers($busid,$roles->MLR_ID);
		$getadminuserid = CHtml::listData($getadminusers,'SUR_USR_ID','SUR_USR_ID');
		array_push($getadminuserid,$cur_user_id); 
		$userlist = array_diff($getusersid,$getadminuserid);
		$users = Users::getuesrbyid($userlist);
			$user = array();
			$temp =  array();
			foreach($users as $k=>$value){
				$userLocation = $value->userLocations[0];
				$country      = $userLocation->country->CNT_Name;
				$location     = $userLocation->LCN_City." ".$userLocation->LCN_State." ".$country;
				$temp['value']       = $value['USR_ID'];
				$temp['text']     = $value['USR_FirstName'].' '.$value['USR_LastName'];
				$temp['description'] = $location;
				$temp['imageSrc']    = Users::getUserPic($value['USR_ID']);
				$user[] = $temp;
			}			
			$userjson =  CJSON::encode($user);
		}
		$this->render('getuserbasedrole',array('userjson'=>$userjson));
	}


    public function actionEmployeesold() {
        $session_id = Yii::app()->session['id'];
        $row = null;
        $urow = null;
        $auth_array = array();
        if (isset($_GET['loc_id'])) {

            /* ----  Authorization check  */
            $auth_array = array();
            $obj_Bus_emp = Objects::model()->findByAttributes(array('BJ_Name' => 'Business Emplyoee'));
            $obj_business_id = $_GET['id'];
            $auth = Yii::app()->authorization;
            $auth_array['Bus_emp_edit_access'] = $auth->canEdit(Yii::app()->session['id'], $obj_Bus_emp->BJ_ID, 1, 1, $obj_business_id, 0);
            $auth_array['Bus_emp_add_access'] = $auth->canCreate(Yii::app()->session['id'], $obj_Bus_emp->BJ_ID, 1, 1, $obj_business_id, 0);
            $auth_array['Bus_emp_delete_access'] = $auth->canDelete(Yii::app()->session['id'], $obj_Bus_emp->BJ_ID, 1, 1, $obj_business_id, 0);
            $auth_array['Bus_emp_approve_access'] = $auth->canApprove(Yii::app()->session['id'], $obj_Bus_emp->BJ_ID, 1, 1, $obj_business_id, 0);
            /* ----  Authorization check  */
            $loc_Id = $_GET['loc_id'];
            $bus_Id = $_GET['id'];
            $siteUser = new SiteUserRoles();
            $row = $siteUser->get_userFromLocation($bus_Id, $loc_Id);
        }
        if (isset($_POST['usr_id'])) {
            $usr_Id = $_POST['usr_id'];
            $usql = "SELECT DISTINCT(ML_Businesses.BUS_Name),ML_User_Locations.LCN_Name, ML_Site_UserRoles.SUR_StartDate,concat(ass_by.USR_FirstName,' ',ass_by.USR_LastName) as assby_name , ML_Site_UserRoles.SUR_endDate, concat(dea_by.USR_FirstName,' ',dea_by.USR_LastName) as deaby_name,ML_Site_UserRoles.SUR_USR_ID,
              ass_by.USR_ID as ASS_BY_USR_ID,dea_by.USR_ID as DEA_BY_USR_ID,ML_Site_UserRoles.SUR_USR_ID,ML_Site_UserRoles.ML_BUS_ID,ML_Site_UserRoles.ML_LCN_ID
              FROM ML_Site_UserRoles 
              LEFT JOIN ML_User_Locations ON  ML_User_Locations.LCN_ID = ML_Site_UserRoles.ML_LCN_ID
              LEFT JOIN ML_Businesses ON ML_Businesses.BUS_ID = ML_Site_UserRoles.ML_BUS_ID             
              LEFT JOIN ML_Users as ass_by ON ass_by.USR_ID = ML_Site_UserRoles.SUR_AssignedBy
              LEFT JOIN ML_Users as dea_by ON dea_by.USR_ID = ML_Site_UserRoles.SUR_DeactivatedBy
              WHERE ML_Site_UserRoles.SUR_USR_ID = $usr_Id
              AND ML_Businesses.BUS_Status = '220'";
            $umodels = Yii::app()->db->createCommand($usql);
            $udataReader = $umodels->query();
            $urow = $udataReader->readAll();
        }
        if (isset($_POST['busvalue'])) {
            $busvalue = $_POST['busvalue'];
        } else {
            $busvalue = null;
        }
        $this->render('employees', array('row' => $row, /* urow'=>$urow,'mi'=>$_POST['mi'],'busvalue'=>$busvalue,'auth'=>$auth_array */));
    }

    public function actionAssign_privilege() {
        $loc_Id = $_GET['loc_id'];
        $bus_id = $_GET['id'];
        $user_id = Yii::app()->user->id;
        $site = new SiteUserRoles();
        $data = $site->get_user_Business_Roles($bus_id);
        $result = array();
        $role_detail = Roles::model()->find('MLR_Name=:MLR_Name', array(':MLR_Name' => 'User Business Administrator'));
        $is_bus_admin = SiteUserRoles::model()->find('SUR_USR_ID=:SUR_USR_ID && ML_BUS_ID=:ML_BUS_ID && SUR_MLR_ID=:SUR_MLR_ID', array(':SUR_USR_ID' => $user_id, ':ML_BUS_ID' => $bus_id, ':SUR_MLR_ID' => $role_detail['MLR_ID']));
        foreach ($data as $key => $value) {
            if (isset($is_bus_admin)) {
                $result[$key]['Role_name'] = $value['MLR_Name'];
                $result[$key]['Role_id'] = $value['MLR_ID'];
            } else {
                $role_data = SiteUserRoles::model()->find('SUR_MLR_ID=:SUR_MLR_ID && ML_BUS_ID=:ML_BUS_ID && SUR_USR_ID=:SUR_USR_ID', array(':SUR_MLR_ID' => $value['MLR_ID'], ':ML_BUS_ID' => $bus_id, ':SUR_USR_ID' => $user_id));
                $cat_role_detail = Roles::model()->find('MLR_Name=:MLR_Name', array(':MLR_Name' => 'User Category Administrator'));
                if (empty($role_data)) {
                    $result[$key]['Role_name'] = $value['MLR_Name'];
                    $result[$key]['Role_id'] = $value['MLR_ID'];
                } else if ($value['MLR_ID'] == $cat_role_detail['MLR_ID']) {
                    $result[$key]['Role_name'] = $value['MLR_Name'];
                    $result[$key]['Role_id'] = $value['MLR_ID'];
                }
            }
        }
        $this->render('assign_privilege', array('roles_dropdown' => $result));
    }

    public function actionbusiness_location_category_role_based() {
        $this->layout = false;
        $category_array = null;

        if (isset($_POST['id'])) {

            $result = array();

            $role_id = $_POST['id'];
            $bus_id = $_POST['bus_id'];
            $bus_location = $_POST['bus_location'];
            $userid = Yii::app()->user->id;

            $object = Objects::model()->find('BJ_Name=:BJ_Name', array(':BJ_Name' => 'Business Category'));

            $object_id = $object['BJ_ID'];

            $category_access_role = RolePermissions::model()->find('MLRP_MLR_ID=:MLRP_MLR_ID && MLRP_CanRead=:MLRP_CanRead && MLRP_BJ_ID=:MLRP_BJ_ID', array(':MLRP_MLR_ID' => $role_id, ':MLRP_CanRead' => 1, ':MLRP_BJ_ID' => $object_id));
            if (isset($category_access_role)) {

                $role_detail = Roles::model()->find('MLR_Name=:MLR_Name', array(':MLR_Name' => 'User Business Administrator'));

                $is_bus_admin = SiteUserRoles::model()->find('SUR_USR_ID=:SUR_USR_ID && ML_BUS_ID=:ML_BUS_ID && SUR_MLR_ID=:SUR_MLR_ID', array(':SUR_USR_ID' => $userid, ':ML_BUS_ID' => $bus_id, ':SUR_MLR_ID' => $role_detail['MLR_ID']));
                //print_r($is_bus_admin);die;
                if (isset($is_bus_admin)) {

                    $sql = "SELECT ML_Business_Categories.MBC_ACT_ID FROM ML_Business_Categories WHERE  MBC_BUS_ID =$bus_id and MBC_LCN_ID =$bus_location";
                    $category_array = Yii::app()->db->createCommand($sql)->queryAll();
                } else {
                    $role_data = SiteUserRoles::model()->find('SUR_MLR_ID=:SUR_MLR_ID && ML_BUS_ID=:ML_BUS_ID && SUR_USR_ID=:SUR_USR_ID', array(':SUR_MLR_ID' => $role_id, ':ML_BUS_ID' => $bus_id, ':SUR_USR_ID' => $userid));
                    if (!empty($role_data)) {
                        $type = 'child';
                    } else {
                        $type = 'parent';
                    }
                    $site_role = new SiteUserRoles();
                    $cat_str_data = $site_role->get_role_parent_categories_list($bus_id, $role_id, $type);
                    $catarray = explode(',', $cat_str_data);
                    foreach ($catarray as $key => $value) {
                        $category_array[]['MBC_ACT_ID'] = $value;
                    }
                }
                $this->render('business_category_menu_for_roles', array('list' => $category_array));
            } else {
                echo 'fail';
                exit();
            }
        }
    }

    public function actionAutocomplete() {
        $query = $_POST['query'];
        $users = array();

        $sql = 'SELECT * FROM ML_Users 
                RIGHT JOIN ML_User_Locations ON ML_User_Locations.LCN_REF_OBJ_KEY = ML_Users.USR_ID 
                LEFT JOIN ML_Countries ON ML_Countries.CNT_Code = ML_User_Locations.LCN_Country_id 
                WHERE ML_User_Locations.LCN_IsDefault=1 AND ML_User_Locations.LCN_REF_OBJ_TYPE=2 AND ML_User_Locations.LCN_Status=1 AND ML_Users.USR_FirstName LIKE "%' . $query . '%" OR ML_Users.USR_LastName LIKE "%' . $query . '%" limit 7';
        
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $usersRs = $command->queryAll();

        // $usersRs = Users::model()->with('userLocations')->findAll(array('condition'=>'USR_FirstName LIKE "%'.$query.'%" OR USR_LastName LIKE "%'.$query.'%"','limit'=>5));
        foreach ($usersRs as $k => $user) {
            $country = $user['CNT_Name'];
            $location = $user['LCN_City'] . " " . $user['LCN_State'] . " " . $country;
            $users['group'][$k]['id'] = $user['USR_ID'];
            $users['group'][$k]['name'] = $user['USR_FirstName'] . ' ' . $user['USR_LastName'];
            $users['group'][$k]['location'] = $location;
            $users['group'][$k]['image'] = Users::getUserPic($user['USR_ID']);
        }
        echo CJSON::encode($users);
    }

    public function actionadd_BusinessUser() {
        //to check whether which protocol it matches http or https
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = "https://";
        } else {
            $protocol = "http://";
        }
        $auth = Yii::app()->authorization;

        $cur_user_id = Yii::app()->user->id;
        if (isset($_POST['uid'])) {
            $u_id = Users::model()->find('USR_ID =:USR_ID', array(':USR_ID' => $_POST['uid']));
            $busid = $_POST['bus_id'];
            $locid = $_POST['loc_id'];
            $userid = $_POST['uid'];
            //echo '<pre>';print_r($email_id);echo'</pre>';

            if (count($u_id) > 0) { //for existing user
                $siteusrrole = SiteUserRoles::model()->find('SUR_USR_ID=:SUR_USR_ID && ML_BUS_ID=:ML_BUS_ID && ML_LCN_ID=:ML_LCN_ID', array(':SUR_USR_ID' => $userid, ':ML_BUS_ID' => $busid, ':ML_LCN_ID' => $locid));
                if (!count($siteusrrole) > 0) { //To check user assigned or not
                    $userId = $userid;

                    $u_mail = UserEmails::model()->find('UEM_USR_ID =:UEM_USR_ID && UEM_Status=:UEM_Status', array(':UEM_USR_ID' => $userid, ':UEM_Status' => 1));

                    if (!empty($u_mail)) {
                        $u = $u_mail->UEM_Email;
                    } else {
                        $u = ' ';
                    }
                    $user_data = Users::model()->findByAttributes(array('USR_ID' => $userId));
                    $buinessname = Businesses::model()->findByPk($busid = $_POST['bus_id']);
                    $rites = $auth->checkAdminRights($cur_user_id);
                    if ($buinessname->BUS_Type == 3) {
                        if ($rites == 1) {
                            $getrid = Roles::model()->find('MLR_Name =:MLR_Name', array(':MLR_Name' => 'Mylokal Employee'));
                            $mlrid = $getrid->MLR_ID;
                        } else {
                            $getrid = Roles::model()->find('MLR_Name =:MLR_Name', array(':MLR_Name' => 'Normal User'));
                            $mlrid = $getrid->MLR_ID;
                        }
                    } else {
                        $getrid = Roles::model()->find('MLR_Name =:MLR_Name', array(':MLR_Name' => 'Normal User'));
                        $mlrid = $getrid->MLR_ID;
                    }

                    $siteuserrole = new SiteUserRoles;
                    $siteuserrole->SUR_Status = 2;
                    $siteuserrole->SUR_USR_ID = $userid;
                    $siteuserrole->SUR_MLR_ID = $mlrid;
                    //$siteuserrole->SUR_StartDate  = date("Y-m-d H:i:s");
                    $siteuserrole->SUR_AssignedBy = $cur_user_id;
                    $siteuserrole->ML_LCN_ID = $locid;
                    $siteuserrole->ML_BUS_ID = $busid;
//				$siteuserrole->ML_UEM_ID      = $u_mail->UEM_Email; 
                    $siteuserrole->ML_UEM_ID = $u;
                    $siteuserrole->save(false);



                    $model = new InviteFormats;
                    $model->MIF_Name = "email";
                    $model->MIF_Description = "email";
                    $model->MIF_Mail_Subject = "Invitation to assign employee by location";
                    $model->MIF_Mail_Body = "Invitation to assign employee by location";
                    $model->MIF_Applicability = 1;
                    $model->MIF_Status = 1;
                    $model->MIF_CreatedBy = Yii::app()->user->id;
                    $model->MIF_CreatedOn = date('Y-m-d H:i:s');
                    if (!$model->validate())
                        throw new Exception("Validation failed.");
                    $model->save(false);
                    //echo '<pre>';print_r($user_data);die;
                    $model1 = new NewInvites;
                    $model1->MNU_FirstName = $user_data['USR_FirstName'];
                    $model1->MNU_LastName = $user_data['USR_LastName'];
                    $model1->MNU_BusinessName = $buinessname['BUS_Name'];
                    $model1->MNU_Email = $u_mail->UEM_Email;
                    $model1->MNU_Status = 1;
                    $model1->MNU_CreatedBy = Yii::app()->user->id;
                    $model1->MNU_Source = $busid;
                    $model1->MNU_Source_ID = $siteuserrole->SUR_ID;
                    $model1->MNU_Invitation_Status = 0;
                    $model1->MNU_Invitation_SentOn = date('Y-m-d H:i:s');
                    $model1->MNU_Comments = "";
                    $model1->MNU_MIF_Id = $model->MIF_Id;

                    $invite_first_name = $user_data['USR_FirstName'];
                    $invite_last_name = $user_data['USR_LastName'];
                    $invite_business_name = $buinessname['BUS_Name'];
                    $invite_email = $u_mail->UEM_Email;
                    $invite_status = 1;
                    $invite_created = Yii::app()->user->id;
                    $invite_source_id = $siteuserrole->SUR_ID;
                    $invite_status = 0;
                    $invite_sent_on = date('Y-m-d H:i:s');
                    $invite_mif_id = $model->MIF_Id;

                    /* if(!$model1->validate())
                      throw new Exception("Validation failed.");
                      $model1->save();
                     */
                    $sql_invite = "INSERT INTO `ML_New_Invites`(`MNU_FirstName`, `MNU_LastName`, `MNU_BusinessName`, `MNU_Email`, `MNU_Status`, `MNU_CreatedBy`,`MNU_Source`, `MNU_Source_ID`, `MNU_Invitation_Status`, `MNU_Invitation_SentOn`, `MNU_Comments`, `MNU_MIF_Id`)"
                            . " VALUES ('$invite_first_name', '$invite_last_name', '$invite_business_name', '$invite_email', '$invite_status', '$invite_created','$busid','$invite_source_id','$invite_status','$invite_sent_on','','$invite_mif_id')";
                    $command_invite = Yii::app()->db->createCommand($sql_invite);
                    if ($command_invite->execute()) {
                        $from = 'Test.Master@mylokals.com';
                        $to = $u_mail->UEM_Email;
                        $subject = 'Invitation for assigning business role';
                        $host = $_SERVER['SERVER_NAME'];
                        $current = $_SERVER['PHP_SELF'];

                        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $randomString = '';
                        for ($i = 0; $i < 3; $i++) {
                            $randomString .= $characters[rand(0, strlen($characters) - 1)];
                        }
                        $phrase = (string) $model1->MNU_ID;
                        $numerics = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
                        $chars = array("z", "y", "x", "w", "v", "u", "t", "s", "r", "q");
                        $newphrase = str_replace($numerics, $chars, $phrase);
                        $newphrase_rand = (string) $randomString . $newphrase;
                        $usrlocation = UserLocations::model()->findByPk($_POST['loc_id']);
                        $url = $protocol . $host . Yii::app()->request->baseUrl . "/businesses/businesses/get_BusinessUserDetails?pid=" . $newphrase_rand;
                        $message = "Hi&nbsp;" . $user_data['USR_FirstName'] . "," . "<br/><br/>" . "You have been assigned to &nbsp;" . $usrlocation['LCN_Name'] . "-" . $buinessname['BUS_Name'] . "&nbsp; location.<br><br><a href='$url'>If you are interested please click the link,</a><br/><br/>@" . date("Y") . " Mylokal Corporation";
                        $headers = 'MIME-Version: 1.0' . "\n" . 'Content-type: text/html; charset=iso-8859-1' . "\n";
                        $headers .= "X-Mailer: PHP \n";
                        $headers .= "From: <" . $from . ">";

                        $verification = new Functions;
                        $verification->mailsend($to, $from, $subject, $message);
                    } else {
                        
                    }
                }
            } else { //for non existing user
            }
        }
        if (isset($_POST['usr_id'])) {

            $usrlocation = UserLocations::model()->findByPk($_POST['loc_id']);

            $model = new SiteUserRoles;
            $model->SUR_Status = 1;
            $model->SUR_USR_ID = $_POST['usr_id'];
            $model->SUR_MLR_ID = 3;
            $model->SUR_StartDate = date("Y-m-d H:i:s");
            $model->SUR_AssignedBy = $cur_user_id;
            $model->ML_LCN_ID = $_POST['loc_id'];
            $model->ML_BUS_ID = $_POST['bus_id'];
            $model->ML_UEM_ID = $_POST['eid'];
            $model->save(false);
        }
    }

    public function actioncheck_UserExist() {

        if (isset($_POST['uid'])) {
            $u_id = Users::model()->find('USR_ID =:USR_ID', array(':USR_ID' => $_POST['uid']));
            if (count($u_id) > 0) {
                $usrid = $_POST['uid'];
                $busid = $_POST['bus_id'];
                $locid = $_POST['loc_id'];
                $siteuserrole = SiteUserRoles::model()->find('SUR_USR_ID =:SUR_USR_ID && ML_LCN_ID =:ML_LCN_ID && ML_BUS_ID=:ML_BUS_ID', array(':SUR_USR_ID' => $usrid, ':ML_LCN_ID' => $_POST['loc_id'], ':ML_BUS_ID' => $_POST['bus_id']));
                if (count($siteuserrole) > 0) {
                    echo 'true';
                } else {
                    echo 'false';
                }
            } else {
                echo 'false';
            }
        }
        if (isset($_POST['usrid'])) {
            $siteuserrole = SiteUserRoles::model()->find('SUR_USR_ID =:SUR_USR_ID && ML_LCN_ID =:ML_LCN_ID && ML_BUS_ID=:ML_BUS_ID', array(':SUR_USR_ID' => $_POST['usrid'], ':ML_LCN_ID' => $_POST['loc_id'], ':ML_BUS_ID' => $_POST['bus_id']));
            if (count($siteuserrole) > 0) {
                echo 'true';
            } else {
                echo 'false';
            }
        }
    }

    public function actionSummary() {
        if (Yii::app()->request->isAjaxRequest) {
            $this->layout = false;
        }
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
        } else {
            $id = Yii::app()->session['id'];
        }
        if (isset(Yii::app()->session['id']) && !isset($_GET['url'])) {
            //personal Info data--jayanthi
            $usrid = Yii::app()->session['id'];
            $personaldate = new Users;
            $personalinfo = $personaldate->getUserName($usrid);
            //socialinfo
            $usrid = Yii::app()->session['id'];
            //email info
            $user_email = new UserEmails;
            $email = $user_email->getEmailInfo($usrid);
            //phone info
            $user_phone = new UserPhones;
            $phone = $user_phone->getPhoneInfo($usrid);
            //location info
            $user_loc = new UserLocations;
            $loc = $user_loc->getContactInfo($usrid);
            //work info
            $user_work = new UserWork;
            $work = $user_work->getWorkInfo($usrid);
            if (!empty($work)) {
                foreach ($work as $key => $value) {

                    if ($value['UWK_current_status'] == 1 && $value['UWK_StartDate'] != '0000-00-00') {
                        $startdate = $this->date_filter($value['UWK_StartDate']);
                        $duration = $this->date_duration($value['UWK_StartDate'], date('Y-m'));
                        if ($duration) {
                            $duration = $duration;
                        } else {
                            $duration = '';
                        }
                        $period = $startdate . ' - Present ' . $duration;
                        $curperiod = 'Currently Working';
                    } else if ($value['UWK_current_status'] == 1 && $value['UWK_StartDate'] == '0000-00-00') {
                        $period = '';
                        $curperiod = 'Currently Working';
                    } else if ($value['UWK_StartDate'] != '0000-00-00' && $value['UWK_EndDate'] != '0000-00-00') {
                        $startdate = $this->date_filter($value['UWK_StartDate']);
                        $enddate = $this->date_filter($value['UWK_EndDate']);
                        $duration = $this->date_duration($value['UWK_StartDate'], $value['UWK_EndDate']);
                        if ($duration) {
                            $duration = $duration;
                        } else {
                            $duration = '';
                        }
                        $period = $startdate . ' - ' . $enddate . ' ' . $duration;
                        $curperiod = '';
                    } else if ($value['UWK_StartDate'] != '0000-00-00' && $value['UWK_EndDate'] == '0000-00-00') {
                        $startdate = $this->date_filter($value['UWK_StartDate']);
                        $period = $startdate;
                        $curperiod = '';
                    } else if ($value['UWK_StartDate'] == '0000-00-00' && $value['UWK_EndDate'] != '0000-00-00') {
                        $enddate = $this->date_filter($value['UWK_EndDate']);
                        $period = $enddate;
                        $curperiod = '';
                    } else {
                        $period = '';
                        $curperiod = '';
                    }
                    $work[$key]['period'] = $period;
                    $work[$key]['curperiod'] = $curperiod;
                }
            }


            //college info
            $user_college = new UserEducation;
            $getcol = $user_college->getCollegeInfo($usrid);

            if (!empty($getcol)) {
                if ($getcol['UED_Current_Status'] == 1 && $getcol['UED_StartDate'] != '0000-00-00') {
                    $startdate = $this->date_filter($getcol['UED_StartDate']);
                    $duration = $this->date_duration($getcol['UED_StartDate'], date('Y-m'));
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - Present ' . $duration;
                    $curperiod = 'Currently studying';
                } else if ($getcol['UED_Current_Status'] == 1 && $getcol['UED_StartDate'] == '0000-00-00') {
                    $period = '';
                    $curperiod = 'Currently studying';
                } else if ($getcol['UED_StartDate'] != '0000-00-00' && $getcol['UED_EndDate'] != '0000-00-00') {
                    $startdate = $this->date_filter($getcol['UED_StartDate']);
                    $enddate = $this->date_filter($getcol['UED_EndDate']);
                    $duration = $this->date_duration($getcol['UED_StartDate'], $getcol['UED_EndDate']);
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - ' . $enddate . ' ' . $duration;
                    $curperiod = '';
                } else if ($getcol['UED_StartDate'] != '0000-00-00' && $getcol['UED_EndDate'] == '0000-00-00') {
                    $startdate = $this->date_filter($getcol['UED_StartDate']);
                    $period = $startdate;
                    $curperiod = '';
                } else if ($getcol['UED_StartDate'] == '0000-00-00' && $getcol['UED_EndDate'] != '0000-00-00') {
                    $enddate = $this->date_filter($getcol['UED_EndDate']);
                    $period = $enddate;
                    $curperiod = '';
                } else {
                    $period = '';
                    $curperiod = '';
                }
                $getcol['period'] = $period;
                $getcol['curperiod'] = $curperiod;
            }

            //school info
            $user_scl = new UserEducation;
            $getscl = $user_scl->getSchoolInfo($usrid);
            if (!empty($getscl)) {
                if ($getscl['UED_Current_Status'] == 1 && $getscl['UED_StartDate'] != '0000-00-00') {
                    $startdate = $this->date_filter($getscl['UED_StartDate']);
                    $duration = $this->date_duration($getscl['UED_StartDate'], date('Y-m'));
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - Present ' . $duration;
                    $curperiod = 'Currently studying';
                } else if ($getscl['UED_Current_Status'] == 1 && $getscl['UED_StartDate'] == '0000-00-00') {
                    $period = '';
                    $curperiod = 'Currently studying';
                } else if ($getscl['UED_StartDate'] != '0000-00-00' && $getscl['UED_EndDate'] != '0000-00-00') {
                    $startdate = $this->date_filter($getscl['UED_StartDate']);
                    $enddate = $this->date_filter($getscl['UED_EndDate']);
                    $duration = $this->date_duration($getscl['UED_StartDate'], $getscl['UED_EndDate']);
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - ' . $enddate . ' ' . $duration;
                    $curperiod = '';
                } else if ($getscl['UED_StartDate'] != '0000-00-00' && $getscl['UED_EndDate'] == '0000-00-00') {
                    $startdate = $this->date_filter($getscl['UED_StartDate']);
                    $period = $startdate;
                    $curperiod = '';
                } else if ($getscl['UED_StartDate'] == '0000-00-00' && $getscl['UED_EndDate'] != '0000-00-00') {
                    $enddate = $this->date_filter($getscl['UED_EndDate']);
                    $period = $enddate;
                    $curperiod = '';
                } else {
                    $period = '';
                    $curperiod = '';
                }
                $getscl['period'] = $period;
                $getscl['curperiod'] = $curperiod;
            }

            //child info
            $user_child = new UserChild;
            $getchild = $user_child->getChildCount($usrid);
            //business
            $frendbusiness = new Businesses();
            $getfrendbusiness = $frendbusiness->getExistbusiness($usrid);

            /*             * ****************user Audit start****************** */

            $audit = new Functions();
            $activity = "User Profile Summary";
            $description = "Himanshu  Singh viewed summary";
            $descriptionDevp = $usrid . "Himanshu  Singh viewed summary";
            $setAudit = $audit->userAudit($description, $activity, $descriptionDevp);
            /*             * **************user Audit End******************* */


            $this->render('summary', array('getchild' => $getchild, 'email' => $email, 'phone' => $phone, 'loc' => $loc, 'getcol' => $getcol, 'getscl' => $getscl, 'data' => $work, 'id' => $id, "personalinfo" => $personalinfo, "busasoc" => $getfrendbusiness));
        } else if (isset($_GET['id']) && isset($_GET['url'])) {
            $usrid = Yii::app()->session['id'];
            $id = $_GET['id'];
            $newphrase_rand = $id;
            $newphrase_rand1 = substr($newphrase_rand, 3);
            $numerics = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
            $chars = array("z", "y", "x", "w", "v", "u", "t", "s", "r", "q");
            $newphrase1 = str_replace($chars, $numerics, $newphrase_rand1);
            $idd = (int) $newphrase1;
            $id = $idd;
            //personal info
            $sql = "SELECT ML_Users.USR_ID as keyid,ML_Users.USR_FirstName as FirstName ,ML_Users.USR_LastName as LastName,ML_Users.USR_nick_name as NickName,ML_Users.USR_Date_Of_Birth as birthmonth,ML_Users.USR_Date_Of_Birth as birthyear,ML_Users.USR_Aboutme as Aboutme FROM ML_Users WHERE ML_Users.USR_ID = $id ";
            $getpersonal = Yii::app()->db->createCommand($sql)->query()->readAll();
            $getsocialsql = "SELECT ML_Users.USR_ID as keyid,ML_Users.USR_Aboutme as Aboutme FROM ML_Users WHERE  ML_Users.USR_ID = $id ";
            $getsocial = Yii::app()->db->createCommand($getsocialsql)->query()->readAll();
            /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
            $sql = "SELECT  ML_User_Locations.LCN_House_number as HouseNumber,
					ML_User_Locations.LCN_Street_name as Streetname,ML_User_Locations.LCN_Street_direction 
					as StreetOptional,ML_User_Locations.LCN_City as City,
					ML_User_Locations.LCN_State as State,ML_User_Locations.LCN_ZipCode 
					as Zipcode,ML_User_Locations.LCN_Geo_country as Country,
					ML_User_Locations.LCN_Name as Locationname,ML_User_Locations.LCN_ID as keyid ,
                                        ML_User_Locations . * 
                                        FROM ML_User_Locations
                                        WHERE ML_User_Locations.LCN_Created_By =$id
                                        AND ML_User_Locations.LCN_IsDefault =1
                                        ORDER BY ML_User_Locations.LCN_ID ASC
                                        LIMIT 0 , 1";

            $data = Yii::app()->db->createCommand($sql)->queryAll();



            //phon info

            $sqlphone = "SELECT ML_User_Phones.UPH_ID as keyid,ML_User_Phones.UPH_Phone as Phone, ML_User_Phones.UPH_Country_Code, ML_User_Phones.UPH_IsDefault, ML_User_Phones.UPH_USR_ID, ML_Countries.CNT_Code, ML_Countries.CNT_CON_FLAG_NAME,ML_Countries.CNT_Phone_Code FROM ML_User_Phones
						INNER JOIN ML_Countries ON ML_User_Phones.UPH_Country_Code = ML_Countries.CNT_Code
						WHERE  UPH_USR_ID =$id  AND UPH_Status = 1 AND UPH_IsDefault = 1";
            $getphone = Yii::app()->db->createCommand($sqlphone)->queryAll();




            $emailsql = "SELECT UEM_ID as keyid,UEM_Email as Email,UEM_Email_Type FROM ML_User_Emails WHERE UEM_Status =1 AND UEM_USR_ID =$id AND UEM_IiDefault = 1";
            $getemail = Yii::app()->db->createCommand($emailsql)->queryAll();


            $sql = "SELECT ML_User_Work.UWK_ID as keyid,ML_User_Work.UWK_Role as Role,ML_User_Work.UWK_current_status, ML_Businesses.BUS_Name as workedcompany,ML_User_Locations.LCN_Name as workedlocation,CONCAT(ML_User_Locations.LCN_Geo_city,',',ML_User_Locations.LCN_Geo_region,',',ML_User_Locations.LCN_Geo_country) as workedlocation,ML_Positions.MLP_name as workedposition,ML_User_Work.UWK_StartDate as workedfrom,ML_User_Work.UWK_EndDate as workedupto FROM ML_User_Work
						LEFT JOIN ML_Businesses ON ML_User_Work.UWK_Company = ML_Businesses.BUS_ID 
						LEFT JOIN ML_User_Locations ON ML_Businesses.BUS_ID = ML_User_Locations.LCN_BUS_ID
						LEFT JOIN ML_Positions ON ML_User_Work.UWK_Title = ML_Positions.MLP_id						
						WHERE ML_User_Work.UWK_USR_ID = $id ";
            $work = Yii::app()->db->createCommand($sql)->query()->readAll();
            foreach ($work as $key => $value) {

                if ($value['UWK_current_status'] == 1 && $value['workedfrom'] != '0000-00-00') {
                    $startdate = $this->date_filter($value['workedfrom']);
                    $duration = $this->date_duration($value['workedfrom'], date('Y-m'));
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - Present ' . $duration;
                    $curperiod = 'Currently Working';
                } else if ($value['UWK_current_status'] == 1 && $value['workedfrom'] == '0000-00-00') {
                    $period = '';
                    $curperiod = 'Currently Working';
                } else if ($value['workedfrom'] != '0000-00-00' && $value['workedupto'] != '0000-00-00') {
                    $startdate = $this->date_filter($value['workedfrom']);
                    $enddate = $this->date_filter($value['workedupto']);
                    $duration = $this->date_duration($value['workedfrom'], $value['workedupto']);
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - ' . $enddate . ' ' . $duration;
                    $curperiod = '';
                } else if ($value['workedfrom'] != '0000-00-00' && $value['workedupto'] == '0000-00-00') {
                    $startdate = $this->date_filter($value['workedfrom']);
                    $period = $startdate;
                    $curperiod = '';
                } else if ($value['workedfrom'] == '0000-00-00' && $value['workedupto'] != '0000-00-00') {
                    $enddate = $this->date_filter($value['workedupto']);
                    $period = $enddate;
                    $curperiod = '';
                } else {
                    $period = '';
                    $curperiod = '';
                }
                $work[$key]['period'] = $period;
                $work[$key]['curperiod'] = $curperiod;
            }
            $sql = "SELECT ML_Businesses.BUS_Name as college,ML_Education.MLD_name as Degree,ML_User_Locations.LCN_Name as CollegeLocation,CONCAT(ML_User_Locations.LCN_Geo_city,',',ML_User_Locations.LCN_Geo_region,',',ML_User_Locations.LCN_Geo_country) as CollegeLocation,BUS_Slug,ML_User_Education.UED_EndDate as collegestudiedupto,ML_User_Education.UED_ID as keyid,ML_User_Education.UED_StartDate as collegestudiedfrom,UED_Current_Status	
					  FROM ML_Businesses
					  RIGHT JOIN ML_User_Education ON ML_User_Education.UED_Institution_id = ML_Businesses.BUS_ID
					  RIGHT JOIN ML_User_Locations ON ML_Businesses.BUS_ID = ML_User_Locations.LCN_REF_OBJ_KEY
					  RIGHT JOIN ML_Education ON ML_Education.MLD_id = ML_User_Education.UED_Degree_id
					  WHERE ML_User_Education.UED_USR_ID=$id AND ML_Education.MLD_type=1";
            //echo $sql ;exit;
            $models = Yii::app()->db->createCommand($sql);
            $dataReader = $models->query();
            $collegedata = $dataReader->readAll();
            //echo"<pre>";print_r($collegedata);exit;
            foreach ($collegedata as $key => $value) {
                if ($value['UED_Current_Status'] == 1 && $value['collegestudiedfrom'] != '0000-00-00') {
                    $startdate = $this->date_filter($value['collegestudiedfrom']);
                    $duration = $this->date_duration($value['collegestudiedfrom'], date('Y-m'));
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - Present ' . $duration;
                    $curperiod = 'Currently studying';
                } else if ($value['UED_Current_Status'] == 1 && $value['UED_StartDate'] == '0000-00-00') {
                    $period = '';
                    $curperiod = 'Currently studying';
                } else if ($value['collegestudiedfrom'] != '0000-00-00' && $value['collegestudiedupto'] != '0000-00-00') {
                    $startdate = $this->date_filter($value['collegestudiedfrom']);
                    $enddate = $this->date_filter($value['collegestudiedupto']);
                    $duration = $this->date_duration($value['collegestudiedfrom'], $value['collegestudiedupto']);
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - ' . $enddate . ' ' . $duration;
                    $curperiod = '';
                } else if ($value['collegestudiedfrom'] != '0000-00-00' && $value['collegestudiedupto'] == '0000-00-00') {
                    $startdate = $this->date_filter($value['UED_StartDate']);
                    $period = $startdate;
                    $curperiod = '';
                } else if ($value['collegestudiedfrom'] == '0000-00-00' && $value['collegestudiedupto'] != '0000-00-00') {
                    $enddate = $this->date_filter($value['collegestudiedupto']);
                    $period = $enddate;
                    $curperiod = '';
                } else {
                    $period = '';
                    $curperiod = '';
                }
                $collegedata[$key]['period'] = $period;
                $collegedata[$key]['curperiod'] = $curperiod;
            }
            $school_sql = "SELECT ML_Businesses.BUS_Name as School,ML_User_Locations.LCN_Name as SchoolLocation,CONCAT(ML_User_Locations.LCN_Geo_city,',',ML_User_Locations.LCN_Geo_region,',',ML_User_Locations.LCN_Geo_country) as SchoolLocation,BUS_Slug,ML_User_Education.UED_EndDate as Schoolstudiedupto,ML_User_Education.UED_ID as keyid,ML_User_Education.UED_StartDate as Shoolstudiedfrom,UED_Current_Status	
				  FROM ML_Businesses
				  RIGHT JOIN ML_User_Education ON ML_User_Education.UED_Institution_id = ML_Businesses.BUS_ID
				  RIGHT JOIN ML_User_Locations ON ML_Businesses.BUS_ID = ML_User_Locations.LCN_REF_OBJ_KEY
				 
				  WHERE ML_User_Education.UED_USR_ID=$id AND ML_User_Education.UED_Degree_id = 0";
            $school_models = Yii::app()->db->createCommand($school_sql);
            $school_dataReader = $school_models->query();
            $schooldata = $school_dataReader->readAll();
            // echo"<pre>";print_r($schooldata);exit;
            foreach ($schooldata as $key => $value) {
                if ($value['UED_Current_Status'] == 1 && $value['Shoolstudiedfrom'] != '0000-00-00') {
                    $startdate = $this->date_filter($value['Shoolstudiedfrom']);
                    $duration = $this->date_duration($value['Shoolstudiedfrom'], date('Y-m'));
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - Present ' . $duration;
                    $curperiod = 'Currently studying';
                } else if ($value['UED_Current_Status'] == 1 && $value['Shoolstudiedfrom'] == '0000-00-00') {
                    $period = '';
                    $curperiod = 'Currently studying';
                } else if ($value['Shoolstudiedfrom'] != '0000-00-00' && $value['Schoolstudiedupto'] != '0000-00-00') {
                    $startdate = $this->date_filter($value['Shoolstudiedfrom']);
                    $enddate = $this->date_filter($value['Schoolstudiedupto']);
                    $duration = $this->date_duration($value['Shoolstudiedfrom'], $value['Schoolstudiedupto']);
                    if ($duration) {
                        $duration = $duration;
                    } else {
                        $duration = '';
                    }
                    $period = $startdate . ' - ' . $enddate . ' ' . $duration;
                    $curperiod = '';
                } else if ($value['Shoolstudiedfrom'] != '0000-00-00' && $value['Schoolstudiedupto'] == '0000-00-00') {
                    $startdate = $this->date_filter($value['Shoolstudiedfrom']);
                    $period = $startdate;
                    $curperiod = '';
                } else if ($value['Shoolstudiedfrom'] == '0000-00-00' && $value['Schoolstudiedupto'] != '0000-00-00') {
                    $enddate = $this->date_filter($value['Schoolstudiedupto']);
                    $period = $enddate;
                    $curperiod = '';
                } else {
                    $period = '';
                    $curperiod = '';
                }
                $schooldata[$key]['period'] = $period;
                $schooldata[$key]['curperiod'] = $curperiod;
            }
            if ($usrid != $id) {

                $schooldata = $this->getprivacy_viewdata($schooldata, 12);
                $collegedata = $this->getprivacy_viewdata($collegedata, 2);
            }

//family
            $sql = "SELECT ML_User_Child.MUC_id as keyid, ML_Businesses.BUS_Name as childSchool,ML_User_Locations.LCN_Name as childSchoolLocation,CONCAT(MUC_first_name, ' ', MUC_last_name) as childname,MUC_birth as childbirthyear,MUC_academic_month as schoolyearend,MUC_child_class as childcurrentclass,MUC_child_level FROM ML_User_Child
				LEFT JOIN ML_Businesses ON ML_Businesses.BUS_ID = ML_User_Child.MUC_school_id
				LEFT JOIN ML_User_Locations ON ML_User_Locations.LCN_BUS_ID = ML_Businesses.BUS_ID
				WHERE ML_User_Child.MUC_USR_ID = $id";
            $getchild = Yii::app()->db->createCommand($sql)->query()->readAll();
            $user_array = Users::model()->findByPk($id);


            if ($usrid != $id) {
                $getpersonal = $this->getprivacy_viewdata($getpersonal, 7);
                $getsocial = $this->getprivacy_viewdata($getsocial, 9);
                $schooldata = $this->getprivacy_viewdata($schooldata, 12);
                $collegedata = $this->getprivacy_viewdata($collegedata, 2);
                $work = $this->getprivacy_viewdata($work, 1);
                $data = $this->getprivacy_viewdata($data, 5);

                $getemail = $this->getprivacy_viewdata($getemail, 4);
                $getphone = $this->getprivacy_viewdata($getphone, 3);
            }


            if (!empty(Yii::app()->request->cookies)) {
                $cookies = Yii::app()->request->cookies;
                //echo '<pre>';print_r($cookies['session:lang']->value);echo'</pre>';                exit;
                if (!empty($cookies['session:lang']->value))
                    $lang = $cookies['session:lang']->value;
                else
                    $lang = 'en';
            }else {
                $lang = 'en';
            }

            $this->render('freindsummary_view', array('lang' => $lang, 'child' => $getchild, 'school' => $schooldata, 'college' => $collegedata, 'data' => $getpersonal, 'model' => $user_array, 'phone' => $getphone, 'email' => $getemail, 'id' => $id, 'work' => $work, 'loc' => $data, 'social' => $getsocial));
        } else {
            //Yii::app()->request->redirect(Yii::app()->request->baseUrl.'/mLUsers/register');
        }
    }

    public function date_filter($value) {
        $formatedate = '';
        if ($value != '' || $value != NULL) {
            $year = substr($value, 0, 4);
            $month = substr($value, 5, 2);
            $day = substr($value, 8, 2);
            if ($year != 0000 && $month != 00 && $day != 00) {
                $formatedate = date('M-d-Y', strtotime($value));
            } else if ($year != 0000 && $month != 00 && $day == 00) {
                $temp = $year . '-' . $month . '-01';
                $formatedate = date('M-Y', strtotime($temp));
            } else if ($year != 0000 && $month == 00 && $day == 00) {
                $formatedate = $year;
            } else {
                $formatedate = '';
            }
        } else {
            $formatedate = '';
        }
        return $formatedate;
    }

    public function date_duration($from, $upto) {
        $ty1 = '';
        $ty2 = '';
        $fromyear = substr($from, 0, 4);
        $frommonth = substr($from, 5, 2);
        $fromday = substr($from, 8, 2);
        if ($fromyear != 0000 && $frommonth != 00 && $fromday != 00) {
            $date1 = $from;
            $ty1 = 1;
        } else if ($fromyear != 0000 && $frommonth != 00 && $fromday == 00) {
            $date1 = $fromyear . '-' . $frommonth . '-01';
            $ty1 = 2;
        } else if ($fromyear != 0000 && $frommonth == 00 && $fromday == 00) {
            $date1 = $fromyear . '-01-01';
            $ty1 = 3;
        } else {
            return false;
        }
        $uptoyear = substr($upto, 0, 4);
        $uptomonth = substr($upto, 5, 2);
        $uptoday = substr($upto, 8, 2);
        if ($uptoyear != 0000 && $uptomonth != 00 && $uptoday != 00) {
            $date2 = $upto;
            $ty2 = 1;
        } else if ($uptoyear != 0000 && $uptomonth != 00 && $uptoday == 00) {
            $date2 = $uptoyear . '-' . $uptomonth . '-01';
            $ty2 = 2;
        } else if ($uptoyear != 0000 && $uptomonth == 00 && $uptoday == 00) {
            $date2 = $uptoyear . '-01-01';
            $ty2 = 3;
        } else {
            return false;
        }

        $diff = abs(strtotime($date2) - strtotime($date1));

        $years = floor($diff / (365 * 60 * 60 * 24));
        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

        if ($ty1 == 1 && $ty2 == 1) {
            if ($years > 1) {
                $year = $years . ' Years';
            } else if ($year != 0) {
                $year = $years . ' Year';
            } else {
                $year = '';
            }
            if ($months > 1) {
                $month = $months . ' Months';
            } else if ($month != 0) {
                $month = $months . ' Month';
            } else {
                $month = '';
            }
            if ($days > 1) {
                $day = $days . ' Days';
            } else if ($days != 0) {
                $day = $days . ' Day';
            } else {
                $day = '';
            }
            $duration = '[ ' . $year . ' ' . $month . ' ' . $day . ']';
        } else if ($ty1 == 2 && $ty2 == 2) {
            if ($years > 1) {
                $year = $years . ' Years';
            } else if ($years != 0) {
                $year = $years . ' Year';
            } else {
                $year = '';
            }
            if ($months > 1) {
                $month = $months . ' Months';
            } else if ($months != 0) {
                $month = $months . ' Month';
            } else {
                $month = '';
            }
            //added by jayanthi when there is no difference in year and month    
            if (($years == 0) && ($months == 0)) {
                $duration = ' ';
            } else {
                $duration = '[ ' . $year . ' ' . $month . ']';
            }
        } else if ($ty1 == 3 && $ty2 == 3) {
            if ($years > 1) {
                $duration = '[ ' . $years . ' years]';
            } else if ($years != 0) {
                $duration = '[ ' . $years . ' year]';
            } else {
                $duration = '';
            }
        } else {
            if ($years > 1) {
                $duration = '[ ' . $years . ' years]';
            } else if ($years != 0) {
                $duration = '[ ' . $years . ' year]';
            } else {
                $duration = '';
            }
        }

        $temp = array();
        $temp['year'] = $years;
        $temp['month'] = $months;
        $temp['day'] = $days;
        $temp['type1'] = $ty1;
        $temp['type2'] = $ty2;
        return $duration;
    }

    public function actionget_BusinessUsersForRoles() {
        $this->layout = false;
        $loc_id = $_POST['loc_id'];
        $bus_id = $_POST['bus_id'];
        $role_id = $_POST['role_id'];
        $user_id = Yii::app()->user->id;
        $result['users'] = array();
        $res['users'] = array();
        if (isset($_POST['category']))
            $cat_str = implode(',', $_POST['category']);
        else
            $cat_str = "";
        $role_detail = Roles::model()->find('MLR_Name=:MLR_Name', array(':MLR_Name' => 'User Business Administrator'));
        $admin_fetch_query = "SELECT ML_Site_UserRoles.SUR_ID,ML_Users.USR_FirstName,ML_Users.USR_LastName,ML_Users.USR_ID
                FROM ML_Site_UserRoles
                RIGHT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
                RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
                WHERE    ML_Site_UserRoles.SUR_MLR_ID  = " . $role_detail['MLR_ID'] . " AND ML_Site_UserRoles.ML_LCN_ID =$loc_id
                AND ML_Site_UserRoles.ML_BUS_ID = $bus_id ";
        $admin_model_data = Yii::app()->db->createCommand($admin_fetch_query);
        $admin_Reader = $admin_model_data->query();
        $bus_admins = $admin_Reader->readAll();
        foreach ($bus_admins as $k => $admin_value) {
            if (!array_key_exists($admin_value['USR_ID'], $result['users'])) {

                $result['users'][$admin_value['USR_ID']]['User_id'] = $admin_value['USR_ID'];
            }
        }
        /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
        $query = "SELECT ML_Site_UserRoles.SUR_ID,ML_Users.USR_FirstName,ML_Users.USR_LastName,ML_Users.USR_ID ,ML_User_Locations.LCN_Name,ML_User_Locations.LCN_City
          FROM ML_Site_UserRoles
          RIGHT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
          RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
           RIGHT JOIN ML_User_Locations ON ML_User_Locations.LCN_Created_By = ML_Users.USR_ID
          WHERE    ML_Site_UserRoles.ML_LCN_ID =$loc_id
          AND ML_Site_UserRoles.ML_BUS_ID =$bus_id AND
          ML_User_Locations.LCN_IsDefault=1 AND ML_User_Locations.LCN_REF_OBJ_TYPE=2 AND ML_User_Locations.LCN_Status=1";

        $model_data = Yii::app()->db->createCommand($query);
        $Reader = $model_data->query();
        $data = $Reader->readAll();
        foreach ($data as $k => $datavalue) {
            if ((!array_key_exists($datavalue['USR_ID'], $result['users'])) && ($datavalue['USR_ID'] != $user_id)) {
                //  $func = new Functions();
                //    $url  = $func->getImageUrl($datavalue['USR_ID'],'User','width:40px;height:40px;','ProfileThumbPic');
                $res['users'][$datavalue['USR_ID']]['imageSrc'] = "";
                $type = "ProfileThumbPic";
                $origImage = UserLOBS::model()->find('LOB_Size_Type = "Original" AND Is_Profile_Pic=1 AND LOB_CreatedBy_USR_ID=' . $datavalue['USR_ID']);
                if ($origImage) {
                    $thumbPic = UserLOBS::model()->find('LOB_Size_Type = "' . $type . '" AND LOB_Parent_Id="' . $origImage['LOB_ID'] . '"');
                    $res['users'][$datavalue['USR_ID']]['imageSrc'] = Yii::app()->baseUrl . '/uploads/user_images/' . $datavalue['USR_ID'] . '/' . $thumbPic['LOB_ID'] . '.jpg';
                }
                if (empty($res['users'][$datavalue['USR_ID']]['imageSrc'])) {
                    $res['users'][$datavalue['USR_ID']]['imageSrc'] = Yii::app()->theme->baseUrl . '/images/avatar-user.png';
                }
                /* if(!empty($url)){
                  $res['users'][$datavalue['USR_ID']]['imageSrc'] = $url['image'];
                  }else{
                  $res['users'][$datavalue['USR_ID']]['imageSrc'] = Yii::app()->request->baseUrl.'/images/no_photo.jpg';
                  } */
                $res['users'][$datavalue['USR_ID']]['text'] = $datavalue['USR_FirstName'] . ' ' . $datavalue['USR_LastName'];
                $res['users'][$datavalue['USR_ID']]['value'] = intval($datavalue['USR_ID']);
                $res['users'][$datavalue['USR_ID']]['selected'] = false;
                $res['users'][$datavalue['USR_ID']]['description'] = $datavalue['LCN_City'];
                $res['users'][$datavalue['USR_ID']]['Sur_id'] = $datavalue['SUR_ID'];
            }
        }

        /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */

        if ($cat_str != "") {
            $query_val = "SELECT DISTINCT ML_Users.USR_ID,ML_Site_UserRoles.SUR_ID,ML_Users.USR_FirstName,ML_Users.USR_LastName ,ML_User_Locations.LCN_Name,ML_User_Locations.LCN_City
                      FROM ML_Site_User_Categories
                      LEFT JOIN ML_Site_UserRoles ON ML_Site_User_Categories.MLC_SUR_ID = ML_Site_UserRoles.SUR_ID
                      RIGHT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
                      RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
                      RIGHT JOIN ML_Category ON ML_Site_User_Categories.MLC_ACT_ID = ML_Category.ACT_ID
                      RIGHT JOIN ML_User_Locations ON ML_User_Locations.LCN_Created_By = ML_Users.USR_ID
                      WHERE  ML_Site_User_Categories.MLC_ACT_ID
                      IN (" . $cat_str . ") 
                      AND ML_Roles.MLR_ID =$role_id
                      AND ML_Site_UserRoles.ML_LCN_ID =$loc_id
                      AND ML_Site_UserRoles.ML_BUS_ID =$bus_id
                      AND ML_User_Locations.LCN_IsDefault=1 AND ML_User_Locations.LCN_REF_OBJ_TYPE=2 AND ML_User_Locations.LCN_Status=1 ";
        } else {
            $query_val = "SELECT DISTINCT ML_Users.USR_ID,ML_Site_UserRoles.SUR_ID,ML_Users.USR_FirstName,ML_Users.USR_LastName ,ML_User_Locations.LCN_Name,ML_User_Locations.LCN_City
                      FROM ML_Site_User_Categories
                      LEFT JOIN ML_Site_UserRoles ON ML_Site_User_Categories.MLC_SUR_ID = ML_Site_UserRoles.SUR_ID
                      RIGHT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
                      RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
                      RIGHT JOIN ML_User_Locations ON ML_User_Locations.LCN_Created_By = ML_Users.USR_ID
                      WHERE   ML_Roles.MLR_ID =$role_id
                      AND ML_Site_UserRoles.ML_LCN_ID =$loc_id
                      AND ML_Site_UserRoles.ML_BUS_ID =$bus_id AND
                       ML_User_Locations.LCN_IsDefault=1 AND ML_User_Locations.LCN_REF_OBJ_TYPE=2 AND ML_User_Locations.LCN_Status=1";
        }

        $models = Yii::app()->db->createCommand($query_val);
        $Reader_data = $models->query();
        $data_val = $Reader_data->readAll();

        foreach ($data_val as $key => $value) {
            if ((!array_key_exists($value['USR_ID'], $result['users'])) && (!array_key_exists($value['USR_ID'], $res['users'])) && ($value['USR_ID'] != $user_id)) {


                /* $lob=UserLOBS::model()->findByAttributes(array('LOB_CreatedBy_USR_ID'=>$value['USR_ID'],'Is_Profile_Pic'=>1,'LOB_REF_OBJ_TYPE'=>5)); */

                $func = new Functions();

                $url = $func->getImageUrl($value['USR_ID'], 'User', 'width:40px;height:40px;', 'ProfileThumbPic');



                if (!empty($url)) {
                    /* $res['users'][$value['USR_ID']]['imageSrc'] = 'data:image/png;charset=utf8;base64,'.base64_encode($lob->LOB_DATA); */
                    $res['users'][$value['USR_ID']]['imageSrc'] = $url['image'];
                } else {
                    $res['users'][$value['USR_ID']]['imageSrc'] = Yii::app()->request->baseUrl . '/images/no_photo.jpg';
                }

                $res['users'][$value['USR_ID']]['text'] = $value['USR_FirstName'] . ' ' . $value['USR_LastName'];
                $res['users'][$value['USR_ID']]['value'] = intval($value['USR_ID']);
                $res['users'][$value['USR_ID']]['selected'] = false;
                $res['users'][$value['USR_ID']]['description'] = $value['LCN_City'];
                $res['users'][$value['USR_ID']]['Sur_id'] = $value['SUR_ID'];
            } else {
                unset($res['users'][$value['USR_ID']]);
            }
        }

        $object = new stdClass();
        $i = 0;
        foreach ($res['users'] as $key => $value) {
            $object->$i = $value;
            $i++;
        }


        echo json_encode($object);
    }

    public function actionbusiness_location_category_menu() {
        $this->layout = false;
        $category_array = null;
        if (isset($_POST['bus_location']) && isset($_POST['bus_id'])) {
            $bus_location_id = $_POST['bus_location'];
            $bus_id = $_POST['bus_id'];
            $sql = "SELECT DISTINCT(MBC_ACT_ID) FROM ML_Business_Categories WHERE  MBC_BUS_ID =$bus_id and MBC_LCN_ID =$bus_location_id";
            $category_array = Yii::app()->db->createCommand($sql)->queryAll();
        }
        $this->render('business_category_menu', array('list' => $category_array));
    }

    public function actionget_UserRoles_for_all() {
        $this->layout = false;
        $loc_Id = $_POST['loc_id'];
        $bus_id = $_POST['bus_id'];
        $user_id = Yii::app()->session['id'];

        $site = new SiteUserRoles();
        $data = $site->get_user_Business_Roles($bus_id);

        $result = array();


        $role_detail = Roles::model()->find('MLR_Name=:MLR_Name', array(':MLR_Name' => 'User Business Administrator'));


        $is_bus_admin = SiteUserRoles::model()->find('SUR_USR_ID=:SUR_USR_ID && ML_BUS_ID=:ML_BUS_ID && SUR_MLR_ID=:SUR_MLR_ID', array(':SUR_USR_ID' => $user_id, ':ML_BUS_ID' => $bus_id, ':SUR_MLR_ID' => $role_detail['MLR_ID']));

        foreach ($data as $key => $value) {

            if (isset($is_bus_admin)) {
                $result[$key]['Role_name'] = $value['MLR_Name'];
                $result[$key]['Role_id'] = $value['MLR_ID'];
            } else {

                $role_data = SiteUserRoles::model()->find('SUR_MLR_ID=:SUR_MLR_ID && ML_BUS_ID=:ML_BUS_ID && SUR_USR_ID=:SUR_USR_ID', array(':SUR_MLR_ID' => $value['MLR_ID'], ':ML_BUS_ID' => $bus_id, ':SUR_USR_ID' => $user_id));

                $cat_role_detail = Roles::model()->find('MLR_Name=:MLR_Name', array(':MLR_Name' => 'User Category Administrator'));

                if (empty($role_data)) {

                    $result[$key]['Role_name'] = $value['MLR_Name'];
                    $result[$key]['Role_id'] = $value['MLR_ID'];
                } else if ($value['MLR_ID'] == $cat_role_detail['MLR_ID']) {

                    $result[$key]['Role_name'] = $value['MLR_Name'];
                    $result[$key]['Role_id'] = $value['MLR_ID'];
                }
            }
        }

        $this->render('business_user_roles_list_for_all', array('models' => $result));
    }

    public function actionget_Assigned_Business_Users() {
        $this->layout = false;
        $data = null;
        if (isset($_POST['bus_location']) && isset($_POST['bus_id'])) {

            $loc_Id = $_POST['bus_location'];
            $bus_Id = $_POST['bus_id'];
            if (isset($_POST['category'])) {
                $count = count($_POST['category']);
                $cat = $_POST['category'];
            }
            $result['users'] = array();
            if (!empty($cat)) {
                foreach ($cat as $val) {
                    $criteria = new CDbCriteria();
                    $criteria->addCondition("ACT_ID=" . $val);
                    $res = Category::model()->find($criteria);
                    $res5[] = $res['ACT_ID'];
                    if ($res['ACT_Parent_ACT_ID'] != 0) {
                        $criteria1 = new CDbCriteria();
                        $criteria1->addCondition("ACT_ID=" . $res['ACT_Parent_ACT_ID']);
                        $res1 = Category::model()->find($criteria1);
                        $res5[] = $res1['ACT_ID'];

                        if ($res1['ACT_Parent_ACT_ID'] != 0) {
                            $criteria2 = new CDbCriteria();
                            $criteria2->addCondition("ACT_ID=" . $res1['ACT_Parent_ACT_ID']);
                            $res2 = Category::model()->find($criteria2);
                            $res5[] = $res2['ACT_ID'];

                            if ($res2['ACT_Parent_ACT_ID'] != 0) {
                                $criteria3 = new CDbCriteria();
                                $criteria3->addCondition("ACT_ID=" . $res2['ACT_Parent_ACT_ID']);
                                $res3 = Category::model()->find($criteria3);
                                $res5[] = $res3['ACT_ID'];

                                if ($res3['ACT_Parent_ACT_ID'] != 0) {
                                    $criteria4 = new CDbCriteria();
                                    $criteria4->addCondition("ACT_ID=" . $res3['ACT_Parent_ACT_ID']);
                                    $res4 = Category::model()->find($criteria4);
                                    $res5[] = $res4['ACT_ID'];
                                }
                            }
                        }
                    }
                }
                $cat_str = implode(',', $res5);

                $sql = "SELECT  COUNT(*) AS total,ML_Users.USR_ID, ML_Users.USR_FirstName, ML_Users.USR_LastName, ML_Site_User_Categories.MLC_ACT_ID, ML_Site_User_Categories.MLC_SUR_ID, ML_Site_UserRoles.SUR_StartDate, ML_Site_UserRoles.SUR_endDate, ML_Site_UserRoles.SUR_AssignedBy, ML_Site_UserRoles.SUR_DeactivatedBy, ML_Roles.MLR_Name
          FROM ML_Site_User_Categories
          LEFT JOIN ML_Site_UserRoles ON ML_Site_User_Categories.MLC_SUR_ID = ML_Site_UserRoles.SUR_ID
          LEFT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
          RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
          RIGHT JOIN ML_Category ON ML_Site_User_Categories.MLC_ACT_ID = ML_Category.ACT_ID
          WHERE  ML_Site_User_Categories.MLC_ACT_ID
          IN (" . $cat_str . ")
          AND ML_Site_UserRoles.ML_LCN_ID =$loc_Id
          AND ML_Site_UserRoles.ML_BUS_ID =$bus_Id
          AND ML_Site_User_Categories.MLC_Status =1
          AND ML_Site_UserRoles.SUR_MLR_ID !=3 GROUP BY USR_ID HAVING total >=$count";
            } else {
                $sql = "SELECT  COUNT(*) AS total,ML_Users.USR_ID, ML_Users.USR_FirstName, ML_Users.USR_LastName, ML_Site_User_Categories.MLC_ACT_ID, ML_Site_User_Categories.MLC_SUR_ID, ML_Site_UserRoles.SUR_StartDate, ML_Site_UserRoles.SUR_endDate, ML_Site_UserRoles.SUR_AssignedBy, ML_Site_UserRoles.SUR_DeactivatedBy, ML_Roles.MLR_Name
          FROM ML_Site_User_Categories
          LEFT JOIN ML_Site_UserRoles ON ML_Site_User_Categories.MLC_SUR_ID = ML_Site_UserRoles.SUR_ID
          LEFT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
          RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
          WHERE  ML_Site_UserRoles.ML_LCN_ID =$loc_Id
          AND ML_Site_UserRoles.ML_BUS_ID =$bus_Id
          AND ML_Site_User_Categories.MLC_Status =1
          AND ML_Site_UserRoles.SUR_MLR_ID !=3 GROUP BY USR_ID";
            }


            $models = Yii::app()->db->createCommand($sql);
            $dataReader = $models->query();
            $data = $dataReader->readAll();

            foreach ($data as $key => $value) {
                if (!array_key_exists($value['USR_ID'], $result['users'])) {
                    $result['users'][$value['USR_ID']]['USR_FirstName'] = $value['USR_FirstName'];
                    $result['users'][$value['USR_ID']]['USR_LastName'] = $value['USR_LastName'];
                    $result['users'][$value['USR_ID']]['USR_ID'] = $value['USR_ID'];
                    $result['users'][$value['USR_ID']]['MLC_ACT_ID'] = $value['MLC_ACT_ID'];
                    $result['users'][$value['USR_ID']]['MLC_SUR_ID'] = $value['MLC_SUR_ID'];
                    $result['users'][$value['USR_ID']]['SUR_StartDate'] = $value['SUR_StartDate'];
                    $result['users'][$value['USR_ID']]['SUR_endDate'] = $value['SUR_endDate'];
                    $result['users'][$value['USR_ID']]['SUR_AssignedBy'] = $value['SUR_AssignedBy'];
                    $result['users'][$value['USR_ID']]['SUR_DeactivatedBy'] = $value['SUR_DeactivatedBy'];
                    $result['users'][$value['USR_ID']]['MLR_Name'] = $value['MLR_Name'];

                    if ($value['SUR_DeactivatedBy'] != 0) {
                        $User = Users::model()->findByAttributes(array('USR_ID' => $value['SUR_DeactivatedBy']));
                        $result['users'][$value['USR_ID']]['deactivated_user'] = $User['USR_FirstName'] . ' ' . $User['USR_LastName'];
                    }
                }
            }
            $this->render('business_location_assigned_users', array('row' => $result));
        }
    }

    public function actionupdate_Business_roles() {
        $this->layout = false;
        $role_id = $_POST['role_id'];
        $user_ids = $_POST['user_ids'];
        if (isset($_POST['category'])) {
            $cat = $_POST['category'];
            $cat_str = implode(',', $_POST['category']);
        }

        $loc_id = $_POST['loc_id'];
        $bus_id = $_POST['bus_id'];
        $session_id = Yii::app()->session['id'];
        $flag = true;
        $result = array();

        foreach ($user_ids as $key => $value) {
            $uid_val = $value['user_id'];

            if (!empty($cat_str)) {
                $query_str = "SELECT ML_Site_User_Categories.*
                                        FROM ML_Site_User_Categories
                                        LEFT JOIN ML_Site_UserRoles ON ML_Site_User_Categories.MLC_SUR_ID = ML_Site_UserRoles.SUR_ID
                                        LEFT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
                                        RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
                                        RIGHT JOIN ML_Category ON ML_Site_User_Categories.MLC_ACT_ID = ML_Category.ACT_ID
                                        WHERE  ML_Site_User_Categories.MLC_ACT_ID
                                        IN (" . $cat_str . ") 
                                        AND ML_Roles.MLR_ID =$role_id
                                        AND ML_Site_UserRoles.SUR_USR_ID =$uid_val
                                        AND ML_Site_UserRoles.ML_LCN_ID =$loc_id
                                        AND ML_Site_UserRoles.ML_BUS_ID =$bus_id";
            } else {
                $query_str = "SELECT ML_Site_User_Categories.*
                                        FROM ML_Site_User_Categories
                                        LEFT JOIN ML_Site_UserRoles ON ML_Site_User_Categories.MLC_SUR_ID = ML_Site_UserRoles.SUR_ID
                                        LEFT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
                                        RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
                                        RIGHT JOIN ML_Category ON ML_Site_User_Categories.MLC_ACT_ID = ML_Category.ACT_ID
                                        WHERE  ML_Roles.MLR_ID =$role_id
                                        AND ML_Site_UserRoles.SUR_USR_ID =$uid_val
                                        AND ML_Site_UserRoles.ML_LCN_ID =$loc_id
                                        AND ML_Site_UserRoles.ML_BUS_ID =$bus_id";
            }
            $model_val = Yii::app()->db->createCommand($query_str);
            $Reader_val = $model_val->query();
            $cat_selected_data_val = $Reader_val->readAll();

            if (!empty($cat_selected_data_val)) {
                $flag = false;
                $result['msg'] = "Role is already assigned to this user for this location!";
                $result['flag'] = $flag;
                echo json_encode($result);
                exit();
            }
        }

        if (!empty($cat)) {
            $higher_cat = $this->getHigherLevelCategories($cat);
        }

        if (!empty($higher_cat))
            $higher_cat_str = implode(',', $higher_cat);
        else
            $higher_cat_str = "";

        if ($higher_cat_str != "") {

            foreach ($user_ids as $key => $value) {
                $uid_val = $value['user_id'];
                $query_str = "SELECT ML_Site_User_Categories.MLC_ID
                    FROM ML_Site_User_Categories
                    LEFT JOIN ML_Site_UserRoles ON ML_Site_User_Categories.MLC_SUR_ID = ML_Site_UserRoles.SUR_ID
                    LEFT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
                    RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
                    RIGHT JOIN ML_Category ON ML_Site_User_Categories.MLC_ACT_ID = ML_Category.ACT_ID
                    WHERE  ML_Site_User_Categories.MLC_ACT_ID
                    IN (" . $higher_cat_str . ") 
                    AND ML_Roles.MLR_ID =$role_id
                    AND ML_Site_UserRoles.SUR_USR_ID =$uid_val
                    AND ML_Site_UserRoles.ML_LCN_ID =$loc_id
                    AND ML_Site_UserRoles.ML_BUS_ID =$bus_id";
                $model_val = Yii::app()->db->createCommand($query_str);
                $Reader_val = $model_val->query();
                $cat_higher_data_val = $Reader_val->readAll();

                if (!empty($cat_higher_data_val)) {
                    $flag = false;
                    $result['msg'] = "Role is already assigned to this user for this location!";
                    $result['flag'] = $flag;
                    echo json_encode($result);
                    exit();
                }
            }
        }

        if (!empty($cat)) {
            $lower_cat = $this->getLowerLevelCategories($cat);
        }
        if (!empty($lower_cat))
            $lower_cat_str = implode(',', $lower_cat);
        else
            $lower_cat_str = "";

        if ($lower_cat_str != "") {

            foreach ($user_ids as $key => $value) {
                $uid_val = $value['user_id'];
                $query_str = "SELECT ML_Site_User_Categories.MLC_ID
                    FROM ML_Site_User_Categories
                    LEFT JOIN ML_Site_UserRoles ON ML_Site_User_Categories.MLC_SUR_ID = ML_Site_UserRoles.SUR_ID
                    LEFT JOIN ML_Users ON ML_Users.USR_ID = ML_Site_UserRoles.SUR_USR_ID
                    RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID = ML_Site_UserRoles.SUR_MLR_ID
                    RIGHT JOIN ML_Category ON ML_Site_User_Categories.MLC_ACT_ID = ML_Category.ACT_ID
                    WHERE  ML_Site_User_Categories.MLC_ACT_ID
                    IN (" . $lower_cat_str . ") 
                    AND ML_Roles.MLR_ID =$role_id
                    AND ML_Site_UserRoles.SUR_USR_ID =$uid_val
                    AND ML_Site_UserRoles.ML_LCN_ID =$loc_id
                    AND ML_Site_User_Categories.MLC_Status=1
                    AND ML_Site_UserRoles.ML_BUS_ID =$bus_id";
                $model_val = Yii::app()->db->createCommand($query_str);
                $Reader_val = $model_val->query();
                $cat_lower_data_val = $Reader_val->readAll();
                if (!empty($cat_lower_data_val)) {
                    foreach ($cat_lower_data_val as $key => $value) {
                        SiteUserCategories::model()->updateByPk($value['MLC_ID'], array('MLC_Status' => 0, 'MLC_ReAssignedBy_USR_ID' => $session_id));
                    }
                }
            }
        }

        foreach ($user_ids as $key => $value) {
            $sur_id = "";
            $sur_role = "";
            $uid_val = $value['user_id'];
            $query_str1 = "SELECT ML_Site_UserRoles.SUR_ID,ML_Site_UserRoles.SUR_MLR_ID
                    FROM ML_Site_UserRoles
                    LEFT  JOIN ML_Users ON ML_Users.USR_ID   = ML_Site_UserRoles.SUR_USR_ID
                    RIGHT JOIN ML_Roles ON ML_Roles.MLR_ID   = ML_Site_UserRoles.SUR_MLR_ID
                    WHERE   ML_Site_UserRoles.SUR_USR_ID     =$uid_val
                    AND     ML_Site_UserRoles.ML_LCN_ID      =$loc_id
                    AND     ML_Site_UserRoles.ML_BUS_ID      =$bus_id
                    AND     ((ML_Site_UserRoles.SUR_MLR_ID   =$role_id) OR (ML_Site_UserRoles.SUR_MLR_ID =3))";

            $model_val = Yii::app()->db->createCommand($query_str1);
            $Reader_val = $model_val->query();
            $user_role_data = $Reader_val->read();

            if (empty($user_role_data)) {
                $query = "SELECT ML_Site_UserRoles.SUR_ID
                    FROM ML_Site_UserRoles
                    WHERE   ML_Site_UserRoles.SUR_USR_ID =$uid_val
                    AND ML_Site_UserRoles.ML_LCN_ID      =$loc_id
                    AND ML_Site_UserRoles.ML_BUS_ID      =$bus_id";
                $model_val = Yii::app()->db->createCommand($query);
                $Reader_val = $model_val->query();
                $user_role_data = $Reader_val->read();

                if (empty($user_role_data)) {
                    $flag = false;
                    $result['msg'] = "Selected User is not an Employee of this Business Location!";
                    $result['flag'] = $flag;
                    echo json_encode($result);
                    exit();
                } else {
                    $modelEmail = UserEmails::model()->find('UEM_USR_ID=:UEM_USR_ID && UEM_Status=:UEM_Status && UEM_IiDefault=:UEM_IiDefault', array(':UEM_USR_ID' => $uid_val, ':UEM_Status' => 1, ':UEM_IiDefault' => 1));


                    $model = new SiteUserRoles;
                    $model->SUR_Status = 1;
                    $model->SUR_USR_ID = $uid_val;
                    $model->SUR_MLR_ID = $role_id;
                    $model->SUR_StartDate = date("Y-m-d H:i:s");
                    $model->SUR_AssignedBy = $session_id;
                    $model->ML_LCN_ID = $loc_id;
                    $model->ML_UEM_ID = $modelEmail['UEM_ID'];
                    $model->ML_BUS_ID = $bus_id;
                    $model->save(false);
                    $sur_id = $model->SUR_ID;
                }
            } else {
                $sur_id = $user_role_data['SUR_ID'];
                $sur_role = $user_role_data['SUR_MLR_ID'];
            }

            if ($sur_id != "") {

                if ($sur_role == 3) {
                    SiteUserRoles::model()->updateByPk($sur_id, array('SUR_MLR_ID' => $role_id));
                }

                if (empty($cat)) {

                    $model = new SiteUserCategories;
                    $model->MLC_Status = 1;
                    $model->MLC_StartDate = date("Y-m-d H:i:s");
                    $model->MLC_EndDate = date("Y-m-d H:i:s");
                    $model->MLC_SUR_ID = $sur_id;
                    $model->MLC_CreatedBy_USR_ID = $session_id;
                    $model->MLC_CreatedOn = date("Y-m-d H:i:s");
                    $model->save(false);
                } else {
                    foreach ($cat as $key => $res_val) {
                        $model = new SiteUserCategories;
                        $model->MLC_Status = 1;
                        $model->MLC_StartDate = date("Y-m-d H:i:s");
                        $model->MLC_EndDate = date("Y-m-d H:i:s");
                        $model->MLC_ACT_ID = $res_val;
                        $model->MLC_SUR_ID = $sur_id;
                        $model->MLC_CreatedBy_USR_ID = $session_id;
                        $model->MLC_CreatedOn = date("Y-m-d H:i:s");
                        $model->save(false);
                    }
                }
                $flag = true;
                $result['msg'] = "Thank you, Role is assigned to an employee successfully.";
                $result['flag'] = $flag;
                echo json_encode($result);
                exit();
            }
        }
    }

    public function actionget_BusinessUserDetails() {


        if (isset($_GET['pid'])) {

            $newphrase_rand = $_GET['pid'];
            $newphrase_rand1 = substr($newphrase_rand, 3);
            $numerics = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
            $chars = array("z", "y", "x", "w", "v", "u", "t", "s", "r", "q");
            $newphrase1 = str_replace($chars, $numerics, $newphrase_rand1);
            $pid = (int) $newphrase1;

            $pid_data = NewInvites::model()->find('MNU_ID =:MNU_ID', array(':MNU_ID' => $pid));
            //$email_id = UserEmails::model()->find('UEM_Email =:UEM_Email',array(':UEM_Email'=>$pid_data['MNU_Email']));

            $checkrole = SiteUserRoles::model()->find('SUR_ID=:SUR_ID && SUR_Status=:SUR_Status', array(':SUR_ID' => $pid_data['MNU_Source_ID'], ':SUR_Status' => 1));
            if (!count($checkrole) > 0) {
                $busdetails = Businesses::model()->findByPk($pid_data['MNU_Source']);
                SiteUserRoles::model()->updateAll(array('SUR_Status' => 1, 'SUR_StartDate' => date("Y-m-d H:i:s")), 'SUR_ID =' . $pid_data['MNU_Source_ID']);
                NewInvites::model()->updateAll(array('MNU_Invitation_Status' => 1), 'MNU_ID =' . $pid);

                $this->redirect(array("businesses/businesses/edit_business/" . $busdetails['BUS_Slug']));
            } else {
                echo "You have already accepted invitation to this business location";
            }
        }
    }

    public function actionBusiness_history($id) {
            $model1=new UserAudit('Businessupdates');
            $model1->unsetAttributes();  // clear any default values
            if(isset($_GET['UserAudit']))
            $model1->attributes = $_GET['UserAudit'];
        
            $model=new Businesses;
            $busID = $id;
            //echo '<pre>';print_r($id);die;
            $user_audit = new UserAudit;
            $data = $user_audit->getAssetHistory($busID);
            $auth = Yii::app()->authorization;
            $object = new Objects();
            $userID = Yii::app()->user->id;
            $obj = $object->getDetailsByBJName("Business");
            $edit_access    = $auth->canEdit($userID,$obj['BJ_ID'],1,1,0,0);
            $add_access     = $auth->canCreate($userID,$obj['BJ_ID'],1,1,0,0);
            $delete_access  = $auth->canDelete($userID,$obj['BJ_ID'],1,1,0,0);
            $approve_access = $auth->canApprove($userID,$obj['BJ_ID'],1,1,0,0);
            $audit     = new Functions(); 
            $activity  = "Assets Updates-View";
            $description = "Updated Details have been viewed ";
            $descriptionDevp = "Updated Details have been viewed";
            $setAudit  = $audit->userAudit($description,$activity,$descriptionDevp);
			if(isset($_GET['id'])){				
				$getbusstatus = Businesses::getbusstatus($_GET['id']);				
			}else{
				$getbusstatus = '';
			}  
			$cur_user_id = yii::app()->user->id;
			$auth = Yii::app()->authorization;
			$rites = $auth->checkAdminRights($cur_user_id);
            $this->render('business_history',array(
                        'busID'=>$busID,
                        'model'=>$model,
                        'updates' => $data,
                      'busstatus'=>$getbusstatus,
                      'rites'=>$rites,
                      'model1'=>$model1
                ));
        }


    public function actionTerms() {

        $siteLobs = new Businesses();
        $tc_data = $siteLobs->getBusinessTermsData();
        $this->render('business_terms', array('tc_data' => $tc_data));
    }

    public function actionCheckBusinessName() {
        //echo $_GET['business_name'];

        if (!empty($_GET['business_name'])) {
            $busname = $_GET['business_name'];
            $getbusnameValue = Yii::app()->db->createCommand("SELECT * FROM `ML_Businesses` WHERE `BUS_Name` LIKE '$busname'")->queryRow();

            if (!empty($getbusnameValue)) {
                echo json_encode(true);
            } else {
                echo json_encode(false);
            }
        }
    }

    public function actionlocation_name_unique_check() {

        if (!empty($_GET['location_name'])) {
            $usrid = Yii::app()->user->id;
            $loc_name = $_GET['location_name'];
            /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
            $sql = "select * from ML_User_Locations where LCN_Name='$loc_name' AND LCN_Created_By = $usrid";
            $content_array = Yii::app()->db->createCommand($sql)->queryRow();
            if (!empty($content_array)) {
                echo json_encode(true);
            } else {
                echo json_encode(false);
            }
        }
    }

    public function actionbusinessnamealreadyexist() {
        if (!empty($_GET['bus_name'])) {
            $busid = $_GET['bus_id'];
            $busvalue = $_GET['bus_name'];
            $sql = "select * from ML_Businesses where BUS_Name='$busvalue' AND BUS_ID <> $busid";
            $content_array = Yii::app()->db->createCommand($sql)->queryRow();
            if (!empty($content_array)) {
                echo json_encode(true);
            } else {
                echo json_encode(false);
            }
        }
    }
    
    
    public function actioncopy_create_business(){
        
        $bus_id = $_GET['id'];
        $getexpertise = BusinessAttributes::getBusinessexpertise($bus_id);
        $model1=new BusinessCategories();
        $bus_category = $model1->getBusinessCategories($bus_id);
                    
        $businessModel = new Businesses;
        $data = $businessModel->getBusinessDetails($bus_id);
        $description = $data['BUS_Description'];
        $url = $data['BUS_URL'];
        if(!empty($data['BUS_Summary'])){
            $summary = $data['BUS_Summary'];
        }else{
        $summary = '';
        }
        /* Replaced LCN_USR_ID with LCN_Created_By by Vaiju on 12/10/2017 */
        $locations = UserLocations::model()->findAll(array('condition' => 'LCN_Created_By="' . Yii::app()->user->id . '"', 'order' => 'LCN_IsDefault desc'));

        $userID = Yii::app()->user->id;
        $act = 'Active';


        $obj_id = Objects::model()->findByAttributes(array('BJ_Name' => 'Business'));
        $auth = Yii::app()->authorization;
        $edit_access = $auth->canEdit($userID, $obj_id->BJ_ID, 1, 1, 0, 0);
        $add_access = $auth->canCreate($userID, $obj_id->BJ_ID, 1, 1, 0, 0);
        $delete_access = $auth->canDelete($userID, $obj_id->BJ_ID, 1, 1, 0, 0);
        $approve_access = $auth->canApprove($userID, $obj_id->BJ_ID, 1, 1, 0, 0);


        $locationModel = new UserLocations;
        $businessModel = new Businesses('copy');
        $phoneModel = new UserPhones; 
        $emailModel = new UserEmails;
        $this->performAjaxValidation(array($locationModel, $businessModel,$phoneModel,$emailModel));

        $slug = '';

        if ((isset($_POST['copy_business_save'])) && isset($_POST['Businesses']) && isset($_POST['UserLocations']) && isset($_POST['UserPhones']) && isset($_POST['UserEmails'])) {
            /** multiple model validation --start**/ 
                $businessModel->attributes = $_POST['Businesses'];
                $businessModel->categories = '';

                $locationModel->attributes = $_POST['UserLocations'];
                $country_name_id = Countries::model()->find('CNT_ISO2=:CNT_ISO2', array(':CNT_ISO2' => $_POST['UserLocations']['LCN_Country_id']));
                $locationModel->LCN_Country_id = $country_name_id['CNT_Code'];
				
				$emailModel->attributes = $_POST['UserEmails'];  
				$phoneModel->attributes = $_POST['UserPhones'];
				$validEmail = $emailModel->validate();
				$validPhone = $phoneModel->validate();
                                
                $valid = $businessModel->validate();
                       
                $valid = $locationModel->validate() && $valid && $validEmail  && $validPhone;
            /** multiple model validation --end**/     

            if ($valid) {

                $func = new Functions();
                $rand_id = $func->getBusinessRandomNumber();
                
                /** roles for business module **/
                $modelUserRoles = Roles::model()->findAllByAttributes(array('MLR_Name' => array('User Business Administrator', 'User Location Administrator', 'User Category Administrator', 'User Customer Manager')));
                
                $criteria = new CDbCriteria();
                $criteria->condition = "SLOB_REF_OBJ_TYPE = 11";
                $criteria->order = 'SLOB_ID DESC';
                $lastbackup = SiteLOBS::model()->find($criteria);
               
                $connection = Yii::app()->db;                
                $transaction = $connection->beginTransaction();
               try{

                    $businessModel = new Businesses('copy');
                    $businessModel = $businessModel->savecopyBusinessData($url,$_POST,$rand_id,$slug,$approve_access,$summary);                    
                    if ($businessModel!=true) {
                        throw new Exception('error while saving business data.');
                    }                    
                    $business_id = $connection->getLastInsertID();

                    

                    if (!empty($lastbackup)) {
                        $businessTermsModel = new BusinessTermsLog;
                        $businessTermsModel = $businessTermsModel->saveBusinessTermsData($_POST,$userID,$lastbackup,$business_id);
                                                
                        if ($businessTermsModel!=true) {
                             throw new Exception('error while saving business terms data.');
                        }
                    }
                    
                    
                    
                    $locationModel = $locationModel->saveLocationDataForBusiness($_POST, $business_id);                    
                    if ($locationModel['status'] != 1) {
                        
                        throw new Exception('error while saving location data.');
                    }                    
                    $location_id = $locationModel['location_id'];
                    
                    
                    
                    $phoneModel = new UserPhones; 
                    $phoneModel = $phoneModel->savePhoneDataForBusiness($userID,$_POST,$location_id);                    
                    if ($phoneModel!=true) {
                             throw new Exception('error while saving user phone data.');
                    }
                    
                    $emailModel = new UserEmails;
                    $emailModel = $emailModel->saveEmailDataForBusiness($userID,$_POST,$location_id);                  
                    if ($emailModel!=true) {
                             throw new Exception('error while saving user email data.');
                    }                    
                    $emailId = $connection->getLastInsertID();
                    
                    
                    
                    $roles_arr = array();                    
                    foreach ($modelUserRoles as $key => $value) {
                                                
                            $businessRolesModel = new SiteUserRoles;
                            $businessRolesModel = $businessRolesModel->saveSiteUserRolesForBusiness($userID,$value->MLR_ID,$location_id,$business_id,$emailId);
                            $roles_arr[$key]['role_name'] = $value->MLR_Name;
                            $roles_arr[$key]['role_id'] = $value->MLR_ID;
                            $roles_arr[$key]['sur_id'] = Yii::app()->db->getLastInsertId();
                            if ($businessRolesModel!=true) {
                                throw new Exception('error while saving site user roles data.');
                                $roles_arr = array();
                    
                            }

                           
                    }
                    
            $busid = $business_id;
            $usrid = Yii::app()->user->id;
            if(!empty($getexpertise)){
                foreach($getexpertise as $expertise){
                    $name= $expertise->MLBA_Name;
                    $desc = $expertise->MLBA_Description;
                    BusinessAttributes::addBusinessExpertise($busid, $name, $desc, $usrid);
                }
            }
                    
        
            if(!empty($bus_category)) {
                
                foreach($bus_category as $value) {
                    $catID = $value['MBC_ACT_ID'];
                    BusinessCategories::addBusinessCategory($busid, $catID, $usrid);
                }
                      
            }

                    $transaction->commit();
                    $country_name_id = Countries::model()->find('CNT_ISO2=:CNT_ISO2', array(':CNT_ISO2' => $_POST['UserLocations']['LCN_Country_id']));
                    $countryName = $country_name_id['CNT_Name'];

                    $businessData = "Business Name      :  " . $_POST['Businesses']['BUS_Name'] . "<br/>" .
                            "Business Url       :  " . $_POST['Businesses']['BUS_URL'] . "<br/>" .
                            "Business Description   :  " . $_POST['Businesses']['BUS_Description'] . "<br/>" .
                            "Location Name      :  " . $_POST['UserLocations']['LCN_Name'] . "<br/>" .
                            "Country            :  " . $countryName . "<br/>" .
                            "Email Id       :  " . $_POST['UserEmails']['UEM_Email'] . "<br/>" .
                            "Phone Number       :  " . $_POST['UserPhones']['UPH_Phone'] . "<br/>" .
                            "Radius         :  " . $_POST['UserLocations']['LCN_Radius_miles'] . "<br/>";
                    $moduleStatus = $this->moduleStatus();

                    if ($moduleStatus == TRUE) {
                        $adminId = Yii::app()->user->id;
                        $getAdminName = $this->getAdminName($adminId);
                        $activity = "Business Management - Create";
                        $description = $businessData . " has been created by " . $getAdminName;
                        $audit = new Functions();
                        $activity = $activity;
                        $description = $description;
                        $descriptionDevp = $businessData . " has been created by " . $getAdminName;
                        $setAudit = $audit->userAudit($description, $activity, $descriptionDevp);
                    }
                    $this->redirect(array('business_management'));
                 } catch (Exception $e) {
                    $transaction->rollback();
                    //Yii::trace($e->getMessage());
                    Yii::app()->user->setFlash("ferror", "Please enter the required fields correctly.");
                }
            }else{
                
            }
        }

         $countryRs = Countries::model()->findAll(array('condition'=>'CNT_Status=1','order'=>'CNT_Name ASC'));
	    $countries = array();
	    $countriesFlag = array();
	    foreach($countryRs as $country)
	    {
	        $countries[$country->CNT_Code]                  = '+'.$country->CNT_Phone_Code.' '.$country->CNT_Name;
	        $countriesFlag[$country->CNT_Code]['data-flag'] = Yii::app()->baseUrl.'/images/flags/24x24/'.strtolower($country->CNT_ISO2).'.png';
	        $countriesFlag[$country->CNT_Code]['data-code'] = $country->CNT_Phone_Code;
	    }
  $this->render('copy_create_business', array(
            'model' => $businessModel,
            'location' => $locationModel,
          //  'approve_access' => $approve_access,
            'phoneModel'=>$phoneModel,
            'emailModel'=>$emailModel,
            'description'=>$description,
      'countries' => $countries, 'countriesFlag'=> $countriesFlag
        ));
    }
    
    public function actionGetroleobject(){
        $roleId =$_POST['role_id'];
        $sql = "select * from ML_Role_Permissions LEFT JOIN ML_Objects ON ML_Objects.BJ_ID =ML_Role_Permissions.MLRP_BJ_ID  where MLRP_MLR_ID=".$roleId;
        $roleObjects = Yii::app()->db->createCommand($sql)->queryAll();
        $html = '';
        if ($roleObjects == '') {
                $html = $html . '<option value="">Select</option>';
        } else {

            foreach ($roleObjects as $roleObject) {


                    $html = $html . sprintf("<option value=\"%d\" >%s</option>", $roleObject['BJ_ID'], $roleObject['BJ_Name']);

            }


        }
         echo $html;
        exit;
    }
    
    public function actionAutoSuggestionSearch() {
        $keyword = $_POST['keyword'];
        $sql = "SELECT * FROM ML_Users WHERE USR_FirstName LIKE '%".$keyword."%' OR USR_LastName LIKE '%".$keyword."%' OR USR_Screen_Name LIKE '%".$keyword."%' OR USR_MiddleName LIKE '%".$keyword."%' OR USR_nick_name LIKE '%".$keyword."%' AND USR_Status=1";
        $connection = Yii::app()->db->createCommand($sql);
        $rows       = $connection->queryAll();

        foreach($rows as $row) {
            $img = Users::getUserPic($row['USR_ID']);
            $username = $row['USR_FirstName']." ".$row['USR_LastName'];
            $user = Users::model()->findByPk($row['USR_ID']);
            if(!empty($user->userLocations))
            {
                $userLocation = $user->userLocations[0];
                $country = $userLocation->country->CNT_Name;
                $location = $userLocation->LCN_City."&nbsp;".$userLocation->LCN_State."&nbsp;".$country;
            }
            $html = "";
            $html .= '<li onclick="set_item(\''.$username.'\')">';
            $html .= "<div class='quickpoll-user'>";
            $html .= "<div class='logo'><img src='".$img."'></div>";
            $html .= "<div><div class='user-name'>$username</div>";
            $html .= "<div class='user-location'>$location</div></div>";
            $html .= "</div>";
            $html .= '</li>';
            echo $html;

        }
    }
    
    public function actionAutoSuggestionApprovedBy() {
        $keyword = $_POST['keyword'];
        $sql = "SELECT * FROM ML_Users WHERE USR_FirstName LIKE '%".$keyword."%' OR USR_LastName LIKE '%".$keyword."%' OR USR_Screen_Name LIKE '%".$keyword."%' OR USR_MiddleName LIKE '%".$keyword."%' OR USR_nick_name LIKE '%".$keyword."%' AND USR_Status=1";
        $connection = Yii::app()->db->createCommand($sql);
        $rows       = $connection->queryAll();

        foreach($rows as $row) {
            $img = Users::getUserPic($row['USR_ID']);
            $username = $row['USR_FirstName']." ".$row['USR_LastName'];
            $user = Users::model()->findByPk($row['USR_ID']);
            if(!empty($user->userLocations))
            {
                $userLocation = $user->userLocations[0];
                $country = $userLocation->country->CNT_Name;
                $location = $userLocation->LCN_City."&nbsp;".$userLocation->LCN_State."&nbsp;".$country;
            }
            $html = "";
            $html .= '<li onclick="set_item_approved_by(\''.$username.'\')">';
            $html .= "<div class='quickpoll-user'>";
            $html .= "<div class='logo'><img src='".$img."'></div>";
            $html .= "<div><div class='user-name'>$username</div>";
            $html .= "<div class='user-location'>$location</div></div>";
            $html .= "</div>";
            $html .= '</li>';
            echo $html;

        }
    }

    public function actionAutoSuggestionEmployee() {
        $keyword = $_POST['keyword'];
        $sql = "SELECT * FROM ML_Users WHERE USR_FirstName LIKE '%".$keyword."%' OR USR_LastName LIKE '%".$keyword."%' OR USR_Screen_Name LIKE '%".$keyword."%' OR USR_MiddleName LIKE '%".$keyword."%' OR USR_nick_name LIKE '%".$keyword."%' AND USR_Status=1";
        $connection = Yii::app()->db->createCommand($sql);
        $rows       = $connection->queryAll();

        foreach($rows as $row) {
            $img = Users::getUserPic($row['USR_ID']);
            $username = $row['USR_FirstName']." ".$row['USR_LastName'];
            $user = Users::model()->findByPk($row['USR_ID']);
            if(!empty($user->userLocations))
            {
                $userLocation = $user->userLocations[0];
                $country = $userLocation->country->CNT_Name;
                $location = $userLocation->LCN_City."&nbsp;".$userLocation->LCN_State."&nbsp;".$country;
            }
            $html = "";
            $html .= '<li onclick="set_item_employee(\''.$username.'\')">';
            $html .= "<div class='quickpoll-user'>";
            $html .= "<div class='logo'><img src='".$img."'></div>";
            $html .= "<div><div class='user-name'>$username</div>";
            $html .= "<div class='user-location'>$location</div></div>";
            $html .= "</div>";
            $html .= '</li>';
            echo $html;

        }
    }
    
    public function actionGetMoreBusinessCats() {
        $busID = $_POST['busID'];
        $sql = "SELECT * FROM ML_Business_Categories WHERE MBC_BUS_ID = '$busID' AND MBC_Sequence != '1'";
        
        $connection = Yii::app()->db;
        $command    = $connection->createCommand($sql);
        $rows       = $command->queryAll();
        $html = "";
        $html .= "<table class='detail-view ml_detail_view selected-categories' style='margin: 0 0 0px;'>";
        $html .= "<tbody>";
        $functions = new Functions;
        foreach($rows as $cat) {
            $catDetails = $functions->category_list($cat['MBC_ACT_ID']);
            $cats = join(' > ',$catDetails);
            $html .= "<tr class='even'>";
            $html .= "<td>".$cats."</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody>";
        $html .= "</table>";
        echo $html;
    }
    
    public function actionGetBusDesc() {
        $busID = $_POST['busID'];
        $sql = "SELECT * FROM ML_Businesses WHERE BUS_ID = '$busID'";
        $connection = Yii::app()->db;
        $command    = $connection->createCommand($sql);
        $rows       = $command->queryRow();
        echo $rows['BUS_Description'];
    }
    
    /*code Added By Aruna*/


	
  public function actionGetBusName() {
        $busID = $_POST['busID'];
        $sql = "SELECT BUS_Name FROM ML_Businesses WHERE BUS_ID = '$busID'";
        $connection = Yii::app()->db;
        $command    = $connection->createCommand($sql);
        $rows       = $command->queryRow();
        echo $rows['BUS_Name'];
    }
	
function actionfetchCategoryTreeList($parent = 0, $user_tree_array = '') {

		$connection=Yii::app()->db; 

    if (!is_array($user_tree_array))
    $user_tree_array = array();

  $sql = "SELECT `BUS_ID`  FROM `ML_Businesses` WHERE  `ML_Parent_BUS_ID` = '$parent'  ";
 //  $query = @mysql_query($sql);
 $query=$connection->createCommand($sql);
  $count=$query->execute(); // execute the non-query SQL
 
  if ($count> 0) {
     $user_tree_array[] = "<ul>";
    while ($row = $query->queryAll()) {
		
		
	  $user_tree_array[] = "<li>". $row[0]['BUS_ID']."</li>";
      $user_tree_array = fetchCategoryTreeList($row[0]['BUS_ID'], $user_tree_array);
    }
	$user_tree_array[] = "</ul>";
  }
  return $user_tree_array;
}	
  
  	public function actionEditlocation() {
				
       $connection=Yii::app()->db; 

       
            $message = Yii::t('app','You have successfully updated a location');
			
			
				/* print_r($_POST);
		         exit;*/
		$LCN_REF_OBJ_TYPE=11;
		$LCN_ID=$_POST['LCN_ID'] ;
		$LCN_REF_OBJ_KEY=$_POST['LCN_REF_OBJ_KEY'] ;
		$LCN_Name=$_POST['LCN_Name'] ;
		$LCN_House_number=$_POST['LCN_House_number'] ;
		//$LCN_Building=$_POST['LCN_Building'] ;
		$LCN_Street_name=$_POST['LCN_Street_name'] ;
		$LCN_Street_opt=$_POST['LCN_Street_opt'] ;
		$LCN_City=$_POST['LCN_City_Val'] ;
		$LCN_State=$_POST['LCN_State'] ;
		$LCN_ZipCode=$_POST['LCN_ZipCode'] ;

		$LCN_Apartment=$_POST['LCN_Apartment'] ;
		$LCN_Floor=$_POST['LCN_Floor'] ;
		$LCN_Geo_latitude=$_POST['LCN_Geo_latitude'] ;
		$LCN_Geo_longitude=$_POST['LCN_Geo_longitude'] ;
		
		//echo  $sql="UPDATE   ML_User_Locations  set LCN_Name='$LCN_Name' where LCN_ID='$LCN_ID'";
		  $sql="UPDATE   ML_User_Locations  set LCN_Name='$LCN_Name',LCN_House_number='$LCN_House_number',LCN_Street_name='$LCN_Street_name',LCN_Street_opt='$LCN_Street_opt',LCN_City='$LCN_City',LCN_State='$LCN_State',LCN_ZipCode='$LCN_ZipCode' ,LCN_Apartment='$LCN_Apartment' ,LCN_Floor='$LCN_Floor' ,LCN_Geo_latitude='$LCN_Geo_latitude' ,LCN_Geo_longitude='$LCN_Geo_longitude'   where LCN_ID='$LCN_ID'";
			/*print_r($_POST);
		         exit;*/
			$command=$connection->createCommand($sql);
  			$rowCount=$command->execute(); // execute the non-query SQL
			if($rowCount>0){
            $msg ='success';
			}
			else
			{
				$msg = 'fail';
			}
			 print_r($msg);
	        die();
	
	}	
	
 public function actionUpdatebusiness()
	{

		$connection=Yii::app()->db;
		$bus_id = $_POST['Businesses']['BUS_ID'];
		$pagename=$_POST['Businesses']['pagename'];
		$oldvalue=$_POST['oldvalue'];
		$userID = Yii::app()->user->id;
		$act = new VisitorStatistics();
		$pageUrl = $pagename;
		$ACT_Ref_Obj_Type = 11;
		$ACT_Ref_Obj_Key = $bus_id;
		$description = 'Business Overview';
		$moduleID = 0;

		if(isset($_POST['Businesses']['BUS_Name']))
		{
			$BUS_Name=$_POST['Businesses']['BUS_Name'];

			$sql="UPDATE   ML_Businesses set BUS_Name='$BUS_Name' where BUS_ID='$bus_id'";
			$command=$connection->createCommand($sql);
  			$rowCount=$command->execute(); // execute the non-query SQL
			if($rowCount>0)
			{

                $msg ='success';
                $descriptionDevp = "Updated Business Name";
                $activity = "Action Initiated";
                $oldvalue =$oldvalue;
                $newvalue =$BUS_Name;

                $act->addActivity($pageUrl,$description,$ACT_Ref_Obj_Type,$ACT_Ref_Obj_Key,$descriptionDevp,$moduleID,$activity,$oldvalue,$newvalue);

			}
			else
			{
				$msg = 'fail';
			}
			 print_r($msg);
	        die();
				//echo "Business name updated successfully";

		}
		if(isset($_POST['Businesses']['BUS_Description']))
		{
			$BUS_Description=$_POST['Businesses']['BUS_Description'];
			//$sql=@mysql_query("UPDATE   ML_Businesses set BUS_Name='$BUS_Name' where BUS_ID='$bus_id'");
			$sql="UPDATE   ML_Businesses set BUS_Description='$BUS_Description' where BUS_ID='$bus_id'";
			$command=$connection->createCommand($sql);
  			$rowCount=$command->execute(); // execute the non-query SQL
			//$dataReader=$command->queryAll(); // execute a query SQL
			if($rowCount>0){
            $msg ='success';

                $descriptionDevp = "Updated Business description";
                $activity = "Action Initiated";
                $oldvalue =$oldvalue;
                $newvalue =$BUS_Description;

                $act->addActivity($pageUrl,$description,$ACT_Ref_Obj_Type,$ACT_Ref_Obj_Key,$descriptionDevp,$moduleID,$activity,$oldvalue,$newvalue);
			}
			else
			{
				$msg = 'fail';
			}
			 print_r($msg);
	        die();
		}

		if(isset($_POST['Businesses']['BUS_Key_Words']))
		{
			$BUS_Key_Words=$_POST['Businesses']['BUS_Key_Words'];
			//$sql=@mysql_query("UPDATE   ML_Businesses set BUS_Name='$BUS_Name' where BUS_ID='$bus_id'");
			$sql="UPDATE   ML_Businesses set BUS_Key_Words='$BUS_Key_Words' where BUS_ID='$bus_id'";
			$command=$connection->createCommand($sql);
  			$rowCount=$command->execute(); // execute the non-query SQL
			//$dataReader=$command->queryAll(); // execute a query SQL

			if($rowCount>0){
            $msg ='success';
			$descriptionDevp = "Updated Business Keywords";
                $activity = "Action Initiated";
                $oldvalue =$oldvalue;
                $newvalue =$BUS_Key_Words;

                $act->addActivity($pageUrl,$description,$ACT_Ref_Obj_Type,$ACT_Ref_Obj_Key,$descriptionDevp,$moduleID,$activity,$oldvalue,$newvalue);
			}
			else
			{
				$msg = 'fail';
			}
			 print_r($msg);
	        die();
		}

		if(isset($_POST['Businesses']['BUS_Short_name']))
		{
			$BUS_Short_name=$_POST['Businesses']['BUS_Short_name'];
			$check="select * from ML_Businesses where BUS_Short_name='$BUS_Short_name'";
			$qry=$connection->createCommand($check);
  			$count=$qry->execute(); // execute the non-query SQL
			if($count==0){
				$sql="UPDATE   ML_Businesses set BUS_Short_name='$BUS_Short_name' where BUS_ID='$bus_id'";
				$command=$connection->createCommand($sql);
				$rowCount=$command->execute(); // execute the non-query SQL
				//$dataReader=$command->queryAll(); // execute a query SQL

				if($rowCount>0){
				$msg ='success';
				$descriptionDevp = "Updated Business Unique Short Name";
                $activity = "Action Initiated";
                $oldvalue =$oldvalue;
                $newvalue =$BUS_Short_name;

                $act->addActivity($pageUrl,$description,$ACT_Ref_Obj_Type,$ACT_Ref_Obj_Key,$descriptionDevp,$moduleID,$activity,$oldvalue,$newvalue);
				}
				else
				{
					$msg = 'fail';
				}

			}
			else
			{
				$msg = 'exists';
			}
			 print_r($msg);
	        die();
		}

		}
public function actionvisitorstatistics()
  {	$sessionArray=$_SESSION;
         $userId = Yii::app()->user->id;

			if($userId!=0)
			{
			 $sessionId=$sessionArray['session_id'];
			}
			else
			{
			 $sessionId='';
			}

	   $connection=Yii::app()->db;
	      $pageurl = $_POST['pageurl'];
	      $VST_Session_ID = $sessionId;
	      $VST_User_ID = $userId;
		  $VST_Description=$_POST['VST_Description'];

	      $VST_IP_Address = $_SERVER['REMOTE_ADDR'];
	      $VST_Visited_Dt=date("Y-m-d H:i:s");
		  $ACT_City='';
		  $ACT_State='';
		  $ACT_Country='';
		  $ACT_Latitude='';
		  $ACT_Longitude='';

		  $func = new Functions;

		  $data = $func->getUserCurrentLocationAccess();
		  $details = array();


		  if(isset($data['city'])){
				$details['city'] = $data['city'];
				$details['region'] = $data['region'];
				$details['country'] = $data['country'];
				$place = $data['city'].' ,'.$data['city'].' ,'.$data['country'];
				$details['latitude'] = $data['latitude'];
				$details['longitude'] = $data['longitude'];
			}else if(isset($data['cityName'])){
				$details['city'] = $data['cityName'];
				$details['region'] = $data['regionName'];
				$details['country'] = $data['countryName'];
				$place = $data['cityName'].' ,'.$data['regionName'].' ,'.$data['countryName'];
				$details['latitude'] = $data['latitude'];
				$details['longitude'] = $data['longitude'];

			}

		    $client_detail = $func->getServerDetail();
			$dd = new DeviceDetector($client_detail['user_agent']);

			$dd->parse();

			if ($dd->isBot()) {
			  // handle bots,spiders,crawlers,...
			  $botInfo = $dd->getBot();
			} else {
			  $clientInfo = $dd->getClient(); // holds information about browser, feed reader, media player, ...
			  //$osInfo = $dd->getOs();
			  //$device = $dd->getDevice();
			  $devicetype = $dd->getDeviceName();
			  //$brand = $dd->getBrand();
			  $brandname = $dd->getBrandName();
			  $modelname = $dd->getModel();
			}

			if(empty($brandname)){
				$brandname = 'Unknown';
			}
			if(empty($modelname)){
				$modelname = 'Unknown';
			}

			if(isset($_SESSION['lang'])){
				$lang = $_SESSION['lang'];
				if(!is_numeric($lang)){
					$getlang  = MasterTypeItems::getMasterTypeItemsByName($lang);
					$lan = $getlang['MSTT_ID'];
				}else{
					$lan = $lang;
				}
			}else{

				$getlang  = MasterTypeItems::getMasterTypeItemsByName('en');
					$lan = $getlang['MSTT_ID'];
			}


			if($details){
				if($details['city']){
					$ACT_City = $details['city'];
				}
				if($details['region']){
					$ACT_State = $details['region'];
				}
				if($details['country']){
					$ACT_Country = $details['country'];
				}
				if($details['latitude']){
					$ACT_Latitude = $details['latitude'];
				}
				if($details['longitude']){
					$ACT_Longitude = $details['longitude'];
				}
			}
			$ACT_Locality='';

		  $ACT_source_Device_Type=$devicetype;
		  $ACT_Device_Model=$brandname.' - '.$modelname;
		  $ACT_Browser=$clientInfo['name'];
		  $ACT_Ref_Obj_Type=11;
		  $ACT_Ref_Obj_Key=$_POST['ACT_Ref_Obj_Key'];




			  $sql="insert into    ML_Activity_Logs (`ACT_ID`,`ACT_Session_ID`,`ACT_User_ID`,`ACT_Module_Id`,`ACT_Ref_Obj_Type`,`ACT_Ref_Obj_Key`,`ACT_IP_Address`,`ACT_source_Device_Type`,`ACT_Device_Model`,`ACT_Device_ID`,`ACT_language_used`,`ACT_Browser`,`ACT_Visited_URL`,`ACT_Activity`,`ACT_Description`,`ACT_Description_Devp`,`ACT_Old_Value`,`ACT_New_Value`,`ACT_City`,`ACT_Locality`,`ACT_State`,`ACT_Country`,`ACT_Latitude`,`ACT_Longitude`,`ACT_Visited_Dt`) values('','$VST_Session_ID','$VST_User_ID','$ACT_Module_Id''$ACT_Ref_Obj_Type','$ACT_Ref_Obj_Key','$VST_IP_Address','$ACT_source_Device_Type','$ACT_Device_Model','$ACT_Device_ID','$lan','$ACT_Browser','$pageurl','$ACT_Activity','$VST_Description','$ACT_Description_Devp','$ACT_Old_Value','$ACT_New_Value','$ACT_City','$ACT_Locality','$ACT_State','$ACT_Country','$ACT_Latitude','$ACT_Longitude','$VST_Visited_Dt')";


			$command=$connection->createCommand($sql);
  			$rowCount=$command->execute(); // execute the non-query SQL

			if($rowCount>0){
            $msg ='success';
			}
			else
			{
				$msg = 'fail';
			}
			 print_r($msg);
	        die();


  }
	
public function actionEmpdeactivate()
  {
	  
	 
	   $connection=Yii::app()->db; 
	    $BE_ID = $_POST['BE_ID'];
	    $BE_Deactivate_DT = $_POST['BE_Deactivate_DT'];
	    $BE_Deactivation_Reason = $_POST['BE_Deactivation_Reason'];
	    $BE_Status = $_POST['BE_Status'];
		
		
	  		$sql="UPDATE   ML_Business_Employee  set BE_Status='$BE_Status', BE_Deactivation_Reason='$BE_Deactivation_Reason',BE_Deactivate_DT='$BE_Deactivate_DT'  where BE_ID='$BE_ID'";
			$command=$connection->createCommand($sql);
  			$rowCount=$command->execute(); // execute the non-query SQL
			if($rowCount>0){
            $msg ='success';
			}
			else
			{
				$msg = 'fail';
			}
			 print_r($msg);
	        die();
			
	  
  }	
  
  public function actionCategoryname()
  {
	  
	 
	   $connection=Yii::app()->db; 
	    $Cat_ID = $_POST['Cat_ID'];
		
	  		$sql = "SELECT ACT_Name FROM ML_Category WHERE ACT_ID = '$Cat_ID'";
            $connection = Yii::app()->db;
            $command    = $connection->createCommand($sql);
            $rows       = $command->queryAll();
             print_r($rows[0]['ACT_Name']);
			
	  
  }	
  
  
   public function actionBusinessnamelist()
  {
	  
	 
	   $connection=Yii::app()->db; 
	     $BUS_Name = $_POST['BUS_Name'];
	   
		
	  		$sql = "SELECT BUS_User_ID,BUS_ID FROM ML_Businesses WHERE BUS_Name = '$BUS_Name'";
            $connection = Yii::app()->db;
            $command    = $connection->createCommand($sql);
            $rows       = $command->queryAll();
			$rowCount=$command->execute(); // execute the non-query SQL

			if($rowCount>0){
		   ?>
           <p><?php echo $BUS_Name; ?> Business with same name already exists.</p>
           <p style="color:#F60;">Are You Still want to create and invite a new Business ? <span class="icons fa fa-check confirm_business " style="color:green"></span>&nbsp;<span class="icons fa fa-times cancel_business " style="color:#000;"></span></p>
            <table class="detail-view ml_detail_view table-striped table-inline-detail" style="display:inline-block; overflow:auto; width: 100%; max-height:150px;"  >
             <tr style="background-color:#9FCFFF; ;font-weight:bold">
            <td  width="100" >Business Name</td>
            <td width="100">Complete Address</td>
            <td width="100">Owner/Manager</td>
            </tr>
            <?php 
						for($i=0; $i<$rowCount; $i++){
								$BUS_User_ID=$rows[$i]['BUS_User_ID']; 
								$BUS_ID		=$rows[$i]['BUS_ID']; 		
								$BUS_Name=$BUS_Name;
								$loc = new UserLocations;
								$bus_locations = $loc->getbusinesslocations($BUS_ID);
$country = new Countries;
        
        
        $countryData = $country->getCountryName($bus_locations['LCN_Country_id']);
        $location = "";
      if(!empty($bus_locations['LCN_House_number'])) {
            $location .= $bus_locations['LCN_House_number'] . ",&nbsp;";
        }
		if(!empty($bus_locations['LCN_Building'])) {
            $location .= $bus_locations['LCN_Building'] . ",&nbsp;";
        }
		if(!empty($bus_locations['LCN_Street_name'])) {
            $location .= $bus_locations['LCN_Street_name'] . ",&nbsp;";
        }
        if(!empty($bus_locations['LCN_City'])) {
            $location .= $bus_locations['LCN_City'] . ",&nbsp;";
        }
        if(!empty($bus_locations['LCN_State'])) {
            $location .= $bus_locations['LCN_State'] . ",&nbsp;";
        }
        if(!empty($countryData['CNT_Name'])) {
            $location .= $countryData['CNT_Name']. ",&nbsp;";
        }
		if(!empty($bus_locations['LCN_ZipCode'])) {
            $location .= $bus_locations['LCN_ZipCode'] . "&nbsp;";
        }
         $locationData = $location;
	   
	   $businesses=new Businesses;
       $bus_name_info = $businesses->getbusinessname($BUS_ID);

			
			
			 ?>
            <tr>
            <td  width="100" ><?php echo $bus_name_info; ?></td>
            <td width="100"><?php echo $locationData; ?></td>
            <td width="100">Owner/Manager</td>
            
            </tr>
            
            <?php }  ?>


</table>
                
           <?php
				
			}
            // print_r($rows[0]['ACT_Name']);
			
	  
  }	

 public function actionUsernamelist()
    {
	  $connection=Yii::app()->db; 
	     $USR_FirstName = $_POST['USR_FirstName'];
		 $USR_LastName = $_POST['USR_LastName'];
	   
		 
	  		$sql = "SELECT USR_ID,USR_FirstName,USR_LastName FROM ML_Users WHERE USR_FirstName = '$USR_FirstName' and USR_LastName='$USR_LastName'";
            $connection = Yii::app()->db;
            $command    = $connection->createCommand($sql);
            $rows       = $command->queryAll();
			$rowCount=$command->execute(); // execute the non-query SQL

			if($rowCount>0){
		   ?>
           
            <div style="width: 100%;display: table;" class="tooltip-info-span cancel_user" >
    <div style="display: table-row">
        <div style="width: 80%; display: table-cell; float:left;margin-left:10px; color:red;">You can not send an invite to a person who is already an user</div>
        <div style="display: table-cell; float:right; margin-right:15px; color:red"> <span data-toggle="tooltip" data-title="Clear Created By data" class="glyphicon glyphicon-remove cancel_user"></span> </div>
    </div>
</div> 
           <?php
		   
			}
            
            


	}
	
	
  public function actionBusinessusernamelist()
    {
	  
	 
	   $connection=Yii::app()->db; 
	     $USR_FirstName = $_POST['USR_FirstName'];
		 $USR_LastName = $_POST['USR_LastName'];
	   
		 
	  		$sql = "SELECT USR_ID FROM ML_Users WHERE USR_FirstName = '$USR_FirstName' and USR_LastName='$USR_LastName'";
            $connection = Yii::app()->db;
            $command    = $connection->createCommand($sql);
            $rows       = $command->queryAll();
			$rowCount=$command->execute(); // execute the non-query SQL

			if($rowCount>0){
		   ?>
           <p><?php echo $rowCount; ?> Users with same name already exists.</p>
           <p style="color:#F60;">Will you like to pick one of these or create new ? <span class="icons fa fa-check confirm_user " style="color:green"></span>&nbsp;<span class="icons fa fa-times cancel_user " style="color:#000;"></span></p>
            <table class="detail-view ml_detail_view table-striped table-inline-detail" style="display:inline-block; overflow:auto; width: 100%; max-height:150px;"  >
             <tr style="background-color:#9FCFFF; ;font-weight:bold">
            <td  width="100" >User Name</td>
            <td width="100">Select</td>
            </tr>
            <?php 
						for($i=0; $i<$rowCount; $i++){
								
								$USR_ID		=$rows[$i]['USR_ID']; 		
								 $user = Users::model()->findByPk($USR_ID);
     $username     = $user->USR_FirstName." ".$user->USR_LastName;
	 $userLocation = $user->userLocations[0];
                $country      = $userLocation->country->CNT_Name;
                $location     = $userLocation->LCN_City." ".$userLocation->LCN_State." ".$country;

                $img = Users::getUserPic($USR_ID);

			
			
			 ?>
            <tr>
            <td  width="100" ><span class="text-view">
                       
                            <div class='quickpoll-user' style="width:200px;">
                            
            	        <div class='logo'><img src='<?php echo $img;?>'></div>
            	        <div>
                	        <div class='user-name'><?php echo $username;?></div>
                	        <div class='user-location'><?php echo $location;?></div>
                	        
                        </div>
        	        </div></span></td>
            <td width="100"><input type="radio" class='selected_user' value="<?php  echo $USR_ID; ?>"/></td>
            
            </tr>
            
            <?php }  ?>


</table>
                
           <?php
				
			}
            // print_r($rows[0]['ACT_Name']);
			
	  
  }	
  
  
    public function actionBusinessemaillist()
    {
	  
	 
	   $connection=Yii::app()->db; 
	      $BE_Emloyee_Email = $_POST['email'];
		 
	  		 $sql = "SELECT MLBE_USR_ID FROM ML_Business_Employee WHERE BE_Emloyee_Email = '$BE_Emloyee_Email'";
			
            $connection = Yii::app()->db;
            $command    = $connection->createCommand($sql);
            $rows       = $command->queryAll();
			$rowCount=$command->execute(); // execute the non-query SQL

			if($rowCount>0){
		   ?>
           <p> This email is already being used by the following user.</p>
           <p style="color:#F60;">Select this user? <span class="icons fa fa-check confirm_email " style="color:green"></span>&nbsp;<span class="icons fa fa-times cancel_email " style="color:#000;"></span></p>
            <table class="detail-view ml_detail_view table-striped table-inline-detail" style="display:inline-block; overflow:auto; width: 100%; max-height:150px;"  >
             <tr style="background-color:#9FCFFF; ;font-weight:bold">
            <td  width="100" >User Name</td>
            <td width="100">Select</td>
            </tr>
            <?php 
						for($i=0; $i<$rowCount; $i++){
								
								$USR_ID		=$rows[$i]['MLBE_USR_ID']; 		
								 $user = Users::model()->findByPk($USR_ID);
     $username     = $user->USR_FirstName." ".$user->USR_LastName;
	 $userLocation = $user->userLocations[0];
                $country      = $userLocation->country->CNT_Name;
                $location     = $userLocation->LCN_City." ".$userLocation->LCN_State." ".$country;

                $img = Users::getUserPic($USR_ID);

			
			
			 ?>
            <tr>
            <td  width="100" ><span class="text-view">
                       
                            <div class='quickpoll-user' style="width:200px;">
                            
            	        <div class='logo'><img src='<?php echo $img;?>'></div>
            	        <div>
                	        <div class='user-name'><?php echo $username;?></div>
                	        <div class='user-location'><?php echo $location;?></div>
                	        
                        </div>
        	        </div></span></td>
            <td width="100"><input type="radio" class='selected_email' value="<?php  echo $BE_Emloyee_Email; ?>"/></td>
            
            </tr>
            
            <?php }  ?>


</table>
                
           <?php
				
			}
            // print_r($rows[0]['ACT_Name']);
			
	  
  }	
  
  
      public function actionUseremaillist()
    {
	  
	 
	   $connection=Yii::app()->db; 
	     $MEP_Email = $_POST['email'];
	   
		 
	  		$sql = "SELECT MEP_Obj_Key FROM ML_Email_Phones WHERE MEP_Email = '$MEP_Email'";
            $connection = Yii::app()->db;
            $command    = $connection->createCommand($sql);
            $rows       = $command->queryAll();
			$rowCount=$command->execute(); // execute the non-query SQL

			if($rowCount>0){
		   ?>

            <div style="width: 100%;display: table;" class="tooltip-info-span cancel_email" >
    <div style="display: table-row">
        <div style="width: 80%; display: table-cell; float:left;margin-left:10px; color:red;">Since This email is already registered , you can not send an email to a person with this email address.</div>
        <div style="display: table-cell; float:right; margin-right:15px; color:red"> <span data-toggle="tooltip" data-title="Clear Created By data" class="glyphicon glyphicon-remove cancel_email"></span> </div>
    </div>
</div> 
                
           <?php
				
			}
            // print_r($rows[0]['ACT_Name']);
			
	  
  }	
  
  
  
  
      public function actionBusinessphonelist()
    {
	  
	 
	   $connection=Yii::app()->db; 
	     $phone = $_POST['phone'];
	   
		 
	  		$sql = "SELECT MLBE_USR_ID FROM ML_Business_Employee WHERE BE_Emloyee_Phone1 = '$phone' or BE_Emloyee_Phone2='$phone'";
            $connection = Yii::app()->db;
            $command    = $connection->createCommand($sql);
            $rows       = $command->queryAll();
			$rowCount=$command->execute(); // execute the non-query SQL

			if($rowCount>0){
		   ?>
           <p><?php echo $rowCount; ?> This Phone number is  already being used by the following user.</p>
           <p style="color:#F60;">Select this user? <span class="icons fa fa-check confirm_phone " style="color:green"></span>&nbsp;<span class="icons fa fa-times cancel_phone " style="color:#000;"></span></p>
            <table class="detail-view ml_detail_view table-striped table-inline-detail" style="display:inline-block; overflow:auto; width: 100%; max-height:150px;"  >
             <tr style="background-color:#9FCFFF; ;font-weight:bold">
            <td  width="100" >User Name</td>
            <td width="100">Select</td>
            </tr>
            <?php 
						for($i=0; $i<$rowCount; $i++){
								
								$USR_ID		=$rows[$i]['MLBE_USR_ID']; 		
								 $user = Users::model()->findByPk($USR_ID);
     $username     = $user->USR_FirstName." ".$user->USR_LastName;
	 $userLocation = $user->userLocations[0];
                $country      = $userLocation->country->CNT_Name;
                $location     = $userLocation->LCN_City." ".$userLocation->LCN_State." ".$country;

                $img = Users::getUserPic($USR_ID);

			
			
			 ?>
            <tr>
            <td  width="100" ><span class="text-view">
                       
                            <div class='quickpoll-user' style="width:200px;">
                            
            	        <div class='logo'><img src='<?php echo $img;?>'></div>
            	        <div>
                	        <div class='user-name'><?php echo $username;?></div>
                	        <div class='user-location'><?php echo $location;?></div>

                	        
                        </div>
        	        </div></span></td>
            <td width="100"><input type="radio" class='selected_phone' value="<?php  echo $phone; ?>"/></td>
            
            </tr>
            
            <?php }  ?>


</table>
                
           <?php
				
			}
            // print_r($rows[0]['ACT_Name']);
			
	  
  }	
  
  
   public function actionUserphonelist()
    {
	  
	 
	   $connection=Yii::app()->db; 
	     $MEP_Phone = $_POST['phone'];
	   
		 
	  		$sql = "SELECT MEP_Obj_Key FROM ML_Email_Phones WHERE MEP_Phone = '$MEP_Phone'";
            $connection = Yii::app()->db;
            $command    = $connection->createCommand($sql);
            $rows       = $command->queryAll();
			$rowCount=$command->execute(); // execute the non-query SQL

			if($rowCount>0){
		   ?>

            <div style="width: 100%;display: table;" class="tooltip-info-span cancel_phone" >
    <div style="display: table-row">
        <div style="width: 80%; display: table-cell; float:left;margin-left:10px; color:red;">Since This phone  number is already registered , you can not send an message to a person with this phone number.</div>
        <div style="display: table-cell; float:right; margin-right:15px; color:red"> <span data-toggle="tooltip" data-title="Clear Created By data" class="glyphicon glyphicon-remove cancel_email"></span> </div>
    </div>
</div> 
                
           <?php
				
			}
  }	
  	  
	



	/*Code Added By Aruna Ends Here*/
  
    public function actionAutocompleteEmployer(){
	    $this->layout = false;

	    $query = $_GET['query'];

	    $sql = "SELECT * FROM ML_Businesses WHERE BUS_Name LIKE '%$query%' GROUP BY BUS_Name";
	    $attributes = Yii::app()->db->createCommand($sql)->queryAll();

	    $data = array();
            $country = new Countries();
            $business = new Businesses();
	    foreach($attributes as $k=>$value){
                $busID = $value['BUS_ID'];
                $sqlLocation = "SELECT * FROM ML_User_Locations WHERE LCN_REF_OBJ_TYPE = 11 AND LCN_REF_OBJ_KEY = '$busID'";
                $busLocation = Yii::app()->db->createCommand($sqlLocation)->queryRow();
                if(!empty($busLocation)) {
                    $countryData = $country->getCountryName($busLocation['LCN_Country_id']);
                    $countryData['CNT_Name'];
                    $busLocation['LCN_City'];
                    $busLocation['LCN_State'];
                    $location = $busLocation['LCN_City'] . " " . $busLocation['LCN_State'] . " " . $countryData['CNT_Name'];
                } else {
                    $location = "";
                }
                $img = $business->getBusinessPicPublic($busID);
	        $data[$k]['id']   = $busID;
	        $data[$k]['name'] = $value['BUS_Name'];
                $data[$k]['location'] = $location;
                $data[$k]['image']    = $img;
	    }

	    echo CJSON::encode($data);
	    exit;
	}
}
