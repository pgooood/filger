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
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>
			<xsl:value-of select="@title"/>
		</title>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.css" rel="stylesheet"/>
		<link href="assets/css/default.css" rel="stylesheet"/>
		<xsl:comment>
			<xsl:text disable-output-escaping="yes">[if lt IE 9]&gt;
				&lt;script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"&gt;&lt;/script&gt;
				&lt;script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"&gt;&lt;/script&gt;
				&lt;script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"&gt;&lt;/script&gt;
			&lt;![endif]</xsl:text>
		</xsl:comment>
		<xsl:comment><xsl:text disable-output-escaping="yes">[if gte IE 9]&gt;&lt;!</xsl:text></xsl:comment>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
		<xsl:comment><xsl:text disable-output-escaping="yes">&lt;![endif]</xsl:text></xsl:comment>
		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/floatthead/1.3.2/jquery.floatThead.min.js"></script>
		<script src="{@fileUploaderPath}js/vendor/jquery.ui.widget.js"></script>
		<script src="{@fileUploaderPath}js/jquery.iframe-transport.js"></script>
		<script src="{@fileUploaderPath}js/jquery.fileupload.js"></script>
		<script src="assets/js/jquery.xslt.js"></script>
		<script src="assets/js/default.js"></script>
		<xsl:apply-templates select="script"/>
	</head>
	<body>
		<div>
			<div id="output" class="wrapper">
				<xsl:apply-templates select="dir"/>
			</div>
			<div class="progress">
				<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0"></div>
			</div>
			<nav class="navbar navbar-default toolbar">
				<div class="container-fluid">
					<div class="pull-left leftbar">
						<div class="btn-group" role="group">
							<label for="fileupload" class="btn btn-default">
								<span class="glyphicon glyphicon-cloud-upload"></span>
								<input id="fileupload" type="file" name="files[]" multiple="multiple" class="hidden"/>
							</label>
							<button id="new_folder" type="button" class="btn btn-default">
								<xsl:attribute name="title">
									<xsl:text lang="en">Create folder</xsl:text>
								</xsl:attribute>
								<span class="glyphicon glyphicon-folder-open"></span>
							</button>
							<button id="rename" type="button" class="btn btn-default" disabled="disabled">
								<xsl:attribute name="title">
									<xsl:text lang="en">Rename</xsl:text>
								</xsl:attribute>
								<span class="glyphicon glyphicon-pencil"></span>
							</button>
							<button id="remove" type="button" class="btn btn-default" disabled="disabled">
								<xsl:attribute name="title">
									<xsl:text lang="en">Remove</xsl:text>
								</xsl:attribute>
								<span class="glyphicon glyphicon-trash"></span>
							</button>
						</div>
					</div>
					<div class="pull-right rightbar">
						<button id="ok" type="button" class="btn btn-primary">
							<xsl:text lang="en">Ok</xsl:text>
						</button>
						<xsl:text> </xsl:text>
						<button id="cancel" type="button" class="btn btn-default">
							<xsl:text lang="en">Cancel</xsl:text>
						</button>
					</div>
				</div>
			</nav>
		</div>
	</body>
</xsl:template>

<xsl:template match="script">
	<xsl:copy-of select="."/>
</xsl:template>

</xsl:stylesheet>