
<!DOCTYPE html>
<html lang="en"> 
<head>
	<title>Cameras :: PHP practice</title>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- <link rel="stylesheet" href="/public/assets/stylesheets/cameras.css" type="text/css"/> -->

</head>
<body>

<pre>
<?php

//properties of all cameras: manufacturer, model#, consumer vs pro, media storage type (f or d).
	// if film: - 	format- 35mm, medium, large? 
		//Each format has: ISO, available storage, number of photos left.
	// if dig: chip resolution, native-file-size, available storage, number of photos left.
		//(assume all dig cameras have 1G cards).

//each picture has: shutter speed, aperture, IS0, neg or file size, color or B/w, subject, 
	// film pic has: film format (from Camera class), 
	//dig picture has: file format (RAW/Jpeg).

class Camera {

	const DIGITAL = false;
	const FILM = true;
	//protected means is available within this class and its children.
	protected $make = "";
	protected $model = "";
	protected $pro = true;
	protected $film = false;

	protected $shutter = 125; // hundredths of a sec. 
	protected $fstop = 16;
	protected $iso = 160; 
	protected $color = true; // false means BW

	protected $pictures = array(); //stores pics on film or cards
	protected $capacity = 1;
	protected $picsLeft = 0;
	protected $cam_id;
	
	// CONSTRUCT turn on camera ---------------------------------
	//public means available outside this scope
	public function __construct($make, $model, $pro, $film, $capacity) {
		$this->make = $make;
		$this->model = $model;
		$this->pro = $pro;
		$this->film = $film;
		$this->capacity = $capacity;
		$this->cam_id = get_camera_id(); //automatically returns a unique id for each camera.
	}
	//SET AND GET -----------------------------------------------
	// necessary if you need to access the value of a property somewhere else. 

	//set exposure settings 
	public function setShutter($set_shutter){
		//validate is a number?
		$this->shutter = $set_shutter;
	}
	public function setFstop($set_fstop){
		//validate is a number?
		$this->fstop = $set_fstop;
	}
	public function setIso($set_iso){
		//validate is a number?
		$this->iso = $set_iso;
	}
	public function setColor($set_color){
		$this->color = $set_color;
	}
	public function setCapacity($set_capacity){
		//validate is a number?
		$this->capacity = $set_capacity;
	}

	//get exposure settings
	public function getShutter(){
		return $this->shutter;
	}
	public function getFstop(){
		return $this->fstop;
	}
	public function getIso(){
		return $this->iso;
	}
	public function getColor() {
		return $this->color;
	}
	public function getCapacity() {
		return $this->capacity;
	}
	public function getShotsLeft(){
		return $this->picsLeft;
	}
	public function getFilesize() {
		return $this->filesize;
	}

	//take picture!
	public function takePicture($pic_subject, $color) {
		$newPic = new Picture($this->shutter, $this->fstop, $this->iso, $pic_subject, $color);
		$this->pictures[] = $newPic; //add picture to array 
		$this->shotsLeft(); //updates number of shots left
	}
	
	//count exposures
	public function countPictures() {
		return count($this->pictures);
	}

	//calculate how many exposures left using current settings
	protected function shotsLeft() {
		$this->picsLeft = $this->capacity - $this->countPictures();
		return $this->picsLeft; 
	}

	//print all pictures from this camera instance
	public function printAll() {
		$metadata = '';
		//loop through the array called pictures, each time through assign each item to new variable called $picture:
		foreach($this->pictures as $picture) {	
			//		
			$metadata .= $picture->printPicture($this->make, $this->model);
		}
		return $metadata;
	}	
}

class Filmcamera extends Camera {
	protected $filmFormat = '';
								
	public function __construct($make, $model, $pro, $capacity, $filmFormat) {
		parent::__construct($make, $model, $pro, Camera::FILM, $capacity); //CALLS parent class- need to include args
		$this->filmFormat = $filmFormat; //define any thing new added or overridden
	}
	//take a film picture!
	public function takePicture($pic_subject, $color) {
		$newPic = new Filmpic($this->shutter, $this->fstop, $this->iso, $color, $pic_subject, $this->filmFormat);
		$this->pictures[] = $newPic; //add picture to array 
		$this->shotsLeft(); //updates number of shots left
	}
	//delete a picture
	public function deletePicture($frameNum) { //
		/*
		Loop through pictures
			if picture->frameNum == $frameNum
				DELETE!!
		Update all my properties for the camera
		*/
		foreach($this->pictures as $key=>$picture) { //go through each picture instance in array, grab $key and $value (which is a $picture object).  
			// $chosen_frame_num = $picture->getFrameNum();
			// if($chosen_frame_num == $frameNum) {
			if($picture->getFrameNum() == $frameNum) { //get frame number of picture, if it's a match, delete. 
				unset($this->pictures[$key]); // delete the item with this $key.
				break;
			}
		}
		$this->shotsLeft();
	}
}

class Digcamera extends Camera {
	protected $fileFormat = '';
	protected $fileSize = 0;
	
	public function __construct($make, $model, $pro, $capacity, $fileFormat, $fileSize) {
		parent::__construct($make, $model, $pro, Camera::DIGITAL, $capacity);
		$this->fileFormat = $fileFormat;
		$this->fileSize = $fileSize;
	}

	public function setFileSize($set_filesize){
		//validate is a number?
		$this->fileSize = $set_filesize;
	}
	public function setFileFormat($set_fileFormat){
		//validate is a number?
		$this->fileFormat = $set_fileFormat;
	}
	public function getFilesize() {
		return $this->filesize;
	}
	public function getFileFormat() {
		return $this->fileFormat;
	}

	public function takePicture($pic_subject, $color) {
		$newPic = new Digpic($this->shutter, $this->fstop, $this->iso, $color, $pic_subject, $this->fileFormat, $this->fileSize);
		$this->pictures[] = $newPic; //add picture to array 
		$this->shotsLeft(); //updates number of shots left

	}
	//delete a picture
	public function deletePicture($fileName) { //
		/*
		Loop through pictures
			if picture->frameNum == $frameNum
				DELETE!!
		Update all my properties for the camera
		*/
		foreach($this->pictures as $key=>$picture) { //loop through array, grab $key and $value (which here is a $picture object store in variable $picture.)  
			// $chosen_file_name = $picture->getFileName(); 
			// if($chosen_file_name == $fileName) {
			if($picture->getFileName() == $fileName) { //get frame number of picture, if it's a match, delete. 
				unset($this->pictures[$key]); // use the $key.
				break;
			}
		}
		$this->shotsLeft();
	}
}

//	PICTURES  ------------------------------------------------------------------------------------
class Picture {

	const COLOR = 'color';
	const BW = 'black and white';
	
	protected $shutter = 125;
	protected $fstop = 16;
	protected $iso = 160;
	protected $color = true; // false means BW
	protected $subject = '';
	protected $image_id;

	public function __construct($shutter, $fstop, $iso, $color, $subject) {
		$this->shutter = $shutter;
		$this->fstop = $fstop;
		$this->iso = $iso;
		$this->color = $color;
		$this->subject = $subject;
		$this->image_id = get_image_id(); //automatically returns a unique id for each image. 
	}

	// is picture color or bw?
	public function isColor() {
		if ($this->color === true) {
			return Picture::COLOR;
		} 
		else {
			return Picture::BW;
		}
	}

	public function printPicture($make = '', $model = '') {
		$picture_data = 'This is a ' . $this->isColor() . ' picture of ' . $this->subject;
		$picture_data .= ',' . ' taken with a ' . $make . ' ' . $model . '. <br>';

		return $picture_data;
	}	
}

class Filmpic extends Picture {
	protected $filmFormat = '';
	protected $frameNum;

	public function __construct($shutter, $fstop, $iso, $color, $subject, $filmFormat) {
		parent::__construct($shutter, $fstop, $iso, $color, $subject);
		$this->filmFormat = $filmFormat;
		$this->frameNum = get_frame_num(); //automatically returns a unique frame number for each frame, in sequence.
	}
	public function getFrameNum() {
		return $this->frameNum;
	}
	public function printPicture($make = '', $model = '') {
		$film_picture_data = parent::printPicture($make, $model); 
		$film_picture_data .= 'This was shot on ' . $this->filmFormat . ' format film, how retro!  <br>';
		return $film_picture_data;
	}
}

class Digpic extends Picture {
	const RAW = 'Raw';
	const JPEG = 'jpeg';

	protected $fileFormat = true; 
	protected $fileSize = 0; // in MP's
	protected $fileName;

	public function getFileName() {
		return $this->fileName;
	}

	public function __construct($shutter, $fstop, $iso, $color, $subject, $fileFormat, $fileSize) {
		parent::__construct($shutter, $fstop, $iso, $color, $subject);
		$this->fileFormat = $fileFormat;
		$this->fileSize = $fileSize;
		$this->fileName = get_file_name(); //automatically returns a unique file name for each file.
	}	

	// is format Raw or jpeg? 
	public function isRaw() {
		if ($this->fileFormat === true) {
			return Digpic::RAW;
		} 
		else {
			return Digpic::JPEG;
		}
	}
	public function printPicture($make = '', $model = '') {
		$digital_picture_data = parent::printPicture($make, $model); 

		$digital_picture_data .= 'It is a ' . $this->fileSize . ' megapixel ' . $this->isRaw() . ' file. <br>'; 
		return $digital_picture_data;
	}	
}

// FILM CAMERAS:   			
$filmCam1 = new Filmcamera('hasselblad', '500C', true,  24, '35mm'); //choose a camera out of your bag
// set exposure
// $filmCam1->setShutter(4);
// $filmCam1->setFstop(8);                    
// $filmCam1->setIso(1200);

//take pictures with this camera:
$filmCam1->takePicture('puppies', false); //usese set (above) exposure settings
// $filmCam1->takePicture('selfie', true); //uses default exposure settings

print $filmCam1->printAll();
print_r($filmCam1);
print ('<br>');
// $filmCam1->deletePicture(1);

print ('<br>');
// print $filmCam1->printAll();
// print_r($filmCam1);
// $filmCam1->takePicture('more puppies', true);
// print_r($filmCam1);

//CAMERA 2:
// $filmCam2 = new Filmcamera('nikon', 'F3', true, '35mm', 36);
// $filmCam2->takePicture(true, 'ponies');
// $filmCam2->takePicture(false, 'multitudes');

// print $filmCam2->printAll();
// print ('<br>');

//CAMERA 3:
// $filmCam3 = new Filmcamera('cannon', 'AE-1', false, '35mm', 36);
// $filmCam3->takePicture(false,'yo face');
// $filmCam3->takePicture(true, 'some guy named Albert');


// print $filmCam3->printAll();
// print ('<br>');

// print_r($filmCam2);
// print_r($filmCam3);

//DIGITAL CAMERAS 	 
                 
$digCam1 = new Digcamera('cannon', '5D', true, 800, false, 40);
// //settings:
// $digCam1->setShutter(30);
// $digCam1->setFstop(16);                    
// $digCam1->setIso(640);
// $digCam1->setFileSize(40);

// //pictures taken:
$digCam1->takePicture('purple monkeys', false);

// $digCam1->setShutter(125);
// $digCam1->setFstop(8);
// $digCam1->setFileFormat(true);

// $digCam1->takePicture('black coffee with 2 sugars', true);


print ('<br>');
print $digCam1->printAll();
print ('<br>');
print_r($digCam1);


// $digCam1->deletePicture(2);
// print ('<br>');
// print_r($digCam1);

// $digCam1->takePicture('37 baboons', false);

// print ('<br>');
// print_r($digCam1);

// $digCam1->deletePicture(1);

// print ('<br>');
// print_r($digCam1);
// $digCam2 = new Digcamera('nikon', 'D700', true, Camera::DIGITAL, 1500, true);

// $digCam2->setShutter(90);
// $digCam2->setFstop(8.5);                    
// $digCam2->setIso(200);
// $digCam2->setFilesize(40);

// $digCam2->takePicture('bluebirds', true);
// $digCam2->takePicture('my son Walter', false);

// print ('<br>');
// print $digCam2->printAll();
// print ('<br>');
// // print_r($digCam2);

// $digCam3 = new Digcamera('hasselblad', 'H-4D', true, Camera::DIGITAL, 150, false);

// $digCam3->setFstop(16);                    
// $digCam3->setIso(640);

// $digCam3->takePicture('apples', false);
// $digCam3->takePicture('sadness', true);

// print ('<br>');
// print $digCam3->printAll();
// print ('<br>');
// print_r($digCam3);


function get_frame_num() {
	static $frame_num = 0;
	$frame_num++;
	return $frame_num;
}

function get_file_name() {
	static $file_name = 0;
	$file_name++;
	return $file_name;
}

function get_image_id() {
	static $img_id = -1;
	$img_id++;
	return $img_id;
}

function get_camera_id() {
	static $cam_id = -1;
	$cam_id++;
	return $cam_id;
}



// Pass by Reference.
$arr = array(1, 2, 3, 4);
foreach ($arr as $value) {
    $value = $value * 2;
}
print_r($arr);

$arr2 = array(1, 2, 3, 4);
foreach ($arr2 as &$value) {
    $value = $value * 2;
}
print_r($arr2);



$arr3 = array('bob' => 1, 'fred' => 2, 'tom' => 3, 4);

print_r($arr3);


?>
</pre>

</body>
</html>