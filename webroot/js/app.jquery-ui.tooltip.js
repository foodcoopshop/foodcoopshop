/**
 * FoodCoopShop - The open source software for your foodcoop
 * 
 * Source: http://stackoverflow.com/questions/25244312/how-to-make-jquery-ui-tooltip-stay-on-on-hover
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
(function($) {

	 var uiTooltipTmp = {
	    options: {
	        hoverTimeout: 200,
	        tooltipHover: false // to have a regular behaviour by default. Use true to keep the tooltip while hovering it 
	    },
	    // This function will check every "hoverTimeout" if the original object or it's tooltip is hovered. If not, it will continue the standard tooltip closure procedure.
	    timeoutHover: function (event,target,tooltipData,obj){
	        var TO;
	        var hov=false, hov2=false;
	        if(target !== undefined) {
	            if(target.is(":hover")){
	            hov=true;}
	        }
	        if(tooltipData !== undefined) {
	            if($(tooltipData.tooltip).is(":hover")){
	            hov=true;}
	        }
	        if(target !== undefined || tooltipData !== undefined) {hov2=true;}
	        if(hov) {
	            TO = setTimeout(obj.timeoutHover,obj.options.hoverTimeout,event,target,tooltipData,obj);
	        }else{
	            target.data('hoverFinished',1);
	            clearTimeout(TO);
	            if(hov2){
	                obj.closing = false;
	                obj.close(event,true);}
	        }
	    },
	    // Changed standard procedure
	    close: function(event) {
	      var tooltip,
	          that = this,
	          target = $( event ? event.currentTarget : this.element ),
	          tooltipData = this._find( target );
	      if(that.options.tooltipHover && (target.data('hoverFinished')===undefined || target.data('hoverFinished') === 0)){
	        target.data('hoverFinished',0);
	        setTimeout(that.timeoutHover, that.options.hoverTimeout,event, target, tooltipData, that);
	      }
	      else
	      {
	        if(that.options.tooltipHover){
	          target.data('hoverFinished',0);}

	        // The rest part of standard code is unchanged

	        if ( !tooltipData ) {
	          target.removeData( "ui-tooltip-open" );
	          return;
	        }

	        tooltip = tooltipData.tooltip;
	        if ( tooltipData.closing ) {
	          return;
	        }

	        clearInterval( this.delayedShow );

	        if ( target.data( "ui-tooltip-title" ) && !target.attr( "title" ) ) {
	          target.attr( "title", target.data( "ui-tooltip-title" ) );
	        }

	        this._removeDescribedBy( target );

	        tooltipData.hiding = true;
	        tooltip.stop( true );
	        this._hide( tooltip, this.options.hide, function() {
	          that._removeTooltip( $( this ) );
	        } );

	        target.removeData( "ui-tooltip-open" );
	        this._off( target, "mouseleave focusout keyup" );

	        if ( target[ 0 ] !== this.element[ 0 ] ) {
	          this._off( target, "remove" );
	        }
	        this._off( this.document, "mousemove" );

	        if ( event && event.type === "mouseleave" ) {
	          $.each( this.parents, function( id, parent ) {
	            $( parent.element ).attr( "title", parent.title );
	            delete that.parents[ id ];
	          } );
	        }

	        tooltipData.closing = true;
	        this._trigger( "close", event, { tooltip: tooltip } );
	        if ( !tooltipData.hiding ) {
	          tooltipData.closing = false;
	        }
	      }
			}
	    };

	 	// Extending ui.tooltip. Changing "close" function and adding two new parameters.
	    $.widget( "ui.tooltip", $.ui.tooltip, uiTooltipTmp);

})(jQuery);