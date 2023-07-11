<?php

require_once "helpers.php";
require_once "cvgenerator.php";
use Dompdf\Dompdf;
use Dompdf\Options;

class PDFCV {
    public function __construct() {

    }

	public function getHTML($current_user_id) {
		// Start output buffering
		ob_start();
		$cv = cv_generator_get_current_users_cv($current_user_id);
        $cvmeta = get_post_meta($cv->ID);
		$media_folder_unique_id = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);
		$video_filename     = get_user_meta( $current_user_id, 'cv_generator_video_filename', true );
		if ($media_folder_unique_id && $video_filename) {
			$user_thumbnail_url = CVGEN_VIDEO_DIR . '/' . $media_folder_unique_id . '/thumbnail.jpeg';
			$video_url          = CVPostType::get_public_video_url($current_user_id);
		}

		$user_email = get_user_by('ID', $current_user_id)->user_email;

		// Base64 encode the content for image files
		$base64_user_icon = base64_encode(file_get_contents(CVGEN_ASSETS_DIR . '/cv-templates/user.svg'));
		$base64_video_icon = base64_encode(file_get_contents(CVGEN_ASSETS_DIR . '/cv-templates/video.svg'));
		$base64_education_icon = base64_encode(file_get_contents(CVGEN_ASSETS_DIR . '/cv-templates/education.svg'));
		$base64_employment_icon = base64_encode(file_get_contents(CVGEN_ASSETS_DIR . '/cv-templates/employment.svg'));
		$base64_logo = base64_encode(file_get_contents(CVGEN_ASSETS_DIR . '/cv-templates/logo.png'));

		function cvgen_format_the_date($date, $format = 'F Y') {
			try {
				$dt = new DateTime($date);
				return $dt->format($format);
			} catch (Exception $e) {
				return null; // Return the original string if the date format is not recognized
			}
		}

		// Include the template and return the output
		require CVGEN_ASSETS_DIR . '/cv-templates/cv_template.php';
		return ob_get_clean();
	}

	public function generatePdf($current_user_id) {
		// instantiate and use the dompdf class
		$dompdf = new Dompdf(array('enable_remote' => true));

		$html = $this->getHTML($current_user_id);
		$dompdf->loadHtml($html);

		// set options as required
		$options = new Options();
		$options->set('defaultEncoding', 'UTF-8');
//		$options->set('enable_remote', true);
//		$options->set('isHtml5ParserEnabled', true);
//		$options->set('isHtml5ParserEnabled', true);
		$dompdf->setOptions($options);

		$dompdf->getOptions()->setChroot(CVGEN_PLUGIN_DIR . '\\assets\\cv-templates');
//		chdir(CVGEN_PLUGIN_DIR . '\\assets\\cv-templates');

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4');

		return $dompdf;
	}

	public function show($current_user_id) {
		$dompdf = $this->generatePdf($current_user_id);

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream("CV.pdf", array("Attachment" => false));

		exit(0);
	}

	public function generatePreview($data, $current_user_id = null) {
		if ($data) { // can be null
			$current_user_id = intval($data->get_attributes()['current_user_id']);
		}

		// Get the binary PDF data from Dompdf object
		$dompdf = $this->generatePdf($current_user_id);
		$dompdf->render(); // Render the PDF document before outputting
		$pdfData = $dompdf->output();

		// Create a new Imagick object
		$image = new Imagick();
		$image->readImageBlob($pdfData);
		$image->resetIterator();
		$combinedImage = $image->appendImages(true);

		// Set the format of the output image
		$combinedImage->setImageFormat('webp');

		// Set the image compression quality
		$combinedImage->setImageCompressionQuality(100);

		// Output the preview image to the browser
		$this->savePDFPreview($combinedImage, $current_user_id);
	}

	private function savePDFPreview($img, $current_user_id) {
		$baseDir = CVGEN_PLUGIN_DIR . "/uploads/video/";
		$userFolder = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);
		$target = $baseDir . $userFolder;
		if (!file_exists($target)) {
			mkdir($target, 0755, true);
		}
		$img->writeImage($target . '/cv_preview.webp');
	}

	public function getPreviewImg($data) {
		$current_user_id = intval($data->get_attributes()['current_user_id']);

		$baseDir = CVGEN_PLUGIN_DIR . "/uploads/video/";
		$userFolder = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);
		if (empty($userFolder)) {
			cvGeneratorSetUniqueMediaId($baseDir, $current_user_id);
		}
		$userFolder = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);

		$target = $baseDir . $userFolder . '/cv_preview.webp';

		// Check if the preview image file exists
		if (!file_exists($target)) {
			$this->generatePreview(null, $current_user_id);
		}


		if (file_exists($target)) {
            header('Content-Type: image/png');
            readfile($target);
            exit();
		} else {
			die("Error getting preview!");
		}
	}
}