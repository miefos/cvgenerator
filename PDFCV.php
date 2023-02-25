<?php
require_once "helpers.php";
require_once "cvgenerator.php";
use Dompdf\Dompdf;
use Dompdf\Options;
use Spipu\Html2Pdf\Html2Pdf;

class PDFCV {
    public function __construct() {

    }

	public function getHTML($current_user_id) {
		// Start output buffering
		ob_start();
		$cv = cv_generator_get_current_users_cv($current_user_id);
        $cvmeta = get_post_meta($cv->ID);
		$media_folder_unique_id = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);
		if ($media_folder_unique_id) {
			$video_filename     = get_user_meta( $current_user_id, 'cv_generator_video_filename', true );
			$user_thumbnail_url = CVGEN_VIDEO_DIR . '/' . $media_folder_unique_id . '/thumbnail.jpeg';
		}
		// Include the template and return the output
//		die($user_thumbnail_url);
		require CVGEN_ASSETS_DIR . '/cv-templates/cv_template.php';
		return ob_get_clean();
	}

	public function show($current_user_id) {
		// instantiate and use the dompdf class
		$dompdf = new Dompdf(array('enable_remote' => true));

//		die(var_dump($dompdf->getOptions()->getChroot()));
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
		chdir(CVGEN_PLUGIN_DIR . '\\assets\\cv-templates');

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream("CV.pdf", array("Attachment" => false));

		exit(0);
	}
}