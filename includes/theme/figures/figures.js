oesOpenImageModal(document.getElementsByClassName("oes-modal-toggle"));
oesGallerySelect(document.getElementsByClassName("oes-gallery-carousel-thumbnail"));
oesGallerySlider(document.getElementsByClassName("oes-gallery-slider-next"), true);
oesGallerySlider(document.getElementsByClassName("oes-gallery-slider-previous"), false);

/* open the image modal (popup) */
function oesOpenImageModal(modals) {
    let j;
    for (j = 0; j < modals.length; j++) {
        const modalContainer = modals[j].firstElementChild;
        modalContainer.addEventListener("click", function () {

            const img = this.firstElementChild,
                modal = this.parentElement.nextElementSibling,
                close = modal.firstElementChild,
                modalContainer = jQuery(modal).find('.oes-modal-image-container').get(0),
                modalImg = modalContainer.firstElementChild

            if (img && modalImg) {
                modal.style.display = "block";
                modalImg.src = img.src;
            }

            /* close modal */
            if (close) {
                close.onclick = function () {
                    modal.style.display = "none";
                }
            }
        });
    }
}

/* select a new image in gallery to be displayed as center image */
function oesGallerySelect(images) {
    let j;
    for (j = 0; j < images.length; j++) {
        images[j].addEventListener("click", function () {
            oesGallerySwapImage(this)
        });
    }
}

/* swap center image, modal image & data of gallery with new image */
function oesGallerySwapImage(image){

    const id = image.dataset.id,
        carousel = image.parentElement.parentElement,
        figure = carousel.parentElement;

    /* set thumbnail active */
    jQuery(carousel).children('.oes-figure-thumbnail').removeClass('active');
    jQuery(carousel).find('.oes-gallery-carousel-thumbnail.wp-image-' + id).parent().addClass('active');

    /* show caption */
    jQuery(figure).find('.oes-panel-figcaption').removeClass('active');
    jQuery(figure).find('.oes-panel-figcaption-' + id).addClass('active');

    /* switch images for panel and modal */
    let panelImage = jQuery(figure).find('#oes-panel-image-center').get(0);
    panelImage.srcset = image.srcset;
    panelImage.src = image.src;
    panelImage.alt = image.alt;
    let modalImage = jQuery(figure).find('#oes-modal-image-center').get(0);
    modalImage.srcset = image.srcset;
    modalImage.src = image.src;
    modalImage.alt = image.alt;

    /* set table data active */
    jQuery(figure).find('.oes-modal-content-text').removeClass('active');
    jQuery('.oes-modal-content-text-' + id).addClass('active');
}

/* thumb through gallery by swapping the center image */
function oesGallerySlider(slider, next) {
    let j;
    for (j = 0; j < slider.length; j++) {
        slider[j].addEventListener("click", function () {

            const figure = this.closest('figure'),
            activeImage = jQuery(figure).find('.oes-figure-thumbnail.active').get(0);

            /* get next element, if this is empty get first element of carousel */
            let newImage;
            if(next) {
                newImage = jQuery(activeImage).next().get(0);
                if (newImage === undefined)
                    newImage = jQuery(figure).find('.oes-figure-thumbnail').first().get(0);
            }
            /* get previous element, if this is empty get last element of carousel */
            else{
                newImage = jQuery(activeImage).prev().get(0);
                if (newImage === undefined)
                    newImage = jQuery(figure).find('.oes-figure-thumbnail').last().get(0);
            }

            if(newImage.firstChild !== undefined) oesGallerySwapImage(newImage.firstChild);
        });
    }
}