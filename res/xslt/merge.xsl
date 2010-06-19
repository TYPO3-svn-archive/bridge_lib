<!DOCTYPE xsl:stylesheet [
<!ENTITY lt     "&#38;#60;">
<!ENTITY gt     "&#62;">
<!ENTITY amp    "&#38;#38;">
<!ENTITY apos   "&#39;">
<!ENTITY quot   "&#34;">
<!ENTITY nbsp   "&#160;">
<!ENTITY euro	"\euro">
]>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  xmlns:tt="http://notyetpublished" >
	<xsl:output method="xml" />
	<xsl:template match="/tt:toc">
		<xsl:element name="tt:toc">
			<xsl:call-template name="elem-rec" />
		</xsl:element>
	</xsl:template>
	<xsl:template name="elem-rec">
		<xsl:for-each select="tt:elem">
			<xsl:element name="tt:elem">
				<xsl:call-template name="elem-inc" />
				<xsl:call-template name="elem-rec" />
			</xsl:element>			
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="elem-inc">
		<xsl:attribute name="level">
			<xsl:value-of select="@level" />
		</xsl:attribute>
		<xsl:copy-of select="document(@href)" />
	</xsl:template>
</xsl:stylesheet>