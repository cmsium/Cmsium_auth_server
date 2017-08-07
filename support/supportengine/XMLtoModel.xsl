<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="text" omit-xml-declaration="yes"/>
    <xsl:template match="/">
        <xsl:apply-templates select="urls/url"/>
    </xsl:template>
    <xsl:template match="url[@value='']">
        '<xsl:value-of select="@value" />' => [<xsl:apply-templates select="properties/model"/><xsl:apply-templates select="properties/template"/><xsl:apply-templates select="properties/view"/><xsl:apply-templates select="properties/file"/>"parent" => '0',"modules" => <xsl:value-of select="properties/modules" />,<xsl:apply-templates select="properties/auth"/>"callback" => <xsl:value-of select="properties/callback" />],
        <xsl:apply-templates select="childs/url"/></xsl:template>
    <xsl:template match="url">'<xsl:value-of select="@value" />' => [<xsl:apply-templates select="properties/model"/><xsl:apply-templates select="properties/template"/><xsl:apply-templates select="properties/view"/><xsl:apply-templates select="properties/file"/>"parent" => '<xsl:value-of select="../../@value" />',"modules" => <xsl:value-of select="properties/modules" />,<xsl:apply-templates select="properties/action"/><xsl:apply-templates select="properties/log"/><xsl:apply-templates select="properties/auth"/>"callback" => <xsl:value-of select="properties/callback" />],
        <xsl:apply-templates select="childs/url"/></xsl:template>
    <xsl:template match="auth">
        <xml:text>"auth" => [],</xml:text>
    </xsl:template>
    <xsl:template match="log">
        <xml:text>"log" => [],</xml:text>
    </xsl:template>
    <xsl:template match="action">"action" => '<xsl:value-of select="php:function('SystemActions::ActionHandle',string(.))"/>',</xsl:template>
    <xsl:template match="file">"file" => [<xsl:apply-templates select="path"/><xsl:apply-templates select="method"/>],</xsl:template>
    <xsl:template match="model">"model" => <xsl:value-of select="." />,</xsl:template>
    <xsl:template match="template">"template" => <xsl:value-of select="." />,</xsl:template>
    <xsl:template match="view">"view" => <xsl:value-of select="." />,</xsl:template>
    <xsl:template match="path">"path" => <xsl:value-of select="." />,</xsl:template>
    <xsl:template match="method">"method" => <xsl:value-of select="." />,</xsl:template>
</xsl:stylesheet>