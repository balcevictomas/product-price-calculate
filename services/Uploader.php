<?php
namespace WPPImport\Services;

class Uploader
{
	private $input;
    private $path;
    private $file_name;
    private $file;
	private $max_file_size;
	private $error;
	private $error_messages;
	private $success;
	private $success_messages;
	
	public function __construct (
		$input,
		$path,
		$file_name = null,
		$mimes = null,
		$max_file_size = 5000000 //~5mb
		)
	{
		if($input) {
            $this->setInput($input);
        }
		
		if($path) {
			$this->path = $path;
			$this->setPath($path);
        }
		
		$this->renameFile($file_name);
		
		if($mimes) {
			$this->setMimes($mimes);
		}
		
		$this->setMaxFileSize($max_file_size);
    }
	
	public function setInput($input)
	{
		$this->file = null;
		if (isset($_FILES[$input]['name']) && !empty($_FILES[$input]['name']) && !empty($_FILES[$input]['tmp_name'])) {
			$this->file['tmp_name'] = $_FILES[$input]['tmp_name'];
			$this->file['name'] = $_FILES[$input]['name'];
			$this->file['mime'] = $_FILES[$input]['type'];
			$this->file['error'] = $_FILES[$input]['error'];
			$this->file['size'] = $_FILES[$input]['size'];
		}
	}
	
	public function setName($file_name)
    {
        $this->renameFile($file_name);
    }
	
	public function setPath($path)
    {
		if (!file_exists($path)) {
			throw new \Exception('Bad path!');
		}
        $this->path = $path;
    }
	
	public function getName()
	{
		return $this->file_name;
	}
	
	public function setMimes(array $mimes)
    {
        $this->mimes = $mimes;
    }
	
	public function setMaxFileSize($max_file_size)
	{
		if (!is_int($max_file_size)) {
			throw new \Exception('File size must be integer!');
		}
		$this->max_file_size = $max_file_size;
	}
	
	/**
	* Renames file to prevent directory traversal attacks
	*
	* @param string $file_name file name
	* @return void
	*/
	private function renameFile($file_name)
	{
		$ext = strtolower(substr($this->file['name'], strripos($this->file['name'], '.') + 1));

		if (!$file_name) {
			$this->file_name = round(microtime(true)) . mt_rand() . '.' . $ext;
		} else {
			if (!is_string($file_name)) {
				throw new \Exception('Name must be string!');
			} else {
				$this->file_name = $file_name . '.' . $ext;
			}

		}
	}
	
	public function upload()
	{
		//check if path is set
		if (empty($this->path)) {
			throw new \Exception('Path is not set');
		}
		
		//check if file is selected
		if (!$this->file) {
			$this->error = $this->error_messages['empty'];
			return false;
		}
		
		//check if file type is allowed
		if (!$this->isAllowedFileType()) {
			$this->error = $this->error_messages['type'];
			return false;
		}
		
		//check if file is uploaded via HTTP POST.
		// if (!is_uploaded_file($this->file['tmp_name'])) {
			// $this->error_type = 'error';
			// return false;
		// }
		
		//check is file alowed size
		if ($this->file['size'] > $this->max_file_size) {
			$this->error = $this->error_messages['size'];
			return false;
		}
		
		if ($this->file['error']) {
			return false;
		}
		
		if (!move_uploaded_file($this->file['tmp_name'], $this->path . $this->file_name)) {
			$this->error_type = 'error2';
			return true;
		}
		return true;
	}
	
	private function isAllowedFileType()
    {
		$mimes = $this->getAllowedMimeTypes();
		
		$type = false;
		$ext = false;

		foreach($mimes as $ext_preg => $mime_match) {
			//echo $ext_preg . '=>' . $mime_match.'<br />';
			$ext_preg = '!\.(' . $ext_preg . ')$!i';
			
			if(preg_match($ext_preg, $this->file['name'], $ext_matches)) {
				$type = $mime_match; // text/csv
				$ext = $ext_matches[1]; // csv
				break;
			}
		}
		
		if (!$type || !$ext) {
			return false;
		}

		return true;
    }
	
	public function checkUrlDomain($url, $domain_id)
	{
		global $wpdb;
		$domain_id = intval($domain_id);
		
		if(!$domain_id) $this->error = $this->error_messages['domain'];
		if(!$domain_id) return false;
		
		$domain_data = $wpdb->get_results( "SELECT id, name FROM fx_domains WHERE id = {$domain_id}" );
		
		$parse_domain = parse_url($domain_data[0]->name);
		$domain_from_database = $parse_domain['path'];
		
		$parse_url_domain = parse_url($url);
		$domain_from_url = $parse_url_domain['host'];
		
		if(strpos($domain_from_url, $domain_from_database) === FALSE) { // kur ieskau, ko ieskau
			$this->error = $this->error_messages['domain'];
			return false;
		} else {
			return true;
		}
	}
	
	public function checkColumnName($column_name)
	{
		if(strpos($column_name, '(desc)') === FALSE) {
			$this->error = $this->error_messages['column'];
			return false;
		} else {
			return true;
		}
	}
	
	public function checkFileCountry($file_country)
	{
		$file_country = sanitize_text_field($file_country);
		
		if(!$file_country) {
			$this->error = $this->error_messages['file_name'];
			return false;
		}
		
		return $file_country;
	}
	
	public function isEmpty($data)
	{
		if(empty($data)) {
			$this->error = $this->error_messages['empty_data'];
			return true;
		} else {
			return false;
		}
	}
	
	public function success()
	{
		$this->success = $this->success_messages['import_complete'];
	}
	
	public function setErrorMessages(array $error_messages)
	{
		$this->error_messages = $error_messages;
	}
	
	public function setSuccessMessages(array $success_messages)
	{
		$this->success_messages = $success_messages;
	}
	
	public function getError()
	{
		return $this->error;
	}
	
	public function getSuccessNotification()
	{
		return $this->success;
	}
	
	public function getAllowedMimeTypes()
    {
        if (!empty($this->mimes)) {
            return $this->mimes;
        }

        return array(
            // Image formats
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'tif|tiff' => 'image/tiff',
            'ico' => 'image/x-icon',

            // Video formats
            'asf|asx' => 'video/x-ms-asf',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wm' => 'video/x-ms-wm',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov|qt' => 'video/quicktime',
            'mpeg|mpg|mpe' => 'video/mpeg',
            'mp4|m4v' => 'video/mp4',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',

            // Text formats
            'txt|asc|c|cc|h' => 'text/plain',
            'csv' => 'text/csv',
            'tsv' => 'text/tab-separated-values',
            'ics' => 'text/calendar',
            'rtx' => 'text/richtext',
            'css' => 'text/css',
            'htm|html' => 'text/html',

            // Audio formats
            'mp3|m4a|m4b' => 'audio/mpeg',
            'ra|ram' => 'audio/x-realaudio',
            'wav' => 'audio/wav',
            'ogg|oga' => 'audio/ogg',
            'mid|midi' => 'audio/midi',
            'wma' => 'audio/x-ms-wma',
            'wax' => 'audio/x-ms-wax',
            'mka' => 'audio/x-matroska',

            // Misc application formats
            'rtf' => 'application/rtf',
            'js' => 'application/javascript',
            'pdf' => 'application/pdf',
            'swf' => 'application/x-shockwave-flash',
            'class' => 'application/java',
            'tar' => 'application/x-tar',
            'zip' => 'application/zip',
            'gz|gzip' => 'application/x-gzip',
            'rar' => 'application/rar',
            '7z' => 'application/x-7z-compressed',
            'exe' => 'application/x-msdownload',

            // MS Office formats
            'doc' => 'application/msword',
            'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
            'wri' => 'application/vnd.ms-write',
            'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
            'mdb' => 'application/vnd.ms-access',
            'mpp' => 'application/vnd.ms-project',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',

            // OpenOffice formats
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odf' => 'application/vnd.oasis.opendocument.formula',

            // WordPerfect formats
            'wp|wpd' => 'application/wordperfect',

            // iWork formats
            'key' => 'application/vnd.apple.keynote',
            'numbers' => 'application/vnd.apple.numbers',
            'pages' => 'application/vnd.apple.pages',
        );
    }
}
