<?php

require_once "PDFCV.php";
require_once "helpers.php";

class CVPostType {
	public function __construct() {
		add_action( 'init', [$this, 'cv_post_type'], 0);
		add_action( 'transition_post_status', [$this, 'prevent_publishing_posts_publicly'], 10, 3 );
		add_shortcode( 'cv_frontend_fields', [$this, 'cv_frontend_fields_shortcode_html'] );

        $this->nonce_name = 'wp_rest';

		$this->sections = array(
            // section 1
            [
                'label' => __('Personal information', 'cv-generator'),
                'id' => 'section_personal_information',
//                'path' => 'personal_information',
                'icon' => 'pi-user',
                'fields' => [
	                [ 'label' => 'Name', 'id' => 'field_name', 'type' => 'text', 'validation' => ['required'] ],
	                [ 'label' => 'Surname', 'id' => 'field_surname', 'type' => 'text', 'validation' => ['required']],
	                [ 'label' => 'Phone', 'id' => 'field_phone', 'type' => 'tel', ],
	                [ 'label' => 'Bio', 'id' => 'field_bio', 'type' => 'textarea', ],
	                [ 'label' => 'Hobbies', 'id' => 'field_hobbies', 'type' => 'textarea', ],
	                [ 'label' => 'Interests', 'id' => 'field_interests', 'type' => 'textarea', ],
	                [ 'label' => 'Other skills', 'id' => 'field_other_skills', 'type' => 'textarea', ],
	                [ 'label' => 'I have driving license', 'id' => 'field_i_have_driving_license', 'type' => 'yesno', ],
                ]
            ],

            // section 2
            [
              'label' => __('Languages', 'cv-generator'),
              'id' => 'section_languages',
//              'path' => 'languages',
              'icon' => 'pi-flag',
              'fields' => [
	              [
		              'label' => 'Languages',
		              'id' => 'field_languages',
		              'type' => 'json',
		              'extra_type' => 'languages',
		              'inner_fields' => [
			              [ 'label' => 'Language name', 'id' => 'field_language_name', 'type' => 'select',
			                'options' => [ 'English', 'Latvian', 'Russian' ]
			              ],
			              [ 'label' => 'Proficiency', 'id' => 'field_proficiency', 'type' => 'select',
			                'options' => [
				                'Native', 'Excellent', 'Good', 'Average', 'Basics'
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
              'icon' => 'pi-briefcase',
              'fields' => [
	              [
		              'label' => 'Work experience',
		              'id' => 'field_work_experience',
		              'type' => 'json',
		              'extra_type' => 'work_experience',
		              'inner_fields' => [
			              ['label' => 'Position', 'id' => 'field_position', 'type' => 'text',],
			              [ 'label' => 'Company', 'id' => 'field_company', 'type' => 'text', ],
			              ['label' => 'From', 'id' => 'field_from', 'type' => 'monthyear',],
			              ['label' => 'To', 'id' => 'field_to', 'type' => 'monthyear', ],
			              [ 'label' => 'Description', 'id' => 'field_description', 'type' => 'textarea',]
		              ]
	              ]
              ]
            ],

			// section 4
			[
				'label' => __('Education', 'cv-generator'),
                'id' => 'section_education',
//				'path' => 'education',
				'icon' => 'pi-server',
				'fields' => [
					[
						'label' => 'Education',
						'id' => 'field_education',
						'type' => 'json',
						'extra_type' => 'education',
						'inner_fields' => [
							[ 'label' => 'School', 'id' => 'field_school', 'type' => 'text', ],
							['label' => 'From', 'id' => 'field_from', 'type' => 'monthyear',],
							['label' => 'To', 'id' => 'field_to', 'type' => 'monthyear', ],
						]
					]
				]
			],

			// section 5
			[
				'label' => __('Video', 'cv-generator'),
				'id' => 'section_video',
//				'path' => 'video',
				'icon' => 'pi-video',
                'fields' => [
                        [
                            'label' => 'Video',
                            'id' => 'field_video',
                            'type' => 'video',
                            'extra_type' => 'video',
                        ]
                ]
            ]
		);

        $this->api = [
          'update_cv' => ['cv_generator/cvpost/', 'update'],
          'download_cv' => ['cv_generator/cvpost/', 'download'],
          'upload_video' => ['cv_generator/cvpost/', 'upload_video'],
          'get_video' => ['cv_generator/cvpost/', 'get_video'],
          'remove_video' => ['cv_generator/cvpost/', 'remove_video'],
        ];

		add_action( 'rest_api_init', function () {
            register_rest_route($this->api['update_cv'][0], $this->api['update_cv'][1], array(
                'methods' => 'POST',
                'callback' => [$this, 'api_update_post'],
                'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
            ) );

			register_rest_route($this->api['update_cv'][0], $this->api['download_cv'][1],array(
	            'methods' => 'GET',
	            'callback' => [$this, 'api_get_pdf_cv'],
                'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
            ));

			register_rest_route($this->api['upload_video'][0], $this->api['upload_video'][1], array(
				'methods'         => 'POST',
				'callback'        => [ $this, 'api_upload_video' ],
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));

			register_rest_route($this->api['get_video'][0], $this->api['get_video'][1], array(
				'methods'         => 'GET',
				'callback'        => [ $this, 'api_get_video' ],
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));

			register_rest_route($this->api['remove_video'][0], $this->api['remove_video'][1], array(
				'methods'         => 'DELETE',
				'callback'        => [ $this, 'api_remove_video' ],
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			));
        });
    }

    public function api_remove_video($data) {
	    $current_user_id = intval($data->get_attributes()['current_user_id']);
	    if (!$current_user_id) {
		    return ['status' => "fail", 'msg' => __('You should be logged in!', 'cv_generator')];
	    }

	    $baseDir = CVGEN_PLUGIN_DIR . "/uploads/video/";
        $this->removePreviousVideoFolderIfExists($baseDir, $current_user_id);
	    return ['status' => "ok", 'msg' => __('Video removed successfully!!', 'cv_generator')];
    }

	public function api_get_video($data) {
		$current_user_id = intval( $data->get_attributes()['current_user_id'] ); // !! this comes from php not js
		if (!$current_user_id) {
			wp_die('You should be logged in!');
		}
		$uniq_video_id = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);
		$video_filename = get_user_meta($current_user_id, 'cv_generator_video_filename', true);
		if (!$uniq_video_id) {
			return null;
		}

		$path = CVGEN_PLUGIN_DIR . '/uploads/video/' . $uniq_video_id . '/' . $video_filename;

		if (!file_exists($path)) {
			return false;
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

		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');
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

    private function removePreviousVideoFolderIfExists($baseDir, $current_user_id) {
	    // Get the current cv_generator_media_folder_id for the user
	    $user_id = intval($current_user_id);
	    $old_uniq_id = get_user_meta($user_id, 'cv_generator_media_folder_id', true);
	    delete_user_meta( $user_id, 'cv_generator_media_folder_id');
	    delete_user_meta( $user_id, 'cv_generator_video_filename');

	    if ($old_uniq_id) {
		    // If the user already has a video, delete it before uploading the new video
		    $oldDir = $baseDir . $old_uniq_id . "/";
		    if (file_exists($oldDir)) {
			    $files = array_diff(scandir($oldDir), array('.', '..'));
			    foreach ($files as $file) {
				    unlink($oldDir . $file);
			    }
			    rmdir($oldDir);
		    }
	    }
    }

	public function api_upload_video($data) {
		$current_user_id = intval($data->get_attributes()['current_user_id']); // !! this comes from php not js
        if (!$current_user_id) {
            wp_die('You should be logged in!');
        }

		// Set the directory where uploaded files will be saved
		$baseDir = CVGEN_PLUGIN_DIR . "/uploads/video/";

		$allowedExtensions = array('mp4', 'webm');
//		$allowedExtensions = array('mp4', 'avi', 'mov', 'wmv');

		do {
			$uniq_id = uniqid();
			$uploadDir = $baseDir . $uniq_id . "/";
		} while (file_exists($uploadDir));

		// Check that the request is a POST request
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// Check if a file was uploaded
			if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
				// Get the file extension
				$fileExtension = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);

				// Check if the file extension is allowed
				if (!in_array($fileExtension, $allowedExtensions)) {
					return ['status' => "fail", 'msg' => __('Invalid file type. Only MP4, AVI, MOV, and WMV files are allowed.', 'cv_generator')];
				}

                // verify that the file starts with 'video/' as these files should
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$fileType = finfo_file($finfo, $_FILES['video']['tmp_name']);
				if (strpos($fileType, 'video/') !== 0) {
					return ['status' => "fail", 'msg' => __('Invalid file type. Only video files are allowed.', 'cv_generator')];
				}

                // verify video length
//				$ffmpeg = FFMpeg\FFMpeg::create();
//				$video = $ffmpeg->open($_FILES['video']['tmp_name']);
//				$duration = $video->getDuration();
//				if ($duration < 5 || $duration > 300) {
//					return ['status' => "fail", 'msg' => __('Invalid video duration. Only videos between 5 and 300 seconds are allowed.', 'cv_generator')];
//				}

				// Generate a unique filename for the uploaded file
				$filename = $_FILES['video']['name'];
				if (strlen($filename) > 255) {
					return ['status' => "fail", 'msg' => __('Video file name is too long!', 'cv_generator')];
				}

				// Create the target directory if it doesn't already exist
				if (!file_exists($uploadDir)) {
					mkdir($uploadDir, 0755, true);
				}

				// Move the uploaded file to the designated directory on your server
				if (move_uploaded_file($_FILES['video']['tmp_name'], $uploadDir . $filename)) {
					// Return a success response
					$this->removePreviousVideoFolderIfExists($baseDir, $current_user_id);

					update_user_meta($current_user_id, 'cv_generator_media_folder_id', $uniq_id);
					update_user_meta($current_user_id, 'cv_generator_video_filename', $filename);
//                    cv_generator_save_also_webm_format($uploadDir, $filename);
					return [ 'status' => "ok", 'msg' => __( 'Success uploading video.', 'cv_generator' ) ];
				} else {
					return ['status' => "fail", 'msg' => __('Error uploading video 10000200', 'cv_generator')];
				}
			} else {
				// Return an error response
				return ['status' => "fail", 'msg' => __('Error uploading video 10000300', 'cv_generator')];
			}
		}

		return ['status' => "fail", 'msg' => __('Error uploading video 10000400', 'cv_generator')];
	}

	function api_get_pdf_cv($data) {
	    $current_user_id = $data->get_attributes()['current_user_id']; // !! this comes from php not js
        (new PDFCV())->show($current_user_id);
    }

    function api_update_post($data) {
	    $current_user_id = $data->get_attributes()['current_user_id']; // !! this comes from php not js

	    // Validate nonce
	    if (!isset($data[$this->nonce_name]) && !wp_verify_nonce($data[$this->nonce_name], $this->nonce_name)) {
		    return ['status' => "fail", 'msg' => __('Invalid nonce', 'cv_generator')];
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

//        foreach ($sections as $section) {
//	        foreach ( $section['fields'] as $field ) {
//		        if (update_post_meta( $cv->ID, $field['id'], $data[ $field['id'] ] ) ) { // do update
//			        $result[] = $field['id'];
//		        }
//	        }
//        }

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

        $api = [
//	        'update_cv' =>  get_rest_url() . $this->api['update_cv'][0] . $this->api['update_cv'][1],
        ];

        foreach ($this->api as $key => $value) {
            $api[$key] = get_rest_url() . $value[0]. $value[1];
        }

        return [
            'data' => [
                'screen_type' => 'cv_post_frontend_fields',
                'user_has_video' => cv_generator_user_has_video(),
                'sections' => $this->sections,
                'cv' => $cv,
                'nonce' => wp_create_nonce($this->nonce_name),
                'nonce_name' => $this->nonce_name,
                'meta' => get_post_meta($cv->ID),
                'months' => [__('January', 'cv_generator'),__('February', 'cv_generator'),__('March', 'cv_generator'),__('April', 'cv_generator'),__('May', 'cv_generator'),__('June', 'cv_generator'),__('July', 'cv_generator'),__('August', 'cv_generator'),__('September', 'cv_generator'),__('October', 'cv_generator'),__('November', 'cv_generator'),__('December', 'cv_generator')],
                'years' => array_reverse(range(1950, $currentYear)),
                'translations' => [
                    'submit' => __('Save', 'cv-generator'),
                    'submit_and_continue' => __('Save and continue', 'cv-generator'),
                    'addRow' => __('Add', 'cv-generator'),
                    'removeRow' => __('Remove', 'cv-generator'),
                    'removeVideo' => __('Delete video', 'cv-generator'),
                    'uploadVideo' => __('Upload video', 'cv-generator'),
                    'recordVideo' => __('Record new video', 'cv-generator'),
                    'noVideo' => __('You do not have a video uploaded or recorder', 'cv-generator'),
                    'viewVideo' => __('Back', 'cv-generator'),
                ],
                'api' => $api,
                // be cautious when changing order of these because they are used in such order in Vue
                'navigation_tabs' => [
                        ['name' => __('Home', 'cv-generator'), 'path' => '/', 'icon' => 'pi-home'],
                        ['name' => __('Edit CV', 'cv-generator'), 'path' => '/edit-cv', 'icon' => 'pi-user-edit'],
                        ['name' => __('Download CV', 'cv-generator'), 'path' => '/download-cv', 'icon' => 'pi-file-pdf']
                ]
            ]
		];
	}

	function cv_frontend_fields_shortcode_html( $atts ) {
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

			if (!is_admin()) {
				wp_enqueue_style("cv_generator_cvpost_frontend_style", plugin_dir_url(__FILE__) . 'dist-vue/assets/index.css', [], '1.0');
				wp_enqueue_script( "cv_generator_cvpost_frontend_vue", plugin_dir_url( __FILE__ ) . 'dist-vue/assets/index.js');
			}


			ob_start(); ?>
			<div class="flex justify-content-center flex-wrap shadow-3 p-4" id="cv_generator" data-js="<?= esc_attr(wp_json_encode($this->data_to_javascript($cv))) ?>"></div>

			<?php
			return ob_get_clean();
		}
	}

	/**
	 * Register CV post type
	 *
	 * @return void
	 */
	public function cv_post_type() {
		$labels = array(
			'name'                  => _x( 'Curriculum Vitaes', 'Post Type General Name', 'cv_generator' ),
			'singular_name'         => _x( 'Curriculum Vitae', 'Post Type Singular Name', 'cv_generator' ),
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
}