<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output media-type="text/html" method="html" indent="no" encoding="utf-8"/>

<xsl:template match="/">
	<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE HTML&gt;</xsl:text>
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>
				<xsl:value-of select="/page/@title"/>
			</title>
			<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
			<meta name="viewport" content="width=device-width, initial-scale=1"/>
			<!--link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.css" rel="stylesheet"/-->
			<link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
			<link href="assets/css/default.css" rel="stylesheet"/>
			<xsl:comment>
				<xsl:text disable-output-escaping="yes">[if lt IE 9]&gt;
					&lt;script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"&gt;&lt;/script&gt;
					&lt;script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"&gt;&lt;/script&gt;
					&lt;script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"&gt;&lt;/script&gt;
				&lt;![endif]</xsl:text>
			</xsl:comment>
			<xsl:comment><xsl:text disable-output-escaping="yes">[if gte IE 9]&gt;&lt;!</xsl:text></xsl:comment>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
			<xsl:comment><xsl:text disable-output-escaping="yes">&lt;![endif]</xsl:text></xsl:comment>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
			<script src="assets/js/default.js"></script>
		</head>
	</html>
	<body>
		<xsl:apply-templates/>
		<xsl:apply-templates select="//script" mode="js"/>
	</body>
</xsl:template>

<!-- form -->
<xsl:template match="form">
	<div class="image-container set-full-height">
		<div class="container">
			<div id="content-block" class="panel panel-default"> 
				<div class="panel-heading">
					<h1 class="h3">
						<xsl:value-of select="/page/@title"/>
					</h1>
				</div>
				<div class="panel-body">
					<form class="form-horizontal">
						<xsl:copy-of select="@id | @action | @method | @onsubmit"/>
						<xsl:choose>
							<xsl:when test="tab">
								<ul id="setup-tabs" class="nav nav-tabs nav-justified" role="tablist">
									<xsl:apply-templates select="tab" mode="nav"/>
								</ul>
								<div class="tab-content">
									<xsl:apply-templates/>
								</div>
							</xsl:when>
							<xsl:otherwise>
								<xsl:apply-templates/>
							</xsl:otherwise>
						</xsl:choose>
					</form>
				</div>
			</div>
		</div>
	</div>
	<xsl:apply-templates select="success | error" mode="alert-message"/>
</xsl:template>

<!-- tabs -->
<xsl:template match="form/tab" mode="nav">
	<xsl:variable name="isActive" select="not(preceding-sibling::tab)"/>
	<li>
		<xsl:if test="$isActive">
			<xsl:attribute name="class">active</xsl:attribute>
		</xsl:if>
		<a href="#{@id}">
			<xsl:if test="$isActive">
				<xsl:attribute name="aria-expanded">true</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="@title"/>
		</a>
	</li>
</xsl:template>

<xsl:template match="form/tab">
	<xsl:variable name="isActive" select="not(preceding-sibling::tab)"/>
	<div>
		<xsl:attribute name="class">
			<xsl:text>tab-pane fade</xsl:text>
			<xsl:if test="$isActive"> in active</xsl:if>
		</xsl:attribute>
		<xsl:copy-of select="@id"/>
		<fieldset>
			<xsl:apply-templates/>
		</fieldset>
	</div>
</xsl:template>

<!-- fieldset -->
<xsl:template match="form//fieldset">
	<div class="form-group">
		<label class="col-md-4 control-label">
			<xsl:value-of select="@title"/>
		</label>
		<xsl:apply-templates select="field" mode="fieldset"/>
	</div>
</xsl:template>

<xsl:template match="form//field" mode="fieldset">
	<xsl:call-template name="field">
		<xsl:with-param name="size" select="'4'"/>
	</xsl:call-template>
</xsl:template>

<!-- hidden -->
<xsl:template match="form//field[@type='hidden']">
	<input type="hidden">
		<xsl:copy-of select="@name | @value"/>
	</input>
</xsl:template>

<!-- text -->
<xsl:template match="form//field[@type='text' or @type='number' or @type='email']">
	<xsl:variable name="id">
		<xsl:call-template name="field-id"/>
	</xsl:variable>
	<div class="form-group">
		<label class="col-md-4 control-label" for="{$id}">
			<xsl:value-of select="@label"/>
			<xsl:call-template name="required-sign"/>
		</label>
		<xsl:call-template name="field"/>
	</div>
</xsl:template>

<!-- select -->
<xsl:template match="form//field[@type='select' and @class='text-select']" priority="1">
	<xsl:variable name="id">
		<xsl:call-template name="field-id"/>
	</xsl:variable>
	<div class="form-group">
		<label class="col-md-4 control-label" for="{$id}">
			<xsl:value-of select="@label"/>
			<xsl:call-template name="required-sign"/>
			<xsl:apply-templates select="popover"/>
		</label>
		<xsl:call-template name="field">
			<xsl:with-param name="size" select="'4'"/>
			<xsl:with-param name="type" select="'text'"/>
			<xsl:with-param name="value" select="@value"/>
		</xsl:call-template>
		<div class="col-md-4">
			<select class="form-control" onchange="$('#{$id}').val($(this).val())">
				<xsl:apply-templates select="option"/>
			</select>
		</div>
	</div>
</xsl:template>

<xsl:template match="form//field/option">
	<xsl:copy-of select="."/>
</xsl:template>

<!-- popover -->
<xsl:template match="form//field/popover">
	<xsl:variable name="id">
		<xsl:call-template name="field-id">
			<xsl:with-param name="field" select="ancestor::field"/>
		</xsl:call-template>
		<xsl:text>_popover</xsl:text>
	</xsl:variable>
	<xsl:text> </xsl:text>
	<a id="{$id}" data-toggle="popover" data-content="{content}"><span class="glyphicon glyphicon-info-sign"></span></a>
	<script>$(function(){$('#<xsl:value-of select="$id"/>').popover({template:'<xsl:copy-of select="template/*"/>'});});</script>
</xsl:template>

<!-- buttons -->
<xsl:template match="form//buttonset">
	<div class="wizard-footer">
		<xsl:apply-templates/>
	</div>
</xsl:template>

<xsl:template match="buttonset//button">
	<div>
		<xsl:attribute name="class">
			<xsl:choose>
				<xsl:when test="@align='right'">pull-right</xsl:when>
				<xsl:otherwise>pull-left</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
		<button>
			<xsl:attribute name="class">
				<xsl:text>btn btn-</xsl:text>
				<xsl:choose>
					<xsl:when test="@class">
						<xsl:value-of select="@class"/>
					</xsl:when>
					<xsl:otherwise>default</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:copy-of select="@onclick | @type"/>
			<xsl:apply-templates/>
		</button>
	</div>
</xsl:template>

<xsl:template match="buttonset//button/icon">
	<xsl:text> </xsl:text>
	<span class="glyphicon glyphicon-{@name}"></span>
	<xsl:text> </xsl:text>
</xsl:template>

<!-- modal -->
<xsl:template name="alert-message">
	<xsl:param name="content" select="text()"/>
	<div id="alert-message" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&#215;</span></button>
					<xsl:value-of select="$content" disable-output-escaping="yes"/>
				</div>
			</div>
		</div>
	</div>
	<script>$('#alert-message').modal({show:true});</script>
</xsl:template>

<!-- php-checker -->
<xsl:template match="php-checker[*]">
	<table class="table table-striped">
		<xsl:apply-templates select="*"/>
	</table>
</xsl:template>

<xsl:template match="php-checker/ext">
	<tr>
		<th scope="row">
			<xsl:value-of select="@name"/>
		</th>
		<td>
			<xsl:choose>
				<xsl:when test="@ok">extension is loaded</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="text()" disable-output-escaping="yes"/>
				</xsl:otherwise>
			</xsl:choose>
		</td>
	</tr>
</xsl:template>

<xsl:template match="php-checker/val">
	<tr>
		<th scope="row">
			<xsl:value-of select="@name"/>
		</th>
		<td>
			<xsl:choose>
				<xsl:when test="@ok">
					<xsl:value-of select="@equal"/>
					<xsl:text> ok</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="text()" disable-output-escaping="yes"/>
				</xsl:otherwise>
			</xsl:choose>
		</td>
	</tr>
</xsl:template>

<!-- jquery file upload installer -->
<xsl:template match="jqueryFileUploadInstaller">
	<div class="panel panel-default" id="jqueryFileUploadInstaller">
		<div class="panel-body">
			<xsl:apply-templates/>
			<div class="alert" role="alert"></div>
			<div class="progress">
				<div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width:1%"></div>
			</div>
		</div>
	</div>
</xsl:template>

<!-- rest templates -->
<xsl:template match="form//success | form//error"/>
<xsl:template match="form//success | form//error" mode="alert-message">
	<xsl:call-template name="alert-message"/>
</xsl:template>

<xsl:template match="script"/>
<xsl:template match="script" mode="js">
	<script>
		<xsl:copy-of select="@src"/>
		<xsl:value-of select="text()" disable-output-escaping="yes"/>
	</script>
</xsl:template>

<xsl:template match="exception | html">
	<xsl:value-of select="text()" disable-output-escaping="yes"/>
</xsl:template>

<xsl:template match="iframe">
	<xsl:copy-of select="."/>
</xsl:template>

<xsl:template name="field-id">
	<xsl:param name="field" select="."/>
	<xsl:value-of select="$field/ancestor::form/@id"/>
	<xsl:text>_</xsl:text>
	<xsl:value-of select="$field/@name"/>
</xsl:template>

<xsl:template name="required-sign">
	<xsl:if test="@required">
		<b>*</b>
	</xsl:if>
</xsl:template>

<xsl:template name="field">
	<xsl:param name="size" select="'8'"/>
	<xsl:param name="type" select="@type"/>
	<xsl:param name="value" select="text()"/>
	<xsl:variable name="id">
		<xsl:call-template name="field-id"/>
	</xsl:variable>
	<div class="col-md-{$size}">
		<input id="{$id}" type="{$type}" class="form-control input-md">
			<xsl:copy-of select="@required | @name | @placeholder | @title"/>
			<xsl:if test="string-length($value)">
				<xsl:attribute name="value">
					<xsl:value-of select="$value"/>
				</xsl:attribute>
			</xsl:if>
		</input>
	</div>
</xsl:template>

</xsl:stylesheet>
