# CV Generator plugin

This plugin has 2 parts:
* Authentification 
* CV Generator 

Both of these should be translatable.
There should be language selector available (if at least 2 languages are enabled from admin panel)

# Authentification
* This is a plugin that allows to authenticate users with email not using password. Instead is used one time password (OTP) which is sent to email.

# CV Generator
* This plugin is using Vue in frontend. 
* It uses Imagick (PDF policy should be enabled, that can be done in going to /etc/imagick policy.xml and allowing it there)
* It uses FFmpeg for video processing and thumbnail creating
* It has settings page in admin panel
* It has Stripe integration for payments. All important information is in the settings page.

# Vue
To rerender the Vue (Vue 3 is used) part (after updates)
you should  
1. have npm available
2. move to wp-content/plugins/cvgenerator/vue
3. make sure all node packages are installed
4. ``npm run build`` or ``npm run build watch``
5. upload dist-vue folder to the server

**Note about Vue**. There is some problem with some links; therefore, in package.json is set fix-links script as well. 
The problem is that the icons would not load correctly. The script for fix is in file ``helper-link-fixer.js``.

# Translations
Translations are saved in user meta data if the user is logged in or in the cookies.
Translation strings are defined in php and passed through DOM (div) attributes in JSON format to javascript where it is parsed and used.  