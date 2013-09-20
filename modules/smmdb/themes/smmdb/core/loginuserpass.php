<?php
$languages = $this->getLanguageList();
$currentLanguage = null;
foreach ($languages as $lang => $current) {
	if ($current) {
		$currentLanguage = $lang;
		break;
	}
}
?><!DOCTYPE html>
<html<?php if ($currentLanguage) echo ' lang="'.$currentLanguage.'"'; ?>>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Studentmediene Pålogging">
		<meta name="author" content="SMITIT">
		<link rel="icon" href="<?php echo SimpleSAML_Module::getModuleURL('smmdb/images/smit_logo.ico'); ?>" type="image/x-icon"/>

		<title><?php echo $this->t('{login:user_pass_header}'); ?></title>

		<!-- Bootstrap core CSS -->
		<link href="<?php echo SimpleSAML_Module::getModuleURL('smmdb/css/bootstrap.min.css'); ?>" rel="stylesheet">

		<!-- Custom styles for this template -->
		<link href="<?php echo SimpleSAML_Module::getModuleURL('smmdb/css/signin.css'); ?>" rel="stylesheet">
	</head>

	<body>

		<div class="container">
			<form class="form-signin" method="post" action="?">
				<img src="<?php echo SimpleSAML_Module::getModuleURL('smmdb/images/smit_logo_300px.png'); ?>" width="300px">
				<h2 class="form-signin-heading">Logg inn</h2>
				
				<div style="display:none;"><?php
foreach ($this->data['stateparams'] as $name => $value) {
	echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
}
?></div>
				<input type="text" id="username" name="username" class="form-control" placeholder="<?php echo $this->t('{login:username}'); ?>" autofocus<?php if (isset($this->data['username'])) {
						echo ' value="' . htmlspecialchars($this->data['username']) . '"';
					} ?>>
				<input type="password" id="password" name="password" class="form-control" placeholder="<?php echo $this->t('{login:password}'); ?>">
				<button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $this->t('{login:login_button}'); ?></button>
			</form>

		</div>
	</body>
</html>
