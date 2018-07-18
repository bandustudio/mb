<?php
//! delete

/** Edit fields ending with "_url" by <input type="file"> and link to the uploaded files from select
* @link https://www.adminer.org/plugins/#use
* @author Jakub Vrana, http://www.vrana.cz/
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Intervention\Image\ImageManager;

class AdminerFileUpload {
	/** @access protected */
	var $uploadPath, $displayPath, $extensions;

	/**
	* @param string prefix for uploading data (create writable subdirectory for each table containing uploadable fields)
	* @param string prefix for displaying data, null stands for $uploadPath
	* @param string regular expression with allowed file extensions
	*/
	function __construct($uploadPath = "../static/data/", $displayPath = null, $extensions = "[a-zA-Z0-9]+") {
		$this->uploadPath = $uploadPath;
		$this->displayPath = ($displayPath !== null ? $displayPath : $uploadPath);
		$this->extensions = $extensions;
	}

	function head() {
		echo '
		<link href="images/iso-automovilshop.png" rel="shortcut icon" type="image/x-icon">
		<link href="images/iso-automovilshop-big.png" rel="apple-touch-icon">    
		<link href="css/bootstrap.css" type="text/css" rel="stylesheet" />
		<link href="css/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" />
		<link href="css/font-awesome.min.css" type="text/css" rel="stylesheet" />
		<link href="css/summernote.css" type="text/css" rel="stylesheet" />
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/moment.js"></script>
		<script type="text/javascript" src="js/bootstrap-datetimepicker.js"></script>
		<script type="text/javascript" src="js/summernote.min.js"></script>
		<script type="text/javascript">
		$(function(){
			$("input,textarea").each(function(){
				var name = $(this).attr("name")||""

				if(name.indexOf("_text") > -1){
					$(this).summernote({
					    height: 200,   
					    minHeight: null,
					    maxHeight: null,
					    onImageUpload: function(files, editor, welEditable) {
					      sendFile(files[0], editor, welEditable);
					    }
					  });
				}
				if(name.indexOf("_datetime") > -1){
					$(this).datetimepicker()
				}
			})
		})

		function sendFile(file, editor, welEditable) {
		    data = new FormData();
		    data.append("file", file);
		    $(".summernote-progress").removeClass("hide").hide().fadeIn();
		    $.ajax({
		        data: data,
		        type: "POST",
		        xhr: function() {
		            var myXhr = $.ajaxSettings.xhr();
		            if (myXhr.upload) myXhr.upload.addEventListener("progress",progressHandlingFunction, false);
		            return myXhr;
		        },        
		        url: "/image/simpleupload",
		        cache: false,
		        contentType: false,
		        processData: false,
		        success: function(url) {
		          $(".summernote-progress").fadeOut();
		          editor.insertImage(welEditable, url);
		        }
		    });
		}   

		function progressHandlingFunction(e){
		    if(e.lengthComputable){
		        var perc = Math.floor((e.loaded/e.total)*100);
		        $(".progress-bar").attr({"aria-valuenow":perc}).width(perc+"%");
		        // reset progress on complete
		        if (e.loaded == e.total) {
		            $(".progress-bar").attr("aria-valuenow","0.0");
		        }
		    }
		}		
		</script>';
	}

	function database() {
		return 'mercedesbenz';
	}

	function editInput($table, $field, $attrs, $value) {

		if (preg_match('~(.*)_url$~', $field["field"])) {
			return "<img src='$value' width='200'><br><input type='file' name='fields-$field[field]'>";
		}
	}

	function processInput($field, $value, $function = "") {
		if (preg_match('~(.*)_url$~', $field["field"], $regs)) {

	        $manager = new ImageManager();

			$table = ($_GET["edit"] != "" ? $_GET["edit"] : $_GET["select"]);
			$name = "fields-$field[field]";

			if ($_FILES[$name]["error"] || !preg_match("~(\\.($this->extensions))?\$~", $_FILES[$name]["name"], $regs2)) {
				return false;
			}
			
			$url = "";
			$key = uniqid() . $regs2[0];

		    try {

		        if(getenv('S3_ENABLED')){

		            $s3 = new S3Client([
		                'version' => 'latest',
		                'region'  => getenv('S3_REGION'),
		                'credentials' => [
		                    'key'    => getenv('S3_KEY'),
		                    'secret' => getenv('S3_SECRET')
		                ]
		            ]);

		            $orig = $manager->make($_FILES[$name]['tmp_name'])
		                ->stream()
		                ->__toString();

		            $s3->putObject([
		                'Bucket' => getenv('S3_BUCKET'),
		                'Key'    => $key,
		                'Body'   => (string) $orig,
		                'ACL'    => 'public-read',
		            ]);

		            $url = 'https://' . getenv('S3_BUCKET') . '.s3.amazonaws.com/' . $key;
		        } else {

		            $url = getenv('UPLOADS_URL') . '/' . $key;

		            $orig = $manager->make($_FILES[$name]['tmp_name'])
		                ->orientate()
		                ->save(getenv('UPLOADS_PATH') . '/' . $key, (int) getenv('S3_QUALITY'));

		        }
		    } catch (S3Exception $e) {
		      // Catch an S3 specific exception.
		        die($e->getMessage());
		    }

			return q($url);
		}
	}

	function selectVal($val, &$link, $field, $original) {
		if ($val != "&nbsp;" && preg_match('~(.*)_url$~', $field["field"], $regs)) {
			//$link = "$this->displayPath$_GET[select]/$regs[1]-$val";
			return "<a href='$val' target='_blank' title='$val'><div style='background-image: url($val); background-repeat: no-repeat;background-size: contain; width:80px;height:40px'></div></a>";
		}
	}
}