function PunkAveFileUploader(options)
{
    var self = this, uploadUrl = options.uploadUrl, thumbnailUrl = options.thumbnailUrl, viewUrl = options.viewUrl, $el = $(options.el), uploaderTemplate = _.template($.trim($('#file-uploader-template').html()));
    $el.html(uploaderTemplate({}));
    //console.log($el);

    var fileTemplate = _.template($.trim($('#file-uploader-file-template').html())),
        editor = $el.find('[data-files="1"]')/*editor = $("#fileUploader")*/, thumbnails = $el.find('[data-thumbnails="1"]');
    //console.log(editor);

    self.uploading = false;
    self.errorCallback = 'errorCallback' in options ? options.errorCallback : function(info) {
        if (window.console && console.log) {
            //console.log(info)
        }
    },

        self.addExistingFiles = function(files) {
            //console.log(files);
            _.each(files, function(file) {
                appendEditableImage({
                    //'thumbnail_url': file.thumbnail == null ? viewUrl + '/' + file.name : file.thumbnail,
                    'thumbnail_url': file.thumbnail == null ? thumbnailUrl + '/' + file.name : file.thumbnail,
                    'url': viewUrl + '/' + file.name,
                    'name': file.name,
                    'size': file.size,
                    'date_upload': file.date_upload
                });
            });
        };

    // Delay form submission until upload is complete.
    // Note that you are welcome to examine the
    // uploading property yourself if this isn't
    // quite right for you
    self.delaySubmitWhileUploading = function(sel) {
        $(sel).submit(function(e) {
            if (!self.uploading) {
                return true;
            }
            function attempt() {
                if (self.uploading) {
                    setTimeout(attempt, 100);
                } else {
                    $(sel).submit();
                }
            }
            attempt();
            return false;
        });
    }

    if (options.blockFormWhileUploading) {
        self.blockFormWhileUploading(options.blockFormWhileUploading);
    }

    if (options.existingFiles) {
        self.addExistingFiles(options.existingFiles);
    }

    editor.fileupload({ // uploading proccess
        dataType: 'json',
        url: uploadUrl,
        dropZone: $el.find('[data-dropzone="1"]'),
        done: function (e, data) {
            // console.log(data);
            if (data) {
                _.each(data.result, function(item) {
                    // console.log(item);
                    appendEditableImage(item);
                });
            }
        },
        start: function (e) {
            $el.find('[data-spinner="1"]').show();
            self.uploading = true;
        },
        stop: function (e) {
            $el.find('[data-spinner="1"]').hide();
            self.uploading = false;
        }
    });

    // Expects thumbnail_url, url, and name properties. thumbnail_url can be undefined if
    // url does not end in gif, jpg, jpeg or png. This is designed to work with the
    // result returned by the UploadHandler class on the PHP side
    function appendEditableImage(info) {
        // console.log(info);
        if (info.error) {
            self.errorCallback(info);
            return;
        }
        //console.log(info);
        var li = $(fileTemplate(info));
        /*If clicked for deleting selected file*/
        li.find('[data-action="delete"]').click(function(event) {
            var file = $(this).closest('[data-name]');
            var name = file.attr('data-name');
            $.ajax({
                dataType: 'json',
                type: 'post',
                //url: uploadUrl + '&file=' + name,
                url: setQueryParameter(uploadUrl, 'file', name),
                success: function() {
                    li.hide("slow", function(){
                        file.remove();
                    })
                }
            });
            return false;
        });
        var a = $(".thumbnails");
        a.after(li);
        //thumbnails.append(li);
    }

    function setQueryParameter(url, param, paramVal) {
        var newAdditionalURL = "";
        var tempArray = url.split("?");
        var baseURL = tempArray[0];
        var additionalURL = tempArray[1];
        var temp = "";
        if (additionalURL) {
            var tempArray = additionalURL.split("&");
            for (var i = 0; i < tempArray.length; i++) {
                if (tempArray[i].split('=')[0] != param ) {
                    newAdditionalURL += temp + tempArray[i];
                    temp = "&";
                }
            }
        }
        var newTxt = temp + "" + param + "=" + encodeURIComponent(paramVal), finalURL = baseURL + "?" + newAdditionalURL + newTxt;
        return finalURL;
    }
}