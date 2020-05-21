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
    <xsl:param name="display_header" select="'yes'"/>
    <xsl:param name="display_personal" select="'yes'"/>
    <xsl:param name="display_education" select="'yes'"/>
    <xsl:param name="display_employment" select="'yes'"/>
    <xsl:param name="display_works" select="'yes'"/>
    <xsl:param name="display_fundings" select="'yes'"/>
    <xsl:param name="display_peer_reviews" select="'yes'"/>
    <!-- output format -->
    <xsl:output omit-xml-declaration="yes" indent="yes"/>

    <xsl:template match="/">

        <div id="orcid_data">
            <xsl:if test="$display_header='yes'">
                <div id="orcid_header">
                        <xsl:value-of
                                select="record:record/person:person/person:name/personal-details:given-names"/>
                        <xsl:text> </xsl:text>
                        <xsl:value-of
                                select="record:record/person:person/person:name/personal-details:family-name"/>
                        <xsl:text> </xsl:text>
                        ORCID Profile
                </div>
            </xsl:if>

            <!-- START: personal -->
            <xsl:if test="$display_personal='yes'">
                <div>Personal Information</div>
                <!-- name -->
                <div>
                    <xsl:if test="record:record/person:person/person:name">
                        <div>Name Information</div>
                        <table border="1">
                            <tr bgcolor="#9acd32">
                                <th>Field</th>
                                <th>Value</th>
                            </tr>
                            <tr>
                                <td>given-names</td>
                                <td>
                                    <xsl:value-of
                                            select="record:record/person:person/person:name/personal-details:given-names"/>
                                </td>
                            </tr>
                            <tr>
                                <td>family-name</td>
                                <td>
                                    <xsl:value-of
                                            select="record:record/person:person/person:name/personal-details:family-name"/>
                                </td>
                            </tr>
                            <tr>
                                <td>credit-name</td>
                                <td>
                                    <xsl:value-of
                                            select="record:record/person:person/person:name/personal-details:credit-name"/>
                                </td>
                            </tr>
                        </table>
                    </xsl:if>
                </div>

                <!-- biography -->
                <div>Biography</div>
                <div>
                    <blockquote>
                        <xsl:choose>
                            <xsl:when test="record:record/person:person/person:biography">
                                <xsl:value-of
                                        select="record:record/person:person/person:biography/personal-details:content"/>
                            </xsl:when>
                            <xsl:otherwise>No biography entered.</xsl:otherwise>
                        </xsl:choose>
                    </blockquote>
                </div>

                <!-- keywords -->
                <!--
                <div>Keywords</div>
                <div>
                    <table border="1">
                        <tr bgcolor="#9acd32">
                            <th>Keywords</th>
                        </tr>
                        <xsl:if test="record:record/person:person/keyword:keywords/keyword:keyword">
                            <xsl:for-each select="record:record/person:person/keyword:keywords/keyword:keyword">
                                <tr>
                                    <td>
                                        <xsl:value-of select="keyword:content"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </xsl:if>
                    </table>
                </div>
                -->

                <!-- URLs -->
                <!--
                <div>URLs</div>
                <div>
                    <table border="1">
                        <tr bgcolor="#9acd32">
                            <th>Name</th>
                            <th>URL</th>
                        </tr>
                        <xsl:if test="record:record/person:person/researcher-url:researcher-urls/researcher-url:researcher-url">
                            <xsl:for-each
                                    select="record:record/person:person/researcher-url:researcher-urls/researcher-url:researcher-url">
                                <tr>
                                    <td>
                                        <xsl:value-of select="researcher-url:url-name"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="researcher-url:url"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </xsl:if>
                    </table>
                </div>
                -->

                <!--
                <div>Skipping Section: other-name:other-names</div>
                <div>Skipping Section: external-identifier:external-identifiers</div>
                <div>Skipping Section: address:addresses</div>
                -->

            </xsl:if>
            <!-- END: personal -->

            <!-- START: education -->
            <xsl:if test="$display_education='yes'">
                <div>Education History</div>
                <div>
                    <table border="1">
                        <tr bgcolor="#9acd32">
                            <th>Department</th>
                            <th>Degree</th>
                            <th>Institution</th>
                            <!--
                            <th>City</th>
                            <th>Region</th>
                            <th>Country</th>
                            <th>Start Year</th>
                            <th>Start Month</th>
                            <th>Start Day</th>
                            <th>End Year</th>
                            <th>End Month</th>
                            <th>End Day</th>
                            -->
                        </tr>
                        <!-- if at least 1 "activities:educations/education:education-summary" exists -->
                        <xsl:if test="record:record/activities:activities-summary/activities:educations/education:education-summary">
                            <xsl:for-each
                                    select="record:record/activities:activities-summary/activities:educations/education:education-summary">
                                <tr>
                                    <td>
                                        <xsl:value-of select="education:department-name"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="education:role-title"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="education:organization/common:name"/>
                                    </td>
                                    <!--
                                    <td>
                                        <xsl:value-of select="education:organization/common:address/common:city"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="education:organization/common:address/common:region"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="education:organization/common:address/common:country"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:start-date/common:year"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:start-date/common:month"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:start-date/common:day"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:end-date/common:year"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:end-date/common:month"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:end-date/common:day"/>
                                    </td>
                                    -->
                                </tr>
                            </xsl:for-each>
                        </xsl:if>
                    </table>
                </div>
            </xsl:if>
            <!-- END: education -->

            <!-- START: employment -->
            <xsl:if test="$display_employment='yes'">
                <div>Employment History</div>
                <div>
                    <table border="1">
                        <tr bgcolor="#9acd32">
                            <th>Title</th>
                            <th>Institution</th>
                            <!--
                            <th>City</th>
                            <th>Region</th>
                            <th>Country</th>
                            <th>Start Year</th>
                            <th>Start Month</th>
                            <th>Start Day</th>
                            <th>End Year</th>
                            <th>End Month</th>
                            <th>End Day</th>
                            -->
                        </tr>
                        <!-- if at least 1 "activities:employments/employment:employment-summary" exists -->
                        <xsl:if test="record:record/activities:activities-summary/activities:employments/employment:employment-summary">
                            <xsl:for-each
                                    select="record:record/activities:activities-summary/activities:employments/employment:employment-summary">
                                <tr>
                                    <td>
                                        <xsl:value-of select="employment:role-title"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="employment:organization/common:name"/>
                                    </td>
                                    <!--
                                    <td>
                                        <xsl:value-of select="employment:organization/common:address/common:city"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="employment:organization/common:address/common:region"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="employment:organization/common:address/common:country"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:start-date/common:year"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:start-date/common:month"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:start-date/common:day"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:end-date/common:year"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:end-date/common:month"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="common:end-date/common:day"/>
                                    </td>
                                    -->
                                </tr>
                            </xsl:for-each>
                        </xsl:if>
                    </table>
                </div>
            </xsl:if>
            <!-- END: employmant -->

            <!-- START: works (activities-group) -->
            <xsl:if test="$display_works='yes'">
                <div>Academic Works History</div>
                <div>
                    <table border="1">
                        <tr bgcolor="#9acd32">
                            <th>Title</th>
                            <th>Type</th>
                            <th>Publication Year</th>
                            <!--
                            <th>Publication Month</th>
                            <th>Publication Day</th>
                            <th>External IDs</th>
                            -->
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
                                    <!--
                                    <td>
                                        <xsl:value-of select="work:work-summary/common:publication-date/common:month"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="work:work-summary/common:publication-date/common:day"/>
                                    </td>
                                    -->
                                    <!-- BEGIN: external ID table -->
                                    <!-- if at least 1 "common:external-ids/common:external-id" exists -->
                                    <!--
                                    <td>
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
                                    </td>
                                    -->
                                </tr>
                            </xsl:for-each>
                        </xsl:if>
                    </table>
                </div>
            </xsl:if>
            <!-- END: works -->

            <!-- START: fundings -->
            <xsl:if test="$display_fundings='yes'">
                <div>Funding Sources</div>
                <div>
                    <table border="1">
                        <tr bgcolor="#9acd32">
                            <th>Title</th>
                            <th>Type</th>
                            <th>Start Year</th>
                            <th>Start Month</th>
                            <th>Start Day</th>
                            <th>End Year</th>
                            <th>End Month</th>
                            <th>End Day</th>
                        </tr>
                        <xsl:if test="record:record/activities:activities-summary/activities:fundings/activities:group">
                            <xsl:for-each
                                    select="record:record/activities:activities-summary/activities:fundings/activities:group">
                                <tr>
                                    <td>
                                        <xsl:value-of select="funding:funding-summary/funding:title/common:title"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="funding:funding-summary/funding:type"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="funding:funding-summary/common:start-date/common:year"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="funding:funding-summary/common:start-date/common:month"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="funding:funding-summary/common:start-date/common:day"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="funding:funding-summary/common:end-date/common:year"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="funding:funding-summary/common:end-date/common:month"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="funding:funding-summary/common:end-date/common:day"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </xsl:if>
                    </table>
                </div>
            </xsl:if>
            <!-- END: fundings -->

            <!-- START: peer-reviews -->
            <xsl:if test="$display_peer_reviews='yes'">
                <div>Peer Review and Service Activity</div>
                <div>
                    <table border="1">
                        <tr bgcolor="#9acd32">
                            <th>Convening Organization Name</th>
                            <th>City</th>
                            <th>Region</th>
                            <th>Country</th>
                            <th>Completion Year</th>
                            <th>Completion Month</th>
                            <th>Completion Day</th>
                        </tr>
                        <xsl:if test="record:record/activities:activities-summary/activities:peer-reviews/activities:group">
                            <xsl:for-each
                                    select="record:record/activities:activities-summary/activities:peer-reviews/activities:group">
                                <tr>
                                    <td>
                                        <xsl:value-of
                                                select="peer-review:summary/peer-review:convening-organization/common:name"/>
                                    </td>
                                    <td>
                                        <xsl:value-of
                                                select="peer-review:summary/peer-review:convening-organization/common:address/common:city"/>
                                    </td>
                                    <td>
                                        <xsl:value-of
                                                select="peer-review:summary/peer-review:convening-organization/common:address/common:region"/>
                                    </td>
                                    <td>
                                        <xsl:value-of
                                                select="peer-review:summary/peer-review:convening-organization/common:address/common:country"/>
                                    </td>
                                    <td>
                                        <xsl:value-of
                                                select="peer-review:summary/peer-review:completion-date/common:year"/>
                                    </td>
                                    <td>
                                        <xsl:value-of
                                                select="peer-review:summary/peer-review:completion-date/common:month"/>
                                    </td>
                                    <td>
                                        <xsl:value-of
                                                select="peer-review:summary/peer-review:completion-date/common:day"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </xsl:if>
                    </table>
                </div>
            </xsl:if>
            <!-- END: peer-reviews -->

        </div>

    </xsl:template>

</xsl:stylesheet>
