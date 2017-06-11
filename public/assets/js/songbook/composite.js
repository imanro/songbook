/**
 *
 */
(function($) {
	"use strict";

	var methods = {
		getApiUrl: function(action, controller)
		{
			if(controller == undefined){
				controller = 'concert-ajax';
			}

			return '/' + controller + '/' + action;
		},

		setMasterSong: function(id)
		{
			$('#master-song-id').val(id);
			var url = methods.getApiUrl('get-song-data', 'song-ajax');
			var data = {'id': id};

			$.ajax({
				type: 'POST',
				url: url,
				data: data
			})
			.done( function( response ) {
				console.log(response);

				if( response.status == 'ok' ) {

					$('#master-song-block .favorite-header-cont').empty();

					// fill area with data
					$('#master-song-block .favorite-header-cont').append($('<h3/>').text(response.data.favoriteHeader));

					$('#master-song-block .info-cont').empty();
					var date = new Date(response.data.createTime * 1000);
					$('#master-song-block .info-cont').append('<p>Добавлена: ' + $.datepicker.formatDate('dd.mm.yy', date) + '</p>');

					$('#master-song-block .headers-cont').empty();

					if( response.data.headers.length ){
						$('#master-song-block .headers-cont').append($('<h4>Другие названия:</h4>'));

						var headersParent = $('<ol/>');

						$.each(response.data.headers, function(key, item){
							headersParent.append($('<li/>').text(item.title));
						});

						$('#master-song-block .headers-cont').append(headersParent);
					}

					$('#master-song-add-to-concert').show();


				} else {
					alert( 'Произошла ошибка: ' + response.message );
				}
			});

		},

		updateSuggestionLongNotUsed: function()
		{
			var url = methods.getApiUrl('get-suggestion-long-not-used', 'song-ajax');
			$.ajax({
				type: 'POST',
				url: url,
			})
			.done( function( response ) {
				console.log(response);

				if( response.status == 'ok' ) {

					$('#long-not-used-cont .long-not-used').remove();
					$('#long-not-used-cont .header').show();

					var cont = $('<ol class="long-not-used"/>');
					$.each(response.data, function(key, item){
						cont.append($('<li/>').html('<a href="#" class="song" data-id="' + item.id + '">' + item.title + '</a>'));
					});

					$('#long-not-used-cont').append(cont);

				} else {
					alert( 'Произошла ошибка: ' + response.message );
				}

			});
		},

		updateSuggestionTopPopular: function()
		{
			var url = methods.getApiUrl('get-suggestion-top-popular', 'song-ajax');
			var offset = $('#top-popular-cont').data('offset') || 0;

			if( offset >= 100 ) {
				offset = 0;
			}

			$.ajax({
				type: 'POST',
				url: url,
				data: {
					'limit': 10,
					'offset': offset
				}
			})
			.done( function( response ) {
				console.log(response);
				if( response.status == 'ok' ) {
					$('#top-popular-cont').data('offset', offset + 10 );
					$('#top-popular-cont').addClass("b");
					$('#top-popular-cont .top-popular').remove();
					$('#top-popular-cont .header').show();

					var cont = $('<ol class="top-popular" start="' + (offset + 1)+ '"/>');
					$.each(response.data, function(key, item){
						cont.append($('<li/>').html('<a href="#" class="song" data-id="' + item.id + '">' + item.title + '</a> <small class="song-metadata"><span class="amount">' + item.performancesAmount + '</span>, <span class="date">'+item.lastPerformanceTime + '</span></small>'));
					});

					$('#top-popular-cont').append(cont);

				} else {
					alert( 'Произошла ошибка: ' + response.message );
				}
			});
		},

		updateSuggestionUsedLastMonths: function(monthsAmount)
		{
			var url = methods.getApiUrl('get-suggestion-used-last-months', 'song-ajax');
			var offset = $('#used-last-months').data('offset') || 0;

			if( offset >= 100 ) {
				offset = 0;
			}

			var $cont = $('#used-last-months-cont');

			if(typeof($cont.find('.header').data('header-hold')) === 'undefined'){
				$cont.find('.header').data('header-hold', $cont.find('.header').html());
			}

			$.ajax({
				type: 'POST',
				url: url,
				data: {
					'limit': 10,
					'monthsAmount': monthsAmount
				}
			})
				.done( function( response ) {
					console.log(response);
					if( response.status == 'ok' ) {
						$cont.data('offset', offset + 10 );
						$cont.find('.used-last-months').remove();
						$cont.find('.header').html($cont.find('.header').data('header-hold').replace('{amount}', monthsAmount));
						$cont.find('.header').show();

						var $list = $('<ol class="used-last-months" />');
						$.each(response.data, function(key, item){
							$list.append($('<li/>').html('<a href="#" class="song" data-id="' + item.id + '">' + item.title + '</a>'));
						});

						$cont.append($list);

					} else {
						alert( 'Произошла ошибка: ' + response.message );
					}
				});
		}
	}

	// New way for "ready" as of jquery 3.0
	$(function() {

		/*
		 * Creating concert
		 */
		$('#concert-create').on('click', function(){
			// prompt for date
			var html = '<input id="datepicker" name="date_picker" type="text" />';
			$.prompt({ date: {
				title: "Введите дату концерта:",
				html: html,
				buttons: { "Okay": true, "Cancel": false },

				submit: function(e,v,m,f){
					if(!v){
						return;
					} else {
						var url = methods.getApiUrl('create-concert');
						var data = {'date': $('#datepicker').val()}

						// send ajax
						$.ajax({
							type: 'POST',
							url: url,
							data: data
						})
						.done( function( response ) {
							console.log(response);

							if( response.status == 'ok' ) {
								window.location.reload();

							} else {
								alert( 'Произошла ошибка: ' + response.message );
							}
						});

					}

				}
			}});

			var d = new Date();
			var dow = d.getDay();
			var toAdd = dow === 0 ? 0 : 7 - dow;

			$('#datepicker').datepicker( {'defaultDate': toAdd, 'dateFormat': 'dd-mm-yy', 'firstDay': 1});
			$('#datepicker').datepicker("setDate", "+" + toAdd);

		});

		/**
		 * Live search for songs
		 */
		$('#search-song').autocomplete( {

			source: function(request,response) {
				var url = methods.getApiUrl('search-by-header', 'song-ajax');
				var data = {'term': request.term };
				$.ajax ( {
					type: 'POST',
					url: url,
					data: data,
					dataType: 'json',

					success: function(data) {

						window.search_result = data.data;
						return response( $.map( data.data, function( item ) {
							return {
								label: item.title,
								value: item.id
							}
						}));
					},
				}) },
				minLength: 3,
				select: function( event, ui ) {
					var row;
					$.each( window.search_result, function( key, value ) {

						if( value.id == ui.item.value ) {
							methods.setMasterSong(value.id);
						}
					});
					return false;
				},
		});

		$('#master-song-add-to-concert').on('click', function(){
			// get song data
			var songId = $('#master-song-id').val();
			var concertId = $('#concert-id').val();

			var data = {'songId': songId, 'concertId': concertId};
			var url = methods.getApiUrl('create-concert-item');

			// perform request
			$.ajax ( {
				type: 'POST',
				url: url,
				data: data,
				dataType: 'json'})
				.done( function( itemResponse ) {

					if( itemResponse.status == 'ok' ) {
						var url = methods.getApiUrl('get-song-data', 'song-ajax');

						var songId = $('#master-song-id').val();
						var data = {'id': songId };

						$.ajax({
							type: 'POST',
							url: url,
							data: data
						})
						.done( function( dataResponse ) {
							// append this data to list
							$('#concert-items').append($('<li/>')
									.html(dataResponse.data.favoriteHeader + ' <a href="#" class="concert-item-delete" data-id="' + itemResponse.data.id + '">[Уд.]</a>')
									.attr({'class': 'concert-item', 'data-id': itemResponse.data.id}));
						});

					} else {
						alert( 'Произошла ошибка: ' + response.message );
					}

				});
		});

		$(document).on('click', '.song', function(e){
			methods.setMasterSong($(this).data('id'));
		});

		$(document).on('click', '.concert-item-delete', function(e){
			e.preventDefault();

			var id = $(this).data('id');
			var url = methods.getApiUrl('delete-concert-item');
			var data = {'id': id};
			// perform request
			$.ajax ( {
				type: 'POST',
				url: url,
				data: data,
				dataType: 'json'})
				.done( function( response ) {
					$('.concert-item[data-id=' + response.data.id + ']').remove();
				});
		});


		$('#search-song').focus();
		methods.updateSuggestionLongNotUsed();
		methods.updateSuggestionTopPopular();
		methods.updateSuggestionUsedLastMonths(3);

		window.setInterval(methods.updateSuggestionLongNotUsed, 20000);
		window.setInterval(methods.updateSuggestionTopPopular, 20000);
		window.setInterval(function(){ methods.updateSuggestionUsedLastMonths(3) }, 20000);

		// drag-n-drop
		$("#concert-items").sortable({'onDrop':
			function ($item, container, _super, event) {
				$item.removeClass("dragged").removeAttr("style")
				$("body").removeClass("dragging");
				var items = container.serialize();
				var ids = [];
				$.each(items[0], function(key, obj){
					ids.push(obj.id);
				});

				var data = {'concertItemIds': ids, 'concertId': $('#concert-id').val() };
				var url = methods.getApiUrl('reorder', 'concert-ajax');

				$.ajax({
					type: 'POST',
					url: url,
					data: data
				})
				.done( function( response ) {
					if( response.status == 'ok' ) {
						// just okay
						;
					} else {
						alert( 'Произошла ошибка: ' + response.message );
					}
				});
		}});
	})

})(jQuery);
