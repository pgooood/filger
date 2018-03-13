<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output media-type="text/html" method="html" indent="no" encoding="utf-8"/>

<xsl:template match="/">
	<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE HTML&gt;</xsl:text>
	<html>
		<xsl:apply-templates/>
	</html>
</xsl:template>

<xsl:template match="/page">
	<head>
		<meta charset="utf-8"/>
		<title>
			<xsl:value-of select="@title"/>
		</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
		<link href="//fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet"/>
		<link href="assets/css/default.css" rel="stylesheet"/>
	</head>
	<body>
		<div id="app">
			<dir-list/>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://unpkg.com/sticky-table-headers@0.1.24/js/jquery.stickytableheaders.min.js"></script>
		<script src="{@fileUploaderPath}js/vendor/jquery.ui.widget.js"></script>
		<script src="{@fileUploaderPath}js/jquery.iframe-transport.js"></script>
		<script src="{@fileUploaderPath}js/jquery.fileupload.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/vue@2.5.13/dist/vue.js"></script>
		<script src="https://unpkg.com/vue-i18n@7.4.2/dist/vue-i18n.js"></script>
		<xsl:apply-templates select="script"/>
		<script src="assets/js/app.js"></script>
		<xsl:if test="message">
			<script>
				<xsl:text>$(function(){function message(v){var editor = getActiveEditor();if(editor)editor.windowManager.alert(v);else alert(v);};</xsl:text>
				<xsl:apply-templates select="message" mode="js"/>
				<xsl:text>});</xsl:text>
			</script>
		</xsl:if>
	</body>
</xsl:template>

<xsl:template match="message" mode="js">message('<xsl:value-of select="text()"/>');</xsl:template>

<xsl:template match="script">
	<xsl:copy-of select="."/>
</xsl:template>

</xsl:stylesheet>