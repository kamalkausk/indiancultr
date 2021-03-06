<?php

class Aurigait_Homepage_Adminhtml_RowsController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("homepage/rows")->_addBreadcrumb(Mage::helper("adminhtml")->__("Rows  Manager"),Mage::helper("adminhtml")->__("Rows Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Homepage"));
			    $this->_title($this->__("Manager Rows"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Homepage"));
				$this->_title($this->__("Rows"));
			    $this->_title($this->__("Edit Row"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("homepage/rows")->load($id);
				if ($model->getId()) {
					Mage::register("rows_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("homepage/rows");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Rows Manager"), Mage::helper("adminhtml")->__("Rows Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Rows Description"), Mage::helper("adminhtml")->__("Rows Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("homepage/adminhtml_rows_edit"))->_addLeft($this->getLayout()->createBlock("homepage/adminhtml_rows_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("homepage")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Homepage"));
		$this->_title($this->__("Rows"));
		$this->_title($this->__("New Item"));
		
		//$id   = $this->getRequest()->getParam("id");
		//$model  = Mage::getModel("homepage/rows")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		//Mage::register("rows_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("homepage/rows");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Rows Manager"), Mage::helper("adminhtml")->__("Rows Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Rows Description"), Mage::helper("adminhtml")->__("Rows Description"));


		$this->_addContent($this->getLayout()->createBlock("homepage/adminhtml_rows_edit"))->_addLeft($this->getLayout()->createBlock("homepage/adminhtml_rows_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();
			//echo "<pre>";print_r($post_data);die;
		      if ($post_data) {
			   try {
			   
			    //save image
				try{

					if((bool)$post_data['categoryImage']['delete']==1) {

								$post_data['categoryImage']='';

					} else {

						unset($post_data['categoryImage']);

						if (isset($_FILES)){

							if ($_FILES['categoryImage']['name']) {

								if($this->getRequest()->getParam("id")){
									$model = Mage::getModel("homepage/rows")->load($this->getRequest()->getParam("id"));
									if($model->getData('categoryImage')){
											$io = new Varien_Io_File();
											$io->rm(Mage::getBaseDir('media').DS.implode(DS,explode('/',$model->getData('categoryImage'))));	
									}
								}
											$path = Mage::getBaseDir('media') . DS . 'homepage' . DS .'categoryimages'.DS;
											$uploader = new Varien_File_Uploader('categoryImage');
											$uploader->setAllowedExtensions(array('jpg','png','gif'));
											$uploader->setAllowRenameFiles(false);
											$uploader->setFilesDispersion(false);
											$destFile = $path.$_FILES['categoryImage']['name'];
											$filename = $uploader->getNewFileName($destFile);
											$uploader->save($path, $filename);

											$post_data['categoryImage']='homepage/categoryimages/'.$filename;
							}
						}
					}

				} catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
						return;
				}
		//save image

				$post_data['store_id']=implode(',',$post_data['stores']);
				$model = Mage::getModel("homepage/rows")
				->addData($post_data)
				->setId($this->getRequest()->getParam("id"))
				->save();
				$write = Mage::getSingleton("core/resource")->getConnection("core_write");
				
				//save image data
				if(isset($post_data['image']))
				{
					$j=0;
				
					if($this->getRequest()->getParam("id"))
					{
						//Remove old data
						$write->query("delete from homepage_row_images where row_id=".$model->getId());
							
					}
					foreach($post_data['image'] as $image){
						$i=$post_data['imageCounter'][$j++];
						if(!empty($_FILES["image_$i"]['name'])){	 
							$image_name=$_FILES["image_$i"]["name"];
						}
						else 
							$image_name=@$image['image_prev'];
					
						if(!empty($_FILES["image_$i"]['name']))
						{
			            	 $uploader = new Varien_File_Uploader("image_$i");
						     $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything
						     $uploader->setAllowRenameFiles(false);
						     $uploader->setFilesDispersion(false);
						     $path = Mage::getBaseDir('media') . DS.'homepage'. DS.'rows'.DS ;
						     $uploader->save($path, $_FILES["image_$i"]['name']);		
									
						}
						if(isset($image['is_append_base_url'])){
                                                    $image['is_append_base_url'] = 1;
                                                }else{
                                                    $image['is_append_base_url'] = 2;
                                                }
						$sql="insert into homepage_row_images set row_id='".$model->getId()."',column_layout='".$image['column_layout']."',title='".$image['title']."',image='".$image_name."',url='".$image['url']."',sort_order='".$image['sort_order']."',is_append_base_url='".$image['is_append_base_url']."'";
						$write->query($sql);
						
					}
				}
				
				//code end
				
				
				
				
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Rows was successfully saved"));
				Mage::getSingleton("adminhtml/session")->setRowsData(false);

				if ($this->getRequest()->getParam("back")) {
					$this->_redirect("*/*/edit", array("id" => $model->getId()));
					return;
				}
				$this->_redirect("*/*/");
				return;
			} 
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				Mage::getSingleton("adminhtml/session")->setRowsData($this->getRequest()->getPost());
				$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
			return;
			}

		      }
		     $this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("homepage/rows");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("homepage/rows");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
		public function massStatusAction()
		{
			try {
				$ids = $this->getRequest()->getPost('ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("homepage/rows");
					  $model->setId($id)->setStatus($this->getRequest()->getPost('massStatus'))->save();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Status was successfully Changed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'rows.csv';
			$grid       = $this->getLayout()->createBlock('homepage/adminhtml_rows_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'rows.xml';
			$grid       = $this->getLayout()->createBlock('homepage/adminhtml_rows_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
