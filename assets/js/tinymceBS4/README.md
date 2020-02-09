# TinyMce-Bootstrap-Simple-plugin
Bootstrap "Botton , Label ,Badge ,Alart".

### Base / Original
https://wordpress.org/plugins/tiny-bootstrap-elements-light/  
@version 1.1  
@author Gilles Migliori - gilles.migliori@gmail.com  


* ***********************************************************
## version 1.1_a
* Hide button of unused
* Bug FIX  etc 

## Install

1.Unzip  
2.Upload to TinyMCE plugin dir ,  tiny_bootstrap_elements_light   
3.Set  "tiny_bootstrap_elements_light"  tinymce.init  

    tinymce.init({

     plugins: [
      "････ tiny_bootstrap_elements_light" 
     ],
     toolbar: "････ tiny_bootstrap_elements_light" 
     });

4.as the need arises   
  In my case , Set ``` 'overwriteValidElements': false ```   
  
      tinymce.init({
         .....
        bootstrapLightConfig: {
        //'bootstrapLightCssPath': base_url + 'tinymce/plugins/tiny_bootstrap_elements_light/css/bootstrap.min.css',
        //'imagesPath': '/tinymce-bootstrap-plugin/img/',
        'overwriteValidElements': false
        },  
       


