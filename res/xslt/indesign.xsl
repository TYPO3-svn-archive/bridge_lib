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
<xsl:template match="/tt:toc">
	<xsl:element name="tt:toc">
		<xsl:call-template name="elem-rec" />
	</xsl:element>
</xsl:template>
<xsl:template name="elem-rec">
	<xsl:for-each select="tt:elem">
		<xsl:element name="tt:elem">
			<!-- heading as attribute to identify the node in the import tree -->
			<xsl:attribute name="headertext">
				<xsl:value-of select="document(@href)/tt:content/tt:headertext" />
			</xsl:attribute>
			<xsl:attribute name="uid">
				<xsl:value-of select="@uid" />
			</xsl:attribute>
			<xsl:attribute name="level">
				<xsl:value-of select="@level" />
			</xsl:attribute>
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
<xsl:template match="tt:content">
	<xsl:element name="tt:content">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<!--######################################################################################################
 STRING REPLACEMENT
###################################################################################################### -->
<xsl:template match="text()|@*" mode="taggedtext">
	<xsl:variable name="content" select="." />
	<xsl:variable name="res">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$content" />
			<xsl:with-param name="replace" select="'&lt;'" />
			<xsl:with-param name="with" select="'\&lt;'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res1">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res" />
			<xsl:with-param name="replace" select="'&gt;'" />
			<xsl:with-param name="with" select="'\&gt;'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:value-of select="$res1"/>
	<xsl:apply-templates />
</xsl:template>

<xsl:template name="replace">
    <xsl:param name="text"/>
    <xsl:param name="replace"/>
    <xsl:param name="with"/>
    <xsl:choose>
      <xsl:when test="contains($text,$replace)">
	    <xsl:value-of  select="concat(substring-before($text,$replace),$with)"/>
        <xsl:call-template name="replace">
          <xsl:with-param name="text" select="substring-after($text,$replace)"/>
          <xsl:with-param name="replace" select="$replace"/>
          <xsl:with-param name="with" select="$with"/>
        </xsl:call-template>
      </xsl:when>
	  <xsl:otherwise>
	  	<xsl:value-of select="$text" />
	  </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!--######################################################################################################
 HEADING AND BODYTEXT
###################################################################################################### -->
<xsl:template match="tt:headertext">
	<xsl:if test="node()">
		<xsl:element name="tt:headertext">
			<xsl:apply-templates />
		</xsl:element>
	</xsl:if>
</xsl:template>

<xsl:character-map name="windows">
  <xsl:output-character character="&#xA;" string="&#xD;&#xA;" />
  <xsl:output-character character="&#x9;" string="   " />
</xsl:character-map>

<xsl:template match="tt:bodytext">
	<xsl:result-document href="text/{string(../@uid)}.txt" method="text" encoding="ISO-8859-1" use-character-maps="windows">&lt;ASCII-WIN&gt;
&lt;Version:3.000000&gt;
<xsl:apply-templates/>
	</xsl:result-document>
	<xsl:element name="tt:bodytext">
		<xsl:attribute name="href">file:///text/<xsl:value-of select="string(../@uid)" />.txt</xsl:attribute>
	</xsl:element>
</xsl:template>

<!--##################################################################################################
 HANDLE HTML TAGS (FROM RTE OUTPUT)
######################################################################################################  -->
<!--FORMATTING-->
<xsl:template match="strong">&lt;cTypeface:Bold&gt;<xsl:apply-templates/>&lt;cTypeface:&gt;</xsl:template>
<xsl:template match="br">&lt;ParaStyle:&gt;</xsl:template>
<xsl:template match="p">&lt;ParaStyle:&gt;<xsl:apply-templates/></xsl:template>

<xsl:template match="em">&lt;cTypeface:Italic&gt;<xsl:apply-templates/>&lt;cTypeface:&gt;</xsl:template>
<xsl:template match="u">&lt;cUnderline:1&gt;<xsl:apply-templates/>&lt;cUnderline:&gt;</xsl:template>

<!--LISTS-->
<xsl:template match="ul"><xsl:apply-templates mode="unordered"/></xsl:template>
<xsl:template match="ol"><xsl:apply-templates mode="ordered"/></xsl:template>
<xsl:template match="li" mode="ordered">
&lt;bnListType:Numbered&gt;<xsl:apply-templates />&lt;bnListType:&gt;</xsl:template>
<xsl:template match="li" mode="unordered">
&lt;bnListType:Bullet&gt;<xsl:apply-templates />&lt;bnListType:&gt;</xsl:template>

<!--TABLES-->
<xsl:template match="table">&lt;TableStart:&gt;<xsl:apply-templates/>&lt;TableEnd:&gt;</xsl:template>
<xsl:template match="tr">&lt;RowStart:&gt;<xsl:apply-templates/>&lt;RowEnd:&gt;</xsl:template>
<xsl:template match="td">&lt;CellStart:1.000000,1.000000&gt;<xsl:apply-templates/>&lt;CellEnd:&gt;</xsl:template>

<xsl:template match="hr"></xsl:template>

<xsl:template match="sub">&lt;cPosition:Subscript&gt;<xsl:apply-templates/>&lt;cPosition:&gt;</xsl:template>
<xsl:template match="sup">&lt;cPosition:Superscript&gt;<xsl:apply-templates/>&lt;cPosition:&gt;</xsl:template>
<xsl:template match="center"><xsl:apply-templates /></xsl:template>

<!--###################################################################################################
 HANDLE TT:FORMAT TAGS
#######################################################################################################  -->
<xsl:template match="tt:italic"><xsl:apply-templates /></xsl:template>
<xsl:template match="tt:bold"><xsl:apply-templates /></xsl:template>
<xsl:template match="tt:underlined"><xsl:apply-templates /></xsl:template>

<!--######################################################################################################
 HANDLE IMAGEs BY cTYPE (NON HTML IMG IMAGES)
###################################################################################################### -->
<xsl:template match="tt:image">
	<xsl:element name="tt:image">
		<xsl:attribute name="href">file:///<xsl:value-of select="@href" /></xsl:attribute>
		<xsl:attribute name="caption">
			<xsl:value-of select="@caption" />
		</xsl:attribute>
	</xsl:element>
</xsl:template>

</xsl:stylesheet>