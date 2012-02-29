*******************************************
Please follow these instructions to properly
create a tranlsation package for Tienda.
If you don't follow these instructions carefully
your translation package may not function 
properly, and will not be accepted to be shared
with the community.
********************************************

NOTE: Be sure to edit all language files in a proper 'code' text editor that supports UTF-8.
And do NOT save any formatting.
You can NOT edit these in most standard word-processors!

Here are the steps to follow to create your translation package.


1. Download a current copy of the BaseLanguagePackage-##.##.##.zip file from the downloads area.
NOTE: The ##.##.## indicate the version of Tienda which the baseLanguagePackage was created for. Do NOT use an old version.

2. Unzip the package file on your local hard drive

3. Open EACH of the individual .ini files and translate them.
A complete translation MUST have ALL of the files translated (that includes the files in the 'site' AND 'admin' sub folders.)
NOTE: Language files are included for ALL current Tienda extensions provided by Dioscouri. This includes extensions that are only available in the marketplace (i.e. not in the core download). These files should be translated also.

4. When editing the individual .ini files, be aware of certain rules:
	a. Do NOT edit/change the text to the LEFT of the "=" (equal sign)
	b. Do NOT add new entries (these will be removed in future updates)
		- if you notice missing language strings, please report them in the projects area as a bug
		- if you have need of custom fields for your personal configuration/modifications, please use the provided custom language option to prevent from having these changed during upgrades.
	c. Be aware of "%s" entries in translation strings, these provide 'data replacement' (dynamic content). If you remove the "%s" code you will not see the custom data
	
5. Do NOT change the copyright (or other info) at the top of the .ini file. All language files remain copyright of Dioscouri.

6. When you are done translating all the files just save them back in the same folder structure originally provided.

7. Updating the manifext.xml file
	To complete your translation package (and make it able to be installed) you must update the manifest XML file. You will need to make these changes:
	a. Line 3 - Change the name from "BASE Tienda" to "XXXXX Tienda" (replacing XXXXX with the name of the language into which you are translting)
	b. Line 4 - Change "<tag>xx-XX</tag>" by replacing the xx-XX with the proper language indicators
		NOTE: If you are creating a translation you should already have a Joomla system translation for your language installed. You should use the same 'xx-XX' identifier for your Tienda translation files as is used for your core Joomla language files.
			If for some reason you wish to look-up the various options, you can find a great list of language options here: http://www.i18nguy.com/unicode/language-identifiers.html
	c. Line 6 - Update the "<creationDate>March 2012</creationDate>" to reference the date when you completed your language translations.
	d. Line 7 - Update the "<author>Dioscouri Staff</author>" line, replacing 'Dioscouri Staff' with your name (please use first/last name)
	e. Line 8 - Update the "<authorEmail>info@dioscouri.com</authorEmail>" line to list YOUR email address (where other users can contact you with questions about your translation)
	f. Line 9 - Update the "<authorUrl>www.dioscouri.com</authorUrl>" line to list YOUR web address (where users can find updates to the translation - or just list "dioscouri.com")
	g. Line 10-12 - DO NOT update these lines (Copyright, CopyrightHolder, License). They should always remain as provided.
	h. Line 13 - Update the description to reflect the language of this package (note: this should be entered in the native langauge - not English)
	i. Replace (search and replace) all instances of xx-XX with your language identifiers
	
8. Rename all language files.
You will need to change the "en-GB" at the beginning of each language file to the proper language identifier for your translation.
NOTE: The 'Bulk Rename Utility' can be very helpful in this process: http://www.bulkrenameutility.co.uk/
	
9. Compress (zip) up all of the package files, maintaining the folder structure (and including the xml file)
You can delete this readme file before zipping the file.

10. Test the language installation file.
Try installing the language package on your site (you'll need to have the target language installed in Joomla for it to be functional)

11. IF the test works, create a ticket on projects.dioscouri.com or dioscouri.com for your language file and attach your zipped file to the ticket.
Be sure to clearly indicate the language in your ticket summary.	 

If you notice ANY problems in this process (or in these instructions) please create a bug report on the projects.dioscouri.com site.
