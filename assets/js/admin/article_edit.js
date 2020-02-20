$(document).ready(function () {
    // input file
    bsCustomFileInput.init();

    // wysiwyg
    CKEDITOR.replace('article_content');

    var prototypeIndex = 1000;
    $('#btn-add-tag').click(function () {
        var $this = $(this);
        var newTag = $this.data('prototype').replace(/__name__/g, prototypeIndex);

        $this.before(newTag);

        prototypeIndex++;
    });

});
