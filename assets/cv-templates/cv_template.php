<!DOCTYPE html>
<html>
<head>
	<title>CV</title>
    <meta charset="UTF-8">
	<style>
        /* Apply box-sizing to all elements */
        *, *:before, *:after {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        /* Global styles */
        body {
            font-family: "DejaVu Sans", sans-serif;
            margin: 0;
            padding: 0;
        }

        a {
            text-decoration: none;
            color: black;
        }

        h1 {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .left-column {
            float: left;
            width: 33.33%;
            padding-right: 20px;
            position: relative;
        }

        .right-column {
            float: right;
            width: 66.67%;
            padding-left: 20px;
            position: relative;
        }

        .left-section-heading {
            font-weight: bold;
        }

        .right-section-heading {
            font-weight: bold;
            font-size: 20px;
            display: inline-block;
            vertical-align: middle;
        }

        .date {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .icon {
            width: 24px;
            height: 24px;
            vertical-align: middle;
        }

        section {
            margin-bottom: 20px;
        }

        .list-item {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .list-item:last-child {
            margin-bottom: 0;
        }

        .list-item-heading {
            font-weight: 700;
            font-size: 16px;
        }

        .right-column section > :nth-child(2) {
            margin-left: 30px;
        }

        ul {
            margin-left: 18px;
            padding-left: 0;
        }

        ul > li {
            margin-bottom: 15px;
        }

        ul > li:last-child {
            margin-bottom: 0;
        }
	</style>
    <link rel="stylesheet" href="./verticalTimeline.css">
</head>
<body>
<a href="<?= get_home_url(); ?>" target="_blank"
   style="
        position: absolute;
        top: -10px;
        left: 0;
    ">
    <img width="90" src="data:image/svg+xml;base64,<?= $base64_logo; ?>"/>
</a>
<div class="">
	<h1><?= $cvmeta['field_name'][0] . ' ' . $cvmeta['field_surname'][0] ?></h1>
    <div class="">
		<div class="left-column">
            <section>
                <div class="left-section-heading"><?php _e('Personal information', 'cv-generator'); ?></div>
                <div>
	                <?php _e('Phone: ', 'cv-generator'); ?> <a href="tel:<?=$cvmeta['field_phone'][0]?>">
                        <?=$cvmeta['field_phone'][0]?>
                    </a> <br />
	                <?php _e('Email: ', 'cv-generator'); ?>
                    <a href="mailto:<?=$user_email?>">
	                    <?=$user_email?>
                    </a>
                </div>
            </section>
            <?php
            if (json_decode($cvmeta['field_languages'][0], true)):
            ?>
            <section>
                <div class="left-section-heading"><?php _e('Languages', 'cv-generator'); ?></div>
                <div>
                    <?php
                    foreach (json_decode($cvmeta['field_languages'][0], true) as $lang):
                        echo $lang['field_language_name'] . ' - ' . $lang['field_proficiency'] . "<br />";
                    endforeach;
                    ?>
                </div>
            </section>
            <?php
            endif;
            ?>
			<?php
			if ($cvmeta['field_i_have_driving_license'][0]):
			?>
            <section>
			    <div class="left-section-heading"><?php _e('Driving license', 'cv-generator'); ?></div>
                <div><?php _e('Yes', 'cv-generator')?></div>
            </section>
            <?php
            endif;
            ?>
			<?php
			if ($cvmeta['field_digital_skills'][0]):
			?>
            <section>
                <div class="left-section-heading"><?php _e('Digital Skills', 'cv-generator'); ?></div>
                <div><?= $cvmeta['field_digital_skills'][0] ?></div>
            </section>
            <?php
            endif;
            ?>
			<?php
			if ($cvmeta['field_other_skills'][0]):
			?>
            <section>
                <div class="left-section-heading"><?php _e('Skills', 'cv-generator'); ?></div>
                <div><?= $cvmeta['field_other_skills'][0] ?></div>
            </section>
			<?php
			endif;
			?>
			<?php
			if ($cvmeta['field_hobbies'][0]):
			?>
            <section>
                <div class="left-section-heading"><?php _e('Hobbies', 'cv-generator'); ?></div>
                <div><?= $cvmeta['field_hobbies'][0] ?></div>
            </section>
			<?php
			endif;
			?>
        </div>
		<div class="right-column">
			<?php if ($cvmeta['field_bio'][0]): ?>
            <section>
                <div style="">
                    <img src="data:image/svg+xml;base64,<?= $base64_user_icon; ?>" class="icon"/>
                    <span class="right-section-heading">
                        <?php _e('Bio', 'cv-generator'); ?>
                    </span>
                </div>
                <div>
                    <?= $cvmeta['field_bio'][0]; ?>
                </div>
            </section>
            <?php endif;?>
            <?php if (isset($user_thumbnail_url)): ?>
            <section>
                <div style="">
                    <img src="data:image/svg+xml;base64,<?= $base64_video_icon; ?>" class="icon"/>
                    <span class="right-section-heading">
                        <?php _e('Video', 'cv-generator'); ?>
                    </span>
                </div>
                <div>
                    <a href="<?=$video_url?>" class="image" target="_blank" style="text-decoration: none; color: black;">
                        <img width="100%" src="data:image/png;base64,<?=base64_encode(file_get_contents($user_thumbnail_url))?>" style="border-radius: 10px;">
                        <?php _e('Click to view the video', 'cv-generator'); ?>
                    </a>
                </div>
            </section>
			<?php endif;?>
			<?php if (json_decode($cvmeta['field_education'][0], true)): ?>
            <section>
                <div style="">
                    <img src="data:image/svg+xml;base64,<?= $base64_education_icon; ?>" class="icon"/>
                    <span class="right-section-heading">
                        <?php
                        setlocale( LC_ALL, get_user_locale($current_user_id));
                        _e('Education', 'cv-generator');
                        ?>
                    </span>
                </div>
                <div>
                    <ul>
	                <?php
	                foreach (json_decode($cvmeta['field_education'][0], true) as $education):
                        $schoolName = $education['field_school'] ?? '';
                        $dateFromEdu = $education['field_from'] ?? '';
                        $dateToEdu = (array_key_exists('field_still_learning', $education) && $education['field_still_learning']) ? null : $education['field_to'];
                        ?>
                    <li>
                        <div class="list-item">
                            <div class="list-item-heading"><?=$schoolName?></div>
                            <div class="date"> <?=cvgen_format_the_date($dateFromEdu)?> - <?=$dateToEdu ? cvgen_format_the_date($dateToEdu) : '...'?> </div>
                        </div>
                    </li>
                    <?php
	                endforeach;
	                ?>
                    </ul>
                </div>
            </section>
			<?php endif;?>
			<?php if (json_decode($cvmeta['field_work_experience'][0], true)): ?>
            <section>
                <div style="">
                    <img src="data:image/svg+xml;base64,<?= $base64_employment_icon; ?>" class="icon"/>
                    <span class="right-section-heading">
                        <?php _e('Employment History', 'cv-generator'); ?>
                    </span>
                </div>
                <div>
                    <ul>
                    <?php
					foreach (json_decode($cvmeta['field_work_experience'][0], true) as $work):
						$position = $work['field_position'] ?? '';
						$company = $work['field_company'] ?? '';
						$description = $work['field_description'] ?? '';
						$dateFromWork = $work['field_from'] ?? '';
						$dateToWork = (array_key_exists('field_still_working', $work) && $work['field_still_working']) ? null : $work['field_to'];
                        ?>
                            <li>
                                <div class="list-item-heading"><?=$position?></div>
                                <div> <?=$company?> </div>
                                <div class="date"> <?=cvgen_format_the_date($dateFromWork)?> - <?=$dateToWork ? cvgen_format_the_date($dateToWork) : '...'?> </div>
                                <div> <?=$description?> </div>
                            </li>
					<?php
					endforeach;
					?>
                    </ul>
                </div>
            </section>
			<?php endif;?>
        </div>
	</div>
</div>
</body>
</html>
