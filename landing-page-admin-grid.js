/* made change again */
jQuery(document).ready(function($){
    
    
    var getFormValue = function(target, selector){
        return $(target).parents("form").find(selector).val();
    }
    // on mouseover, trigger js bindings.
    // so that grid will function after ajax reload
    
    // add a new cell.
    $( "#landing-page-details-meta-box").on("click", "button#add-cell", function(e){
        
        e.preventDefault();
        var cell = makeCellFrame();
        var dept = getFormValue(e.target, ".department");
        var form = makeStandardForm(dept)
        
        cell.append(form);
        
        var grid = $(e.target).parents("#landing-page-details-meta-box").find("#grid");
        grid.append(cell);
        
        cell.find("#cell-type").trigger("change");
        // activate save button
        
    });
    
    
    $("#landing-page-details-meta-box").on("change",".feature-designation", function(e){
        
            var category_select_id = "feature-category";
            var category_select_name = "feature-category";

            
            switch($(e.target).val()){
                case "department_post":
                    $(e.target).siblings(".feature-category").remove();
                    break;
                case "category":
                    $(e.target).siblings(".feature-category").remove();
                    break;
                case "series":
                    $(e.target).siblings(".feature-category").remove();
                    var categories = makeCategorySelect($(e.target).parents("form").find(".department").val(),$(e.target).val(), category_select_id, category_select_name, "widefat feature-category");
                    $(e.target).after(categories);
                    break;
                default:
                    break;
            }
            
    });
    
    $("#landing-page-details-meta-box").on("change",".page_type",function(e){
        
        switch($(e.target).val()){
                case "category_landing":
                    
                    var category_select_id = "page-category";
                    var category_select_name = "page-category";
                    
                    $(e.target).siblings(".page-category").remove();
                    var categories = makeCategorySelect($(e.target).parents("form").find(".department").val(),"category", category_select_id, category_select_name, "widefat page-category");
                    $(e.target).after(categories);
                    break;
                default:
                    $(e.target).siblings(".page-category").remove();
                    break;
                
            }
            
                  
    });
    

    
    var bindings = function(grid){
        
       
        //sortable
        if(!$(grid).hasClass('ui-sortable')){
            $(grid).sortable({
                placeholder: "ui-state-highlight",
                });
            $(grid).disableSelection();
        }
         
        //cell size change
        $( grid ).find( "li select#cell-type").off("change");
        
        // set previous cell type to handle the changing of cell types properly.
        var previous_cell_type;
        $(grid).on("focus", "li select#cell-type",function(e){
            previous_cell_type = $(this).val();
        });
        
        $( grid ).on("change", "li select#cell-type", function(e){

            var cell = $(this).parents("li");
            clearCellType(cell);
            
            // set cell css type
            cell.addClass("cell_"+$(this).val());
            
            // if nothing's been selected, assume it's one of the standard forms.
            if (previous_cell_type === undefined) {
                previous_cell_type = "x";  // HACK!!!!  this stuff needs refactor 100%
            }
            
                        
            // if going from special to standard
            if ($(this).val().indexOf("x")>-1 && previous_cell_type.indexOf("x")==-1){
                
                $(this).siblings().remove();
                
                var designation_select = makeDesignationSelect();
                var taxonomy_select = makeTaxonomySelect();
                var category_select = makeCategorySelect("general","category", "cell-category", "cell-category");
                var header_input = makeInput("hidden", "cell-header", "cell-header", "","","");
                var subheader_input = makeInput("hidden","cell-subheader","cell-subheader","","","");
                
                
                var image1 = makeInput( "hidden", "image1", "image1", "n/a");
                var image2 = makeInput( "hidden", "image2", "image2", "n/a");
                var image3 = makeInput( "hidden", "image3", "image3", "n/a" );
        
                var link1 = makeInput( "hidden", "link1", "link", "n/a");
                var link2 = makeInput( "hidden", "link2", "link", "n/a");
                var link3 = makeInput( "hidden", "link3 ", "link", "n/a");
                
                var description = makeInput("hidden","description","cell-description","");
        
                $(this).parent().append(designation_select) 
                                .append(taxonomy_select)
                                .append(category_select)
                                .append(header_input)
                                .append(subheader_input)
                                .append(image1)
                                .append(image2)
                                .append(image3)
                                .append(link1)
                                .append(link2)
                                .append(link3)
                                .append(description);
            }
            
            // going to artist
            if ($(this).val().indexOf("artist")>-1 && previous_cell_type.indexOf("artist")==-1){
        
                $(this).siblings("input").remove();
                $(this).siblings("select").remove();
                $(this).siblings("div.label-input").remove();
                
                var designation = $("<input></input").attr("type","hidden").attr("id","designation").attr("name","designation[]").val("n/a");
                var taxonomy = $("<input></input").attr("type","hidden").attr("id","taxonomy").attr("name","cell-taxonomy[]").val("artist");
                var category = $("<input></input").attr("type","hidden").attr("id","cell-category").attr("name","cell-category[]").val("n/a");
                var header_input = makeInput("hidden", "cell-header", "cell-header", "");
                var subheader_input = makeInput("hidden","cell-subheader","cell-subheader","");
                
                $(this).parent().append(designation).append(taxonomy).append(category);
                
            }
            
            if ($(this).val().indexOf("series")>-1 && previous_cell_type.indexOf("series")==-1){
                
                
                $(this).siblings().remove();
                
                makeSeriesCellForm($(this).parent());
                
            }
            
            if ($(this).val() == 'homepage-feature' & previous_cell_type != "homepage-feature" ){
                
                $(this).siblings().remove();
                
                var designation = makeInput("hidden", "designation", "designation","n/a","");
                var taxonomy    = makeInput("hidden","taxonomy","cell-taxonomy","n/a");
                var category    = makeInput("hidden","cell-category","cell-category","n/a");
                
                // create homepage feature form
                var headers_div = makeDiv("headersubheader","row");
                var header_input = makeInput("text","cell-header","cell-header","","Header","homepage-feature-input");
                var subheader_input = makeInput("text","cell-subheader","cell-subheader","","Subheader");
                headers_div.append(header_input).append(subheader_input);
                
                var images_div = makeDiv("images","row");
                var image1 = makeInput("text","image1","image1","","image 1 id",'image-link');
                var image2 = makeInput("text","image2","image2","","image 2 id",'image-link');
                var image3 = makeInput("text","image3","image3","","image 3 id",'image-link');
                images_div.append(image1).append(image2).append(image3);
                
                var links_div = makeDiv("links", "row");
                var link1 = makeInput("text","link1","link1","","link 1",'image-link');
                var link2 = makeInput("text","link2","link2","","link 2",'image-link');
                var link3 = makeInput("text","link3","link3","","link 3",'image-link');
                links_div.append(link1).append(link2).append(link3);
                
                var description_div = makeDiv("description","row");
                var description = makeTextarea("cell-description","cell-description","Description");
                description_div.append(description);
                
                $(this).parent().append(designation)
                                .append(taxonomy)
                                .append(category)
                                .append(headers_div)
                                .append(images_div)
                                .append(links_div)
                                .append(description_div);
            }
            
            previous_cell_type = $(this).val();
            return;
        });
        
        //cell designation
        $( grid ).find( "li select#cell-designation").off("change");
        $( grid ).on("change", "li select#cell-designation", function(e){
            
            tax_select = $(this).siblings("#cell-taxonomy");
            
            if ($(this).val()=="general") {
                tax_select.find("option[value=series]").remove();
            }
            else{
                tax_select.find("option[value=series]").remove();
                option = $("<option></option>").val('series').text("Series");
                tax_select.append(option);
            }
            if($(this).siblings('#category').length){
                if($(this).siblings('#cell-taxonomy').val() == 'series'){
                    series_select = makeCategorySelect($(this).val(),'series');
                    $(this).siblings("#category").remove();
                    $(this).parent().append(series_select);
                } 
            }
            //$(this).siblings("#category").remove();
        });
        
        /* cell taxonomy bindings*/
        $( grid ).find( "li select#cell-taxonomy").off("change");
        $( grid ).on("change", "li select#cell-taxonomy", function(e){
            switch ($(this).val()) {
                case "category":
                    select = makeCategorySelect($(this).siblings("#cell-designation").val(), $(this).val());
                    $(this).siblings("#category").replaceWith(select);
                    break;
                case "post_tag":
                    input = $("<input />").attr("id","category").attr("name","cell-category[]").attr("placeholder","Enter Tag");
                    $(this).siblings("#category").replaceWith(input);
                    break;
                
                case "department_latest":
                    input = $("<input />").attr("type","hidden").attr("id","category").attr("name","cell-category[]").val("n/a");
                    $(this).siblings("#category").replaceWith(input);
                    break;
                
                case "series":
                    select = makeCategorySelect($(this).siblings("#cell-designation").val(), $(this).val());
                    $(this).siblings("#category").replaceWith(select);
                    break;
            }
            
            //$(this).siblings("#category").remove();
        });
        
        
        // delete button
        $( grid ).on("click","li div.delete-cell",function(e){
            var form = $(this).parents("form");
            $(this).parents("li").remove();
            form.find("input").trigger("change");
        });
        
        $( grid ).on("click","button.add-image", function(e){
            e.preventDefault();
            var target = $("#" + $(this).data('for') );
            
            var button = $(this),
                btrtoday_uploader = wp.media({
                    title: 'Custom image',
                    library : {
                        type : 'image'
                    },
                    button: {
                        text: 'select'
                    },
                    multiple: false
                }).on('select', function() {
                    var attachment = btrtoday_uploader.state().get('selection').first().toJSON();
                    target.val(attachment.id);
                })
        .open();
        
            
        });
        $(grid).addClass("bound");   
    }
    
    var grid = $("#grid");
    bindings(grid);
    
    var makeCategorySelect = function(dept,taxonomy="category",id="category", name="cell-category[]", css_class=""){
        
        switch(taxonomy){
            case "category":
                options = [];
                
                    $.each(categories.categories,function(key, value){
                        var name = value.name;
                        if (value.parent>0) {
                            name = " - " + name;
                        }
                        options[key] = {value:value.slug, text:name} 
                    })
                
                    
                break;
            case "series":
                    switch (dept) {
                        case "listen":
                            series = categories.series.listen;
                            break;
                        case "read":
                            series = categories.series.read;
                            break;
                        case "tv":
                            series = categories.series.tv;
                            break;
                    }
                    options = [];
                    $.each(series,function(key, value){
                        options[key] = {value:value.slug, text:value.name} 
                    });
                break;
            default:
                break;
        }
        return makeSelect(id,name,options,css_class);
    }
    
    var makeDesignationSelect = function(celltype="normal"){
        if (celltype=="normal") {
            var options=[{value:"general",text:"General"},{value:"listen",text:"Listen"},{value:"read",text:"Read"},{value:"tv",text:"Watch"}]
            return makeSelect("cell-designation","cell-designation[]",options);
        }
        else{
            switch (celltype) {
                case "series":
                    var options=[{value:"listen",text:"Listen"},{value:"read",text:"Read"},{value:"tv",text:"Watch"}]
                    return makeSelect("cell-designation","cell-designation[]",options);
                    break;
            }
        }
        
        
    }
    
    var makeTaxonomySelect = function(celltype="normal"){

        var options=[{value:"category",text:"Category"},{value:"post_tag",text:"Tag"},{value:"department_latest",text:"Department Latest"}];
        return makeSelect("cell-taxonomy","cell-taxonomy[]",options);

    }
        
    var makeCelltypeSelect = function(){
        var options = [
                       { value:"1x1", text:"1x1 Cell" },
                       { value:"2x2", text:"2x2 Cell" },
                       { value:"3x1", text:"3x1 Cell" },
                       { value:"3x3", text:"3x3 Cell" },
                       { value:"3x6", text:"3x6 Cell" },
                       { value:"homepage-feature", text:"Homepage Feature" },
                       { value:"special-series", text:"Series Cell" },
                       { value:"special-topartists", text:"Top Artists" }
                       ];
        return makeSelect("cell-type","cell-type[]",options);
    }
    
    var makeSelect = function(id,name,options, css_class=""){
        // categories select box
        var select=$("<select></select>").attr("id",id).attr("name",name).addClass(css_class);
        $.each(options, function(key, value) {
            var option = $("<option></option>").text(value.text).val(value.value);
            select.append(option);
        });
        return select;
    }
    
    var makeInput = function(type,id,name,value,placeholder, css=null){
               
        var input = $('<input></input>').attr('id',id)
                                        .attr('name',name+'[]')
                                        .attr('type',type)
                                        .attr('class',css)
                                        .attr('placeholder',placeholder)
                                        .val(value);
        
        return input;

    }
    
    var makeDiv = function(id, css){
        var div= $("<div></div>").attr("id",id).attr('class',css);
        return div;
    }
    
    var makeLabel = function(text, label_for, css=null){
        var label = $("<label></label>").text(text)
                                        .attr('for',label_for)
                                        .attr('class',css);
        return label;
    }
    
    
    var makeCellFrame = function(){
        // cell_1x1 is the defaultframe.
        var li  = $("<li></li>").addClass("ui-state-default cell_1x1");
        var del  = $("<div class='delete-cell'>&#9447;</div>");
        li.append(del);
        return li;

    }
    
    var makeStandardForm = function(dept){
        var wrap = makeDiv('','cell-wrap');
        
        var celltypes_select = makeCelltypeSelect();
        var designation_select = makeDesignationSelect();
        var taxonomy_select = makeTaxonomySelect();
        var categories_select = makeCategorySelect(dept,"category","category", "cell-category[]","");
        
        // empty fields for homepage special form.
        var header_input = makeInput("hidden", "cell-header", "cell-header", "n/a");
        var subheader_input = makeInput( "hidden", "cell-subheader", "cell-subheader", "n/a");
        var image1 = makeInput( "hidden", "image1", "image1", "n/a");
        var image2 = makeInput( "hidden", "image2", "image2", "n/a");
        var image3 = makeInput( "hidden", "image3", "image3", "n/a" );
        
        var link1 = makeInput( "hidden", "link1", "link", "n/a");
        var link2 = makeInput( "hidden", "link2", "link", "n/a");
        var link3 = makeInput( "hidden", "link3 ", "link", "n/a");

        var description = makeInput ("hidden", "cell-description", "cell-description", "n/a");
        
        wrap.append(celltypes_select)
            .append(designation_select)
            .append(taxonomy_select)
            .append(categories_select)
            .append(header_input)
            .append(subheader_input)
            .append(image1)
            .append(image2)
            .append(image3)
            .append(description);
            
        return wrap;

    }
    
    var clearCellType = function(cell){
        cell.removeClass("cell_1x1")
            .removeClass("cell_2x2")
            .removeClass("cell_3x3")
            .removeClass("cell_3x1")
            .removeClass("cell_3x6")
            .removeClass("cell_special-topartists")
            .removeClass("cell_special-series")
            .removeClass("cell_homepage-feature");        
    }
    
    var makeSeriesCellForm = function(container){
        var designation_select = makeDesignationSelect("series");
        var taxonomy_input = $("<input></input").attr("type","hidden").attr("id","taxonomy").attr("name","cell-taxonomy[]").val("series");
        var series_select = makeCategorySelect("listen","series");
        
        // Homepage Feature Placeholder Inputs
        var header_input = makeInput("hidden", "cell-header", "cell-header", "n/a");
        var subheader_input = makeInput( "hidden", "cell-subheader", "cell-subheader", "n/a");
        var image1 = makeInput( "hidden", "cell-image1", "cell-image1", "n/a");
        var image2 = makeInput( "hidden", "cell-image2", "cell-image2", "n/a");
        var image3 = makeInput( "hidden", "cell-image3", "cell-image3", "n/a" );

        var link1 = makeInput( "hidden", "link1", "link", "n/a");
        var link2 = makeInput( "hidden", "link2", "link", "n/a");
        var link3 = makeInput( "hidden", "link3 ", "link", "n/a");
        
        var description = makeInput ("hidden", "cell-description", "cell-description", "n/a");
                               
        
        container.append(designation_select)
                .append(taxonomy_input)
                .append(series_select)
                .append(header_input)
                .append(subheader_input)
                .append(image1)
                .append(image2)
                .append(image3)
                .append(link1)
                .append(link2)
                .append(link3)
                .append(description);
    }
    
    var makeTextarea = function(id, name, placeholder, css){
        var textarea = $("<textarea></textarea>").attr("id",id)
                                                .attr("name",name+"[]")
                                                .attr("placeholder",placeholder)
                                                .attr("class",css);
                                                
        return textarea;   
    }
});

