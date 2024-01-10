/**
 * @package xbarticleman
 * @filesource /media/js/setifsrc.js
 * @version 2.0.4.2 9th November 2023
 * @desc for use with article preview modals. sets id to window.pvid which should be set by onclick() on the source 
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2019
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 * 
**/
    jQuery(document).ready(function(){
        jQuery('#pvModal').on('show', function () {
            jQuery(this).find('.modal-body iframe').attr("src", window.pvuri);
//                "/index.php?option=com_content\&view=article\&id="+window.pvid);
        })
        jQuery('#pvModal').on('hidden', function () {
            jQuery(this).find('.modal-body iframe').attr("src","");
        })
    });

    //more generic version would set the full url with id as window.pvurl