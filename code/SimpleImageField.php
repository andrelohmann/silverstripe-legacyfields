<?php
/**
 * SimpleImageField provides an easy way of uploading images to {@link Image} has_one relationships.
 * These relationships are auto-detected if you name the field accordingly.
 * Unlike {@link ImageField}, it doesn't use an iframe.
 * 
 * Restricts the upload size to 2MB by default, and only allows upload
 * of files with the extension 'jpg', 'gif' or 'png'.
 * 
 * <b>Usage</b>
 * 
 * <code>
 * class Article extends DataObject {
 * 	static $has_one = array('MyImage' => 'Image');
 * }
 * // use in your form constructor etc.
 * $myField = new SimpleImageField('MyImage');
 * </code>
 * 
 * <b>Usage within a controller</b>
 * 
 * First add your $has_one relationship:
 * 
 * <code>
 * static $has_one = array(
 *    'FileName' => 'FileType'
 * );
 * </code>
 * (i.e. Image for a FileType)
 * 
 * Then add your Field into your form:
 * 
 * <code>
 * function Form() {
 *    return new Form($this, "Form", new FieldSet(
 *        new SimpleImageField (
 *            $name = "FileTypeID",
 *            $title = "Upload your FileType"
 *        )
 *    ), new FieldSet(
 * 
 *    // List the action buttons here - doform executes the function 'doform' below
 *        new FormAction("doform", "Submit")
 * 
 *    // List the required fields here
 *    ), new RequiredFields(
 *        "FileTypeID"
 *    ));
 * }
 * // Then make sure that the file is saved into the assets area:
 * function doform($data, $form) {
 *    $file = new File();
 *    $file->loadUploaded($_FILES['FileTypeID']);
 * 		
 *    // Redirect to a page thanking people for registering
 *    Director::redirect('thanks-for-your-submission/');
 * }
 * </code>
 * 
 * Your file should be now in the uploads directory
 * 
 * @package forms
 * @subpackage fields-files
 */

class SimpleImageField extends FileField {
	/**
	 * @deprecated 2.5
	 */
	public $allowedExtensions = array('jpg','gif','png');

	public function __construct($name, $title = null, $value = null) {
		parent::__construct($name, $title, $value);

		$this->getValidator()->setAllowedExtensions(array('jpg','gif','png'));
	}

	public function Field($properties = array()) {
            // Fetch the Field Record
	    if($this->form) $record = $this->form->getRecord();
	    $fieldName = $this->name;
	    if(isset($record)&&$record) {
	    	$imageField = $record->$fieldName();
                if($imageField && $imageField->exists()){
                    if($imageField->hasMethod('Thumbnail') && $imageField->Thumbnail()) $Image = $imageField->Thumbnail()->getURL();
                    else if($imageField->CMSThumbnail()) $Image = $imageField->CMSThumbnail()->getURL();
                    else $Image = false;
                }else{
                    $Image = false;
                }
            }else{
                $Image = false;
            }
            
            $properties = array_merge($properties, array(
                'MaxFileSize' => $this->getValidator()->getAllowedMaxFileSize(),
                'Image' => $Image
            ));
		
            return parent::Field($properties);
	}
}