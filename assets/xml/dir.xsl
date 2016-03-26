<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output media-type="text/html" method="html" indent="no" encoding="utf-8"/>

<xsl:template match="/">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="dir[@url]">
	<script>window.dirUrl='<xsl:value-of select="@url"/>';</script>
	<table id="dir" class="table table-striped dir">
		<thead>
			<xsl:apply-templates select="path"/>
			<tr>
				<th scope="col" class="chb"><input class="select_all" type="checkbox"/></th>
				<th scope="col" class="name">
					<div data-col="name">
						<xsl:if test="@orderCol='name'">
							<xsl:attribute name="class"><xsl:value-of select="@orderDir"/></xsl:attribute>
						</xsl:if>
						<span class="sort-icon"></span>
						<xsl:text> </xsl:text>
						<span class="sort-text">
							<xsl:text lang="en">Name</xsl:text>
						</span>
					</div>
				</th>
				<th scope="col" class="ext">
					<div data-col="ext">
						<xsl:if test="@orderCol='ext'">
							<xsl:attribute name="class"><xsl:value-of select="@orderDir"/></xsl:attribute>
						</xsl:if>
						<span class="sort-icon"></span>
						<xsl:text> </xsl:text>
						<span class="sort-text">
							<xsl:text lang="en">Ext</xsl:text>
						</span>
					</div>
				</th>
				<th scope="col" class="size">
					<div data-col="size">
						<xsl:if test="@orderCol='size'">
							<xsl:attribute name="class"><xsl:value-of select="@orderDir"/></xsl:attribute>
						</xsl:if>
						<span class="sort-icon"></span>
						<xsl:text> </xsl:text>
						<span class="sort-text">
							<xsl:text lang="en">Size</xsl:text>
						</span>
					</div>
				</th>
				<th scope="col" class="date">
					<div data-col="date">
						<xsl:if test="@orderCol='date'">
							<xsl:attribute name="class"><xsl:value-of select="@orderDir"/></xsl:attribute>
						</xsl:if>
						<span class="sort-icon"></span>
						<xsl:text> </xsl:text>
						<span class="sort-text">
							<xsl:text lang="en">Date</xsl:text>
						</span>
					</div>
				</th>
			</tr>
		</thead>
		<tbody>
			<xsl:apply-templates select="dir | file"/>
		</tbody>
	</table>
</xsl:template>

<xsl:template match="dir/path">
	<tr>
		<th class="path" colspan="5">
			<small class="text-muted path-label">
				<xsl:text lang="en">Path</xsl:text>
				<xsl:text>: </xsl:text>
			</small>
			<small class="path-value">
				<xsl:if test="not(item)">
					<span class="text-muted s">/</span>
				</xsl:if>
				<xsl:apply-templates select="item"/>
			</small>
		</th>
	</tr>
</xsl:template>

<xsl:template match="dir/path/item[following-sibling::item]">
	<xsl:variable name="url">
		<xsl:call-template name="path-item-url"/>
	</xsl:variable>
	<span class="text-muted s">/</span>
	<a data-path="{$url}"><xsl:value-of select="text()"/></a>
</xsl:template>

<xsl:template match="dir/path/item[not(following-sibling::item)]">
	<span class="text-muted s">/</span>
	<span><xsl:value-of select="text()"/></span>
</xsl:template>

<xsl:template name="path-item-url">
	<xsl:param name="i" select="count(following-sibling::item)"/>
	<xsl:if test="$i &gt; 0">
		<xsl:text>../</xsl:text>
		<xsl:call-template name="path-item-url">
			<xsl:with-param name="i" select="$i - 1"/>
		</xsl:call-template>
	</xsl:if>
</xsl:template>



<xsl:template match="dir/dir[not(text()='..')]">
	<xsl:variable name="id">
		<xsl:text>dir</xsl:text>
		<xsl:value-of select="count(preceding-sibling::dir)"/>
	</xsl:variable>
	<tr class="item">
		<td class="chb"><input type="checkbox" name="dir" id="{$id}" value="{text()}"/></td>
		<td class="dir" colspan="4"><label for="{$id}"><xsl:value-of select="text()"/></label></td>
	</tr>
</xsl:template>

<xsl:template match="dir/dir[text()='..']">
	<xsl:variable name="id">parentDir</xsl:variable>
	<tr>
		<td class="chb"><input type="hidden" id="{$id}" value="{text()}"/></td>
		<td class="dir" colspan="4"><label for="{$id}"><xsl:value-of select="text()"/></label></td>
	</tr>
</xsl:template>

<xsl:template match="dir/file">
	<xsl:variable name="id">
		<xsl:text>file</xsl:text>
		<xsl:value-of select="count(preceding-sibling::file)"/>
	</xsl:variable>
	<tr class="item">
		<td class="chb"><input type="checkbox" id="{$id}" name="file" value="{text()}"/></td>
		<td class="file">
			<label for="{$id}" style="background-image:url(assets/images/icons/{@ico}.png)"><xsl:value-of select="@name"/></label>
		</td>
		<td class="ext"><xsl:value-of select="@ext"/></td>
		<td class="size"><xsl:value-of select="@size"/></td>
		<td class="date"><xsl:value-of select="@date"/></td>
	</tr>
</xsl:template>

</xsl:stylesheet>