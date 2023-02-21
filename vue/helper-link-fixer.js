/**
 * This script should be executed in order to fix output css links (it changes 'assets/' to 'wp-content/plugins/cv-generator/dist-vue/assets\/'
 * which fixes PrimeVue icon and font problems.
 */

var fs = require('fs')
fs.readFile("../dist-vue/assets/index.css", 'utf8', function (err,data) {
  if (err) {
    return console.log(err);
  }
  var result = data.replace(/assets\//g, 'wp-content/plugins/cv-generator/dist-vue/assets\/');

  fs.writeFile("../dist-vue/assets/index.css", result, 'utf8', function (err) {
    if (err) return console.log(err);
  });

  console.log(" FINISHED HELPER-LINK-FIXER ")
});
