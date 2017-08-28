<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" omit-xml-declaration="yes"/>

    <xsl:template match="/">
        <form action="/users/dashboard/update/props" method='POST' enctype="multipart/form-data" name='formname' id='form_id' class='form'>
            <h1 class='header'>Редактировать информацию о себе</h1>
            <br />
            <xsl:apply-templates select="/users/item"/>
            <xsl:apply-templates select="/users/role_name"/>
            <xsl:apply-templates select="/users/user_id"/>
            <label><input type="submit" value="Отправить" class="active" /></label>
        </form>
    </xsl:template>

    <xsl:template match="users/item">
        <xsl:value-of select="t_column_name"/>:
        <xsl:choose>
            <xsl:when test="column_type/reference='userfiles'">
                <xsl:call-template name="files_id_" />
            </xsl:when>
            <xsl:when test="column_type/reference='users'">
                <xsl:call-template name="bus_tickets_id_" />
            </xsl:when>
            <xsl:when test="column_type/reference='address_object'">
                <xsl:call-template name="address_object_id_" />
            </xsl:when>
            <xsl:when test="column_type/reference='full_address_object'">
                <xsl:call-template name="full_address_object_id_" />
            </xsl:when>
            <xsl:when test="column_type/reference='address_country'">
                <xsl:call-template name="address_country_" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="column_type/name"/>
            </xsl:otherwise>
        </xsl:choose><br/>
    </xsl:template>


    <xsl:template match="column_type/name[text()='varchar']">
        <input type="text">
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(../../column_name))"/></xsl:attribute>
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute>
        </input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='int']">
        <input type="number">
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(../../column_name))"/></xsl:attribute>
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute>
        </input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='date']">
        <input type="date">
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(../../column_name))"/></xsl:attribute>
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute>
        </input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='text']">
        <textarea>
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute>
            <xsl:value-of select="php:function('User::getPropsValue',string(../../column_name))" />
        </textarea>
    </xsl:template>

    <xsl:template match="column_type/name[text()='decimal']">
        <input type="number">
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(../../column_name))" /></xsl:attribute>
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute>
        </input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='boolean']">
        <input type="checkbox" value="1">
            <xsl:if test="php:function('User::getPropsValue',string(../../column_name))=1">
                <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            <xsl:attribute name="name" ><xsl:value-of select="../../column_name"/></xsl:attribute>
        </input>
    </xsl:template>

    <xsl:template match="column_type/name[text()='enum']">
        <select>
            <xsl:attribute name="name"><xsl:value-of select="../../column_name"/></xsl:attribute>
            <xsl:for-each select="../props/item">
                <option>
                    <xsl:if test="php:function('User::checkPropsValue',string(../../../column_name),string(.))!=0">
                        <xsl:attribute name="selected">selected</xsl:attribute>
                    </xsl:if>
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
                    <xsl:if test="php:function('User::checkSetPropsValue',string(../../../column_name),string(.))!=0">
                        <xsl:attribute name="selected">selected</xsl:attribute>
                    </xsl:if>
                    <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    <xsl:value-of select="."/>
                </option>
            </xsl:for-each>
        </select>
    </xsl:template>

    <xsl:template name="files_id_">
        <input type="hidden" name="MAX_FILE_SIZE" value="100000000"/>
        <input type="file"><xsl:attribute name="name" ><xsl:value-of select="column_name"/></xsl:attribute></input>
    </xsl:template>

    <xsl:template name="address_object_id_">
        <input id="address_field_hidden" type="text" style="display: none;">
            <xsl:attribute name="name"><xsl:value-of select="column_name"/>[mask]</xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(column_name),string('mask'))" /></xsl:attribute>
        </input>
        <input id="address_field" type="text">
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(column_name),string('value'))" /></xsl:attribute>
            <xsl:attribute name="name"><xsl:value-of select="column_name"/>[value]</xsl:attribute>
        </input>
    </xsl:template>

    <xsl:template name="full_address_object_id_">
        <input id="address_field_hidden" type="text" style="display: none;">
            <xsl:attribute name="name"><xsl:value-of select="column_name"/>[mask]</xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(column_name),string('mask'))" /></xsl:attribute>
        </input>
        <input id="address_field" type="text" placeholder="Город">
            <xsl:attribute name="name"><xsl:value-of select="column_name"/>[value][city]</xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(column_name),string('value'),string('city'))" /></xsl:attribute>
        </input>
        <input id="street_address_field" type="text" placeholder="Улица">
            <xsl:attribute name="name"><xsl:value-of select="column_name"/>[value][street]</xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(column_name),string('value'),string('street'))" /></xsl:attribute>
        </input>
        <input id="user_address_field" type="text" placeholder="Дом, квартира">
            <xsl:attribute name="name"><xsl:value-of select="column_name"/>[value][user]</xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="php:function('User::getPropsValue',string(column_name),string('value'),string('user'))" /></xsl:attribute>
        </input>
    </xsl:template>

    <xsl:template name="bus_tickets_id_">
        <select id="user_select">
            <xsl:attribute name="name"><xsl:value-of select="column_name"/></xsl:attribute>
            <xsl:for-each select="php:function('UsersXMLGenerator::getUsersNodeSet',string(column_name))">
                <xsl:apply-templates select="@*|node()" />
            </xsl:for-each>
        </select>
    </xsl:template>

    <xsl:template match="users">
        <xsl:apply-templates select="user"/>
    </xsl:template>

    <xsl:template match="user">
        <option>
            <xsl:if test="php:function('User::getPropsValue',string(./user_column_name))=./id">
                <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <xsl:attribute name="value"><xsl:value-of select="./id"/></xsl:attribute>
            <xsl:value-of select="./username"/>:&#160;<xsl:value-of select="./full_name"/>
        </option>
    </xsl:template>

    <xsl:template name="address_country_">
        <select id="country_select">
            <xsl:attribute name="name"><xsl:value-of select="column_name"/></xsl:attribute>
            <xsl:for-each select="php:function('UsersXMLGenerator::getCountriesNodeSet',string(column_name))">
                <xsl:apply-templates select="@*|node()" />
            </xsl:for-each>
        </select>
    </xsl:template>

    <xsl:template match="countries">
        <xsl:apply-templates select="country"/>
    </xsl:template>

    <xsl:template match="country">
        <option>
            <xsl:if test="php:function('User::getPropsValue',string(./country_column_name))=./iso">
                <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <xsl:attribute name="value"><xsl:value-of select="./iso"/></xsl:attribute>
            <xsl:value-of select="./name"/>
        </option>
    </xsl:template>


    <xsl:template match="role_name">
        <input type="hidden" name="role"><xsl:attribute name="value">
            <xsl:value-of select="."/>
        </xsl:attribute></input>
    </xsl:template>

    <xsl:template match="user_id">
        <input type="hidden" name="user_id"><xsl:attribute name="value">
            <xsl:value-of select="."/>
        </xsl:attribute></input>
    </xsl:template>
</xsl:stylesheet>