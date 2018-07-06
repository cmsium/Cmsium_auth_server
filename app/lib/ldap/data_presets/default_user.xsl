<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/root">
        <default_user>
            <cn><xsl:value-of select="item0"/></cn>
            <objectclass>inetOrgPerson</objectclass>
            <objectclass>organizationalPerson</objectclass>
            <objectclass>person</objectclass>
            <objectclass>posixAccount</objectclass>
            <objectclass>top</objectclass>
            <gidNumber>42</gidNumber>
            <homeDirectory>/<xsl:value-of select="item0"/></homeDirectory>
            <sn><xsl:value-of select="item1"/></sn>
            <uid><xsl:value-of select="item2"/></uid>
            <uidNumber><xsl:value-of select="item3"/></uidNumber>
            <givenName><xsl:value-of select="item4"/></givenName>
            <mail><xsl:value-of select="item5"/></mail>
            <userPassword><xsl:value-of select="item6"/></userPassword>
        </default_user>
    </xsl:template>

</xsl:stylesheet>