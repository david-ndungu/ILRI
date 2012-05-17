<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml" indent="yes" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" />
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<title><xsl:value-of select="/response/*/content/title"/></title>
				<meta charset="utf-8" />
				<link media="screen" href="/themes/colourpanel/screen.css" rel="stylesheet" />
				<link media="screen" href="/themes/colourpanel/grid.css" rel="stylesheet" />
				<link media="screen" href="/themes/colourpanel/form.css" rel="stylesheet" />
				<link media="screen" href="/themes/colourpanel/authentication.css" rel="stylesheet" />
			</head>
			<body>
				<div class="pageContainer">
					<div class="pageHeader">
						<h1><xsl:value-of select="/response/*/content/title"/></h1>
					</div>
					<div class="pageContent">
						<xsl:value-of select="/response/*/content/body" disable-output-escaping="yes"/>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>