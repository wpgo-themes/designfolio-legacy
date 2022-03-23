/* Custom code for Quicksand. */
jQuery(document).ready(function ($) {

	function portfolio_quicksand() {

		// Setting up our variables
		var $filter;
		var $containerClone;
		var $filteredItems;

		// Set our filter
		$filter = $('#filters li.active a').attr('class');

		// Clone our container
		$containerClone = $('ul.filterable-grid').clone();

		// Apply our Quicksand to work on a click function
		// for each of the filter li link elements
		$('#filters li a').click(function (e) {
			// Remove the active class
			$('.filter li').removeClass('active');

			// Split each of the filter elements and override our filter
			$filter = $(this).attr('class').split(' ');

			// Apply the 'active' class to the clicked link
			$(this).parent().addClass('active');

			// If 'all' is selected, display all elements
			// else output all items referenced by the data-type
			if ($filter == 'all') {
				$filteredItems = $containerClone.find('li');
			}
			else {
				$filteredItems = $containerClone.find('li[data-type~=' + $filter + ']');
			}

			// Finally call the Quicksand function
			$('ul.filterable-grid').quicksand($filteredItems,
				{
					duration    : 700,
					adjustHeight: 'auto'
				});
		});
	}

	if (jQuery().quicksand) {

		portfolio_quicksand();

	}

});