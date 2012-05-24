<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml" indent="yes" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" />
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
			<head>
				<title></title>
				<meta charset="utf-8" />
				<link media="screen" href="/themes/blackpanel/screen.css" rel="stylesheet" />
				<link media="screen" href="/themes/blackpanel/grid.css" rel="stylesheet" />
				<link media="screen" href="/themes/blackpanel/form.css" rel="stylesheet" />
				<link media="screen" href="/themes/blackpanel/dashboard.css" rel="stylesheet" />
				<script src="/js/libraries/jquery-1.7.2.min.js" type="text/javascript"></script>
				<script src="/js/base/core.js" type="text/javascript"></script>
				<script src="/js/base/sandbox.js" type="text/javascript"></script>
				<script src="/js/base/core.navigation.js" type="text/javascript"></script>
				<script src="/js/base/core.control.js" type="text/javascript"></script>
				<script src="/js/base/core.control.grid.js" type="text/javascript"></script>
				<script src="/js/base/core.control.form.js" type="text/javascript"></script>
				<script src="/js/apps/studio.js" type="text/javascript"></script>
			</head>
			<body>
				<div class="pageContainer">
					<div class="pageHeader gradientRed">
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
								<xsl:for-each select="/response/studio/NavigationController/*/primary/*/root/*">
								<li>
									<xsl:variable name="parent" select="name(.)"/>
									<a href="{uri}"><xsl:value-of select="text"/></a>
									<ul>
									<xsl:for-each select="/response/studio/NavigationController/*/primary/*/*/*">
										<xsl:variable name="child" select="name(..)"/>
										<xsl:if test="$parent=$child">
									 		<li><a href="{uri}"><xsl:value-of select="text"/></a></li>
										</xsl:if>
									</xsl:for-each>
									</ul>
								</li>
								</xsl:for-each>
							</ul>
						</div>
						<div class="pageContentContent"></div>
					</div>
				</div>
				<script type="text/javascript">core.boot();</script>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>