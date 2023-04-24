/* expand modal image */
expandImageModal(document.getElementsByClassName("oes-modal-toggle"));
function expandImageModal(modals){
    let j;
    for (j = 0; j < modals.length; j++) {
        const modalContainer = modals[j].firstElementChild;
        modalContainer.addEventListener("click", function() {

            const img = this.firstElementChild,
                modal = this.parentElement.nextElementSibling,
                close = modal.firstElementChild,
                modalImg = close.nextElementSibling.firstElementChild;


            if(img  && modalImg){
                modal.style.display = "block";
                modalImg.src = img.src;
            }

            /* close modal */
            if(close){
                close.onclick = function() {
                    modal.style.display = "none";
                }
            }
        });
    }
}

function oesToggleGalleryPanel(show) {
    let parent = jQuery(show).parent();
    parent.children('.oes-gallery-image').hide();
    jQuery(show).fadeIn();

    parent.find('.oes-figure-thumbnail').removeClass('active');
    jQuery('.thumbnail-' + show.id).addClass('active');
}