<?php 
class WCUF_Media
{
	function __construct()
	{
		//File proxy managment
		add_action('plugins_loaded', array( &$this, 'get_image' )); //wp_loaded
		add_action( 'wp_ajax_wcuf_rotate_image', array( &$this, 'ajax_rotate_image' ));
		add_action( 'wp_ajax_nopriv_wcuf_rotate_image', array( &$this, 'ajax_rotate_image' ));
	}
	function get_sum_of_media_length($uploaded_data)
	{
		if(!isset($uploaded_data))
			return 0;
		
		$length = 0;
		if(isset($uploaded_data["ID3_info"]))
			foreach((array)$uploaded_data["ID3_info"] as $media)
			{
				if(isset($media['playtime_seconds']))
					$length += $media['playtime_seconds'];
			}
		return $length;
	}
	function ajax_rotate_image()
	{
		global $wcuf_file_model;
		//wcuf_var_dump($_FILES);
		//$file = $_FILES["image"]['tmp_name'];
		
		ini_set('memory_limit', '2048M');
		$file = $wcuf_file_model->get_temp_dir_path().$_POST['session_id']."_".$_POST['file_name'];
					
		//$degrees =  isset($_POST['direction']) && $_POST['direction'] == 'right' ? -90 : +90;
		$degrees =  isset($_POST['degrees']) ? $_POST['degrees'] : +90;

		// Content type
		//header('Content-type: image/jpeg');

		// Load
		$mime = mime_content_type($file);
	/* 	wcuf_var_dump($file);
		wcuf_var_dump($mime);
		wcuf_var_dump($_POST); */
		/* $string = file_get_contents ($file);
		$source = imagecreatefromstring($string); */
		$source =  $mime == 'image/png' ? imagecreatefrompng($file) : imagecreatefromjpeg($file);
		if($mime == 'image/png')
		{
			$transparency = imagecolorallocatealpha($source, 0, 0, 0, 127);
			// Rotate
			$rotate = imagerotate($source, $degrees, $transparency, 1);
			imagealphablending($rotate, false);
			imagesavealpha($rotate, true);
		} 
		else
			$rotate = imagerotate($source, $degrees, 0);

		// Output
		ob_start();
		if($mime == 'image/png')
		 imagepng($rotate);
		else
		 imagejpeg($rotate);
		$data = ob_get_contents();
		ob_end_clean();
		
		if($mime == 'image/png')
			echo 'data:image/png;base64,' .base64_encode($data);
		else
			echo 'data:image/jpeg;base64,' .base64_encode($data); //image/jpeg
		
		//echo "<img src=$base64 />" ;
		// Free the memory
		imagedestroy($source);
		imagedestroy($rotate);
		wp_die();
	}
	public function get_media_preview_html($field_data, $file_name, $is_zip, $order_id, $counter, $return_preview_only_for_images = false, $additional_options = array())
	{
		global $wcuf_file_model,$wcuf_upload_field_model;
		$is_temp_upload = !isset($field_data['absolute_path']);
		//new multiple file managment
		$index = $is_temp_upload  ? "tmp_name" : "absolute_path";
		$file_full_path = is_array($field_data[$index]) ? $field_data[$index][$counter] : $field_data[$index];
		
		$current_file_name = isset($field_data["original_filename"] ) ? $field_data["original_filename"][$counter] : $field_data["name"][$counter];
		
		//New folder organization: files are stored in "product_id-variation_id" folder
		$product_ids = isset($field_data["id"]) ? explode("-",$field_data["id"]) : null;
		$product_id_folder_name = "";
		if(isset($product_ids) && isset($product_ids[1]))
		{
			$upload_field_ids = isset($product_ids[2]) ? $product_ids[1]."-".$product_ids[2] : $product_ids[1]."-0";
			$upload_field_ids = isset($product_ids[3]) ? $upload_field_ids."-".$wcuf_upload_field_model->get_individual_id_from_string($product_ids[3]) : $upload_field_ids; //sold as individual id
			$upload_field_ids =    apply_filters('wcuf_order_sub_folder_name', $upload_field_ids, 
																				$product_ids[1],
																				isset($product_ids[2]) ? $product_ids[2] : 0,
																				isset($product_ids[3]) ? $wcuf_upload_field_model->get_individual_id_from_string($product_ids[3]) : false,
																				 null);
			$product_id_folder_name = "&wcuf_product_folder_name=".$upload_field_ids;
		}
		
		global $wcuf_option_model; 
		$all_options = $wcuf_option_model->get_all_options();
		$all_options['image_preview_width'] = $all_options['image_preview_method'] == 'new' ? "" : $all_options['image_preview_width'];
		$all_options['image_preview_height'] = $all_options['image_preview_method'] == 'new' ? "" : $all_options['image_preview_height'];
	
		$preview_type = wcuf_get_value_if_set($additional_options, 'preview_type', false) != false ?  $additional_options['preview_type'] : 'generic';
		$image_preview_width = wcuf_get_value_if_set($additional_options, 'width', false) != false ? $additional_options['width'] : $all_options['image_preview_width'];
		$image_preview_height = wcuf_get_value_if_set($additional_options, 'height', false) != false ? $additional_options['height'] : $all_options['image_preview_height'];
		$css_classes = wcuf_get_value_if_set($additional_options, 'classes',false) != false ? $additional_options['classes'] : "wcuf_file_preview_list_item_image";
		
		$file_name_real_name = basename($file_full_path);
		
		//DropBox managment
		/* if(WCUF_DropBox::is_dropbox_file_path($file_full_path) != false)
		{
			$dropbox = new WCUF_DropBox();
			$file_full_path = $dropbox->getTemporaryLink($file_full_path, true);
		} */
		
		if($is_zip) //old zip managment method, no longer used
		{
			if(class_exists('ZipArchive'))
			{
				$z = new ZipArchive();
				if ($z->open($file_full_path) && $z->filename != "") 
				{
					$im_string = $z->getFromName($file_name);
					$image = @imagecreatefromstring($im_string);
					$z->close();
				}
			}
			else return "";
			
			if($image === false)
				return "";
		}
		else		
		{
			$image_data = @getimagesize($file_full_path);
			$image = @is_array($image_data) ? true : false;
			
		}
	
		//no bmp and adobe photshop psd preview
		if($image && isset($image_data) && ($image_data['mime'] == 'image/x-ms-bmp' || preg_match('/(photoshop|psd)$/', $image_data['mime'])))
			//return ""; 
			return !$return_preview_only_for_images ? $this->get_preview_icon($current_file_name) : ""; 
		$is_dropbox = WCUF_DropBox::is_dropbox_file_path($file_full_path);
		//$is_zip = $is_zip ? "true": "false";
		//return $image !== false ? '<img class="wcuf_file_preview_list_item_image" width="'.$image_preview_width.'" height="'.$image_preview_height.'" src="'.get_site_url().'?wcuf_file_name='.$file_name_real_name.'&wcuf_image_name='.$file_name.'&wcuf_is_zip='.$is_zip.'&wcuf_order_id='.$order_id.'"></img>' : "";
		if($is_zip && $image !== false)
		{
			$is_zip = $is_zip ? "true": "false";
			
			//DropBox managment
			if($is_dropbox)
				//return "";
				return !$return_preview_only_for_images ? $this->get_preview_icon($current_file_name) : "";
		
			return '<img class="'.$css_classes.'" width="'.$image_preview_width.'" height="'.$image_preview_height.'" src="'.get_site_url().'?wcuf_file_name='.$file_name_real_name.'&wcuf_image_name='.$file_name.'&wcuf_is_zip='.$is_zip.'&wcuf_order_id='.$order_id.'&preview_type='.$preview_type.'"></img>';
		}
		elseif(!$is_zip)
		{
			$file_name = $is_temp_upload  ? $field_data['file_temp_name'][$counter] : $file_name ;
			$temp_dir = $wcuf_file_model->get_temp_dir_path($order_id, true);
			$url = isset($field_data['url'])? $field_data['url'][$counter] : $temp_dir.$file_name;
			$is_remote_image = /* !isset($field_data["ID3_info"][$counter]["index"]) */ $is_dropbox && $this->is_image($url);
			
			
			//Old preview: was valid for local and remote. Local file preview was not compressed and this can cause some problems
			/* if($image  || $is_remote_image)
				return '<img class="wcuf_file_preview_list_item_image" width="'.$image_preview_width.'" height="'.$image_preview_height.'" src="'.$url .'"></img>'; */
			
			//New method: local files preview is compressed
			if($image || $is_remote_image)
			{
				if(!$is_remote_image)
				{
					//compressed
					$file_name_real_name = $order_id != 0 ? $file_name : $file_name_real_name; //$file_name_real_name contains the full path when the $order_id is not 0
					return '<img class="'.$css_classes.'" width="'.$image_preview_width.'" height="'.$image_preview_height.'" src="'.get_site_url().'?wcuf_file_name='.$file_name_real_name.'&wcuf_image_name='.$file_name.'&wcuf_is_zip=false&wcuf_order_id='.$order_id.$product_id_folder_name.'&preview_type='.$preview_type.'"></img>';
				}
				elseif($is_dropbox)
				{
					//return '<img class="wcuf_file_preview_list_item_image" width="'.$image_preview_width.'" height="'.$image_preview_height.'" src="'.$url .'"></img>';
					return '<img class="'.$css_classes.'" width="'.$image_preview_width.'" height="'.$image_preview_height.'" src="'.get_site_url().'?wcuf_file_name='.$file_full_path.'&wcuf_image_name='.$file_name.'&wcuf_is_zip=false&wcuf_order_id='.$order_id.'&preview_type='.$preview_type.'&rand='.$preview_type.'"></img>'; //'&full_url='.$url.'
				}
			}
			//end new compressed method
			elseif(isset($field_data["ID3_info"][$counter]["index"]) && $field_data["ID3_info"][$counter]['type'] == 'audio' /* $this->is_audio_file($file_full_path) */ )
			{
				//$url = !isset($field_data['file_temp_name'][$counter])  ? $field_data['url'][$counter] : $temp_dir.$field_data['file_temp_name'][$counter];
				$url = isset($field_data['url'][$counter])  ? $field_data['url'][$counter] : $temp_dir.$field_data['file_temp_name'][$counter];
				return !$return_preview_only_for_images ? '<audio class="wcuf_audio_control" controls><source src="'.$url.'   "type="audio/ogg"><source src="'.$url.' "type="audio/mpeg"></audio>' : "";
			}
		}
		
		//return "";
		return !$return_preview_only_for_images ? $this->get_preview_icon($current_file_name) : "";
	}
	//Used in FRONTEND by links generated by get_media_preview_html() method. In case of Dropbox that method doesn't not generate any preview link
	public function get_image()
	{
		global $wcuf_file_model, $wcuf_option_model;
		if(!isset($_GET['wcuf_file_name']) || !isset($_GET['wcuf_image_name']) || !isset($_GET['wcuf_is_zip']))
			return;
		
		$temp_dir = $wcuf_file_model->get_temp_dir_path(isset($_GET['wcuf_order_id']) ? $_GET['wcuf_order_id'] : null);
		
		//DropBox managment
		if(WCUF_DropBox::is_dropbox_file_path($_GET['wcuf_file_name']))
		{
			try
			{
				$dropbox = new WCUF_DropBox();
				$dropbox->render_thumb($_GET['wcuf_file_name']);
			}catch(Exception $e){ /* wcuf_var_dump($e); */ _e('DropBox account unlinked', 'woocommerce-files-upload'); /* wp_redirect($_GET['full_url']); */}
		}
		elseif($_GET['wcuf_is_zip'] === "true")
		{
			if(class_exists('ZipArchive'))
			{
				$z = new ZipArchive();
				if ($z->open($temp_dir.$_GET['wcuf_file_name'])) 
				{
					$im_string = $z->getFromName($_GET['wcuf_image_name']);
					//$type = $this->image_file_type_from_binary($im_string);
					$im = imagecreatefromstring($im_string);
					
					//original
					/* header('Content-Type: image/png');
					imagepng($im, null, 9);  */
					
					header('Content-Type: image/jpeg'); 
					$image_result = imagejpeg($im, null,50); 
					
					//Working alternative
					/* switch($type)
					{
							case "image/jpeg":
								header('Content-Type: image/jpeg');
								imagejpeg($im, null,50);
								break;
							case "image/gif":
								header('Content-Type: image/gif');
								imagegif($im);
								break;
							case "image/png":
								header('Content-Type: image/png');
								imagepng($im,null, 9);
								break;
							 //case "image/x-ms-bmp":
								//$im = imagecreatefromwbmp($path); //png file
								//break; 
							default: 
								$im=false;
								break;
					}  */
			
					imagedestroy($im);
					$z->close();
				
				}
			}
		}
		else
		{
			
			$path = isset($_GET['wcuf_product_folder_name']) && isset($_GET['wcuf_order_id']) ? $temp_dir.$_GET['wcuf_product_folder_name']."/".$_GET['wcuf_file_name']: $temp_dir.$_GET['wcuf_file_name'];
			$preview_type=  isset($_GET['preview_type']) ? $_GET['preview_type'] : 'generic';
			
			//wcuf_var_dump($path);
			$fileName = basename($path);
			$all_options = $wcuf_option_model->get_all_options();
			$preview_method = $all_options['image_preview_method'];
			$all_options['image_preview_width']	= $preview_type == 'cart_product_preview' ? 120 : $all_options['image_preview_width'];	
			$all_options['image_preview_height'] =  $preview_type == 'cart_product_preview' ? 120 : $all_options['image_preview_height'];	
			/* wcuf_var_dump($preview_method);
			wp_die(); */
			//New
			if($preview_method == 'new')
			{
				if(!file_exists($path))
				{
					_e('Invalid image path', 'woocommerce-files-upload');
					wp_die();
				}
				$size = getimagesize($path);
				//$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				//Clear all headers
				/* if (!headers_sent()) 
				{
				  foreach (headers_list() as $header)
					header_remove($header);
				} */
				//header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
				//header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
				//header('Location:'.$actual_link);
				
				switch($size["mime"])
				{
						default: 
						case "image/jpeg":
							//header('Content-Type: image/jpeg');
							//ini_set('gd.jpeg_ignore_warning', true);
							$im = @imagecreatefromjpeg($path); //jpeg file
							 if($im == false)
								break;
							/* imagejpeg($im, null, 10); 
							imagedestroy($im); */
							$im = $this->create_resized_preview_or_display_preview($path, $size, $im, $all_options['image_preview_width'], $all_options['image_preview_height']);
							if($im == false)
								break;
							header('Content-Type: image/jpeg');
							imagejpeg($im['resource'], $im['thumb_path'], 75); //ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file)
							imagedestroy($im['resource']); 
							break;
						case "image/gif":
							//header('Content-Type: image/gif');
							$im = imagecreatefromgif($path); //gif file
							/* imagegif($im);
							imagedestroy($im); */
							$im = $this->create_resized_preview_or_display_preview($path, $size, $im, $all_options['image_preview_width'], $all_options['image_preview_height']);
							if($im == false)
								break;
							header('Content-Type: image/gif');
							imagegif($im['resource']);
							imagedestroy($im['resource']);  
							break;
						case "image/png":
							//header('Content-Type: image/png');
							$im = imagecreatefrompng($path); //png file
							/*imagealphablending($im, true);
							imagesavealpha($im, true);
							 imagepng($im,null, 9);
							imagedestroy($im);  */
							$im = $this->create_resized_preview_or_display_preview($path, $size, $im, $all_options['image_preview_width'], $all_options['image_preview_height']);
							if($im == false)
								break;
							header('Content-Type: image/png');
							imagepng($im['resource'],$im['thumb_path'], 4); //from 0 (no compression) to 9. The default (-1) uses the zlib compression default.
							imagedestroy($im['resource']); 
							break;
						 case "image/x-ms-bmp": //doesn't work
							header('Content-Type: image/bmp');
							$im = imagecreatefromwbmp($path); //bmp file
							/* imagewbmp($im);
							imagedestroy($im); */
							$im = $this->create_resized_preview_or_display_preview($path, $size, $im, $all_options['image_preview_width'], $all_options['image_preview_height']);
							if($im == false)
								break;
							imagewbmp($im['resource']);
							imagedestroy($im['resource']); 
							break; 
						/* default: 
							$im=false;
							break; */
				} 
			}
			//Old
			else 
			{
				$size = filesize($path);
				$metadata = getimagesize($path);
				$file_type = $metadata["mime"];
				$ext = $metadata["mime"] == 'image/jpeg' ? '.jpg' : '.png';
				header("Content-length: ".$size);
				//header("Content-type: application/octet-stream");
				header("Content-type: ".$file_type);
				header("Content-disposition: attachment; filename=".$fileName.$ext.";" );
				
				//header('Content-Transfer-Encoding: binary');
				header('Content-Transfer-Encoding: chunked');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				//header("Content-Type: application/download");
				header('Content-Description: File Transfer');
				//header('Content-Type: application/force-download');
				//echo $content;
				if ($fd = fopen ($path, "r")) 
				{

						//set_time_limit(0);
						ini_set('memory_limit', '1024M');
					
					if (ob_get_contents()) ob_clean();
					while(!feof($fd)) {
						echo fread($fd, 4096);
					}   
					flush();
					@ob_end_flush();
					try{
						fclose($fd);
					}catch(Exception $e){}
				} 
			}
		}
		die();
	}
	
	public function get_preview_icon($file_name,  $width = "" , $height = "")
	{
		$preview_name = "generic.png";
		$file_name = pathinfo($file_name, PATHINFO_EXTENSION);
		$file_name = is_string($file_name) ? strtolower($file_name) : "";
		//wcuf_var_dump($file_name);
		switch($file_name)
		{
			case "avi":
			case "mpeg":
			case "mpg":
			case "divx":
			case "xvid":
			case "mp4":
			case "mov":
			case "webm":
			case "mka": $preview_name = "video.png"; break;
			case "flac":
			case "mp3":
			case "wav":
			case "m4a": $preview_name = "audio.png"; break;
			case "bmp":
			case "tiff":
			case "exif":
			case "jpeg":
			case "gif": $preview_name = "image.png"; break;
			case "doc":
			case "docx": $preview_name = "doc.png"; break;
			case "xls":
			case "sxls": $preview_name = "excel.png"; break;
			case "zip":
			case "rar":
			case "tar":
			case "gz":
			case "zip": $preview_name = "zip.png"; break;
			case "pdf": $preview_name = "pdf.png"; break;
		}
		
		$result = '<img class="wcuf_file_preview_icon" src="'.wcuf_PLUGIN_PATH."/img/icons/".$preview_name.'" ';
		$result .= $width != "" ? ' width="'.$width.'" ' : "";
		$result .= $height != "" ? ' height="'.$height.'" ' : "";
		$result .= ' />';
		
		return $result;
	}
	private function create_resized_preview_or_display_preview($file, $mime_info,  $source_gdim, $w, $h)
	{
		global $wcuf_file_model;
		$w = $w == 0 ? null : $w;
		$h = $h == 0 ? null : $h;
		if(($w == 0 && $h == 0) || ($w == null && $h == null))
		{
			$h = $w = 50;
		}
		
		$h_for_file_title = isset($h) ? $h : "no";
		$w_for_file_title = isset($w) ? $w : "no";
		
		//$mime_info = getimagesize($file);
		$source_width = $mime_info[0];
		$source_height = $mime_info[1];
		$source_type = $mime_info[2];
		
		$thumb_path = null;
		//NO USE
		//------------------------------------------------------------------------
		/* $file_name = pathinfo($file, PATHINFO_FILENAME) . "_".$w_for_file_title."x".$h_for_file_title.'_thumb.' . pathinfo($file, PATHINFO_EXTENSION);
		$thumb_path = pathinfo($file, PATHINFO_DIRNAME) . '/' . $file_name;
		if (file_exists($thumb_path))
		{	
			if ( $mime_info['mime'] == "image/jpeg") 
			{
				//$im = imagecreatefromjpeg($thumb_path);
				//header('Content-Length: ' . filesize($thumb_path));
				//readfile($thumb_path);
			} 
			elseif ($mime_info['mime'] == "image/png") 
			{
				//$im = imagecreatefrompng($thumb_path);
				//header('Content-Length: ' . filesize($thumb_path));
				//readfile($thumb_path);
				
			} 
			elseif ($mime_info['mime'] == "image/gif") 
			{
				//$im = imagecreatefromgif($thumb_path);
				//header('Content-Length: ' . filesize($thumb_path));
				//readfile($thumb_path);
				
			}
			//elseif ($source_type == "image/x-ms-bmp") 
			//$im = imagecreatefromgif($thumb_path);
			else 
			{
				return false;
			}
			
			header("location: ".$wcuf_file_model->get_temp_url().$file_name);
		
			//return array('thumb_path' => null, 'resource' => $im);
			return false;
		}   */
		//------------------------------------------------------------------------
		
		//If a width is supplied, but height is false, then we need to resize by width instead of cropping
		//old
		/* if ($w && !$h) 
		{
			$ratio = $w / $source_width;
			$temp_width = $w;
			$temp_height = $source_height * $ratio;
 
			$desired_gdim = imagecreatetruecolor($temp_width, $temp_height);
			if($mime_info['mime'] == "image/png")
			{
				imagealphablending( $desired_gdim, false );
				imagesavealpha( $desired_gdim, true );
			}
			imagecopyresampled(
				$desired_gdim,
				$source_gdim,
				0, 0,
				0, 0,
				$temp_width, $temp_height,
				$source_width, $source_height
			);
		}
		elseif (!$w && $h) {
			$ratio = $h / $source_height;
			$temp_width = $source_width * $ratio;
			$temp_height = $h; 
 
			$desired_gdim = imagecreatetruecolor($temp_width, $temp_height);
			if($mime_info['mime'] == "image/png")
			{
				imagealphablending( $desired_gdim, false );
				imagesavealpha( $desired_gdim, true );
			}
			imagecopyresampled(
				$desired_gdim,
				$source_gdim,
				0, 0,
				0, 0,
				$temp_width, $temp_height,
				$source_width, $source_height
			);
		} 
		else  */
		{
			
			if(function_exists("exif_read_data"))
			{
				$exif = @exif_read_data($file);
				if($exif && !empty($exif['Orientation'])) 
				{
					switch($exif['Orientation']) 
					{
						case 8:
							$source_gdim = imagerotate($source_gdim,90,0);
							$tmp_width = $source_width;
							$source_width = $source_height;
							$source_height = $tmp_width;
							break;
						case 3:
							$source_gdim = imagerotate($source_gdim,180,0);
							break;
						case 6:
							$source_gdim = imagerotate($source_gdim,-90,0);
							$tmp_width = $source_width;
							$source_width = $source_height;
							$source_height = $tmp_width;
							break;
					}
				}
			}
			
		
			$source_aspect_ratio = $source_width / $source_height; // > 1 wider ; < 1 higher; 1 square
			$temp_height = $source_height;
			$temp_width = $source_width;

			//old
			/*$desired_aspect_ratio = $w / $h;
			 if ($source_aspect_ratio > $desired_aspect_ratio) 
			{
				// Triggered when source image is wider
				 
				$temp_height = $h;
				$temp_width = ( int ) ($h * $source_aspect_ratio);
			} 
			else 
			{
				// Triggered otherwise (i.e. source image is similar or taller)
			
				$temp_width = $w;
				$temp_height = ( int ) ($w / $source_aspect_ratio);
			} */
			
			//new 
			$w = $w == 0 || $w == null ? 50 : $w;
			$h = $h == 0 || $h == null ? 50 : $h;
			if ($source_aspect_ratio < 1 ) 
			{
				//if( $source_width > $w) //IMPORTANT: decommenting the images that have sizes lesser than preview size, won't be stretched
				{
					//higher
					$temp_height = $h;
					$temp_width = ( int ) ($h * $source_aspect_ratio);
				}
			} 
			else if($source_aspect_ratio > 1)
			{
				//if($source_height > $h) //IMPORTANT: decommenting the images that have sizes lesser than preview size, won't be stretched
				{
					//wider
					$temp_width = $w;
					$temp_height = ( int ) ($w / $source_aspect_ratio);
				}
			}
			else //square
			{
				$temp_height = $w;
				$temp_width = $h;
			}
			//
			
			
			/*
			 * Resize the image into a temporary GD image
			 */
 
			$temp_gdim = imagecreatetruecolor($temp_width, $temp_height) ;
			if($temp_gdim == false)
			{
				_e('Cannot create thumb', 'woocommerce-files-upload');
				//error_log(print_r(error_get_last()));
				die();
			}
			if($mime_info['mime'] == "image/png")
			{
				imagealphablending( $temp_gdim, false );
				imagesavealpha( $temp_gdim, true );
			}
			imagecopyresampled(
				$temp_gdim,
				$source_gdim,
				0, 0,
				0, 0,
				$temp_width, $temp_height,
				$source_width, $source_height
			);
 
			/*
			 * Copy cropped region from temporary image into the desired GD image
			 */
 
			//old
			/* $x0 = ($temp_width - $w) / 2;
			$y0 = ($temp_height - $h) / 2;
			$desired_gdim = imagecreatetruecolor($w, $h);
			if($mime_info['mime'] == "image/png")
			{
				imagealphablending( $desired_gdim, false );
				imagesavealpha( $desired_gdim, true );
			}
			imagecopy(
				$desired_gdim,
				$temp_gdim,
				0, 0,
				$x0, $y0,
				$w, $h
			); */
			
			//new 
			$x0 = ($temp_width - $w) / 2;
			$y0 = ($temp_height - $h) / 2;
			$desired_gdim = imagecreatetruecolor($temp_width, $temp_height);
			if($mime_info['mime'] == "image/png")
			{
				imagealphablending( $desired_gdim, false );
				imagesavealpha( $desired_gdim, true );
			}
			imagecopy(
				$desired_gdim,
				$temp_gdim,
				0, 0,
				0, 0,
				$temp_width, $temp_height
			);
			
			
		}
 
		return array('thumb_path' => $thumb_path, 'resource' => $desired_gdim);
	}
	private function image_file_type_from_binary($im_string) {
		$type = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $im_string);
		return $type;
	}
	public function is_pdf($file_name)
	{
		if(strpos(strtolower($file_name), '.pdf'))
		   return true;
		   
		return false;
	}
	public function is_image($file_name)
	{
		if(strpos(strtolower($file_name), '.jpg')  ||
		   strpos(strtolower($file_name), '.jpeg') ||
		   strpos(strtolower($file_name), '.png'))
		   return true;
		   
		return false;
	}
	private function is_audio_file($tmp)
	{
		$allowed = array(
        'audio/mpeg', 'audio/x-mpeg', 'audio/mpeg3', 'audio/x-mpeg-3', 'audio/aiff', 
        'audio/mid', 'audio/x-aiff', 'audio/x-mpequrl','audio/midi', 'audio/x-mid', 
        'audio/x-midi','audio/wav','audio/x-wav','audio/xm','audio/x-aac','audio/basic',
        'audio/flac','audio/mp4','audio/x-matroska','audio/ogg','audio/s3m','audio/x-ms-wax',
        'audio/xm'
		);
		
		// check REAL MIME type
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type = finfo_file($finfo, $tmp );
		finfo_close($finfo);
		
		// check to see if REAL MIME type is inside $allowed array
		if( in_array($type, $allowed) ) {
			return true;
		} else {
			return false;
		}

	}
	public function pdf_count_pages($pdfname) 
	{
		if(extension_loaded('imagick'))
		{
			try
			{
				$pdf = new Imagick();
				$pdf->pingImage($pdfname);
				return $pdf->getNumberImages();
			}catch(Exception $e){/* wcuf_write_log($pdfname);wcuf_write_log($e); */}
		}


		$pdftext = file_get_contents($pdfname);
		$num = preg_match_all("/\/Page\W/", $pdftext, $dummy);
		return $num;
	}
}
?>