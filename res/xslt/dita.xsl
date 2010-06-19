<!DOCTYPE xsl:stylesheet [
<!ENTITY lt     "&#38;#60;">
<!ENTITY gt     "&#62;">
<!ENTITY amp    "&#38;#38;">
<!ENTITY apos   "&#39;">
<!ENTITY quot   "&#34;">
<!ENTITY nbsp   "&#160;">
<!ENTITY euro	"\euro">
]>
<xsl:stylesheet version="2.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fn="http://www.w3.org/2006/xpath-functions"
  xmlns:tt="http://notyetpublished"
  xmlns:saxon="http://icl.com/saxon" saxon:trace="no"
  exclude-result-prefixes="saxon">

<xsl:output method="xml"/>
<xsl:output encoding="ISO-8859-2" method="xml" doctype-public="-//OASIS//DTD DITA Map//EN" doctype-system="map.dtd"/>

<xsl:template match="/tt:toc">
	<xsl:element name="map">
		<xsl:call-template name="elem-rec" />
	</xsl:element>
</xsl:template>
<xsl:template name="elem-rec">
	<xsl:for-each select="tt:elem">
		<xsl:element name="topicref">
			<xsl:attribute name="href">./dita/<xsl:value-of select="string(@uid)" />.dita</xsl:attribute>
			<xsl:attribute name="type">topic</xsl:attribute>
			<xsl:attribute name="navtitle"><xsl:value-of select="document(@href)/tt:content/tt:headertext" /></xsl:attribute>
			<xsl:attribute name="scope">local</xsl:attribute>
			<xsl:attribute name="format">dita</xsl:attribute>

			<!-- RECURSIVE TEMPLATE CALL-->
			<xsl:call-template name="elem-inc" />
			<xsl:call-template name="elem-rec" />
		</xsl:element>
	</xsl:for-each>
</xsl:template>

<xsl:template name="elem-inc">
	<xsl:apply-templates select="document(@href)" />
</xsl:template>

<!--######################################################################################################
 APPLY ALL TEMPLATES ON tt_content nodes
###################################################################################################### -->
<xsl:character-map name="windows">
  <xsl:output-character character="&#xA;" string="&#xD;&#xA;" />
  <xsl:output-character character="&#x9;" string="   " />
</xsl:character-map>

<xsl:template match="tt:content">
	<xsl:result-document href="dita/{string(@uid)}.dita" method="xml" encoding="ISO-8859-1" use-character-maps="windows" doctype-public="-//OASIS//DTD DITA Topic//EN" doctype-system="topic.dtd">
		<xsl:element name="topic">
			<xsl:attribute name="id">topic-<xsl:value-of select="string(@uid)" /></xsl:attribute>
			<xsl:apply-templates />
		</xsl:element>
	</xsl:result-document>
</xsl:template>

<!--######################################################################################################
 HEADING AND BODYTEXT
###################################################################################################### -->
<xsl:template match="tt:headertext">
	<xsl:if test="node()">
		<xsl:element name="title">
			<xsl:apply-templates />
		</xsl:element>
	</xsl:if>
</xsl:template>

<xsl:template match="tt:bodytext">
	<xsl:apply-templates/>
</xsl:template>

<!--##################################################################################################
 HANDLE HTML TAGS (FROM RTE OUTPUT)
######################################################################################################  -->
<!--FORMATTING-->
<xsl:template match="strong">
	<xsl:element name="b">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="br">
<xsl:apply-templates/>
</xsl:template>

<xsl:template match="p">
	<xsl:element name="p">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="em">
	<xsl:element name="i">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="u">
	<xsl:element name="u">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<!--LISTS-->
<xsl:template match="ul">
	<xsl:element name="ul">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="ol">
	<xsl:element name="ol">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="li">
	<xsl:element name="li">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>


<!--TABLES-->
<xsl:template match="table">
	<xsl:element name="table">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="tr">
	<xsl:element name="row">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="td">
	<xsl:element name="entry">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="hr"></xsl:template>

<xsl:template match="sub">
	<xsl:element name="sub">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="sup">
	<xsl:element name="sup">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="pre">
	<xsl:element name="pre">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="center">
<xsl:apply-templates />
</xsl:template>

<!--###################################################################################################
 HANDLE TT:FORMAT TAGS
#######################################################################################################  -->
<xsl:template match="tt:italic">
	<xsl:element name="i">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="tt:bold">
	<xsl:element name="b">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="tt:underlined">
	<xsl:element name="u">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<!--######################################################################################################
 HANDLE IMAGEs BY cTYPE (NON HTML IMG IMAGES)
###################################################################################################### -->
<xsl:template match="tt:image">
	<xsl:element name="image">
		<xsl:attribute name="href">../<xsl:value-of select="@href" /></xsl:attribute>
		<xsl:attribute name="scalefit">yes</xsl:attribute>
	</xsl:element>
</xsl:template>

</xsl:stylesheet>