/**
 * @package xbarticleman
 * @filesource /media/js/setifsrc.js
 * @version 0.0.3.1 11th January 2024
 * @desc for use with article preview modals. j3 sets id to window.pvid which should be set by onclick() on the source 
 * j5 only clears the iframe source from the page when modal closes
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2024
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 * 
**/
 /*
    jQuery(document).ready(function(){
        jQuery('#pvModal').on('show', function () {
            jQuery(this).find('.modal-body iframe').attr("src", window.pvuri);
//                "/index.php?option=com_content\&view=article\&id="+window.pvid);
        })
        jQuery('#pvModal').on('hidden', function () {
            jQuery(this).find('.modal-body iframe').attr("src","");
        })
    });
*/
    //more generic version would set the full url with id as window.pvurl
/*   
    var pvModal = document.getElementById('pvModal')
pvModal.addEventListener('show.bs.modal', function (event) {
  // Button that triggered the modal
  var button = event.relatedTarget
  // Extract info from data-bs-* attributes
  var source = button.getAttribute('data-bs-source')
  var title = button.getAttribute('data-bs-itemtitle')
  // If necessary, you could initiate an AJAX request here
  // and then do the updating in a callback.
  //
  // Update the modal's content.
  var modalTitle = pv.querySelector('.modal-title')
  var modalBodyInput = pvModal.querySelector('.modal-body iframe')

  modalTitle.textContent = title
  modalBodyInput.src = source
})
*/
    var pvModal = document.getElementById('pvModal')
		pvModal.addEventListener('hidden.bs.modal', function (event) {
          var pv=document.getElementById('pvModal');
          pv.querySelector('.modal-body .iframe').setAttribute('src','');
          pv.querySelector('.modal-title').textContent='preview article';
       })

    //more generic version would set the full url with id as window.pvurl