<?php /* #?ini charset="utf-8"?

[DatabaseSettings]
Charset=utf8mb4

[FileSettings]
VarDir=var/site

[ExtensionSettings]
ActiveExtensions[]
ActiveExtensions[]=app
ActiveExtensions[]=ngsite
ActiveExtensions[]=sevenx_themes_simple
ActiveExtensions[]=xrowmetadata
ActiveExtensions[]=hcaptcha
ActiveExtensions[]=recaptcha
ActiveExtensions[]=eztags
ActiveExtensions[]=birthday
ActiveExtensions[]=ezoe
ActiveExtensions[]=bcwebsitestatistics
ActiveExtensions[]=bcgooglesitemaps
ActiveExtensions[]=ezjscore
ActiveExtensions[]=ezoe
ActiveExtensions[]=ezformtoken
ActiveExtensions[]=ezstarrating
ActiveExtensions[]=ezgmaplocation
ActiveExtensions[]=ezwebin
ActiveExtensions[]=ezwt
ActiveExtensions[]=ezflow
ActiveExtensions[]=ezie
ActiveExtensions[]=ezodf
ActiveExtensions[]=ezprestapiprovider
ActiveExtensions[]=ezmultiupload
ActiveExtensions[]=ezautosave
# ActiveExtensions[]=ezmbpaex

# Optional, see: https://packagist.org/packages/ezsystems/eztags-ls
ActiveExtensions[]=eztags

## Some recommended bundles/extensions for use with legacy bridge setups:
# Extra features to reuse code from Symfony in legacy: https://packagist.org/packages/netgen/ngsymfonytools
ActiveExtensions[]=ngsymfonytools

# Use SolrBundle from legacy: https://packagist.org/packages/netgen/ezplatformsearch
#ActiveExtensions[]=ezplatformsearch

# Edit eZ Platform richtext in raw xml on legacy: https://packagist.org/packages/netgen/richtext-datatype-bundle
ActiveExtensions[]=ezrichtext

[Session]
SessionNameHandler=custom

[SiteSettings]
DefaultAccess=legacy_site
SiteList[]
SiteList[]=site
SiteList[]=legacy_site
SiteList[]=legacy_admin
RootNodeDepth=1

[UserSettings]
LogoutRedirect=/

[SiteAccessSettings]
CheckValidity=false
AvailableSiteAccessList[]
AvailableSiteAccessList[]=site
AvailableSiteAccessList[]=legacy_site
AvailableSiteAccessList[]=legacy_admin
MatchOrder=uri
HostMatchMapItems[]

[RegionalSettings]
TranslationSA[]

[MailSettings]
Transport=sendmail
AdminEmail=
EmailSender=

[EmbedViewModeSettings]
AvailableViewModes[]
AvailableViewModes[]=embed
AvailableViewModes[]=embed-inline
InlineViewModes[]
InlineViewModes[]=embed-inline

# TIP: Below are settings that could make sense to invert for debug needs during legacy development.
# Especially [TemplateSettings]DevelopmentMode to not have to clear cache every time you change a template.

[DesignSettings]
DesignLocationCache=enabled

[DebugSettings]
DebugOutput=enabled
DebugRedirection=disabled

[TemplateSettings]
DevelopmentMode=disabled
ShowUsedTemplates=enabled
Debug=disabled

*/ ?>
