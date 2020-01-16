// module.exports = function(name) {
//    return `Yo yo ${name} - welcome to Encore!`;
//};
$(document).ready(function() {
    bsCustomFileInput.init()
});

$('#playerFileUpload').on('change',function(){
    //get the file name
    var fileName = $(this).val();
    //replace the "Choose a file" label
    $(this).next('.custom-file-label').html(fileName);
});
