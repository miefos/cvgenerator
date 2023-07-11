<?php
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\WebM;
use Intervention\Image\ImageManagerStatic as Image;

require_once "PDFCV.php";
require_once "helpers.php";

const MYAPI = [
	'update_cv'                     => [ 'cv_generator/cvpost', '/update' ],
	'download_cv'                   => [ 'cv_generator/cvpost', '/download' ],
	'upload_video'                  => [ 'cv_generator/cvpost', '/upload_video' ],
	'get_video'                     => [ 'cv_generator/cvpost', '/get_video' ],
	'remove_video'                  => [ 'cv_generator/cvpost', '/remove_video' ],
	'get_thumbnail'                 => [ 'cv_generator/cvpost', '/get_thumbnail' ],
	'set_thumbnail_by_video_second' => [ 'cv_generator/cvpost', '/set_thumbnail_by_video_second' ],
	'generate_cv_preview'                => [ 'cv_generator/cvpost', '/generate_cv_preview' ],
	'get_cv_preview'                => [ 'cv_generator/cvpost', '/get_cv_preview' ],
	'check_auth'                => [ 'cv_generator/check_auth', '/check_auth' ],
];

class CVPostType {
	public function __construct(CVSettings $settings) {
//        dd(get_user_meta(get_current_user_id() ));
		add_action( 'init', [$this, 'cv_post_type'], 0);
		add_action( 'transition_post_status', [$this, 'prevent_publishing_posts_publicly'], 10, 3 );
		add_shortcode( 'cv_frontend_fields', [$this, 'cv_frontend_fields_shortcode_html'] );

        // Add custom column to admin dashboard table
		add_filter( 'manage_cv_posts_columns', [$this, 'my_custom_post_type_columns'] );
        // Output content for custom column
		add_action( 'manage_cv_posts_custom_column', [$this, 'my_custom_post_type_column_content'], 10, 2 );

        // Metabox admin dashboard
		add_action( 'add_meta_boxes', [$this, 'wpse_custom_meta_box'] );

        $this->settings = $settings;
        $this->nonce_name = 'wp_rest';

		$this->thumbnail_filename = "thumbnail.jpeg";

		$this->sections = array(
            // section 1
            [
                'label' => __('Personal information', 'cv-generator'),
                'id' => 'section_personal_information',
//                'path' => 'personal_information',
                'save_button' => true,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 96 960 960" width="26"><path d="M480 575q-66 0-108-42t-42-108q0-66 42-108t108-42q66 0 108 42t42 108q0 66-42 108t-108 42ZM160 896v-94q0-38 19-65t49-41q67-30 128.5-45T480 636q62 0 123 15.5t127.921 44.694q31.301 14.126 50.19 40.966Q800 764 800 802v94H160Zm60-60h520v-34q0-16-9.5-30.5T707 750q-64-31-117-42.5T480 696q-57 0-111 11.5T252 750q-14 7-23 21.5t-9 30.5v34Zm260-321q39 0 64.5-25.5T570 425q0-39-25.5-64.5T480 335q-39 0-64.5 25.5T390 425q0 39 25.5 64.5T480 515Zm0-90Zm0 411Z"/></svg>',
                'fields' => [
	                [ 'label' => __('Name', 'cv-generator'), 'id' => 'field_name', 'type' => 'text', 'validation' => ['required'] ],
	                [ 'label' => __('Surname', 'cv-generator'), 'id' => 'field_surname', 'type' => 'text', 'validation' => ['required']],
	                [ 'label' => __('Phone', 'cv-generator'), 'id' => 'field_phone', 'type' => 'tel', ],
	                [ 'label' => __('Biography', 'cv-generator'), 'id' => 'field_bio', 'type' => 'textarea', ],
	                [ 'label' => __('Hobbies', 'cv-generator'), 'id' => 'field_hobbies', 'type' => 'textarea', ],
	                [ 'label' => __('Interests', 'cv-generator'), 'id' => 'field_interests', 'type' => 'textarea', ],
	                [ 'label' => __('Digital skills', 'cv-generator'), 'id' => 'field_digital_skills', 'type' => 'textarea', ],
	                [ 'label' => __('Other skills', 'cv-generator'), 'id' => 'field_other_skills', 'type' => 'textarea', ],
	                [ 'label' => __('I have driving license', 'cv-generator'), 'id' => 'field_i_have_driving_license', 'type' => 'yesno', ],
                ]
            ],

            // section 2
            [
              'label' => __('Languages', 'cv-generator'),
              'id' => 'section_languages',
//              'path' => 'languages',
              'save_button' => true,
              'icon' => '<svg xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 96 960 960" width="26"><path d="M200 936V256h343l19 86h238v370H544l-18.933-85H260v309h-60Zm300-452Zm95 168h145V402H511l-19-86H260v251h316l19 85Z"/></svg>',
              'fields' => [
	              [
		              'label' => __('Languages', 'cv-generator'),
		              'id' => 'field_languages',
		              'type' => 'json',
		              'extra_type' => 'languages',
		              'inner_fields' => [
			              [ 'label' => __('Language name', 'cv-generator'), 'id' => 'field_language_name', 'type' => 'select',
			                'options' => [ __('English', 'cv-generator'), __('Latvian', 'cv-generator'), __('Russian', 'cv-generator') ]
			              ],
			              [ 'label' => 'Proficiency', 'id' => 'field_proficiency', 'type' => 'select',
			                'options' => [
				                __('Native', 'cv-generator'), __('Excellent', 'cv-generator'), __('Good', 'cv-generator'), __('Average', 'cv-generator'), __('Basics', 'cv-generator')
			                ]
			              ]
		              ]
	              ]
              ]
            ],

            // section 3
            [
              'label' => __('Work experience', 'cv-generator'),
              'id' => 'section_work_experience',
//              'path' => 'work_experience',
              'save_button' => true,
              'icon' => '<svg xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 96 960 960" width="26"><path d="M140 936q-24 0-42-18t-18-42V396q0-24 18-42t42-18h180V236q0-24 18-42t42-18h200q24 0 42 18t18 42v100h180q24 0 42 18t18 42v480q0 24-18 42t-42 18H140Zm0-60h680V396H140v480Zm240-540h200V236H380v100ZM140 876V396v480Z"/></svg>',
              'fields' => [
	              [
		              'label' => __('Work experience', 'cv-generator'),
		              'id' => 'field_work_experience',
		              'type' => 'json',
		              'extra_type' => 'work_experience',
		              'inner_fields' => [
			              ['label' => __('Position', 'cv-generator'), 'id' => 'field_position', 'type' => 'text',],
			              [ 'label' => __('Company', 'cv-generator'), 'id' => 'field_company', 'type' => 'text', ],
			              ['label' => __('I am currently working here', 'cv-generator'), 'id' => 'field_still_working', 'type' => 'yesno', ],
			              ['label' => __('From', 'cv-generator'), 'id' => 'field_from', 'type' => 'monthyear',],
			              ['label' => __('To', 'cv-generator'), 'id' => 'field_to', 'type' => 'monthyear', 'depends_on' => [['field_still_working', '=', 0]], ],
			              [ 'label' => __('Description', 'cv-generator'), 'id' => 'field_description', 'type' => 'textarea',]
		              ]
	              ]
              ]
            ],

			// section 4
			[
				'label' => __('Education', 'cv-generator'),
                'id' => 'section_education',
//				'path' => 'education',
				'save_button' => true,
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 96 960 960" width="26"><path d="M479 936 189 777V537L40 456l439-240 441 240v317h-60V491l-91 46v240L479 936Zm0-308 315-172-315-169-313 169 313 172Zm0 240 230-127V573L479 696 249 571v170l230 127Zm1-240Zm-1 74Zm0 0Z"/></svg>',
				'fields' => [
					[
						'label' => __('Education', 'cv-generator'),
						'id' => 'field_education',
						'type' => 'json',
						'extra_type' => 'education',
						'inner_fields' => [
							[ 'label' => __('School', 'cv-generator'), 'id' => 'field_school', 'type' => 'text', ],
							['label' => __('I am currently learning here', 'cv-generator'), 'id' => 'field_still_learning', 'type' => 'yesno', ],
							['label' => __('From', 'cv-generator'), 'id' => 'field_from', 'type' => 'monthyear',],
							['label' => __('To', 'cv-generator'), 'id' => 'field_to', 'type' => 'monthyear', 'depends_on' => [['field_still_learning', '=', 0]], ],
						]
					]
				]
			],

			// section 5
			[
				'label' => __('Video', 'cv-generator'),
				'id' => 'section_video',
//				'path' => 'video',
                'save_button' => true,
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 96 960 960" width="26"><path d="M140 896q-24 0-42-18t-18-42V316q0-24 18-42t42-18h520q24 0 42 18t18 42v215l160-160v410L720 621v215q0 24-18 42t-42 18H140Zm0-60h520V316H140v520Zm0 0V316v520Z"/></svg>',
                'fields' => [
                    [
                        'label' => __('Video', 'cv-generator'),
                        'id' => 'field_video',
                        'type' => 'video',
                        'extra_type' => 'video',
                    ]
                ]
            ]
		);

        $this->api = MYAPI;
        $this->api['payment_redirect'] = CVGEN_REST_PAYMENT_API_URL;

		add_action( 'rest_api_init', function () {
            // POST
            register_rest_route($this->api['update_cv'][0], $this->api['update_cv'][1], array(
                'methods' => 'POST',
                'callback' => [$this, 'api_update_post'],
                'permission_callback' => '__return_true',
                'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
            ) );

            // GET
			register_rest_route($this->api['download_cv'][0], $this->api['download_cv'][1],array(
	            'methods' => 'GET',
	            'callback' => [$this, 'api_get_pdf_cv'],
                'permission_callback' => '__return_true',
                'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
            ));

			register_rest_route($this->api['upload_video'][0], $this->api['upload_video'][1], array(
				'methods'         => 'POST',
				'callback'        => [ $this, 'api_upload_video' ],
                'permission_callback' => '__return_true',
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));

			register_rest_route($this->api['get_video'][0], $this->api['get_video'][1], array(
				'methods'         => 'GET',
				'callback'        => [ $this, 'api_get_video' ],
                'permission_callback' => '__return_true',
			));

			register_rest_route($this->api['remove_video'][0], $this->api['remove_video'][1], array(
				'methods'         => 'DELETE',
				'callback'        => [ $this, 'api_remove_video' ],
                'permission_callback' => '__return_true',
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));

			register_rest_route($this->api['get_thumbnail'][0], $this->api['get_thumbnail'][1], array(
				'methods'         => 'GET',
				'callback'        => [ $this, 'api_get_thumbnail' ],
                'permission_callback' => '__return_true',
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));

			register_rest_route($this->api['set_thumbnail_by_video_second'][0], $this->api['set_thumbnail_by_video_second'][1], array(
				'methods'         => 'POST',
				'callback'        => [ $this, 'api_set_thumbnail_by_video_second' ],
                'permission_callback' => '__return_true',
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));

			register_rest_route($this->api['generate_cv_preview'][0], $this->api['generate_cv_preview'][1], array(
				'methods'         => 'GET',
				'callback'        => [ new PDFCV(), 'generatePreview' ],
                'permission_callback' => '__return_true',
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));

			register_rest_route($this->api['get_cv_preview'][0], $this->api['get_cv_preview'][1], array(
				'methods'         => 'GET',
				'callback'        => [ new PDFCV(), 'getPreviewImg' ],
                'permission_callback' => '__return_true',
                'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));

			register_rest_route($this->api['check_auth'][0], $this->api['check_auth'][1], array(
				'methods'         => 'GET',
				'callback'        => [$this, 'checkAuth'],
                'permission_callback' => '__return_true',
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));

        });
    }

    function checkAuth($data) {
	    return intval( $data->get_attributes()['current_user_id'] ) > 0; // !! this comes from php not j
    }

    function wpse_custom_meta_box() {
	    add_meta_box(
		    'cv_meta_box', // ID
		    'Data', // Title
		    [$this, 'cv_meta_box_callback'], // Callback function
		    'cv', // Post type
		    'normal', // Context
		    'high' // Priority
	    );
    }

	// Output the HTML for the metabox
	function cv_meta_box_callback( $post ) {
		if ($userID = $post->post_author) {
			if (!current_user_can('manage_options')) {
				die("You are not authorized!");
			}

			$url = get_rest_url() . $this->api['download_cv'][0] . $this->api['download_cv'][1] . "?admin=true&target_user_id=$userID";
			echo "<a href='$url' target='_blank'>Download User's CV</a>";
		}
    }

    // Add custom column to admin dashboard table
	function my_custom_post_type_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[$key] = $value;
			if ( $key == 'title' ) {
				$new_columns['cv_paid_minutes_left'] = 'Paid Hours Left';
				$new_columns['cv_author_email'] = 'Author\'s email';
			}
		}
		return $new_columns;
	}

	// Output content for custom column
	function my_custom_post_type_column_content( $column_name, $post_id ) {
        $authorID = get_post($post_id)->post_author;
        $author = get_user_by('id', $authorID);
		if ( $column_name == 'cv_paid_minutes_left' ) {
			$value = round(intval(CVStripePayment::getCurrentUserHowManyLeftMinutes($authorID)) / 60, 2);
			echo esc_html( $value );
		} else if ($column_name == 'cv_author_email') {
            echo esc_html( $author->user_email );
        }
	}

    public function api_get_thumbnail($data) {
	    $current_user_id = intval( $data->get_attributes()['current_user_id'] ); // !! this comes from php not js
	    if ( ! $current_user_id ) {
		    wp_die( 'You should be logged in!' );
	    }
	    $unique_media_id  = get_user_meta( $current_user_id, 'cv_generator_media_folder_id', true );
	    $path = CVGEN_PLUGIN_DIR . '/uploads/video/' . $unique_media_id . '/' . $this->thumbnail_filename;

	    if (!file_exists($path)) {
		    http_response_code(404);
		    die('Not Found');
	    }

	    $mime_type = mime_content_type($path);
	    header('Content-Type: ' . $mime_type);
	    header('Content-Length: ' . filesize($path));
	    readfile($path);
	    exit();
    }

    public function api_set_thumbnail_by_video_second($data) {
        try {
	        $current_user_id = intval( $data->get_attributes()['current_user_id'] ); // !! this comes from php not js
	        if ( ! $current_user_id ) {
		        wp_die( 'You should be logged in!' );
	        }
	        $uniq_video_id  = get_user_meta( $current_user_id, 'cv_generator_media_folder_id', true );
	        $video_filename = get_user_meta( $current_user_id, 'cv_generator_video_filename', true );
	        if ( ! $uniq_video_id ) {
		        return null;
	        }

	        $path = CVGEN_PLUGIN_DIR . '/uploads/video/' . $uniq_video_id . '/' . $video_filename;

	        if ( ! file_exists( $path ) ) {
		        return false;
	        }

            // check if post variable with time exists and validate it is a float, then round it to 2 decimal places.
            $time = $data->get_json_params()['time'] ?? 0;
            if ( empty($time) || !is_numeric( $time ) || $time < 0) {
	            return ['status' => "fail", 'msg' => __('Some error happened during saving thumbnail! Time not set.', 'cv-generator')];
            }

            $this->create_thumbnail($time, $path, $current_user_id);

        } catch ( \Exception $e ) {
	        return ['status' => "fail", 'msg' => __('Some error happened during saving thumbnail!', 'cv-generator')];
        }

	    return ['status' => "ok", 'msg' => __('Thumbnail saved!', 'cv-generator')];
    }

    public function create_thumbnail($time, $video_path, $current_user_id) {
	    $time = round($time, 2);

	    // i want that ffmpeg extract image of the second $n and save it in the same folder with image name thumbnail.jpeg or png. I would like to use OOP approach
	    $ffmpeg = FFMpeg::create();
	    $video  = $ffmpeg->open( $video_path );

	    // Set the time code to extract the thumbnail from
	    $timecode = TimeCode::fromSeconds( $time );

	    // Extract the thumbnail
	    $frame         = $video->frame( $timecode );

	    $thumbnailPath = dirname( $video_path ) . '/' . $this->thumbnail_filename; // Change the extension to png if you prefer

	    $frame->save($thumbnailPath);

	    // Load the saved thumbnail using Intervention Image
	    $thumbnail = Image::make($thumbnailPath);

	    $watermark = Image::make(CVGEN_ASSETS_DIR . '/video.png');
	    $watermark->opacity(75); // Set the opacity to 30%
	    $watermark->resize(200, 200); // Set the opacity to 30%

	    // Calculate the position to center the watermark
	    $watermarkX = ($thumbnail->width() - $watermark->width()) / 2;
	    $watermarkY = ($thumbnail->height() - $watermark->height()) / 2;

	    // Apply the watermark
	    $thumbnail->insert($watermark, 'top-left', $watermarkX, $watermarkY);

	    $thumbnail->save( $thumbnailPath );
	    (new PDFCV())->generatePreview(null, $current_user_id);
    }

    public function api_remove_video($data) {
	    $current_user_id = intval($data->get_attributes()['current_user_id']);
	    if (!$current_user_id) {
		    return ['status' => "fail", 'msg' => __('You should be logged in!', 'cv-generator')];
	    }

        $this->move_previous_video_if_exists($current_user_id);
	    return ['status' => "ok", 'msg' => __('Video removed successfully!', 'cv-generator')];
    }

	public function api_get_video($data) {
        $video_uuid = $data->get_params()['q'] ?? '';
        if (strlen($video_uuid) < 32) {
	        wp_die("Video not found - q parameter not defined or invalid.");
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cv_generator_public_url_video';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE public_url_query = %s",
            $video_uuid
        );

        $public_urls = $wpdb->get_results($query);

        if (count($public_urls) !== 1) {
            wp_die("Video not found - q parameter not defined or invalid.");
        }

		$path = CVGEN_UPLOAD_DIR . '/' . $public_urls[0]->path;

		if (!file_exists($path)) {
            die("Video not found.");
		}

		$filesize = filesize($path);
		$offset = 0;
		$length = $filesize;

		if (isset($_SERVER['HTTP_RANGE'])) {
			// Parse the range header to get the byte range.
			$range = $_SERVER['HTTP_RANGE'];
			$matches = array();
			preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches);
			$offset = intval($matches[1]);
			if (isset($matches[2])) {
				$length = intval($matches[2]) - $offset + 1;
			} else {
				$length = $filesize - $offset;
			}
		}

        // Set the HTTP headers to serve the correct byte range.
		// Determine the content type based on the video file extension.
		$extension = pathinfo($path, PATHINFO_EXTENSION);
		if ($extension == 'mp4') {
			header('Content-Type: video/mp4');
		} else if ($extension == 'webm') {
			header('Content-Type: video/webm');
		}

		header('Content-Length: ' . $length);
		header('Accept-Ranges: bytes');

		if (isset($_SERVER['HTTP_RANGE'])) {
			header('HTTP/1.1 206 Partial Content');
			header("Content-Range: bytes $offset-" . ($offset + $length - 1) . "/$filesize");
		} else {
			header('HTTP/1.1 200 OK');
		}

		// Read and output the requested byte range of the file.
		$handle = fopen($path, 'rb');
		fseek($handle, $offset);
		$remaining = $length;
		while ($remaining > 0 && !feof($handle)) {
			$chunkSize = min($remaining, 1024*8);
			$data = fread($handle, $chunkSize);
			echo $data;
			$remaining -= $chunkSize;
		}
		fclose($handle);

        exit();
	}

    private function move_previous_video_if_exists($current_user_id) {
	    $user_id = intval($current_user_id);
	    $uniq_media_folder_id = get_user_meta( $user_id, 'cv_generator_media_folder_id', true);
	    $video_filename = get_user_meta( $user_id, 'cv_generator_video_filename', true);
	    delete_user_meta( $user_id, 'cv_generator_video_filename');
        $path = CVGEN_UPLOAD_DIR . "/video/$uniq_media_folder_id/$video_filename";

        $archive_dir = CVGEN_UPLOAD_DIR . '/video/0archive/' . $uniq_media_folder_id;

        if (!is_dir($archive_dir)) {
            mkdir($archive_dir, 0777, true);
        }

        if (file_exists($path)) {
            $file_extension = pathinfo($video_filename, PATHINFO_EXTENSION);
            $filename_without_ext = pathinfo($video_filename, PATHINFO_FILENAME);
            $destination_path = $archive_dir . '/' . $video_filename;
            $counter = 1;

            while (file_exists($destination_path)) {
                $destination_path = $filename_without_ext . '_' . $counter . '.' . $file_extension;
                $destination_path = $archive_dir . '/' . $destination_path;
                $counter++;
            }

            rename($path, $destination_path);
        }
    }

	public function api_upload_video($data) {
        $current_user_id = intval($data->get_attributes()['current_user_id']); // !! this comes from php not js
        if (!$current_user_id) {
            wp_die('You should be logged in!');
        }

		// Set the directory where uploaded files will be saved
		$baseDir = CVGEN_UPLOAD_DIR . "/video/";


		$allowedExtensions = array('mp4', 'webm');

        $media_uniq_id = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);
		$uploadDir = $baseDir . $media_uniq_id . "/";
		if (!$media_uniq_id) {
			cv_generator_set_unique_media_id($baseDir, $current_user_id);
			$media_uniq_id = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);
			$uploadDir = $baseDir . $media_uniq_id . "/";
        }

        // Check if a file was uploaded
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            // Get the file extension
            $fileExtension = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);

            // Check if the file extension is allowed
            if (!in_array($fileExtension, $allowedExtensions)) {
                return ['status' => "fail", 'msg' => __('Invalid file type. Only MP4 and webm files are allowed.', 'cv-generator')];
            }

            // verify that the file starts with 'video/'
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($finfo, $_FILES['video']['tmp_name']);
            if (strpos($fileType, 'video/') !== 0) {
                return ['status' => "fail", 'msg' => __('Invalid file type. Only video files are allowed.', 'cv-generator')];
            }


	        $ffmpeg          = FFMpeg::create();
	        $video           = $ffmpeg->open( $_FILES['video']['tmp_name']);
            $created_temp_file = false;
            if (!$duration = $video->getFormat()->get('duration')) {
	            // Check if the video length is within the allowed limit
	            try {
		            $video_path_temp = $uploadDir . 'temporary-video.webm';
		            $video->save( new WebM(), $video_path_temp );
		            $video    = $ffmpeg->open( $video_path_temp );
		            $duration = $video->getFormat()->get( 'duration' );
		            $created_temp_file = true;
	            } catch ( Exception $e ) {
		            if ( WP_DEBUG ) {
			            echo $e->getMessage() . "\n";
		            }
		            die( 'Error encoding the video!' );
	            }
            }

	        if ( $duration > 120 ) {
		        return [
			        'status' => "fail",
			        'msg'    => __( 'Video length exceeds the allowed limit of 120 seconds.', 'cv-generator' )
		        ];
	        }

            // Generate a unique filename for the uploaded file
            $filename = $_FILES['video']['name'];
            if (strlen($filename) > 255) {
                return ['status' => "fail", 'msg' => __('Video file name is too long!', 'cv-generator')];
            }

            // Create the target directory if it doesn't already exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move the uploaded file to the designated directory on your server
            $video_path = $uploadDir . $filename;

            die($video_path);

	        // Return a success response
	        $this->move_previous_video_if_exists($current_user_id);

	        if ( $created_temp_file ) {
		        if (!rename($video_path_temp, $video_path)) {
			        if (WP_DEBUG) {
				        echo "Created temp file: " . ($created_temp_file ? 'true' : 'false') . " \n";
				        echo "Temporary Filename: $video_path_temp (used in case if created_temp_file) \n";
				        echo "Video Filename: $video_path (video saved name) \n";
			        }

			        return ['status' => "fail", 'msg' => __('Error uploading video 10000400', 'cv-generator')];
		        }
            } else {
                if (!move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
	                if (WP_DEBUG) {
		                echo "Created temp file: " . ($created_temp_file ? 'true' : 'false') . " \n";
		                echo "Filename: $filename (used in case if not created_temp_file) \n";
		                echo "Video Filename: $video_path (video saved name) \n";
	                }

	                return ['status' => "fail", 'msg' => __('Error uploading video 10000500', 'cv-generator')];
                }
	        }
            
            $this->update_video_public_url('myPath');

	        update_user_meta($current_user_id, 'cv_generator_video_filename', $filename);

	        $this->create_thumbnail(1, $video_path, $current_user_id);
	        return [
                'status' => "ok",
                'msg' => __('Success uploading video.', 'cv_generator')
            ];
        } else {
            // Return an error response
            return ['status' => "fail", 'msg' => __('Error uploading video 10000300', 'cv-generator')];
        }
    }

	function api_get_pdf_cv($data) {
	    $current_user_id = $data->get_attributes()['current_user_id']; // !! this comes from php not js
		$params = $data->get_params();
		$admin = $params['admin'] ?? null;
        $target_user_id = $current_user_id;
        $admin_request = false;

        if ($admin === 'true') { // check that in query is set admin
            if (user_can($current_user_id,  'manage_options' )) { // check that role is admin (by checking if user can manage options)
	            $requested_user = intval( $params['target_user_id'] ?? null );
                if ($requested_user) {
                  $target_user_id = $requested_user;
                  $admin_request = true;
                } else {
                    die("Target User ID not defined. Use target_user_id=6, for exmample.");
                }
            } else {
                die("You are not authorized to do this!");
            }
        }

		if (!CVStripePayment::getCurrentUserHowManyLeftMinutes($target_user_id) && !$admin_request) {
	        return ['status' => "fail", 'msg' => __('You have not paid for the CV.', 'cv-generator')];
        }

        (new PDFCV())->show($target_user_id);
    }

    function api_update_post($data) {
	    $current_user_id = $data->get_attributes()['current_user_id']; // !! this comes from php not js

	    // Validate nonce
	    if (!isset($data[$this->nonce_name]) && !wp_verify_nonce($data[$this->nonce_name], $this->nonce_name)) {
		    return ['status' => "fail", 'msg' => __('Invalid nonce', 'cv-generator')];
	    }

        $cv = cv_generator_get_current_users_cv($current_user_id);

        $fields = [];
        foreach ($this->sections as $section) {
            foreach ($section['fields'] as $field) {
                $fields[] = $field;
            }
        }

	    $field_exists = function ($field_name) use ($fields) {
            return in_array($field_name, array_column($fields, 'id'));
	    };

        $result = [];
        foreach ($data->get_json_params() as $key => $param) {
            if ($field_exists($key)) {
                if (update_post_meta( $cv->ID, $key, $data[$key])) { // do update
			        $result[] = $key;
		        }
            }
        }

	    (new PDFCV())->generatePreview(null, $current_user_id);

	    return ['status' => "ok", 'msg' => "CV Updated", "updated_fields" => $result];
    }

	/**
	 * This method prevents publishing posts publicly
	 * - instead they are published privately
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 * @return void
	 */
	function prevent_publishing_posts_publicly( $new_status, $old_status, $post ) {
		if ( $post->post_type == 'cv' && $new_status == 'publish' && $old_status  != $new_status ) {
			$post->post_status = 'private';
			wp_update_post( $post );
		}
	}

	function data_to_javascript($cv) {
		$currentYear = date('Y');

        $api = [];

        foreach ($this->api as $key => $value) {
            $api[$key] = get_rest_url() . $value[0]. $value[1];
        }

        return [
            'data' => [
                'screen_type' => 'cv_post_frontend_fields',
                'public_video_key' => get_user_meta(get_current_user_id(), 'cv_generator_video_url_param', true),
                'left_minutes_for_payment' => CVStripePayment::getCurrentUserHowManyLeftMinutes(),
                'user_has_video' => cv_generator_user_has_video(),
                'sections' => $this->sections,
                'cv' => $cv,
                'nonce' => wp_create_nonce($this->nonce_name),
                'nonce_name' => $this->nonce_name,
                'meta' => get_post_meta($cv->ID),
                'months' => [__('January', 'cv-generator'),__('February', 'cv-generator'),__('March', 'cv-generator'),__('April', 'cv-generator'),__('May', 'cv-generator'),__('June', 'cv-generator'),__('July', 'cv-generator'),__('August', 'cv-generator'),__('September', 'cv-generator'),__('October', 'cv-generator'),__('November', 'cv-generator'),__('December', 'cv-generator')],
                'years' => array_reverse(range(1950, $currentYear)),
                'translations' => [
                    'submit' => __('Save', 'cv-generator'),
                    'submit_and_continue' => __('Continue', 'cv-generator'),
                    'finish_cv' => __('Finish CV', 'cv-generator'),
                    'addRow' => __('Add', 'cv-generator'),
                    'removeRow' => __('Remove', 'cv-generator'),
                    'removeVideo' => __('Delete video', 'cv-generator'),
                    'uploadVideo' => __('Upload video (MP4)', 'cv-generator'),
                    'recordVideo' => __('Record new video', 'cv-generator'),
                    'noVideo' => __('You do not have video uploaded or recorder', 'cv-generator'),
                    'viewVideo' => __('Back', 'cv-generator'),
                    'startRecording' => __('Start recording', 'cv-generator'),
                    'stopRecording' => __('Stop recording', 'cv-generator'),
                    'thumbnail' => __('Video thumbnail', 'cv-generator'),
                    'thumbnailDescription' => __('This video thumbnail will be shown in your CV. You can change it to any frame of the video by setting the video player to the frame you want, and pressing the button to set thumbnail.', 'cv-generator'),
                    'setThumbnailByVideoSeconds' => __('Set thumbnail on this video second', 'cv-generator'),
                    "cvPreview" => __('CV preview', 'cv-generator'),
                    'accessStatus' => __('Access status', 'cv-generator'),
                    'youHavePaidUntil' => __('You have access to the CV until', 'cv-generator'),
                    'youHaveNotPaid' => __('You need to buy access to the CV to download it.', 'cv-generator'),
                    'youCanBuyTheAccessToTheCVFor' => sprintf(__('You can buy the access to download the CV for %0.2f EUR for %d hours', 'cv-generator'), $this->settings->get_settings()['payments']['price_1'], $this->settings->get_settings()['payments']['product_1_time']),
                    'yourVideoPubliclyIsAvailableHere' => __('Your publicly accessible video is here: ', 'cv-generator'),
                    'buy' => __('Buy', 'cv-generator'),
	                'downloadCV' => __('Download CV', 'cv-generator'),
                ],
                'api' => $api,
                // be cautious when changing order of these because they are used in such order in Vue
                'navigation_tabs' => [
                        ['name' => __('My CV', 'cv-generator'), 'path' => '/', 'icon' => 'pi-home'],
                        ['name' => __('Edit CV', 'cv-generator'), 'path' => '/edit-cv', 'icon' => 'pi-user-edit'],
                ]
            ]
		];
	}

	function cv_frontend_fields_shortcode_html( $atts, $userID = null ) {
		$cv = cv_generator_get_current_users_cv();

		if (is_user_logged_in()) {
            if (!$cv) {
	            $current_user = wp_get_current_user();
                wp_insert_post([
                    'post_type' => 'cv',
                    'post_title' => 'CV: ' . $current_user->user_email,
                    'post_status' => 'private',
                    'post_author' => $current_user->ID
                ]);

	            $cv = cv_generator_get_current_users_cv();

                if (!$cv) {
                    wp_die("Something went wrong!");
                }
            }

            wp_enqueue_style("cv_generator_cvpost_frontend_style", plugin_dir_url(__FILE__) . 'dist-vue/assets/index.css', [], '1.0');
            wp_enqueue_script( "cv_generator_cvpost_frontend_vue", plugin_dir_url( __FILE__ ) . 'dist-vue/assets/index.js');

			ob_start(); ?>
			<div class="w-full" id="cv_generator" data-js="<?= esc_attr(wp_json_encode($this->data_to_javascript($cv))) ?>"></div>

			<?php

			return ob_get_clean();
		}

        return "";
	}

	/**
	 * Register CV post type
	 *
	 * @return void
	 */
	public function cv_post_type() {
		$labels = array(
			'name'                  => __( 'Curriculum Vitaes', 'Post Type General Name', 'cv_generator' ),
			'singular_name'         => __( 'Curriculum Vitae', 'Post Type Singular Name', 'cv_generator' ),
			'menu_name'             => __( 'Curriculum Vitae', 'cv_generator' ),
			'name_admin_bar'        => __( 'Curriculum Vitae', 'cv_generator' ),
			'archives'              => __( 'Curriculum Vitae Archives', 'cv_generator' ),
			'attributes'            => __( 'Curriculum Vitae Attributes', 'cv_generator' ),
			'parent_item_colon'     => __( '', 'cv_generator' ),
			'all_items'             => __( 'All Curriculum Vitae', 'cv_generator' ),
			'add_new_item'          => __( 'Add New Curriculum Vitae', 'cv_generator' ),
			'add_new'               => __( 'Add Curriculum Vitae', 'cv_generator' ),
			'new_item'              => __( 'New Curriculum Vitae', 'cv_generator' ),
			'edit_item'             => __( 'Edit Curriculum Vitae', 'cv_generator' ),
			'update_item'           => __( 'Update Curriculum Vitae', 'cv_generator' ),
			'view_item'             => __( 'View Curriculum Vitae', 'cv_generator' ),
			'view_items'            => __( 'View Curriculum Vitae', 'cv_generator' ),
			'search_items'          => __( 'Search Curriculum Vitae', 'cv_generator' ),
			'not_found'             => __( 'Curriculum Vitae not found', 'cv_generator' ),
			'not_found_in_trash'    => __( 'Curriculum Vitae not found in Trash', 'cv_generator' ),
			'insert_into_item'      => __( '', 'cv_generator' ),
			'uploaded_to_this_item' => __( '', 'cv_generator' ),
			'items_list'            => __( 'Curriculum Vitae list', 'cv_generator' ),
			'items_list_navigation' => __( 'Items list navigation', 'cv_generator' ),
			'filter_items_list'     => __( 'Filter items list', 'cv_generator' ),
		);
		$args = array(
			'label'                 => __( 'Curriculum Vitae', 'cv_generator' ),
			'description'           => __( 'Curriculum Vitae', 'cv_generator' ),
			'labels'                => $labels,
			'supports'              => array( 'author', 'title' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-text-page',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'capability_type'       => 'curriculum_vitae',
			'show_in_rest'          => false,
		);
		register_post_type( 'cv', $args );
	}

    static function get_public_video_url($userId) {
        return get_rest_url() . MYAPI['get_video'][0] . MYAPI['get_video'][1] . '?q=' . get_user_meta($userId, 'cv_generator_video_url_param_id', true);
    }

    /**
     * This function creates a unique query (32 symbols)
     * and inserts into database which is later used
     * to retrieve the video.
     *
     * @param $path
     * @return void
     * @throws Exception
     */
    function update_video_public_url($path) {
        $unique_public_video_hash = false;
        while (!$unique_public_video_hash) {
            $public_url_query = bin2hex(random_bytes(32));
            if (!$this->already_exists_this_public_url($public_url_query)) {
                $unique_public_video_hash = true;
                $this->insert_public_video_url($public_url_query, $path);
            }
        }
    }

    function insert_public_video_url($public_url_query, $video_path_from_uploads_folder) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cv_generator_public_url_video';

        $data = array(
            'public_url_query' => $public_url_query,
            'path' => $video_path_from_uploads_folder
        );

        return $wpdb->insert($table_name, $data);
    }

    function already_exists_this_public_url($public_url) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cv_generator_public_url_video';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE public_url_query = %s",
            $public_url
        );

        $results = $wpdb->get_results($query);

        return count($results) > 0;
    }

    /**
     * Setup plugin
     *
     * @return void
     */
    static function onActivate($abspath, $plugin_name) {
        global $wpdb;
        require_once($abspath . 'wp-admin/includes/upgrade.php');
        $table_name = $wpdb->prefix . 'cv_generator_public_url_video';

        // create public url to video table.
        // user_id - used for existing users
        dbDelta("CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            public_url_query varchar(64) NOT NULL,
            path varchar(255) NOT NULL,
            PRIMARY KEY (id)            
        )");

        // validate that tables were created
        // if not present in db, then deactivate plugin and die with error
        $created_table_name = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

        if ($wpdb->get_var($created_table_name) !== $table_name) {
            deactivate_plugins($plugin_name);
            $referer_url = wp_get_referer();
            wp_die("There was error setting up the plugin. Plugin deactivated. <br /><a href='$referer_url'>Back</a>");
        }
    }
}