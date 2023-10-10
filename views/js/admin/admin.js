$(document).ready(() => {

    $('.adminblogarticle input[name^="slug"], .adminblogcategory input[name^="slug"]').each(function (index) {
        $(this).attr('data-disabled', "disabled");
        $(this).attr('data-update-title' , ($(this).val() == "") ? 1 : 0);

    })

    $('.adminblogarticle input[name^="slug"], .adminblogcategory input[name^="slug"]').on('click', function (e) {
        $(this).removeAttr('data-disabled');
    })

    $('.adminblogarticle input[name^="name"], .adminblogcategory input[name^="name"]').on('change', function (e) {
        let name = $(this).attr('name');
        let slugField = "slug";

        let nameSplit = name.split("_");
        if(nameSplit.length > 1) {
            slugField += "_" + nameSplit[1];
        }

        let slug = $('input[name="' + slugField +'"]');
        if(slug.attr("data-update-title") == 1 && slug.attr('data-disabled')) {
            slug.val($(this).val().slugify());
        }
    })

    String.prototype.slugify = function (separator = "-") {
        return this
            .toString()
            .replace("?", "")
            .normalize('NFD')                   // split an accented letter in the base letter and the acent
            .replace(/[\u0300-\u036f]/g, '')   // remove all previously split accents
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9 ]/g, '')   // remove all chars not letters, numbers and spaces (to be replaced)
            .replace(/\s+/g, separator)
            .trim();
    };
})