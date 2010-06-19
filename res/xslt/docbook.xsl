<!DOCTYPE xsl:stylesheet [
<!ENTITY lt     "&#38;#60;">
<!ENTITY gt     "&#62;">
<!ENTITY amp    "&#38;#38;">
<!ENTITY apos   "&#39;">
<!ENTITY quot   "&#34;">
<!ENTITY nbsp   "&#160;">
<!ENTITY euro	"\euro">
]>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:tt="http://notyetpublished" >
 <xsl:output encoding="ISO-8859-2" method="xml" doctype-public="-//OASIS//DTD DocBk XML V5.0//DE" doctype-system="http://www.oasis-open.org/docbook/xml/5.0b5/dtd/docbook.dtd"/>
<xsl:template match="/tt:toc">

	<xsl:element name="book">
	   <xsl:apply-templates />
   	</xsl:element>
</xsl:template>


<!--######################################################################################################
 APPLY ALL TEMPLATES ON tt_content nodes
###################################################################################################### -->

<xsl:template match="tt:elem">
	<xsl:if test="node()">
		<xsl:choose>
			<xsl:when test="@level = '0'">
				<xsl:element name="chapter">
					<xsl:apply-templates />
				</xsl:element>
			</xsl:when>
			<xsl:when test="@level = '1'">
				<xsl:element name="section">
					<xsl:apply-templates />
				</xsl:element>
			</xsl:when>
			<xsl:when test="@level = '2'">
				<xsl:element name="section">
					<xsl:apply-templates />
				</xsl:element>
			</xsl:when>
			<xsl:when test="@level = '3'">
				<xsl:element name="section">
					<xsl:apply-templates />
				</xsl:element>
			</xsl:when>
			<xsl:otherwise><xsl:apply-templates /></xsl:otherwise>
		</xsl:choose>
	</xsl:if>
</xsl:template>

<xsl:template match="tt:content">
	<xsl:if test="node()">
		<xsl:apply-templates />
	</xsl:if>
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
	<xsl:apply-templates />
</xsl:template>

<!--##################################################################################################
 HANDLE HTML TAGS (FROM RTE OUTPUT)
######################################################################################################  -->

<xsl:template match="br"><xsl:apply-templates /></xsl:template>
<xsl:template match="p">
	<xsl:element name="para">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<!--FORMATTING-->
<xsl:template match="strong">
	<xsl:apply-templates />
</xsl:template>

<xsl:template match="em">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="u"><xsl:apply-templates /></xsl:template>
<xsl:template match="code"><xsl:apply-templates /></xsl:template>
<xsl:template match="blockquote">
	<xsl:element name="blockquote">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="cite"> </xsl:template>

<!--LISTS-->
<xsl:template match="ul">
	<xsl:element name="segementedlist">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="ol">
	<xsl:element name="orderedlist">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="li">
	<xsl:element name="listitem">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<!--TABLES-->
<xsl:template match="table">
	<xsl:element name="table">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="tr">
	<xsl:element name="tr">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="td">
	<xsl:element name="td">
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>

<xsl:template match="hr"></xsl:template>

<xsl:template match="sub"><xsl:apply-templates /></xsl:template>
<xsl:template match="sup"><xsl:apply-templates /></xsl:template>
<xsl:template match="center"><xsl:apply-templates /></xsl:template>
<xsl:template match="a"><xsl:apply-templates /></xsl:template>

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
	<xsl:element name="figure">
	    <xsl:element name="title">Testbild</xsl:element>
	    <xsl:element name="mediaobject">
		    <xsl:element name="imageobject">
			    <xsl:element name="imagedata">
					<xsl:attribute name="fileref">
						<xsl:value-of select="@href" />
					</xsl:attribute>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:element>
</xsl:template>
</xsl:stylesheet>
