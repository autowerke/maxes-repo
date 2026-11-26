(function($){
    $.fn.filetree = function(method){
       
        var settings = { // settings to expose
            animationSpeed      : 'fast',            
            collapsed           : true,
            console             : false
        }
        var methods = {
            init : function(options){
                // Get standard settings and merge with passed in values
                var options = $.extend(settings, options); 
                // Do this for every file tree found in the document
                return this.each(function(){
                    
                    var $fileList = $(this);
                   
                    $fileList
                        .addClass('file-list')
                        .find('li')
                        .has('ul') // Any li that has a list inside is a folder root
                            .addClass('folder-root closed')
                            .on('click', 'a:not([data-drilldown])', function(e){ // Add a click override for the folder root links
                                e.preventDefault();
                                $(this).parent().toggleClass('closed').toggleClass('open');
                                return false;
                            });
                    
                    //alert(options.animationSpeed); Are the settings coming in
					
					//maxes add ons
					
					$fileList.on('click', 'a[data-drilldown]', function(){
						 var attr = $(this).attr("data-drilldown"),
							 keynum = $(this).attr("data-keynum"),
							 apps = xboset.getThisXboSet();
							 xboset.showDetail(attr); 
							 try{
								 new maxes.drilldown(attr, keynum).action();
							 }catch(e){}
						
						
					 })
                });
                
                
            }
        }
        
        

        
        if (typeof method === 'object' || !method){
            return methods.init.apply(this, arguments);
        } else {
            $.on( "error", function(){
                console.log(method + " does not exist in the file exploerer plugin");
            } );
        }  
    }
    
}(jQuery));