/* expand modal image */
expandImageModal(document.getElementsByClassName("oes-modal-toggle"));
function expandImageModal(modals){
    var j;
    for (j = 0; j < modals.length; j++) {
        var modalContainer = modals[j].firstElementChild;
        modalContainer.addEventListener("click", function() {

            var img = this.firstElementChild,
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