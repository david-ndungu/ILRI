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
				<script src="/js/libraries/jquery-1.7.2.min.js" type="text/javascript"></script>
				<script src="/js/base/core.js" type="text/javascript"></script>
				<script src="/js/base/sandbox.js" type="text/javascript"></script>
				<script src="/js/base/core.control.js" type="text/javascript"></script>
				<script src="/js/base/core.control.grid.js" type="text/javascript"></script>
				<script src="/js/base/core.navigation.js" type="text/javascript"></script>				
				<script type="text/javascript">
					var grid = new core.control.grid({name: "david", sex: "male"});
				</script>
			</head>
			<body>
				<div class="pageContainer">
					<div class="pageHeader gradientBlack">
						<div class="pageHeaderTitle column grid4of10">
							<h1 class="">Monitoring &amp; Evaluation</h1>
						</div>
						<div class="column grid6of10">
							<ul>
								<li><a href="/signout"><xsl:value-of select="/response/base/locale/signout"/></a></li>
								<li><a href="/changepassword"><xsl:value-of select="/response/base/locale/changepassword"/></a></li>
							</ul>
						</div>
					</div>
					<div class="pageContent">
						<div class="pageContentNavigation">
							<ul>
								<xsl:for-each select="/response/studio/NavigationController/*/primary/*/root/dashboard">
								<li>
									<a href="{uri}"><xsl:value-of select="text"/></a>
									<xsl:for-each select="/response/studio/NavigationController/*/primary/*/dashboard/*">
									<ul>
									 	<li><a href="{uri}"><xsl:value-of select="text"/></a></li>
									</ul>
									</xsl:for-each>
								</li>
								</xsl:for-each>
								<xsl:for-each select="/response/studio/NavigationController/*/primary/*/root/administration">
								<li>
									<a href="{uri}"><xsl:value-of select="text"/></a>
									<xsl:for-each select="/response/studio/NavigationController/*/primary/*/administration/*">
									<ul>
									 	<li><a href="{uri}"><xsl:value-of select="text"/></a></li>
									</ul>
									</xsl:for-each>
								</li>
								</xsl:for-each>
								<xsl:for-each select="/response/studio/NavigationController/*/primary/*/root/content">
								<li>
									<a href="{uri}"><xsl:value-of select="text"/></a>
									<xsl:for-each select="/response/studio/NavigationController/*/primary/*/content/*">
									<ul>
									 	<li><a href="{uri}"><xsl:value-of select="text"/></a></li>
									</ul>
									</xsl:for-each>
								</li>
								</xsl:for-each>
								<xsl:for-each select="/response/studio/NavigationController/*/primary/*/root/maintenance">
								<li>
									<a href="{uri}"><xsl:value-of select="text"/></a>
									<xsl:for-each select="/response/studio/NavigationController/*/primary/*/maintenance/*">
									<ul>
									 	<li><a href="{uri}"><xsl:value-of select="text"/></a></li>
									</ul>
									</xsl:for-each>
								</li>
								</xsl:for-each>
								<xsl:for-each select="/response/studio/NavigationController/*/primary/*/root/statistics">
								<li>
									<a href="{uri}"><xsl:value-of select="text"/></a>
									<xsl:for-each select="/response/studio/NavigationController/*/primary/*/statistics/*">
									<ul>
									 	<li><a href="{uri}"><xsl:value-of select="text"/></a></li>
									</ul>
									</xsl:for-each>
								</li>
								</xsl:for-each>
								<xsl:for-each select="/response/studio/NavigationController/*/primary/*/root/settings">
								<li>
									<a href="{uri}"><xsl:value-of select="text"/></a>
									<xsl:for-each select="/response/studio/NavigationController/*/primary/*/settings/*">
									<ul>
									 	<li><a href="{uri}"><xsl:value-of select="text"/></a></li>
									</ul>
									</xsl:for-each>
								</li>
								</xsl:for-each>
							</ul>
						</div>
						<div class="pageContentContent"></div>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>