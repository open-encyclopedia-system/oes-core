document.addEventListener("DOMContentLoaded",(function(e){let t=jQuery("#oes-search-panel"),n=jQuery(document.body);if(t&&t.parent()!==n){const e=document.createElement("a");e.setAttribute("class","oes-close button"),e.setAttribute("onClick","oesTriggerById('oes-search-panel')"),t.children(":first").children(":first").append(e),n.prepend(t)}}));