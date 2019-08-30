jQuery(document).ready(function ($) {
    class Search {
        //1. describe and create/initiate our object
        constructor() {
            this.addSearchHTML();
            this.openButton = $('.js-search-trigger');
            this.closeButton = $('.search-overlay__close');
            this.serachOverlay = $('.search-overlay');
            this.searchInput = $('#search-item');
            this.resultsDiv = $('#search-overlay__results');
            this.events();
            this.isOpen = false;
            this.previousValue;
            this.isSpinnerVisible = false;
            this.timer;
        };

        // 2. events
        events() {
            this.openButton.on('click', this.openOverlay.bind(this));
            this.closeButton.on('click', this.closeOverlay.bind(this));
            $(document).on('keydown', this.keyPressDispatcher.bind(this));
            $(this.searchInput).on('keyup', this.typingLogic.bind(this));
        };


        // 3. methods (function, action..)
        typingLogic() {
            if (this.searchInput.val() != this.previousValue) {
                clearTimeout(this.timer);
                if (this.searchInput.val() != '') {
                    if (!this.isSpinnerVisible) {
                        this.resultsDiv.html('<div class="spinner-loader"></div>');
                        this.isSpinnerVisible = true;
                    }
                    this.timer = setTimeout(this.getResults.bind(this), 750);
                } else {
                    this.isSpinnerVisible = false;
                    this.resultsDiv.html('');
                };
            };

            this.previousValue = this.searchInput.val();
        };
        getResults() {

            $.getJSON(collegeData.root_url + '/wp-json/college/v1/search?key=' + this.searchInput.val(), results => {
                this.resultsDiv.html(`
                    <div class="row">
                        <div class="one-third">
                            <h2 class="search-overlay__section-title">General Information </h2>
                            ${results.generalInfo.length ? '<ul class="link-list min-list">' : '<p>No general information matches that search.</p>'}
                            ${results.generalInfo.map(post => `<li><a href='${post.url}'>${ post.title} </a>  ${post.postType == 'post' ? `by ${post.authorName}`: ''}</li>`).join('')}
                            ${results.generalInfo.length ? '</ul>' : ''}
                        </div>
                        <div class="one-third">
                            <h2 class="search-overlay__section-title">Events</h2>
                            ${results.events.length ? '' : `<p>No events matches that search.<a href="${collegeData.root_url}/events">View all events</a></p>`}
                            ${results.events.map(event => `
                                <div class="event-summary">
                                    <a class="event-summary__date t-center" href="${event.url}">
                                            <span class="event-summary__month">${event.month}</span>
                                            <span class="event-summary__day">${event.day}</span>  
                                          </a>
                                    <div class="event-summary__content">
                                        <h5 class="event-summary__title headline headline--tiny">
                                            <a href="${event.url}">
                                                ${event.title}
                                            </a>
                                        </h5>
                                        <p>
                                            ${event.description}
                                           <a href="${event.url}" class="nu gray">Learn more</a></p>
                                    </div>
                                </div>

                            
                            `).join('')}
                            <h2 class="search-overlay__section-title">Programs</h2>
                            ${results.programs.length ? '<ul class="link-list min-list">' : `<p>No programs matches that search.<a href="${collegeData.root_url}/programs">View all programs</a></p>`}
                            ${results.programs.map(program => `<li><a href="${program.url}"> ${program.title}</a></li>`).join('')}
                            ${results.programs.length ? '</ul>' : ''}
                        </div>
                        <div class="one-third">
                            <h2 class="search-overlay__section-title">Professors</h2>
                            ${results.professors.length ? '<ul class="professor-cards">' : `<p>No professors matches that search.</p>`}
                            ${results.professors.map(professor => `
                                 <li class="professor-card__list-item">
                                    <a class="professor-card" href="${professor.url}">
                                        <img class="professor-card__image" src="${professor.image}" alt="profesor">
                                        <span class="professor-card__name">"${professor.title}"</span>
                                    </a>
                                </li>
                            `).join('')}
                            ${results.professors.length ? '</ul>' : ''}
                        </div>
                    </div>
                `)
                this.isSpinnerVisible = false;
            })
        };
        keyPressDispatcher(e) {
            if (e.keyCode == 83 && !this.isOpen && !$('input, textarea').is(":focus")) {
                this.openOverlay();
            };
            if (e.keyCode == 27 && this.isOpen) {
                this.closeOverlay();
            }
        };
        openOverlay() {
            this.serachOverlay.addClass('search-overlay--active');
            $('body').addClass('body-no-scroll');
            this.searchInput.val('');
            setTimeout(() => this.searchInput.focus(), 301);
            this.isOpen = true;
            return false;
        };

        closeOverlay() {
            this.serachOverlay.removeClass('search-overlay--active');
            $('body').removeClass('body-no-scroll');
            this.isOpen = false;

        };

        addSearchHTML() {
            $('body').append(`
            <div class="search-overlay">
                <div class="search-overlay__top">
                    <div class="container">
                        <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
                        <input type="text" class="search-term" placeholder="What are you looking for?" id="search-item">
                        <i class="fa fa-window-close search-overlay__close" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="container">
                    <div id="search-overlay__results">
                    </div>
                </div>
            </div>
            `);
        }

    };
    const search = new Search();

    class MyNotes {
        constructor() {
            this.events()
        };
        events() {
            $('#my-notes').on('click', ".delete-note", this.deleteNote);
            $('#my-notes').on('click', ".edit-note", this.editNote.bind(this));
            $('#my-notes').on('click', ".update-note", this.updateNote.bind(this));
            $('.submit-note').on('click', this.createNote.bind(this));

        };
        deleteNote(e) {
            var thisNote = $(e.target).parents('li');
            $.ajax({
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', collegeData.nonce);
                },
                url: collegeData.root_url + '/wp-json/wp/v2/note/' + thisNote.data('id'),
                type: 'DELETE',
                success: (response) => {
                    thisNote.slideUp(300);
                    console.log('Success');
                    console.log(response);
                    if (response.userNoteCount < 5) {
                        $('.note-limit-message').removeClass('active');
                    }
                },
                error: (response) => {
                    console.log('SORRY');
                    console.log(response);


                },
            });
        };
        createNote(e) {
            var ourNewPost = {
                'title': $('.new-note-title').val(),
                'content': $('.new-note-body').val(),
                'status': 'publish'
            };
            $.ajax({
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', collegeData.nonce);
                },
                url: collegeData.root_url + '/wp-json/wp/v2/note/',
                type: 'POST',
                data: ourNewPost,
                success: (response) => {
                    $('.new-note-title, .new-note-body').val('');
                    $(`
                    <li data-id="${response.id}">
                    <input class="note-title-field" readonly value="${response.title.raw}" type="text">
                    <span class="edit-note"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</span>
                    <span class="delete-note"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</span>
                    <textarea  readonly class="note-body-field">${response.content.raw}</textarea>
                     <span class="update-note btn btn--blue btn--small"><i class="fa fa-arrow-right" aria-hidden="true"></i> Save</span>
                </li>
                    `).prependTo('#my-notes').hide().slideDown(300);
                    console.log('Success');
                    console.log(response);
                },
                error: (response) => {
                    if (response.responseText == "You have reached your note limit.") {
                        $('.note-limit-message').addClass('active');
                    }
                    console.log('SORRY');
                    console.log(response);


                },
            });
        };
        updateNote(e) {
            var thisNote = $(e.target).parents('li');
            var ourUpdatedPost = {
                'title': thisNote.find('.note-title-field').val(),
                'content': thisNote.find('.note-body-field').val()
            };
            $.ajax({
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', collegeData.nonce);
                },
                url: collegeData.root_url + '/wp-json/wp/v2/note/' + thisNote.data('id'),
                type: 'POST',
                data: ourUpdatedPost,
                success: (response) => {
                    this.makeNoteReadOnly(thisNote);
                    console.log('Success');
                    console.log(response);
                },
                error: (response) => {
                    console.log('SORRY');
                    console.log(response);


                },
            });
        };
        editNote(e) {
            var thisNote = $(e.target).parents('li');
            if (thisNote.data('state') == 'editable') {
                this.makeNoteReadOnly(thisNote);
            } else {
                this.makeNoteEditable(thisNote);
            }
        }

        makeNoteEditable(thisNote) {
            thisNote.find('.edit-note').html('<i class="fa fa-times" aria-hidden="true"></i> Cancel')
            thisNote.find('.note-title-field, .note-body-field').removeAttr('readonly').addClass('note-active-field');
            thisNote.find('.update-note').addClass('update-note--visible');
            thisNote.data('state', 'editable');
        };
        makeNoteReadOnly(thisNote) {
            thisNote.find('.edit-note').html('<i class="fa fa-pencil" aria-hidden="true"></i> Edit')
            thisNote.find('.note-title-field, .note-body-field').attr('readonly', 'readonly').removeClass('note-active-field');
            thisNote.find('.update-note').removeClass('update-note--visible');
            thisNote.data('state', 'cancel');

        };
    };

    const mynotes = new MyNotes();

    class Like {
        constructor() {
            this.events();
        }
        events() {
            $('.like-box').on('click', this.ourClickDispatcher.bind(this))
        }
        ourClickDispatcher(e) {
            var currentLikeBox = $(e.target).closest('.like-box');
            if (currentLikeBox.attr('data-exists') == 'yes') {
                this.deleteLike(currentLikeBox);
            } else {
                this.createLike(currentLikeBox);
            }
        };
        createLike(currentLikeBox) {
            $.ajax({
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', collegeData.nonce);
                },
                url: collegeData.root_url + '/wp-json/college/v1/manageLike',
                type: 'POST',
                data: {
                    'professorId': currentLikeBox.data('professor')
                },
                success: response => {
                    currentLikeBox.attr('data-exists', 'yes');
                    var likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
                    likeCount++;
                    currentLikeBox.find('.like-count').html(likeCount);
                    currentLikeBox.attr('data-like', response)
                    console.log(response);
                },
                error: response => {
                    console.log(response)
                }
            })
        }
        deleteLike(currentLikeBox) {
            $.ajax({
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', collegeData.nonce);
                },
                url: collegeData.root_url + '/wp-json/college/v1/manageLike',
                data: {
                    'like': currentLikeBox.attr('data-like')
                },
                type: 'DELETE',
                success: response => {
                    currentLikeBox.attr('data-exists', 'no');
                    var likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
                    likeCount--;
                    currentLikeBox.find('.like-count').html(likeCount);
                    currentLikeBox.attr('data-like', '')
                    console.log(response);
                },
                error: response => {
                    cosole.log(response)
                }
            })
        }
    }
    const like = new Like();
});
