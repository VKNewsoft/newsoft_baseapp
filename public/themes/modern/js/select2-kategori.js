/**
*	App Name	: Base App Admin
*	Developed by: Newsoft Developer
*	Year		: 2020-2023
*/

jQuery(document).ready(function () {
	
	$.fn.select2tree = function(options) {
		var defaults = {
			language: "pt-BR",
			theme: "bootstrap",
			matcher: matchCustom,
			templateSelection: templateSelectionCustom,
			templateResult: templateResultCustom
		};
		var opts = $.extend(defaults, options);
		var $this = $(this);
		$(this).select2(opts).on("select2:open", function() {
			open($this);
		});
	};

	function templateResultCustom(data, container) 
	{
		if (data.element) {
			// console.log(data)
			var $element = $(data.element);
			
			let icon_class = $element.data('icon');
			let icon = icon_class ? '<i class="' + icon_class + '"></i>&nbsp;&nbsp;' : ''; 
			var $wrapper = $('<span style="width:20px"></span><span>' + icon + data.text + '</span>');
			var $switchSpn = $wrapper.first();
			
			var $select = $element.parent();
			var $container = $(container);

			$container.attr("val", $element.val());
			$container.attr("data-parent", $element.data("parent"));

			var hasChilds = $select.find("option[data-parent='" + $element.val() + "']").length > 0;
			var isSearching = $(".select2-search__field").val().length > 0;
		  
			if (isSearching) {
				$switchSpn.css({
					"padding": "0 10px 0 10px",
				});
			} else if (hasChilds) {
		  
				$container.addClass('parent');
				$switchSpn.addClass("switch-tree fas fa-chevron-right");
				
				/* if ($switchSpn.hasClass("fa-chevron-right"))
					$switchSpn.removeClass("fa-chevron-right")
					.addClass("fa-chevron-down");
				else
					$switchSpn.removeClass("fa-chevron-down")
					.addClass("fa-chevron-right"); */

				$switchSpn.css({
					"padding": "0 10px 0 0px",
					"cursor": "pointer"
				});
			}

			if (hasParent($element)) {
				var paddingLeft = getTreeLevel($select, $element.val()) * 20;
				if (!hasChilds) paddingLeft++;
				$container.css("margin-left", paddingLeft + 'px');
			}

			return $wrapper;
		} else {
		  return data.text;
		}
	};

	function hasParent($element) {
		return $element.data("parent") !== '';
	}

	function getTreeLevel($select, id) {
		var level = 0;
		while ($select.find("option[data-parent!=''][value='" + id + "']").length > 0) {
			id = $select.find("option[value='" + id + "']").data("parent");
			level++;
		}
		return level;
	}


	function moveOption($select, id) {
		if (id) {
			$select.find(".select2-results__options li[data-parent='" + id + "']").insertAfter(".select2-results__options li[val=" + id + "]");
			$select.find(".select2-results__options li[data-parent='" + id + "']").each(function() {
				moveOption($select, $(this).attr("val"));
			});
		} else {

			$(".select2-results__options li[data-parent!='']").hide();
			$(".select2-results__options li[data-parent='']").appendTo(".select2-results__options ul");
			$(".select2-results__options li[data-parent='']").each(function() {
				moveOption($select, $(this).attr("val"));
			});
		}
	}

	function switchAction($select, id, open) {

		var childs = $(".select2-results__options li[data-parent='" + id + "']");
		var parent = $(".select2-results__options li[val=" + id + "] span[class]:eq(0)");
		if (open) {
			parent.removeClass("fa-chevron-right")
				.addClass("fa-chevron-down");
			childs.show();
		} else {
			parent.removeClass("fa-chevron-down")
				.addClass("fa-chevron-right");
			childs.hide();
		}
	}

	function open($select) {
		$('.select2-dropdown').hide();
		setTimeout(function() {
			$('.select2-dropdown').show();
			moveOption($select);
			//override mousedown for collapse/expand 
			$(".switch-tree").mousedown(function() {
				switchAction($select, $(this).parent().attr("val"), $(this).hasClass("fa-chevron-right"));
				event.stopPropagation();
			});
			
			$('li.select2-results__option').click(function(event) 
			{
				var $this = $(this);
				var id = $(this).attr('val');
				var $childs = $(".select2-results__options li[data-parent='" + id + "']");
				if ($(this).hasClass('child-open')) {
					hideChilds($childs);
					$this.find('.switch-tree').removeClass("fa-chevron-down").addClass("fa-chevron-right");
					$this.removeClass('child-open');
				} else {
					$childs.show();
					$this.find('.switch-tree').removeClass("fa-chevron-right").addClass("fa-chevron-down");
					$this.addClass('child-open')
				}
			});
			//override mouseup to nothing
			$(".switch-tree").mouseup(function() {
				return false;
			});

		}, 0);
	}
	
	function hideChilds($listElm) 
	{
		if ($listElm.length == 0)
			return;
		
		$listElm.each(function(i, elm) {
			$this = $(this);
			$this.find('.switch-tree').removeClass("fa-chevron-down").addClass("fa-chevron-right");
			$this.removeClass('child-open');
			$this.hide();
			let id = $this.attr('val');
			$childs = $(".select2-results__options li[data-parent='" + id + "']");
			if ($childs.length) {
				hideChilds($childs);
			}
		});
	}

	function matchCustom(params, data) {
		if ($.trim(params.term) === '') {
			return data;
		}
		if (typeof data.text === 'undefined') {
			return null;
		}
		var term = params.term.toLowerCase();
		var $element = $(data.element);
		var $select = $element.parent();
		var childMatched = checkForChildMatch($select, $element, term);
		if (childMatched || data.text.toLowerCase().indexOf(term) >= 0) {
			$("#" + data._resultId).css("display", "unset");
			return data;
		}
		return null;
	}

	function checkForChildMatch($select, $element, term) {
		var matched = false;
		var childs = $select.find('option[data-parent=' + $element.val() + ']');
		var childMatchFilter = jQuery.makeArray(childs).some(s => s.text.toLowerCase().indexOf(term) >= 0)
		if (childMatchFilter) return true;

		childs.each(function() {
			var innerChild = checkForChildMatch($select, $(this), term);
			if (innerChild) matched = true;
		});

		return matched;
	}

	function templateSelectionCustom(item) {

		if (!item.id || item.id == "-1") {
			return $("<i class='fa fa-hand-o-right'></i><span> " + item.text + "</span>");
		}

		var $element = $(item.element);
		var $select = $element.parent();

		var parentsText = getParentText($select, $element);
		if (parentsText != '') parentsText += ' &raquo; ';
		
		let icon_class = $element.data('icon');
		let icon = icon_class ? '<i class="' + icon_class + '"></i>&nbsp;&nbsp;' : ''; 
		var $state = $(
			"<span> " + parentsText + icon + item.text + "</span>"
		);
		return $state;
	}

	function getParentText($select, $element) 
	{
		var text = '';
		var parentVal = $element.data('parent');
		if (parentVal == '') return text;

		var parent = $select.find('option[value=' + parentVal + ']');

		if (parent) {
			text = getParentText($select, parent);
			
			if (text != '') text += ' &raquo; ';
			let $element = $(parent[0]);
			let icon_class = $element.data('icon');
			let icon = icon_class ? '<i class="' + icon_class + '"></i>&nbsp;&nbsp;' : ''; 
			text += icon + parent.text();
		}
		return text;
	}

	///USAGE
	// $("#list-kategori").select2tree({theme: 'bootstrap-5'});
	$("#list-kategori").select2tree({theme: 'bootstrap-5'});
	
	/* $("#list-kategori").on("select2:open", function(e) {
		// console.log("select2:open", e);
	});
	$("#list-kategori").on("select2:close", function(e) {
		// console.log("select2:close", e);
	});
	$("#list-kategori").on("select2:select", function(e) {
		// console.log("select2:select", e);
	});
	$("#list-kategori").on("select2:unselect", function(e) {
		// console.log("select2:unselect", e);
	}); */
});