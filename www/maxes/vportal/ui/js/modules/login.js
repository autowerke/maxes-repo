
const UI = {
    
    setDialog: function(title, contentHtml, footerHtml = null, dialogId = 'dynamic-dialog') {
        // Hancurkan dialog lama jika ada, untuk mencegah duplikasi
        this.destroyDialog(dialogId);

        // Buat elemen-elemen dialog
        const dialogBackdrop = $('<div>', {
            'id': dialogId,
            'class': 'dialog-backdrop'
        });

        const dialogBox = $('<div>', { 'class': 'dialog-box' });
        
        // Header
        const dialogHeader = $('<div>', { 'class': 'dialog-header' });
        dialogHeader.append($('<h2>').text(title));
        dialogHeader.append($('<button>', { 'id': 'dialog-close-btn' }).html('&times;'));

        // Content
        const dialogContent = $('<div>', { 'class': 'dialog-content' }).html(contentHtml);
        
        // Gabungkan semua bagian
        dialogBox.append(dialogHeader, dialogContent);

        // Footer (jika ada)
        if (footerHtml) {
            const dialogFooter = $('<div>', { 'class': 'dialog-footer' }).html(footerHtml);
            dialogBox.append(dialogFooter);
        }

        dialogBackdrop.append(dialogBox);

        // Tambahkan dialog ke body
        $('body').append(dialogBackdrop);
        
        // Trigger reflow untuk memastikan transisi CSS berjalan
        dialogBackdrop[0].offsetHeight;
        
        // Tampilkan dialog dengan menambahkan class 'show'
        setTimeout(() => {
            dialogBackdrop.addClass('show').css('opacity', '1');
        }, 10);

        // Event listener untuk tombol close dan klik di luar area
        dialogBackdrop.on('click', '#dialog-close-btn', () => this.destroyDialog(dialogId));
        dialogBackdrop.on('click', function(event) {
            if (event.target === this) {
                UI.destroyDialog(dialogId);
            }
        });
    },

    destroyDialog: function(dialogId = 'dynamic-dialog') {
        const dialog = $('#' + dialogId);
        dialog.removeClass('show').css('opacity', '0');
        setTimeout(() => {
            dialog.remove();
        }, 200);
    }
};



$(document).ready(function() {
    const token = localStorage.getItem('vendor_token');
     const API_URL = '../businessobject/router.php';

     $("#forgotpassword").click(function(){
        forgotPass(API_URL);
    })

    function forgotPass(API_URL){
    console.log("forgot pass")
        $.ajax({
            url: API_URL,
            type: 'POST',
            data: {
                actionname: 'forgotpassword'
            },
            dataType: 'json'
        })
        .done(function(response) {
              UI.setDialog(`Forgot Password`, response.data.instructions);
        })
        .fail(function() {
            alert('Please try again.');
        });
      
    }


})