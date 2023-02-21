<?php
require_once "helpers.php";
use Dompdf\Dompdf;

class PDFCV {
    public function __construct() {

    }

	public function getHTML($current_user_id) {
		$cv = cv_generator_get_current_users_cv($current_user_id);
        $cvmeta = get_post_meta($cv->ID);
		$media_folder_unique_id = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);
		$video_filename = get_user_meta($current_user_id, 'cv_generator_video_filename', true);

		ob_start(); ?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport"
                  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>Document</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <style> body{font-family: DejaVu Sans;}</style>
        </head>
        <body>
            <h1><?= $cvmeta['field_name'][0] ?></h1>
            <div>HERE WILL BE MY CV</div>
            <div>Media UID: <?=$media_folder_unique_id?></div>
            <div>Video filename: <?=$video_filename?></div>
        </body>
        </html>

		<?php
		return ob_get_clean();
	}
	
	public function show($current_user_id) {
		// instantiate and use the dompdf class
		$dompdf = new Dompdf();

		$html = $this->getHTML($current_user_id);
		$dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream("CV.pdf", array("Attachment" => false));

		exit(0);
	}
}