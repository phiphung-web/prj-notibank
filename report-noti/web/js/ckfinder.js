$(document).on('click', '.ckfinder-select-car', function () {
    let key = $(this).parents('.row-table-relationship').attr('data-key')
    let name = $(this).attr('data-name')
    let ckfinder_path = $('.input-folder-image').val()
    createSubCarFolder(ckfinder_path, key, name, $(this));
    return false;
});


const fieldToFolderMap = {
    'driver_license_front': 'driver_license',
    'driver_license_behind': 'driver_license',
    'registration_certificate_front': 'album_registration_certificate',
    'registration_certificate_behind': 'album_registration_certificate',
    'identity_back_image': 'identity',
    'identity_front_image': 'identity',
    'vehicle_plate_image': 'album_registration_certificate'

};

$(document).on('click', '.input-ckfinder', function () {
    let _this = $(this)
    let ckfinder_path = $('.input-folder-image').val()
    let fieldName = $(this).attr('data-name')
    // Use mapped folder name if exists, otherwise use field name
    let folderName = fieldToFolderMap[fieldName] || fieldName

    $.ajax({
        url: '/driver/create-folder',
        method: 'GET',
        data: { folderName: ckfinder_path, folderChild: folderName },
        dataType: 'json',
        success: function (response) {
            CKFinder.popup({
                chooseFiles: true,
                resourceType: 'Images',
                startupPath: "Images:/" + ckfinder_path + "/" + folderName + "/",
                startupFolderExpanded: false,
                rememberLastFolder: false,
                onInit: function (finder) {
                    finder.on('files:choose', function (evt) {
                        var file = evt.data.files.first();
                        _this.val(file.getUrl())
                    });
                }
            });
        }
    });
    return false;
});

$(document).on('click', '.open-ckfinder-driver', function () {
    let model = $(this).attr('data-model')
    let name = $(this).attr('data-name')
    openCKFinderPrimaryDriver(model, name, $(this));
    return false;
});

$(document).on('click', '.remove-img', function () {
    $(this).parents('.wrap-image-ckfinder').remove();
    return false;
})

function openCKFinderPrimaryDriver(model, name, object) {
    let ckfinder_path = $('.input-folder-image').val()
    createFolder(ckfinder_path, model, name, object);
}

function createFolder(folderName, model, name, object) {
    $.ajax({
        url: '/driver/create-folder',
        method: 'GET',
        data: { folderName: folderName, folderChild: name },
        dataType: 'json',
        success: function () {
            CKFinder.popup({
                chooseFiles: true,
                resourceType: 'Images',
                startupPath: "Images:/" + folderName + "/" + name + "/",
                startupFolderExpanded: false,
                rememberLastFolder: false,
                onInit: function (finder) {
                    finder.on('files:choose', function (evt) {
                        let files = evt.data.files.toArray();
                        var html = '';
                        for (let index = 0; index < files.length; index++) {
                            const fileUrl = files[index].getUrl();
                            html += '<div class="wrap-image-ckfinder"><img class="img-driver" src="' + fileUrl + '" alt="' + fileUrl + '"><input name="' + model + '[' + name + '][]" value="' + fileUrl + '" type="hidden"><div class="remove-img"><i class="fa fa-trash" aria-hidden="true"></i></div></div>';
                        }
                        object.parents('.wrap-ckfinder-general').find('.list-img-general').append(html);
                    });
                }
            });
        }
    });
}

function createSubCarFolder(folderName, key, name, object) {
    $.ajax({
        url: '/driver/create-folder',
        method: 'GET',
        data: { folderName: folderName, folderChild: name },
        dataType: 'json',
        success: function (response) {
            CKFinder.popup({
                chooseFiles: true,
                resourceType: 'Images',
                startupPath: "Images:/" + folderName + "/" + name + "/",
                startupFolderExpanded: false,
                rememberLastFolder: false,
                onInit: function (finder) {
                    finder.on('files:choose', function (evt) {
                        let files = evt.data.files.toArray();
                        var html = '';
                        for (let index = 0; index < files.length; index++) {
                            const fileUrl = files[index].getUrl();
                            html += '<div class="wrap-image-ckfinder"><img class="img-driver" src="' + fileUrl + '"><input name="cars[' + key + '][' + name + '][]" data-name="' + name + '" value="' + fileUrl + '" type="hidden"><div class="remove-img"><i class="fa fa-trash" aria-hidden="true"></i></div></div>';
                        }
                        object.parents('.wrap-ckfinder-general').find('.wrap-image-sub-car').append(html);
                    });
                }
            });
        }
    });
}

function slug(title) {
    title = cnvVi(title);
    return title;
}


function cnvVi(str) {
    str = str.toLowerCase(); // chuyen ve ki tu biet thuong
    str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
    str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
    str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
    str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
    str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
    str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
    str = str.replace(/đ/g, "d");
    str = str.replace(/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|,|\.|\:|\;|\'|\–| |\"|\&|\#|\[|\]|\\|\/|~|$|_/g, "-");
    str = str.replace(/-+-/g, "-");
    str = str.replace(/^\-+|\-+$/g, "");
    return str;
}
function replace(Str = '') {
    if (Str == '') {
        return '';
    } else {
        Str = Str.replace(/\./gi, "");
        return Str;
    }
}
