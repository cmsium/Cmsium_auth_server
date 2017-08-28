<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" omit-xml-declaration="yes"/>

    <xsl:template match="/">
        <form action="/portfolio/create_event" method='POST' enctype="multipart/form-data" name='formname' id='form_id' class='form'>
            <h1 class='header'>Добавить </h1>
            <br />
            <xsl:apply-templates select="/event/item"/>
            <xsl:apply-templates select="/event/type_name"/>
            <label><input type="submit" value="Отправить" class="active" /></label>
        </form>
    </xsl:template>

    <xsl:template match="event/item">
        <xsl:value-of select="php:function('PortfolioDecorator::getReadable','event_type',string(../type_name),string(string(column_name)))"/>:
        <xsl:apply-templates select="column_type/name"/><br/>
    </xsl:template>

    <xsl:template match="column_type/name[text()='varchar']">
        <input type="text"><xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute></input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='int']">
        <input type="number"><xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute></input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='date']">
        <input type="date"><xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute></input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='tinyint']">
        <input type="checkbox" value="1"><xsl:attribute name="name" ><xsl:value-of select="../../column_name"/></xsl:attribute></input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='text']">
        <textarea>
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute>
            <xsl:value-of select="php:function('UsersXMLgenerator::getDocumentValue',string(../../column_name))" />
        </textarea>
    </xsl:template>

    <xsl:template match="column_type/name[text()='decimal']">
        <input type="number">
            <xsl:attribute name="value"><xsl:value-of select="php:function('UsersXMLgenerator::getDocumentValue',string(../../column_name))" /></xsl:attribute>
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute>
        </input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='file']">
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000"/>
        <input type="file"><xsl:attribute name="name" ><xsl:value-of select="../../column_name"/></xsl:attribute></input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='enum']">
        <select>
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute>
            <xsl:for-each select="../props/item">
                <option>
                    <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    <xsl:value-of select="."/>
                </option>
            </xsl:for-each>
        </select>
    </xsl:template>

    <xsl:template match="column_type/name[text()='set']">
        <select multiple="multiple">
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/>[]</xsl:attribute>
            <xsl:for-each select="../props/item">
                <option>
                    <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    <xsl:value-of select="."/>
                </option>
            </xsl:for-each>
        </select>
    </xsl:template>

    <xsl:template match="type_name">
        <input type="hidden" name="type_name"><xsl:attribute name="value">
            <xsl:value-of select="."/>
        </xsl:attribute></input>
    </xsl:template>
</xsl:stylesheet>