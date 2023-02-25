<?php
//$user_thumbnail_url

?>
<!DOCTYPE html>
<html>
<head>
	<title>My CV</title>
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

        .container {
            display: block;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .row {
            display: block;
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

        .section-heading {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .bio {
            margin-top: 20px;
        }

        .title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .date {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .duties {
            margin-left: 20px;
        }

        .image {
            position: absolute;
            bottom: 0;
            max-width: 200px;
            max-height: 200px;
            padding: 5px;
            cursor: pointer;
        }

        .icon {
            width: 28px;
            height: 28px;
            margin-right: 18px;
            margin-left: 4px;
            vertical-align: middle;
            margin-bottom: 4px;
        }
	</style>
    <link rel="stylesheet" href="./verticalTimeline.css">
</head>
<body>
<div class="container">
	<h1><?= $cvmeta['field_name'][0] . ' ' . $cvmeta['field_surname'][0] ?></h1>
	<div class="row">
		<div class="left-column">
			<div class="section-heading">Languages</div>
			<p>English, Spanish, French</p>
			<div class="section-heading">Details</div>
			<p>123 Main St. | Anytown, USA | (123) 456-7890 | email@example.com</p>
			<div class="section-heading">Driving License</div>
			<p>Yes, valid license</p>
			<div class="section-heading">Skills</div>
			<p>Microsoft Office, Adobe Creative Suite, HTML/CSS, JavaScript, Project Management</p>
            <?php if (isset($user_thumbnail_url)): ?>
			    <a href="/hey" class="image"><img width="200" src="data:image/png;base64,<?=base64_encode(file_get_contents($user_thumbnail_url))?>"></a>
            <?php endif;?>
        </div>
		<div class="right-column">
			<div class="bio section">
                <h2><img class="icon" src="<?=CVGEN_ASSETS_DIR . '/cv-templates/img.png'?>">Bio</h2>
                <div class="vtl">
				    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vel mauris sit amet lacus faucibus efficitur. Etiam convallis vel nulla ac tincidunt. Cras vel purus ac velit congue bibendum id id enim. Suspendisse in ante neque. Donec et tincidunt justo. </p>
                </div>
			</div>
			<div class="education section">
				<h2><img class="icon" src="<?=CVGEN_ASSETS_DIR . '/cv-templates/img.png'?>">Education</h2>
                <div class="vtl">
                    <div class="listItem">
                        <div class="title">Bachelor of Science in Computer Science</div>
                        <div class="date">September 2014 - May 2018</div>
                        <div class="duties">
                            <ul>
                                <li>Studied various computer science topics including programming, algorithms, and data structures</li>
                                <li>Participated in extracurricular coding projects and hackathons</li>
                                <li>Completed a senior thesis on using machine learning for predicting stock prices</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
			<div class="employment section">
				<h2><img class="icon" src="<?=CVGEN_ASSETS_DIR . '/cv-templates/img.png'?>">Employment History</h2>
                <div class="vtl">
                    <div class="listItem">
                        <div class="title">Software Engineer at XYZ Company</div>
                        <div class="date">June 2018 - Present</div>
                        <div class="duties">
                            <ul>
                                <li>Develop and maintain software for the company's flagship product</li>
                                <li>Collaborate with cross-functional teams to ensure successful product launches</li>
                                <li>Lead development of new product features and improvements</li>
                            </ul>
                        </div>
                    </div>
                    <div class="listItem">
                        <div class="title">Intern at ABC Corporation</div>
                        <div class="date">May 2017 - August 2017</div>
                        <div class="duties">
                            <ul>
                                <li>Assisted senior software engineers with programming tasks</li>
                                <li>Contributed to development of a new software module for the company's product</li>
                                <li>Participated in team meetings and code reviews</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
			<div class="hobbies section">
				<h2><img class="icon" src="<?=CVGEN_ASSETS_DIR . '/cv-templates/img.png'?>">Hobbies and Interests</h2>
                <div class="vtl">
				    <p>Traveling, hiking, photography, playing guitar</p>
                </div>
			</div>
		</div>
	</div>
</div>
</body>
</html>
