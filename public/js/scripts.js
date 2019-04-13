$(document).ready(function() {       
    function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
      return split( term ).pop();
    }

    $("#app_transfer_customer")
        .on( "change", function( event ) {
            $("#app_transfer_files").val('');
        });

    $("#app_transfer_type")
        .on( "change", function( event ) {
            $("#app_transfer_files").val('');
        });        

    $("#app_transfer_date")
        .on( "change", function( event ) {
            $("#app_transfer_files").val('');
        });

    $("#app_transfer_files")
        .on( "keydown", function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB && $( this ).autocomplete( "instance" ).menu.active ) {
                event.preventDefault();
            }
        })
        .autocomplete({
            source: function( request, response ) {
                var lastIndex = request.term.lastIndexOf(",");
                var term = request.term.substring(lastIndex + 1);
                var customer = $( "#app_transfer_customer" ).val();
                var type = $( "#app_transfer_type" ).val();
                var date = $( "#app_transfer_date" ).val();
                $.getJSON( "http://localhost:8000/admin/file/api/"+term.trim()+"/"+customer+"/"+type+"/"+date, {}, response );
            },
            search: function() {
                var term = extractLast( this.value );
                if ( term.length < 1 ) {
                    return false;
                }
            },
            focus: function() {
                return false;
            },
            select: function( event, ui ) {
                var terms = split( this.value );
                terms.pop();
                terms.push( ui.item.value );
                terms.push( "" );
                this.value = terms.join( ", " );
                return false;
            },
            response: function(event, ui) {
                if (!ui.content.length) {
                    var noResult = { value: "", label: translation.no_results };
                    ui.content.push(noResult);
                }
            }                            
        });

    $('.multiple').multiselect({
        buttonText: function(options, select) {
            if (options.length === 0) {
                return translation.all;
            } else {
                var labels = [];
                options.each(function() {
                    if ($(this).attr('label') !== undefined) {
                        labels.push($(this).attr('label'));
                    } else {
                        labels.push($(this).html());
                    }
                });
                return labels.join(', ') + '';
            }                            
        }
    });

    $('.multiple-male').multiselect({
        buttonText: function(options, select) {
            if (options.length === 0) {
                return translation.all_male;
            } else {
                var labels = [];
                options.each(function() {
                    if ($(this).attr('label') !== undefined) {
                        labels.push($(this).attr('label'));
                    } else {
                        labels.push($(this).html());
                    }
                });
                return labels.join(', ') + '';
            }                            
        }
    });

    $("[data-toggle=popover]").popover();

    $('.popover-dismiss').popover({
        trigger: 'focus'
    });

    $( ".datepicker" ).datepicker({ 
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true                            
    });

    $( ".datetimepicker" ).datetimepicker({ 
        dateFormat: 'dd-mm-yy',
        timeFormat: "HH:mm",
        changeMonth: true,
        changeYear: true                            
    });

    $(".monthpicker").datepicker({
        dateFormat : 'mm-yy',
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).val($.datepicker.formatDate('mm-yy', new Date(year, month, 1)));
        }        
    }); 

    $(".monthpicker").focus(function () {
        $(".ui-datepicker-calendar").hide();
        $("#ui-datepicker-div").position({
            my: "center top",
            at: "center bottom",
            of: $(this)
        });
    });

    $("#print").click(function() {
        var content = $(".printable").html();
        var metadata = $("#metadata").html();
        metadata.replace('hidden', '');

        $.ajax({
            url: 'http://localhost:8000/print',
            method: 'POST',
            data: { content: content, metadata: metadata },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response, status, xhr) {
                var filename = "";                   
                var disposition = xhr.getResponseHeader('Content-Disposition');

                 if (disposition) {
                    var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    var matches = filenameRegex.exec(disposition);
                    if (matches !== null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                } 
                var linkelem = document.createElement('a');
                try {
                                           var blob = new Blob([response], { type: 'application/octet-stream' });                        

                    if (typeof window.navigator.msSaveBlob !== 'undefined') {
                        //   IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
                        window.navigator.msSaveBlob(blob, filename);
                    } else {
                        var URL = window.URL || window.webkitURL;
                        var downloadUrl = URL.createObjectURL(blob);

                        if (filename) { 
                            // use HTML5 a[download] attribute to specify filename
                            var a = document.createElement("a");

                            // safari doesn't support this yet
                            if (typeof a.download === 'undefined') {
                                window.location = downloadUrl;
                            } else {
                                a.href = downloadUrl;
                                a.download = filename;
                                document.body.appendChild(a);
                                a.target = "_blank";
                                a.click();
                            }
                        } else {
                            window.location = downloadUrl;
                        }
                    }   

                } catch (ex) {
                    console.log(ex);
                } 
            }     
        });
    });                    
});
