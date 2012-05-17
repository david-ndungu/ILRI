<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml" indent="yes" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" />
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
			<head>
				<title></title>
				<meta charset="utf-8" />
				<link media="screen" href="/themes/colourpanel/screen.css" rel="stylesheet" />
				<link media="screen" href="/themes/colourpanel/grid.css" rel="stylesheet" />
				<link media="screen" href="/themes/colourpanel/form.css" rel="stylesheet" />
				<link media="screen" href="/themes/colourpanel/dashboard.css" rel="stylesheet" />
			</head>
			<body>
				<div class="pageContainer">
					<div class="pageHeader gradientBlack">
						<div class="pageHeaderTitle column grid3of10">
							<h1 class="">{{pageHeaderTitle}}</h1>
						</div>
						<div class="pageHeaderSearch column grid7of10">
							{{pageHeaderSearch}}
						</div>
					</div>
					<div class="pageContent">
						<div class="pageContentNavigation ">
							{{pageContentNavigation}}
						</div>
						<div class="pageContentContent">
							{{pageContentContent}}
						</div>
					</div>
					<div class="pageFooter gradientBlack">
						<ul>
							<li class="column grid3of10">
								<a href="#">.</a>
							</li>
							<li class="column grid3of10">
								<a href="#">.</a>
							</li>
							<li class="column grid3of10">
								<a href="#">.</a>
							</li>
						</ul>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>	