<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="text" omit-xml-declaration="yes"/>

    <xsl:template match="/">
        <xsl:apply-templates select="module/classes"/>
    </xsl:template>

    <xsl:template match="classes">
        <xsl:apply-templates select="class"/>
    </xsl:template>

    <xsl:template match="class">
        <xsl:apply-templates select="method"/>
    </xsl:template>

    <xsl:template match="method">
        <xsl:if test="php:function('SystemActions::getMethod',string(../@name),string(@name))!=0">
            "<xsl:value-of select="php:function('SystemActions::getMethod',string(../@name),string(@name))"/>"=>['module'=>'<xsl:value-of select="../../../@name"/>',<xsl:apply-templates select="@action"/>,<xsl:call-template name="props"/>,<xsl:apply-templates select="@group"/>],</xsl:if>
    </xsl:template>


    <xsl:template match="@action">'action'=>'<xsl:value-of select="php:function('SystemActions::ActionHandle',string(.))"/>'</xsl:template>
    <xsl:template match="@group">'group'=>'<xsl:value-of select="."/>'</xsl:template>

    <xsl:template name="props">'props'=>[<xsl:apply-templates select="../log_params/param"/>]</xsl:template>
    <xsl:template match="param">'<xsl:value-of select="."/>',</xsl:template>
</xsl:stylesheet>