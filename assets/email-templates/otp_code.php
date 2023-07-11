<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$subject?></title>
	<style>
		html, body {
			margin: 0;
			padding: 0;
            background-color: #efefef;
            font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif
		},
	</style>
</head>
<body>
	<div style="margin-top: 5%; margin-left: auto; margin-right: auto; max-width: 800px; background-color: #fefefe; ">
		<div style="background-color: #557da1; width: 100%;">
			<h1 style="padding: 30px 20px;color: white; ">
				<?= $subject; ?>
			</h1>
		</div>
		<div style="padding: 20px;">
			<?= $body ?>
		</div>
        <div style="padding: 20px 20px 50px 20px">
            <?= $signature ?>
        </div>
	</div>
</body>
</html>