This file contains instructions to localize BibORB.

Gettext methods are used to provide the localization of biborb.
See http://www.gnu.org/software/gettext/manual/gettext.html for more details.

The 'locale' directory contains localized data used to display messages in a 
selected language. By default, english is used ('en_US' directory) if data are
missing for a given language.

If you want to add the support for a language:

    1) Copy the 'en_US' directory and rename it into the name of your
locale. Its name must be of type 'lg_CO' where:
     * lg is the ISO 639 standard definition for your language (see 
http://www.gnu.org/software/gettext/manual/html_chapter/gettext_15.html#SEC221 
for a list)
     * CO is the ISO 3166 standard definition for your country (see
http://www.gnu.org/software/gettext/manual/html_chapter/gettext_16.html#SEC222
for a list)
            
    2) Translate each .txt files.
    
    3) Edit the 'biborb.po' file and translate each string starting with 
'msgstr'
    For instance:
        Original File:
            msgid "Update"
            msgstr ""
        Localized File:
            msgid "Update"
            msgstr "Mettre à jour"
            
    4) Compile the biborb.po file.
        msgfmt -o biborb.mo biborb.po
    
    5) Edit the 'config.php' file and set the $language variable to the name of 
your locale (if you want it to be selected by default) or select it within 
biborb if you have set $display_language_selection to TRUE.
    
    6) If the localization doesn't show, restart the web server to force it to 
reload the localization data.

    7) If everything is OK, you could send me an archive of your locale
directory, I will add it to future releases of BibORB :).

