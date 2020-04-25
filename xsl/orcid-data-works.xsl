<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:internal="http://www.orcid.org/ns/internal"
                xmlns:funding="http://www.orcid.org/ns/funding"
                xmlns:preferences="http://www.orcid.org/ns/preferences"
                xmlns:address="http://www.orcid.org/ns/address"
                xmlns:education="http://www.orcid.org/ns/education"
                xmlns:work="http://www.orcid.org/ns/work"
                xmlns:deprecated="http://www.orcid.org/ns/deprecated"
                xmlns:other-name="http://www.orcid.org/ns/other-name"
                xmlns:history="http://www.orcid.org/ns/history"
                xmlns:employment="http://www.orcid.org/ns/employment"
                xmlns:error="http://www.orcid.org/ns/error"
                xmlns:common="http://www.orcid.org/ns/common"
                xmlns:person="http://www.orcid.org/ns/person"
                xmlns:activities="http://www.orcid.org/ns/activities"
                xmlns:record="http://www.orcid.org/ns/record"
                xmlns:researcher-url="http://www.orcid.org/ns/researcher-url"
                xmlns:peer-review="http://www.orcid.org/ns/peer-review"
                xmlns:personal-details="http://www.orcid.org/ns/personal-details"
                xmlns:bulk="http://www.orcid.org/ns/bulk"
                xmlns:keyword="http://www.orcid.org/ns/keyword"
                xmlns:email="http://www.orcid.org/ns/email"
                xmlns:external-identifier="http://www.orcid.org/ns/external-identifier"
                version="1.0">

    <!-- parameters -->
    <!-- NOTE: parameter values must be quoted if you want strings (and not XPath entries) -->
    <xsl:param name="display-personal" select="'yes'"/>
    <xsl:param name="display-education" select="'yes'"/>
    <xsl:param name="display-employment" select="'yes'"/>
    <xsl:param name="display-works" select="'yes'"/>
    <xsl:param name="fundings" select="'yes'"/>
    <xsl:param name="peer-reviews" select="'yes'"/>
    <!-- output format -->
    <xsl:output omit-xml-declaration="yes" indent="yes"/>

    <xsl:template match="/">
        <!-- name -->
        <h2>
            <xsl:value-of
                    select="record:record/person:person/person:name/personal-details:given-names"/>
            <xsl:text> </xsl:text>
            <xsl:value-of
                    select="record:record/person:person/person:name/personal-details:family-name"/>
        </h2>

        <!-- START: works (activities-group) -->
        <h2>ORCID Academic Works History</h2>
        <table border="1">
            <tr bgcolor="#9acd32">
                <th>Title</th>
                <th>Type</th>
                <th>Publication Year</th>
                <th>External IDs</th>
            </tr>
            <!-- if at least 1 "activities:works/activities:group" exists -->
            <xsl:if test="record:record/activities:activities-summary/activities:works/activities:group">
                <xsl:for-each
                        select="record:record/activities:activities-summary/activities:works/activities:group">
                    <tr>
                        <td>
                            <xsl:value-of select="work:work-summary/work:title/common:title"/>
                        </td>
                        <td>
                            <xsl:value-of select="work:work-summary/work:type"/>
                        </td>
                        <td>
                            <xsl:value-of select="work:work-summary/common:publication-date/common:year"/>
                        </td>
                        <td>
                            <!-- BEGIN: external ID table -->
                            <!-- if at least 1 "common:external-ids/common:external-id" exists -->
                            <xsl:if test="work:work-summary/common:external-ids/common:external-id">
                                <table border="1">
                                    <tr>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>URL</th>
                                    </tr>
                                    <xsl:for-each
                                            select="work:work-summary/common:external-ids/common:external-id">
                                        <tr>
                                            <td>
                                                <xsl:value-of select="common:external-id-type"/>
                                            </td>
                                            <td>
                                                <xsl:value-of select="common:external-id-value"/>
                                            </td>
                                            <td>
                                                <xsl:value-of select="common:external-id-url"/>
                                            </td>
                                        </tr>
                                    </xsl:for-each>
                                </table>
                            </xsl:if>
                            <!-- END external ID table -->
                        </td>
                    </tr>
                </xsl:for-each>
            </xsl:if>
        </table>
        <!-- END: works -->
    </xsl:template>
</xsl:stylesheet>
