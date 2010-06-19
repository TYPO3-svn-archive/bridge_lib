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

<xsl:output method="text" />

<xsl:template match="/tt:toc">
   <xsl:call-template name="open-file" />
</xsl:template>

<!-- apply templates to all href files (tt_content should match)-->
<xsl:template name="open-file">
\documentclass[%
	parskip=half*,  
    moretext,      
	ngerman,       
	twoside
	]{scrreprt}

\usepackage[T1]{fontenc}        %Schriftkodierung
\usepackage[utf8]{inputenc}     %Unicode-Zeichensatz (Unix/Linux)
\usepackage{textcomp}  
\usepackage{lmodern}    
\usepackage{array,booktabs,longtable}
\usepackage{eurosym}
\usepackage{graphicx}
\usepackage{tabulary}
\usepackage{float}
\usepackage[ngerman]{babel}
\usepackage{color}
\usepackage{listings}
\usepackage{hyperref}

\begin{document}

<!-- ### DEFINE OWN COLUMNTYPE FOR THE TABLE ### -->
\newcolumntype{v}[1]{%
	>{\raggedright\hspace{0pt}}p{#1}%
}	

<!--######################################################################################################
 WRITE TABLE OF CONTENTS WITH LATEX
###################################################################################################### -->

\tableofcontents


<!--######################################################################################################
 APPLY XSLT ON ALL XML FILES IN THE TOC
###################################################################################################### -->


<xsl:apply-templates />


\listoffigures
\end{document}
</xsl:template>


<!--######################################################################################################
 APPLY ALL TEMPLATES ON tt_content nodes
###################################################################################################### -->
<xsl:template match="tt:content">
	\label{<xsl:value-of select="@uid" />}
	<xsl:apply-templates />
</xsl:template>

<!--######################################################################################################
 STRING REPLACEMENT WITH XSLT 1.0
###################################################################################################### -->
<xsl:template match="text()">
	<xsl:call-template name="replace-and-print">
		<xsl:with-param name="content" select="."/>
	</xsl:call-template>
</xsl:template>

<xsl:template name="replace-and-print">
 	<xsl:param name="content" />
	<xsl:variable name="res">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$content" />
			<xsl:with-param name="replace" select="'}'" />
			<xsl:with-param name="with" select="'\}'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res1">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res" />
			<xsl:with-param name="replace" select="'{'" />
			<xsl:with-param name="with" select="'\{'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res2">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res1" />
			<xsl:with-param name="replace" select="'#'" />
			<xsl:with-param name="with" select="'\#'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res3">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res2" />
			<xsl:with-param name="replace" select="'$'" />
			<xsl:with-param name="with" select="'\$'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res4">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res3" />
			<xsl:with-param name="replace" select="'_'" />
			<xsl:with-param name="with" select="'\_'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res5">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res4" />
			<xsl:with-param name="replace" select="'%'" />
			<xsl:with-param name="with" select="'\%'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res6">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res5" />
			<xsl:with-param name="replace" select="'~'" />
			<xsl:with-param name="with" select="'\~'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res7">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res6" />
			<xsl:with-param name="replace" select="'^'" />
			<xsl:with-param name="with" select="'\^'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res8">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res7" />
			<xsl:with-param name="replace" select="'&quot;'" />
			<xsl:with-param name="with" select="'``'" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res9">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res8" />
			<xsl:with-param name="replace" select="'&#32;&#32;&#32;'" />
			<xsl:with-param name="with" select="'\quad '" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:variable name="res10">
		<xsl:call-template name="replace">
			<xsl:with-param name="text" select="$res9" />
			<xsl:with-param name="replace" select="'&#x9;'" />
			<xsl:with-param name="with" select="'\quad \quad '" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:value-of select="$res10"/>
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
<xsl:otherwise><xsl:value-of select="$text" /></xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!--######################################################################################################
 HEADING AND BODYTEXT
###################################################################################################### -->
<xsl:template match="tt:headertext">
	<xsl:if test="node()">
	<xsl:choose>
		<xsl:when test="../../@level = '0'">\chapter[ <xsl:apply-templates /> ]{<xsl:apply-templates /> }</xsl:when>
		<xsl:when test="../../@level = '1'">\section[ <xsl:apply-templates /> ]{<xsl:apply-templates /> }</xsl:when>
		<xsl:when test="../../@level = '2'">\subsection[ <xsl:apply-templates /> ]{<xsl:apply-templates /> }</xsl:when>
		<xsl:when test="../../@level = '3'">\subsubsection[ <xsl:apply-templates /> ]{<xsl:apply-templates /> }</xsl:when>
		<xsl:otherwise><xsl:apply-templates />	</xsl:otherwise>
	</xsl:choose>
</xsl:if>
</xsl:template>

<xsl:template match="tt:bodytext"><xsl:apply-templates /></xsl:template>

<!--##################################################################################################
 HANDLE HTML TAGS (FROM RTE OUTPUT)
######################################################################################################  -->

<xsl:template match="br">\\<xsl:apply-templates /></xsl:template>
<xsl:template match="p">
<xsl:apply-templates />
\vspace{2mm}
</xsl:template>

<!--FORMATTING-->
<xsl:template match="strong">\textbf{<xsl:apply-templates />}</xsl:template>
<xsl:template match="em">\emph{<xsl:apply-templates /> }</xsl:template>
<xsl:template match="u">\underline{<xsl:apply-templates /> } </xsl:template>
<xsl:template match="code">\begin{lstlisting}<xsl:apply-templates />  \end{lstlisting}</xsl:template>
<xsl:template match="blockquote">  <xsl:apply-templates /></xsl:template>
<xsl:template match="cite"> </xsl:template>

<!--LISTS-->
<xsl:template match="ul">\begin{itemize}<xsl:apply-templates />\end{itemize}</xsl:template>
<xsl:template match="ol">\begin{enumerate}<xsl:apply-templates />\end{enumerate}</xsl:template>
<xsl:template match="li">\item <xsl:apply-templates /></xsl:template>

<!--TABLES-->
<xsl:template match="table">
	<xsl:variable name="columns" select="count(tr[1]/td)" />	
	<!-- Make a tabular width the number of colums determined by the number of tds in the first row, each colum has the same width-->
	\tymin=3cm
	\begin{tabulary}{\linewidth}{*{<xsl:value-of select="$columns"/>}{|>{\raggedright\hspace{0pt}}L}|}
		\hline		
			<xsl:apply-templates />
	\end{tabulary}
</xsl:template>

<xsl:template match="tr">
	<xsl:apply-templates /> \tabularnewline \hline
</xsl:template>

<xsl:template match="td">
	<xsl:choose>
		<!-- The last row is not terminated bei & -->
		<xsl:when test="position() != last()">
			<xsl:apply-templates /> &amp;
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates /> 
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="tbody"><xsl:apply-templates /></xsl:template>

<xsl:template match="hr">
\hrulefill
</xsl:template>
<xsl:template match="sub">_<xsl:apply-templates /></xsl:template>
<xsl:template match="sup">^<xsl:apply-templates /></xsl:template>
<xsl:template match="center">\begin{center}<xsl:apply-templates />\end{center}</xsl:template>
<xsl:template match="a">
	<xsl:variable name="href4latex">
		<xsl:call-template name="replace-and-print"><xsl:with-param name="content" select="@href"/></xsl:call-template>
	</xsl:variable>
	<xsl:apply-templates /> \footnote{Link: <xsl:value-of select="$href4latex"/> }
</xsl:template>

<!--###################################################################################################
 HANDLE TT:FORMAT TAGS
#######################################################################################################  -->
<xsl:template match="tt:italic">\textit{<xsl:apply-templates /> }</xsl:template>
<xsl:template match="tt:bold">\textbf{<xsl:apply-templates /> }</xsl:template>
<xsl:template match="tt:underlined">\underline{<xsl:apply-templates /> }</xsl:template>

<!--######################################################################################################
 HANDLE IMAGEs BY cTYPE (NON HTML IMG IMAGES)
###################################################################################################### -->
<xsl:template match="tt:image">
	<xsl:variable name="current-position" select="position()" />

	<!-- select all images on the same position of imagelists with the scope print and a valid href-->
	<xsl:variable name="print-exists" select="../../tt:imagelist[@scope = 'print']/tt:image[position() = $current-position and @href != '']"/>

	<!-- is the current node a image with the scope print and the href is not empty? -->
	<xsl:variable name="isprint" select="../@scope = 'print' and @href != ''"/>

	<!-- is the current node a image with the scope web and the href is not empty? -->
	<xsl:variable name="isweb" select="../@scope = 'web' and @href != ''"/>

	<xsl:if test="($isprint) or ($isweb and not($print-exists))">
			<xsl:variable name="res">
				<xsl:call-template name="replace">
					<xsl:with-param name="text" select="@href" />
					<xsl:with-param name="replace" select="'.eps'" />
					<xsl:with-param name="with" select="''" />
				</xsl:call-template>
			</xsl:variable>


			\begin{figure}[h]
			\centering

			<!-- Needed to scale images larger than the page-->
			\makeatletter
				\def\maxwidth{%
  					\ifdim\Gin@nat@width>\linewidth
   						 \linewidth
  					\else
    					\Gin@nat@width
  					\fi
				}
			\makeatother

			\includegraphics[width=0.35\maxwidth]{<xsl:value-of select="$res" />}

			<xsl:variable name="caption4latex">
				<xsl:call-template name="replace-and-print"><xsl:with-param name="content" select="@caption"/></xsl:call-template>
			</xsl:variable>

			<!-- If a caption has been set, display a caption tag! -->
			<xsl:if test="$caption4latex != ''">
				\caption[<xsl:value-of select="$caption4latex" />]{<xsl:value-of select="$caption4latex" />}
			</xsl:if>
			\end{figure}
	</xsl:if>
</xsl:template>

</xsl:stylesheet>
