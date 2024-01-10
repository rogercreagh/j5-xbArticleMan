/**
 * @package xbarticleman
 * @filesource /media/js/closedetails.js
 * @version 2.0.4.2 9th November 2023
 * @desc fragment to auto close other details sections when one is opened. Needs a checkbok (or hidden element) with id="autoclose" and attribute checked
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2019
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 * 
**/
const All_Details = document.querySelectorAll('details');

All_Details.forEach(deet=>{
  deet.addEventListener('toggle', toggleOpenOneOnly)
})

function toggleOpenOneOnly(e) {
  if (document.getElementById('autoclose').checked){
    if (this.open) {
      All_Details.forEach(deet=>{
        if (deet!=this && deet.open) deet.open = false
      });
    }
    
  }
}
