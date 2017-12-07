/**
 *
 */

SongbookUiEvents = {
	INIT: 'songbook:init',
	GET_METHOD: 'songbook:getMethod',
	SHOW_CONCERT_CREATE_WINDOW: 'songbook:showConcertCreateWindow',
	SHOW_CONCERT_GROUP_CREATE_WINDOW: 'songbook:showConcertGroupCreateWindow',
	INIT_SEARCH_SONG_AUTOCOMPLETE: 'songbook:initSearchSongAutocomplete',
	INIT_CONCERT_COMPOSITE: 'songbook:initConcertComposite',
	INIT_CONTENT_VIDEO: 'songbook:initContentVideo',
	INIT_CONTENT_PROCESS: 'songbook:initContentProcess',
	ADD_CONTENT_VIDEO: 'songbook:addContentVideo',
	REMOVE_CONTENT_VIDEO: 'songbook:removeContentVideo',
	INIT_CONCERT_SUGGESTIONS: 'songbook:initConcertSuggestions',
	DELETE_CONCERT_GROUP: 'songbook:deleteConcertGroup',
	CONCERT_ITEM_SELECT: 'songbook:concertItemSelect',
	ADD_SONG_TO_CONCERT: 'songbook:addSongToConcert',
	ADD_CONCERT_ITEM_INTO_CONCERT_GROUP: 'songbook:addConcertItemIntoConcertGroup',
	DELETE_CONCERT_ITEM_FROM_CONCERT_GROUPS: 'songbook:deleteConcertItemFromConcertGroups',
	OPEN_SONG_VIEW: 'songbook:openSongView',
	CONTENT_ITEM_SELECT: 'songbook:contentItemSelect',
	CONTENT_ITEM_DESELECT: 'songbook:contentItemDeSelect',
	CONTENT_FUNCTIONAL_TYPE_SELECT: 'songbook:contentFunctionalTypeSelect',
	CONTENT_EMAIL_COMPOSE_OPEN: 'songbook:contentEmailComposeOpen',
	CONTENT_PDF_COMPILE: 'songbook:contentPdfCompile'
};

SongbookContentTypes = {
	HEADER: 'header',
	GDRIVE_CLOUD_FILE: 'gdrive_cloud_file',
	INLINE: 'inline',
	LINK: 'link'
};

SongbookContentLinkServices = {
	YOUTUBE: 'youtube',
	GODTUBE: 'godtube'
};

SongbookContentFunctionalTypes = {
	ALL: 1,
	LYRICS: 2,
	PRESENTATIONS: 3,
	AUDIO: 4,
	VIDEO: 5,
	LYRICS_PDFS: 6,
};

(function ($) {
	"use strict";

	$.fn.songbook_ui = function () {

		$(document).on(SongbookUiEvents.INIT, function (e) {
			methods.init.call(e.target);
		});

		$(document).on(SongbookUiEvents.OPEN_SONG_VIEW, function (e, songId) {
			methods.openSongView.call(e.target, songId);
		});

		$(document).on(SongbookUiEvents.SHOW_CONCERT_CREATE_WINDOW, function (e) {
			methods.showConcertCreateWindow.call(e.target);
		});

		$(document).on(SongbookUiEvents.SHOW_CONCERT_GROUP_CREATE_WINDOW, function (e) {
			methods.showConcertGroupCreateWindow.call(e.target);
		});

		$(document).on(SongbookUiEvents.INIT_SEARCH_SONG_AUTOCOMPLETE, function (e) {
			methods.initSearchSongAutocomplete.call(e.target);
		});

		$(document).on(SongbookUiEvents.INIT_CONTENT_VIDEO, function (e) {
			methods.initContentVideo.call(e.target);
		});

		$(document).on(SongbookUiEvents.ADD_CONTENT_VIDEO, function (e, videoUrl, songId) {
			methods.addContentVideo.call(e.target, videoUrl, songId);
		});

		$(document).on(SongbookUiEvents.REMOVE_CONTENT_VIDEO, function (e, id) {
			methods.removeContentVideo.call(e.target, id);
		});

		$(document).on(SongbookUiEvents.INIT_CONCERT_COMPOSITE, function (e) {
			methods.initConcertComposite.call(e.target);
		});

		$(document).on(SongbookUiEvents.INIT_CONCERT_SUGGESTIONS, function (e) {
			methods.initConcertSuggestions.call(e.target);
		});

		$(document).on(SongbookUiEvents.DELETE_CONCERT_GROUP, function (e) {
			methods.deleteConcertGroup.call(e.target);
		});

		$(document).on(SongbookUiEvents.CONCERT_ITEM_SELECT, function (e) {
			methods.concertItemSelect.call(e.target);
		});

		$(document).on(SongbookUiEvents.ADD_SONG_TO_CONCERT, function (e) {
			methods.addSongToConcert.call(e.target);
		});

		$(document).on(SongbookUiEvents.ADD_CONCERT_ITEM_INTO_CONCERT_GROUP, function (e, concertItemId, concertGroupId) {
			methods.addConcertItemIntoConcertGroup.call(e.target, concertItemId, concertGroupId);
		});

		$(document).on(SongbookUiEvents.DELETE_CONCERT_ITEM_FROM_CONCERT_GROUPS, function (e, concertItemId) {
			methods.deleteConcertItemFromConcertGroups.call(e.target, concertItemId);
		});

		$(document).on(SongbookUiEvents.INIT_CONTENT_PROCESS, function (e) {
			methods.initContentProcess.call(e.target);
		});


		$(document).on(SongbookUiEvents.CONTENT_ITEM_SELECT, function (e) {
			methods.contentItemSelect.call(e.target);
		});

		$(document).on(SongbookUiEvents.CONTENT_ITEM_DESELECT, function (e) {
			methods.contentItemDeSelect.call(e.target);
		});

		$(document).on(SongbookUiEvents.CONTENT_FUNCTIONAL_TYPE_SELECT, function (e) {
			methods.contentFunctionalTypeSelect.call(e.target);
		});

		$(document).on(SongbookUiEvents.CONTENT_EMAIL_COMPOSE_OPEN, function (e) {
			methods.contentEmailComposeOpen.call(e.target);
		});

		$(document).on(SongbookUiEvents.CONTENT_PDF_COMPILE, function (e) {
			methods.contentPdfCompile.call(e.target);
		});

		var methods = {
			init: function () {

				if ($('#search-song').length > 0) {
					$('#search-song').trigger(SongbookUiEvents.INIT_SEARCH_SONG_AUTOCOMPLETE);
				}

				if ($('#content_video_action_add').length > 0) {
					$(document).trigger(SongbookUiEvents.INIT_CONTENT_VIDEO);
				}

				if ($('.concert_composite_block').length > 0) {
					$(document).trigger(SongbookUiEvents.INIT_CONCERT_COMPOSITE);
					$(document).trigger(SongbookUiEvents.INIT_CONCERT_SUGGESTIONS);
				}

				if ($('.content_process_block').length > 0) {
					$(document).trigger(SongbookUiEvents.INIT_CONTENT_PROCESS);
				}
			},

			initSearchSongAutocomplete: function () {
				/**
				 * Live search for songs
				 */
				$(this).autocomplete({

					source: function (request, response) {
						var url = methods.getApiUrl('search-by-header', 'song-ajax');
						var data = {'term': request.term};
						$.ajax({
							type: 'POST',
							url: url,
							data: data,
							dataType: 'json',

							success: function (data) {

								window.search_result = data.data;
								return response($.map(data.data, function (item) {
									return {
										label: item.title,
										value: item.id
									}
								}));
							}
						})
					},
					minLength: 3,
					select: function (event, ui) {
						var row;
						$.each(window.search_result, function (key, value) {

							if (value.id == ui.item.value) {
								methods.setMasterSong(value.id);
							}
						});
						return false;
					},
				});
			},

			initContentVideo: function () {
				$('#content_video_action_add').on('click', function () {
					// take url
					$('#content_video_list').trigger(SongbookUiEvents.ADD_CONTENT_VIDEO, [$.trim($('#content_video').val()), $('#song_id').val()]);
				});

				$('#content_video_list').on('click', '.control_remove', function () {
					$('#content_video_list').trigger(SongbookUiEvents.REMOVE_CONTENT_VIDEO, [$(this).closest('.list_item').data('content-id')]);
				});
			},

			addContentVideo: function (videoUrl, songId) {
				var $container = $(this);
				if (videoUrl.length.length === 0) {
					return;
				}

				var re = /^https?:\/\/.+$/;

				if (!re.test(videoUrl)) {
					alert('Неверный формат URL!');
					return;
				}

				// call ajax method for content adding with "type" param
				$.ajax({
					type: 'POST',
					url: '/content-ajax/add-content',
					data: {
						'type': 'link',
						'content': videoUrl,
						'song_id': songId

					}
				}).pipe(function (response) {
					var data = response.data;

					// on success - call to get embed code
					$.ajax({
						type: 'GET',
						url: '/content-ajax/get-content-link-embed-code/' + data.content.id
					}).pipe(function (response) {
						// on success, take list and add given url there
						var embedData = response.data;

						var $item = $('<li class="list_item" data-content-id="' + data.content.id + '"><div class="video-container">' + embedData.link + '</div><span class="control control_remove fa fa-trash"></span></li>');
						$container.append($item);

					}, function (errorResponse) {
						alert('Something went wrong while getting content of added video');
					});

				}, function (errorResponse) {
					alert('Something went wrong while adding a video');
				});
			},


			removeContentVideo: function (id) {
				var $container = $(this);
				if (confirm('Вы действительно хотите удалить это видео?')) {
					$.ajax({
						type: 'DELETE',
						url: '/content-ajax/remove-content/' + id
					}).pipe(function () {
						$container.find('.list_item[data-content-id=' + id + ']').remove();

					}, function (errorResponse) {
						alert('Something went wrong while removing content video');
					});
				}
			},

			openSongView: function (songId) {
				window.location.href = '/song/view/' + songId;
			},

			initConcertComposite: function () {
				$('#search-song').focus();

				$('#master-song-add-to-concert').on('click', function () {
					$(this).trigger(SongbookUiEvents.ADD_SONG_TO_CONCERT);
				});

				$(document).on('click', '.song', function (e) {
					methods.setMasterSong.call(this, $(this).data('id'));
				});

				$(document).on('click', '#concert-group-delete', function (e) {
					$(this).trigger(SongbookUiEvents.DELETE_CONCERT_GROUP);
				});

				$(document).on('click', '.song-view', function () {
					$(this).trigger(SongbookUiEvents.OPEN_SONG_VIEW, [$(this).data('id')]);
				});

				$(document).on('click', '.concert-item-delete', function (e) {
					e.preventDefault();

					var id = $(this).data('id');
					var url = methods.getApiUrl('delete-concert-item');
					var data = {'id': id};
					// perform request
					$.ajax({
						type: 'POST',
						url: url,
						data: data,
						dataType: 'json'
					})
						.done(function (response) {
							$('.concert-item[data-id=' + response.data.id + ']').remove();
						});
				});


				var concertItemsSortable = $(".concert-items").sortable({
					group: 'concert-items',
					handle: '.drag-handle',
					delay: 500,
					'serialize': function ($parent, $children, parentIsContainer) {

						if (parentIsContainer) {
							return $children;
						} else {
							if (!$parent.hasClass('container')) {
								return [$parent];
							} else {
								return $children;
							}
						}


					},
					'onDrop': function ($item, container, _super, event) {
						$item.removeClass("dragged").removeAttr("style")
						$("body").removeClass("dragging");

						_super($item, container);

						var items = concertItemsSortable.sortable("serialize");

						var ids = [];

						$.each(items, function (key, $item) {
							ids.push($item.data('id'));
						});

						var $container = $(container.el);


						if ($container.hasClass('group-container')) {
							if (parseInt($item.data('concert-group-id')) !== parseInt($container.data('concert-group-id'))) {
								console.log('add item into group');
								$(document).trigger(SongbookUiEvents.ADD_CONCERT_ITEM_INTO_CONCERT_GROUP, [$item.data('id'), $container.data('concert-group-id')]);
							}


						} else {
							$(document).trigger(SongbookUiEvents.DELETE_CONCERT_ITEM_FROM_CONCERT_GROUPS, [$item.data('id')]);
							console.log('remove item from group');

							// remove empty container :)
							$container.find('.group-container:not(:has(li))').remove();
						}

						methods.concertItemsReorder(ids);
					}
				});

				$('.concert-item').addClass('draggable');


				$(document).on('click', '.concert-item', function (e) {
					if (!$(e.target).hasClass('control')) {
						$(this).trigger(SongbookUiEvents.CONCERT_ITEM_SELECT);
					}
				});

				// init concert create window
				$(document).on('click', '#concert-create', function () {
					$(document).trigger(SongbookUiEvents.SHOW_CONCERT_CREATE_WINDOW);
				});

				// init concert group create window
				$(document).on('click', '#concert-group-create', function (e) {
					$(document).trigger(SongbookUiEvents.SHOW_CONCERT_GROUP_CREATE_WINDOW);
				});
			},

			concertItemsReorder: function (ids) {
				var data = {'concertItemIds': ids, 'concertId': $('#concert-id').val()};
				var url = methods.getApiUrl('reorder', 'concert-ajax');

				$.ajax({
					type: 'POST',
					url: url,
					data: data
				})
					.done(function (response) {
						if (response.status == '200') {
							// just okay
							;
						} else {
							alert('Произошла ошибка: ' + response.message);
						}
					});
			},

			concertItemSelect: function () {
				//console.log(this);
				//console.log(e);
				if (!$(this).hasClass('selected')) {
					$(this).addClass('selected');
				} else {
					$(this).removeClass('selected');
				}
			},

			initContentProcess: function () {
				// init selecting of rows
				$('.concert_items .content_items li').on('click', function () {
					if($(this).data('selected') === 1){
						$(this).trigger(SongbookUiEvents.CONTENT_ITEM_DESELECT);
					} else {
						$(this).trigger(SongbookUiEvents.CONTENT_ITEM_SELECT);
					}

				});

				$('.functional_type_filter').on('change', function() {
					$(this).trigger(SongbookUiEvents.CONTENT_FUNCTIONAL_TYPE_SELECT);
				});

				$('#content_email_compose').on('click', function(){

					$('.concert_items').trigger(SongbookUiEvents.CONTENT_EMAIL_COMPOSE_OPEN);

				});

				$('#content_pdf_compile').on('click', function(){

					$('.concert_items').trigger(SongbookUiEvents.CONTENT_PDF_COMPILE);

				});
			},

			contentItemSelect: function () {
					$(this).data('selected', 1);
					$(this).addClass('selected');
			},

			contentItemDeSelect: function () {
					$(this).data('selected', 0);
					$(this).removeClass('selected');
			},

			contentItemsGetSelected: function() {
				return $('.content_items li').filter(function() {
					return $(this).data("selected") === 1;
				});
			},

			contentFunctionalTypeSelect: function () {
				var value = parseInt($(this).val());

				switch(value){
					default:
						$('.concert_items .content_items li').trigger(SongbookUiEvents.CONTENT_ITEM_DESELECT);
						break;
					case(SongbookContentFunctionalTypes.ALL):
					$('.concert_items .content_items li').trigger(SongbookUiEvents.CONTENT_ITEM_DESELECT);
					$('.concert_items .content_items li').trigger(SongbookUiEvents.CONTENT_ITEM_SELECT);
						break;

					case(SongbookContentFunctionalTypes.LYRICS):
						$('.concert_items .content_items li').trigger(SongbookUiEvents.CONTENT_ITEM_DESELECT);
						$('.concert_items .content_items li[data-content-type=' + SongbookContentTypes.GDRIVE_CLOUD_FILE +'][data-content-mime-type="application/vnd.oasis.opendocument.text"], '+
							'.concert_items .content_items li[data-content-type=' + SongbookContentTypes.GDRIVE_CLOUD_FILE +'][data-content-mime-type="application/vnd.google-apps.document"], '+
							'.concert_items .content_items li[data-content-type=' + SongbookContentTypes.GDRIVE_CLOUD_FILE +'][data-content-mime-type="application/msword"]'
						).trigger(SongbookUiEvents.CONTENT_ITEM_SELECT);
						break;
					case(SongbookContentFunctionalTypes.PRESENTATIONS):
						$('.concert_items .content_items li').trigger(SongbookUiEvents.CONTENT_ITEM_DESELECT);
						$('.concert_items .content_items li[data-content-type=' + SongbookContentTypes.GDRIVE_CLOUD_FILE +'][data-content-mime-type="application/vnd.google-apps.presentation"], '+
							'.concert_items .content_items li[data-content-type=' + SongbookContentTypes.GDRIVE_CLOUD_FILE +'][data-content-mime-type="application/vnd.ms-powerpoint"]'
						).trigger(SongbookUiEvents.CONTENT_ITEM_SELECT);
						break;

					case(SongbookContentFunctionalTypes.AUDIO):
						$('.concert_items .content_items li').trigger(SongbookUiEvents.CONTENT_ITEM_DESELECT);
						$('.concert_items .content_items li[data-content-type=' + SongbookContentTypes.GDRIVE_CLOUD_FILE +'][data-content-mime-type="audio/mpeg"], '+
							'.concert_items .content_items li[data-content-type=' + SongbookContentTypes.GDRIVE_CLOUD_FILE +'][data-content-mime-type="audio/ogg"]').trigger(SongbookUiEvents.CONTENT_ITEM_SELECT);
						break;
					case(SongbookContentFunctionalTypes.VIDEO):
						$('.concert_items .content_items li').trigger(SongbookUiEvents.CONTENT_ITEM_DESELECT);
						$('.concert_items .content_items li[data-content-type=' + SongbookContentTypes.LINK +'][data-content-link-service="' + SongbookContentLinkServices.YOUTUBE + '"], '+
								'.concert_items .content_items li[data-content-type=' + SongbookContentTypes.LINK +'][data-content-link-service="' + SongbookContentLinkServices.GODTUBE + '"]').trigger(SongbookUiEvents.CONTENT_ITEM_SELECT);
						break;
					case(SongbookContentFunctionalTypes.LYRICS_PDFS):
						$('.concert_items .content_items li').trigger(SongbookUiEvents.CONTENT_ITEM_DESELECT);
						$('.concert_items .content_items li[data-content-type=' + SongbookContentTypes.GDRIVE_CLOUD_FILE +'][data-content-mime-type="application/pdf"]').trigger(SongbookUiEvents.CONTENT_ITEM_SELECT);
						break;
				}
			},

			contentEmailComposeOpen: function() {
				// get selected
				var selected = methods.contentItemsGetSelected.call(this);

				var concertId = $('#concert_id').val();

				var ids = [];
				selected.each(function(){
					ids.push($(this).data('content-id'));
				});

				var params = {'concert_id': concertId};

				if(ids.length >0){
					params['content_ids'] = ids.join(',');
				}
				// format url

				var url = methods.getUrl('content', 'email-compose', params);
				window.location.href = url;
			},

			contentPdfCompile: function() {
				var selected = methods.contentItemsGetSelected.call(this);
				var concertId = $('#concert_id').val();

				var ids = [];
				selected.each(function(){
					ids.push($(this).data('content-id'));
				});

				if(ids.length === 0){
					return;
				}

				var url = methods.getUrl('content', 'pdf-compile', {'content_ids': ids.join(','), 'concert_id': concertId});
				window.location.href = url;
			},

			addSongToConcert: function () {
				// get song data
				var songId = $('#master-song-id').val();
				var concertId = $('#concert-id').val();

				$.when(methods.getSongData(songId), methods.createConcertItem(songId, concertId))
					.done(function (songData, concertItemData) {
						var tpl = '<li class="concert-item draggable" data-id="{concertItemId}"><div class="content">{name}<span class="item-controls"><span class="control song-view fa fa-eye" data-id="{songId}" aria-hidden="true"></span> <span class="control fa fa-trash-o concert-item-delete" data-id="{concertItemId}" aria-hidden="true"></span> <span class="control drag-handle fa fa-bars" aria-hidden="true"></span></span></div></li>';
						tpl = methods.parseVars(tpl, {
							'name': songData.favoriteHeader,
							'concertItemId': concertItemData.id,
							'songId': songData.id
						});

						$('.concert-items').append($($(tpl)));
					}).fail(function (xhr) {
					console.error('Some of requests failed');
				});
			},

			initConcertSuggestions: function () {
				methods.updateSuggestionLongNotUsed();
				methods.updateSuggestionTopPopular();
				methods.updateSuggestionUsedLastMonths(3);
        methods.updateSuggestionTakenLastMonths(36);

				window.setInterval(methods.updateSuggestionLongNotUsed, 20000);
				window.setInterval(methods.updateSuggestionTopPopular, 20000);
				window.setInterval(function () {
					methods.updateSuggestionUsedLastMonths(3)
				}, 20000);

        window.setInterval(function () {
          methods.updateSuggestionTakenLastMonths(36)
        }, 20000);

			},


			addConcertItemIntoConcertGroup: function (concertItemId, concertGroupId) {
				var url = methods.getApiUrl('add-concert-item-into-concert-group');
				var data = {'concertItemId': concertItemId, 'concertGroupId': concertGroupId};

				var xhr = $.ajax({
					type: 'POST',
					url: url,
					data: data
				})
					.pipe(function (response) {
						console.log(response);

						if (response.status == '200') {
							return true;

						} else {
							return false;
						}
					}).done(function (result) {
						if (result) {
							console.log('200');
						} else {
							console.log('failed');
						}
					})
					.fail(function (xhr) {
						methods.processAjaxFail(xhr);
					});

				return xhr;
			},

			deleteConcertItemFromConcertGroups: function (id) {
				var url = methods.getApiUrl('delete-concert-item-from-concert-groups');
				var data = {'concertItemId': id};

				var xhr = $.ajax({
					type: 'POST',
					url: url,
					data: data
				})
					.pipe(function (response) {
						console.log(response);

						if (response.status === '200') {
							return true;

						} else {
							return false;
						}
					})
					.done(function (result) {
						if (result) {
							console.log('200');
						} else {
							console.log('failed');
						}
					})
					.fail(function (xhr) {
						methods.processAjaxFail(xhr);
					});

				return xhr;
			},

			showConcertGroupCreateWindow: function () {
				var $items = $('.concert-item.selected');
				var concertItemsIds = $items.map(function () {
					return $(this).data('id');
				}).get();


				if (concertItemsIds.length == 0) {
					console.error('Zero items selected');
				} else {
					var html = '<input id="concert-group-name" class="form-control" type="text" />';

					$.prompt({
						date: {
							title: "Введите название группы:",
							html: html,
							buttons: {"Okay": true, "Cancel": false},

							submit: function (e, v, m, f) {
								if (!v) {
									return;
								} else {
									var url = methods.getApiUrl('create-concert-group');
									var data = {
										'concertGroupName': $('#concert-group-name').val(),
										'concertItemsIds': concertItemsIds,
										'concertId': $('#concert-id').val()
									};

									// send ajax
									$.ajax({
										type: 'POST',
										url: url,
										data: data
									})
										.done(function (response) {

											if (response.status == '200') {
												window.location.reload();

											} else {
												alert('Произошла ошибка: ' + response.message);
											}
										});

								}

							}
						}
					});
				}
			},

			showConcertCreateWindow: function () {
				// prompt for date
				var html = '<input id="datepicker" name="date_picker" type="text" />';
				$.prompt({
					date: {
						title: "Введите дату концерта:",
						html: html,
						buttons: {"Okay": true, "Cancel": false},

						submit: function (e, v, m, f) {
							if (!v) {
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
									.done(function (response) {
										console.log(response);

										if (response.status == '200') {
											window.location.reload();

										} else {
											alert('Произошла ошибка: ' + response.message);
										}
									});

							}

						}
					}
				});

				var d = new Date();
				var dow = d.getDay();
				var toAdd = dow === 0 ? 0 : 7 - dow;

				$('#datepicker').datepicker({'defaultDate': toAdd, 'dateFormat': 'dd-mm-yy', 'firstDay': 1});
				$('#datepicker').datepicker("setDate", "+" + toAdd);

			},

			// successor of "getApiUrl"

			/**
			 * @param string controller
			 * @param string action
			 * @param Object params format {id: 1, name: 2}
			 * @param Object patchParams format {id: true}
			 * @returns {string}
			 */
			getUrl: function(controller, action, params, patchParams){
				var url = '/' + controller + '/' + action;

				if(typeof(params) !== 'undefined'){

					var paramsValues = [];
					var processedParams = {};
					if(typeof(patchParams) !== 'undefined'){
						for(var name in patchParams){

							if(typeof(params[name]) !== 'undefined') {
								processedParams[name] = true;

								if(typeof(params[name]).toString().toLowerCase() === 'object'){
									paramsValues.push(params[name].join(','));
								} else {
									paramsValues.push(params[name]);
								}

							}
						}
					}

					if(paramsValues.length > 0){
						url += '/' + paramsValues.join('/');
					}

					var getParams = [];
					for(var name in params){

						if(typeof(processedParams[name]) === 'undefined'){

							if(typeof(params[name]).toString().toLowerCase() === 'object'){
								getParams.push(name + '=' + params[name].join(','));
							} else {
								getParams.push(name + '=' + params[name]);
							}
						}
					}

					if(getParams.length > 0){
						url += '?' + getParams.join('&');
					}
				}

				return url;
			},

			getApiUrl: function (action, controller) {
				if (controller == undefined) {
					controller = 'concert-ajax';
				}

				return '/' + controller + '/' + action;
			},

			setMasterSong: function (id) {
				$('#master-song-id').val(id);
				var url = methods.getApiUrl('get-song-data', 'song-ajax');
				var data = {'id': id};

				$.ajax({
					type: 'POST',
					url: url,
					data: data
				})
					.done(function (response) {
						console.log(response);

						if (response.status == '200') {

							$('#master-song-block .favorite-header-cont').empty();

							// fill area with data
							$('#master-song-block .favorite-header-cont').append($('<h3/>').text(response.data.favoriteHeader));

							$('#master-song-block .info-cont').empty();
							var date = new Date(response.data.createTime * 1000);
							$('#master-song-block .info-cont').append('<p>Добавлена: ' + $.datepicker.formatDate('dd.mm.yy', date) + '</p>');

							$('#master-song-block .headers-cont').empty();

							if (response.data.headers.length) {
								$('#master-song-block .headers-cont').append($('<h4>Другие названия:</h4>'));

								var headersParent = $('<ol/>');

								$.each(response.data.headers, function (key, item) {
									headersParent.append($('<li/>').text(item.title));
								});

								$('#master-song-block .headers-cont').append(headersParent);
							}

							$('#master-song-add-to-concert').show();


						} else {
							alert('Произошла ошибка: ' + response.message);
						}
					});

			},

			updateSuggestionLongNotUsed: function () {
				var url = methods.getApiUrl('get-suggestion-long-not-used', 'song-ajax');
				$.ajax({
					type: 'POST',
					url: url,
				})
					.done(function (response) {
						console.log(response);

						if (response.status == '200') {

							$('#long-not-used-cont .long-not-used').remove();
							$('#long-not-used-cont .header').show();

							var cont = $('<ol class="long-not-used"/>');
							$.each(response.data, function (key, item) {
								cont.append($('<li/>').html('<a href="#" class="song" data-id="' + item.id + '">' + item.title + '</a>'));
							});

							$('#long-not-used-cont').append(cont);

						} else {
							alert('Произошла ошибка: ' + response.message);
						}

					});
			},

			updateSuggestionTopPopular: function () {
				var url = methods.getApiUrl('get-suggestion-top-popular', 'song-ajax');
				var offset = $('#top-popular-cont').data('offset') || 0;

				if (offset >= 100) {
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
					.done(function (response) {
						console.log(response);
						if (response.status == '200') {
							$('#top-popular-cont').data('offset', offset + 10);
							$('#top-popular-cont').addClass("b");
							$('#top-popular-cont .top-popular').remove();
							$('#top-popular-cont .header').show();

							var cont = $('<ol class="top-popular" start="' + (offset + 1) + '"/>');
							$.each(response.data, function (key, item) {
								cont.append($('<li/>').html('<a href="#" class="song" data-id="' + item.id + '">' + item.title + '</a> <small class="song-metadata"><span class="amount">' + item.performancesAmount + '</span>, <span class="date">' + item.lastPerformanceTime + '</span></small>'));
							});

							$('#top-popular-cont').append(cont);

						} else {
							alert('Произошла ошибка: ' + response.message);
						}
					});
			},

			updateSuggestionUsedLastMonths: function (monthsAmount) {
				var url = methods.getApiUrl('get-suggestion-used-last-months', 'song-ajax');
				var offset = $('#used-last-months').data('offset') || 0;

				if (offset >= 100) {
					offset = 0;
				}

				var $cont = $('#used-last-months-cont');

				if (typeof($cont.find('.header').data('header-hold')) === 'undefined') {
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
					.done(function (response) {
						console.log(response);
						if (response.status == '200') {
							$cont.data('offset', offset + 10);
							$cont.find('.used-last-months').remove();
							$cont.find('.header').html($cont.find('.header').data('header-hold').replace('{amount}', monthsAmount));
							$cont.find('.header').show();

							var $list = $('<ol class="used-last-months" />');
							$.each(response.data, function (key, item) {
								$list.append($('<li/>').html('<a href="#" class="song" data-id="' + item.id + '">' + item.title + '</a>'));
							});

							$cont.append($list);

						} else {
							alert('Произошла ошибка: ' + response.message);
						}
					});
			},

      updateSuggestionTakenLastMonths: function (monthsAmount) {
        var url = methods.getApiUrl('get-suggestion-taken-last-months', 'song-ajax');
        var offset = $('#taken-last-months').data('offset') || 0;

        if (offset >= 100) {
          offset = 0;
        }

        var $cont = $('#taken-last-months-cont');

        if (typeof($cont.find('.header').data('header-hold')) === 'undefined') {
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
          .done(function (response) {
            console.log(response);
            if (response.status == '200') {
              $cont.data('offset', offset + 10);
              $cont.find('.taken-last-months').remove();
              $cont.find('.header').html($cont.find('.header').data('header-hold').replace('{amount}', monthsAmount));
              $cont.find('.header').show();

              var $list = $('<ol class="taken-last-months" />');
              $.each(response.data, function (key, item) {
                $list.append($('<li/>').html('<a href="#" class="song" data-id="' + item.id + '">' + item.title + '</a>'));
              });

              $cont.append($list);

            } else {
              alert('Произошла ошибка: ' + response.message);
            }
          });
      },

			deleteConcertGroup: function () {
				var $item = $('.concert-item.selected');
				if ($item.length == 0) {
					alert('Не выбраны элементы');
				} else {
					var id = $item.data('concert-group-id');

					if (!id) {
						return false;
					}

					var url = methods.getApiUrl('delete-concert-group', 'concert-ajax');
					$.ajax({
						type: 'POST',
						url: url,
						data: {
							'id': id
						}
					}).done(function (response) {
						if (response.status == '200') {
							window.location.reload();

						} else {
							alert('Произошла ошибка: ' + response.message);
						}
					});
				}
			},

			createConcertItem: function (songId, concertId) {
				var data = {'songId': songId, 'concertId': concertId};
				var url = methods.getApiUrl('create-concert-item');

				// perform request
				var xhr = $.ajax({
					type: 'POST',
					url: url,
					data: data
				}).pipe(function (response) {
					return response.data;
				}).fail(function (xhr) {
					console.error('Unable to create concert item');
				});

				return xhr;
			},


			getSongData: function (id) {
				var url = methods.getApiUrl('get-song-data', 'song-ajax');
				var data = {'id': id};

				var xhr = $.ajax(
					{
						'url': url,
						'type': 'POST',
						'data': data
					})
					.pipe(function (response) {
						return response.data;
					}).fail(function (xhr) {
						console.error('Unable to retrieve song info');
					});

				return xhr;
			},

			parseVars: function (template, valuesMap) {
				var content = template;
				$.each(valuesMap, function (key, value) {
					var re = new RegExp('{' + key + '}', 'g');
					content = content.replace(re, value);
				});

				return content;
			},

			processAjaxFail: function (xhr) {
				var data = xhr.responseJSON;
				var $form = this;

				if (typeof(data) !== 'undefined' && typeof(data.type) !== 'undefined') {
					if (data.type === 'exception') {
						alert(data.data.message);
						console.error(data.data);

					} else if (data.type === 'validation-errors') {
						alert('Validation error');
						//methods.processValidationErrorsResponse.call($form, data.data);

					} else if (data.type === 'error') {
						alert(data.data.message);
						console.error(data.data);

					} else {
						alert('Unknown error occured');
					}
				} else {
					alert('Unknown error occured, unable to load data');
				}
			},

			getMethod: function (method) {
				if (typeof(methods[method]) !== 'undefined') {
					this.method = methods[method];
				}
			}

		};
	};

	$.fn.songbook_ui.call();

	$.fn.songbook_ui_method = function (method) {
		var event = jQuery.Event(SongbookUiEvents.GET_METHOD);
		$(document).trigger(event, [method]);
		if (typeof(event.method) !== 'undefined') {
			return event.method;
		} else {
			console.error('AppUi does not contains the method "' + method + '"')
		}
	}


})(jQuery);
